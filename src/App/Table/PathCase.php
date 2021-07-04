<?php
namespace App\Table;

use App\Db\AnimalTypeMap;
use App\Db\ContactMap;
use App\Db\PathCaseMap;
use Tk\Db\Tool;
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
        $this->addCss('tk-pathCase-table');

        $this->appendCell(new Cell\Checkbox('id'));
        //$this->resetSession();
        $this->appendCell(new Cell\Text('pathologyId'))->setLabel('Pathology #')
            ->addCss('key')->setUrl($this->getEditUrl());

        $this->appendCell(new Cell\Text('pathologistId'))->setLabel('Pathologist')
            ->addOnPropertyValue(function (Cell\Text $cell, \App\Db\PathCase $obj, $value) {
                $value = '';
                $user = $obj->getPathologist();
                if ($user) {
                    $value = $user->getName();
                }
                return $value;
            });

        $this->appendCell(new Cell\Text('userId'))->setLabel('Creator')
            ->addOnPropertyValue(function (Cell\Text $cell, \App\Db\PathCase $obj, $value) {
                $value = '';
                $user = $obj->getUser();
                if ($user) {
                    $value = $user->getName();
                }
                return $value;
            });
        $this->appendCell(new Cell\Text('clientId'))
            ->addOnPropertyValue(function (Cell\Text $cell, \App\Db\PathCase $obj, $value) {
                $value = '';
                $user = $obj->getClient();
                if ($user) {
                    $value = $user->getName();
                }
                return $value;
            });
        $this->appendCell(new Cell\Text('type'));
        $this->appendCell(new Cell\Text('submissionType'));
        $this->appendCell(new Cell\Text('status'));

        $this->appendCell(new Cell\Text('accountStatus'));
        $this->appendCell(new Cell\Boolean('billable'));
        $this->appendCell(new Cell\Text('cost'));
        $this->appendCell(new Cell\Boolean('submissionReceived'));
        $this->appendCell(new Cell\Boolean('afterHours'));

        $this->appendCell(new Cell\Text('zootonic'));
        $this->appendCell(new Cell\Text('issue'));
        $this->appendCell(new Cell\Text('specimenCount'));
        $this->appendCell(new Cell\Text('animalName'));
        $this->appendCell(new Cell\Text('animalTypeId'))
            ->addOnPropertyValue(function (Cell\Text $cell, \App\Db\PathCase $obj, $value) {
                $o = $obj->getAnimalType();
                $value = '';
                if ($o) {
                    $value = $o->getName();
                }
                return $value;
            });
        $this->appendCell(new Cell\Text('species'));
        //$this->appendCell(new Cell\Text('breed'));
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
        $this->appendCell(new Cell\Text('colour'));
        $this->appendCell(new Cell\Text('weight'));
        $this->appendCell(new Cell\Text('size'));
        $this->appendCell(new Cell\Date('dob'));
        $this->appendCell(new Cell\Date('dod'));
        $this->appendCell(new Cell\Text('age'))
            ->setOrderProperty('b.age')
            ->addOnPropertyValue(function (Cell\Text $cell, \App\Db\PathCase $obj, $value) {
                $value = sprintf('%s.%s', $obj->getAge(), $obj->getAgeMonths());
                return $value;
            });
        $this->appendCell(new Cell\Boolean('euthanised'));
        $this->appendCell(new Cell\Html('morphologicalDiagnosis'));
        $this->appendCell(new Cell\Text('euthanisedMethod'));
        $this->appendCell(new Cell\Text('acType'));
        $this->appendCell(new Cell\Date('acHold'));
        //$this->appendCell(new Cell\Text('storageId'));
        $this->appendCell(new Cell\Date('disposal'));

        $this->appendCell(new Cell\Date('arrival'));

        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        $this->appendFilter(new Field\Input('age'))->setAttr('placeholder', 'Age');

        $list = $this->getConfig()->getUserMapper()->findFiltered(array(
            'institutionId' => $this->getConfig()->getInstitutionId(),
            'type' => User::TYPE_STAFF
        ), Tool::create('name_first, name_last'));
        
        $this->appendFilter(Field\Select::createSelect('pathologistId', $list)->prependOption('-- Pathologist --'));

