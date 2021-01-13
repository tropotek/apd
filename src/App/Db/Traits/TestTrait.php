<?php

namespace App\Db\Traits;

use App\Db\Test;
use App\Db\TestMap;
use Exception;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait TestTrait
{

    /**
     * @var Test
     */
    private $_test = null;


    /**
     * @return int
     */
    public function getTestId()
    {
        return $this->testId;
    }

    /**
     * @param int $testId
     * @return $this
     */
    public function setTestId($testId)
    {
        $this->testId = (int)$testId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return Test|null
     */
    public function getTest()
    {
        if (!$this->_test) {
            try {
                $this->_test = TestMap::create()->find($this->getTestId());
            } catch (Exception $e) {
            }
        }
        return $this->_test;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateTestId($errors = [])
    {
        if (!$this->getTestId()) {
            $errors['testId'] = 'Invalid value: testId';
        }
        return $errors;
    }

}