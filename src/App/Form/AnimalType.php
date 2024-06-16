<?php
namespace App\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new AnimalType::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2021-01-13
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class AnimalType extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {

        //$this->appendField(new Field\Select('parentId', []))->prependOption('-- Select --', '');
        $this->appendField(new Field\Input('name'));
        $this->appendField(new Field\Checkbox('active'));
        $this->appendField(new Field\Textarea('description'));

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
        $this->load(\App\Db\AnimalTypeMap::create()->unmapForm($this->getAnimalType()));
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
        \App\Db\AnimalTypeMap::create()->mapForm($form->getValues(), $this->getAnimalType());

        // Do Custom Validations

        $form->addFieldErrors($this->getAnimalType()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getAnimalType()->getId();
        $this->getAnimalType()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('animalTypeId', $this->getAnimalType()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\AnimalType
     */
    public function getAnimalType()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\AnimalType $animalType
     * @return $this
     */
    public function setAnimalType($animalType)
    {
        return $this->setModel($animalType);
    }

}