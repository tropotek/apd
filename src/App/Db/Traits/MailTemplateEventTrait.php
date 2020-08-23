<?php

namespace App\Db\Traits;

use App\Db\MailTemplateEvent;
use App\Db\MailTemplateEventMap;
use Exception;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait MailTemplateEventTrait
{

    /**
     * @var MailTemplateEvent
     */
    private $_mailTemplateEvent = null;


    /**
     * @return int
     */
    public function getMailTemplateEventId()
    {
        return $this->mailTemplateEventId;
    }

    /**
     * @param int $mailTemplateEventId
     * @return $this
     */
    public function setMailTemplateEventId($mailTemplateEventId)
    {
        $this->mailTemplateEventId = (int)$mailTemplateEventId;
        return $this;
    }

    /**
     * Get the subject related to this object
     *
     * @return MailTemplateEvent|null
     */
    public function getMailTemplateEvent()
    {
        if (!$this->_mailTemplateEvent) {
            try {
                $this->_mailTemplateEvent = MailTemplateEventMap::create()->find($this->getMailTemplateEventId());
            } catch (Exception $e) {
            }
        }
        return $this->_mailTemplateEvent;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateMailTemplateEventId($errors = [])
    {
        if (!$this->getMailTemplateEventId()) {
            $errors['mailTemplateEventId'] = 'Invalid value: mailTemplateEventId';
        }
        return $errors;
    }

}