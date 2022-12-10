<?php
namespace App\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Product::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2022-12-10
 * @link http://tropotek.com.au/
 * @license Copyright 2022 Tropotek
 */
class Product extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        $layout = $this->getForm()->getRenderer()->getLayout();
        //$layout->removeRow('serviceId', 'col');
        $layout->removeRow('price', 'col-4');

        $this->appendField(new Field\Input('name'));
        $this->appendField(new Field\Input('price'));
        $this->appendField(new Field\Input('code'));
        //$this->appendField(new Field\Input('description'));

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
        $this->load(\App\Db\ProductMap::create()->unmapForm($this->getProduct()));
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
        \App\Db\ProductMap::create()->mapForm($form->getValues(), $this->getProduct());

        // Do Custom Validations

        $form->addFieldErrors($this->getProduct()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getProduct()->getId();
        $this->getProduct()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('productId', $this->getProduct()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\Product
     */
    public function getProduct()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\Product $product
     * @return $this
     */
    public function setProduct($product)
    {
        return $this->setModel($product);
    }

}