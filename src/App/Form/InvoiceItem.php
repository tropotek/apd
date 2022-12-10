<?php
namespace App\Form;

use App\Form\Field\Money;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new InvoiceItem::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2021-01-27
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class InvoiceItem extends \Bs\FormIface
{
    /**
     * @var null|\App\Ui\Dialog\InvoiceItem
     */
    protected $dialog = null;


    /**
     * @throws \Exception
     */
    public function init()
    {
        $layout = $this->getRenderer()->getLayout();

        //$layout->removeRow('description', 'cell');
        $layout->removeRow('code', 'cell');
        $layout->removeRow('qty', 'cell');
        $layout->removeRow('price', 'cell');

        $this->appendField(\App\Form\Field\Autocomplete::createAutocomplete('description', \Tk\Uri::create('/ajax/product/findByName.html')))
            ->setLabel('Description/Product')->setAttr('data-value-type', 'label')
            ->setAttr('placeholder', 'Description/Product')->setRequired();
        //$this->appendField(new Field\Input('description'));
        $this->appendField(new Field\Hidden('code'));
        $this->appendField(new Field\Input('qty'));
        $this->appendField(new Money('price'))->addCss('money');

        $this->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->appendField(new Event\Link('cancel', $this->getBackUrl()));


        $js = <<<JS
jQuery(function ($) {
  
  function init() {
    var form = $(this);
    $('input.autocomplete', form).each(function () {
      $(this).on('autocompleteselect', function( event, ui ) {
        $('[name="code"]', form).val(ui.item.code);
        $('[name="price"]', form).val(ui.item.price);
      });
    });
  }
  
  $('form').on('init', document, init).each(init);
  
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
        $this->load(\App\Db\InvoiceItemMap::create()->unmapForm($this->getInvoiceItem()));
        parent::execute($request);
    }

    /**
     * @param Form $form
     * @param Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        $values = $form->getValues();
        // Load the object with form data
        \App\Db\InvoiceItemMap::create()->mapForm($values, $this->getInvoiceItem());

        if (!$this->getInvoiceItem()->getDescription() && isset($values['ac-description'])) {
            $this->getInvoiceItem()->setDescription($values['ac-description']);
        }

        // Do Custom Validations
        $form->addFieldErrors($this->getInvoiceItem()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getInvoiceItem()->getId();
        $this->getInvoiceItem()->save();


        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('invoiceItemId', $this->getInvoiceItem()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\InvoiceItem
     */
    public function getInvoiceItem()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\InvoiceItem $invoiceItem
     * @return $this
     */
    public function setInvoiceItem($invoiceItem)
    {
        return $this->setModel($invoiceItem);
    }

    /**
     * @return \App\Ui\Dialog\InvoiceItem|null
     */
    public function getDialog(): ?\App\Ui\Dialog\InvoiceItem
    {
        return $this->dialog;
    }

    /**
     * @param \App\Ui\Dialog\InvoiceItem|null $dialog
     * @return InvoiceItem
     */
    public function setDialog(?\App\Ui\Dialog\InvoiceItem $dialog): InvoiceItem
    {
        $this->dialog = $dialog;
        return $this;
    }

}