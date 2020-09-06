<?php
namespace App\Form;

use App\Db\StorageMap;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Cassette::create();
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
class Cassette extends \Bs\FormIface
{

    /**
     * @throws \Exception
     */
    public function init()
    {
        $layout = $this->getForm()->getRenderer()->getLayout();

        $layout->removeRow('container', 'col');
        $layout->removeRow('qty', 'col');
        $layout->removeRow('name', 'col-10');

        //$this->appendField(new Field\Select('pathCaseId', array()))->prependOption('-- Select --', '');
        $list = StorageMap::create()->findFiltered(array('institutionId' => $this->getCassette()->getPathCase()->getInstitutionId()));
        $this->appendField(new Field\Select('storageId', $list))->prependOption('-- None --', '');
        $this->appendField(new Field\Input('container'));
        $this->appendField(new Field\Input('number'));      // TODO: Auto determine this number
        $this->appendField(new Field\Input('qty'));
        $this->appendField(new Field\Input('name'));
        //$this->appendField(new Field\Input('price'));
        $this->appendField(new Field\Textarea('comments'));
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
        $this->load(\App\Db\CassetteMap::create()->unmapForm($this->getCassette()));
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
        \App\Db\CassetteMap::create()->mapForm($form->getValues(), $this->getCassette());

        // Do Custom Validations

        $form->addFieldErrors($this->getCassette()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getCassette()->getId();
        $this->getCassette()->save();

        // Do Custom data saving

        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('cassetteId', $this->getCassette()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\Cassette
     */
    public function getCassette()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\Cassette $cassette
     * @return $this
     */
    public function setCassette($cassette)
    {
        return $this->setModel($cassette);
    }

}