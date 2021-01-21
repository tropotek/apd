<?php


namespace App\Db;


use Bs\Event\StatusEvent;
use Tk\Collection;
use Tk\Mail\CurlyMessage;
use Tk\Mail\Message;
use Bs\Db\Status;
use Tk\ObjectUtil;
use Uni\Db\User;
use Uni\Uri;

class PathCaseDecorator
{

    /**
     * @param PathCase $case
     * @param MailTemplate $mailTemplate
     * @param null|string $subject
     * @return CurlyMessage|null
     * @throws \Exception
     */
    public static function onCreateMessages(PathCase $case, MailTemplate $mailTemplate, $subject = null)
    {
        $status = $case->getCurrentStatus();
        $message = null;

        $message = CurlyMessage::create($mailTemplate->getTemplate());
        $message->set('_mailTemplate', $mailTemplate);
        if (!$subject) {
            $subject = '[#' . $case->getId() . '] ' . ObjectUtil::basename($case) . ' ' . ucfirst($status->getName()) . ': ' . $case->getPathologyId();
        }
        $message->setSubject($subject);
        $message->setFrom(Message::joinEmail($case->getInstitution()->getEmail(), $case->getInstitution()->getName()));

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

        switch($mailTemplate->getRecipientType()) {
            case MailTemplate::RECIPIENT_AUTHOR:
                if ($case->getUser()) {
                    $message->addTo($case->getUser()->getEmail());
                    $message->replace(Collection::prefixArrayKeys(array(
                        'type' => $mailTemplate->getRecipientType(),
                        'name' => $case->getUser()->getName(),
                        'email' => $case->getUser()->getEmail()
                    ), 'recipient::'));
                }
                break;
            case MailTemplate::RECIPIENT_CLIENT:
                if ($case->getClient()) {
                    $message->addTo($case->getClient()->getEmail());
                    $message->replace(Collection::prefixArrayKeys(array(
                        'type' => $mailTemplate->getRecipientType(),
                        'name' => $case->getClient()->getNameFirst(),
                        'email' => $case->getClient()->getEmail()
                    ), 'recipient::'));
                }
                break;
            case MailTemplate::RECIPIENT_PATHOLOGIST:
                if ($case->getPathologist()) {
                    $message->addTo($case->getPathologist()->getEmail());
                    $message->replace(Collection::prefixArrayKeys(array(
                        'type' => $mailTemplate->getRecipientType(),
                        'name' => $case->getPathologist()->getName(),
                        'email' => $case->getPathologist()->getEmail()
                    ), 'recipient::'));
                }
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
                if ($case->getOwner()) {
                    $message->addTo($case->getOwner()->getEmail());
                    $message->replace(Collection::prefixArrayKeys(array(
                        'type' => $mailTemplate->getRecipientType(),
                        'name' => $case->getOwner()->getNameFirst(),
                        'email' => $case->getOwner()->getEmail()
                    ), 'recipient::'));
                }
                break;
        }
        return $message;
    }
}