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
     * The human readable name for this email event
     * @var string
     */
    public $name = '';

    /**
     * The event value that is sent when this event is triggered
     * @var string
     */
    public $event = '';

    /**
     * This is the callable function in the format of "MyNameSpc\MyClass::myCallbackMethod"
     *   This callback method is used to render the mail message template
     *
     * @var string
     */
    public $callback = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * (Array) available tags that can be used in the template content {tag} = "Some dynamic value"
     *
     * @var array
     */
    public $tags = [];


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
     * @param string $callback
     * @return MailTemplateEvent
     */
    public function setCallback($callback) : MailTemplateEvent
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return string
     */
    public function getCallback() : string
    {
        return $this->callback;
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
     * @param array $tags
     * @return MailTemplateEvent
     */
    public function setTags($tags) : MailTemplateEvent
    {
        $this->tags = $tags;
        return $this;
    }

    public function addTag(string $tag, string $description)
    {
        $this->tags[$tag] = $description;
        return$this;
    }

    /**
     * @param string $key
     * @return array    Returns the removed key and description value if found
     */
    public function removeTag(string $key)
    {
        $v = array($key => '');
        if (isset($this->tags[$key])) {
            $v = array($key => $this->tags[$key]);
            unset($this->tags[$key]);
        }
        return $v;
    }

    /**
     * @return array
     */
    public function getTags() : array
    {
        return $this->tags;
    }

    /**
     * @return array
     */
    public function validate()
    {
        $errors = [];

        if (!$this->name) {
            $errors['name'] = 'Invalid value: name';
        }

        if (!$this->event) {
            $errors['event'] = 'Invalid value: event';
        }

        if (!$this->callback) {
            $errors['callback'] = 'Invalid value: callback';
        }

        return $errors;
    }

}
