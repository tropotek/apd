<?php
namespace App\Db;

use Bs\Db\Traits\ForeignModelTrait;
use Bs\Db\Traits\TimestampTrait;
use Bs\Db\Traits\UserTrait;
use Bs\Db\UserIface;
use Tk\Db\ModelInterface;
use Tk\Db\Tool;

/**
 * @author Mick Mifsud
 * @created 2021-01-28
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class Notice extends \Tk\Db\Map\Model implements \Tk\ValidInterface
{
    use UserTrait;
    use TimestampTrait;
    use ForeignModelTrait;


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
    public $type = '';

    /**
     * @var string
     */
    public $subject = '';

    /**
     * @var string
     */
    public $body = '';

    /**
     * @var array
     */
    public $param = array();

    /**
     * @var \DateTime
     */
    public $modified = null;

    /**
     * @var \DateTime
     */
    public $created = null;


    /**
     * Notice
     */
    public function __construct()
    {
        $this->_TimestampTrait();

    }

    /**
     * @param null|ModelInterface $model
     * @param null|UserIface $author
     * @return Notice
     */
    public static function create($model = null, $author = null)
    {
        $obj = new static();
        $obj->setForeignModel($model);
        if ($author)
            $obj->setUserId($author);
        return $obj;
    }

    /**
     * This method automatically execute the Decorator's onCreateNotice() method
     *
     * @param string $subject
     * @param null|ModelInterface $model
     * @param array|UserIface[] $recipients
     * @param null|int|UserIface $author (optional) If 0 then it is assumed to be a system message not from a user
     * @return Notice
     */
    public static function createNotice($subject, $model = null, $recipients = [], $author = null)
    {
        $obj = static::create($model, $author);
        $obj->setSubject($subject);
        $obj->addRecipient($recipients);
        $obj->getNoticeDecorator()->onCreateNotice($obj);
        return $obj;
    }

    /**
     * @param string|ModelInterface $model
     * @return null|NoticeDecoratorInterface
     */
    public static function makeNoticeDecorator($model)
    {
        if (is_object($model))
            $model = get_class($model);
        $model = str_replace('NoticeDecorator', '', $model);
        $class = $model . 'NoticeDecorator';
        $strategy = null;
        if (class_exists($class))
            $strategy = new $class();
        return $strategy;
    }

    /**
     * @param string|ModelInterface $model
     * @return null|NoticeDecoratorInterface
     */
    public function getNoticeDecorator()
    {
        return self::makeNoticeDecorator($this->getFkey());
    }

    /**
     * @param UserIface|UserIface[]|array $user
     * @return $this
     */
    function addRecipient($user)
    {
        if (!is_array($user)) $user = array($user);
        foreach ($user as $u) {  // Create an unread notice for the user
            if (!$u) continue;
            $r = new NoticeRecipient();
            $r->setNoticeId($this->getVolatileId());
            $r->setUserId($u->getId());
            $r->save();
        }
        return $this;
    }

    /**
     * @param UserIface $user
     * @return NoticeRecipient
     */
    public function getNoticeRecipient($user)
    {
        if (!$user instanceof UserIface) return null;
        return NoticeRecipientMap::create()->findRecipient($this->getId(), $user->getId());
    }

    /**
     * Get a list of recipients that this notice is set to
     *
     * @param null|Tool $tool
     * @return NoticeRecipient[]
     */
    public function getNoticeRecipientList($tool = null)
    {
        return NoticeRecipientMap::create()->findFiltered(array('' => $this->getId()), $tool);
    }



    /**
     * @param string $type
     * @return Notice
     */
    public function setType($type) : Notice
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * @param string $subject
     * @return Notice
     */
    public function setSubject($subject) : Notice
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject() : string
    {
        return $this->subject;
    }

    /**
     * @param string $body
     * @return Notice
     */
    public function setBody($body) : Notice
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody() : string
    {
        return $this->body;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasParam($name)
    {
        return isset($this->param[$name]);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function setParam($name, $value)
    {
        $this->param[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getParam($name)
    {
        if ($this->hasParam($name))
            return $this->param[$name];
        return null;
    }



    /**
     * Create and return the icon css for the notice interface
     *
     * @return string
     */
    public function getIconCss()
    {
        $icon = 'fa fa-envelope';
        //$icon = 'fa fa-comment';
        //$icon = 'fa fa-eye';
        switch ($this->getFkey()) {
            case 'App\Db\PathCase':
                $icon = 'fa fa-paw';
                break;
            case 'App\Db\Request':
                $icon = 'fa fa-medkit';
                break;
            case 'App\Db\InvoiceItem':
                $icon = 'fa fa-money';
                break;
            case 'App\Db\Contact':
                $icon = 'fa fa-user-md';
                break;
        }
        return $icon;
    }

}
