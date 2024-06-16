<?php
namespace App\Table;

use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new AnimalType::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2021-01-13
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class AnimalType extends \Bs\TableIface
{

    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {

        $this->appendCell(new Cell\Checkbox('id'));
        //$this->appendCell(new Cell\Text('parentId'));
        $this->appendCell(new Cell\Text('name'))->addCss('key')->setUrl($this->getEditUrl());
        $this->appendCell(new Cell\Text('description'));
        $this->appendCell(new Cell\Boolean('active'));
        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');
        $list = ['Active' => '1', 'Not Active' => '0'];
        $this->appendFilter(Field\Select::createSelect('active', $list)
            ->setValue('1')
            ->prependOption('-- All States--'));

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Animal Type', \Bs\Uri::createHomeUrl('/animal/typeEdit.html'), 'fa fa-plus'));
        //$this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified', 'created')));
        //$this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());

        // load table
        //$this->setList($this->findList());

        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\App\Db\AnimalType[]
     * @throws \Exception
     */
    public function findList($filter = [], $tool = null)
    {
        if (!$tool) $tool = $this->getTool('name');
        $filter = array_merge($this->getFilterValues(), $filter);
        return \App\Db\AnimalTypeMap::create()->findFiltered($filter, $tool);
    }

}