<?php
namespace App\Table;

use App\Db\MailTemplateEventMap;
use App\Db\MailTemplateMap;
use Tk\Form\Field;
use Tk\Table\Cell;

/**
 * Example:
 * <code>
 *   $table = new MailTemplate::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2020-08-17
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class MailTemplate extends \Bs\TableIface
{

    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {

        $this->appendCell(new Cell\Checkbox('id'));
        $this->appendCell(new Cell\Text('id'));
        $this->appendCell(new Cell\Text('mailTemplateEventId'))->setLabel('Mail Event')->addCss('key')->setUrl($this->getEditUrl())->addOnPropertyValue(
            function (\Tk\Table\Cell\Iface $cell, $obj, $value) {
                /** @var \App\Db\MailTemplateEvent $mEvent */
                $mEvent = MailTemplateEventMap::create()->find($value);
                if ($mEvent) {
                    $value = $mEvent->getName();
                }
                return $value;
            }
        );
        $this->appendCell(new Cell\Text('recipientType'));
        $this->appendCell(new Cell\Boolean('active'));
        $this->appendCell(new Cell\Date('modified'));
        $this->appendCell(new Cell\Date('created'));

        // Filters
        $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Mail Template', \Bs\Uri::createHomeUrl('/mail/templateEdit.html'), 'fa fa-plus'));
        $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified', 'created')));
        $this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());

        // load table
        //$this->setList($this->findList());

        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\App\Db\MailTemplate[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\MailTemplateMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}