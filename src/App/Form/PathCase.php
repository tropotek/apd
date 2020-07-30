<?php
namespace App\Form;

use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new PathCase::create();
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
class PathCase extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        $this->appendField(new Field\Select('clientId', array()))->prependOption('-- Select --', '');
        $this->appendField(new Field\Select('pathologyId', array()))->prependOption('-- Select --', '');
        $this->appendField(new Field\Input('type'));
        $this->appendField(new Field\Input('submissionType'));
        $this->appendField(new Field\Input('status'));
        $this->appendField(new Field\Input('submitted'));
        $this->appendField(new Field\Input('examined'));
        $this->appendField(new Field\Input('finalised'));
        $this->appendField(new Field\Input('zootonicDisease'));
        $this->appendField(new Field\Input('zootonicResult'));
        $this->appendField(new Field\Input('specimenCount'));
        $this->appendField(new Field\Input('animalName'));
        $this->appendField(new Field\Input('species'));
        $this->appendField(new Field\Input('gender'));
        $this->appendField(new Field\Checkbox('desexed'));
        $this->appendField(new Field\Input('patientNumber'));
        $this->appendField(new Field\Input('microchip'));
        $this->appendField(new Field\Input('ownerName'));
        $this->appendField(new Field\Input('origin'));
        $this->appendField(new Field\Input('breed'));
        $this->appendField(new Field\Input('vmisWeight'));
        $this->appendField(new Field\Input('necoWeight'));
        $this->appendField(new Field\Input('dob'));
        $this->appendField(new Field\Input('dod'));
        $this->appendField(new Field\Checkbox('euthanised'));
        $this->appendField(new Field\Input('euthanisedMethod'));
        $this->appendField(new Field\Input('acType'));
        $this->appendField(new Field\Input('acHold'));
        $this->appendField(new Field\Select('storageId', array()))->prependOption('-- Select --', '');
        $this->appendField(new Field\Input('disposal'));
        $this->appendField(new Field\Textarea('clinicalHistory'));
        $this->appendField(new Field\Textarea('grossPathology'));
        $this->appendField(new Field\Textarea('grossMorphologicalDiagnosis'));
        $this->appendField(new Field\Textarea('histopathology'));
        $this->appendField(new Field\Textarea('ancillaryTesting'));
        $this->appendField(new Field\Textarea('morphologicalDiagnosis'));
        $this->appendField(new Field\Textarea('causeOfDeath'));
        $this->appendField(new Field\Textarea('comments'));
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
        $this->load(\App\Db\PathCaseMap::create()->unmapForm($this->getPathCase()));
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
        \App\Db\PathCaseMap::create()->mapForm($form->getValues(), $this->getPathCase());

        // Do Custom Validations

        $form->addFieldErrors($this->getPathCase()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getPathCase()->getId();
        $this->getPathCase()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('pathCaseId', $this->getPathCase()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\PathCase
     */
    public function getPathCase()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\PathCase $pathCase
     * @return $this
     */
    public function setPathCase($pathCase)
    {
        return $this->setModel($pathCase);
    }

}