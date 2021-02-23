<?php
namespace App\Db;

use App\Db\Traits\MailTemplateEventTrait;
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
    use MailTemplateEventTrait;


    const RECIPIENT_AUTHOR              = 'author';
    const RECIPIENT_CLIENT              = 'client';
    const RECIPIENT_OWNER               = 'owner';
    const RECIPIENT_PATHOLOGIST         = 'pathologist';
    const RECIPIENT_STUDENTS            = 'students';
    const RECIPIENT_SERVICE_TEAM        = 'serviceTeam';


    public static function getRecipientSelectList()
    {
        return [
            'Client' => self::RECIPIENT_CLIENT,             // Changes based on if it is a case or request
            'Animal Owner' => self::RECIPIENT_OWNER,
            'Pathologist' => self::RECIPIENT_PATHOLOGIST,
            'Service Team' => self::RECIPIENT_SERVICE_TEAM,
            'Students' => self::RECIPIENT_STUDENTS,
            'Author' => self::RECIPIENT_AUTHOR              // Changes based on if it is a case or request
        ];
    }

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var int
     */
    public $institutionId = 0;

    /**
     * @var int
     */
    public $mailTemplateEventId = 0;

    /**
     * The mail template event name that triggers the sending of this template
     *
     * @var string
     */
    public $event = '';

    /**
     * Identify the recipient type of this template (staff, client, etc...)
     *
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
        $this->institutionId = $this->getConfig()->getInstitutionId();
    }

//    /**
//     * @param string $event
//     * @return MailTemplate
//     */
//    public function setEvent($event) : MailTemplate
//    {
//        $this->event = $event;
//        return $this;
//    }
//
//    /**
//     * @return string
//     */
//    public function getEvent() : string
//    {
//        return $this->event;
//    }

//    /**
//     * @return MailTemplateEvent|object|\Tk\Db\Map\Model|null
//     * @throws \Exception
//     */
//    public function getMailTemplateEvent()
//    {
//        return MailTemplateEventMap::create()->findFiltered(array('event' => $this->event))->current();
//    }

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

        if (!$this->recipientType) {
            $errors['recipientType'] = 'Invalid value: recipientType';
        }

        return $errors;
    }

}
