<?php

namespace App\Db\Traits;

use App\Db\Notice;
use App\Db\NoticeMap;
use Exception;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait NoticeTrait
{

    /**
     * @var Notice
     */
    private $_notice = null;


    /**
     * @return int
     */
    public function getNoticeId()
    {
        return $this->noticeId;
    }

    /**
     * @param int $noticeId
     * @return $this
     */
    public function setNoticeId($noticeId)
    {
        $this->noticeId = (int)$noticeId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return Notice|null
     */
    public function getNotice()
    {
        if (!$this->_notice) {
            try {
                $this->_notice = NoticeMap::create()->find($this->getNoticeId());
            } catch (Exception $e) {
            }
        }
        return $this->_notice;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateNoticeId($errors = [])
    {
        if (!$this->getNoticeId()) {
            $errors['noticeId'] = 'Invalid value: noticeId';
        }
        return $errors;
    }

}