<?php
namespace App\Form;

use App\Db\AnimalTypeMap;
use App\Db\ContactMap;
use App\Db\Notice;
use App\Db\PathCaseMap;
use App\Db\Permission;
use App\Db\StorageMap;
use App\Db\StudentMap;
use Tk\Alert;
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
        if ($this->getConfig()->getRequest()->has('gc')) {
            $this->doGetContact($this->getConfig()->getRequest());
        }
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
        $this->readonly = ($this->getPathCase()->hasStatus(\App\Db\PathCase::STATUS_COMPLETED)) && $this->getPathCase()->isBilled();

        $layout = $this->getRenderer()->getLayout();

        $layout->removeRow('arrival', 'col');

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
            
        if (!$this->getPathCase()->getType()) {
            $list = \Tk\ObjectUtil::getClassConstants($this->getPathCase(), 'TYPE', true);
            $this->appendField(Field\Select::createSelect('type', $list)->prependOption('-- Select --', ''))
                ->setLabel('Case Type')->setTabGroup($tab);
        }

        $list = \Tk\ObjectUtil::getClassConstants($this->getPathCase(), 'SUBMISSION', true);
        $this->appendField(Field\Select::createSelect('submissionType', $list)->prependOption('-- Select --', ''))
            ->setTabGroup($tab);

        $contact = new \App\Db\Contact();
        $contact->setType(\App\Db\Contact::TYPE_CLIENT);
        $form = \App\Form\Contact::create('clientSelect')->setType(\App\Db\Contact::TYPE_CLIENT)->setModel($contact);
        $form->removeField('notes');
        $list = \App\Db\Contact::getSelectList(\App\Db\Contact::TYPE_CLIENT);
        $this->appendField(Field\DialogSelect::createDialogSelect('clientId[]', $list, $form, 'Create Client'))
            ->addCss('tk-multiselect tk-multiselect1')
            ->setAttr('data-reset-on-hide', true)
            ->setTabGroup($tab)->setLabel('Submitting Client')
            ->setNotes('Start typing to find a client, if not found use the + icon to create a new client.<br/>The client to be invoiced.');

        $js = <<<JS
