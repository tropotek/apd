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
        $this->appendCell(new Cell\Text('pathologyId'))->addCss('key')->setUrl($this->getEditUrl());
        $this->appendCell(new Cell\Text('userId'))->setLabel('Staff')->addOnPropertyValue(function (Cell\Text $cell, \App\Db\PathCase $obj, $value)
        {
            $user = $obj->getUser();
            if ($user) {
                $value = $user->getName();
            }
            return $value;
        });
        $this->appendCell(new Cell\Text('clientId'))->addOnPropertyValue(function (Cell\Text $cell, \App\Db\PathCase $obj, $value)
        {
            $user = $obj->getClient();
            if ($user) {
                $value = $user->getName();
            }
            return $value;
        });
        $this->appendCell(new Cell\Text('type'));
        $this->appendCell(new Cell\Text('submissionType'));
        $this->appendCell(new Cell\Text('status'));


        $this->appendCell(new Cell\Text('cost'));
        $this->appendCell(new Cell\Boolean('afterHours'));
        $this->appendCell(new Cell\Text('zootonic'));
        $this->appendCell(new Cell\Text('issue'));
        $this->appendCell(new Cell\Text('specimenCount'));
        $this->appendCell(new Cell\Text('animalName'));
        $this->appendCell(new Cell\Text('species'));
        $this->appendCell(new Cell\Text('sex'));
        $this->appendCell(new Cell\Boolean('desexed'));
        $this->appendCell(new Cell\Text('patientNumber'));
        $this->appendCell(new Cell\Text('microchip'));
        $this->appendCell(new Cell\Text('owner'))
            ->addOnPropertyValue(function (Cell\Text $cell, \App\Db\PathCase $obj, $value) {
                $owner = $obj->getOwner();
                $value = '';
                if ($owner) {
                    $value = $owner->getName();
                }
                return $value;
            });
        $this->appendCell(new Cell\Text('origin'));
        $this->appendCell(new Cell\Text('breed'));
        $this->appendCell(new Cell\Text('colour'));
        $this->appendCell(new Cell\Text('weight'));
        $this->appendCell(new Cell\Date('dob'));
        $this->appendCell(new Cell\Date('dod'));
        $this->appendCell(new Cell\Boolean('euthanised'));
        $this->appendCell(new Cell\Text('euthanisedMethod'));
        $this->appendCell(new Cell\Text('acType'));
        $this->appendCell(new Cell\Date('acHold'));
        //$this->appendCell(new Cell\Text('storageId'));
        $this->appendCell(new Cell\Date('disposal'));



        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Path Case', \Bs\Uri::createHomeUrl('/pathCaseEdit.html'), 'fa fa-plus'));
        //$this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified')));
        $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setSelected(
                array('id', 'pathologyId', 'userId', 'clientId', 'type', 'submissionType', 'status', 'created')
            )->setHidden(array('id'))
        );
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