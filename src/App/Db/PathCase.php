<?php
namespace App\Db;

use App\Db\Traits\ClientTrait;
use App\Db\Traits\StorageTrait;
use Bs\Db\Traits\TimestampTrait;
use Uni\Db\Traits\InstitutionTrait;
//use Uni\Db\Traits\StatusTrait;

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

    //use StatusTrait;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $institutionId = 0;

    /**
     * @var int
     */
    public $clientId = 0;

    /**
     * @var string
     */
    public $pathologyId = '';

    /**
     * @var string
     */
    public $type = '';

    /**
     * @var string
     */
    public $submissionType = '';

    /**
     * @var string
     */
    public $status = '';

    /**
     * @var \DateTime
     */
    public $submitted = null;

    /**
     * @var \DateTime
     */
    public $examined = null;

    /**
     * @var \DateTime
     */
    public $finalised = null;

    /**
     * @var string
     */
    public $zootonicDisease = '';

    /**
     * @var string
     */
    public $zootonicResult = '';

    /**
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
    public $gender = '';

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
    public $vmisWeight = '';

    /**
     * @var string
     */
    public $necoWeight = '';

    /**
     * @var \DateTime
     */
    public $dob = null;

    /**
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
     * @var string
     */
    public $acType = '';

    /**
     * @var \DateTime
     */
    public $acHold = null;

    /**
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
     * @var string
     */
    public $causeOfDeath = '';

    /**
     * @var string
     */
    public $comments = '';

    /**
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
        $this->institutionId = $this->getConfig()->getInstitutionId();
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

    // TODO: ---------------------------------------------
    /**
     * @param string $status
     * @return PathCase
     */
    public function setStatus($status) : PathCase
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus() : string
    {
        return $this->status;
    }

    // TODO: ---------------------------------------------

    /**
     * @param \DateTime $submitted
     * @return PathCase
     */
    public function setSubmitted($submitted) : PathCase
    {
        $this->submitted = $submitted;
        return $this;
    }

    /**
     * @param null|string $format   If supplied then a string of the formatted date is returned
     * @return \DateTime|string
     */
    public function getSubmitted($format = null)
    {
        if ($format && $this->submitted)
            return $this->submitted->format($format);
        return $this->submitted;
    }

    /**
     * @param \DateTime $examined
     * @return PathCase
     */
    public function setExamined($examined) : PathCase
    {
        $this->examined = $examined;
        return $this;
    }

    /**
     * @param null|string $format   If supplied then a string of the formatted date is returned
     * @return \DateTime|string
     */
    public function getExamined($format = null)
    {
        if ($format && $this->examined)
            return $this->examined->format($format);
        return $this->examined;
    }

    /**
     * @param \DateTime $finalised
     * @return PathCase
     */
    public function setFinalised($finalised) : PathCase
    {
        $this->finalised = $finalised;
        return $this;
    }

    /**
     * @param null|string $format   If supplied then a string of the formatted date is returned
     * @return \DateTime|string
     */
    public function getFinalised($format = null)
    {
        if ($format && $this->finalised)
            return $this->finalised->format($format);
        return $this->finalised;
    }

    /**
     * @param string $zootonicDisease
     * @return PathCase
     */
    public function setZootonicDisease($zootonicDisease) : PathCase
    {
        $this->zootonicDisease = $zootonicDisease;
        return $this;
    }

    /**
     * @return string
     */
    public function getZootonicDisease() : string
    {
        return $this->zootonicDisease;
    }

    /**
     * @param string $zootonicResult
     * @return PathCase
     */
    public function setZootonicResult($zootonicResult) : PathCase
    {
        $this->zootonicResult = $zootonicResult;
        return $this;
    }

    /**
     * @return string
     */
    public function getZootonicResult() : string
    {
        return $this->zootonicResult;
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
     * @param string $gender
     * @return PathCase
     */
    public function setGender($gender) : PathCase
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * @return string
     */
    public function getGender() : string
    {
        return $this->gender;
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
     * @param string $vmisWeight
     * @return PathCase
     */
    public function setVmisWeight($vmisWeight) : PathCase
    {
        $this->vmisWeight = $vmisWeight;
        return $this;
    }

    /**
     * @return string
     */
    public function getVmisWeight() : string
    {
        return $this->vmisWeight;
    }

    /**
     * @param string $necoWeight
     * @return PathCase
     */
    public function setNecoWeight($necoWeight) : PathCase
    {
        $this->necoWeight = $necoWeight;
        return $this;
    }

    /**
     * @return string
     */
    public function getNecoWeight() : string
    {
        return $this->necoWeight;
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

        if (!$this->clientId) {
            $errors['clientId'] = 'Invalid value: clientId';
        }

        if (!$this->pathologyId) {
            $errors['pathologyId'] = 'Invalid value: pathologyId';
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

        if (!$this->zootonicDisease) {
            $errors['zootonicDisease'] = 'Invalid value: zootonicDisease';
        }

        if (!$this->zootonicResult) {
            $errors['zootonicResult'] = 'Invalid value: zootonicResult';
        }

        if (!$this->specimenCount) {
            $errors['specimenCount'] = 'Invalid value: specimenCount';
        }

        if (!$this->animalName) {
            $errors['animalName'] = 'Invalid value: animalName';
        }

        if (!$this->species) {
            $errors['species'] = 'Invalid value: species';
        }

        if (!$this->gender) {
            $errors['gender'] = 'Invalid value: gender';
        }

        if (!$this->patientNumber) {
            $errors['patientNumber'] = 'Invalid value: patientNumber';
        }

        if (!$this->microchip) {
            $errors['microchip'] = 'Invalid value: microchip';
        }

        if (!$this->ownerName) {
            $errors['ownerName'] = 'Invalid value: ownerName';
        }

        if (!$this->origin) {
            $errors['origin'] = 'Invalid value: origin';
        }

        if (!$this->breed) {
            $errors['breed'] = 'Invalid value: breed';
        }

        if (!$this->vmisWeight) {
            $errors['vmisWeight'] = 'Invalid value: vmisWeight';
        }

        if (!$this->necoWeight) {
            $errors['necoWeight'] = 'Invalid value: necoWeight';
        }

        if (!$this->euthanisedMethod) {
            $errors['euthanisedMethod'] = 'Invalid value: euthanisedMethod';
        }

        if (!$this->acType) {
            $errors['acType'] = 'Invalid value: acType';
        }

        if (!$this->storageId) {
            $errors['storageId'] = 'Invalid value: storageId';
        }

        return $errors;
    }

}
