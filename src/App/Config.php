<?php
namespace App;


use App\Db\Permission;

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