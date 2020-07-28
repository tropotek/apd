<?php
namespace App\Db;

use Tk\Db\Pdo;
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
class AddressMap extends Mapper
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
     * @return \Tk\DataMap\DataMap
     */
    public function getDbMap()
    {
        if (!$this->dbMap) { 
            $this->dbMap = new \Tk\DataMap\DataMap();
            $this->dbMap->addPropertyMap(new Db\Integer('id'), 'key');
            $this->dbMap->addPropertyMap(new Db\Integer('institutionId', 'institution_id'));
            $this->dbMap->addPropertyMap(new Db\Text('number'));
            $this->dbMap->addPropertyMap(new Db\Text('street'));
            $this->dbMap->addPropertyMap(new Db\Text('city'));
            $this->dbMap->addPropertyMap(new Db\Text('country'));
            $this->dbMap->addPropertyMap(new Db\Text('state'));
            $this->dbMap->addPropertyMap(new Db\Text('postcode'));
            $this->dbMap->addPropertyMap(new Db\Text('address'));
            $this->dbMap->addPropertyMap(new Db\Decimal('mapZoom', 'map_zoom'));
            $this->dbMap->addPropertyMap(new Db\Decimal('mapLng', 'map_lng'));
            $this->dbMap->addPropertyMap(new Db\Decimal('mapLat', 'map_lat'));

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
            $this->formMap->addPropertyMap(new Form\Text('number'));
            $this->formMap->addPropertyMap(new Form\Text('street'));
            $this->formMap->addPropertyMap(new Form\Text('city'));
            $this->formMap->addPropertyMap(new Form\Text('country'));
            $this->formMap->addPropertyMap(new Form\Text('state'));
            $this->formMap->addPropertyMap(new Form\Text('postcode'));
            $this->formMap->addPropertyMap(new Form\Text('address'));
            $this->formMap->addPropertyMap(new Form\Decimal('mapZoom'));
            $this->formMap->addPropertyMap(new Form\Decimal('mapLng'));
            $this->formMap->addPropertyMap(new Form\Decimal('mapLat'));

        }
        return $this->formMap;
    }

    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|Address[]
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
        if (!empty($filter['number'])) {
            $filter->appendWhere('a.number = %s AND ', $this->quote($filter['number']));
        }
        if (!empty($filter['street'])) {
            $filter->appendWhere('a.street = %s AND ', $this->quote($filter['street']));
        }
        if (!empty($filter['city'])) {
            $filter->appendWhere('a.city = %s AND ', $this->quote($filter['city']));
        }
        if (!empty($filter['country'])) {
            $filter->appendWhere('a.country = %s AND ', $this->quote($filter['country']));
        }
        if (!empty($filter['state'])) {
            $filter->appendWhere('a.state = %s AND ', $this->quote($filter['state']));
        }
        if (!empty($filter['postcode'])) {
            $filter->appendWhere('a.postcode = %s AND ', $this->quote($filter['postcode']));
        }
        if (!empty($filter['address'])) {
            $filter->appendWhere('a.address = %s AND ', $this->quote($filter['address']));
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