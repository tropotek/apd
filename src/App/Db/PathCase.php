<?php
namespace App\Db;

use App\Db\Traits\ClientTrait;
use App\Db\Traits\PathologistTrait;
use App\Db\Traits\StorageTrait;
use Bs\Db\Status;
use Bs\Db\Traits\StatusTrait;
use Bs\Db\Traits\TimestampTrait;
use Bs\Db\Traits\UserTrait;
use Tk\Db\Tool;
use Uni\Db\Traits\InstitutionTrait;

/**
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class PathCase extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use TimestampTrait;
    use InstitutionTrait;
    use ClientTrait;
    use StorageTrait;
    use StatusTrait;
    use UserTrait;
    use PathologistTrait;

    // TODO: Check these status's against the actual workflow.
    const STATUS_PENDING            = 'pending';            // Submitted ???
    const STATUS_HOLD               = 'hold';               // Awaiting review???
    const STATUS_FROZEN_STORAGE     = 'frozenStorage';      //
    const STATUS_EXAMINED           = 'examined';           //
    const STATUS_REPORTED           = 'reported';           //
    const STATUS_DISPOSED           = 'disposed';           //
    const STATUS_COMPLETED          = 'completed';          //
    const STATUS_CANCELLED          = 'cancelled';          // case cancelled

    const TYPE_BIOPSY               = 'biopsy';
    const TYPE_NECROPSY             = 'necropsy';

    // TODO: Go ovber these types with stakeholder's b4 release
    const SUBMISSION_EXTERNAL_VET   = 'externalVet';
    const SUBMISSION_INTERNAL_VET   = 'internalVet';
    const SUBMISSION_RESEARCH       = 'research';
    const SUBMISSION_TEACHING       = 'teaching';
    const SUBMISSION_PAID           = 'paid';
    const SUBMISSION_OTHER          = 'other';

    // After Care Options
    const AC_GENERAL                = 'general';
    const AC_CREMATION              = 'cremation';
    const AC_INCINERATION           = 'incineration';

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $institutionId = 0;


    /**
     * Staff who created this case
     *
     * @var int
     */
    public $userId = 0;

    /**
     * Client/Clinician
     * @var int
     */
    public $clientId = 0;

    /**
     * userId of the pathologist user
     * @var int
     */
    public $pathologistId = 0;

    /**
     * @var string
     */
    public $resident = '';

    /**
     * @var string
     */
    public $student = '';

    /**
     * @var string
     */
    public $studentEmail = '';


    /**
     * Pathology Number
     * @var string
     */
    public $pathologyId = '';

    /**
     * BIOPSY, NECROPSY
     * @var string
     */
    public $type = '';

    /**
     * direct client/external vet/INTernal vet/researcher/ Other - Specify
     * @var string
     */
    public $submissionType = '';

    /**
     * Pending/frozen storage/examined/reported/awaiting review (if applicable)/completed
     * @var string
     */
    public $status = 'pending';

    /**
     * A description of any risks with the animal
     * @var string
     */
    public $zoonotic = '';

    /**
     * If true then alert user of this info when viewing the case
     * @var bool
     */
    public $zoonoticAlert = false;

    /**
     * Any issues the staff should be alerted to when dealing with this animal
     * @var string
     */
    public $issue = '';

    /**
     * If true then alert user of this info when viewing the case
     * @var bool
     */
    public $issueAlert = false;


    /**
     * TODO: NOT sure if this is needed
     * @var int
     */
    public $specimenCount = 1;

    /**
     * @var string
     */
    public $animalName = '';

    /**
     * @var string
     */
    public $species = '';

    /**
     * @var string
     */
    public $sex = '';

    /**
     * @var bool
     */
    public $desexed = false;

    /**
     * @var string
     */
    public $patientNumber = '';

    /**
     * @var string
     */
    public $microchip = '';

    /**
     * @var string
     */
    public $ownerName = '';

    /**
     * TODO: For now a TEXT box, but NOT sure if this should be lookup table
     * @var string
     */
    public $origin = '';

    /**
     * @var string
     */
    public $breed = '';

    /**
     * @var string
     */
    public $colour = '';

    /**
     * @var string
     */
    public $weight = '';

    /**
     * Date of birth
     * @var \DateTime
     */
    public $dob = null;

    /**
     * Date and time of death
     * @var \DateTime
     */
    public $dod = null;

    /**
     * @var bool
     */
    public $euthanised = false;

    /**
     * @var string
     */
    public $euthanisedMethod = '';

    /**
     * after care type: General Disposal/cremation/INTernal incineration
     * @var string
     */
    public $acType = '';

    /**
     * after care Date to wait until processing animal
     * @var \DateTime
     */
    public $acHold = null;

    /**
     * The current location of the animal (cleared when disposal is completed)
     * @var int
     */
    public $storageId = 0;

    /**
     * @var \DateTime
     */
    public $disposal = null;

    /**
     * @var string
     */
    public $collectedSamples = '';

    /**
     * @var string
     */
    public $clinicalHistory = '';

    /**
     * @var string
     */
    public $grossPathology = '';

    /**
     * @var string
     */
    public $grossMorphologicalDiagnosis = '';

    /**
     * @var string
     */
    public $histopathology = '';

    /**
     * @var string
     */
    public $ancillaryTesting = '';

    /**
     * @var string
     */
    public $morphologicalDiagnosis = '';

    /**
     * (required) case NOT saved if blank
     * @var string
     */
    public $causeOfDeath = '';

    /**
     * Any notes added after report submitted
     * @var string
     */
    public $addendum = '';

    /**
     * public comments
     * @var string
     */
    public $comments = '';

    /**
     * Staff only notes
     * @var string
     */
    public $notes = '';

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * PathCase
     */
    public function __construct()
    {
        $this->_TimestampTrait();
        $this->setInstitutionId($this->getConfig()->getInstitutionId());
        if ($this->getConfig()->getAuthUser())
            $this->setUserId($this->getConfig()->getAuthUser()->getId());
    }

    /**
     * @param Tool|null $tool
     * @return File[]|\Tk\Db\Map\ArrayObject
     * @throws \Exception
     */
    public function getFiles(Tool $tool = null)
    {
        $list = FileMap::create()->findFiltered(array('model' => $this), $tool);
        return $list;
    }

    /**
     * Get the Case root file folder, all content related to the case must be stored in here
     *
     * @return string
     * @throws \Exception
     */
    public function getDataPath()
    {
        if ($this->getInstitution()) {
            return sprintf('%s/pathCase/%s', $this->getInstitution()->getDataPath(), $this->getVolatileId());
        }
        return sprintf('pathCase/%s', $this->getVolatileId());
    }

    /**
     * @param string $pathologyId
     * @return PathCase
     */
    public function setPathologyId($pathologyId) : PathCase
    {
        $this->pathologyId = $pathologyId;
        return $this;
    }

    /**
     * @return string
     */
    public function getPathologyId() : string
    {
        return $this->pathologyId;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getVolatilePathologyId() : string
    {
        if (!$this->getInstitutionId())
            throw new \Tk\Exception('No institutionId set.');
        if ($this->getPathologyId()) {
            return $this->getPathologyId();
        }
        /** @var PathCase $prev */
        $y = date('y');
        $str = '001-'.$y;
        $prev = PathCaseMap::create()->findFiltered(array(
            'institutionId' => $this->getInstitutionId()
        ), Tool::create('id DESC', 1))->current();
        if ($prev) {
            $pidArr = explode('-', $prev->getPathologyId());
            if (count($pidArr) >= 2 && (int)$pidArr[1] && $y == (int)$pidArr[1]) {
                $str = sprintf('%03d-%s', (int)$pidArr[0] + 1, $y);
            }
        }
        return $str;
    }

    /**
     * @return int
     * @throws \Exception
     */
    public function insert()
    {
        if (!$this->getPathologyId()) {
            $this->setPathologyId($this->getVolatilePathologyId());
        }
        return parent::insert();
    }

    /**
     * @param string $type
     * @return PathCase
     */
    public function setType($type) : PathCase
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @param string $submissionType
     * @return PathCase
     */
    public function setSubmissionType($submissionType) : PathCase
    {
        $this->submissionType = $submissionType;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubmissionType() : string
    {
        return $this->submissionType;
    }

    /**
     * @param string $zoonotic
     * @return PathCase
     */
    public function setZoonotic($zoonotic) : PathCase
    {
        $this->zoonotic = $zoonotic;
        return $this;
    }

    /**
     * @return string
     */
    public function getZoonotic() : string
    {
        return $this->zoonotic;
    }

    /**
     * @return bool
     */
    public function isZoonoticAlert(): bool
    {
        return $this->zoonoticAlert;
    }

    /**
     * @param bool $zoonoticAlert
     * @return PathCase
     */
    public function setZoonoticAlert(bool $zoonoticAlert): PathCase
    {
        $this->zoonoticAlert = $zoonoticAlert;
        return $this;
    }

    /**
     * @return string
     */
    public function getIssue(): string
    {
        return $this->issue;
    }

    /**
     * @param string $issue
     * @return PathCase
     */
    public function setIssue(string $issue): PathCase
    {
        $this->issue = $issue;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIssueAlert(): bool
    {
        return $this->issueAlert;
    }

    /**
     * @param bool $issueAlert
     * @return PathCase
     */
    public function setIssueAlert(bool $issueAlert): PathCase
    {
        $this->issueAlert = $issueAlert;
        return $this;
    }

    /**
     * @param int $specimenCount
     * @return PathCase
     */
    public function setSpecimenCount($specimenCount) : PathCase
    {
        $this->specimenCount = $specimenCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getSpecimenCount() : int
    {
        return $this->specimenCount;
    }

    /**
     * @param string $animalName
     * @return PathCase
     */
    public function setAnimalName($animalName) : PathCase
    {
        $this->animalName = $animalName;
        return $this;
    }

    /**
     * @return string
     */
    public function getAnimalName() : string
    {
        return $this->animalName;
    }

    /**
     * @param string $species
     * @return PathCase
     */
    public function setSpecies($species) : PathCase
    {
        $this->species = $species;
        return $this;
    }

    /**
     * @return string
     */
    public function getSpecies() : string
    {
        return $this->species;
    }

    /**
     * @param string $sex
     * @return PathCase
     */
    public function setSex($sex) : PathCase
    {
        $this->sex = $sex;
        return $this;
    }

    /**
     * @return string
     */
    public function getSex() : string
    {
        return $this->sex;
    }

    /**
     * @param bool $desexed
     * @return PathCase
     */
    public function setDesexed($desexed) : PathCase
    {
        $this->desexed = $desexed;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDesexed() : bool
    {
        return $this->desexed;
    }

    /**
     * @param string $patientNumber
     * @return PathCase
     */
    public function setPatientNumber($patientNumber) : PathCase
    {
        $this->patientNumber = $patientNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getPatientNumber() : string
    {
        return $this->patientNumber;
    }

    /**
     * @param string $microchip
     * @return PathCase
     */
    public function setMicrochip($microchip) : PathCase
    {
        $this->microchip = $microchip;
        return $this;
    }

    /**
     * @return string
     */
    public function getMicrochip() : string
    {
        return $this->microchip;
    }

    /**
     * @param string $ownerName
     * @return PathCase
     */
    public function setOwnerName($ownerName) : PathCase
    {
        $this->ownerName = $ownerName;
        return $this;
    }

    /**
     * @return string
     */
    public function getOwnerName() : string
    {
        return $this->ownerName;
    }

    /**
     * @param string $origin
     * @return PathCase
     */
    public function setOrigin($origin) : PathCase
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrigin() : string
    {
        return $this->origin;
    }

    /**
     * @param string $breed
     * @return PathCase
     */
    public function setBreed($breed) : PathCase
    {
        $this->breed = $breed;
        return $this;
    }

    /**
     * @return string
     */
    public function getBreed() : string
    {
        return $this->breed;
    }

    /**
     * @return string
     */
    public function getColour(): string
    {
        return $this->colour;
    }

    /**
     * @param string $colour
     * @return PathCase
     */
    public function setColour(string $colour): PathCase
    {
        $this->colour = $colour;
        return $this;
    }

    /**
     * @param string $weight
     * @return PathCase
     */
    public function setWeight($weight) : PathCase
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return string
     */
    public function getWeight() : string
    {
        return $this->weight;
    }

    /**
     * @param \DateTime $dob
     * @return PathCase
     */
    public function setDob($dob) : PathCase
    {
        $this->dob = $dob;
        return $this;
    }

    /**
     * @param null|string $format   If supplied then a string of the formatted date is returned
     * @return \DateTime|string
     */
    public function getDob($format = null)
    {
        if ($format && $this->dob)
            return $this->dob->format($format);
        return $this->dob;
    }

    /**
     * @param \DateTime $dod
     * @return PathCase
     */
    public function setDod($dod) : PathCase
    {
        $this->dod = $dod;
        return $this;
    }

    /**
     * @param null|string $format   If supplied then a string of the formatted date is returned
     * @return \DateTime|string
     */
    public function getDod($format = null)
    {
        if ($format && $this->dod)
            return $this->dod->format($format);
        return $this->dod;
    }

    /**
     * @param bool $euthanised
     * @return PathCase
     */
    public function setEuthanised($euthanised) : PathCase
    {
        $this->euthanised = $euthanised;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEuthanised() : bool
    {
        return $this->euthanised;
    }

    /**
     * @param string $euthanisedMethod
     * @return PathCase
     */
    public function setEuthanisedMethod($euthanisedMethod) : PathCase
    {
        $this->euthanisedMethod = $euthanisedMethod;
        return $this;
    }

    /**
     * @return string
     */
    public function getEuthanisedMethod() : string
    {
        return $this->euthanisedMethod;
    }

    /**
     * @param string $acType
     * @return PathCase
     */
    public function setAcType($acType) : PathCase
    {
        $this->acType = $acType;
        return $this;
    }

    /**
     * @return string
     */
    public function getAcType() : string
    {
        return $this->acType;
    }

    /**
     * @param \DateTime $acHold
     * @return PathCase
     */
    public function setAcHold($acHold) : PathCase
    {
        $this->acHold = $acHold;
        return $this;
    }

    /**
     * @param null|string $format   If supplied then a string of the formatted date is returned
     * @return \DateTime|string
     */
    public function getAcHold($format = null)
    {
        if ($format && $this->acHold)
            return $this->acHold->format($format);
        return $this->acHold;
    }

    /**
     * @param \DateTime $disposal
     * @return PathCase
     */
    public function setDisposal($disposal) : PathCase
    {
        $this->disposal = $disposal;
        return $this;
    }

    /**
     * @param null|string $format   If supplied then a string of the formatted date is returned
     * @return \DateTime|string
     */
    public function getDisposal($format = null)
    {
        if ($format && $this->disposal)
            return $this->disposal->format($format);
        return $this->disposal;
    }

    /**
     * @return string
     */
    public function getResident(): string
    {
        return $this->resident;
    }

    /**
     * @param string $resident
     * @return PathCase
     */
    public function setResident(string $resident): PathCase
    {
        $this->resident = $resident;
        return $this;
    }

    /**
     * @return string
     */
    public function getStudent(): string
    {
        return $this->student;
    }

    /**
     * @param string $student
     * @return PathCase
     */
    public function setStudent(string $student): PathCase
    {
        $this->student = $student;
        return $this;
    }

    /**
     * @return string
     */
    public function getStudentEmail(): string
    {
        return $this->studentEmail;
    }

    /**
     * @param string $studentEmail
     * @return PathCase
     */
    public function setStudentEmail(string $studentEmail): PathCase
    {
        $this->studentEmail = $studentEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollectedSamples(): string
    {
        return $this->collectedSamples;
    }

    /**
     * @param string $collectedSamples
     * @return PathCase
     */
    public function setCollectedSamples(string $collectedSamples): PathCase
    {
        $this->collectedSamples = $collectedSamples;
        return $this;
    }

    /**
     * @param string $clinicalHistory
     * @return PathCase
     */
    public function setClinicalHistory($clinicalHistory) : PathCase
    {
        $this->clinicalHistory = $clinicalHistory;
        return $this;
    }

    /**
     * @return string
     */
    public function getClinicalHistory() : string
    {
        return $this->clinicalHistory;
    }

    /**
     * @param string $grossPathology
     * @return PathCase
     */
    public function setGrossPathology($grossPathology) : PathCase
    {
        $this->grossPathology = $grossPathology;
        return $this;
    }

    /**
     * @return string
     */
    public function getGrossPathology() : string
    {
        return $this->grossPathology;
    }

    /**
     * @param string $grossMorphologicalDiagnosis
     * @return PathCase
     */
    public function setGrossMorphologicalDiagnosis($grossMorphologicalDiagnosis) : PathCase
    {
        $this->grossMorphologicalDiagnosis = $grossMorphologicalDiagnosis;
        return $this;
    }

    /**
     * @return string
     */
    public function getGrossMorphologicalDiagnosis() : string
    {
        return $this->grossMorphologicalDiagnosis;
    }

    /**
     * @param string $histopathology
     * @return PathCase
     */
    public function setHistopathology($histopathology) : PathCase
    {
        $this->histopathology = $histopathology;
        return $this;
    }

    /**
     * @return string
     */
    public function getHistopathology() : string
    {
        return $this->histopathology;
    }

    /**
     * @param string $ancillaryTesting
     * @return PathCase
     */
    public function setAncillaryTesting($ancillaryTesting) : PathCase
    {
        $this->ancillaryTesting = $ancillaryTesting;
        return $this;
    }

    /**
     * @return string
     */
    public function getAncillaryTesting() : string
    {
        return $this->ancillaryTesting;
    }

    /**
     * @param string $morphologicalDiagnosis
     * @return PathCase
     */
    public function setMorphologicalDiagnosis($morphologicalDiagnosis) : PathCase
    {
        $this->morphologicalDiagnosis = $morphologicalDiagnosis;
        return $this;
    }

    /**
     * @return string
     */
    public function getMorphologicalDiagnosis() : string
    {
        return $this->morphologicalDiagnosis;
    }

    /**
     * @param string $causeOfDeath
     * @return PathCase
     */
    public function setCauseOfDeath($causeOfDeath) : PathCase
    {
        $this->causeOfDeath = $causeOfDeath;
        return $this;
    }

    /**
     * @return string
     */
    public function getCauseOfDeath() : string
    {
        return $this->causeOfDeath;
    }

    /**
     * @return string
     */
    public function getAddendum(): string
    {
        return $this->addendum;
    }

    /**
     * @param string $addendum
     * @return PathCase
     */
    public function setAddendum(string $addendum): PathCase
    {
        $this->addendum = $addendum;
        return $this;
    }

    /**
     * @param string $comments
     * @return PathCase
     */
    public function setComments($comments) : PathCase
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return string
     */
    public function getComments() : string
    {
        return $this->comments;
    }

    /**
     * @param string $notes
     * @return PathCase
     */
    public function setNotes($notes) : PathCase
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotes() : string
    {
        return $this->notes;
    }

    /**
     * @return array
     */
    public function validate()
    {
        $errors = array();

        if (!$this->institutionId) {
            $errors['institutionId'] = 'Invalid value: institutionId';
        }

        if (!$this->type) {
            $errors['type'] = 'Invalid value: type';
        }

        if (!$this->submissionType) {
            $errors['submissionType'] = 'Invalid value: submissionType';
        }

        if (!$this->status) {
            $errors['status'] = 'Invalid value: status';
        }

        if (!$this->species) {
            $errors['species'] = 'Invalid value: species';
        }

//        if (!$this->sex) {
//            $errors['gender'] = 'Invalid value: gender';
//        }

        if (!$this->patientNumber) {
            $errors['patientNumber'] = 'Invalid value: patientNumber';
        }

        if (!$this->ownerName) {
            $errors['ownerName'] = 'Invalid value: ownerName';
        }

        return $errors;
    }

    public function hasStatusChanged(Status $status)
    {
        $prevStatusName = $status->getPreviousName();
        switch ($status->getName()) {
            case PathCase::STATUS_PENDING:
                if (!$prevStatusName || PathCase::STATUS_HOLD == $prevStatusName)
                    return true;
                break;
            case PathCase::STATUS_EXAMINED:
                if (!$prevStatusName || PathCase::STATUS_PENDING == $prevStatusName || PathCase::STATUS_HOLD == $prevStatusName)
                    return true;
                break;
            case PathCase::STATUS_REPORTED:
                if (PathCase::STATUS_EXAMINED == $prevStatusName || PathCase::STATUS_PENDING == $prevStatusName )
                    return true;
                break;
            case PathCase::STATUS_COMPLETED:
                if (PathCase::STATUS_PENDING == $prevStatusName || PathCase::STATUS_REPORTED == $prevStatusName || PathCase::STATUS_EXAMINED == $prevStatusName)
                    return true;
                break;
            case PathCase::STATUS_DISPOSED:
            case PathCase::STATUS_HOLD:
            case PathCase::STATUS_FROZEN_STORAGE:
            case Request::STATUS_CANCELLED:
                return true;
        }

        return false;
    }
}
