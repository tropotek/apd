<?php


namespace App\Db;


use Bs\Event\StatusEvent;
use Tk\Collection;
use Tk\Mail\CurlyMessage;
use Tk\Mail\Message;
use Bs\Db\Status;
use Tk\ObjectUtil;
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
        if (!$case instanceof PathCase) {
            return;
        }
        $status = $event->getStatus();

        $message = CurlyMessage::create($mailTemplate->getTemplate());
        $message->set('_mailTemplate', $mailTemplate);
        $message->setSubject('[#' . $case->getId() . '] ' . ObjectUtil::basename($case) . ' ' . ucfirst($status->getName()) . ': ' . $case->getPathologyId());
        $message->setFrom(Message::joinEmail($case->getInstitution()->getEmail(), $case->getInstitution()->getName()));

        $message->replace(Collection::prefixArrayKeys([
            'id' => $status->getId(),
            'name' => $status->getName(),
            'message' => nl2br($status->getMessage()),
            'event' => $status->getEvent()
        ], 'status::'));
        $message->replace(Collection::prefixArrayKeys(\App\Db\PathCaseMap::create()->unmapForm($case), 'pathCase::'));
        if ($case->getInstitution())
            $message->replace(Collection::prefixArrayKeys(\Uni\Db\InstitutionMap::create()->unmapForm($case->getInstitution()), 'institution::'));
        if ($case->getClient())
            $message->replace(Collection::prefixArrayKeys(\App\Db\ContactMap::create()->unmapForm($case->getClient()), 'client::'));

        switch($mailTemplate->getRecipientType()) {
            case MailTemplate::RECIPIENT_AUTHOR:
                $message->addTo($case->getUser()->getEmail());
                $message->replace(Collection::prefixArrayKeys(array(
                    'type' => $mailTemplate->getRecipientType(),
                    'name' => $case->getUser()->getName(),
                    'email' => $case->getUser()->getEmail()
                ), 'recipient::'));
                break;
            case MailTemplate::RECIPIENT_CLIENT:
                $message->addTo($case->getClient()->getEmail());
                $message->replace(Collection::prefixArrayKeys(array(
                    'type' => $mailTemplate->getRecipientType(),
                    'name' => $case->getClient()->getName(),
                    'email' => $case->getClient()->getEmail()
                ), 'recipient::'));
                break;
            case MailTemplate::RECIPIENT_PATHOLOGIST:
                $message->addTo($case->getPathologist()->getEmail());
                $message->replace(Collection::prefixArrayKeys(array(
                    'type' => $mailTemplate->getRecipientType(),
                    'name' => $case->getPathologist()->getName(),
                    'email' => $case->getPathologist()->getEmail()
                ), 'recipient::'));
                break;
            case MailTemplate::RECIPIENT_STUDENTS:
                // TODO: send each student an individual email (if required)
                foreach ($case->getStudentList() as $student) {
                    $message->addTo($student->getEmail());
                }
                $message->replace(Collection::prefixArrayKeys(array(
                    'type' => $mailTemplate->getRecipientType(),
                    'name' => 'Student',
                    'email' => ''
                ), 'recipient::'));
                break;
            case MailTemplate::RECIPIENT_OWNER:
                $message->addTo($case->getOwner()->getEmail());
                $message->replace(Collection::prefixArrayKeys(array(
                    'type' => $mailTemplate->getRecipientType(),
                    'name' => $case->getOwner()->getName(),
                    'email' => $case->getOwner()->getEmail()
                ), 'recipient::'));
                break;
        }

        $event->addMessage($message);

    }
}