<?php

namespace App\Db;

use Bs\Db\Traits\ForeignModelTrait;
use Bs\Db\Traits\TimestampTrait;
use Bs\Db\Traits\UserTrait;
use DateTime;
use Exception;
use Tk\Db\Map\Model;
use Tk\ValidInterface;
use Uni\Db\Traits\CourseTrait;
use Uni\Db\Traits\InstitutionTrait;
use Uni\Db\Traits\SubjectTrait;
use Uni\Db\User;

/**
 * @author Mick Mifsud
 * @created 2019-05-21
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Note extends Model implements ValidInterface
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
     * @var string
     */
    public $message = '';

    /**
     * @var string
     */
    public $data = '';

    /**
     * @var DateTime
     */
    public $modified = null;

    /**
     * @var DateTime
     */
    public $created = null;


    /**
     * Note constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        $this->_TimestampTrait();
    }

    /**
     * @param null $model
     * @param null $author
     * @return Note
     * @throws Exception
     */
    public static function create($model = null, $author = null)
    {
        $obj = new static();
        $obj->setForeignModel($model);
        $obj->setUserId($author);
        return $obj;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return Note
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @param User $user
     * @return bool
     * @throws Exception
     */
    public function canDelete($user)
    {
        if ($user->getId() == $this->userId) {
            return true;
        }
        if ($user->isAdmin() || $user->isClient()) {
            return true;
        }
        if ($user->hasPermission(Permission::MANAGE_SUBJECT) || $user->hasPermission(Permission::MANAGE_SITE)) {
            return true;
        }
        return false;
    }



    /**
     * @return array
     */
    public function validate()
    {
        $errors = array();
        $errors = $this->validateModelId($errors);

        if (!$this->getMessage()) {
            $errors['message'] = 'Please supply a valid message for this Note';
        }

        return $errors;
    }

}
