<?php
namespace App\Db;

use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;
use Bs\Db\Mapper;
use Tk\Db\Filter;

/**
 * @author Mick Mifsud
 * @created 2022-12-10
 * @link http://tropotek.com.au/
 * @license Copyright 2022 Tropotek
 */
class ProductMap extends Mapper
{

    /**
     * @return \Tk\DataMap\DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) { 
            $this->dbMap = new \Tk\DataMap\DataMap();
            $this->dbMap->addPropertyMap(new Db\Integer('id'), 'key');
            $this->dbMap->addPropertyMap(new Db\Integer('institutionId', 'institution_id'));
            $this->dbMap->addPropertyMap(new Db\Text('name'));
            $this->dbMap->addPropertyMap(new Db\Text('code'));
            $this->dbMap->addPropertyMap(new Db\Money('price'));
            $this->dbMap->addPropertyMap(new Db\Text('description'));
            $this->dbMap->addPropertyMap(new Db\Date('modified'));
            $this->dbMap->addPropertyMap(new Db\Date('created'));
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
            $this->formMap->addPropertyMap(new Form\Integer('institutionId'));
            $this->formMap->addPropertyMap(new Form\Text('name'));
            $this->formMap->addPropertyMap(new Form\Text('code'));
            $this->formMap->addPropertyMap(new Form\Money('price'));
            $this->formMap->addPropertyMap(new Form\Text('description'));
        }
        return $this->formMap;
    }

    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|Product[]
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
            $w .= sprintf('a.name LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.code LIKE %s OR ', $this->quote($kw));
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

        if (!empty($filter['institutionId'])) {
            $filter->appendWhere('a.institution_id = %s AND ', (int)$filter['institutionId']);
        }
        if (!empty($filter['name'])) {
            $filter->appendWhere('a.name = %s AND ', $this->quote($filter['name']));
        }
        if (!empty($filter['code'])) {
            $filter->appendWhere('a.code = %s AND ', $this->quote($filter['code']));
        }

        if (!empty($filter['del'])) {
            $filter->appendWhere('a.del = %s AND ', (int)$filter['del']);
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }

}