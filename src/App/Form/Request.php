<?php
namespace App\Form;

use App\Db\CassetteMap;
use App\Db\ContactMap;
use App\Db\ServiceMap;
use Tk\Form\Field;
use Tk\Form\Event;
use Tk\Form;

/**
 * Example:
 * <code>
 *   $form = new Request::create();
 *   $form->setModel($obj);
 *   $formTemplate = $form->getRenderer()->show();
 *   $template->appendTemplate('form', $formTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2020-07-30
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Request extends \Bs\FormIface
{
    /**
     * @var null|\App\Ui\Dialog\Request
     */
    protected $dialog = null;

    /**
     * @throws \Exception
     */
    public function init()
    {
        $layout = $this->getForm()->getRenderer()->getLayout();
        //$layout->removeRow('serviceId', 'col');
        $layout->removeRow('clientId', 'col');

        //$this->appendField(new Field\Select('pathCaseId', array()))->prependOption('-- Select --', '');
        if (!$this->getDialog()) {
            if (!$this->getRequest()->getCassetteId()) {
                $list = CassetteMap::create()->findFiltered(array('pathCaseId' => $this->getRequest()->getPathCaseId()));
                $this->appendField(Field\Select::createSelect('cassetteId', $list)
                    ->addOnShowOption(function (\Dom\Template $template, \Tk\Form\Field\Option $option, $var) {
                        /** @var \App\Db\Cassette $cassette */
                        $cassette = CassetteMap::create()->find($option->getValue());
                        if ($cassette)
                            $option->setName('[' . $cassette->getNumber() . '] ' . $cassette->getName());
                    }))->prependOption('-- Select --', '');
            } else {
                // TODO: Add a text field with cassete name
                $cassette = $this->getRequest()->getCassette();
                $this->appendField(new Field\Html('cassette'))->setValue($cassette->getName());
            }
        }
        $list = ServiceMap::create()->findFiltered(array('institutionId' => $this->getRequest()->getPathCase()->getInstitutionId()));
        $this->appendField(new Field\Select('serviceId', $list))->prependOption('-- Select --', '');
        $list = ContactMap::create()->findFiltered(array('institutionId' => $this->getRequest()->getPathCase()->getInstitutionId()));
        $this->appendField(new Field\Select('clientId', $list))->prependOption('-- Select --', '');

        if ($this->getRequest()->getId()) {
            $list = \App\Db\Request::getStatusList($this->getRequest()->getStatus());
            $this->appendField(new \Bs\Form\Field\StatusSelect('status', $list))
                ->setRequired()->prependOption('-- Status --', '')
                ->setNotes('Set the status. Use the checkbox to disable notification emails.');
        }
        $this->appendField(new Field\Input('qty'));
        //$this->appendField(new Field\Input('price'));
        $comm = $this->appendField(new Field\Textarea('comments'));
        if ($this->dialog) {
            $comm->setAttr('rows', 2);
            $comm->setAttr('style', 'height: 4.4em;min-height: unset;');
        }

        //$this->appendField(new Field\Textarea('notes'));

        $this->appendField(new Event\Submit('update', array($this, 'doSubmit')));
        $this->appendField(new Event\Submit('save', array($this, 'doSubmit')));
        $this->appendField(new Event\Link('cancel', $this->getBackUrl()));

    }

    /**
     * @param \Tk\Request|null $request
     * @throws \Exception
     */
    public function execute($request = null)
    {
        $this->load(\App\Db\RequestMap::create()->unmapForm($this->getRequest()));
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
        \App\Db\RequestMap::create()->mapForm($form->getValues(), $this->getRequest());
        $cassetteList = $this->getConfig()->getRequest()->get('cassetteId', null);
        if ($cassetteList && count($cassetteList)) {
            $this->getRequest()->setCassetteId($cassetteList[0]);       // Fix cassetteId error
        }

        $form->addFieldErrors($this->getRequest()->validate());
        if ($form->hasErrors()) {
            return;
        }

        $isNew = (bool)$this->getRequest()->getId();
        if (count($cassetteList)) {
            $cassetteList = $this->getConfig()->getRequest()->get('cassetteId');
            foreach ($cassetteList as $i => $v) {
                $req = new \App\Db\Request();
                \App\Db\RequestMap::create()->mapForm($form->getValues(), $req);
                $req->setCassetteId($v);
                $req->save();
            }
        } else {
            $this->getRequest()->save();
        }


        \Tk\Alert::addSuccess('Record saved!');
        $event->setRedirect($this->getBackUrl());
        if ($form->getTriggeredEvent()->getName() == 'save') {
            $event->setRedirect(\Tk\Uri::create()->set('requestId', $this->getRequest()->getId()));
        }
    }

    /**
     * @return \Tk\Db\ModelInterface|\App\Db\Request
     */
    public function getRequest()
    {
        return $this->getModel();
    }

    /**
     * @param \App\Db\Request $request
     * @return $this
     */
    public function setRequest($request)
    {
        return $this->setModel($request);
    }

    /**
     * @return \App\Ui\Dialog\Request|null
     */
    public function getDialog(): ?\App\Ui\Dialog\Request
    {
        return $this->dialog;
    }

    /**
     * @param \App\Ui\Dialog\Request|null $dialog
     * @return Request
     */
    public function setDialog(?\App\Ui\Dialog\Request $dialog): Request
    {
        $this->dialog = $dialog;
        return $this;
    }

}