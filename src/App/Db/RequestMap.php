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
 * @created 2020-07-30
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class RequestMap extends Mapper
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
            $this->dbMap->addPropertyMap(new Db\Integer('cassetteId', 'cassette_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('serviceId', 'service_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('clientId', 'client_id'));
            $this->dbMap->addPropertyMap(new Db\Text('status'));
            $this->dbMap->addPropertyMap(new Db\Integer('qty'));
            $this->dbMap->addPropertyMap(new Db\Money('cost'));
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
            $this->formMap->addPropertyMap(new Form\Integer('cassetteId'));
            $this->formMap->addPropertyMap(new Form\Integer('serviceId'));
            $this->formMap->addPropertyMap(new Form\Integer('clientId'));
            $this->formMap->addPropertyMap(new Form\Text('status'));
            $this->formMap->addPropertyMap(new Form\Integer('qty'));
            $this->formMap->addPropertyMap(new Form\Money('cost'));
            $this->formMap->addPropertyMap(new Form\Text('comments'));
            $this->formMap->addPropertyMap(new Form\Text('notes'));

        }
        return $this->formMap;
    }

    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|Request[]
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

        if (isset($filter['pathCaseId'])) {
            $filter->appendWhere('a.path_case_id = %s AND ', (int)$filter['pathCaseId']);
        }
        if (isset($filter['cassetteId'])) {
            $filter->appendWhere('a.cassette_id = %s AND ', (int)$filter['cassetteId']);
        }
        if (isset($filter['serviceId'])) {
            $filter->appendWhere('a.service_id = %s AND ', (int)$filter['serviceId']);
        }
        if (isset($filter['clientId'])) {
            $filter->appendWhere('a.client_id = %s AND ', (int)$filter['clientId']);
        }
        if (!empty($filter['status'])) {
            $filter->appendWhere('a.status = %s AND ', $this->quote($filter['status']));
        }
        if (!empty($filter['qty'])) {
            $filter->appendWhere('a.qty = %s AND ', (int)$filter['qty']);
        }
        if (!empty($filter['cost'])) {
            $filter->appendWhere('a.cost = %s AND ', (float)$filter['cost']);
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }

}