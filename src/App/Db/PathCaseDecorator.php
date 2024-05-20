<?php
namespace App\Db;

use App\Config;
use Tk\Collection;
use Tk\Date;
use Tk\Db\Map\Model;
use Tk\Mail\CurlyMessage;
use Tk\Mail\Message;
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
        $users = [];
        switch($mailTemplate->getRecipientType()) {
            case MailTemplate::RECIPIENT_AUTHOR:
                if ($case->getUser() && !$case->getUser()->hasPermission(Permission::IS_EXTERNAL)) {
                    $users[] = array(
                        'type' => $mailTemplate->getRecipientType(),
                        'name' => $case->getUser()->getName(),
                        'email' => $case->getUser()->getEmail()
                    );
                }
                break;
            case MailTemplate::RECIPIENT_CLIENT:
                if ($case->getCompany()) {
                    if ($case->getCompany()->getEmail()) {
                        $users[] = array(
                            'type'  => $mailTemplate->getRecipientType(),
                            'name'  => $case->getCompany()->getName(),
                            'email' => $case->getCompany()->getEmail()
                        );
                    }
                    foreach ($case->getContactList() as $contact) {
                        if ($contact->getEmail()) {
                            $users[] = array(
                                'type'  => $mailTemplate->getRecipientType(),
                                'name'  => $contact->getName(),
                                'email' => $contact->getEmail()
                            );
                        }
                    }
                }
                break;
            case MailTemplate::RECIPIENT_PATHOLOGIST:
                if ($case->getPathologist() && !$case->getPathologist()->hasPermission(Permission::IS_EXTERNAL)) {
                    $users[] = array(
                        'type' => $mailTemplate->getRecipientType(),
                        'name' => $case->getPathologist()->getName(),
                        'email' => $case->getPathologist()->getEmail()
                    );
                }
                break;
//            case MailTemplate::RECIPIENT_STUDENTS:
//                foreach ($case->getStudentList() as $student) {
//                    $users[] = [
//                        'type' => $mailTemplate->getRecipientType(),
//                        'name' => $student->getName(),
//                        'email' => $student->getEmail()
//                    ];
//                    foreach ($student->getEmailCcList() as $e) {
//                        $users[] = array(
//                            'type' => $mailTemplate->getRecipientType(),
//                            'name' => $student->getName(),
//                            'email' => $e
//                        );
//                    }
//                }
//                break;
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
                $subject = '[' . $case->getPathologyId() . '] ' . ObjectUtil::basename($case) . ' ' . ucfirst($status->getName());
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
            $message->set('pathCase::disposeOn', $case->getDisposeOn(Date::FORMAT_MED_DATE) ?? '');
            if ($case->getInstitution()) {
                $message->replace(Collection::prefixArrayKeys(\Uni\Db\InstitutionMap::create()->unmapForm($case->getInstitution()), 'institution::'));
            }

            if ($case->getCompany()) {
                $message->replace(Collection::prefixArrayKeys(\App\Db\CompanyMap::create()->unmapForm($case->getCompany()), 'client::'));
                $message->set('client::url', Uri::create('/staff/companyEdit.html')
                    ->setScheme(\Tk\Uri::SCHEME_HTTP_SSL)->set('companyId', $case->getCompanyId())->toString());
            }

            /** @var User $pathologist */
            $pathologist = $config->getUserMapper()->find($case->getPathologistId());
            if ($pathologist) {
                $message->set('pathCase::pathologist', $pathologist->getName());
            }

            $user = $config->getUserMapper()->find($case->getUserId());
            vd($user);
            if ($user) {
                $message->set('pathCase::author', $user->getName());
            }

            $messageList[] = $message;
        }
        return $messageList;
    }
}