<?php
namespace App\Form;

use App\Db\AnimalTypeMap;
use App\Db\Notice;
use App\Db\PathCaseMap;
use App\Db\Permission;
use App\Db\StorageMap;
use Tk\Date;
use Tk\Db\Tool;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;
use Tk\ObjectUtil;

/**
 * Example:
 * <code>
 *   $form = new PathCase::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class PathCase extends \Bs\FormIface
{
    /**
     * Readonly field exceptions
     * @var string[]
     */
    protected $exceptions = ['addendum'];

    /**
     * When set to true only the $this->exceptions (above) fields are editable.
     * @var bool
     */
    protected $readonly = false;

    protected $secondOpinionText = '';


    public function __construct($formId = '')
    {
        parent::__construct($formId);
        if ($this->getConfig()->getRequest()->has('gcl')) {
            $this->doGetContactList($this->getConfig()->getRequest());
        }
    }

    public function doGetContactList(\Tk\Request $request)
    {
        $companyId = $request->request->getInt('gcl');
        $contacts = \App\Db\CompanyContact::getSelectList($companyId);
        \Tk\ResponseJson::createJson($contacts)->send();
        exit();
    }


    /**
     * @throws \Exception
     */
    public function init()
    {
        // All MCE editors must use the case folder to store media in
        //     /media => WYSIWYG files, /files => case attached files
        // TODO: Allow WYSIWYG to view all files but only upload to html folder if possible (add this later)
        $mediaPath = $this->getPathCase()->getDataPath().'/media';
        $mce = 'mce-min';

        //$this->readonly = ($this->getPathCase()->hasStatus(\App\Db\PathCase::STATUS_COMPLETED)) && $this->getPathCase()->isBilled();
        if ($this->getPathCase()->hasStatus(\App\Db\PathCase::STATUS_COMPLETED) && $this->getPathCase()->isBilled()) {
            $this->readonly = true;
        }
        if (
            !($this->getAuthUser()->getId() == $this->getPathCase()->getUserId() ||
            $this->getAuthUser()->hasPermission([Permission::IS_PATHOLOGIST, Permission::IS_TECHNICIAN]))
        ) {
            $this->readonly = true;
        }


        $layout = $this->getRenderer()->getLayout();

        $layout->removeRow('arrival', 'col');
        $layout->removeRow('servicesCompletedOn', 'col');

        $layout->removeRow('type', 'col');

        $layout->addRow('billable', 'col-2');
        $layout->removeRow('accountStatus', 'col');
        $layout->removeRow('cost', 'col');

        $layout->removeRow('afterHours', 'col');

        $layout->removeRow('patientNumber', 'col');
        $layout->removeRow('microchip', 'col');

        $layout->removeRow('species', 'col');
        $layout->removeRow('specimenCount', 'col-2');

        $layout->removeRow('desexed', 'col');

        $layout->addRow('bioSamples', 'col2');
        $layout->removeRow('bioNotes', 'col');

        $layout->removeRow('weight', 'col');
        $layout->removeRow('colour', 'col');

        $layout->removeRow('dob', 'col');
        $layout->removeRow('dod', 'col');

        $layout->removeRow('euthanisedMethod', 'col');


        // FORM FIELDS
        $tab = 'Details';
        $this->appendField(new Field\Input('pathologyId'))->setLabel('Pathology ID')->setTabGroup($tab)
                ->addCss('tk-input-lock');

        $this->appendField(new Field\Input('arrival'))->setTabGroup($tab)->addCss('date')
            ->setAttr('placeholder', 'dd/mm/yyyy')->setNotes('The date the case was received.');

        $f = $this->appendField(new Field\Input('servicesCompletedOn'))->setTabGroup($tab)->addCss('date')
            ->setAttr('placeholder', 'dd/mm/yyyy')->setLabel('Services Completed On');
        if ($this->getPathCase()->getType() == \App\Db\PathCase::TYPE_NECROPSY) {
            $f->setLabel('Necropsy Performed On');
        }

        if (!$this->getPathCase()->getType()) {
            $list = \Tk\ObjectUtil::getClassConstants($this->getPathCase(), 'TYPE', true);
            $this->appendField(Field\Select::createSelect('type', $list)->prependOption('-- Select --', ''))
                ->setLabel('Case Type')->setTabGroup($tab);
        }

        $list = \Tk\ObjectUtil::getClassConstants($this->getPathCase(), 'SUBMISSION', true);
        $this->appendField(Field\Select::createSelect('submissionType', $list))
            ->prependOption('-- Select --', '')
            ->setTabGroup($tab);

        $layout->removeRow('contacts', 'col');

        $list = \App\Db\CompanyMap::create()->findFiltered([
            'institutionId' => $this->getConfig()->getInstitutionId()
        ], Tool::create("FIELD(name, 'Private Clients') DESC, a.name"));
        $this->appendField(Field\Select::createSelect('companyId', $list))
            ->prependOption('-- None --', '')
            ->addCss('tk-ms-company')->setLabel('Submitting Client')
            ->setTabGroup($tab);

        $contact = new \App\Db\CompanyContact();
        $form = \App\Form\CompanyContact::create('contactSelect')->setModel($contact);
        $form->appendField(Field\Hidden::create('companyId'));
        $list = \App\Db\CompanyContact::getSelectList($this->getPathCase()->getCompanyId() ?? 0);
        $field = Field\DialogSelect::createDialogSelect('contacts[]', $list, $form, 'Create Contact');
        $this->appendField($field)
            ->addCss('tk-multiselect tk-ms-contact')
            ->setAttr('data-reset-on-hide', true)
            ->setTabGroup($tab)->setLabel('Client Contacts')
            ->setNotes('Select Client Contacts that will be able to receive the pathology report.')
            ->setValue($this->getPathCase()->getContactList()->toArray('id'));;

        $modalId = json_encode($field->getDialog()->getId());
        $companyId = json_encode($this->getPathCase()->getCompanyId() ?? 0);

        $js = <<<JS
jQuery(function ($) {
    const contactModal = $('#' + {$modalId});
    let select = $('select.tk-ms-contact');
    let companyId = ${companyId};
    
    contactModal.on('shown.bs.modal', function() {
        // Set the company ID for new contact
        $('input[name=companyId]', contactModal).val(companyId);
    });
    
    select.on('change', function () {
        $(document).trigger('company-panel.refresh');
    });
    
    $('select.tk-ms-company').on('change', function () {
        // Populate the contactId field
        companyId = $(this).val();
        
        $(document).trigger('company-panel.refresh');
        
        // AJAX call for list of available contacts (if any)
        $.get(document.location, { 
            'gcl': companyId, 
            crumb_ignore: 'crumb_ignore',
            //nolog: 'nolog' 
        })
        .done(function(data) {
            select.empty();
            for(const k in data) {
                select.append("<option value=\"" + data[k] + "\">" + k + "</option>")
            }
        })
        .fail(function() {
            console.error('Cannot load company contacts.')
        });
    });
    
    select.select2({
        placeholder: 'Select Contact',
        allowClear: false,
        minimumInputLength: 0
    });
  
});
JS;
        $this->getRenderer()->getTemplate()->appendJs($js);


        $this->appendField(new Field\Checkbox('billable'))->setTabGroup($tab)
            ->setNotes('Is this case billable?');
        $js = <<<JS
jQuery(function ($) {
  function updateBillable(el) {
    var d = 'disabled';
    if (el.prop('checked') === true) {
      $('#path_case-accountStatus').removeAttr(d, d);
      $('#path_case-cost').removeAttr(d, d);
      $('.tk-invoice-list').css('display', 'block');
      if ($('#path_case-accountStatus').val() === '') {
        $("#path_case-accountStatus").prop("selectedIndex", 1);
      }
    } else {
      $('#path_case-accountStatus').attr(d, d);
      $('#path_case-cost').attr(d, d);
      $('.tk-invoice-list').css('display', 'none');
    }
  }
  $('#path_case-billable').on('change', function () {
    updateBillable($(this));
  });
  updateBillable($('#path_case-billable'));
});
JS;
        $this->getRenderer()->getTemplate()->appendJs($js);

        $this->appendField(Field\Select::createSelect('accountStatus', \App\Db\PathCase::ACCOUNT_STATUS_LIST)->prependOption('-- Select --', ''))
            ->setTabGroup($tab);

        $this->appendField(new Field\Checkbox('submissionReceived'))->setTabGroup($tab)
            ->setNotes('Has the submission form been received?');

        $this->appendField(new Field\Checkbox('afterHours'))->setTabGroup($tab)
            ->setNotes('Was this case worked after normal open hours?');

        if ($this->getPathCase()->getType() == \App\Db\PathCase::TYPE_BIOPSY || !$this->getPathCase()->getType()) {
            $this->appendField(new Field\Input('bioSamples'))->setType('number')->setTabGroup($tab);
            $this->appendField(new Field\Input('bioNotes'))->setTabGroup($tab);
        }

        if ($this->getPathCase()->getId()) {
            $list = \App\Db\PathCase::getStatusList($this->getPathCase()->getStatus());
            $this->appendField(\Bs\Form\Field\StatusSelect::createSelect('status', $list)->prependOption('-- Status --', ''))
                ->setRequired()->setTabGroup($tab)
                ->setNotes('Set the status. Use the checkbox to disable notification emails.');
        }


        $tab = 'Details';
        $list  = $this->getConfig()->getUserMapper()->findFiltered(
            [
                'institutionId'=> $this->getPathCase()->getInstitutionId(),
                'permission' => Permission::IS_PATHOLOGIST,
                'active' => true
            ],
            Tool::create('nameFirst')
        );
        $this->appendField(Field\Select::createSelect('pathologistId', $list)
            ->prependOption('-- Select --', ''))
            ->setTabGroup($tab)->setLabel('Pathologist');

//        $student = new \App\Db\Student();
//        $form = \App\Form\Student::create('studentSelect')->setModel($student);
//        $list = \App\Db\Student::getSelectList();
//        $this->appendField(Field\DialogSelect::createDialogSelect('students[]', $list, $form,'Create Student'))
//            ->addCss('tk-multiselect tk-multiselect2')
//            ->setTabGroup($tab)->setLabel('Students')->setNotes('Type to find an existing student, create a new student record if not found in list.')
//            ->setValue($this->getPathCase()->getStudentList()->toArray('id'));

        // Enable select2 multi select for student field
        $js = <<<JS
jQuery(function ($) {
  	$('select.tk-multiselect2').select2({
        placeholder: 'Select a Student',
        allowClear: false,
        minimumInputLength: 0
    });
});
JS;
        $this->getRenderer()->getTemplate()->appendJs($js);

        $this->appendField(Field\CheckboxInput::create('zoonotic'))->setTabGroup($tab)->setLabel('Zoonotic/Other Risks')
            ->setNotes('Tick the checkbox to alert users of these risks when viewing this case.')
            ->setAttr('placeholder', 'None');

        $this->appendField(Field\CheckboxInput::create('issue'))->setTabGroup($tab)
            ->setLabel('Case Issues')
            ->setNotes('Tick the checkbox to alert users of any issues to be aware of when viewing this case.')
            ->setAttr('placeholder', 'None');


        $tab = 'Animal';
        $this->appendField(new Field\Autocomplete('ownerName'))->setTabGroup($tab)
            ->addOnAjax(function (Field\Autocomplete $field, \Tk\Request $request) {
                return PathCaseMap::create()->getOwnerNameList($this->getConfig()->getInstitutionId(), $request->get('term'));
            });

        $this->appendField(new Field\Input('animalName'))->setLabel('Animal Name/ID')->setTabGroup($tab);
        $this->appendField(new Field\Input('patientNumber'))->setTabGroup($tab);
        $this->appendField(new Field\Input('microchip'))->setTabGroup($tab);

        $list = AnimalTypeMap::create()->findFiltered(['institutionId' => $this->getConfig()->getInstitutionId(), 'parent_id' => 0]);
        $this->appendField(Field\Select::createSelect('animalTypeId', $list)->prependOption('-- Select --', ''))
            ->setTabGroup($tab);

        $this->appendField(new Field\Autocomplete('species'))->setLabel('Species/Breed')->setTabGroup($tab)
            ->addOnAjax(function (Field\Autocomplete $field, \Tk\Request $request) {
                return PathCaseMap::create()->getSpeciesList($this->getConfig()->getInstitutionId(), $request->get('term'));
            });

        $this->appendField(new Field\Input('specimenCount'))->setLabel('Animal Count')->setTabGroup($tab);
        $list = array('-- N/A --' => '', 'Male' => 'M', 'Female' => 'F');
        $this->appendField(Field\Select::createSelect('sex', $list))
            ->setTabGroup($tab);
        $this->appendField(new Field\Checkbox('desexed'))->setTabGroup($tab);

        $list = [
            '-- N/A --' => '',
            'Extra-small < 1kg' => 'Extra-small < 1kg',
            'Small < 10kg' => 'Small < 10kg',
            'Medium < 50kg' => 'Medium < 50kg',
            'Large < 200kg' => 'Large < 200kg',
            'Extra-large > 200kg' => 'Extra-large > 200kg'
        ];
        $this->appendField(Field\Select::createSelect('size', $list))
            ->setTabGroup($tab);
        $this->appendField(new Field\Input('weight'))->setTabGroup($tab);

        $this->appendField(new Field\Autocomplete('colour'))->setTabGroup($tab)
            ->addOnAjax(function (Field\Autocomplete $field, \Tk\Request $request) {
                return PathCaseMap::create()->getColourList($this->getConfig()->getInstitutionId(), $request->get('term'));
            });

        $this->appendField(new \App\Form\Field\Age('age'))->setTabGroup($tab);

        $this->appendField(new Field\Input('dob'))->setTabGroup($tab)->addCss('date')
            ->setAttr('placeholder', 'dd/mm/yyyy');
        $this->appendField(new Field\Input('dod'))->setTabGroup($tab)->addCss('date')
            ->setAttr('placeholder', 'dd/mm/yyyy');


        $this->appendField(new Field\Checkbox('euthanised'))->setTabGroup($tab);
        $this->appendField(new Field\Input('euthanisedMethod'))->setTabGroup($tab);

        $this->appendField(new Field\Textarea('clinicalHistory'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);


        $tab = 'Reporting';


        $this->appendField(new Field\Checkbox('studentReport'))->setTabGroup($tab);

        $list  = ObjectUtil::getClassConstants($this->getPathCase(), 'REPORT_STATUS', true);
        $f = $this->appendField(Field\Select::createSelect('reportStatus', $list)->prependOption('-- Select --', ''))
            ->setTabGroup($tab);

        if (!$this->getAuthUser()->hasPermission([Permission::IS_PATHOLOGIST, Permission::CASE_FULL_EDIT])) {
            $f->setDisabled(true);
        }

        $this->appendField(new Field\Textarea('collectedSamples'))
            ->addCss('mce-min')->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);

        $this->appendField(new Field\Textarea('grossPathology'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);

        $this->appendField(new Field\Textarea('grossMorphologicalDiagnosis'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab)
            ->setNotes('This information will not be included in the final report');

        $this->appendField(new Field\Textarea('histopathology'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);

        $this->appendField(new Field\Textarea('ancillaryTesting'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);

        $this->appendField(new Field\Textarea('morphologicalDiagnosis'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);

        $this->appendField(new Field\Textarea('causeOfDeath'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);

        $this->appendField(new Field\Textarea('comments'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);

        $this->appendField(new Field\Textarea('secondOpinion'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab)
            ->setNotes('If you edit this field, then your details will be used as the pathologist on the report. Clear this text field to revert to the case`s selected pathologist.');

        $this->appendField(new Field\Textarea('addendum'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);

        $list  = $this->getConfig()->getUserMapper()->findFiltered(
            [
                'institutionId'=> $this->getPathCase()->getInstitutionId(),
                'permission' => Permission::CAN_REVIEW_CASE,
                'active' => true
            ],
            Tool::create('nameFirst')
        );
        $this->appendField(Field\Select::createSelect('reviewedById', $list)
            ->prependOption('-- Select --', ''))
            ->setTabGroup($tab)->setLabel('Case Reviewed By');

        // Setup auto-save on the tinymce instances
        $js = <<<JS
jQuery(function ($) {
  
  var init = function() {
      var form = $(this);
      form.find('textarea.mce, textarea.mce-med, textarea.mce-min, textarea.mce-micro')
        .each(function () {
          var el = $(this);
          var ed = tinymce.get(el.attr('id'));
          var urlParams = new URLSearchParams(window.location.search);
          ed.on('change', function () {
            if (!el.hasClass('saving')) {
              el.addClass('saving');
              $.post(config.siteUrl + '/ajax/mceAutosave', {
                crumb_ignore: 'crumb_ignore',
                nolog: 'nolog',
                obj: 'PathCase',
                id: urlParams.get('pathCaseId'),
                fieldName: el.attr('name'),
                value: ed.getContent()
              }, function (data) {
              }).fail(function() {
                  console.error('error')
              }).always(function() {
                el.removeClass('saving');
              });
            }
          });
        });
  };
  $('form').on('change', document, init).each(init);
});
JS;
        $this->getRenderer()->getTemplate()->appendJs($js);

        $js = <<<JS
jQuery(function ($) {
    $(document).on('click', '#request-save', function () {
        // remove the services completed date value
        $('#path_case-servicesCompletedOn').val('');
    });
});
JS;
        $this->getRenderer()->getTemplate()->appendJs($js);

        $tab = 'Files';
        $maxFiles = 10;
        /** @var \Bs\Form\Field\File $fileField */
        $this->appendField(\Bs\Form\Field\File::createFile('files[]', $this->getPathCase()))
            ->setTabGroup($tab)->setAttr('data-max-files', $maxFiles)
            ->setAttr('data-select-title', 'Add file to report emails.'
            )
            ->setNotes('Upload any related files. A max. of '.$maxFiles.' files can be selected and uploaded per form submission.<br/>Select/check any file you want to be included with the email report.<br/>Note: Files larger than 2Mb will not be attached to emails.');


        $tab = 'After Care';
        if ($this->getPathCase()->getType() == \App\Db\PathCase::TYPE_NECROPSY) {
            $list = StorageMap::create()->findFiltered(['institutionId' => $this->getConfig()->getInstitutionId()], Tool::create('name'));
            $this->appendField(Field\Select::createSelect('storageId', $list)->prependOption('-- Select --', ''))
                ->setTabGroup($tab);

            $this->appendField(new Field\Input('acHold'))->addCss('date')->setAttr('placeholder', 'dd/mm/yyyy')
                ->setLabel('Hold Until')->setTabGroup($tab);

            $this->appendField(Field\Select::createSelect('disposeMethod', \App\Db\PathCase::DISPOSAL_METHOD_LIST)->prependOption('-- None --', ''))
                ->setLabel('Method Of Disposal')->setTabGroup($tab);

            $this->appendField(new Field\Input('disposeOn'))->addCss('date')->setAttr('placeholder', 'dd/mm/yyyy')
                ->setLabel('Disposal Completion Date')->setTabGroup($tab);
        }

        foreach ($this->getFieldList() as $field) {
            if (in_array($field->getName(), $this->exceptions)) continue;
            if ($this->isReadonly()) {
                $field->setReadonly();
            }
        }

        if (!$this->readonly) {
            $this->appendField(new Event\Submit('update', array($this, 'doSubmit')));
            $this->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        }
        $this->appendField(new Event\Link('cancel', $this->getBackUrl()));
    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function execute($request = null)
    {
        if ($this->getRequest()->has('action')) return;        // ignore column requests
        $this->getForm()->getField('contacts')->getDialog()->execute($request);
        //$this->getForm()->getField('students')->getDialog()->execute($request);

        $this->load(\App\Db\PathCaseMap::create()->unmapForm($this->getPathCase()));
        parent::execute($request);
    }

    public function doSubmit(Form $form, Event\Iface $event)
    {
        $this->secondOpinionText = $this->getPathCase()->getSecondOpinion();

        // Load the object with form data
        $vals = $form->getValues();

        if (!isset($vals['billable']) || !$vals['billable']) {
            unset($vals['accountStatus']);
        }
        // Stop any javascript accidentally sending data back that gets updated.
        if ($this->isReadonly()) {
            $newVals = [];
            foreach ($vals as $k => $v) {
                if (!in_array($k, $this->exceptions)) continue;
                $newVals[$k] = $v;
            }
            $vals = $newVals;
        }

        $orgCompanyId = $this->getPathCase()->getCompanyId();

        // set null values
        if (!$vals['pathologistId']) $vals['pathologistId'] = null;
        if (!$vals['reviewedById']) $vals['reviewedById'] = null;
        if (!$vals['companyId']) $vals['companyId'] = null;
        if (!$vals['disposedOn']) $vals['disposedOn'] = null;
        if (!$vals['disposedOn']) $vals['disposedOn'] = null;

        \App\Db\PathCaseMap::create()->mapForm($vals, $this->getPathCase());

        $form->addFieldErrors($this->getPathCase()->validate());
        if ($form->hasErrors()) {
            if (!array_key_exists('files', $form->getAllErrors())) {
                $form->addFieldError('files', 'Please re-select any files if required.');
            }
            return;
        }

        $isNew = ($this->getPathCase()->getId() == 0);
        $this->getPathCase()->setStatusNotify(true);
        if ($isNew)
            $this->getPathCase()->setStatusMessage('New pathology case created.');

        // This should automatically set the reportStatus to completed once the case is completed, as this option is sometimes fogotten
        $autocomplete = (bool)$this->getConfig()->getInstitution()->getData()->get(\App\Controller\Institution\Edit::INSTITUTION_AUTOCOMPLETE_REPORT_STATUS);
        if (
            $autocomplete &&
            $this->getPathCase()->getStatus() == \App\Db\PathCase::STATUS_COMPLETED &&
            $this->getPathCase()->getCurrentStatus()->getName() != \App\Db\PathCase::STATUS_COMPLETED &&
            $this->getPathCase()->getReportStatus() != \App\Db\PathCase::REPORT_STATUS_COMPLETED
        )
        {
            $this->getPathCase()->setReportStatus(\App\Db\PathCase::REPORT_STATUS_COMPLETED);
        }

        $this->getPathCase()->save();

        if ($isNew) {
            Notice::create($this->getPathCase());
        }

        // Update second Opinion user
        if ($this->secondOpinionText != $this->getPathCase()->getSecondOpinion()) {
            $this->getPathCase()->setSoUserId($this->getAuthUser());
        }
        if (!trim($this->getPathCase()->getSecondOpinion())) {
            $this->getPathCase()->setSoUserId(0);
        }

        // Clear contacts if company has changed
        if ($this->getPathCase()->getCompanyId() != $orgCompanyId) {
            PathCaseMap::create()->removeContact($this->getPathCase()->getId());
        }
        // save selected company contacts...
        if (!empty($vals['contacts']) && is_array($vals['contacts'])) {
            // Remove all existing linked contacts
            PathCaseMap::create()->removeContact($this->getPathCase()->getId());
            foreach ($vals['contacts'] as $id) {
                PathCaseMap::create()->addContact($this->getPathCase()->getId(), $id);
            }
        }

        // Save selected students
        if (!empty($vals['students']) && is_array($vals['students'])) {
            // Remove all existing linked students
            PathCaseMap::create()->removeStudent($this->getPathCase()->getId());
            foreach ($vals['students'] as $id) {
                PathCaseMap::create()->addStudent($this->getPathCase()->getId(), $id);
            }
        }

        /** @var \Bs\Form\Field\File $fileField */
        $fileField = $form->getField('files');
        $fileField->doSubmit();
        if (
            $this->getPathCase()->hasStatus([\App\Db\PathCase::STATUS_EXAMINED, \App\Db\PathCase::STATUS_REPORTED, \App\Db\PathCase::STATUS_COMPLETED]) &&
            $this->getAuthUser()->hasPermission(Permission::CAN_REVIEW_CASE)
        ) {
            if ($form->getField('reviewCase')->getValue()) {
                $this->getPathCase()->setReviewedById($this->getAuthUser()->getId());
                $this->getPathCase()->setReviewedOn(Date::create());
                $this->getPathCase()->save();
            }
        }

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('pathCaseId', $this->getPathCase()->getId()));
        }
    }

    public function isReadonly()
    {
        if ($this->getAuthUser()->hasPermission(Permission::CASE_FULL_EDIT))
            return false;
        return $this->readonly;
    }

    public function getPathCase(): \App\Db\PathCase
    {
        return $this->getModel();
    }

    public function setPathCase(\App\Db\PathCase $pathCase): PathCase
    {
        return $this->setModel($pathCase);
    }

}