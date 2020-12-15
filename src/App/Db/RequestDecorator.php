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
        $case = $request->getPathCase();

        $message = CurlyMessage::create($mailTemplate->getTemplate());
        $message->set('_mailTemplate', $mailTemplate);
        $message->setSubject('[#' . $request->getId() . '] Pathology Request - ' . ucfirst($status->getName()) . ': ' . $request->getPathCase()->getPathologyId());
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
        $message->replace(Collection::prefixArrayKeys(\App\Db\RequestMap::create()
            ->unmapForm($request), 'request::'));
        if ($request->getPathCase() && $request->getPathCase()->getInstitution())
            $message->replace(Collection::prefixArrayKeys(\Uni\Db\InstitutionMap::create()
                ->unmapForm($request->getPathCase()->getInstitution()), 'institution::'));
        if ($request->getClient())
            $message->replace(Collection::prefixArrayKeys(\App\Db\ContactMap::create()
                ->unmapForm($request->getClient()), 'client::'));
        if ($request->getPathCase())
            $message->replace(Collection::prefixArrayKeys(\App\Db\PathCaseMap::create()
                ->unmapForm($request->getPathCase()), 'pathCase::'));

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
                $message->addTo($request->getClient()->getEmail());
                $message->replace(Collection::prefixArrayKeys(array(
                    'type' => $mailTemplate->getRecipientType(),
                    'name' => $request->getClient()->getNameFirst(),
                    'email' => $request->getClient()->getEmail()
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
                    'name' => $case->getOwner()->getNameFirst(),
                    'email' => $case->getOwner()->getEmail()
                ), 'recipient::'));
                break;
        }
        $event->addMessage($message);

    }
}