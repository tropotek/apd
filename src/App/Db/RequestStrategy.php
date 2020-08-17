<?php


namespace App\Db;


use Tk\Mail\CurlyMessage;
use Tk\Mail\Message;
use Uni\Db\Status;

class RequestStrategy
{
    public static function onStatusChange(Status $status)
    {
        /** @var Request $model */
        $model = $status->getModel();
        $prevStatusName = $status->getPreviousName();

        switch ($status->getName()) {
            case Request::STATUS_PENDING:
                if (!$prevStatusName)
                    return true;
                break;
            case Request::STATUS_PROCESSING:
                if (!$prevStatusName || Request::STATUS_PENDING == $prevStatusName)
                    return true;
                break;
            case Request::STATUS_COMPLETED:
                if (!$prevStatusName || Request::STATUS_PENDING == $prevStatusName || Request::STATUS_PROCESSING == $prevStatusName)
                    return true;
                break;
            case Request::STATUS_CANCELLED:
                    return true;
        }
        return false;
    }


    public static function onFormatMessage(Status $status, CurlyMessage $message)
    {
        /** @var PathCase $case */
        $case = $status->getModel();

        $message->setSubject('[#' . $case->getId() . '] ' . ucfirst($status->getName()) . ': ' . $case->getPathologyId());
        $message->setFrom(Message::joinEmail($case->getInstitution()->getEmail(), $case->getInstitution()->getName()));

        // Setup default message vars
//        StatusMessage::setStudent($message, $status->findLastStudent());
//        StatusMessage::setStaff($message, $status->findLastStaff());
//        StatusMessage::setCompany($message, $case);

        // TODO: recipients need to be competed when we know whats going on

        /** @var MailTemplate $mailTemplate */
        $mailTemplate = $message->get('_mailTemplate');
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