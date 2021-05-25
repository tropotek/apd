<?php
namespace App\Form;

use App\Db\AnimalTypeMap;
use App\Db\ContactMap;
use App\Db\Notice;
use App\Db\PathCaseMap;
use App\Db\Permission;
use App\Db\StorageMap;
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


    public function __construct($formId = '')
    {
        parent::__construct($formId);

        if ($this->getConfig()->getRequest()->has('del')) {
            $this->doDelete($this->getConfig()->getRequest());
        }
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
        $this->readonly = ($this->getPathCase()->getStatus() == \App\Db\PathCase::STATUS_COMPLETED);
        if ($this->getAuthUser()->hasPermission(Permission::CASE_FULL_EDIT)) {
            Alert::addInfo('This case has been marked COMPLETED! You have permission to modify completed cases.');
        }

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

        $layout->addRow('bioSamples', 'col2');
        $layout->removeRow('bioNotes', 'col');

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
    minimumInputLength: 0
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

//        $this->appendField(new Money('cost'))->addCss('money')->setLabel('Billable Amount')->setTabGroup($tab)
//            ->setNotes('');

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

        // TODO: would be nice to be able to add fieldsets to a tabgroup
        //->setFieldset($fieldset);
        //$tab = 'Animal';
        $fieldset = 'Animal';

        $contact = new \App\Db\Contact();
        $contact->setType(\App\Db\Contact::TYPE_OWNER);
        $form = \App\Form\Contact::create('ownerSelect')->setType(\App\Db\Contact::TYPE_OWNER)->setModel($contact);
        $form->removeField('accountCode');
        $form->removeField('nameCompany');
        $form->removeField('notes');


        $list = \App\Db\Contact::getSelectList(\App\Db\Contact::TYPE_OWNER);
        $this->appendField(Field\DialogSelect::createDialogSelect('ownerId[]', $list, $form, 'Create Owner'))
            ->addCss('tk-multiselect tk-multiselect1')
            ->setTabGroup($tab)->setLabel('Owner Name')
            ->setNotes('Start typing to find an owner, if not found use the + icon to create a new owner.<br/>The Client Record of the animal owner.');

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
        $contact->setType(\App\Db\Contact::TYPE_STUDENT);
        $form = \App\Form\Contact::create('studentSelect')->setType(\App\Db\Contact::TYPE_STUDENT)->setModel($contact);
        $form->removeField('nameCompany');
        $form->removeField('accountCode');
        $form->removeField('fax');
        $form->removeField('notes');

        $list = \App\Db\Contact::getSelectList(\App\Db\Contact::TYPE_STUDENT);
        $this->appendField(Field\DialogSelect::createDialogSelect('students[]', $list, $form,'Create Student'))
            ->addCss('tk-multiselect tk-multiselect2')
            ->setTabGroup($tab)->setLabel('Students')->setNotes('Start typing to find a student, if not found use the + icon to create a new student.')
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

        $this->appendField(new Field\Textarea('addendum'))
            ->addCss($mce)->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);

        // Setup auto-save on the MCE instances
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
                //console.log(data);
              }).fail(function() {
                  console.error('error')
              }).always(function() {
                //setTimeout(function(){ el.removeClass('saving'); }, 3000);
                el.removeClass('saving');
              });
            }
            //console.log(ed.getContent());
          });
          // console.log(el.attr('id'));
          // console.log(el.val());
          // console.log(ed.getContent());
        });
  };
  $('form').on('change', document, init).each(init);
});
JS;
        $this->getRenderer()->getTemplate()->appendJs($js);




        $tab = 'Files';
        $maxFiles = 10;
        /** @var Field\File $fileField */
        $fileField = $this->appendField(Field\File::create('files[]', $this->getPathCase()->getDataPath()))
            ->setTabGroup($tab)->addCss('tk-multiinput')
            ->setAttr('multiple', 'multiple')
            //->setAttr('accept', '.png,.jpg,.jpeg,.gif')
            ->setNotes('Upload any related files. A max. of '.$maxFiles.' files can be selected and uploaded per form submission.');

        if ($this->getPathCase()->getId()) {
            $files = $this->getPathCase()->getFiles()->toArray();
            usort($files, function ($a, $b) {
                return $a->getLabel() <=> $b->getLabel();
                //return strcmp($a->getLabel(), $b->getLabel());
            });
            $v = json_encode($files);
            $fileField->setAttr('data-value', $v);
            $fileField->setAttr('data-prop-path', 'path');
            $fileField->setAttr('data-prop-id', 'id');
            $fileField->setAttr('data-max-files', $maxFiles);
        }

        // Enable select2 multi select for student field
        $js = <<<JS
