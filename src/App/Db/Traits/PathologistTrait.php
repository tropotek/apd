<?php

namespace App\Db\Traits;

use Bs\Config;
use Bs\Db\user;
use Bs\Db\UserIface;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait PathologistTrait
{

    /**
     * @var UserIface
     */
    private $_pathologist = null;


    /**
     * @return null|int
     */
    public function getPathologistId()
    {
        return $this->pathologistId;
    }

    /**
     * @param null|int|UserIface $userId
     * @return $this
     */
    public function setPathologistId($userId)
    {
        if ($userId instanceof UserIface) $userId = $userId->getId();
        $this->pathologistId = (int)$userId;
        return $this;
    }

    /**
     * Find this Pathologist user
     *
     * @return UserIface|\Uni\Db\UserIface|User|\Uni\Db\User|null
     * @throws \Exception
     */
    public function getPathologist()
    {
        if (!$this->_pathologist)
            $this->_pathologist = Config::getInstance()->getUserMapper()->find($this->getPathologistId());
        return $this->_pathologist;
    }


    /**
     * @param array $errors
     * @return array
     */
    public function validatePathologistId($errors = [])
    {
        if (!$this->getPathologistId()) {
            $errors['pathologistId'] = 'Invalid value: pathologistId';
        }
        return $errors;
    }

}