<?php

namespace App\Db\Traits;

use App\Db\Storage;
use App\Db\StorageMap;
use Exception;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait StorageTrait
{

    /**
     * @var Storage
     */
    private $_storage = null;


    /**
     * @return int
     */
    public function getStorageId()
    {
        return $this->storageId;
    }

    /**
     * @param int $storageId
     * @return $this
     */
    public function setStorageId($storageId)
    {
        $this->storageId = (int)$storageId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return Storage|null
     */
    public function getStorage()
    {
        if (!$this->_storage) {
            try {
                $this->_storage = StorageMap::create()->find($this->getStorageId());
            } catch (Exception $e) {
            }
        }
        return $this->_storage;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateStorageId($errors = [])
    {
        if (!$this->getStorageId()) {
            $errors['storageId'] = 'Invalid value: storageId';
        }
        return $errors;
    }

}