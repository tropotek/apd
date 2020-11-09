<?php
namespace App\Form;

use App\Db\MailTemplateEventMap;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new MailTemplate::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2020-08-17
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class MailTemplate extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {

        $mediaPath = $this->getMailTemplate()->getInstitution()->getDataPath().'/mtpl/media';
        //vd($mediaPath);

        $list = MailTemplateEventMap::create()->findFiltered(array()); //->toArray('id', 'name');
        $this->appendField(new Field\Select('mailTemplateEventId', $list))->prependOption('-- Select --', '');

        $list = \App\Db\MailTemplate::getRecipientSelectList();
        $this->appendField(new Field\Select('recipientType', $list))->prependOption('-- Select --', '');
        $this->appendField(new Field\Checkbox('active'));
        $this->appendField(new Field\Textarea('template'))
            ->addCss('mce')->setAttr('data-elfinder-path', $mediaPath);

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
        $this->load(\App\Db\MailTemplateMap::create()->unmapForm($this->getMailTemplate()));
        parent::execute($request);
    }

    /**
     * @param Form $form
     * @param Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        // Load the object with form data
        \App\Db\MailTemplateMap::create()->mapForm($form->getValues(), $this->getMailTemplate());

        // Do Custom Validations

        $form->addFieldErrors($this->getMailTemplate()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getMailTemplate()->getId();
        $this->getMailTemplate()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('mailTemplateId', $this->getMailTemplate()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\MailTemplate
     */
    public function getMailTemplate()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\MailTemplate $mailTemplate
     * @return $this
     */
    public function setMailTemplate($mailTemplate)
    {
        return $this->setModel($mailTemplate);
    }

}