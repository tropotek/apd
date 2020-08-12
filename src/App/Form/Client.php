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
        $layout = $this->getForm()->getRenderer()->getLayout();

        $layout->removeRow('billingEmail', 'col');
        $layout->removeRow('fax', 'col');
        $layout->removeRow('postcode', 'col');
        $layout->removeRow('country', 'col');
        $layout->removeRow('bPostcode', 'col');
        $layout->removeRow('bCountry', 'col');

        $tab = 'Details';

        $this->appendField(new Field\Select(
            'userId',
            $this->getConfig()->getUserMapper()->findFiltered(array('institutionId' => $this->getClient()->getInstitutionId()))
        ))->prependOption('-- None --', '')->setTabGroup($tab);
        $this->appendField(new Field\Input('accountCode'))->setTabGroup($tab)->setNotes('Add an internal account code if available.');

//        $this->appendField(new Field\Input('uid'))->setTabGroup($tab);
        $this->appendField(new Field\Input('name'))->setTabGroup($tab);
        $this->appendField(new Field\Input('email'))->setTabGroup($tab);
        $this->appendField(new Field\Input('billingEmail'))->setTabGroup($tab);
        $this->appendField(new Field\Input('phone'))->setTabGroup($tab);
        $this->appendField(new Field\Input('fax'))->setTabGroup($tab);
        $this->appendField(new Field\Textarea('notes'))->setTabGroup($tab);

        //$this->appendField(new Field\Select('addressId', array()))->prependOption('-- Select --', '');

        $tab = 'Address';
        $this->appendField(new Field\Input('street'))->setTabGroup($tab);
        $this->appendField(new Field\Input('city'))->setTabGroup($tab);
        $this->appendField(new Field\Input('postcode'))->setTabGroup($tab);
        $this->appendField(new Field\Input('state'))->setTabGroup($tab);
        $this->appendField(new Field\Input('country'))->setTabGroup($tab);

        $tab = 'Billing';
        $this->appendField(new Field\Checkbox('useAddress'))->setLabel('')->setCheckboxLabel('Use Address as Billing Address')->setTabGroup($tab);
        $this->appendField(new Field\Input('bStreet'))->setLabel('Billing Street')->setTabGroup($tab);
        $this->appendField(new Field\Input('bCity'))->setLabel('Billing City')->setTabGroup($tab);
        $this->appendField(new Field\Input('bPostcode'))->setLabel('Billing Postcode')->setTabGroup($tab);
        $this->appendField(new Field\Input('bState'))->setLabel('Billing State')->setTabGroup($tab);
        $this->appendField(new Field\Input('bCountry'))->setLabel('Billing Country')->setTabGroup($tab);

        $this->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->appendField(new Event\Link('cancel', $this->getBackUrl()));

        $js = <<<JS
jQuery(function($) {
  // Force the use billing address checkbox off
    $('#clientBilling input').not('#client-useAddress').on('change', function () {
        if ($('#clientBilling input').not('#client-useAddress').filter(function () { return $.trim($(this).val()).length === 0}).length !== 0) {
          $('#clientBilling #client-useAddress').prop('checked', false);
        }
    });
    $('#client-useAddress').on('change', function () {
      if ($(this).prop('checked')) {
        $('#clientBilling input').not('#client-useAddress').attr('disabled', 'disabled');
      } else {
        $('#clientBilling input').not('#client-useAddress').removeAttr('disabled');
      }
    });
});
JS;
        $this->getRenderer()->getTemplate()->appendJs($js);


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