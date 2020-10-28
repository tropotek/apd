<?php

namespace App\Db;

use Bs\Db\Mapper;
use Exception;
use Tk\DataMap\DataMap;
use Tk\DataMap\Db;
use Tk\DataMap\Form;
use Tk\Db\Filter;
use Tk\Db\Map\ArrayObject;
use Tk\Db\ModelInterface;
use Tk\Db\Tool;

/**
 * @author Mick Mifsud
 * @created 2019-05-21
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class NoteMap extends Mapper
{

    /**
     * @return DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) {
            $this->dbMap = new DataMap();
            $this->dbMap->addPropertyMap(new Db\Integer('id'), 'key');
            $this->dbMap->addPropertyMap(new Db\Integer('userId', 'user_id'));
            $this->dbMap->addPropertyMap(new Db\Text('fkey'));
            $this->dbMap->addPropertyMap(new Db\Integer('fid'));
            $this->dbMap->addPropertyMap(new Db\Text('message'));
            $this->dbMap->addPropertyMap(new Db\Text('data'));
            $this->dbMap->addPropertyMap(new Db\Date('modified'));
            $this->dbMap->addPropertyMap(new Db\Date('created'));

        }
        return $this->dbMap;
    }

    /**
     * @return DataMap
     */
    public function getFormMap()
    {
        if (!$this->formMap) {
            $this->formMap = new DataMap();
            $this->formMap->addPropertyMap(new Form\Integer('id'), 'key');
            $this->formMap->addPropertyMap(new Form\Integer('userId'));
            $this->formMap->addPropertyMap(new Form\Text('fkey'));
            $this->formMap->addPropertyMap(new Form\Integer('fid'));
            $this->formMap->addPropertyMap(new Form\Text('message'));
            $this->formMap->addPropertyMap(new Form\Text('data'));
            $this->formMap->addPropertyMap(new Form\Date('modified'));
            $this->formMap->addPropertyMap(new Form\Date('created'));

        }
        return $this->formMap;
    }


    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|Note[]
     * @throws Exception
     */
    public function findFiltered($filter, $tool = null)
    {
        return $this->selectFromFilter($this->makeQuery(Filter::create($filter)), $tool);
    }

    /**
     * @param Filter $filter
     * @return Filter
     */
    public function makeQuery(Filter $filter)
    {
        $filter->appendFrom('%s a ', $this->quoteParameter($this->getTable()));

        if (!empty($filter['keywords'])) {
            $kw = '%' . $this->getDb()->escapeString($filter['keywords']) . '%';
            $w = '';
            //$w .= sprintf('a.name LIKE %s OR ', $this->getDb()->quote($kw));
            if (is_numeric($filter['keywords'])) {
                $id = (int)$filter['keywords'];
                $w .= sprintf('a.id = %d OR ', $id);
            }
            if ($w) $filter->appendWhere('(%s) AND ', substr($w, 0, -3));
        }

        if (!empty($filter['id'])) {
            $w = $this->makeMultiQuery($filter['id'], 'a.id');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }
        if (!empty($filter['userId'])) {
            $filter->appendWhere('a.user_id = %s AND ', (int)$filter['userId']);
        }

        if (!empty($filter['model']) && $filter['model'] instanceof ModelInterface) {
            $filter['fid'] = $filter['model']->getId();
            $filter['fkey'] = get_class($filter['model']);
        }
        if (!empty($filter['fkey'])) {
            $filter->appendWhere('a.fkey = %s AND ', $this->quote($filter['fkey']));
        }
        if (!empty($filter['fid'])) {
            $filter->appendWhere('a.fid = %s AND ', (int)$filter['fid']);
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }

}
