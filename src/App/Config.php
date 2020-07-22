<?php
namespace App;


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
     * @param string $type (optional) If set returns only the permissions for that user type otherwise returns all permissions
     * @return array
     */
    public function getPermissionList($type = '')
    {
         return \App\Db\Permission::getPermissionList($type);
    }


}