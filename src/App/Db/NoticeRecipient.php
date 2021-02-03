<?php
namespace App\Db;

use App\Db\Traits\NoticeTrait;
use Bs\Db\Traits\CreatedTrait;
use Bs\Db\Traits\UserTrait;

/**
 * @author Mick Mifsud
 * @created 2021-01-28
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class NoticeRecipient extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use CreatedTrait;
    use NoticeTrait;
    use UserTrait;

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
     * @var bool
     */
    public $alert = false;

    /**
     * @var \DateTime|null
     */
    public $viewed = null;

    /**
     * @var \DateTime|null
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
        $this->_CreatedTrait();

    }

    /**
     * @return bool
     */
    public function isAlert(): bool
    {
        return $this->alert;
    }

    /**
     * @param bool $alert
     * @return NoticeRecipient
     */
    public function setAlert(bool $alert): NoticeRecipient
    {
        $this->alert = $alert;
        return $this;
    }

    /**
     * @param \DateTime|null $viewed
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
    public function getViewed() : ?\DateTime
    {
        return $this->viewed;
    }

    /**
     * @return bool
     */
    public function isViewed()
    {
        return ($this->viewed !== null);
    }

    /**
     * @param \DateTime|null $read
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
    public function getRead() : ?\DateTime
    {
        return $this->read;
    }

    /**
     * @return bool
     */
    public function isRead()
    {
        return ($this->getRead() !== null);
    }

}
