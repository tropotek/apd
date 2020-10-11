<?php
namespace App\Table;

use Tk\Form\Field;
use Tk\Table\Cell;
use Tk\Table\Cell\ActionButton;

/**
 * Example:
 * <code>
 *   $table = new Cassette::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Cassette extends \Bs\TableIface
{
    /**
     * @var bool
     */
    private $minMode = false;

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
     * @return Cassette
     */
    public function setMinMode(bool $minMode): Cassette
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
        if($this->isMinMode())
            $this->getRenderer()->enableFooter(false);

        $this->appendCell(new Cell\Checkbox('id'));
        if ($this->isMinMode()) {
//            $this->appendCell(new Cell\Checkbox('id'));
//        } else {
            $aCell = $this->getActionCell();
            $url = \Uni\Uri::createHomeUrl('/requestEdit.html');
            $aCell->addButton(ActionButton::create('Create Request', $url, 'fa fa-medkit')->addCss('btn-primary'))
                ->setShowLabel(false)
                ->addOnShow(function ($cell, $obj, $button) {
                    /* @var $obj \App\Db\Cassette */
                    /* @var $button ActionButton */
                    $button->getUrl()->set('cassetteId', $obj->getId());
                    if ($obj->getQty() <= 0) {
                        $button->addCss('btn-dark disabled');
                    }
                });
            $this->appendCell($this->getActionCell())->setLabel('');

        }
        $this->appendCell(new Cell\Text('number'))->setLabel('#');
        $this->appendCell(new Cell\Text('name'))->addCss('key')->setUrl($this->getEditUrl());
        //$this->appendCell(new Cell\Text('pathCaseId'));
        //$this->appendCell(new Cell\Text('storageId'));
        //$this->appendCell(new Cell\Text('price'));
        //$this->appendCell(new Cell\Date('modified'));
        //$this->appendCell(new Cell\Text('container'));
        //$this->appendCell(new Cell\Text('qty'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        if (!$this->isMinMode())
            $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        if ($this->isMinMode()) {
            //$this->appendAction(\Tk\Table\Action\Link::createLink('New Cassette', \Bs\Uri::createHomeUrl('/cassetteEdit.html'), 'fa fa-plus'));
        }
        $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified', 'container')));
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
     * @return \Tk\Db\Map\ArrayObject|\App\Db\Cassette[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\CassetteMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}