<?php
namespace App\Table;

use App\Db\AnimalTypeMap;
use App\Db\CompanyMap;
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
        $this->appendCell(new Cell\Text('pathologyId'))->setLabel('Pathology #')
            ->addCss('key')->setOrderProperty('s.pathIdx')->setUrl($this->getEditUrl());
        $this->appendCell(new Cell\Text('type'));

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
        $this->appendCell(new Cell\Text('companyId'))
            ->setLabel('Client')
            ->addOnPropertyValue(function (Cell\Text $cell, \App\Db\PathCase $obj, $value) {
                $value = '';
                $user = $obj->getCompany();
                if ($user) {
                    $value = $user->getName();
                }
                return $value;
            });
        $this->appendCell(new Cell\Text('contacts'))
            ->setLabel('Client Contacts')
            ->addOnCellHtml(function (\Tk\Table\Cell\Iface $cell, $obj, $html) {
                $list = $obj->getContactList(Tool::create('name'));
                if ($list->count()) {
                    $html .= implode('<br/>', $list->toArray('name'));
                }
                return $html;
            });
        $this->appendCell(new Cell\Text('submissionType'));
        $this->appendCell(new Cell\Text('status'));

        $this->appendCell(new Cell\Text('accountStatus'));
        $this->appendCell(new Cell\Boolean('billable'));
        $this->appendCell(new Cell\Text('cost'))->addOnPropertyValue(function (Cell\Text $cell, \App\Db\PathCase $obj, $value) {
            return $obj->getInvoiceTotal();
        });
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
        $this->appendCell(new Cell\Text('sex'));
        $this->appendCell(new Cell\Boolean('desexed'));
        $this->appendCell(new Cell\Text('patientNumber'));
        $this->appendCell(new Cell\Text('microchip'));
        $this->appendCell(new Cell\Text('ownerName'));
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
        $this->appendCell(new Cell\Text('disposeMethod'));
        $this->appendCell(new Cell\Date('acHold'));
        //$this->appendCell(new Cell\Text('storageId'));
        $this->appendCell(new Cell\Date('disposeOn'));

        $this->appendCell(new Cell\Date('arrival'));

        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');
        $this->appendFilter(new Field\Input('age'))->setAttr('placeholder', 'Age');
        $list = $this->getConfig()->getUserMapper()->findFiltered([
            'institutionId' => $this->getConfig()->getInstitutionId(),
            'type' => User::TYPE_STAFF,
            'active' => true
        ], Tool::create('active DESC, name_first, name_last'));
        
        $this->appendFilter(Field\Select::createSelect('pathologistId', $list)->prependOption('-- Pathologist --'));

        $this->appendFilter(Field\Select::createSelect('userId', $list)->prependOption('-- Creator --'));


        $list = CompanyMap::create()->findFiltered([
            'institutionId' => $this->getConfig()->getInstitutionId(),
        ], Tool::create('name'));
        $this->appendFilter(Field\Select::createSelect('companyId', $list))//; //->prependOption('-- Submitter/Client --'))
            ->addCss('tk-multiselect1')->prependOption('-- Submitter/Client --');

        $js = <<<JS
jQuery(function ($) {
  	$('select.tk-multiselect1').select2({
        placeholder: '-- Submitter/Client --',
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
        $this->appendFilter(Field\Select::createSelect('species', PathCaseMap::create()->findSpeciesList())->prependOption('-- Species --'));
        $this->appendFilter(Field\Select::createSelect('isDisposable', ['Yes' => '1', 'No' => '0'])->prependOption('-- Is Disposable --', ''));
        $this->appendFilter(Field\Select::createSelect('disposeMethod', \App\Db\PathCase::DISPOSAL_METHOD_LIST)->prependOption('-- Method Of Disposal --', ''));
        $this->appendFilter(Field\Select::createSelect('billable', ['Yes' => '1', 'No' => '0'])->prependOption('-- Is Billable --', ''));
        $this->appendFilter(Field\Select::createSelect('accountStatus', \App\Db\PathCase::ACCOUNT_STATUS_LIST)->prependOption('-- Account Status --', ''));

        $this->appendFilter(new Field\DateRange('arrival'));

        // Actions
        $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setSelected(
                array('id', 'pathologyId', 'pathologistId', 'companyId', 'contacts', 'owner', 'age',
                    'patientNumber', 'type', 'submissionType', 'status', 'arrival')
            )
        );
        $this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());
        $this->appendAction(\App\Table\Action\Status::create(\App\Db\PathCase::getStatusList()));

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
        return \App\Db\PathCaseMap::create()->findFiltered($filter, $tool);
    }

}