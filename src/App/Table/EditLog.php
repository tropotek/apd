<?php
namespace App\Table;

use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new EditLog::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2021-06-02
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class EditLog extends \Bs\TableIface
{

    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {


        $this->getActionCell(true)->append(\Tk\Table\Ui\ActionButton::createBtn('View', $this->getEditUrl(), 'fa fa-eye')->setAttr('target', '_blank'))
            ->setGroup('edit')->addOnShow(function (\Tk\Table\Cell\Iface $cell, \App\Db\EditLog $obj, \Tk\Table\Ui\ActionButton $button) {
                $button->setUrl($button->getUrl()->set('editLogId', $obj->getId()));
            });
        // TODO: use the PathCase view not report PDF
        $url = clone $this->getEditUrl();
        $this->getActionCell(true)->append(\Tk\Table\Ui\ActionButton::createBtn('PDF', $url->set('pdf-view'), 'fa fa-file-pdf-o')->setAttr('target', '_blank'))
            ->setGroup('edit')->addOnShow(function (\Tk\Table\Cell\Iface $cell, \App\Db\EditLog $obj, \Tk\Table\Ui\ActionButton $button) {
                $button->setUrl($button->getUrl()->set('editLogId', $obj->getId()));
            });
        $this->appendCell($this->getActionCell());

        //$this->appendCell(new Cell\Checkbox('id'));
        $this->appendCell(new Cell\Text('userId'))->addCss('key')->setUrl($this->getEditUrl())
            ->addOnPropertyValue(function (Cell\Text $cell, \App\Db\EditLog $obj, $value) {
                if ($obj->getUser()) {
                    $value = $obj->getUser()->getName();
                } else {
                    $value = '{Unknown}';
                }
                return $value;
            });
//        $this->appendCell(new Cell\Text('fkey'));
//        $this->appendCell(new Cell\Text('fid'));
        //$this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        //$this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Edit Log', \Bs\Uri::createHomeUrl('/edit/logEdit.html'), 'fa fa-plus'));
        //$this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified', 'created')));
        //$this->appendAction(\Tk\Table\Action\Delete::create());
        //$this->appendAction(\Tk\Table\Action\Csv::create());

        // load table
        //$this->setList($this->findList());

        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\App\Db\EditLog[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\EditLogMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}