<?php
namespace App\Table;

use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new Student::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 *
 */
class Student extends \Bs\TableIface
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
        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        $this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());

        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\App\Db\Student[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\StudentMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}