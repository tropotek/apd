<?php
namespace App\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Request::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2020-07-30
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Request extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {

        //$this->appendField(new Field\Select('pathCaseId', array()))->prependOption('-- Select --', '');
        //$this->appendField(new Field\Select('cassetteId', array()))->prependOption('-- Select --', '');
        $this->appendField(new Field\Select('serviceId', array()))->prependOption('-- Select --', '');
        $this->appendField(new Field\Select('clientId', array()))->prependOption('-- Select --', '');
        $this->appendField(new \Bs\Form\Field\StatusSelect('status', \App\Db\Request::getStatusList($this->getRequest()->getStatus())))
            ->setRequired()->prependOption('-- Status --', '')
            ->setNotes('Set the status. Use the checkbox to disable notification emails.');
        $this->appendField(new Field\Input('qty'));
        //$this->appendField(new Field\Input('price'));
        $this->appendField(new Field\Textarea('comments'));
        $this->appendField(new Field\Textarea('notes'));

        $this->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->appendField(new Event\Link('cancel', $this->getBackUrl()));

    }

    /**
     * @param \Tk\Request|null $request
     * @throws \Exception
     */
    public function execute($request = null)
    {
        $this->load(\App\Db\RequestMap::create()->unmapForm($this->getRequest()));
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
        \App\Db\RequestMap::create()->mapForm($form->getValues(), $this->getRequest());

        // Do Custom Validations

        $form->addFieldErrors($this->getRequest()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getRequest()->getId();
        $this->getRequest()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('requestId', $this->getRequest()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\Request
     */
    public function getRequest()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\Request $request
     * @return $this
     */
    public function setRequest($request)
    {
        return $this->setModel($request);
    }

}