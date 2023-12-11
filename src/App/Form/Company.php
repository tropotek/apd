<?php
namespace App\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Company::create();
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
class Company extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        $layout = $this->getRenderer()->getLayout();

        $layout->removeRow('email', 'col');

        $layout->removeRow('phone', 'col');
        $layout->removeRow('fax', 'col');

        $layout->removeRow('city', 'col');
        $layout->removeRow('state', 'col');

        $layout->removeRow('country', 'col');

        $this->appendField(new Field\Input('name'));
        $this->appendField(new Field\Input('email'));
        $this->appendField(new Field\Input('phone'));
        $this->appendField(new Field\Input('fax'));
        $this->appendField(new Field\Input('street'));
        $this->appendField(new Field\Input('city'));
        $this->appendField(new Field\Input('state'));
        $this->appendField(new Field\Input('postcode'));
        $this->appendField(new Field\Input('country'));
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
        $this->load(\App\Db\CompanyMap::create()->unmapForm($this->getCompany()));
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
        \App\Db\CompanyMap::create()->mapForm($form->getValues(), $this->getCompany());

        // Do Custom Validations

        $form->addFieldErrors($this->getCompany()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getCompany()->getId();
        $this->getCompany()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('companyId', $this->getCompany()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\Company
     */
    public function getCompany()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\Company $company
     * @return $this
     */
    public function setCompany($company)
    {
        return $this->setModel($company);
    }

}