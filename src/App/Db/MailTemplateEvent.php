<?php
namespace App\Db;

/**
 * @author Mick Mifsud
 * @created 2020-08-17
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class MailTemplateEvent extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $event = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $emailTags = '';


    /**
     * MailTemplateEvent
     */
    public function __construct()
    {

    }
    
    /**
     * @param string $name
     * @return MailTemplateEvent
     */
    public function setName($name) : MailTemplateEvent
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @param string $event
     * @return MailTemplateEvent
     */
    public function setEvent($event) : MailTemplateEvent
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
     * @param string $description
     * @return MailTemplateEvent
     */
    public function setDescription($description) : MailTemplateEvent
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * @param string $emailTags
     * @return MailTemplateEvent
     */
    public function setEmailTags($emailTags) : MailTemplateEvent
    {
        $this->emailTags = $emailTags;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmailTags() : string
    {
        return $this->emailTags;
    }

    /**
     * @return array
     */
    public function validate()
    {
        $errors = array();

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        if (!$this->event) {
            $errors['event'] = 'Invalid value: event';
        }

        return $errors;
    }

}
