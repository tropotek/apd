<?php
namespace App\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Address::create();
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
class Address extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {

        $this->appendField(new Field\Select('institutionId', array()))->prependOption('-- Select --', '');
        $this->appendField(new Field\Input('number'));
        $this->appendField(new Field\Input('street'));
        $this->appendField(new Field\Input('city'));
        $this->appendField(new Field\Input('country'));
        $this->appendField(new Field\Input('state'));
        $this->appendField(new Field\Input('postcode'));
        $this->appendField(new Field\Input('address'));
        $this->appendField(new Field\Input('mapZoom'));
        $this->appendField(new Field\Input('mapLng'));
        $this->appendField(new Field\Input('mapLat'));

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
        $this->load(\App\Db\AddressMap::create()->unmapForm($this->getAddress()));
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
        \App\Db\AddressMap::create()->mapForm($form->getValues(), $this->getAddress());

        // Do Custom Validations

        $form->addFieldErrors($this->getAddress()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getAddress()->getId();
        $this->getAddress()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('addressId', $this->getAddress()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\Address
     */
    public function getAddress()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\Address $address
     * @return $this
     */
    public function setAddress($address)
    {
        return $this->setModel($address);
    }

}