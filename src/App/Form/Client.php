<?php
namespace App\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Client::create();
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
class Client extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {

        $this->appendField(new Field\Select('institutionId', array()))->prependOption('-- Select --', '');
        $this->appendField(new Field\Select('userId', array()))->prependOption('-- Select --', '');
        $this->appendField(new Field\Input('uid'));
        $this->appendField(new Field\Input('name'));
        $this->appendField(new Field\Input('email'));
        $this->appendField(new Field\Input('billingEmail'));
        $this->appendField(new Field\Input('phone'));
        $this->appendField(new Field\Input('fax'));
        $this->appendField(new Field\Select('addressId', array()))->prependOption('-- Select --', '');
        $this->appendField(new Field\Select('billingAddressId', array()))->prependOption('-- Select --', '');
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
        $this->load(\App\Db\ClientMap::create()->unmapForm($this->getClient()));
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
        \App\Db\ClientMap::create()->mapForm($form->getValues(), $this->getClient());

        // Do Custom Validations

        $form->addFieldErrors($this->getClient()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getClient()->getId();
        $this->getClient()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('clientId', $this->getClient()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\Client
     */
    public function getClient()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\Client $client
     * @return $this
     */
    public function setClient($client)
    {
        return $this->setModel($client);
    }

}