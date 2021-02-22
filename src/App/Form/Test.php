<?php
namespace App\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Test::create();
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
class Test extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {

        $this->appendField(new Field\Input('name'));
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
        $this->load(\App\Db\TestMap::create()->unmapForm($this->getTest()));
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
        \App\Db\TestMap::create()->mapForm($form->getValues(), $this->getTest());

        // Do Custom Validations

        $form->addFieldErrors($this->getTest()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getTest()->getId();
        $this->getTest()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('testId', $this->getTest()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\Test
     */
    public function getTest()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\Test $test
     * @return $this
     */
    public function setTest($test)
    {
        return $this->setModel($test);
    }

}