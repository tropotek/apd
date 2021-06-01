<?php
namespace App\Listener;

use App\Db\MailTemplateEvent;
use App\Db\MailTemplateEventMap;
use App\Db\Notice;
use App\Db\Traits\EditLogTrait;
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
class EditLogHandler implements Subscriber
{
    use ConfigTrait;

    /**
     * @param \Bs\Event\DbEvent $event
     */
    public function onModelSave(\Bs\Event\DbEvent $event)
    {
        /** @var Model|EditLogTrait $model */
        $model = $event->getModel();

        // TODO: In the future see if we can compare the previous state with the
        //       current state and only save a log if there are differences

        if (in_array(EditLogTrait::class, class_uses($model))) {
            $log = $model->createEditLog();
            $log->save();
        }
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \Bs\DbEvents::MODEL_SAVE_POST =>  array('onModelSave', 0)
        );
    }

}


