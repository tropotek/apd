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
class StorageMap extends Mapper
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
            $this->dbMap->addPropertyMap(new Db\Integer('addressId', 'address_id'));
            $this->dbMap->addPropertyMap(new Db\Text('uid'));
            $this->dbMap->addPropertyMap(new Db\Text('name'));
            $this->dbMap->addPropertyMap(new Db\Decimal('mapZoom', 'map_zoom'));
            $this->dbMap->addPropertyMap(new Db\Decimal('mapLng', 'map_lng'));
            $this->dbMap->addPropertyMap(new Db\Decimal('mapLat', 'map_lat'));
            $this->dbMap->addPropertyMap(new Db\Text('notes'));

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
            $this->formMap->addPropertyMap(new Form\Integer('addressId'));
            $this->formMap->addPropertyMap(new Form\Text('uid'));
            $this->formMap->addPropertyMap(new Form\Text('name'));
            $this->formMap->addPropertyMap(new Form\Decimal('mapZoom'));
            $this->formMap->addPropertyMap(new Form\Decimal('mapLng'));
            $this->formMap->addPropertyMap(new Form\Decimal('mapLat'));
            $this->formMap->addPropertyMap(new Form\Text('notes'));

        }
        return $this->formMap;
    }

    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|Storage[]
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

        if (!empty($filter['institutionId'])) {
            $filter->appendWhere('a.institution_id = %s AND ', (int)$filter['institutionId']);
        }
        if (!empty($filter['addressId'])) {
            $filter->appendWhere('a.address_id = %s AND ', (int)$filter['addressId']);
        }
        if (!empty($filter['uid'])) {
            $filter->appendWhere('a.uid = %s AND ', $this->quote($filter['uid']));
        }
        if (!empty($filter['name'])) {
            $filter->appendWhere('a.name = %s AND ', $this->quote($filter['name']));
        }
        if (!empty($filter['mapZoom'])) {
            $filter->appendWhere('a.map_zoom = %s AND ', (float)$filter['mapZoom']);
        }
        if (!empty($filter['mapLng'])) {
            $filter->appendWhere('a.map_lng = %s AND ', (float)$filter['mapLng']);
        }
        if (!empty($filter['mapLat'])) {
            $filter->appendWhere('a.map_lat = %s AND ', (float)$filter['mapLat']);
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }

}