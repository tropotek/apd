<?php
namespace App\Db;

use App\Config;
use Tk\Collection;
use Tk\Db\Map\Model;
use Tk\Mail\CurlyMessage;
use Tk\Mail\Message;
use Tk\ObjectUtil;
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
                    foreach ($case->getClient()->getEmailCcList() as $e) {
                        $users[] = array(
                            'type' => $mailTemplate->getRecipientType(),
                            'name' => $case->getClient()->getNameFirst(),
                            'email' => $e
                        );
                    }
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
                    foreach ($student->getEmailCcList() as $e) {
                        $users[] = array(
                            'type' => $mailTemplate->getRecipientType(),
                            'name' => $student->getName(),
                            'email' => $e
                        );
                    }
                }
                break;
            case MailTemplate::RECIPIENT_OWNER:
                if (PathCase::useOwnerObject()) {
                    if ($case->getOwner()) {
                        $users[] = [
                            'type' => $mailTemplate->getRecipientType(),
                            'name' => $case->getOwner()->getNameFirst(),
                            'email' => $case->getOwner()->getEmail()
                        ];
                        foreach ($case->getOwner()->getEmailCcList() as $e) {
                            $users[] = array(
                                'type' => $mailTemplate->getRecipientType(),
                                'name' => $case->getOwner()->getName(),
                                'email' => $e
                            );
                        }
                    }
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
        $config = Config::getInstance();

        $recipientList = self::getRecipients($case, $mailTemplate);
        foreach ($recipientList as $recipient) {
            $message = CurlyMessage::create($mailTemplate->getTemplate());
            $message->set('_mailTemplate', $mailTemplate);
            if (!$subject) {
                $subject = '[#' . $case->getId() . '] ' . ObjectUtil::basename($case) . ' ' . ucfirst($status->getName()) . ': ' . $case->getPathologyId();
            }
            $message->setSubject($subject);

            $message->setFrom(Message::joinEmail($config->get('site.email'),
                $case->getInstitution()->getName()));
            $message->setReplyTo(Message::joinEmail($case->getInstitution()->getEmail(), $case->getInstitution()->getName()));

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