<?php
namespace App\Table;

use Tk\Date;
use Tk\Form\Field;
use Tk\Table\Cell;
use Uni\Uri;

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
class InvoiceItemReport extends \Bs\TableIface
{

    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {
        $this->appendCell(new Cell\Text('pathologyId'))
            ->setUrl(Uri::createHomeUrl('/pathCaseEdit.html'))
            ->setUrlProperty('pathCaseId')
            ->addOnPropertyValue(function (\Tk\Table\Cell\Iface $cell, \App\Db\InvoiceItem $obj, $value) {
                $value = $obj->getPathCase()->getPathologyId();
                return $value;
            });
        $this->appendCell(new Cell\Text('description'))->addCss('key')->setUrl($this->getEditUrl());
        $this->appendCell(new Cell\Text('code'));
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
        $f = $this->appendFilter(new Field\DateRange('created'));
        $this->getFilterForm()->load([
            'createdStart' => Date::getMonthStart(Date::create()->sub(new \DateInterval('P1M')))->format(Date::FORMAT_SHORT_DATE)
        ]);

        // Actions
        //$this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified', 'created')));
        $this->appendAction(\Tk\Table\Action\Csv::create());

        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\App\Db\InvoiceItem[]
     * @throws \Exception
     */
    public function findList($filter = [], $tool = null)
    {
        if (!$tool) $tool = $this->getTool('created, path_case_id');
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\InvoiceItemMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}