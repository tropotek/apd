<?php
namespace App\Listener;

use App\Db\MailTemplateEvent;
use App\Db\MailTemplateEventMap;
use Tk\ConfigTrait;
use Tk\Event\Subscriber;


/**
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class MailTemplateHandler implements Subscriber
{
    use ConfigTrait;

    /**
     * After a status change is triggered Look for any existing template to be sent
     * If found then create that template as a message
     *
     * @param \Bs\Event\StatusEvent $event
     * @throws \Exception
     */
    public function onStatusChange(\Bs\Event\StatusEvent $event)
    {
        // do not send messages if notify is false
        if (!$event->getStatus()->isNotify()) return;

        // Find the mail template event type from the status.event field
        /** @var MailTemplateEvent $mEvent */
        $mEvent = MailTemplateEventMap::create()->findFiltered(array('event' => $event->getStatus()->getEvent()))->current();
        if (!$mEvent) return;

        // Find all mail templates for this status update
        $mailTemplateList = \App\Db\MailTemplateMap::create()->findFiltered(array(
            'active' => true,
            'institutionId' => $this->getConfig()->getInstitutionId(),
            'mailTemplateEventId' => $mEvent->getId()
        ));

        /** @var \App\Db\MailTemplate $mailTemplate */
        foreach ($mailTemplateList as $mailTemplate) {
            try {
                if (!is_callable($mEvent->getCallback())) continue;
                call_user_func_array($mEvent->getCallback(), array($event, $mailTemplate));
            } catch (\Exception $e) {
                \Tk\Log::error($e->getMessage());
            }
        }
        // Stop if no messages are being sent
        if (!count($event->getMessageList())) $event->stopPropagation();
    }

    /**
     * If messages exist in the queue, add recipients and insert the
     * mail template variables for formatting templates.
     *
     * @param \Bs\Event\StatusEvent $event
     * @throws \Exception
     */
    public function onStatusSendMessages(\Bs\Event\StatusEvent $event)
    {
        if (!$event->getStatus()->isNotify()) return;   // do not send messages

        /** @var \Tk\Mail\CurlyMessage $message */
        foreach ($event->getMessageList() as $message) {
            /** @var \App\Db\MailTemplate $mailTemplate */
            $mailTemplate = $message->get('_mailTemplate');
            if (!$mailTemplate) continue;

            // Call the template callback as first step
            $mEvent = $mailTemplate->getMailTemplateEvent();
            if (!$mEvent) continue;

            if (!count($message->getTo())) {
                \Tk\Log::error('onSendStatusMessages: Recipient Not Found');
                continue;
            }

            if (\App\Config::getInstance()->isDebug() && $message->has('recipient::type')) {
                $message->setSubject($message->getSubject() . ' [' . ucwords($message->get('recipient::type')) . ']');
            }


            // Check the message content is not empty
            $tst = trim(preg_replace('/\W/', '', html_entity_decode(strip_tags($message->getParsed()))));
            if ($tst == '') {
                \Tk\Log::warning($message->getSubject() . ' [EMPTY - NOT SENT]');
                continue;
            }

            // TODO: Add the signature var
            //$message->setBody(sprintf('<div>%s {sig}</div>', $message->getBody()));

            // Fix all message relative paths
            $tpl = null;
            try {
                $tpl = \Dom\Template::load('<div>'. $message->getBody().'</div>');
            } catch (\Exception $e) {
                \Tk\Log::notice($e->__toString());
            }
            if ($tpl) {
                $config = \App\Config::getInstance();
                /** @var \Uni\Db\InstitutionIface $institution */
                $institution = $config->getInstitutionMapper()->find($message->get('institution::id'));
                $dm = new \Dom\Modifier\Modifier();

                $path = \Uni\Uri::create('/');
                $path->setHost($config->getSiteHost());
                if ($institution && $institution->getDomain()) {
                    $path->setHost($institution->getDomain());
                }
                $dm->add(new \Dom\Modifier\Filter\UrlPath($path));
                $dm->execute($tpl->getDocument(false));
                $message->setBody($tpl->toString(true));
            }

            // Send message
            if (!\App\Config::getInstance()->getEmailGateway()->send($message)) {
                \Tk\Log::warning('Email Not Sent: ' . implode(', ', $message->getTo()));
                \Tk\Log::warning(implode("\n", \App\Config::getInstance()->getEmailGateway()->getErrors()));
            } else {
                \Tk\Log::notice('Email To: ' . implode(', ', $message->getTo()));
                \Tk\Log::notice('      Subject: ' . $message->getSubject() );
                $event->set('sent', $event->get('sent', 0)+1);
            }
        }

    }

    /**
     * @param \Tk\Mail\MailEvent $event
     */
    public function postSend(\Tk\Mail\MailEvent $event)
    {
        /** @var \Tk\Ml\Db\MailLog $mailLog */
        $mailLog = $event->get('mailLog');

        if ($this->getConfig()->getInstitution() && $mailLog) {
            $mailLog->setForeignModel($this->getConfig()->getInstitution());
            $mailLog->save();
        }

        /** @var \Tk\Mail\CurlyMessage $message */
        $message = $event->getMessage();
        if ($message instanceof \Tk\Mail\CurlyMessage) {
            // Link status to mail log if one exists
            if ($message->has('status::id')) {
                $message->addHeader('X-status-id', $message->get('status::id'));
                /** @var \Bs\Db\Status $status */
                $status = \Bs\Db\StatusMap::create()->find($message->get('status::id'));
                if ($status) {
                    $message->addHeader('X-status-name', $status->getName());
                }
            }
        }

    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            \Bs\StatusEvents::STATUS_CHANGE => array('onStatusChange', 0),
            \Bs\StatusEvents::STATUS_SEND_MESSAGES => array('onStatusSendMessages', 0),
            \Tk\Mail\MailEvents::POST_SEND => array('postSend', -1)
        );
    }

}


