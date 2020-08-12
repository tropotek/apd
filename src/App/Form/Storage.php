<?php
namespace App\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Storage::create();
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
class Storage extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        $layout = $this->getForm()->getRenderer()->getLayout();
        $layout->removeRow('name', 'col-10');

        $this->appendField(new Field\Input('uid'));
        $this->appendField(new Field\Input('name'));
        $this->appendField(new Field\Textarea('notes'));

        // TODO: Add a map field here
        $this->appendField(new Field\Hidden('mapZoom'));
        $this->appendField(new Field\Hidden('mapLng'));
        $this->appendField(new Field\Hidden('mapLat'));

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
        $this->load(\App\Db\StorageMap::create()->unmapForm($this->getStorage()));
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
        \App\Db\StorageMap::create()->mapForm($form->getValues(), $this->getStorage());

        // Do Custom Validations

        $form->addFieldErrors($this->getStorage()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getStorage()->getId();
        $this->getStorage()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('storageId', $this->getStorage()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\Storage
     */
    public function getStorage()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\Storage $storage
     * @return $this
     */
    public function setStorage($storage)
    {
        return $this->setModel($storage);
    }

}