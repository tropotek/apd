<?php

namespace App\Db\Traits;

use App\Db\AnimalType;
use App\Db\AnimalTypeMap;
use Exception;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait AnimalTypeTrait
{

    /**
     * @var AnimalType
     */
    private $_animalType = null;


    /**
     * @return int
     */
    public function getAnimalTypeId()
    {
        return $this->animalTypeId;
    }

    /**
     * @param int $animalTypeId
     * @return $this
     */
    public function setAnimalTypeId($animalTypeId)
    {
        $this->animalTypeId = (int)$animalTypeId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return AnimalType|null
     */
    public function getAnimalType()
    {
        if (!$this->_animalType) {
            try {
                $this->_animalType = AnimalTypeMap::create()->find($this->getAnimalTypeId());
            } catch (Exception $e) {
            }
        }
        return $this->_animalType;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateAnimalTypeId($errors = [])
    {
        if (!$this->getAnimalTypeId()) {
            $errors['animalTypeId'] = 'Invalid value: animalTypeId';
        }
        return $errors;
    }

}