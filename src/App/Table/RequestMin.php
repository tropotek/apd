<?php
namespace App\Table;


use Tk\Form\Field;
use Tk\Table\Cell;
use Uni\Uri;

/**
 * Example:
 * <code>
 *   $table = new Request::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2020-07-30
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class RequestMin extends \Bs\TableIface
{
    /**
     * @var bool
     */
    private $minMode = false;


    public function __construct($tableId = '')
    {
        parent::__construct($tableId);

        if ($this->getConfig()->getRequest()->has('rComplete')) {
            $this->doComplete($this->getConfig()->getRequest()->get('rComplete'));
        }

        if ($this->getConfig()->getRequest()->has('rCancel')) {
            $this->doCancel($this->getConfig()->getRequest()->get('rCancel'));
        }

        if ($this->getConfig()->getRequest()->has('rDel')) {
            $this->doDelete($this->getConfig()->getRequest()->get('rDel'));
        }

    }

    public function doComplete($requestId)
    {
        /** @var \App\Db\Request $request */
        $request = \App\Db\RequestMap::create()->find($requestId);
        if ($request) {
            $request->setStatus(\App\Db\Request::STATUS_COMPLETED);
            //$request->setStatusMessage('');
            $request->setStatusExecute(true);
            $request->setStatusNotify(true);
            $request->save();
            \Tk\Uri::create()->remove('rComplete')->redirect();
        }
    }

    public function doCancel($requestId)
    {
        /** @var \App\Db\Request $request */
        $request = \App\Db\RequestMap::create()->find($requestId);
        if ($request) {
            $request->setStatus(\App\Db\Request::STATUS_CANCELLED);
            //$request->setStatusMessage('');
            $request->setStatusExecute(true);
            $request->setStatusNotify(true);
            $request->save();
            \Tk\Uri::create()->remove('rCancel')->redirect();
        }
    }

    public function doDelete($requestId)
    {
        /** @var \App\Db\Request $request */
        $request = \App\Db\RequestMap::create()->find($requestId);
        if ($request) {
            $request->delete();
            \Tk\Uri::create()->remove('rDel')->redirect();
        }
    }


    /**
     * @return bool
     */
    public function isMinMode(): bool
    {
        return $this->minMode;
    }

    /**
     * Set this to true to enable minimum mode that will render for side panels
     *
     * @param bool $minMode
     * @return Request
     */
    public function setMinMode(bool $minMode)
    {
        $this->minMode = $minMode;
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {
        $this->addCss('tk-request-table');

        if($this->isMinMode())
            $this->getRenderer()->enableFooter(false);

        if (!$this->isMinMode()) {
            $this->appendCell(new Cell\Checkbox('id'));
        }

        $aCell = $this->getActionCell();
        $aCell->addButton(Cell\ActionButton::create('Complete', Uri::create(), 'fa fa-thumbs-up')->addCss('btn-success'))
            ->setShowLabel(false)
            ->addOnShow(function ($cell, \App\Db\Request $obj, Cell\ActionButton $button) {
                $button->getUrl()->set('rComplete', $obj->getId());
                $button->setAttr('data-confirm', 'Are you sure you want to mark request completed?');
                if ($obj->getStatus() == \App\Db\Request::STATUS_COMPLETED) {
                    $button->setAttr('disabled')->addCss('disabled');
                }
            });
        $aCell->addButton(Cell\ActionButton::create('Cancel', Uri::create(), 'fa fa-thumbs-down')->addCss('btn-warning'))
            ->setShowLabel(false)
            ->addOnShow(function ($cell, \App\Db\Request $obj, Cell\ActionButton $button) {
                $button->getUrl()->set('rCancel', $obj->getId());
                $button->setAttr('data-confirm', 'Are you sure you want to cancel this request?');
                if ($obj->getStatus() == \App\Db\Request::STATUS_CANCELLED) {
                    $button->setAttr('disabled')->addCss('disabled');
                }
            });

//        if ($this->isMinMode()) {
//            $aCell->addButton(Cell\ActionButton::create('Delete', Uri::create(), 'fa fa-trash')->addCss('btn-danger'))
//                ->setShowLabel(false)
//                ->addOnShow(function ($cell, \App\Db\Request $obj, Cell\ActionButton $button) {
//                    $button->getUrl()->set('rDel', $obj->getId());
//                    $button->setAttr('data-confirm', 'Are you sure you want to remove this request?');
//                });
//        }
        $this->appendCell($this->getActionCell())->setLabel('');


        //$this->appendCell(new Cell\Text('pathCaseId'));
        $this->appendCell(new Cell\Text('status'));
        $this->appendCell(new Cell\Text('qty'));
        $this->appendCell(new Cell\Text('serviceId'))->addCss('key')->setUrl($this->getEditUrl())
            ->addOnPropertyValue(function (Cell\Text $cell, \App\Db\Request $obj, $value) {
                \Tk\Db\Map\Mapper::$HIDE_DELETED = false;
                if ($obj->getService()) {
                    $value = $obj->getService()->getName();
                    if ($obj->getService()->isDel()) {
                        $value = '* ' . $value;
                    }
                }
                \Tk\Db\Map\Mapper::$HIDE_DELETED = true;
                return $value;
            });

        $this->appendCell(new Cell\Text('cassetteId'))->setUrl($this->getEditUrl())
            ->addOnPropertyValue(function (Cell\Text $cell, \App\Db\Request $obj, $value) {
                \Tk\Db\Map\Mapper::$HIDE_DELETED = false;
                if ($obj->getCassette()) {
                    $value = $obj->getCassette()->getNumber();
                    if ($obj->getCassette()->isDel()) {
                        $value = '* ' . $value;
                    }
                }
                \Tk\Db\Map\Mapper::$HIDE_DELETED = true;
                return $value;
            });

        $this->appendCell(new Cell\Text('testId'))
            ->addOnPropertyValue(function (Cell\Text $cell, \App\Db\Request $obj, $value) {
                $value = '';
                if ($obj->getTest()) {
                    $value = $obj->getTest()->getName();
                }
                return $value;
            });


        if (!$this->isMinMode()) {
            $this->appendCell(new Cell\Date('modified'));
            $this->appendCell(new Cell\Date('created'));
        }

        // Filters
        if (!$this->isMinMode())
            $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Request', \Bs\Uri::createHomeUrl('/requestEdit.html'), 'fa fa-plus'));

        $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified')));
        if (!$this->isMinMode())
            $this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());

        // load table
        //$this->setList($this->findList());

        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\App\Db\Request[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\RequestMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}