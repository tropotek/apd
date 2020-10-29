<?php
namespace App\Table;

use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new Client::create();
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
class Contact extends \Bs\TableIface
{

    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {

        $this->appendCell(new Cell\Checkbox('id'));
        $this->appendCell(new Cell\Text('name'))->addCss('key')->setUrl($this->getEditUrl());
        //$this->appendCell(new Cell\Text('userId'));
        $this->appendCell(new Cell\Text('type'));
        //$this->appendCell(new Cell\Text('uid'));
        $this->appendCell(new Cell\Text('email'));
        $this->appendCell(new Cell\Text('billingEmail'));
        $this->appendCell(new Cell\Text('phone'));
        $this->appendCell(new Cell\Text('fax'));
        //$this->appendCell(new Cell\Text('addressId'));
        //$this->appendCell(new Cell\Text('billingAddressId'));

        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        if (!$this->getConfig()->getRequest()->query->has('type')) {
            $list = \App\Db\Contact::getTypeList();
            $this->appendFilter(new Field\Select('type', $list))
                ->setRequired()->prependOption('-- Contact Type --', '');
        }

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Client', \Bs\Uri::createHomeUrl('/clientEdit.html'), 'fa fa-plus'));
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
     * @return \Tk\Db\Map\ArrayObject|\App\Db\Contact[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\ContactMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}