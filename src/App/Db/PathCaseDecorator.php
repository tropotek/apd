<?php


namespace App\Db;


use Tk\Collection;
use Tk\Mail\CurlyMessage;
use Tk\Mail\Message;
use Bs\Db\Status;

class PathCaseDecorator
{


    /**
     * @param Status $status
     * @param CurlyMessage $message
     * @throws \Exception
     */
    public static function onFormatMessage(Status $status, CurlyMessage $message)
    {
        /** @var PathCase $case */
        $case = $status->getModel();
        /** @var MailTemplate $mailTemplate */
        $mailTemplate = $message->get('_mailTemplate');

        $message->setSubject('[#' . $case->getId() . '] ' . ucfirst($status->getName()) . ': ' . $case->getPathologyId());
        $message->setFrom(Message::joinEmail($case->getInstitution()->getEmail(), $case->getInstitution()->getName()));

        // Setup default message dynamic vars
        $message->replace(Collection::prefixArrayKeys(\Uni\Db\InstitutionMap::create()->unmapForm($case->getInstitution()), 'institution::'));
        $message->replace(Collection::prefixArrayKeys(\App\Db\ClientMap::create()->unmapForm($case->getClient()), 'client::'));
        $message->replace(Collection::prefixArrayKeys(\App\Db\PathCaseMap::create()->unmapForm($case), 'pathCase::'));
        $message->replace(Collection::prefixArrayKeys(\Bs\Db\StatusMap::create()->unmapForm($case->getStatusObject()), 'status::'));

        // Recipient
        if ($mailTemplate->getRecipientType() == 'client') {
            $message->replace(Collection::prefixArrayKeys(\App\Db\ClientMap::create()->unmapForm($case->getClient()), 'recipient::'));
        } else if ($mailTemplate->getRecipientType() == 'staff') {
            $staff = '';// ??????????????????
            $message->replace(Collection::prefixArrayKeys(\Bs\Db\StatusMap::create()->unmapForm($case->getStatusObject()), 'recipient::'));
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