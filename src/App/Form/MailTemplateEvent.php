<?php
namespace App\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new MailTemplateEvent::create();
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
class MailTemplateEvent extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {

        $this->appendField(new Field\Input('name'));
        $this->appendField(new Field\Input('event'));
        $this->appendField(new Field\Textarea('description'));
        $this->appendField(new Field\Textarea('emailTags'));

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
        $this->load(\App\Db\MailTemplateEventMap::create()->unmapForm($this->getMailTemplateEvent()));
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
        \App\Db\MailTemplateEventMap::create()->mapForm($form->getValues(), $this->getMailTemplateEvent());

        // Do Custom Validations

        $form->addFieldErrors($this->getMailTemplateEvent()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getMailTemplateEvent()->getId();
        $this->getMailTemplateEvent()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('mailTemplateEventId', $this->getMailTemplateEvent()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\MailTemplateEvent
     */
    public function getMailTemplateEvent()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\MailTemplateEvent $mailTemplateEvent
     * @return $this
     */
    public function setMailTemplateEvent($mailTemplateEvent)
    {
        return $this->setModel($mailTemplateEvent);
    }

}