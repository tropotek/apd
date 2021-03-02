<?php
namespace App\Ui\Dialog;

use App\Db\Contact;
use App\Db\PathCase;
use Tk\Form;
use Tk\Form\Event\Submit;
use Tk\Form\Field\CheckboxGroup;
use Tk\Form\Field\Input;
use Tk\Form\Field\Textarea;
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
        if ($client && $client->getEmail()) {
            $list[$client->getNameFirst() . ' (' . $client->getEmail() . ')'] = $client->getEmail();
            $selected = $client->getEmail();
        }

        $user = $this->getPathCase()->getUser();
        if ($user && $user->getEmail())
            $list[$user->getName() . ' (' . $user->getEmail() . ')'] = $user->getEmail();

        $owner = $this->getPathCase()->getOwner();
        if ($owner && $owner->getEmail())
            $list[$owner->getNameFirst() . ' (' . $owner->getEmail() . ')'] = $owner->getEmail();

        $path = $this->getPathCase()->getPathologist();
        if ($path && $path->getEmail())
            $list[$path->getName() . ' (' . $path->getEmail() . ')'] = $path->getEmail();

        /** @var Contact $student */
        foreach ($this->getPathCase()->getStudentList() as $student) {
            $list[$student->getNameFirst() . ' (' . $student->getEmail() . ')'] = $student->getEmail();
        }

        /*
        if ($this->getPathCase()->getStudentEmail())
            $list[$this->getPathCase()->getStudent() . ' (' . $this->getPathCase()->getStudentEmail() . ')'] = $this->getPathCase()->getStudentEmail();
        */

        $form->appendField(CheckboxGroup::createSelect('to', $list))->setValue(array($selected));
        $form->appendField(Input::create('toText'))->setLabel('To')->setNotes('Other emails to send the report to separate by comma, space, semi-colin.');

        $form->appendField(Textarea::create('message'))
            ->addCss('mce-min')->setLabel('Report Email Message')->setAttr('data-elfinder-path', $this->getConfig()->getInstitution()->getDataPath().'/media')
            ->setValue($this->getConfig()->getInstitution()->getData()->get(\App\Controller\Institution\Edit::INSTITUTION_REPORT_TEMPLATE));

        $form->appendField(new Submit('save', array($this, 'doSubmit')))->setLabel('Send');
        $form->appendField(new Form\Event\Link('cancel', $this->getBackUrl()));


//        $form->getField('save')
//            ->appendCallback(function (\Bs\FormIface $form, \Tk\Form\Event\Iface $event) {
//                $res = \Tk\ResponseJson::createJson($form->getModel());
//                vd('------');
//                if ($form->hasErrors()) {
//                    vd($form->getAllErrors());
//                    $errors = $form->getAllErrors();
//                    $res = \Tk\ResponseJson::createJson($errors, \Tk\Response::HTTP_INTERNAL_SERVER_ERROR);
//                }
//                \Tk\Alert::clear();
//                $res->send();
//                exit;
//            });


        $form->initForm();
        //$form->execute($this->getConfig()->getRequest());

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
        $message->setFrom($this->getConfig()->getInstitution()->getEmail());
        //$s = $this->pathCase->getPathologyId() . ': ' . $this->pathCase->getName();
        $s = $this->pathCase->getPathologyId();
        $message->setSubject($s);

        // Attach PDF
        $pdf = \App\Ui\CaseReportPdf::createReport($this->pathCase);
        $filename = $this->pathCase->getPathologyId() . '_' . $this->pathCase->getPatientNumber().'.pdf';
        $pdfString = $pdf->getPdfAttachment($filename);
        $message->addStringAttachment($pdfString, $filename);

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
                vd($e->getMessage());
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
  //$('.modal-body form#email-pdf').on('init', '.modal-dialog', init).each(init);
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