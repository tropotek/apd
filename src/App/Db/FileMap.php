<?php

namespace App\Db;

use Exception;
use Tk\DataMap\DataMap;
use Tk\DataMap\Db;
use Tk\DataMap\Form;
use Tk\Db\Filter;
use Tk\Db\Map\ArrayObject;
use Tk\Db\Map\Model;
use Tk\Db\Pdo;
use Tk\Db\Tool;

/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class FileMap extends Mapper
{
    /**
     * Mapper constructor.
     *
     * @param Pdo|null $db
     * @throws Exception
     */
    public function __construct($db = null)
    {
        parent::__construct($db);
        $this->setMarkDeleted('');
    }

    /**
     * @return DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) {
            $this->dbMap = new DataMap();
            $this->dbMap->addPropertyMap(new Db\Integer('id'), 'key');
            $this->dbMap->addPropertyMap(new Db\Text('fkey'));
            $this->dbMap->addPropertyMap(new Db\Integer('fid'));
            $this->dbMap->addPropertyMap(new Db\Text('path'));
            $this->dbMap->addPropertyMap(new Db\Integer('bytes'));
            $this->dbMap->addPropertyMap(new Db\Text('mime'));
            $this->dbMap->addPropertyMap(new Db\Text('notes'));
            $this->dbMap->addPropertyMap(new Db\Text('hash'));
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
            $this->formMap->addPropertyMap(new Form\Text('fkey'));
            $this->formMap->addPropertyMap(new Form\Integer('fid'));
            $this->formMap->addPropertyMap(new Form\Text('path'));
            $this->formMap->addPropertyMap(new Form\Integer('bytes'));
            $this->formMap->addPropertyMap(new Form\Text('mime'));
            $this->formMap->addPropertyMap(new Form\Text('notes'));
        }
        return $this->formMap;
    }


    /**
     * @param string $hash
     * @return File|Model|null
     * @throws Exception
     */
    public function findByHash($hash)
    {
        return $this->findFiltered(array('hash' => $hash))->current();
    }


    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|File[]
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
            $w .= sprintf('a.path LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.mime LIKE %s OR ', $this->quote($kw));
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

        if (!empty($filter['path'])) {
            $filter->appendWhere('a.path = %s AND ', $this->quote($filter['path']));
        }
        if (!empty($filter['mime'])) {
            $filter->appendWhere('a.mime = %s AND ', $this->quote($filter['mime']));
        }
        if (!empty($filter['hash'])) {
            $filter->appendWhere('a.hash = %s AND ', $this->quote($filter['hash']));
        }

        if (!empty($filter['model']) && $filter['model'] instanceof Model) {
            $filter['fid'] = $filter['model']->getId();
            $filter['fkey'] = get_class($filter['model']);
        }
        if (isset($filter['fid'])) {
            $filter->appendWhere('a.fid = %d AND ', (int)$filter['fid']);
        }
        if (isset($filter['fkey'])) {
            $filter->appendWhere('a.fkey = %s AND ', $this->quote($filter['fkey']));
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }

}