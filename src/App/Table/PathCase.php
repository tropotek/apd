<?php
namespace App\Table;

use App\Db\ClientMap;
use Tk\Form\Field;
use Tk\Table\Cell;
use Uni\Db\User;

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
        $this->appendCell(new Cell\Text('age'))
            ->setOrderProperty('b.age')
            ->addOnPropertyValue(function (Cell\Text $cell, \App\Db\PathCase $obj, $value) {
                $value = '';
                $dob = $obj->getDob();
                if (property_exists($obj, 'age') && property_exists($obj, 'age_m')) {
                    $value = sprintf('%s.%s', $obj->age, $obj->age_m);
                } else if ($dob) {
                    $dod = \Tk\Date::create();
                    if ($obj->getDod())
                        $dod = $obj->getDod();
                    $value = sprintf('%s.%s', $dob->diff($dod)->y, $dob->diff($dod)->m);
                }
                return $value;
            });
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

        $this->appendFilter(new Field\Input('age'))->setAttr('placeholder', 'Age');

        $list = $this->getConfig()->getUserMapper()->findFiltered(array(
            'institutionId' => $this->getConfig()->getInstitutionId(),
            'type' => User::TYPE_STAFF
        ));
        $this->appendFilter(Field\Select::createSelect('userId', $list)->prependOption('-- Staff --'));
        
        $list = ClientMap::create()->findFiltered(array(
            'institutionId' => $this->getConfig()->getInstitutionId()
        ));
        $this->appendFilter(Field\Select::createSelect('clientId', $list)->prependOption('-- Submitter/Client --'));
        $this->appendFilter(Field\Select::createSelect('ownerId', $list)->prependOption('-- Owner --'));

        $list = \Tk\ObjectUtil::getClassConstants(\App\Db\PathCase::class, 'TYPE', true);
        $this->appendFilter(Field\Select::createSelect('type', $list)->prependOption('-- Case Type --'));
        $list = \Tk\ObjectUtil::getClassConstants(\App\Db\PathCase::class, 'TYPE', true);
        $this->appendFilter(Field\Select::createSelect('submissionType', $list)->prependOption('-- Submission Type --'));
        $list = \Tk\ObjectUtil::getClassConstants(\App\Db\PathCase::class, 'STATUS', true);
        $this->appendFilter(Field\Select::createSelect('status', $list)->prependOption('-- Status --'));


        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Path Case', \Bs\Uri::createHomeUrl('/pathCaseEdit.html'), 'fa fa-plus'));
        //$this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified')));
        $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setSelected(
                array('id', 'pathologyId', 'userId', 'clientId', 'owner', 'age', 'patientNumber', 'type', 'submissionType', 'status', 'created')
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