<?php
namespace App\Db;

use Bs\Db\Traits\TimestampTrait;
use Uni\Db\Traits\InstitutionTrait;

/**
 * @author Mick Mifsud
 * @created 2020-08-17
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class MailTemplate extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use InstitutionTrait;
    use TimestampTrait;

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $institutionId = 0;

    /**
     * @var string
     */
    public $event = '';

    /**
     * @var string
     */
    public $recipientType = '';

    /**
     * @var string
     */
    public $template = '';

    /**
     * @var bool
     */
    public $active = true;

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * MailTemplate
     */
    public function __construct()
    {
        $this->_TimestampTrait();

    }

    /**
     * @param string $event
     * @return MailTemplate
     */
    public function setEvent($event) : MailTemplate
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return string
     */
    public function getEvent() : string
    {
        return $this->event;
    }

    /**
     * @param string $recipientType
     * @return MailTemplate
     */
    public function setRecipientType($recipientType) : MailTemplate
    {
        $this->recipientType = $recipientType;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecipientType() : string
    {
        return $this->recipientType;
    }

    /**
     * @param string $template
     * @return MailTemplate
     */
    public function setTemplate($template) : MailTemplate
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate() : string
    {
        return $this->template;
    }

    /**
     * @param bool $active
     * @return MailTemplate
     */
    public function setActive($active) : MailTemplate
    {
        $this->active = $active;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return $this->active;
    }

    /**
     * @return array
     */
    public function validate()
    {
        $errors = array();

        if (!$this->institutionId) {
            $errors['institutionId'] = 'Invalid value: institutionId';
        }

        if (!$this->event) {
            $errors['event'] = 'Invalid value: event';
        }

        if (!$this->recipientType) {
            $errors['recipientType'] = 'Invalid value: recipientType';
        }

        return $errors;
    }

}
