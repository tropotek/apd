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
class PathCaseMap extends Mapper
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
            $this->dbMap->addPropertyMap(new Db\Integer('clientId', 'client_id'));
            $this->dbMap->addPropertyMap(new Db\Text('pathologyId', 'pathology_id'));
            $this->dbMap->addPropertyMap(new Db\Text('type'));
            $this->dbMap->addPropertyMap(new Db\Text('submissionType', 'submission_type'));
            $this->dbMap->addPropertyMap(new Db\Text('status'));
            $this->dbMap->addPropertyMap(new Db\Date('submitted'));
            $this->dbMap->addPropertyMap(new Db\Date('examined'));
            $this->dbMap->addPropertyMap(new Db\Date('finalised'));
            $this->dbMap->addPropertyMap(new Db\Text('zootonicDisease', 'zootonic_disease'));
            $this->dbMap->addPropertyMap(new Db\Text('zootonicResult', 'zootonic_result'));
            $this->dbMap->addPropertyMap(new Db\Integer('specimenCount', 'specimen_count'));
            $this->dbMap->addPropertyMap(new Db\Text('animalName', 'animal_name'));
            $this->dbMap->addPropertyMap(new Db\Text('species'));
            $this->dbMap->addPropertyMap(new Db\Text('gender'));
            $this->dbMap->addPropertyMap(new Db\Boolean('desexed'));
            $this->dbMap->addPropertyMap(new Db\Text('patientNumber', 'patient_number'));
            $this->dbMap->addPropertyMap(new Db\Text('microchip'));
            $this->dbMap->addPropertyMap(new Db\Text('ownerName', 'owner_name'));
            $this->dbMap->addPropertyMap(new Db\Text('origin'));
            $this->dbMap->addPropertyMap(new Db\Text('breed'));
            $this->dbMap->addPropertyMap(new Db\Text('vmisWeight', 'vmis_weight'));
            $this->dbMap->addPropertyMap(new Db\Text('necoWeight', 'neco_weight'));
            $this->dbMap->addPropertyMap(new Db\Date('dob'));
            $this->dbMap->addPropertyMap(new Db\Date('dod'));
            $this->dbMap->addPropertyMap(new Db\Boolean('euthanised'));
            $this->dbMap->addPropertyMap(new Db\Text('euthanisedMethod', 'euthanised_method'));
            $this->dbMap->addPropertyMap(new Db\Text('acType', 'ac_type'));
            $this->dbMap->addPropertyMap(new Db\Date('acHold', 'ac_hold'));
            $this->dbMap->addPropertyMap(new Db\Integer('storageId', 'storage_id'));
            $this->dbMap->addPropertyMap(new Db\Date('disposal'));
            $this->dbMap->addPropertyMap(new Db\Text('clinicalHistory', 'clinical_history'));
            $this->dbMap->addPropertyMap(new Db\Text('grossPathology', 'gross_pathology'));
            $this->dbMap->addPropertyMap(new Db\Text('grossMorphologicalDiagnosis', 'gross_morphological_diagnosis'));
            $this->dbMap->addPropertyMap(new Db\Text('histopathology'));
            $this->dbMap->addPropertyMap(new Db\Text('ancillaryTesting', 'ancillary_testing'));
            $this->dbMap->addPropertyMap(new Db\Text('morphologicalDiagnosis', 'morphological_diagnosis'));
            $this->dbMap->addPropertyMap(new Db\Text('causeOfDeath', 'cause_of_death'));
            $this->dbMap->addPropertyMap(new Db\Text('comments'));
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
            $this->formMap->addPropertyMap(new Form\Integer('clientId'));
            $this->formMap->addPropertyMap(new Form\Text('pathologyId'));
            $this->formMap->addPropertyMap(new Form\Text('type'));
            $this->formMap->addPropertyMap(new Form\Text('submissionType'));
            $this->formMap->addPropertyMap(new Form\Text('status'));
            $this->formMap->addPropertyMap(new Form\Date('submitted'));
            $this->formMap->addPropertyMap(new Form\Date('examined'));
            $this->formMap->addPropertyMap(new Form\Date('finalised'));
            $this->formMap->addPropertyMap(new Form\Text('zootonicDisease'));
            $this->formMap->addPropertyMap(new Form\Text('zootonicResult'));
            $this->formMap->addPropertyMap(new Form\Integer('specimenCount'));
            $this->formMap->addPropertyMap(new Form\Text('animalName'));
            $this->formMap->addPropertyMap(new Form\Text('species'));
            $this->formMap->addPropertyMap(new Form\Text('gender'));
            $this->formMap->addPropertyMap(new Form\Boolean('desexed'));
            $this->formMap->addPropertyMap(new Form\Text('patientNumber'));
            $this->formMap->addPropertyMap(new Form\Text('microchip'));
            $this->formMap->addPropertyMap(new Form\Text('ownerName'));
            $this->formMap->addPropertyMap(new Form\Text('origin'));
            $this->formMap->addPropertyMap(new Form\Text('breed'));
            $this->formMap->addPropertyMap(new Form\Text('vmisWeight'));
            $this->formMap->addPropertyMap(new Form\Text('necoWeight'));
            $this->formMap->addPropertyMap(new Form\Date('dob'));
            $this->formMap->addPropertyMap(new Form\Date('dod'));
            $this->formMap->addPropertyMap(new Form\Boolean('euthanised'));
            $this->formMap->addPropertyMap(new Form\Text('euthanisedMethod'));
            $this->formMap->addPropertyMap(new Form\Text('acType'));
            $this->formMap->addPropertyMap(new Form\Date('acHold'));
            $this->formMap->addPropertyMap(new Form\Integer('storageId'));
            $this->formMap->addPropertyMap(new Form\Date('disposal'));
            $this->formMap->addPropertyMap(new Form\Text('clinicalHistory'));
            $this->formMap->addPropertyMap(new Form\Text('grossPathology'));
            $this->formMap->addPropertyMap(new Form\Text('grossMorphologicalDiagnosis'));
            $this->formMap->addPropertyMap(new Form\Text('histopathology'));
            $this->formMap->addPropertyMap(new Form\Text('ancillaryTesting'));
            $this->formMap->addPropertyMap(new Form\Text('morphologicalDiagnosis'));
            $this->formMap->addPropertyMap(new Form\Text('causeOfDeath'));
            $this->formMap->addPropertyMap(new Form\Text('comments'));
            $this->formMap->addPropertyMap(new Form\Text('notes'));

        }
        return $this->formMap;
    }

    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|PathCase[]
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
        if (!empty($filter['clientId'])) {
            $filter->appendWhere('a.client_id = %s AND ', (int)$filter['clientId']);
        }
        if (!empty($filter['pathologyId'])) {
            $filter->appendWhere('a.pathology_id = %s AND ', $this->quote($filter['pathologyId']));
        }
        if (!empty($filter['type'])) {
            $filter->appendWhere('a.type = %s AND ', $this->quote($filter['type']));
        }
        if (!empty($filter['submissionType'])) {
            $filter->appendWhere('a.submission_type = %s AND ', $this->quote($filter['submissionType']));
        }
        if (!empty($filter['status'])) {
            $filter->appendWhere('a.status = %s AND ', $this->quote($filter['status']));
        }
        if (!empty($filter['zootonicDisease'])) {
            $filter->appendWhere('a.zootonic_disease = %s AND ', $this->quote($filter['zootonicDisease']));
        }
        if (!empty($filter['zootonicResult'])) {
            $filter->appendWhere('a.zootonic_result = %s AND ', $this->quote($filter['zootonicResult']));
        }
        if (!empty($filter['specimenCount'])) {
            $filter->appendWhere('a.specimen_count = %s AND ', (int)$filter['specimenCount']);
        }
        if (!empty($filter['animalName'])) {
            $filter->appendWhere('a.animal_name = %s AND ', $this->quote($filter['animalName']));
        }
        if (!empty($filter['species'])) {
            $filter->appendWhere('a.species = %s AND ', $this->quote($filter['species']));
        }
        if (!empty($filter['gender'])) {
            $filter->appendWhere('a.gender = %s AND ', $this->quote($filter['gender']));
        }
        if (!empty($filter['desexed'])) {
            $filter->appendWhere('a.desexed = %s AND ', (int)$filter['desexed']);
        }
        if (!empty($filter['patientNumber'])) {
            $filter->appendWhere('a.patient_number = %s AND ', $this->quote($filter['patientNumber']));
        }
        if (!empty($filter['microchip'])) {
            $filter->appendWhere('a.microchip = %s AND ', $this->quote($filter['microchip']));
        }
        if (!empty($filter['ownerName'])) {
            $filter->appendWhere('a.owner_name = %s AND ', $this->quote($filter['ownerName']));
        }
        if (!empty($filter['origin'])) {
            $filter->appendWhere('a.origin = %s AND ', $this->quote($filter['origin']));
        }
        if (!empty($filter['breed'])) {
            $filter->appendWhere('a.breed = %s AND ', $this->quote($filter['breed']));
        }
        if (!empty($filter['vmisWeight'])) {
            $filter->appendWhere('a.vmis_weight = %s AND ', $this->quote($filter['vmisWeight']));
        }
        if (!empty($filter['necoWeight'])) {
            $filter->appendWhere('a.neco_weight = %s AND ', $this->quote($filter['necoWeight']));
        }
        if (!empty($filter['euthanised'])) {
            $filter->appendWhere('a.euthanised = %s AND ', (int)$filter['euthanised']);
        }
        if (!empty($filter['euthanisedMethod'])) {
            $filter->appendWhere('a.euthanised_method = %s AND ', $this->quote($filter['euthanisedMethod']));
        }
        if (!empty($filter['acType'])) {
            $filter->appendWhere('a.ac_type = %s AND ', $this->quote($filter['acType']));
        }
        if (!empty($filter['storageId'])) {
            $filter->appendWhere('a.storage_id = %s AND ', (int)$filter['storageId']);
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }

}