<?php


namespace App\Db;


use Bs\Event\StatusEvent;
use Tk\Collection;
use Tk\Mail\CurlyMessage;
use Tk\Mail\Message;
use Bs\Db\Status;
use Uni\Db\User;

class RequestDecorator
{

    /**
     * @param StatusEvent $event
     * @param MailTemplate $mailTemplate
     * @throws \Exception
     */
    public static function onCreateMessages(StatusEvent $event, MailTemplate $mailTemplate)
    {
        /** @var Request $case */
        $request = $event->getStatus()->getModel();
        if (!$request instanceof Request) {
            return;
        }

        /** @var Request $request */
        $request = $event->getStatus()->getModel();
        $status = $event->getStatus();
        $messageList = array();

        // Create one message per recipient
        if ($mailTemplate->getRecipientType() == 'client') { // Should only ever be one client
            $message = CurlyMessage::create($mailTemplate->getTemplate());
            $message->set('_mailTemplate', $mailTemplate);
            $message->addTo($request->getClient()->getEmail());
            $message->replace(Collection::prefixArrayKeys(array(
                'type' => 'client',
                'name' => $request->getClient()->getName(),
                'email' => $request->getClient()->getEmail()
            ), 'recipient::'));
            $messageList[] = $message;
        } else if ($mailTemplate->getRecipientType() == 'staff') {  // all staff involved in the pathCase
            $staffList =  $status->findUsersByType($mailTemplate->getRecipientType());
            if ($request->getPathCase()->getUser())
                $staffList[$request->getPathCase()->getUserId()] = $request->getPathCase()->getUser();
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

            $message->setSubject('[#' . $request->getId() . '] Pathology Request - ' . ucfirst($status->getName()) . ': ' . $request->getPathCase()->getPathologyId());
            $message->setFrom(Message::joinEmail($request->getPathCase()->getInstitution()->getEmail(),
                $request->getPathCase()->getInstitution()->getName()));

            // Setup default message dynamic vars
            $message->replace(Collection::prefixArrayKeys([
                'id' => $status->getId(),
                'name' => $status->getName(),
                'message' => nl2br($status->getMessage()),
                'event' => $status->getEvent(),
//                'fkey'  => $status->getFkey(),
//                'fid' => $status->getFId()
            ], 'status::'));
            $message->replace(Collection::prefixArrayKeys(\App\Db\RequestMap::create()->unmapForm($request), 'request::'));
            if ($request->getPathCase() && $request->getPathCase()->getInstitution())
                $message->replace(Collection::prefixArrayKeys(\Uni\Db\InstitutionMap::create()->unmapForm($request->getPathCase()->getInstitution()), 'institution::'));
            if ($request->getClient())
                $message->replace(Collection::prefixArrayKeys(\App\Db\ContactMap::create()->unmapForm($request->getClient()), 'client::'));
            if ($request->getPathCase())
                $message->replace(Collection::prefixArrayKeys(\App\Db\PathCaseMap::create()->unmapForm($request->getPathCase()), 'pathCase::'));

            $event->addMessage($message);
        }
    }
}