jQuery(function ($) {
  
  $('select.tk-multiselect1').select2({
    placeholder: 'Select Contact',
    allowClear: false,
    maximumSelectionLength: 1,
    minimumInputLength: 0,
    escapeMarkup: function (item) {
      return '<span class="tk-selection_choice_label">'+item+'</span>';
    }
  }).on('change', function () {
    // Disable the add contact button if one is selected
    var btn = $(this).closest('.input-group').find('button');
    if ($(this).val().length) {
      btn.attr('title', 'Clear selected to create new record.');
      btn.attr('disabled', 'disabled');
    } else {
      btn.attr('title', $(btn.attr('data-target') + ' .modal-title').text());
      btn.removeAttr('disabled', 'disabled');
    }
    
    // Limit to one selection after a new contact created
    if ($(this).val().length > 1) {
      var a = $(this).val();
      $(this).val([a[a.length-1]]);
      $(this).trigger('change');
    }
    
  }).trigger('change');
  
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

        $list = \Tk\ObjectUtil::getClassConstants($this->getPathCase(), 'ACCOUNT_STATUS', true);
        $this->appendField(Field\Select::createSelect('accountStatus', $list)->prependOption('-- Select --', ''))
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

        $contact = new \App\Db\Contact();
        $contact->setType(\App\Db\Contact::TYPE_OWNER);
        $form = \App\Form\Contact::create('ownerSelect')->setType(\App\Db\Contact::TYPE_OWNER)->setModel($contact);
        $form->removeField('accountCode');
        $form->removeField('nameCompany');
        $form->removeField('notes');

        $this->appendField(new Field\Input('ownerName'))->setTabGroup($tab);
        $this->appendField(new Field\Input('animalName'))->setLabel('Animal Name/ID')->setTabGroup($tab);
        $this->appendField(new Field\Input('patientNumber'))->setTabGroup($tab);
        $this->appendField(new Field\Input('microchip'))->setTabGroup($tab);

        $list = AnimalTypeMap::create()->findFiltered(['institutionId' => $this->getConfig()->getInstitutionId(), 'parent_id' => 0]);
        $this->appendField(Field\Select::createSelect('animalTypeId', $list)->prependOption('-- Select --', ''))
            ->setTabGroup($tab);

        // TODO: Autocomplete field
        $this->appendField(new Field\Input('species'))->setLabel('Species/Breed')->setTabGroup($tab);

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
        $this->appendField(new Field\Input('colour'))->setTabGroup($tab);
        $this->appendField(new \App\Form\Field\Age('age'))->setTabGroup($tab);

        // TODO: we need to validate the dob is < dod at some point
        $this->appendField(new Field\Input('dob'))->setTabGroup($tab)->addCss('date')
            ->setAttr('placeholder', 'dd/mm/yyyy');
        $this->appendField(new Field\Input('dod'))->setTabGroup($tab)->addCss('date')
            ->setAttr('placeholder', 'dd/mm/yyyy');

        $list  = $this->getConfig()->getUserMapper()->findFiltered(
            array('institutionId'=> $this->getPathCase()->getInstitutionId(), 'permission' => Permission::IS_PATHOLOGIST, 'active' => true),
            Tool::create('nameFirst')
        );
        $this->appendField(Field\Select::createSelect('pathologistId', $list)
            ->prependOption('-- Select --', ''))
            ->setTabGroup($tab)->setLabel('Pathologist');

        $student = new \App\Db\Student();
        $form = \App\Form\Student::create('studentSelect')->setModel($student);
        $list = \App\Db\Student::getSelectList();
        $this->appendField(Field\DialogSelect::createDialogSelect('students[]', $list, $form,'Create Student'))
            ->addCss('tk-multiselect tk-multiselect2')
            ->setTabGroup($tab)->setLabel('Students')->setNotes('(Optional) Start typing to find an existing student, only create a new student record if not found in list.')
            ->setValue($this->getPathCase()->getStudentList()->toArray('id'));

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

        $this->appendField(new Field\Checkbox('euthanised'))->setTabGroup($tab);
        $this->appendField(new Field\Input('euthanisedMethod'))->setTabGroup($tab);

        $this->appendField(Field\CheckboxInput::create('zoonotic'))->setTabGroup($tab)->setLabel('Zoonotic/Other Risks')
            ->setNotes('Tick the checkbox to alert users of these risks when viewing this case.')
            ->setAttr('placeholder', 'None');

        $this->appendField(Field\CheckboxInput::create('issue'))->setTabGroup($tab)
            ->setLabel('Case Issues')
            ->setNotes('Tick the checkbox to alert users of any issues to be aware of when viewing this case.')
            ->setAttr('placeholder', 'None');

        $this->appendField(new Field\Textarea('clinicalHistory'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);

        $tab = 'Reporting';
        $list  = ObjectUtil::getClassConstants($this->getPathCase(), 'REPORT_STATUS', true);

        $this->appendField(new Field\Checkbox('studentReport'))->setTabGroup($tab);

        $this->appendField(Field\Select::createSelect('reportStatus', $list)->prependOption('-- Select --', ''))
            ->setTabGroup($tab);

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


        // File field
        $tab = 'Files';
        $maxFiles = 10;
        /** @var \Bs\Form\Field\File $fileField */
        $this->appendField(\Bs\Form\Field\File::createFile('files[]', $this->getPathCase()))
            ->setTabGroup($tab)->setAttr('data-max-files', $maxFiles)
            ->setAttr('data-select-title', 'Add file to report emails.')
            ->setNotes('Upload any related files. A max. of '.$maxFiles.' files can be selected and uploaded per form submission.<br/>Select/check any file you want to be included with the email report.<br/>Note: Files larger than 2Mb will not be attached to emails.');


        $tab = 'After Care';
        if ($this->getPathCase()->getType() != \App\Db\PathCase::TYPE_BIOPSY) {
            $list = StorageMap::create()->findFiltered(['institutionId' => $this->getConfig()->getInstitutionId()], Tool::create('name'));
            $this->appendField(Field\Select::createSelect('storageId', $list)->prependOption('-- Select --', ''))
                ->setTabGroup($tab);
            $this->appendField(new Field\Input('acHold'))->addCss('date')->setAttr('placeholder', 'dd/mm/yyyy')
                ->setLabel('Aftercare Hold')->setTabGroup($tab);
            $list = \Tk\ObjectUtil::getClassConstants($this->getPathCase(), 'AC_');
            $this->appendField(Field\Select::createSelect('acType', $list)->prependOption('-- None --', ''))
                ->setLabel('Method Of Disposal')->setTabGroup($tab);
            $this->appendField(new Field\Input('acHold'))->addCss('date')->setAttr('placeholder', 'dd/mm/yyyy')
                ->setLabel('Hold Until')->setTabGroup($tab);
            $this->appendField(new Field\Input('disposal'))->addCss('date')->setAttr('placeholder', 'dd/mm/yyyy')
                ->setLabel('Disposal Completion Date')->setTabGroup($tab);
        }

        foreach ($this->getFieldList() as $field) {
            if (in_array($field->getName(), $this->exceptions)) continue;
            if ($this->isReadonly()) {
                $field->setReadonly(); //->setDisabled();
            }
        }

        $this->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->appendField(new Event\Link('cancel', $this->getBackUrl()));



        // Add contact hover box
        $js = <<<JS
jQuery(function ($) {

    function closePanel()
    {
        if ($('.tk-contact:hover').length || $('.tk-contact .pinned').length) return;
        $('.tk-contact').fadeOut(function () {
             $('.tk-contact').remove();
        });
    }
        
    var init = function () {
        $(document).on('click', closePanel);
        
        $(this).parent().find('.tk-selection_choice_label').each(function (e) {
            $(this).attr('title', 'Click to view details');
            $(this).on('click', function (e) {
                if ($('.tk-contact .pinned').length) return;
                $('.tk-contact').remove();

                var el = $(this);
                var data = $(this).closest('.select2-selection__choice').data()['data'];
                var name = data['text'].split('(')[0];
                var email = '';
                if (data['text'].indexOf('(') > -1 && data['text'].split('(')[1].replace(')', '')) {
                    email = data['text'].split('(')[1].replace(')', '');
                }

                $.get(document.location, {
                    'gc': data['id'],
                    crumb_ignore: 'crumb_ignore',
                    nolog: 'nolog'
                }, function (gcData) {
                    var contact = gcData.contact;
                    var html = '<div class="tk-contact"><div class="control">' +
                        '<a href="javascript:;" class="tk-close"><i class="fa fa-times"></i></a>' +
                        '<a href="contactEdit.html?contactId=' + contact.id + '&crumb_ignore=&nolog=" target="_blank" class="tk-edit"><i class="fa fa-pencil"></i></a>' +
                        '<a href="javascript:;" class="tk-pin"><i class="fa fa-thumb-tack"></i></a>' +
                        '</div>' +
                        '<h3 class="name"><a href="contactEdit.html?contactId=' + contact.id + '&crumb_ignore=&nolog=" target="_blank">' + name + '</a></h3>';
                    // if (email)
                    //   html += '<p><a href="mailto:'+email+'" class="email">' + email + '</a></p>';
                    //html += '<hr/>';
                    html += '<div class="details"><b>Contact:</b><br/>';
                    html += '<ul>';
                    if (contact.email)
                        html += '<li><i class="fa fa-envelope-o"></i> <a href="mailto:' + contact.email + '">' + contact.email + '</a></li>';
                    if (contact.nameCompany)
                        html += '<li><i class="fa fa-building"></i> <a href="contactEdit.html?contactId=' + contact.id + '" target="_blank">' + contact.nameCompany + '</a></li>';
                    if (contact.phone)
                        html += '<li><i class="fa fa-phone"></i> <a href="tel:' + contact.phone + '">' + contact.phone + '</a></li>';
                    if (contact.fax)
                        html += '<li><i class="fa fa-fax"></i> <a href="tel:' + contact.fax + '">' + contact.fax + '</a></li>';
                    if (contact.address)
                        html += '<li><i class="fa fa-building"></i> <a href="tel:' + contact.address + '">' + contact.address + '</a></li>';
                    html += '</ul></div>';
                    html += '';

                    if (contact.notes && contact.notes !== '***')
                        html += '<p>' + contact.notes + '</p>';

                    html += '</div>';

                    var off = el.offset();
                    var x = e.pageX - off.left;
                    var y = e.pageY - off.top;

                    var panel = $(html);
                    panel.css('left', x);
                    panel.css('top', y);
                    el.closest('.form-group').append(panel);

                    panel.find('a.tk-close').on('click', function () {
                        $('.tk-contact').fadeOut(function () {
                             $('.tk-contact').remove();
                        });
                    });
                    panel.find('a.tk-pin').on('click', function () {
                        $(this).toggleClass('pinned');
                    });
                }, 'json');

            });

        });

    };
    $('.tk-multiselect').on('change', document, init).each(init);
});
JS;
        $this->getRenderer()->getTemplate()->appendJs($js);

        $css = <<<CSS
.tk-contact {
  background-color: white;
  border: 1px solid #CCC;
  position: absolute;
  box-shadow: 2px 2px 2px #CCC;
  z-index: 9999;
  padding: 10px;
  min-width: 300px;
  max-width: 450px;
  border-radius: 5px;
  font-size: 0.9em;
}
.tk-contact .control a {
  color: #666;
  display: inline-block;
  padding: 0 5px;
  float: right;
}
.tk-contact .control a.tk-pin {
  transform: rotate(90deg);
}
.tk-contact .control a.tk-pin.pinned {
  transform: rotate(45deg);
  color: #000;
}
.tk-contact h3 {
 padding: 0;
 margin: 0 0 10px 0;
}
.tk-contact ul {
  list-style: none;
  padding: 0;
  margin: 0.2em 0 0.2em 3em;
}
.tk-contact ul li {
  list-style-position: outside;
  text-indent: -0.6em;
  margin-top: 0.4em;
}
.tk-contact ul li .fa {
  margin-right: 0.2em;
}

.tk-contact .details .fa {
  color: #999;
}
.tk-contact .details p {
  padding: 0px 0px;
  margin: 5px 0px 5px 20px;
}

/* Fix Google place autocomplete z-index */
.pac-container {
    z-index: 10000 !important;
}

CSS;
        $this->getRenderer()->getTemplate()->appendCss($css);

    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function execute($request = null)
    {
        if ($this->getRequest()->has('action')) return;        // ignore column requests
        $this->getForm()->getField('clientId')->getDialog()->execute($request);
        if ($this->getForm()->getField('ownerId')) {
            $this->getForm()->getField('ownerId')->getDialog()->execute($request);
        }
        // TODO:
        $this->getForm()->getField('students')->getDialog()->execute($request);

        $this->load(\App\Db\PathCaseMap::create()->unmapForm($this->getPathCase()));
        parent::execute($request);
    }

    public function doGetContact(\Tk\Request $request)
    {
        $data = [];
        $contact = ContactMap::create()->find($request->get('gc'));
        if ($contact) {
            $contact->address = $contact->getAddress();
            $data['contact'] = $contact;
            \Tk\ResponseJson::createJson($data)->send();
            exit();
        }
        \Tk\ResponseJson::createJson(['status' => 'err', 'msg' => 'Contact Not Found!'])->send();
        exit();
    }

    /**
     * @param Form $form
     * @param Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        $this->secondOpinionText = $this->getPathCase()->getSecondOpinion();

        // Load the object with form data
        $vals = $form->getValues();

//        if (!empty($vals['clientId']) && is_array($vals['clientId']))
//            $vals['clientId'] = current($vals['clientId']);
//        else
//            $vals['clientId'] = 0;

//        if (!empty($vals['ownerId']) && is_array($vals['ownerId']))
//            $vals['ownerId'] = current($vals['ownerId']);
//        else
//            $vals['ownerId'] = 0;

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

        // TODO save selected company contacts...

        // Save selected students
        if (!empty($vals['students']) && is_array($vals['students'])) {
            // Remove all existing linked students
            PathCaseMap::create()->removeStudent($this->getPathCase()->getId());
            foreach ($vals['students'] as $id) {
                $student = StudentMap::create()->find($id);
                if ($student) {
                    PathCaseMap::create()->addStudent($this->getPathCase()->getId(), $student->getId());
                }
            }
        }

        /** @var \Bs\Form\Field\File $fileField */
        $fileField = $form->getField('files');
        $fileField->doSubmit();

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

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\PathCase
     */
    public function getPathCase()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\PathCase $pathCase
     * @return $this
     */
    public function setPathCase($pathCase)
    {
        return $this->setModel($pathCase);
    }

}