<?php
namespace App\Table;

use App\Db\CompanyContactMap;
use App\Table\Action\MoveCompany;
use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new Company::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 */
class Company extends \Bs\TableIface
{

    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {

        $this->appendCell(new Cell\Checkbox('id'));
        $this->appendCell(new Cell\Text('name'))->addCss('key')->setUrl($this->getEditUrl());
        $this->appendCell(new Cell\Text('email'));
        $this->appendCell(new Cell\Text('phone'));
        $this->appendCell(new Cell\Text('city'));
        $this->appendCell(new Cell\Text('state'));
        $this->appendCell(new Cell\Text('contacts'))
            ->addOnPropertyValue(function (\Tk\Table\Cell\Iface $cell, \App\Db\Company $obj, $value) {
                $value = CompanyContactMap::create()->findFiltered(['companyId' => $obj->getId()])->count();
                return $value;
            });
        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Delete::create());
        /** @var MoveCompany $a */
        $a = $this->appendAction(new \App\Table\Action\MoveCompany('delete', 'fa fa-times'));
        $a->setLabel('Delete');
        $a->setDeleteAfterMove(true);
        $this->appendAction(\Tk\Table\Action\Csv::create());
        $this->appendAction(new \App\Table\Action\MoveCompany());

        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\App\Db\Company[]
     * @throws \Exception
     */
    public function findList($filter = [], $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\CompanyMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}