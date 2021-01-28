<?php
namespace App\Db;

use Tk\Date;
use Tk\Db\Map\Model;
use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;
use Bs\Db\Mapper;
use Tk\Db\Filter;

/**
 * @author Mick Mifsud
 * @created 2021-01-28
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class NoticeMap extends Mapper
{

    /**
     * @return \Tk\DataMap\DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) { 
            $this->dbMap = new \Tk\DataMap\DataMap();
            $this->dbMap->addPropertyMap(new Db\Integer('id'), 'key');
            $this->dbMap->addPropertyMap(new Db\Integer('userId', 'user_id'));
            $this->dbMap->addPropertyMap(new Db\Text('fkey'));
            $this->dbMap->addPropertyMap(new Db\Integer('fid'));
            $this->dbMap->addPropertyMap(new Db\Text('type'));
            $this->dbMap->addPropertyMap(new Db\Text('subject'));
            $this->dbMap->addPropertyMap(new Db\Text('body'));
            $this->dbMap->addPropertyMap(new Db\JsonArray('param', 'data'));

        }
        return $this->dbMap;
    }

    /**
     * @return \Tk\DataMap\DataMap
     */
    public function getFormMap()
    {
        if (!$this->formMap) {
            $this->formMap = new \Tk\DataMap\DataMap();
            $this->formMap->addPropertyMap(new Form\Integer('id'), 'key');
            $this->formMap->addPropertyMap(new Form\Integer('userId'));
            $this->formMap->addPropertyMap(new Form\Text('fkey'));
            $this->formMap->addPropertyMap(new Form\Integer('fid'));
            $this->formMap->addPropertyMap(new Form\Text('type'));
            $this->formMap->addPropertyMap(new Form\Text('subject'));
            $this->formMap->addPropertyMap(new Form\Text('body'));
            $this->formMap->addPropertyMap(new Form\ObjectMap('param'));

        }
        return $this->formMap;
    }

    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|Notice[]
     * @throws \Exception
     */
    public function findFiltered($filter, $tool = null)
    {
        return $this->selectFromFilter($this->makeQuery(\Tk\Db\Filter::create($filter)), $tool);
    }

    /**
     * @param Filter $filter
     * @return Filter
     */
    public function makeQuery(Filter $filter)
    {
        $filter->appendFrom('%s a', $this->quoteParameter($this->getTable()));

        if (!empty($filter['keywords'])) {
            $kw = '%' . $this->escapeString($filter['keywords']) . '%';
            $w = '';
            $w .= sprintf('a.subject LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.body LIKE %s OR ', $this->quote($kw));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (isset($filter['id'])) {
            $w = $this->makeMultiQuery($filter['id'], 'a.id');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (isset($filter['userId'])) {
            $filter->appendWhere('a.user_id = %s AND ', (int)$filter['userId']);
        }

        if (isset($filter['recipientId'])) {
            $filter->appendFrom(', %s b', $this->quoteTable('notice_recipient'));
            $filter->appendWhere('a.id = b.notice_id AND ');
            $filter->appendWhere('b.user_id = %s AND ', (int)$filter['recipientId']);
            if (isset($filter['viewed'])) {
                if ($filter['viewed'] === true) {
                    $filter->appendWhere('b.viewed IS NOT NULL AND ');
                } else {
                    $filter->appendWhere('b.viewed IS NULL AND ');
                }
            }
        }

        if (!empty($filter['model']) && $filter['model'] instanceof Model) {
            $filter['fid'] = $filter['model']->getId();
            $filter['fkey'] = get_class($filter['model']);
        }
        if (!empty($filter['fkey'])) {
            $filter->appendWhere('a.fkey = %s AND ', $this->quote($filter['fkey']));
        }
        if (!empty($filter['fid'])) {
            $filter->appendWhere('a.fid = %s AND ', (int)$filter['fid']);
        }
        if (!empty($filter['type'])) {
            $filter->appendWhere('a.type = %s AND ', $this->quote($filter['type']));
        }
        if (!empty($filter['created'])) {
            $filter->appendWhere('a.created > %s AND ', $this->quote($filter['created']->format(Date::FORMAT_ISO_DATETIME)));
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }

}