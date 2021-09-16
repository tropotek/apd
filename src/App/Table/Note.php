<?php
namespace App\Table;

use Tk\Table\Cell;

/**
 * 
 * @author Mick Mifsud
 * @created 2018-09-24
 * @link http://tropotek.com.au/
 * @license Copyright 2018 Tropotek
 */
class Note extends \Uni\TableIface
{

    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {
        $this->getRenderer()->enableFooter(false);
        $this->getRenderer()->getTemplate()->appendCss('.tk-table td {
    white-space: normal;
}');

        $actionsCell = new \Tk\Table\Cell\Actions();
        $actionsCell->setLabel('');
        $actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Remove', \Tk\Uri::create(), 'fa fa-trash', 'btn-danger tk-remove'))
            ->addOnShow(function ($cell, $obj, $button) {
                /* @var $obj \App\Db\Note */
                /* @var $button \Tk\Table\Cell\ActionButton */
                $button->setAttr('data-confirm', 'Remove this note?');
                $config = \App\Config::getInstance();
                if ($obj->canDelete($config->getAuthUser())) {
                    $button->setUrl(\Tk\Uri::create()->set('del', $obj->getId()));
                } else {
                    $button->setVisible(false);
                }
            });
//        $actionsCell->addButton(\Tk\Table\Cell\ActionButton::create('Reminder', \Tk\Uri::create(), 'fa fa-bell-slash', 'btn-primary tk-reminder'))
//            ->addOnShow(function ($cell, $obj, $button) {
//                /* @var $cell Cell\Iface */
//                /* @var $obj \App\Db\Note */
//                /* @var $button \Tk\Table\Cell\ActionButton */
//                $button->setAttr('title', 'Remove the reminder');
//                $button->setAttr('data-confirm', 'Remove this reminder?');
//                $config = \App\Config::getInstance();
//                if ($obj->canDelete($config->getAuthUser()) && $obj->hasReminder()) {
//                    $button->setUrl(\Tk\Uri::create()->set('del-rem', $obj->getId()));
//                } else {
//                    $button->setVisible(false);
//                }
//            });


        //$this->appendCell(new Cell\Checkbox('id'));
        if ($actionsCell->hasButtons())
            $this->appendCell($actionsCell);

        $this->appendCell(new Cell\Text('message'))->addCss('key')->addOnCellHtml(function ($cell, $obj, $value) {
            /** @var Cell\Text $cell */
            /** @var \App\Db\Note $obj */
            $value = \Tk\Str::stripEntities(strip_tags($value));
            return nl2br($value);
        });
        $this->appendCell(new Cell\Text('userId'))->setLabel('From')->addOnPropertyValue(function ($cell, $obj, $value) {
            /** @var Cell\Text $cell */
            /** @var \App\Db\Note $obj */
            if ($obj->getUser()) {
                $value = $obj->getUser()->getName();
            } else {
                $value = '{Unknown}';
            }
            return $value;
        });
        $this->appendCell(Cell\Date::createDate('created', \Tk\Date::FORMAT_ISO_DATE))->addOnPropertyValue(function ($cell, $obj, $value) {
            /** @var Cell\Text $cell */
            /** @var \App\Db\Note $obj */
            if ($value instanceof \DateTime)
                $cell->setAttr('title', $value->format(\Tk\Date::FORMAT_ISO_DATETIME));
            return $value;
        });

        // Actions
        $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('created')));
        //$this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());

        
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $request = \App\Config::getInstance()->getRequest();

        if ($request->has('del')) {
            /** @var \App\Db\Note $note */
            $note = \App\Db\NoteMap::create()->find($request->get('del'));
            if ($note && $note->canDelete($this->getConfig()->getAuthUser()))  {
                \Tk\Alert::addSuccess('Note Successfully deleted.');
                $note->delete();
            }
            \Tk\Uri::create()->remove('del')->redirect();
        }

//        if ($request->has('del-rem')) {
//            /** @var \App\Db\Note $note */
//            $note = \App\Db\NoteMap::create()->find($request->get('del-rem'));
//            if ($note && $note->canDelete($this->getConfig()->getAuthUser()) && $note->hasReminder())  {
//                $noticeList = \App\Db\NoticeMap::create()->findFiltered(array('noteId' => $note->getId()));
//                foreach ($noticeList as $notice) {
//                    $notice->delete();
//                }
//            }
//            \Tk\Uri::create()->remove('del-rem')->redirect();
//        }
        parent::execute();
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\App\Db\Note[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool('created DESC');
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\NoteMap::create()->findFiltered($filter, $tool);
        return $list;
    }

}