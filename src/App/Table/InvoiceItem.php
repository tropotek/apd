<?php
namespace App\Table;

use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new InvoiceItem::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2021-01-27
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class InvoiceItem extends \Bs\TableIface
{

    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {

        $this->appendCell(new Cell\Checkbox('id'));
        $this->appendCell(new Cell\Text('description'))->addCss('key')->setUrl($this->getEditUrl());
        $this->appendCell(new Cell\Text('qty'));
        $this->appendCell(new Cell\Text('price'));
        $this->appendCell(new Cell\Text('total'))->setOrderProperty('')
            ->addOnPropertyValue(function (\Tk\Table\Cell\Iface $cell, \App\Db\InvoiceItem $obj, $value) {
                $value = $obj->getTotal()->toString();
                return $value;
            }
        );
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Invoice Item', \Bs\Uri::createHomeUrl('/invoice/itemEdit.html'), 'fa fa-plus'));
        //$this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified', 'created')));
        $this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());

        // load table
        //$this->setList($this->findList());

        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\App\Db\InvoiceItem[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\InvoiceItemMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}