<?php
namespace App\Listener;

use App\Db\MailTemplateEvent;
use App\Db\MailTemplateEventMap;
use App\Db\Notice;
use Tk\ConfigTrait;
use Tk\Db\Map\Model;
use Tk\Db\ModelInterface;
use Tk\Event\Subscriber;
use Tk\Mail\CurlyMessage;


/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class NoticeHandler implements Subscriber
{
    use ConfigTrait;

    /**
     * @param \Bs\Event\DbEvent $event
     */
    public function onModelInsert(\Bs\Event\DbEvent $event)
    {
        //
//        $strat = Notice::makeNoticeDecorator($event->getModel());
//        if (!$event->getModel() instanceof Notice && $strat) {
//            vd('Add notify on new model insert');
//            Notice::create($event->getModel());
//
//
//
//        }
    }

    /**
     * After a status change is triggered Look for any existing template to be sent
     * If found then create that template as a message
     *
     * @param \Bs\Event\StatusEvent $event
     * @throws \Exception
     */
    public function onStatusChange(\Bs\Event\StatusEvent $event)
    {
        // TODO: Only add one notification on bulk status changes????
        $strat = Notice::makeNoticeDecorator($event->getStatus()->getModel());
        if ($strat) {
            vd($event->getStatus()->getModel()->getCurrentStatus());
            vd('Add notify on status change');


        }
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \Bs\DbEvents::MODEL_INSERT =>  array('onModelInsert', 0),
            \Bs\StatusEvents::STATUS_CHANGE => array('onStatusChange', 0),
        );
    }

}


