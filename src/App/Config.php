<?php
namespace App;


use App\Db\Permission;
use Tk\Mail\Message;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Michael Mifsud
 */
class Config extends \Uni\Config
{


    /**
     * @param \Tk\EventDispatcher\EventDispatcher $dispatcher
     * @throws \Exception
     */
    public function setupDispatcher($dispatcher)
    {
        \App\Dispatch::create($dispatcher);
    }

    /**
     * @return \Bs\Listener\PageTemplateHandler
     */
    public function getPageTemplateHandler()
    {
        if (!$this->get('page.template.handler')) {
            $this->set('page.template.handler', new \App\Listener\PageTemplateHandler());
        }
        return $this->get('page.template.handler');
    }

    /**
     * @return \Bs\Listener\AuthHandler
     */
    public function getAuthHandler()
    {
        if (!$this->get('auth.handler')) {
            $this->set('auth.handler', new \App\Listener\AuthHandler());
        }
        return $this->get('auth.handler');
    }

    /**
     * @return Permission|null
     */
    public function getPermission()
    {
        return \App\Db\Permission::getInstance();
    }


    /**
     * getEmailGateway
     *
     * @return \Tk\Mail\Gateway
     */
    public function getEmailGateway()
    {
        if (!$this->get('email.gateway')) {
            if ($this->getInstitution() && $this->getInstitution()->getData()->get('inst.dkim.enable')) {
                $this['mail.dkim.domain'] = $this->getInstitution()->getData()->get('inst.dkim.domain');
                $this['mail.dkim.private_string'] = $this->getInstitution()->getData()->get('inst.dkim.private');
            }
            $gateway = new \Tk\Mail\Gateway($this);
            $gateway->setDispatcher($this->getEventDispatcher());
            $this->set('email.gateway', $gateway);
        }
        return $this->get('email.gateway');
    }


    /**
     * @param string $xtplFile The mail template filename as found in the /html/xtpl/mail folder
     * @return \Tk\Mail\CurlyMessage
     * @TODO: Should this be a direct filepath so we can create a message with any template?
     */
    public function createMessage($xtplFile = 'mail.default')
    {
        $config = self::getInstance();
        $request = $config->getRequest();

        $template = '{content}';
        $xtplFile = str_replace(array('./', '../'), '', strip_tags(trim($xtplFile)));
        $xtplFile = $config->getSitePath() . $config->get('template.xtpl.path') . '/mail/' . $xtplFile . $config->get('template.xtpl.ext');
        if (is_file($xtplFile)) {
            $template = file_get_contents($xtplFile);
            if (!$template) {
                \Tk\log::warning('Template file not found, using default template: ' . $xtplFile);
                $template = '{content}';
            }
        }

        $message = \Tk\Mail\CurlyMessage::create($template);
        $message->setFrom($config->get('site.email'));

        if ($this->getInstitution()) {
            $message->setFrom(Message::joinEmail($this->getInstitution()->getEmail(),
                $this->getInstitution()->getName()));
            $message->addHeader('Sender', 'anat-vet@apd-vet.com');
            $message->setReplyTo(Message::joinEmail($this->getInstitution()->getEmail(),
                $this->getInstitution()->getName()));
        }

        if ($request->getTkUri())
            $message->set('_uri', \Tk\Uri::create('')->toString());
        if ($request->getReferer())
            $message->set('_referer', $request->getReferer()->toString());
        if ($request->getClientIp())
            $message->set('_ip', $request->getClientIp());
        if ($request->getUserAgent())
            $message->set('_user_agent', $request->getUserAgent());

        return $message;
    }

//    /**
//     * validate a filename and see if we think it is a script or harmful
//     * to upload to the server
//     *
//     * @param $filename
//     * @return bool
//     * @deprecated Moved to \Bs\Config
//     */
//    public static function validateFile($filename)
//    {
//        $filename = basename($filename);
//        $ext = trim(\Tk\File::getExtension($filename), '.');
//        // TODO: make these configurable in the config.php
//        $exclude = array('exe', 'com', 'php', 'perl', 'php5', 'php4', 'html', 'css', 'js');
//        $include = array();
//
//        if (count($exclude)) {
//            if (in_array($ext, $exclude)) {
//                return false;
//            }
//        }
//        if (count($include)) {
//            return in_array($ext, $include);
//        }
//        return true;
//    }

}