//        $list = $this->getConfig()->getUserMapper()->findFiltered(array(
//            'institutionId' => $this->getConfig()->getInstitutionId(),
//            'type' => User::TYPE_STAFF
//        ));
        $this->appendFilter(Field\Select::createSelect('userId', $list)->prependOption('-- Creator --'));


        $list = ContactMap::create()->findFiltered(array(
            'institutionId' => $this->getConfig()->getInstitutionId(),
            'type' => \App\Db\Contact::TYPE_CLIENT
        ), Tool::create('name_company, name_first, name_last'));
        $this->appendFilter(Field\Select::createSelect('clientId', $list))//; //->prependOption('-- Submitter/Client --'))
            ->addCss('tk-multiselect1')->prependOption('-- Submitter/Client --')
            ->addOnShowOption(function (\Dom\Template $template, \Tk\Form\Field\Option $option, $var) {
                /** @var \App\Db\Contact $contact */
                $contact = ContactMap::create()->find($option->getValue());
                if ($contact)
                    $option->setName($contact->getDisplayName());
            });

        $list = ContactMap::create()->findFiltered(array(
            'institutionId' => $this->getConfig()->getInstitutionId(),
            'type' => \App\Db\Contact::TYPE_OWNER
        ), Tool::create('name_first, name_last'));
        $this->appendFilter(Field\Select::createSelect('ownerId', $list))
            ->addCss('tk-multiselect2')->prependOption('-- Owner --')
            ->addOnShowOption(function (\Dom\Template $template, \Tk\Form\Field\Option $option, $var) {
                /** @var \App\Db\Contact $contact */
                $contact = ContactMap::create()->find($option->getValue());
                if ($contact)
                    $option->setName($contact->getDisplayName());
            });

        $js = <<<JS
jQuery(function ($) {
  	$('select.tk-multiselect1').select2({
        placeholder: '-- Submitter/Client --',
        allowClear: false,
        minimumInputLength: 0
    });
  	$('select.tk-multiselect2').select2({
        placeholder: '-- Owner --',
        allowClear: false,
        minimumInputLength: 0
    });
});
JS;
        $this->getRenderer()->getTemplate()->appendJs($js);

        $list = \Tk\ObjectUtil::getClassConstants(\App\Db\PathCase::class, 'TYPE', true);
        $this->appendFilter(Field\Select::createSelect('type', $list)->prependOption('-- Case Type --'));
        $list = \Tk\ObjectUtil::getClassConstants(\App\Db\PathCase::class, 'SUBMISSION', true);
        $this->appendFilter(Field\Select::createSelect('submissionType', $list)->prependOption('-- Submission Type --'));

        $list = \Tk\ObjectUtil::getClassConstants(\App\Db\PathCase::class, 'STATUS', true);
        $this->appendFilter(Field\CheckboxSelect::createSelect('status', $list));

        // Species Filter
        $list = AnimalTypeMap::create()->findFiltered(['institutionId' => $this->getConfig()->getInstitutionId(), 'parent_id' => 0]);
        $this->appendFilter(Field\Select::createSelect('animalTypeId', $list)->prependOption('-- Animal Type --', ''));

        $list = array(
            'Extra-small < 1kg' => 'Extra-small < 1kg',
            'Small < 10kg' => 'Small < 10kg',
            'Medium < 50kg' => 'Medium < 50kg',
            'Large < 200kg' => 'Large < 200kg',
            'Extra-large > 200kg' => 'Extra-large > 200kg');
        $this->appendFilter(Field\Select::createSelect('size', $list)->prependOption('-- Size --'));

        $speciesList = PathCaseMap::create()->findSpeciesList();
        $this->appendFilter(Field\Select::createSelect('species', $speciesList)->prependOption('-- Species --'));

        // Breed Filter
//        $breedList = PathCaseMap::create()->findBreedList();
//        $this->appendFilter(Field\Select::createSelect('breed', $breedList)->prependOption('-- Breed --'));
        // TODO: create an auto update JS when the species is selected repopulate the breed select options.

        $list = array('Yes' => '1', 'No' => '0');
        $this->appendFilter(Field\Select::createSelect('isDisposed', $list)->prependOption('-- Is Disposed --', ''));

        $list = \Tk\ObjectUtil::getClassConstants(\App\Db\PathCase::class, 'AC_');
        $this->appendFilter(Field\Select::createSelect('acType', $list)->prependOption('-- Method Of Disposal --', ''));

        $list = array('Yes' => '1', 'No' => '0');
        $this->appendFilter(Field\Select::createSelect('billable', $list)->prependOption('-- Is Billable --', ''));

        $this->appendFilter(new Field\DateRange('arrival'));

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Path Case', \Bs\Uri::createHomeUrl('/pathCaseEdit.html'), 'fa fa-plus'));
        //$this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified')));
        $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setSelected(
                array('id', 'pathologyId', 'pathologistId', 'clientId', 'owner', 'age',
                    'patientNumber', 'type', 'submissionType', 'status', 'arrival')
            )
        );
        $this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());
        $this->appendAction(\App\Table\Action\Status::create(\App\Db\PathCase::getStatusList()));

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