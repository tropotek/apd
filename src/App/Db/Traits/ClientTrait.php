<?php

namespace App\Db\Traits;

use App\Db\Client;
use App\Db\ClientMap;
use Exception;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait ClientTrait
{

    /**
     * @var Client
     */
    private $_client = null;


    /**
     * @return int
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param int $clientId
     * @return $this
     */
    public function setClientId($clientId)
    {
        $this->clientId = (int)$clientId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return Client|null
     */
    public function getClient()
    {
        if (!$this->_client) {
            try {
                $this->_client = ClientMap::create()->find($this->getClientId());
            } catch (Exception $e) {
            }
        }
        return $this->_client;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateClientId($errors = [])
    {
        if (!$this->getClientId()) {
            $errors['clientId'] = 'Invalid value: clientId';
        }
        return $errors;
    }

}