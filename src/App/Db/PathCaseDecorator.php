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
            $staffList =  array();



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
            $message->replace(Collection::prefixArrayKeys(\Uni\Db\InstitutionMap::create()->unmapForm($case->getInstitution()), 'institution::'));
            $message->replace(Collection::prefixArrayKeys(\App\Db\ClientMap::create()->unmapForm($case->getClient()), 'client::'));
            $message->replace(Collection::prefixArrayKeys(\App\Db\PathCaseMap::create()->unmapForm($case), 'pathCase::'));
            $message->replace(Collection::prefixArrayKeys(\Bs\Db\StatusMap::create()->unmapForm($status), 'status::'));

//        // Recipient
//        $rec = array();
//        if ($mailTemplate->getRecipientType() == 'client') {
//            $rec = array(
//                'type' => 'client',
//                'email' => $case->getClient()->getEmail(),
//                'name' => $case->getClient()->getName()
//            );
//            $message->addTo($case->getClient()->getEmail());
//        } else if ($mailTemplate->getRecipientType() == 'staff') {
//            $rec = array(
//                'type' => 'staff',
//                'email' => $case->getClient()->getEmail(),
//                'name' => $case->getClient()->getName()
//            );
//        }
//        $message->replace(Collection::prefixArrayKeys($rec, 'recipient::'));

            $event->addMessage($message);
        }
exit;

        // TODO: recipients need to be competed when we know whats going on

//        switch ($mailTemplate->getRecipientType()) {
//            case 'client':
//                if ($case && $case->getEmail()) {
//                    $message->addTo(Message::joinEmail($case->getEmail(), $case->getName()));
//                    $message->set('recipient::email', $case->getEmail());
//                    $message->set('recipient::name', $case->getName());
//                }
//                break;
//            case 'staff':
//                $staffList = $status->getSubject()->getCourse()->getUsers();
//                if (count($staffList)) {
//                    /** @var User $s */
//                    foreach ($staffList as $s) {
//                        $message->addBcc(Message::joinEmail($s->getEmail(), $s->getName()));
//                    }
//                    $message->addTo(Message::joinEmail($status->getSubject()->getCourse()->getEmail(), $status->getSubjectName()));
//                    $message->set('recipient::email', $status->getSubject()->getCourse()->getEmail());
//                    $message->set('recipient::name', $status->getSubjectName());
//                }
//                break;
//        }

    }
}