<?php

namespace App\Db\Traits;

use App\Db\Service;
use App\Db\ServiceMap;
use Exception;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait ServiceTrait
{

    /**
     * @var Service
     */
    private $_service = null;


    /**
     * @return int
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @param int $serviceId
     * @return $this
     */
    public function setServiceId($serviceId)
    {
        $this->serviceId = (int)$serviceId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return Service|null
     */
    public function getService()
    {
        if (!$this->_service) {
            try {
                $this->_service = ServiceMap::create()->find($this->getServiceId());
            } catch (Exception $e) {
            }
        }
        return $this->_service;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateServiceId($errors = [])
    {
        if (!$this->getServiceId()) {
            $errors['serviceId'] = 'Invalid value: serviceId';
        }
        return $errors;
    }

}