<?php
namespace App\Table;

use App\Db\PathCaseMap;
use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new CompanyContact::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 */
class CompanyContact extends \Bs\TableIface
{

    /**
     * @var null|\App\Db\Company
     */
    protected $company = null;

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

        // Actions
        //TODO: use a dialog for add/edit
        $this->appendAction(\Tk\Table\Action\Link::createLink('New Contact', $this->getEditUrl(), 'fa fa-plus'));
        $this->appendAction(\Tk\Table\Action\Delete::create()->addOnDelete(
            function (\Tk\Table\Action\Delete $action, $obj) {
                PathCaseMap::create()->removeContact(null, $obj->getId());
            }
        ));

        $this->getRenderer()->getFootRenderer('Pager')->setSmall(true);

        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\App\Db\CompanyContact[]
     * @throws \Exception
     */
    public function findList($filter = [], $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\CompanyContactMap::create()->findFiltered($filter, $tool);
        return $list;
    }

    public function getCompany(): ?\App\Db\Company
    {
        return $this->company;
    }

    public function setCompany(?\App\Db\Company $company): CompanyContact
    {
        $this->company = $company;
        return $this;
    }

}