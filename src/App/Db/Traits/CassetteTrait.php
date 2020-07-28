<?php

namespace App\Db\Traits;

use App\Db\Cassette;
use App\Db\CassetteMap;
use Exception;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait CassetteTrait
{

    /**
     * @var Cassette
     */
    private $_cassette = null;


    /**
     * @return int
     */
    public function getCassetteId()
    {
        return $this->cassetteId;
    }

    /**
     * @param int $cassetteId
     * @return $this
     */
    public function setCassetteId($cassetteId)
    {
        $this->cassetteId = (int)$cassetteId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return Cassette|null
     */
    public function getCassette()
    {
        if (!$this->_cassette) {
            try {
                $this->_cassette = CassetteMap::create()->find($this->getCassetteId());
            } catch (Exception $e) {
            }
        }
        return $this->_cassette;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateCassetteId($errors = [])
    {
        if (!$this->getCassetteId()) {
            $errors['cassetteId'] = 'Invalid value: cassetteId';
        }
        return $errors;
    }

}