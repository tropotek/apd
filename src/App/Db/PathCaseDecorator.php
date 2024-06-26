<?php


namespace App\Db;


use Bs\Event\StatusEvent;
use Tk\Collection;
use Tk\Db\Map\Model;
use Tk\Mail\CurlyMessage;
use Tk\Mail\Message;
use Bs\Db\Status;
use Tk\ObjectUtil;
use Uni\Db\User;
use Uni\Uri;

class PathCaseDecorator
{


    /**
     * @param Model|PathCase $case
     * @param MailTemplate $mailTemplate
     * @return array
     */
    public static function getRecipients($case, MailTemplate $mailTemplate)
    {
        $users = array();
        switch($mailTemplate->getRecipientType()) {
            case MailTemplate::RECIPIENT_AUTHOR:
                if ($case->getUser()) {
                    $users[] = array(
                        'type' => $mailTemplate->getRecipientType(),
                        'name' => $case->getUser()->getName(),
                        'email' => $case->getUser()->getEmail()
                    );
                }
                break;
            case MailTemplate::RECIPIENT_CLIENT:
                if ($case->getClient()) {
                    $users[] = array(
                        'type' => $mailTemplate->getRecipientType(),
                        'name' => $case->getClient()->getNameFirst(),
                        'email' => $case->getClient()->getEmail()
                    );
                }
                break;
            case MailTemplate::RECIPIENT_PATHOLOGIST:
                if ($case->getPathologist()) {
                    $users[] = array(
                        'type' => $mailTemplate->getRecipientType(),
                        'name' => $case->getPathologist()->getName(),
                        'email' => $case->getPathologist()->getEmail()
                    );
                }
                break;
            case MailTemplate::RECIPIENT_STUDENTS:
                foreach ($case->getStudentList() as $student) {
                    $users[] = [
                        'type' => $mailTemplate->getRecipientType(),
                        'name' => $student->getName(),
                        'email' => $student->getEmail()
                    ];
                }
                break;
            case MailTemplate::RECIPIENT_OWNER:
                if ($case->getOwner()) {
                    $users[] = [
                        'type' => $mailTemplate->getRecipientType(),
                        'name' => $case->getOwner()->getNameFirst(),
                        'email' => $case->getOwner()->getEmail()
                    ];
                }
                break;
        }
        return $users;
    }

    /**
     * @param PathCase $case
     * @param MailTemplate $mailTemplate
     * @param null|string $subject
     * @return CurlyMessage[]|CurlyMessage|array
     * @throws \Exception
     */
    public static function onCreateMessages(PathCase $case, MailTemplate $mailTemplate, $subject = null)
    {
        $status = $case->getCurrentStatus();
        $messageList = [];

        $recipientList = self::getRecipients($case, $mailTemplate);
        foreach ($recipientList as $recipient) {
            $message = CurlyMessage::create($mailTemplate->getTemplate());
            $message->set('_mailTemplate', $mailTemplate);
            if (!$subject) {
                $subject = '[#' . $case->getId() . '] ' . ObjectUtil::basename($case) . ' ' . ucfirst($status->getName()) . ': ' . $case->getPathologyId();
            }
            $message->setSubject($subject);
            $message->setFrom(Message::joinEmail($case->getInstitution()->getEmail(), $case->getInstitution()->getName()));

            $message->addTo($recipient['email']);
            $message->replace(Collection::prefixArrayKeys($recipient, 'recipient::'));

            $message->replace(Collection::prefixArrayKeys([
                'id' => $status->getId(),
                'name' => $status->getName(),
                'message' => nl2br($status->getMessage()),
                'event' => $status->getEvent()
            ], 'status::'));
            $message->set('pathCase::url', Uri::create('/staff/pathCaseEdit.html')
                ->setScheme(Uri::SCHEME_HTTP_SSL)
                ->set('pathCaseId', $case->getId())->toString());
            $message->replace(Collection::prefixArrayKeys(\App\Db\PathCaseMap::create()->unmapForm($case), 'pathCase::'));
            if ($case->getInstitution())
                $message->replace(Collection::prefixArrayKeys(\Uni\Db\InstitutionMap::create()->unmapForm($case->getInstitution()), 'institution::'));
            if ($case->getClient())
                $message->replace(Collection::prefixArrayKeys(\App\Db\ContactMap::create()->unmapForm($case->getClient()), 'client::'));
            $messageList[] = $message;
        }
        return $messageList;
    }
}