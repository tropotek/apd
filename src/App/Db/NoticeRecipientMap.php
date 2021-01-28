<?php
namespace App\Db;

use Tk\Db\Map\Model;
use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;
use Bs\Db\Mapper;
use Tk\Db\Filter;
use Tk\Exception;

/**
 * @author Mick Mifsud
 * @created 2021-01-28
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class NoticeRecipientMap extends Mapper
{

    /**
     * @return \Tk\DataMap\DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) { 
            $this->dbMap = new \Tk\DataMap\DataMap();
            $this->dbMap->addPropertyMap(new Db\Integer('id'), 'key');
            $this->dbMap->addPropertyMap(new Db\Integer('noticeId', 'notice_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('userId', 'user_id'));
            $this->dbMap->addPropertyMap(new Db\Date('viewed'));
            $this->dbMap->addPropertyMap(new Db\Date('read'));

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
            $this->formMap->addPropertyMap(new Form\Integer('noticeId'));
            $this->formMap->addPropertyMap(new Form\Integer('userId'));
            $this->formMap->addPropertyMap(new Form\Date('viewed'));
            $this->formMap->addPropertyMap(new Form\Date('read'));

        }
        return $this->formMap;
    }

    /**
     * @param $noticeId
     * @param $userId
     * @return Model|NoticeRecipient
     * @throws \Exception
     */
    public function findRecipient($noticeId, $userId)
    {
        $filter = $this->makeQuery(Filter::create(array(
            'noticeId' => $noticeId,
            'userId' => $userId
        )));
        $res = $this->selectFromFilter($filter);
        return $res->current();
    }

    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|NoticeRecipient[]
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
            //$w .= sprintf('a.name LIKE %s OR ', $this->quote($kw));
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

        if (isset($filter['noticeId'])) {
            $filter->appendWhere('a.notice_id = %s AND ', (int)$filter['noticeId']);
        }
        if (isset($filter['userId'])) {
            $filter->appendWhere('a.user_id = %s AND ', (int)$filter['userId']);
        }
        if (isset($filter['viewed']) && $filter['viewed'] !== '' && $filter['viewed'] !== null) {
            if ($filter['viewed']) {
                $filter->appendWhere('a.viewed IS NOT NULL AND ');
            } else {
                $filter->appendWhere('a.viewed IS NULL AND ');
            }
        }
        if (isset($filter['read']) && $filter['read'] !== '' && $filter['read'] !== null) {
            if ($filter['read']) {
                $filter->appendWhere('a.read IS NOT NULL AND ');
            } else {
                $filter->appendWhere('a.read IS NULL AND ');
            }
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }

}