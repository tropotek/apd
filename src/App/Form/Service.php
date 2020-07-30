<?php
namespace App\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Service::create();
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
class Service extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        $this->appendField(new Field\Input('name'));
        //$this->appendField(new Field\Input('price'));
        $this->appendField(new Field\Textarea('comments'));
        $this->appendField(new Field\Textarea('notes'));

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
        $this->load(\App\Db\ServiceMap::create()->unmapForm($this->getService()));
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
        \App\Db\ServiceMap::create()->mapForm($form->getValues(), $this->getService());

        // Do Custom Validations

        $form->addFieldErrors($this->getService()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getService()->getId();
        $this->getService()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('serviceId', $this->getService()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\Service
     */
    public function getService()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\Service $service
     * @return $this
     */
    public function setService($service)
    {
        return $this->setModel($service);
    }

}