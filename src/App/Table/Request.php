<?php
namespace App\Table;

use Tk\Form\Field;
use Tk\Table\Cell;

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
class Request extends \Bs\TableIface
{

    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {

        $this->appendCell(new Cell\Checkbox('id'));
        //$this->appendCell(new Cell\Text('pathCaseId'));
        $this->appendCell(new Cell\Text('cassetteId'))->addCss('key')->setUrl($this->getEditUrl());;
        $this->appendCell(new Cell\Text('serviceId'));
        $this->appendCell(new Cell\Text('clientId'));
        $this->appendCell(new Cell\Text('qty'));
        //$this->appendCell(new Cell\Text('price'));

        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Request', \Bs\Uri::createHomeUrl('/requestEdit.html'), 'fa fa-plus'));
        $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified')));
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