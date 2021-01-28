<?php
namespace App\Db;

/**
 * @author Mick Mifsud
 * @created 2021-01-28
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class NoticeRecipient extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $noticeId = 0;

    /**
     * @var int
     */
    public $userId = 0;

    /**
     * @var \DateTime
     */
    public $viewed = null;

    /**
     * @var \DateTime
     */
    public $read = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * NoticeRecipient
     */
    public function __construct()
    {
        $this->created = new \DateTime();

    }
    
    /**
     * @param int $noticeId
     * @return NoticeRecipient
     */
    public function setNoticeId($noticeId) : NoticeRecipient
    {
        $this->noticeId = $noticeId;
        return $this;
    }

    /**
     * @return int
     */
    public function getNoticeId() : int
    {
        return $this->noticeId;
    }

    /**
     * @param int $userId
     * @return NoticeRecipient
     */
    public function setUserId($userId) : NoticeRecipient
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId() : int
    {
        return $this->userId;
    }

    /**
     * @param \DateTime $viewed
     * @return NoticeRecipient
     */
    public function setViewed($viewed) : NoticeRecipient
    {
        $this->viewed = $viewed;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getViewed() : \DateTime
    {
        return $this->viewed;
    }

    /**
     * @param \DateTime $read
     * @return NoticeRecipient
     */
    public function setRead($read) : NoticeRecipient
    {
        $this->read = $read;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRead() : \DateTime
    {
        return $this->read;
    }

    /**
     * @param \DateTime $created
     * @return NoticeRecipient
     */
    public function setCreated($created) : NoticeRecipient
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreated() : \DateTime
    {
        return $this->created;
    }

    /**
     * @return array
     */
    public function validate()
    {
        $errors = array();

        if (!$this->noticeId) {
            $errors['noticeId'] = 'Invalid value: noticeId';
        }

        if (!$this->userId) {
            $errors['userId'] = 'Invalid value: userId';
        }

        return $errors;
    }

}
