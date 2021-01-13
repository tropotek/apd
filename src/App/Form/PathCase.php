<?php
namespace App\Form;

use App\Db\AnimalTypeMap;
use App\Db\ContactMap;
use App\Db\StorageMap;
use App\Form\Field\Money;
use Tk\Db\Tool;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;
use Tk\ObjectUtil;
use Uni\Db\User;

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

    public function __construct($formId = '')
    {
        parent::__construct($formId);

        if ($this->getConfig()->getRequest()->has('del')) {
            $this->doDelete($this->getConfig()->getRequest());
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

        $layout = $this->getRenderer()->getLayout();

        $layout->removeRow('arrival', 'col');

        $layout->removeRow('type', 'col');
        $layout->removeRow('clientId', 'col');

        $layout->addRow('billable', 'col-2');
        $layout->removeRow('accountStatus', 'col');
        $layout->removeRow('cost', 'col');

        $layout->removeRow('afterHours', 'col');

        $layout->removeRow('patientNumber', 'col');
        $layout->removeRow('microchip', 'col');

        $layout->removeRow('species', 'col');
        $layout->removeRow('specimenCount', 'col-2');

        $layout->removeRow('desexed', 'col');

        $layout->removeRow('colour', 'col');
        //$layout->removeRow('origin', 'col');

        $layout->removeRow('dob', 'col');
        $layout->removeRow('dod', 'col');

        //$layout->removeRow('resident', 'col');

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
        $form = \App\Form\Contact::create('clientSelect')->setType(\App\Db\Contact::TYPE_CLIENT)->setModel($contact);
        $form->removeField('notes');
        $list = \App\Db\Contact::getSelectList(\App\Db\Contact::TYPE_CLIENT);
        $this->appendField(Field\DialogSelect::createDialogSelect('clientId[]', $list, $form, 'Create Client'))
            ->addCss('tk-multiselect1')
            ->setTabGroup($tab)->setLabel('Submitting Client')
            ->setNotes('This is the contact that will be invoiced.');

        $js = <<<JS
jQuery(function ($) {
  
  /*
  // Create an onHover info box for contacts that have been selected
  $('.select2-selection__choice').each(function () {
    
    $(this).attr({
      "data-container": "body",
      "data-toggle": "popover",
      "data-placement": "right",
      "data-content": "This is a test<br> Hope it works!!!!"
    });
    console.log(this);
    
  }).popover({ trigger: "hover" , html: true, animation:false})
    .on("mouseenter", function () {
      var _this = this;
      $(this).popover("show");
      $(".popover").on("mouseleave", function () {
          $(_this).popover('hide');
      });
    })
    .on("mouseleave", function () {
      var _this = this;
      setTimeout(function () {
          if (!$(".popover:hover").length) {
              $(_this).popover("hide");
          }
      }, 300);
    });
  */
  
  $('select.tk-multiselect1').select2({
    placeholder: 'Select Contact',
    allowClear: false,
    maximumSelectionLength: 1,
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
    } else {
      $('#path_case-accountStatus').attr(d, d);
      $('#path_case-cost').attr(d, d);
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

        $this->appendField(new Money('cost'))->addCss('money')->setLabel('Billable Amount')->setTabGroup($tab)
            ->setNotes('');

        $this->appendField(new Field\Checkbox('submissionReceived'))->setTabGroup($tab)
            ->setNotes('Has the submission form been received?');

        $this->appendField(new Field\Checkbox('afterHours'))->setTabGroup($tab)
            ->setNotes('Was this case worked after normal open hours?');


        if ($this->getPathCase()->getId()) {
            $list = \App\Db\PathCase::getStatusList($this->getPathCase()->getStatus());
            $this->appendField(\Bs\Form\Field\StatusSelect::createSelect('status', $list)->prependOption('-- Status --', ''))
                ->setRequired()->setTabGroup($tab)
                ->setNotes('Set the status. Use the checkbox to disable notification emails.');
        }

        // TODO: would be nice to be able to add fieldsets to a tabgroup
        //->setFieldset($fieldset);
        //$tab = 'Animal';
        $fieldset = 'Animal';

        $contact = new \App\Db\Contact();
        $form = \App\Form\Contact::create('ownerSelect')->setType(\App\Db\Contact::TYPE_OWNER)->setModel($contact);
        $form->removeField('accountCode');
        $form->removeField('nameCompany');
        $form->removeField('notes');
        $list = \App\Db\Contact::getSelectList(\App\Db\Contact::TYPE_OWNER);
        $this->appendField(Field\DialogSelect::createDialogSelect('ownerId[]', $list, $form, 'Create Owner'))
            ->addCss('tk-multiselect1')
            ->setTabGroup($tab)->setLabel('Owner Name')
            ->setNotes('This is the Client Record of the animal owner.');

        $this->appendField(new Field\Input('animalName'))->setLabel('Animal Name/ID')->setTabGroup($tab);
        $this->appendField(new Field\Input('patientNumber'))->setTabGroup($tab);
        $this->appendField(new Field\Input('microchip'))->setTabGroup($tab);

        // \App\Db\PathCase::getSpeciesList($this->getPathCase()->getSpecies())
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

        $this->appendField(new Field\Input('weight'))->setTabGroup($tab);
        $this->appendField(new Field\Input('colour'))->setTabGroup($tab);
        //$this->appendField(new Field\Input('origin'))->setTabGroup($tab);


        $this->appendField(new \App\Form\Field\Age('age'))->setTabGroup($tab);

        // TODO: we need to validate the dob is < dod at some point
        $this->appendField(new Field\Input('dob'))->setTabGroup($tab)->addCss('date')
            ->setAttr('placeholder', 'dd/mm/yyyy');
        $this->appendField(new Field\Input('dod'))->setTabGroup($tab)->addCss('date')
            ->setAttr('placeholder', 'dd/mm/yyyy');
        // END Animal


        $list  = $this->getConfig()->getUserMapper()->findFiltered(
            array('institutionId'=> $this->getPathCase()->getInstitutionId(), 'type' => 'staff'),
            Tool::create('nameFirst')
        );
        $this->appendField(Field\Select::createSelect('pathologistId', $list)
            ->prependOption('-- Select --', ''))
            ->setTabGroup($tab)->setLabel('Pathologist');


        $contact = new \App\Db\Contact();
        $form = \App\Form\Contact::create('studentSelect')->setType(\App\Db\Contact::TYPE_STUDENT)->setModel($contact);
        $form->removeField('nameCompany');
        $form->removeField('accountCode');
        $form->removeField('fax');
        $form->removeField('notes');
        $list = \App\Db\Contact::getSelectList(\App\Db\Contact::TYPE_STUDENT);
        $this->appendField(Field\DialogSelect::createDialogSelect('students[]', $list, $form,'Create Student'))
            ->addCss('tk-multiselect2')
            ->setTabGroup($tab)->setLabel('Students')->setNotes('Add any students')
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
            ->setNotes('Tick the checkbox to alert users of these risks when viewing this case.');

        $this->appendField(Field\CheckboxInput::create('issue'))->setTabGroup($tab)
            ->setLabel('Case Issues')
            ->setNotes('Tick the checkbox to alert users of any issues to be aware of when viewing this case.');

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

        $this->appendField(new Field\Textarea('morphologicalDiagnosis'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);
        $this->appendField(new Field\Textarea('grossMorphologicalDiagnosis'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab)
        ->setNotes('This information will not be included in the final report');

        $this->appendField(new Field\Textarea('histopathology'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);
        $this->appendField(new Field\Textarea('ancillaryTesting'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);
        $this->appendField(new Field\Textarea('causeOfDeath'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);
        $this->appendField(new Field\Textarea('comments'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);
        $this->appendField(new Field\Textarea('addendum'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);


        $tab = 'Files';
        /** @var Field\File $fileField */
        $fileField = $this->appendField(Field\File::create('files[]', $this->getPathCase()->getDataPath()))
            ->setTabGroup($tab)->addCss('tk-multiinput')
            ->setAttr('multiple', 'multiple')
            //->setAttr('accept', '.png,.jpg,.jpeg,.gif')
            ->setNotes('Upload any related files. Multiple files can be selected.');


        if ($this->getPathCase()->getId()) {
            $v = json_encode($this->getPathCase()->getFiles()->toArray());
            $fileField->setAttr('data-value', $v);
            $fileField->setAttr('data-prop-path', 'path');
            $fileField->setAttr('data-prop-id', 'id');
        }

//        $this->appendField(new Field\File('files'))
//            ->addCss('')->setAttr('data-path', $mediaPath)->setTabGroup($tab);

        if ($this->getPathCase()->getType() != \App\Db\PathCase::TYPE_BIOPSY) {
            $tab = 'After Care';
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

        $this->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->appendField(new Event\Link('cancel', $this->getBackUrl()));

    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function execute($request = null)
    {
        if ($this->getRequest()->has('action')) return;        // ignore column requests
        $this->getForm()->getField('clientId')->getDialog()->execute($request);
        $this->getForm()->getField('ownerId')->getDialog()->execute($request);

        $this->load(\App\Db\PathCaseMap::create()->unmapForm($this->getPathCase()));
        parent::execute($request);
    }

    /**
     * @param \Tk\Request $request
     */
    public function doDelete(\Tk\Request $request)
    {
        $fileId = $request->get('del');
        try {
            /** @var \App\Db\File $file */
            $file = \App\Db\FileMap::create()->find($fileId);
            if ($file) $file->delete();
        } catch (\Exception $e) {
            \Tk\ResponseJson::createJson(array('status' => 'err', 'msg' => $e->getMessage()), 500)->send();
            exit();
        }
        \Tk\ResponseJson::createJson(array('status' => 'ok'))->send();
        exit();
    }

    /**
     * @param Form $form
     * @param Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        // Load the object with form data
        $vals = $form->getValues();

        if (!empty($vals['clientId']) && is_array($vals['clientId']))
            $vals['clientId'] = current($vals['clientId']);
        else
            $vals['clientId'] = 0;

        if (!empty($vals['ownerId']) && is_array($vals['ownerId']))
            $vals['ownerId'] = current($vals['ownerId']);
        else
            $vals['ownerId'] = 0;

        \App\Db\PathCaseMap::create()->mapForm($vals, $this->getPathCase());
        // Do Custom Validations
        /** @var \Tk\Form\Field\File $fileField */
        $fileField = $form->getField('files');

        $form->addFieldErrors($this->getPathCase()->validate());
        if ($form->hasErrors()) {
            if (!array_key_exists('files', $form->getAllErrors())) {
                $form->addFieldError('files', 'Please re-select any files if required.');
            }
            return;
        }

        $isNew = (bool)$this->getPathCase()->getId();
        $this->getPathCase()->save();


        /** @var \Tk\Form\Field\File $fileField */
        $fileField = $form->getField('files');
        if ($fileField->hasFile()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
            foreach ($fileField->getUploadedFiles() as $file) {
                if (!\App\Config::getInstance()->validateFile($file->getClientOriginalName())) {
                    \Tk\Alert::addWarning('Illegal file type: ' . $file->getClientOriginalName());
                    continue;
                }
                try {
                    $filePath = $this->getConfig()->getDataPath() . $this->getPathCase()->getDataPath() . '/' . $file->getClientOriginalName();
                    if (!is_dir(dirname($filePath))) {
                        mkdir(dirname($filePath), $this->getConfig()->getDirMask(), true);
                    }
                    $file->move(dirname($filePath), basename($filePath));
                    $oFile = \App\Db\FileMap::create()->findFiltered(array('model' => $this->getPathCase(), 'path' => $this->getPathCase()->getDataPath() . '/' . $file->getClientOriginalName()))->current();
                    if (!$oFile) {
                        $oFile = \App\Db\File::create($this->getPathCase(), $this->getPathCase()->getDataPath() . '/' . $file->getClientOriginalName(), $this->getConfig()->getDataPath() );
                    }
                    //$oFile->path = $this->report->getDataPath() . '/' . $file->getClientOriginalName();
                    $oFile->save();
                } catch (\Exception $e) {
                    \Tk\Log::error($e->__toString());
                    \Tk\Alert::addWarning('Error Uploading file: ' . $file->getClientOriginalName());
                }
            }
        }


        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('pathCaseId', $this->getPathCase()->getId()));
        }
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