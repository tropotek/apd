<?php

namespace App\Db\Traits;

use App\Db\Request;
use App\Db\RequestMap;
use Exception;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait RequestTrait
{

    /**
     * @var Request
     */
    private $_request = null;


    /**
     * @return int
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * @param int $requestId
     * @return $this
     */
    public function setRequestId($requestId)
    {
        $this->requestId = (int)$requestId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return Request|null
     */
    public function getRequest()
    {
        if (!$this->_request) {
            try {
                $this->_request = RequestMap::create()->find($this->getRequestId());
            } catch (Exception $e) {
            }
        }
        return $this->_request;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateRequestId($errors = [])
    {
        if (!$this->getRequestId()) {
            $errors['requestId'] = 'Invalid value: requestId';
        }
        return $errors;
    }

}