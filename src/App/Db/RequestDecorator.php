<?php


namespace App\Db;


use Bs\Event\StatusEvent;
use Tk\Collection;
use Tk\Mail\CurlyMessage;
use Tk\Mail\Message;
use Bs\Db\Status;
use Uni\Db\User;
use Uni\Uri;

class RequestDecorator
{

    /**
     * @param Request $request
     * @param MailTemplate $mailTemplate
     * @param null $subject
     * @return CurlyMessage|null
     * @throws \Exception
     */
    public static function onCreateMessages(Request $request, MailTemplate $mailTemplate, $subject = null)
    {
        $status = $request->getCurrentStatus();
        $case = $request->getPathCase();

        $message = CurlyMessage::create($mailTemplate->getTemplate());
        $message->set('_mailTemplate', $mailTemplate);
        if (!$subject) {
            $subject = '[#' . $request->getId() . '] Pathology Request - ' . ucfirst($status->getName()) . ': ' . $request->getPathCase()->getPathologyId();
        }
        $message->setSubject($subject);
        $message->setFrom(Message::joinEmail($request->getPathCase()->getInstitution()->getEmail(),
            $request->getPathCase()->getInstitution()->getName()));

        $message->replace(Collection::prefixArrayKeys([
            'id' => $status->getId(),
            'name' => $status->getName(),
            'message' => nl2br($status->getMessage()),
            'event' => $status->getEvent(),
//                'fkey'  => $status->getFkey(),
//                'fid' => $status->getFId()
        ], 'status::'));

        if ($request->getPathCase())
            $message->set('pathCase::url', Uri::create('/staff/pathCaseEdit.html')
                ->setScheme(Uri::SCHEME_HTTP_SSL)->set('pathCaseId', $request->getPathCase()->getId())->toString());
        $message->set('request::url', Uri::create('/staff/requestEdit.html')
            ->setScheme(Uri::SCHEME_HTTP_SSL)->set('requestId', $request->getId())->toString());
        $message->replace(Collection::prefixArrayKeys(\App\Db\RequestMap::create()
            ->unmapForm($request), 'request::'));
        if ($request->getPathCase() && $request->getPathCase()->getInstitution())
            $message->replace(Collection::prefixArrayKeys(\Uni\Db\InstitutionMap::create()
                ->unmapForm($request->getPathCase()->getInstitution()), 'institution::'));

        if ($request->getTest())
            $message->replace(Collection::prefixArrayKeys(\App\Db\ContactMap::create()
                ->unmapForm($request->getTest()), 'test::'));

        if ($request->getPathCase())
            $message->replace(Collection::prefixArrayKeys(\App\Db\PathCaseMap::create()
                ->unmapForm($request->getPathCase()), 'pathCase::'));

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