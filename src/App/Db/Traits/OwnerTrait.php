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
trait OwnerTrait
{

    /**
     * @var Client
     */
    private $_owner = null;


    /**
     * @return int
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @param int $clientId
     * @return $this
     */
    public function setOwnerId($clientId)
    {
        $this->ownerId = (int)$clientId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return Client|null
     */
    public function getOwner()
    {
        if (!$this->_owner) {
            try {
                $this->_owner = ClientMap::create()->find($this->getOwnerId());
            } catch (Exception $e) {
            }
        }
        return $this->_owner;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateOwnerId($errors = [])
    {
        if (!$this->getOwnerId()) {
            $errors['ownerId'] = 'Invalid value: ownerId';
        }
        return $errors;
    }

}