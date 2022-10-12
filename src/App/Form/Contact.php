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
class Contact extends \Bs\FormIface
{
    /**
     * @var string
     */
    protected $type = '';

    /**
     * @throws \Exception
     */
    public function init()
    {
        if ($this->getConfig()->getRequest()->query->has('type') && !$this->getType()) {
            $this->setType($this->getConfig()->getRequest()->query->has('type'));
        }

        $layout = $this->getForm()->getRenderer()->getLayout();

        $layout->removeRow('nameCompany', 'col');
        $layout->removeRow('accountCode', 'col');
        $layout->removeRow('fax', 'col');
        $layout->removeRow('postcode', 'col');
        $layout->removeRow('country', 'col');
        $layout->removeRow('nameLast', 'col');
        $layout->removeRow('emailCc', 'col');

        $tab = 'Details';
        //if (!$this->getConfig()->getRequest()->query->has('type') && !$this->isTypeHidden()) {
        if ($this->getType() == '') {
            $list = \App\Db\Contact::getTypeList($this->getClient()->getType());
            $this->appendField(new Field\Select('type', $list))
                ->setRequired()->prependOption('-- Contact Type --', '')->setTabGroup($tab);
        } else {
            $this->appendField(new Field\Hidden('type'));
        }

        $this->appendField(new Field\Input('nameCompany'))->setLabel('Company Name')->setTabGroup($tab);
        $this->appendField(new Field\Input('accountCode'))->setTabGroup($tab)
            ->setNotes('The clients billing account code if available.');
        $this->appendField(new Field\Input('nameFirst'))->setLabel('Contact Firstname')->setTabGroup($tab);
        $this->appendField(new Field\Input('nameLast'))->setLabel('Contact Surname')->setTabGroup($tab);
        $this->appendField(new Field\Input('email'))->setTabGroup($tab);
        $this->appendField(new Field\Input('emailCc'))->setTabGroup($tab)
            ->setNotes('Add multiple emails seperated by a comma or semicolon.');
        $this->appendField(new Field\Input('phone'))->setTabGroup($tab);
        $this->appendField(new Field\Input('fax'))->setTabGroup($tab);


        //$tab = 'Address';
        $this->appendField(new Field\GmapAddress('address'))->setTabGroup($tab)
            ->setAttr('data-manual-btn', 'true')
            ->setNotes('Start typing the address and select from the dropdown or click the edit icon to enter the address manually');
        $this->appendField(new Field\Input('street'))->setTabGroup($tab);
        $this->appendField(new Field\Input('city'))->setTabGroup($tab);
        $this->appendField(new Field\Input('postcode'))->setTabGroup($tab);
        $this->appendField(new Field\Input('state'))->setTabGroup($tab);
        $this->appendField(new Field\Input('country'))->setTabGroup($tab);

        $this->appendField(new Field\Textarea('notes'))->setTabGroup($tab);

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
        $arr = \App\Db\ContactMap::create()->unmapForm($this->getClient());
        $arr['address'] = $this->getClient()->getAddress();
        $this->load($arr);
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
        \App\Db\ContactMap::create()->mapForm($form->getValues(), $this->getClient());
        if ($this->getType() && !$this->getClient()->getType())
            $this->getClient()->setType($this->getType());

        // Do Custom Validations
        $form->addFieldErrors($this->getClient()->validate());
        if ($form->hasErrors()) {
            return;
        }
        vd($this->getClient());
        $isNew = (bool)$this->getClient()->getId();
        $this->getClient()->save();

        // For the dialog form
        $this->getClient()->name = $this->getClient()->getName();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('contactId', $this->getClient()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\Contact
     */
    public function getClient()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\Contact $client
     * @return $this
     */
    public function setClient($client)
    {
        return $this->setModel($client);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Contact
     */
    public function setType(string $type): Contact
    {
        $this->type = $type;
        return $this;
    }

}