jQuery(function ($) {
    $('.tk-multiinput').each(function () {
      var input = $(this);
      $(this.form).on('submit', function() {
        var max = parseInt(input.data('maxFiles'));
        if (parseInt(input.get(0).files.length) > max){
           alert("You can only upload a maximum of "+max+" files per form submission");
           return false;
        }
      });      
    });
      
});
JS;
        $this->getRenderer()->getTemplate()->appendJs($js);

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
  
  	var init = function() {
  	  $(this).parent().find('.select2-selection__choice').each(function() {
        $(this).on('mouseenter', function (e) {
          $('.tk-contact').remove();
          
          var el = $(this);
          var data = $(this).data()['data'];
          var name = data['text'].split('(')[0];
          var email = '';
          if (data['text'].indexOf('(') > -1 && data['text'].split('(')[1].replace(')', '')) {
            email = data['text'].split('(')[1].replace(')', '');
          }
          //console.log(data['text']); // TODO: get the name and email from here for instant data
          var off = el.closest('.form-group').offset();
          // var x = e.pageX - this.offsetLeft;
          // var y = e.pageY - this.offsetTop;
          var x = e.pageX - off.left;
          var y = e.pageY - off.top;
            
          $.get(document.location, {
              'gc': data['id'],
              crumb_ignore: 'crumb_ignore',
              nolog: 'nolog'
            }, function (gcData) {
            var contact = gcData.contact;
            var html = '<div class="tk-contact"><div class="control">' +
              '<a href="javascript:;" class="tk-close"><i class="fa fa-times"></i></a>' +
              '<a href="contactEdit.html?contactId='+contact.id+'&crumb_ignore=&nolog=" target="_blank" class="tk-edit"><i class="fa fa-pencil"></i></a>' +
              '<a href="javascript:;" class="tk-pin"><i class="fa fa-thumb-tack"></i></a>' +
              '</div>' +
              '<h3 class="name"><a href="contactEdit.html?contactId='+contact.id+'&crumb_ignore=&nolog=" target="_blank">' + name + '</a></h3>';
            // if (email)
            //   html += '<p><a href="mailto:'+email+'" class="email">' + email + '</a></p>';
            //html += '<hr/>';
            html += '<div class="details"><b>Contact:</b><br/>';
            html += '<ul>';
            if (contact.email)
              html += '<li><i class="fa fa-envelope-o"></i> <a href="mailto:'+contact.email+'">'+contact.email+'</a></li>';
            if (contact.nameCompany)
              html += '<li><i class="fa fa-building"></i> <a href="tel:'+contact.nameCompany+'">'+contact.nameCompany+'</a></li>';
            if (contact.phone)
              html += '<li><i class="fa fa-phone"></i> <a href="tel:'+contact.phone+'">'+contact.phone+'</a></li>';
            if (contact.fax)
              html += '<li><i class="fa fa-fax"></i> <a href="tel:'+contact.fax+'">'+contact.fax+'</a></li>';
            if (contact.address)
              html += '<li><i class="fa fa-building"></i> <a href="tel:'+contact.address+'">'+contact.address+'</a></li>';
            html += '</ul></div>';
            html += '';
            
            if (contact.notes && contact.notes !== '***')
              html += '<p>'+contact.notes+'</p>';
            
            html += '</div>';
            
            var panel = $(html);
            panel.css('left', x);
            panel.css('top', y);
            el.closest('.form-group').append(panel);
            
            panel.on('mouseout', function () {
              if ($('.tk-contact:hover').length || $('.tk-contact .pinned').length) return;
              $('.tk-contact').fadeOut(function () {
                $(this).remove();
              });
            });
            panel.find('a.tk-close').on('click', function () {
              $('.tk-contact').fadeOut(function () {
                $(this).remove();
              });
            });
            panel.find('a.tk-pin').on('click', function () {
              $(this).toggleClass('pinned');
            });
          }, 'json');
          
        });
        
        $(this).on('mouseout', function () {
          if ($('.tk-contact:hover').length || $('.tk-contact .pinned').length) return;
          $('.tk-contact').fadeOut(function () {
            $(this).remove();
          });
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

/*
.tk-contact ul li::before {
    content: "\\f005";
    font-family: "Font Awesome 5 Free";
}
*/

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
        $this->getForm()->getField('ownerId')->getDialog()->execute($request);
        $this->getForm()->getField('students')->getDialog()->execute($request);

        $this->load(\App\Db\PathCaseMap::create()->unmapForm($this->getPathCase()));
        parent::execute($request);
    }
    /**
     * @param \Tk\Request $request
     */
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

        if (!isset($vals['billable']) || !$vals['billable']) {
            unset($vals['accountStatus']);
        }
        // Stop any javascript accadently sending data back that gets updated.
        if ($this->isReadonly()) {
            $newVals = [];
            foreach ($vals as $k => $v) {
                if (!in_array($k, $this->exceptions)) continue;
                $newVals[$k] = $v;
            }
            $vals = $newVals;
        }

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

        $isNew = ($this->getPathCase()->getId() == 0);
        $this->getPathCase()->setStatusNotify(true);
        $this->getPathCase()->setStatusMessage('New pathology case created.');
        $this->getPathCase()->save();
        if ($isNew) {
            Notice::create($this->getPathCase());
        }

        // Save the student field
        if (!empty($vals['students']) && is_array($vals['students'])) {
            foreach ($vals['students'] as $id) {
                $contact = ContactMap::create()->find($id);
                if ($contact) {
                    PathCaseMap::create()->addContact($this->getPathCase()->getId(), $contact->getId());
                }
            }
        }


        /** @var \Tk\Form\Field\File $fileField */
        $fileField = $form->getField('files');
        if ($fileField->hasFile()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
            foreach ($fileField->getUploadedFiles() as $i => $file) {
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

        // TODO: create a new file field and move this there using AJAX calls
        $activeList = $this->getRequest()->get('fActive', []);  // Get the active status
        $labelList = $this->getRequest()->get('fLabel', []);    // Get the active status
        $fileList = \App\Db\FileMap::create()->findFiltered(['model' => $this->getPathCase()]);
        foreach ($fileList as $i => $file) {
            if (in_array($i, $activeList)) {
                $file->setActive(true);
            }
            if (isset($labelList[$i]))
                $file->setLabel($labelList[$i]);
            $file->save();
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