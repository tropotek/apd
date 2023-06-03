<?php
namespace App\Ui\Dialog;

use App\Db\Contact;
use App\Db\PathCase;
use Tk\Form;
use Tk\Form\Event\Submit;
use Tk\Form\Field\CheckboxGroup;
use Tk\Form\Field\Input;
use Tk\Form\Field\Textarea;
use Tk\Mail\Message;
use Tk\Ui\Dialog\JsonForm;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class EmailReport extends JsonForm
{
    /**
     * @var PathCase|null
     */
    protected $pathCase = null;

    /**
     * EmailReport constructor.
     * @param PathCase $pathCase
     */
    public function __construct(PathCase $pathCase)
    {
        $this->addCss('email-report-dialog');

        $this->pathCase = $pathCase;
        $this->setResetOnHide(false);
        $form = $this->getConfig()->createForm('email-pdf');
        $form->setRenderer($this->getConfig()->createFormRenderer($form));

        $selected = '';
        $list = array();


        $client = $this->getPathCase()->getClient();
        if ($client) {
            if ($client->getEmail()) {
                $list[$client->getNameFirst() . ' (' . $client->getEmail() . ')'] = $client->getEmail();
                $selected = $client->getEmail();
            }
            foreach ($client->getEmailCcList() as $i => $e) {
                $list['Client CC ('.$e.')'] = $e;
            }

        }

        $user = $this->getPathCase()->getUser();
        if ($user && $user->getEmail())
            $list[$user->getName() . ' (' . $user->getEmail() . ')'] = $user->getEmail();

        if (PathCase::useOwnerObject()) {
            $owner = $this->getPathCase()->getOwner();
            if ($owner && $owner->getEmail())
                $list[$owner->getNameFirst() . ' (' . $owner->getEmail() . ')'] = $owner->getEmail();
        }

        $path = $this->getPathCase()->getPathologist();
        if ($path && $path->getEmail())
            $list[$path->getName() . ' (' . $path->getEmail() . ')'] = $path->getEmail();

        /** @var Contact $student */
        foreach ($this->getPathCase()->getStudentList() as $student) {
            $list[$student->getNameFirst() . ' (' . $student->getEmail() . ')'] = $student->getEmail();
        }

        $form->appendField(CheckboxGroup::createSelect('to', $list))->setValue(array($selected));
        $form->appendField(Input::create('toText'))->setLabel('To')->setNotes('Other emails to send the report to separate by comma, space, semi-colin.');

        $form->appendField(Textarea::create('message'))
            ->addCss('mce-min')->setLabel('Report Email Message')->setAttr('data-elfinder-path', $this->getConfig()->getInstitution()->getDataPath().'/media')
            ->setValue($this->getConfig()->getInstitution()->getData()->get(\App\Controller\Institution\Edit::INSTITUTION_REPORT_TEMPLATE));

        $form->appendField(new Submit('save', array($this, 'doSubmit')))->setLabel('Send');
        $form->appendField(new Form\Event\Link('cancel', $this->getBackUrl()));

        $form->initForm();

        parent::__construct($form, 'Email Report');
    }


    /**
     * @param PathCase $pathCase
     * @return static
     */
    public static function createEmailReport(PathCase $pathCase)
    {
        $obj = new static($pathCase);
        return $obj;
    }

    /**
     * @param Form $form
     * @param Form\Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        $values = $form->getValues();
        $list = array();
        if (is_array($values['to']))
            $list = $values['to'];

        $list = array_merge(\Tk\Str::explode(array(',', ':', ';', ' '), $values['toText']), $list);
        $list = array_merge($list, $list);
        $list = \Tk\Str::trimArray($list);
        $list = array_combine($list, $list);

        if (!count($list)) {
            $form->addFieldError('toText', 'No valid emails selected.');
            return;
        }

        // Create message
        $message = $this->getConfig()->createMessage();
//        $message->setFrom(Message::joinEmail($this->getConfig()->getInstitution()->getEmail(),
//            $this->getConfig()->getInstitution()->getName()));
        $message->setReplyTo(Message::joinEmail($this->getConfig()->getInstitution()->getEmail(),
            $this->getConfig()->getInstitution()->getName()));

        $s = $this->pathCase->getPathologyId();
        $message->setSubject($s);

        // Attach PDF
        $pdf = \App\Ui\CaseReportPdf::createReport($this->pathCase);
        $int = '';
        if ($this->pathCase->getReportStatus() == PathCase::REPORT_STATUS_INTERIM)
            $int = '-' . PathCase::REPORT_STATUS_INTERIM;
        $filename = 'AnatomicPathologyReport-' . $this->pathCase->getPathologyId() . '-' . $this->pathCase->getPatientNumber().$int.'.pdf';
        $pdfString = $pdf->getPdfAttachment($filename);
        $message->addStringAttachment($pdfString, $filename);

        // Attach any files attached to the case record
        foreach ($this->pathCase->getSelectedFileList() as $file) {
            $filePath = $this->getConfig()->getDataPath() . $file->getPath();
            if (!is_file($filePath) || $file->getBytes() > $this->getConfig()->get('pathCase.report.maxAttachmentSize', 2000000)) continue;
            $message->addAttachment($filePath, basename($file->getPath()), $file->getMime());
        }

        $message->set('clientName', $this->getPathCase()->getClient()->getName());
        $message->set('pathologyId', $this->pathCase->getPathologyId());
        $message->set('animalName', $this->pathCase->getAnimalName());
        $message->set('animalType', '');
        $message->set('animalType', '');
        $message->set('animalTypeId', '');
        $message->set('clientName', $this->pathCase->getOwnerName());
        $message->set('pathologistName', '');
        if ($this->pathCase->getPathologist()) {
            $message->set('pathologistName', $this->pathCase->getPathologist()->getName());
            //$message->set('clientName', $this->pathCase->getPathologist()->getName());
        }
        if ($this->pathCase->getAnimalType()) {
            $message->set('animalType', $this->pathCase->getAnimalType()->getName());
            $message->set('animalTypeId', $this->pathCase->getAnimalTypeId());
        }
        if ($this->pathCase->getClient()) {
            $message->set('clientName', $this->pathCase->getClient()->getName());
            if (!$this->pathCase->getClient()->getName() && $this->pathCase->getClient()->getNameCompany()) {
                $message->set('clientName', $this->pathCase->getClient()->getNameCompany());
            }
        }
        $message->setContent($values['message']);
        $message->set('sig', '');

        // Email individually to selected email addresses
        foreach ( $list as $to) {
            $message->reset();
            $message->addTo($to);
            try {
                if (!$this->getConfig()->getEmailGateway()->send($message)) {
                    $form->addFieldError('to', 'Failed Sending to: ' . $to);
                }
            } catch (\Exception $e) {
                \Tk\Log::debug($e->getMessage());
                $form->addFieldError('to', 'Failed Sending to: ' . $to);
            }
        }


    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $js = <<<JS
jQuery(function ($) {
  
  function init() {
    var form = $(this);
    var dialog = form.closest('.modal.email-report-dialog');
    
    dialog.on('DialogForm:error', function (e, xhr, errMsg, errHtml) {
      console.log('\App\Ui\Dialog\EmailReport:');
      console.log(arguments);
      alert(errMsg);
    });
    
  }
  $('.email-report-dialog form').on('init', 'body', init).each(init);
});
JS;

        $template->appendJs($js);

        return $template;
    }

    /**
     * @return PathCase|null
     */
    public function getPathCase(): ?PathCase
    {
        return $this->pathCase;
    }

    /**
     * @param PathCase|null $pathCase
     * @return EmailReport
     */
    public function setPathCase(?PathCase $pathCase): EmailReport
    {
        $this->pathCase = $pathCase;
        return $this;
    }

}