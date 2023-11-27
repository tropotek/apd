<?php
namespace App\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new CompanyContact::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2023-11-27
 * @link http://tropotek.com.au/
 * @license Copyright 2023 Tropotek
 */
class CompanyContact extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        $layout = $this->getRenderer()->getLayout();

        $layout->removeRow('email', 'col');
        $layout->removeRow('phone', 'col');

        $this->appendField(new Field\Input('name'));
        $this->appendField(new Field\Input('email'));
        $this->appendField(new Field\Input('phone'));

        $this->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->appendField(new Event\Link('cancel', $this->getBackUrl()));
vd();
    }

    /**
     * @param \Tk\Request $request
     * @throws \Exception
     */
    public function execute($request = null)
    {
        $this->load(\App\Db\CompanyContactMap::create()->unmapForm($this->getCompanyContact()));
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
        \App\Db\CompanyContactMap::create()->mapForm($form->getValues(), $this->getCompanyContact());

        // Do Custom Validations

        $form->addFieldErrors($this->getCompanyContact()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getCompanyContact()->getId();
        $this->getCompanyContact()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('companyContactId', $this->getCompanyContact()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\CompanyContact
     */
    public function getCompanyContact()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\CompanyContact $companyContact
     * @return $this
     */
    public function setCompanyContact($companyContact)
    {
        return $this->setModel($companyContact);
    }

}