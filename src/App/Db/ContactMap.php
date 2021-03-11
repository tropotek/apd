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
class ContactMap extends Mapper
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
            $this->dbMap->addPropertyMap(new Db\Integer('userId', 'user_id'));
            $this->dbMap->addPropertyMap(new Db\Text('uid'));
            $this->dbMap->addPropertyMap(new Db\Text('type'));
            $this->dbMap->addPropertyMap(new Db\Text('nameCompany', 'name_company'));
            $this->dbMap->addPropertyMap(new Db\Text('nameFirst', 'name_first'));
            $this->dbMap->addPropertyMap(new Db\Text('nameLast', 'name_last'));
            $this->dbMap->addPropertyMap(new Db\Text('accountCode', 'account_code'));
            $this->dbMap->addPropertyMap(new Db\Text('email'));
            $this->dbMap->addPropertyMap(new Db\Text('phone'));
            $this->dbMap->addPropertyMap(new Db\Text('fax'));

            $this->dbMap->addPropertyMap(new Db\Text('street'));
            $this->dbMap->addPropertyMap(new Db\Text('city'));
            $this->dbMap->addPropertyMap(new Db\Text('country'));
            $this->dbMap->addPropertyMap(new Db\Text('state'));
            $this->dbMap->addPropertyMap(new Db\Text('postcode'));

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
            $this->formMap->addPropertyMap(new Form\Integer('userId'));
            $this->formMap->addPropertyMap(new Form\Text('uid'));
            $this->formMap->addPropertyMap(new Form\Text('type'));
            $this->formMap->addPropertyMap(new Form\Text('accountCode'));
            $this->formMap->addPropertyMap(new Form\Text('nameCompany'));
            $this->formMap->addPropertyMap(new Form\Text('nameFirst'));
            $this->formMap->addPropertyMap(new Form\Text('nameLast'));
            $this->formMap->addPropertyMap(new Form\Text('email'));
            $this->formMap->addPropertyMap(new Form\Text('phone'));
            $this->formMap->addPropertyMap(new Form\Text('fax'));

            $this->formMap->addPropertyMap(new Form\Text('street'));
            $this->formMap->addPropertyMap(new Form\Text('city'));
            $this->formMap->addPropertyMap(new Form\Text('country'));
            $this->formMap->addPropertyMap(new Form\Text('state'));
            $this->formMap->addPropertyMap(new Form\Text('postcode'));

            $this->formMap->addPropertyMap(new Form\Text('notes'));

        }
        return $this->formMap;
    }

    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|Contact[]
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
            $w .= sprintf('a.name_company LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.name_first LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.name_last LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.phone LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.fax LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.email LIKE %s OR ', $this->quote($kw));
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
        if (isset($filter['userId'])) {
            $filter->appendWhere('a.user_id = %s AND ', (int)$filter['userId']);
        }
        if (!empty($filter['uid'])) {
            $filter->appendWhere('a.uid = %s AND ', $this->quote($filter['uid']));
        }
        if (!empty($filter['type'])) {
            $filter->appendWhere('a.type = %s AND ', $this->quote($filter['type']));
        }
        if (!empty($filter['accountCode'])) {
            $filter->appendWhere('a.account_code = %s AND ', $this->quote($filter['accountCode']));
        }
        if (!empty($filter['nameCompany'])) {
            $filter->appendWhere('LOWER(a.name_company) LIKE LOWER(%s) AND ', $this->quote($filter['nameCompany']));
        }
        if (!empty($filter['nameFirst'])) {
            $filter->appendWhere('LOWER(a.name_first) LIKE LOWER(%s) AND ', $this->quote($filter['nameFirst']));
        }
        if (!empty($filter['nameLast'])) {
            $filter->appendWhere('LOWER(a.name_last) LIKE LOWER(%s) AND ', $this->quote($filter['nameLast']));
        }
        if (!empty($filter['email'])) {
            $filter->appendWhere('a.email = %s AND ', $this->quote($filter['email']));
        }
        if (!empty($filter['phone'])) {
            $filter->appendWhere('a.phone = %s AND ', $this->quote($filter['phone']));
        }
        if (!empty($filter['fax'])) {
            $filter->appendWhere('a.fax = %s AND ', $this->quote($filter['fax']));
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

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        if (!empty($filter['pathCaseId'])) {
            // link to path_case_has_contact ???
            $filter->appendFrom(', %s z', $this->quoteTable('path_case_has_contact'));
            $filter->appendWhere('a.id = z.contact_id AND z.path_case_id = %s AND ', (int)$filter['pathCaseId']);
        }

        return $filter;
    }

}