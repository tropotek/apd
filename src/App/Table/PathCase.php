<?php
namespace App\Table;

use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new PathCase::create();
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
class PathCase extends \Bs\TableIface
{

    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {

        $this->appendCell(new Cell\Checkbox('id'));
        $this->appendCell(new Cell\Text('institutionId'));
        $this->appendCell(new Cell\Text('clientId'));
        $this->appendCell(new Cell\Text('pathologyId'));
        $this->appendCell(new Cell\Text('type'));
        $this->appendCell(new Cell\Text('submissionType'));
        $this->appendCell(new Cell\Text('status'));
        $this->appendCell(new Cell\Date('submitted'));
        $this->appendCell(new Cell\Date('examined'));
        $this->appendCell(new Cell\Date('finalised'));
        $this->appendCell(new Cell\Text('zootonicDisease'));
        $this->appendCell(new Cell\Text('zootonicResult'));
        $this->appendCell(new Cell\Text('specimenCount'));
        $this->appendCell(new Cell\Text('animalName'));
        $this->appendCell(new Cell\Text('species'));
        $this->appendCell(new Cell\Text('gender'));
        $this->appendCell(new Cell\Boolean('desexed'));
        $this->appendCell(new Cell\Text('patientNumber'));
        $this->appendCell(new Cell\Text('microchip'));
        $this->appendCell(new Cell\Text('ownerName'));
        $this->appendCell(new Cell\Text('origin'));
        $this->appendCell(new Cell\Text('breed'));
        $this->appendCell(new Cell\Text('vmisWeight'));
        $this->appendCell(new Cell\Text('necoWeight'));
        $this->appendCell(new Cell\Date('dob'));
        $this->appendCell(new Cell\Date('dod'));
        $this->appendCell(new Cell\Boolean('euthanised'));
        $this->appendCell(new Cell\Text('euthanisedMethod'));
        $this->appendCell(new Cell\Text('acType'));
        $this->appendCell(new Cell\Date('acHold'));
        $this->appendCell(new Cell\Text('storageId'));
        $this->appendCell(new Cell\Date('disposal'));
        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Path Case', \Bs\Uri::createHomeUrl('/pathCaseEdit.html'), 'fa fa-plus'));
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
     * @return \Tk\Db\Map\ArrayObject|\App\Db\PathCase[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\PathCaseMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}