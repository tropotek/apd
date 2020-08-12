<?php
namespace App\Listener;

use Dom\Exception;
use Tk\ConfigTrait;
use Tk\Event\Subscriber;
use Tk\Mail\CurlyMessage;


/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class StatusMailHandler implements Subscriber
{
    use ConfigTrait;

    /**
     * @param \Uni\Event\StatusEvent $event
     * @throws \Exception
     */
    public function onStatusChange(\Uni\Event\StatusEvent $event)
    {
        // do not send messages
        if (!$event->getStatus()->isNotify()) return;

//
//        // Find all mail templates for this status update
//        $mailTemplateList = \App\Db\MailTemplateMap::create()->findFiltered(array(
//            'active' => true,
//            'courseId' => $event->getStatus()->getCourseId(),
//            'event' => $event->getStatus()->getEvent()
//        ));
//
//        /** @var \App\Db\MailTemplate $mailTemplate */
//        foreach ($mailTemplateList as $mailTemplate) {
//            // send a message for each template found
//            try {
//                $modelStrategy = $event->getStatus()->getModelStrategy();
//                if (!$modelStrategy) {
//                    \Tk\Log::warning('onStatusChange: Strategy Not Found For: ' . $event->getStatus()->getFkey());
//                    continue;
//                }
//
//                // create and populate status email message
//                $message = CurlyMessage::create($mailTemplate->getTemplate());
//                $message->set('_mailTemplate', $mailTemplate);
//                $modelStrategy->formatStatusMessage($event->getStatus(), $message);
//
//                // Save the message for sending
//                if ($message instanceof \Tk\Mail\Message) {
//                    \App\Util\StatusMessage::setStatus($message, $event->getStatus());
//                    \App\Util\StatusMessage::setCourse($message, $event->getStatus()->getCourse());
//                    \App\Util\StatusMessage::setSubject($message, $event->getStatus()->getSubject());
//                    if ($mailTemplate->getRecipient())
//                        \App\Util\StatusMessage::setRecipient($message, $mailTemplate->getRecipient());
//                    if ($message->hasRecipient()) {
//                        $event->addMessage($message);
//                    }
//                }
//            } catch (\Exception $e) {
//                \Tk\Log::error($e->getMessage());
//            }
//        }
//        if (!count($event->getMessageList())) $event->stopPropagation();
    }

    /**
     * @param \Uni\Event\StatusEvent $event
     * @throws \Exception
     */
    public function onSendStatusMessages(\Uni\Event\StatusEvent $event)
    {
        if (!$event->getStatus()->isNotify()) return;   // do not send messages

//        /** @var \Tk\Mail\CurlyMessage $message */
//        foreach ($event->getMessageList() as $message) {
//            if (!count($message->getTo())) {
//                \Tk\Log::error('onSendStatusMessages: Recipient Not Found');
//                continue;
//            }
//            if (\App\Config::getInstance()->isDebug() && $message->has('recipient::type')) {
//                $message->setSubject($message->getSubject() . ' [' . ucwords($message->get('recipient::type')) . ']');
//            }
//
//            // Check the message is not empty
//            $tst = trim(preg_replace('/\W/', '', html_entity_decode(strip_tags($message->getParsed()))));
//            if ($tst == '') {
//                \Tk\Log::warning($message->getSubject() . ' [EMPTY - NOT SENT]');
//                continue;
//            }
//
//            // Add the profile signature var
//            $message->setBody(sprintf('<div>%s {sig}</div>', $message->getBody()));
//
//            // Fix all message relative paths
//            $tpl = null;
//            try {
//                $tpl = \Dom\Template::load($message->getBody());
//            } catch (Exception $e) {
//                \Tk\Log::notice($e->__toString());
//            }
//            if ($tpl) {
//                $config = \App\Config::getInstance();
//                /** @var \Uni\Db\InstitutionIface $institution */
//                $institution = $config->getInstitutionMapper()->find($message->get('institution::id'));
//                $dm = new \Dom\Modifier\Modifier();
//
//                $path = \Uni\Uri::create('/');
//                $path->setHost($config->getSiteHost());
//                if ($institution && $institution->getDomain()) {
//                    $path->setHost($institution->getDomain());
//                }
//                $dm->add(new \Dom\Modifier\Filter\UrlPath($path));
//                $dm->execute($tpl->getDocument(false));
//                $message->setBody($tpl->toString(true));
//            }
//
//            // Send message
//            if (!\App\Config::getInstance()->getEmailGateway()->send($message)) {
//                \Tk\Log::warning('Email Not Sent: ' . implode(', ', $message->getTo()));
//                \Tk\Log::warning(implode("\n", \App\Config::getInstance()->getEmailGateway()->getErrors()));
//            } else {
//                \Tk\Log::notice('Email To: ' . implode(', ', $message->getTo()));
//                \Tk\Log::notice('      Subject: ' . $message->getSubject() );
//                $event->set('sent', $event->get('sent', 0)+1);
//            }
//        }

    }

    /**
     * @param \Tk\Mail\MailEvent $event
     */
    public function postSend(\Tk\Mail\MailEvent $event)
    {
        /** @var \Tk\Ml\Db\MailLog $mailLog */
        $mailLog = $event->get('mailLog');
        //$message = $event->getMessage();

        if ($this->getConfig()->getInstitution())
            $mailLog->setForeignModel($this->getConfig()->getInstitution());


//        if (!$mailLog || !$message instanceof \Tk\Mail\CurlyMessage) return;
//        // Link status to mail log if one exists
//        if ($message->get('status::id')) {
//            \App\Db\MailLogMap::create()->addStatus($mailLog->getId(), $message->get('status::id'));
//        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \Bs\StatusEvents::STATUS_CHANGE => array('onStatusChange', 0),
            \Bs\StatusEvents::STATUS_SEND_MESSAGES => array('onSendStatusMessages', 0),
            \Tk\Mail\MailEvents::POST_SEND => array('postSend', 10)
        );
    }

}


