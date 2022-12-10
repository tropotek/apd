<?php
namespace App\Db;

use Tk\Date;
use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;
use Bs\Db\Mapper;
use Tk\Db\Filter;

/**
 * @author Mick Mifsud
 * @created 2021-01-27
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class InvoiceItemMap extends Mapper
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
            $this->dbMap->addPropertyMap(new Db\Text('code'));
            $this->dbMap->addPropertyMap(new Db\Text('description'));
            $this->dbMap->addPropertyMap(new Db\Decimal('qty'));
            $this->dbMap->addPropertyMap(new Db\Money('price'));
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
            $this->formMap->addPropertyMap(new Form\Text('code'));
            $this->formMap->addPropertyMap(new Form\Text('description'));
            $this->formMap->addPropertyMap(new Form\Decimal('qty'));
            $this->formMap->addPropertyMap(new Form\Money('price'));
        }
        return $this->formMap;
    }

    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|InvoiceItem[]
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
            $w .= sprintf('a.code LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.description LIKE %s OR ', $this->quote($kw));
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
        if (!empty($filter['code'])) {
            $filter->appendWhere('a.code = %s AND ', $this->quote($filter['code']));
        }
        if (!empty($filter['qty'])) {
            $filter->appendWhere('a.qty = %s AND ', (float)$filter['qty']);
        }
        if (!empty($filter['price'])) {
            $filter->appendWhere('a.price = %s AND ', (float)$filter['price']);
        }


        $dates = array('createdStart', 'createdEnd');
        foreach ($dates as $name) {
            if (!empty($filter[$name]) && !$filter[$name] instanceof \DateTime) {
                $filter[$name] = Date::createFormDate($filter[$name]);
            }
        }
        if (!empty($filter['createdStart'])) {
            /** @var \DateTime $date */
            $date = Date::floor($filter['createdStart']);
            $filter->appendWhere('a.created >= %s AND ', $this->quote($date->format(Date::FORMAT_ISO_DATETIME)));
        }
        if (!empty($filter['createdEnd'])) {
            /** @var \DateTime $date */
            $date = Date::ceil($filter['createdEnd']);
            $filter->appendWhere('a.created <= %s AND ', $this->quote($date->format(Date::FORMAT_ISO_DATETIME)));
        }


        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }

}