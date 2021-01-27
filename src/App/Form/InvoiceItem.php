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

        $this->appendField(new Field\Input('description'));
        //$this->appendField(new Field\Input('code'));
        $this->appendField(new Field\Input('qty'));
        $this->appendField(new Money('price'))->addCss('money');

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
        // Load the object with form data
        \App\Db\InvoiceItemMap::create()->mapForm($form->getValues(), $this->getInvoiceItem());

        // Do Custom Validations

        $form->addFieldErrors($this->getInvoiceItem()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getInvoiceItem()->getId();
        $this->getInvoiceItem()->save();

        // Do Custom data saving

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