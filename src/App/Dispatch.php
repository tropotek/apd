<?php
namespace App;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Dispatch extends \Uni\Dispatch
{
    /**
     * @throws \Exception
     */
    public function init()
    {
        parent::init();
        $dispatcher = $this->getDispatcher();

        $dispatcher->addSubscriber(new \App\Listener\MailTemplateHandler());
        $dispatcher->addSubscriber(new \App\Listener\NoticeHandler());
        $dispatcher->addSubscriber(new \App\Listener\EditLogHandler());

        if (!$this->getConfig()->isCli()) {
            $dispatcher->addSubscriber(new \App\Listener\NavRendererHandler());
            $dispatcher->addSubscriber(new \Bs\Listener\PageLoaderHandler());
        }

    }

}