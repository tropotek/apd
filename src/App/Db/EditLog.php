<?php
namespace App\Db;

use Bs\Db\Traits\ForeignModelTrait;
use Bs\Db\Traits\TimestampTrait;
use Bs\Db\Traits\UserTrait;
use Tk\Db\Map\Model;

/**
 * @author Mick Mifsud
 * @created 2021-06-02
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class EditLog extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use UserTrait;
    use ForeignModelTrait;
    use TimestampTrait;


    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $userId = 0;

    /**
     * @var string
     */
    public $fkey = '';

    /**
     * @var int
     */
    public $fid = 0;

    /**
     * @var object|null
     */
    public $state = '';

    /**
     * @var string
     */
    public $message = '';

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * EditLog
     */
    public function __construct()
    {
        $this->_TimestampTrait();

    }

    /**
     * @param object|Model $state
     * @return EditLog
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return object|Model
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $message
     * @return EditLog
     */
    public function setMessage($message) : EditLog
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * @return array
     */
    public function validate()
    {
        $errors = array();

        if (!$this->userId) {
            $errors['userId'] = 'Invalid value: userId';
        }

        if (!$this->fkey) {
            $errors['fkey'] = 'Invalid value: fkey';
        }

        if (!$this->fid) {
            $errors['fid'] = 'Invalid value: fid';
        }

        return $errors;
    }

}
