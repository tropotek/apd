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
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class CassetteMap extends Mapper
{

    /**
     * @return \Tk\DataMap\DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) { 
            $this->dbMap = new \Tk\DataMap\DataMap();
            $this->dbMap->addPropertyMap(new Db\Integer('id'), 'key');
            $this->dbMap->addPropertyMap(new Db\Integer('pathCaseId', 'path_case_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('storageId', 'storage_id'));
            $this->dbMap->addPropertyMap(new Db\Text('container'));
            $this->dbMap->addPropertyMap(new Db\Text('number'));
            $this->dbMap->addPropertyMap(new Db\Text('name'));
            $this->dbMap->addPropertyMap(new Db\Integer('qty'));
            $this->dbMap->addPropertyMap(new Db\Decimal('price'));
            $this->dbMap->addPropertyMap(new Db\Text('comments'));
            $this->dbMap->addPropertyMap(new Db\Text('notes'));
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
            $this->formMap->addPropertyMap(new Form\Integer('pathCaseId'));
            $this->formMap->addPropertyMap(new Form\Integer('storageId'));
            $this->formMap->addPropertyMap(new Form\Text('container'));
            $this->formMap->addPropertyMap(new Form\Text('number'));
            $this->formMap->addPropertyMap(new Form\Text('name'));
            $this->formMap->addPropertyMap(new Form\Integer('qty'));
            $this->formMap->addPropertyMap(new Form\Decimal('price'));
            $this->formMap->addPropertyMap(new Form\Text('comments'));
            $this->formMap->addPropertyMap(new Form\Text('notes'));

        }
        return $this->formMap;
    }

    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|Cassette[]
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

        if (!empty($filter['id'])) {
            $w = $this->makeMultiQuery($filter['id'], 'a.id');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (!empty($filter['pathCaseId'])) {
            $filter->appendWhere('a.path_case_id = %s AND ', (int)$filter['pathCaseId']);
        }
        if (!empty($filter['storageId'])) {
            $filter->appendWhere('a.storage_id = %s AND ', (int)$filter['storageId']);
        }
        if (!empty($filter['container'])) {
            $filter->appendWhere('a.container = %s AND ', $this->quote($filter['container']));
        }
        if (!empty($filter['number'])) {
            $filter->appendWhere('a.number = %s AND ', $this->quote($filter['number']));
        }
        if (!empty($filter['name'])) {
            $filter->appendWhere('a.name = %s AND ', $this->quote($filter['name']));
        }
        if (!empty($filter['qty'])) {
            $filter->appendWhere('a.qty = %s AND ', (int)$filter['qty']);
        }
        if (!empty($filter['price'])) {
            $filter->appendWhere('a.price = %s AND ', (float)$filter['price']);
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }

}