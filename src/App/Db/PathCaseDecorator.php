<?php


namespace App\Db;


use Bs\Event\StatusEvent;
use Tk\Collection;
use Tk\Mail\CurlyMessage;
use Tk\Mail\Message;
use Bs\Db\Status;
use Uni\Db\User;

class PathCaseDecorator
{


    /**
     * @param StatusEvent $event
     * @param MailTemplate $mailTemplate
     * @throws \Exception
     */
    public static function onCreateMessages(StatusEvent $event, MailTemplate $mailTemplate)
    {
        /** @var PathCase $case */
        $case = $event->getStatus()->getModel();
        $status = $event->getStatus();
        $messageList = array();

        // Create one message per recipient
        if ($mailTemplate->getRecipientType() == 'client') { // Should only ever be one client
            $message = CurlyMessage::create($mailTemplate->getTemplate());
            $message->set('_mailTemplate', $mailTemplate);
            $message->addTo($case->getClient()->getEmail());
            $message->replace(Collection::prefixArrayKeys(array(
                'type' => 'client',
                'name' => $case->getClient()->getName(),
                'email' => $case->getClient()->getEmail()
            ), 'recipient::'));
            $messageList[] = $message;
        } else if ($mailTemplate->getRecipientType() == 'staff') {  // all staff involved in the pathCase
            $staffList =  $status->findUsersByType($mailTemplate->getRecipientType());
            if ($case->getUser())
                $staffList[$case->getUserId()] = $case->getUser();
            /** @var User $user */
            foreach ($staffList as $user) {
                $message = CurlyMessage::create($mailTemplate->getTemplate());
                $message->set('_mailTemplate', $mailTemplate);
                $message->addTo($user->getEmail());
                $message->replace(Collection::prefixArrayKeys(array(
                    'type' => 'staff',
                    'name' => $user->getName(),
                    'email' => $user->getEmail()
                ), 'recipient::'));
                $messageList[] = $message;
            }
        }

        foreach ($messageList as $message) {

            $message->setSubject('[#' . $case->getId() . '] ' . ucfirst($status->getName()) . ': ' . $case->getPathologyId());
            $message->setFrom(Message::joinEmail($case->getInstitution()->getEmail(), $case->getInstitution()->getName()));

            // Setup default message dynamic vars
            $message->replace(Collection::prefixArrayKeys([
                'id' => $status->getId(),
                'name' => $status->getName(),
                'message' => nl2br($status->getMessage()),
                'event' => $status->getEvent(),
//                'fkey'  => $status->getFkey(),
//                'fid' => $status->getFId()
            ], 'status::'));
            $message->replace(Collection::prefixArrayKeys(\App\Db\PathCaseMap::create()->unmapForm($case), 'pathCase::'));
            if ($case->getInstitution())
                $message->replace(Collection::prefixArrayKeys(\Uni\Db\InstitutionMap::create()->unmapForm($case->getInstitution()), 'institution::'));
            if ($case->getClient())
                $message->replace(Collection::prefixArrayKeys(\App\Db\ClientMap::create()->unmapForm($case->getClient()), 'client::'));

            $event->addMessage($message);
        }
    }
}