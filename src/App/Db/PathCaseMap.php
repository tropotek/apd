<?php

namespace App\Db;

use Tk\Date;
use Tk\Db\Tool;
use Tk\Db\Map\ArrayObject;
use Tk\DataMap\Db;
use Tk\DataMap\Form;
use Bs\Db\Mapper;
use Tk\Db\Filter;
use Tk\Exception;

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
            $this->dbMap->addPropertyMap(new Db\Integer('userId', 'user_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('clientId', 'client_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('ownerId', 'owner_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('pathologistId', 'pathologist_id'));
            $this->dbMap->addPropertyMap(new Db\Integer('soUserId', 'so_user_id'));
            $this->dbMap->addPropertyMap(new Db\Text('resident'));
            $this->dbMap->addPropertyMap(new Db\Text('pathologyId', 'pathology_id'));
            $this->dbMap->addPropertyMap(new Db\Text('name'));
            $this->dbMap->addPropertyMap(new Db\Text('type'));
            $this->dbMap->addPropertyMap(new Db\Text('submissionType', 'submission_type'));
            $this->dbMap->addPropertyMap(new Db\Boolean('submissionReceived', 'submission_received'));
            $this->dbMap->addPropertyMap(new Db\Date('arrival'));
            $this->dbMap->addPropertyMap(new Db\Text('status'));
            $this->dbMap->addPropertyMap(new Db\Text('reportStatus', 'report_status'));
            $this->dbMap->addPropertyMap(new Db\Boolean('billable'));
            $this->dbMap->addPropertyMap(new Db\Text('accountStatus', 'account_status'));
            $this->dbMap->addPropertyMap(new Db\Money('cost'));
            $this->dbMap->addPropertyMap(new Db\Boolean('afterHours', 'after_hours'));
            $this->dbMap->addPropertyMap(new Db\Text('zoonotic'));
            $this->dbMap->addPropertyMap(new Db\Boolean('zoonoticAlert', 'zoonotic_alert'));
            $this->dbMap->addPropertyMap(new Db\Text('issue'));
            $this->dbMap->addPropertyMap(new Db\Boolean('issueAlert', 'issue_alert'));
            $this->dbMap->addPropertyMap(new Db\Integer('bioSamples', 'bio_samples'));
            $this->dbMap->addPropertyMap(new Db\Text('bioNotes', 'bio_notes'));
            $this->dbMap->addPropertyMap(new Db\Integer('specimenCount', 'specimen_count'));
            $this->dbMap->addPropertyMap(new Db\Text('animalName', 'animal_name'));
            $this->dbMap->addPropertyMap(new Db\Integer('animalTypeId', 'animal_type_id'));
            $this->dbMap->addPropertyMap(new Db\Text('species'));
            $this->dbMap->addPropertyMap(new Db\Text('breed'));
            $this->dbMap->addPropertyMap(new Db\Text('sex'));
            $this->dbMap->addPropertyMap(new Db\Boolean('desexed'));
            $this->dbMap->addPropertyMap(new Db\Text('patientNumber', 'patient_number'));
            $this->dbMap->addPropertyMap(new Db\Text('microchip'));
            $this->dbMap->addPropertyMap(new Db\Text('origin'));
            $this->dbMap->addPropertyMap(new Db\Text('colour'));
            $this->dbMap->addPropertyMap(new Db\Text('weight'));
            $this->dbMap->addPropertyMap(new Db\Text('size'));
            $this->dbMap->addPropertyMap(new Db\Date('dob'));
            $this->dbMap->addPropertyMap(new Db\Date('dod'));
            $this->dbMap->addPropertyMap(new Db\Boolean('euthanised'));
            $this->dbMap->addPropertyMap(new Db\Text('euthanisedMethod', 'euthanised_method'));
            $this->dbMap->addPropertyMap(new Db\Text('acType', 'ac_type'));
            $this->dbMap->addPropertyMap(new Db\Date('acHold', 'ac_hold'));
            $this->dbMap->addPropertyMap(new Db\Integer('storageId', 'storage_id'));
            $this->dbMap->addPropertyMap(new Db\Date('disposal'));
            $this->dbMap->addPropertyMap(new Db\Boolean('studentReport', 'student_report'));
            $this->dbMap->addPropertyMap(new Db\Text('reportStatus', 'report_status'));
            $this->dbMap->addPropertyMap(new Db\Text('collectedSamples', 'collected_samples'));
            $this->dbMap->addPropertyMap(new Db\Text('clinicalHistory', 'clinical_history'));
            $this->dbMap->addPropertyMap(new Db\Text('grossPathology', 'gross_pathology'));
            $this->dbMap->addPropertyMap(new Db\Text('grossMorphologicalDiagnosis', 'gross_morphological_diagnosis'));
            $this->dbMap->addPropertyMap(new Db\Text('histopathology'));
            $this->dbMap->addPropertyMap(new Db\Text('ancillaryTesting', 'ancillary_testing'));
            $this->dbMap->addPropertyMap(new Db\Text('morphologicalDiagnosis', 'morphological_diagnosis'));
            $this->dbMap->addPropertyMap(new Db\Text('causeOfDeath', 'cause_of_death'));
            $this->dbMap->addPropertyMap(new Db\Text('comments'));
            $this->dbMap->addPropertyMap(new Db\Text('secondOpinion', 'second_opinion'));
            $this->dbMap->addPropertyMap(new Db\Text('addendum'));
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
            $this->formMap->addPropertyMap(new Form\Integer('clientId'));
            $this->formMap->addPropertyMap(new Form\Integer('ownerId'));
            $this->formMap->addPropertyMap(new Form\Integer('pathologistId'));
            //$this->formMap->addPropertyMap(new Form\Integer('soUserId'));
            $this->formMap->addPropertyMap(new Form\Text('resident'));
            $this->formMap->addPropertyMap(new Form\Text('pathologyId'));
            $this->formMap->addPropertyMap(new Form\Text('name'));
            $this->formMap->addPropertyMap(new Form\Text('type'));
            $this->formMap->addPropertyMap(new Form\Text('submissionType'));
            $this->formMap->addPropertyMap(new Form\Boolean('submissionReceived'));
            $this->formMap->addPropertyMap(new Form\Date('arrival'));
            $this->formMap->addPropertyMap(new Form\Text('status'));
            $this->formMap->addPropertyMap(new Form\Text('reportStatus'));
            $this->formMap->addPropertyMap(new Form\Boolean('billable'));
            $this->formMap->addPropertyMap(new Form\Text('accountStatus'));
            $this->formMap->addPropertyMap(new Form\Money('cost'));
            $this->formMap->addPropertyMap(new Form\Boolean('afterHours'));
            $this->formMap->addPropertyMap(new Form\Text('zoonotic'));
            $this->formMap->addPropertyMap(new Form\Boolean('zoonoticAlert'));
            $this->formMap->addPropertyMap(new Form\Text('issue'));
            $this->formMap->addPropertyMap(new Form\Boolean('issueAlert'));
            $this->formMap->addPropertyMap(new Form\Integer('bioSamples'));
            $this->formMap->addPropertyMap(new Form\Text('bioNotes'));
            $this->formMap->addPropertyMap(new Form\Integer('specimenCount'));
            $this->formMap->addPropertyMap(new Form\Text('animalName'));
            $this->formMap->addPropertyMap(new Form\Integer('animalTypeId'));
            $this->formMap->addPropertyMap(new Form\Text('species'));
            $this->formMap->addPropertyMap(new Form\Text('breed'));
            $this->formMap->addPropertyMap(new Form\Text('sex'));
            $this->formMap->addPropertyMap(new Form\Boolean('desexed'));
            $this->formMap->addPropertyMap(new Form\Text('patientNumber'));
            $this->formMap->addPropertyMap(new Form\Text('microchip'));
            $this->formMap->addPropertyMap(new Form\Text('origin'));
            $this->formMap->addPropertyMap(new Form\Text('colour'));
            $this->formMap->addPropertyMap(new Form\Text('weight'));
            $this->formMap->addPropertyMap(new Form\Text('size'));
            $this->formMap->addPropertyMap(new Form\Date('dob'));
            $this->formMap->addPropertyMap(new Form\Date('dod'));
            $this->formMap->addPropertyMap(new Form\Boolean('euthanised'));
            $this->formMap->addPropertyMap(new Form\Text('euthanisedMethod'));
            $this->formMap->addPropertyMap(new Form\Text('acType'));
            $this->formMap->addPropertyMap(new Form\Date('acHold'));
            $this->formMap->addPropertyMap(new Form\Integer('storageId'));
            $this->formMap->addPropertyMap(new Form\Date('disposal'));
            $this->formMap->addPropertyMap(new Form\Boolean('studentReport'));
            $this->formMap->addPropertyMap(new Form\Text('reportStatus'));
            $this->formMap->addPropertyMap(new Form\Text('collectedSamples'));
            $this->formMap->addPropertyMap(new Form\Text('clinicalHistory'));
            $this->formMap->addPropertyMap(new Form\Text('grossPathology'));
            $this->formMap->addPropertyMap(new Form\Text('grossMorphologicalDiagnosis'));
            $this->formMap->addPropertyMap(new Form\Text('histopathology'));
            $this->formMap->addPropertyMap(new Form\Text('ancillaryTesting'));
            $this->formMap->addPropertyMap(new Form\Text('morphologicalDiagnosis'));
            $this->formMap->addPropertyMap(new Form\Text('causeOfDeath'));
            $this->formMap->addPropertyMap(new Form\Text('comments'));
            $this->formMap->addPropertyMap(new Form\Text('secondOpinion'));
            $this->formMap->addPropertyMap(new Form\Text('addendum'));
            $this->formMap->addPropertyMap(new Form\Text('notes'));

        }
        return $this->formMap;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function findSpeciesList()
    {
        $sql = <<<SQL
SELECT DISTINCT species
FROM path_case a
WHERE a.del = 0
GROUP BY species
ORDER BY species
SQL;

        $res = $this->getDb()->query($sql);
        $arr = array();
        foreach ($res as $row) {
            if (!$row->species) continue;
            $spec = $row->species;
            $arr[$spec] = $spec;
        }
        return $arr;
    }

    /**
     * @param string $species (optional)
     * @return array
     * @throws \Tk\Db\Exception
     */
    public function findBreedList($species = '')
    {
        $wSpecies = '';
        if ($species) {
            $wSpecies = sprintf(', species = %s', $this->quote($species));
        }

        $sql = <<<SQL
SELECT DISTINCT breed
FROM path_case a
WHERE a.del = 0 $wSpecies
GROUP BY breed
ORDER BY breed
SQL;

        $res = $this->getDb()->query($sql);
        $arr = array();
        foreach ($res as $row) {
            if (!$row->breed) continue;
            $spec = $row->breed;
            $arr[$spec] = $spec;
        }
        return $arr;
    }

    /**
     * @param array|Filter $filter
     * @param Tool $tool
     * @return ArrayObject|PathCase[]
     * @throws \Exception
     */
    public function findFiltered($filter, $tool = null)
    {
        $r = $this->selectFromFilter($this->makeQuery(\Tk\Db\Filter::create($filter)), $tool);
        return $r;
    }

    /**
     * @param Filter $filter
     * @return Filter
     */
    public function makeQuery(Filter $filter)
    {
        $filter->appendSelect(' a.*, b.age, b.age_m, b.path_year, b.path_no ');

        $filter->appendFrom('%s a', $this->quoteParameter($this->getTable()));
        $filter->appendFrom(',
            (
                SELECT id,
                    TIMESTAMPDIFF(YEAR, a1.dob, if (ISNULL(a1.dod), now(), a1.dod)) as \'age\',
                    TIMESTAMPDIFF(MONTH, a1.dob, if (ISNULL(a1.dod), now(), a1.dod)) % 12 as \'age_m\',
                    IF (SUBSTR(a1.pathology_id, -2) REGEXP \'^[0-9]{2}$\', SUBSTR(a1.pathology_id, -2), \'\') as \'path_year\',
                    IF (SUBSTR(a1.pathology_id, 1, 3) REGEXP \'^[0-9]{3}$\', SUBSTR(a1.pathology_id, 1, 3), \'\') as \'path_no\'
                FROM `path_case` a1
            ) b');
        $filter->appendWhere('a.id = b.id AND ');

        if (!empty($filter['keywords'])) {
            $kw = '%' . $this->escapeString($filter['keywords']) . '%';
            $w = '';
            $w .= sprintf('a.pathology_id LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.animal_name LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.patient_number LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.microchip LIKE %s OR ', $this->quote($kw));

            // TODO: Test, if slow maybe we need another search field or full text search fields???
            $w .= sprintf('a.collected_samples LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.clinical_history LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.gross_pathology LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.gross_morphological_diagnosis LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.histopathology LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.ancillary_testing LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.morphological_diagnosis LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.cause_of_death LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.comments LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.addendum LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.second_opinion LIKE %s OR ', $this->quote($kw));
            $w .= sprintf('a.notes LIKE %s OR ', $this->quote($kw));

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

        if (!empty($filter['age'])) {
            $filter->appendWhere('b.age = %s AND ', $this->quote($filter['age']));
        }

        if (!empty($filter['institutionId'])) {
            $filter->appendWhere('a.institution_id = %s AND ', (int)$filter['institutionId']);
        }
        if (!empty($filter['clientId'])) {
            $filter->appendWhere('a.client_id = %s AND ', (int)$filter['clientId']);
        }
        if (!empty($filter['soUserId'])) {
            $filter->appendWhere('a.so_user_id = %s AND ', (int)$filter['soUserId']);
        }


        if (!empty($filter['pathologistId']) && !empty($filter['userId']) && !empty($filter['user_pathologist_or'])) {
            $filter->appendWhere('(a.user_id = %s OR ', (int)$filter['userId']);
            $filter->appendWhere('a.pathologist_id = %s) AND ', (int)$filter['pathologistId']);
        } else {
            if (!empty($filter['userId'])) {
                $filter->appendWhere('a.user_id = %s AND ', (int)$filter['userId']);
            }
            if (!empty($filter['pathologistId'])) {
                $filter->appendWhere('a.pathologist_id = %s AND ', (int)$filter['pathologistId']);
            }
        }



        if (!empty($filter['pathologyId'])) {
            $filter->appendWhere('a.pathology_id = %s AND ', $this->quote($filter['pathologyId']));
        }
        if (!empty($filter['animalTypeId'])) {
            $filter->appendWhere('a.animal_type_id = %s AND ', $this->quote($filter['animalTypeId']));
        }
        if (!empty($filter['resident'])) {
            $filter->appendWhere('a.resident = %s AND ', $this->quote($filter['resident']));
        }
        if (!empty($filter['type'])) {
            $filter->appendWhere('a.type = %s AND ', $this->quote($filter['type']));
        }
        if (!empty($filter['submissionType'])) {
            $filter->appendWhere('a.submission_type = %s AND ', $this->quote($filter['submissionType']));
        }
//        if (!empty($filter['status'])) {
//            $filter->appendWhere('a.status = %s AND ', $this->quote($filter['status']));
//        }
        if (!empty($filter['status'])) {
            $w = $this->makeMultiQuery($filter['status'], 'a.status');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }
        if (!empty($filter['afterHours'])) {
            $filter->appendWhere('a.after_hours = %s AND ', (int)$filter['afterHours']);
        }
        if (!empty($filter['reportStatus'])) {
            $filter->appendWhere('a.report_status = %s AND ', $this->quote($filter['reportStatus']));
        }
        if (!empty($filter['submissionReceived'])) {
            $filter->appendWhere('a.submission_received = %s AND ', (int)$filter['submissionReceived']);
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
        if (!empty($filter['sex'])) {
            $filter->appendWhere('a.sex = %s AND ', $this->quote($filter['sex']));
        }
        if (!empty($filter['desexed'])) {
            $filter->appendWhere('a.desexed = %s AND ', (int)$filter['desexed']);
        }
        if (!empty($filter['zoonoticAlert'])) {
            $filter->appendWhere('a.zoonotic_alert = %s AND ', (int)$filter['zoonoticAlert']);
        }
        if (!empty($filter['issueAlert'])) {
            $filter->appendWhere('a.issue_alert = %s AND ', (int)$filter['issueAlert']);
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
        if (!empty($filter['ownerEmail'])) {
            $filter->appendWhere('a.owner_email = %s AND ', $this->quote($filter['ownerEmail']));
        }
        if (!empty($filter['ownerPhone'])) {
            $filter->appendWhere('a.owner_phone = %s AND ', $this->quote($filter['ownerPhone']));
        }
        if (!empty($filter['origin'])) {
            $filter->appendWhere('a.origin = %s AND ', $this->quote($filter['origin']));
        }
        if (!empty($filter['colour'])) {
            $filter->appendWhere('a.colour = %s AND ', $this->quote($filter['colour']));
        }
        if (!empty($filter['breed'])) {
            $filter->appendWhere('a.breed = %s AND ', $this->quote($filter['breed']));
        }
        if (!empty($filter['weight'])) {
            $filter->appendWhere('a.weight = %s AND ', $this->quote($filter['weight']));
        }
        if (!empty($filter['size'])) {
            $filter->appendWhere('a.size = %s AND ', $this->quote($filter['size']));
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
        if (isset($filter['isDisposed']) && $filter['isDisposed'] !== '' && $filter['isDisposed'] !== null) {
            if ($filter['isDisposed'] > 0) {
                $filter->appendWhere('a.disposal IS NOT NULL AND ');
            } else {
                $filter->appendWhere('a.disposal IS NULL AND ');
            }
        }

        // This query is used for finding reminder disposals (see Cron.php)
        if (!empty($filter['disposedAfter']) && $filter['disposedAfter'] !== '' && $filter['disposedAfter'] !== null) {
            $now = $this->quote(\Tk\Date::create()->format(\Tk\Date::FORMAT_ISO_DATETIME));
            if (!$filter['disposedAfter'] instanceof \DateTime)
                $filter['disposedAfter'] = \Tk\Date::createFormDate($filter['disposedAfter']);
            $filter->appendWhere('a.disposal IS NOT NULL AND a.disposal > %s AND a.disposal <= %s AND ', $now, $this->quote($filter['disposedAfter']->format(\Tk\Date::FORMAT_ISO_DATETIME)));
        }

        if (isset($filter['reminderSent']) && $filter['reminderSent'] !== '' && $filter['reminderSent'] !== null) {
            $filter->appendFrom(' LEFT JOIN %s c ON (b.id = c.path_case_id AND c.type = %s)',
                $this->quoteTable('mail_sent'), $this->quote('reminder'));
            if ($filter['reminderSent'] > 0) {
                $filter->appendWhere('c.`date` IS NOT NULL');
            } else {
                $filter->appendWhere('c.`date` IS NULL');
            }
        }


        if (isset($filter['studentReport']) && $filter['studentReport'] !== '' && $filter['studentReport'] !== null) {
            $filter->appendWhere('a.student_report = %s AND ', (int)$filter['studentReport']);
        }
        if (isset($filter['billable']) && $filter['billable'] !== '' && $filter['billable'] !== null) {
            $filter->appendWhere('a.billable = %s AND ', (int)$filter['billable']);
        }

        $dates = array('arrivalStart', 'arrivalEnd');
        foreach ($dates as $name) {
            if (!empty($filter[$name]) && !$filter[$name] instanceof \DateTime) {
                $filter[$name] = Date::createFormDate($filter[$name]);
            }
        }
        if (!empty($filter['arrivalStart'])) {
            /** @var \DateTime $date */
            $date = Date::floor($filter['arrivalStart']);
            $filter->appendWhere('a.arrival >= %s AND ', $this->quote($date->format(Date::FORMAT_ISO_DATETIME)));
        }
        if (!empty($filter['arrivalEnd'])) {
            /** @var \DateTime $date */
            $date = Date::floor($filter['arrivalEnd']);
            $filter->appendWhere('a.arrival <= %s AND ', $this->quote($date->format(Date::FORMAT_ISO_DATETIME)));
        }

        if (!empty($filter['exclude'])) {
            $w = $this->makeMultiQuery($filter['exclude'], 'a.id', 'AND', '!=');
            if ($w) $filter->appendWhere('(%s) AND ', $w);
        }

        return $filter;
    }


    /**
     * @param int $pathCaseId
     * @param int $contactId
     * @return boolean
     * @throws Exception
     */
    public function hasContact($pathCaseId, $contactId)
    {
        $stm = $this->getDb()->prepare('SELECT * FROM path_case_has_contact WHERE path_case_id = ? AND contact_id = ?');
        $stm->bindParam(1, $pathCaseId);
        $stm->bindParam(2, $contactId);
        $stm->execute();
        return ($stm->rowCount() > 0);
    }

    /**
     * @param null|int $pathCaseId (optional) If not provided all Cases`s for that Contact are removed
     * @param null|int $contactId (optional) If not provided all Contacts for that Case are removed
     * @throws Exception
     */
    public function removeContact($pathCaseId = null, $contactId = null)
    {
        if (!$pathCaseId && !$contactId) throw new Exception('At least one parameter should be set.');
        $where = '';
        if ($pathCaseId)
            $where = sprintf('path_case_id = %d AND ', (int)$pathCaseId);
        if ($contactId)
            $where = sprintf('contact_id = %d AND ', (int)$contactId);
        if ($where)
            $where = substr($where, 0, -4);
        $stm = $this->getDb()->prepare('DELETE FROM path_case_has_contact WHERE ' . $where);
        $stm->execute();
    }

    /**
     * @param int $pathCaseId
     * @param int $contactId
     * @throws Exception
     */
    public function addContact($pathCaseId, $contactId)
    {
        if ($this->hasContact($pathCaseId, $contactId)) return;
        $stm = $this->getDb()->prepare('INSERT INTO path_case_has_contact (path_case_id, contact_id)  VALUES (?, ?) ');
        $stm->bindParam(1, $pathCaseId);
        $stm->bindParam(2, $contactId);
        $stm->execute();
    }


    /**
     * @param int $pathCaseId
     * @param string $type
     * @return \DateTime|null Return null if record not in the table
     * @throws \Tk\Db\Exception
     */
    public function hasMailSent($pathCaseId, $type = 'reminder')
    {
        $stm = $this->getDb()->prepare('SELECT * FROM mail_sent WHERE path_case_id = ? AND type = ? LIMIT 1');
        $stm->execute([$pathCaseId, $type]);
        $date = $stm->fetchColumn(2);
        if ($date !== false)
            return \Tk\Date::create($date);
        return null;
    }

    /**
     * @param int $pathCaseId
     * @param string $type
     * @throws \Tk\Db\Exception
     */
    public function addMailSent($pathCaseId, $type = 'reminder')
    {
        if ($this->hasMailSent($pathCaseId, $type)) return;
        $now = \Tk\Date::create()->format(\Tk\Date::FORMAT_ISO_DATETIME);
        $stm = $this->getDb()->prepare('INSERT INTO mail_sent (path_case_id, type, `date`)  VALUES (?, ?, ?) ');
        $stm->execute([$pathCaseId, $type, $now]);
    }

}