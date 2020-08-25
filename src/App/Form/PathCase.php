<?php
namespace App\Form;

use App\Db\ClientMap;
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
        $list  = ClientMap::create()->findFiltered(array('institutionId'=> $this->getPathCase()->getInstitutionId()));

        $tab = 'Details';

        // TODO: Add ability to create a new client with a button and dialog box.
        $this->appendField(Field\Select::createSelect('clientId', $list)->prependOption('-- Select --', ''))
            ->setTabGroup($tab);
        $this->appendField(new Field\Input('pathologyId'))->setTabGroup($tab);
        $list = \Tk\ObjectUtil::getClassConstants($this->getPathCase(), 'TYPE_');
        $this->appendField(Field\Select::createSelect('type', $list)->prependOption('-- Select --', ''))
            ->setTabGroup($tab);

        $list = \Tk\ObjectUtil::getClassConstants($this->getPathCase(), 'SUBMISSION_');
        $this->appendField(Field\Select::createSelect('submissionType', $list)->prependOption('-- Select --', ''))
            ->setTabGroup($tab);

        if ($this->getPathCase()->getId()) {
            $list = \App\Db\PathCase::getStatusList($this->getPathCase()->getStatus());
            $this->appendField(\Bs\Form\Field\StatusSelect::createSelect('status', $list)->prependOption('-- Status --', ''))
                ->setRequired()->setTabGroup($tab)
                ->setNotes('Set the status. Use the checkbox to disable notification emails.');
        }
//        $this->appendField(new Field\Input('submitted'))->setTabGroup($tab)->addCss('date')->setAttr('placeholder', 'dd/mm/yyyy');
//        $this->appendField(new Field\Input('examined'))->setTabGroup($tab)->addCss('date')->setAttr('placeholder', 'dd/mm/yyyy');
//        $this->appendField(new Field\Input('finalised'))->setTabGroup($tab)->addCss('date')->setAttr('placeholder', 'dd/mm/yyyy');

        $list = \Tk\ObjectUtil::getClassConstants($this->getPathCase(), 'ZOO_');
        $this->appendField(new Field\Input('zootonicDisease'))->setTabGroup($tab);
        $this->appendField(Field\Select::createSelect('zootonicResult', $list)->prependOption('-- Select --', ''))
            ->setTabGroup($tab);
        //$this->appendField(new Field\Input('zootonicResult'))->setTabGroup($tab);

        $this->appendField(new Field\Input('specimenCount'))->setTabGroup($tab);

        $tab = 'Animal';
        $this->appendField(new Field\Input('animalName'))->setTabGroup($tab);
        $this->appendField(new Field\Input('species'))->setTabGroup($tab);
        $this->appendField(new Field\Input('gender'))->setTabGroup($tab);
        $this->appendField(new Field\Checkbox('desexed'))->setTabGroup($tab);
        $this->appendField(new Field\Input('patientNumber'))->setTabGroup($tab);
        $this->appendField(new Field\Input('microchip'))->setTabGroup($tab);
        $this->appendField(new Field\Input('ownerName'))->setTabGroup($tab);
        $this->appendField(new Field\Input('origin'))->setTabGroup($tab);
        $this->appendField(new Field\Input('breed'))->setTabGroup($tab);
        $this->appendField(new Field\Input('vmisWeight'))->setTabGroup($tab);
        $this->appendField(new Field\Input('necoWeight'))->setTabGroup($tab);
        $this->appendField(new Field\Input('dob'))->setTabGroup($tab)->addCss('date')->setAttr('placeholder', 'dd/mm/yyyy');
        $this->appendField(new Field\Input('dod'))->setTabGroup($tab)->addCss('date')->setAttr('placeholder', 'dd/mm/yyyy');

        $tab = 'Sample';
        $this->appendField(new Field\Checkbox('euthanised'))->setTabGroup($tab);
        $this->appendField(new Field\Input('euthanisedMethod'))->setTabGroup($tab);

        $list = \Tk\ObjectUtil::getClassConstants($this->getPathCase(), 'AC_');
        $this->appendField(Field\Select::createSelect('acType', $list)->prependOption('-- None --', ''))
            ->setLabel('Aftercare Type')->setTabGroup($tab);
        $this->appendField(new Field\Input('acHold'))->addCss('date')->setAttr('placeholder', 'dd/mm/yyyy')
            ->setLabel('Aftercare Hold')->setTabGroup($tab);
        $this->appendField(Field\Select::createSelect('storageId', array())->prependOption('-- Select --', ''))
            ->setTabGroup($tab);
        $this->appendField(new Field\Input('disposal'))->addCss('date')->setAttr('placeholder', 'dd/mm/yyyy')
            ->setTabGroup($tab);

        $tab = 'Reporting';
        // TODO: All MCE editors must use the case folder to store media in
        //     /media => WYSIWYG files, /files => case attached files
        // TODO: Allow WYSIWYG to view all files but only upload to html folder if possible (add this later)
        $mediaPath = $this->getPathCase()->getDataPath().'/media';
        $this->appendField(new Field\Textarea('clinicalHistory'))
            ->addCss('mce-med')->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);
        $this->appendField(new Field\Textarea('grossPathology'))
            ->addCss('mce-med')->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);
        $this->appendField(new Field\Textarea('grossMorphologicalDiagnosis'))
            ->addCss('mce-med')->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);
        $this->appendField(new Field\Textarea('histopathology'))
            ->addCss('mce-med')->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);
        $this->appendField(new Field\Textarea('ancillaryTesting'))
            ->addCss('mce-med')->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);
        $this->appendField(new Field\Textarea('morphologicalDiagnosis'))
            ->addCss('mce-med')->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);
        $this->appendField(new Field\Textarea('causeOfDeath'))
            ->addCss('mce-med')->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);
        $this->appendField(new Field\Textarea('comments'))
            ->addCss('mce-med')->setAttr('data-elfinder-path', $mediaPath)->setTabGroup($tab);
        //$this->appendField(new Field\Textarea('notes'));

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