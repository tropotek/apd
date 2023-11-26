<?php
namespace App\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;
use Tk\Mail\Message;

/**
 * Example:
 * <code>
 *   $form = new Student::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 */
class Student extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        $this->appendField(new Field\Input('name'));
        $this->appendField(new Field\Input('email'));

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
        $this->load(\App\Db\StudentMap::create()->unmapForm($this->getStudent()));
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
        \App\Db\StudentMap::create()->mapForm($form->getValues(), $this->getStudent());

        // Do Custom Validations

        $form->addFieldErrors($this->getStudent()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getStudent()->getId();
        $this->getStudent()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('studentId', $this->getStudent()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\Student
     */
    public function getStudent()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\Student $student
     * @return $this
     */
    public function setStudent($student)
    {
        return $this->setModel($student);
    }

}