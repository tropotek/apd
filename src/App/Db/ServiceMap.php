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
class ServiceMap extends Mapper
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
            $this->formMap->addPropertyMap(new Form\Integer('institutionId'));
            $this->formMap->addPropertyMap(new Form\Text('name'));
            $this->formMap->addPropertyMap(new Form\Money('cost'));
            $this->formMap->addPropertyMap(new Form\Text('comments'));
            $this->formMap->addPropertyMap(new Form\Text('notes'));

        }
        return $this->formMap;
    }

    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|Service[]
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
            $w .= sprintf('a.comments LIKE %s OR ', $this->quote($kw));
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

        if (isset($filter['institutionId'])) {
            $filter->appendWhere('a.institution_id = %s AND ', (int)$filter['institutionId']);
        }
        if (!empty($filter['name'])) {
            $filter->appendWhere('a.name = %s AND ', $this->quote($filter['name']));
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





    /**
     * @param int $serviceId
     * @return array
     * @throws \Exception
     */
    public function findUsers($serviceId)
    {
        $stm = $this->getDb()->prepare('SELECT user_id FROM service_has_user WHERE service_id = ?');
        $stm->execute(array($serviceId));
        return $stm->fetchAll(\PDO::FETCH_COLUMN, 0);
    }

    /**
     * @param int $serviceId
     * @param int $userId
     * @return boolean
     * @throws \Exception
     */
    public function hasUser($serviceId, $userId)
    {
        $stm = $this->getDb()->prepare('SELECT * FROM service_has_user WHERE service_id = ? AND user_id = ?');
        $stm->execute(array($serviceId, $userId));
        return ($stm->rowCount() > 0);
    }

    /**
     * @param int $serviceId
     * @param int $userId
     * @throws \Exception
     */
    public function addUser($serviceId, $userId)
    {
        if ($this->hasUser($serviceId, $userId)) return;
        $stm = $this->getDb()->prepare('INSERT INTO service_has_user (service_id, user_id)  VALUES (?, ?)');
        $stm->execute(array($serviceId, $userId));
    }

    /**
     * depending on the combination of parameters:
     *  o remove a user from a service
     *  o remove all users from a service
     *  o remove all services from a user
     *
     * @param int $serviceId
     * @param int $userId
     * @throws \Exception
     */
    public function removeUser($serviceId = null, $userId = null)
    {
        if ($serviceId && $userId) {
            $stm = $this->getDb()->prepare('DELETE FROM service_has_user WHERE service_id = ? AND user_id = ?');
            $stm->execute(array($serviceId, $userId));
        } else if(!$serviceId && $userId) {
            $stm = $this->getDb()->prepare('DELETE FROM service_has_user WHERE user_id = ?');
            $stm->execute(array($userId));
        } else if ($serviceId && !$userId) {
            $stm = $this->getDb()->prepare('DELETE FROM service_has_user WHERE service_id = ?');
            $stm->execute(array($serviceId));
        }
    }









}