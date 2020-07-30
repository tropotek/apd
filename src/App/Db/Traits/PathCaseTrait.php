<?php

namespace App\Db\Traits;

use App\Db\PathCase;
use App\Db\PathCaseMap;
use Exception;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait PathCaseTrait
{

    /**
     * @var PathCase
     */
    private $_pathCase = null;


    /**
     * @return int
     */
    public function getPathCaseId()
    {
        return $this->pathCaseId;
    }

    /**
     * @param int $pathCaseId
     * @return $this
     */
    public function setPathCaseId($pathCaseId)
    {
        $this->pathCaseId = (int)$pathCaseId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return PathCase|null
     */
    public function getPathCase()
    {
        if (!$this->_pathCase) {
            try {
                $this->_pathCase = PathCaseMap::create()->find($this->getPathCaseId());
            } catch (Exception $e) {
            }
        }
        return $this->_pathCase;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validatePathCaseId($errors = [])
    {
        if (!$this->getPathCaseId()) {
            $errors['pathCaseId'] = 'Invalid value: pathCaseId';
        }
        return $errors;
    }

}