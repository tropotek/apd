<?php
namespace App\Db;

use App\Config;
use Tk\Collection;
use Tk\Date;
use Tk\Db\Map\Model;
use Tk\Mail\CurlyMessage;
use Tk\Mail\Message;
use Uni\Uri;


class RequestDecorator
{

    /**
     * @param Model|Request $request
     * @param MailTemplate $mailTemplate
     * @return array
     */
    public static function getRecipients($request, MailTemplate $mailTemplate)
    {
        $users = PathCaseDecorator::getRecipients($request->getPathCase(), $mailTemplate);
        switch($mailTemplate->getRecipientType()) {
            case MailTemplate::RECIPIENT_SERVICE_TEAM:
                $arr = $request->getService()->getUsers();
                foreach ($arr as $user) {
                    $users[] = [
                        'type' => $mailTemplate->getRecipientType(),
                        'name' => $user->getName(),
                        'email' => $user->getEmail()
                    ];
                }
                break;
        }
        return $users;
    }

    /**
     * @param Request $request
     * @param MailTemplate $mailTemplate
     * @param null $subject
     * @return CurlyMessage[]|CurlyMessage|array
     * @throws \Exception
     */
    public static function onCreateMessages(Request $request, MailTemplate $mailTemplate, $subject = null)
    {
        $status = $request->getCurrentStatus();
        $messageList = [];
        $config = Config::getInstance();

        $recipientList = self::getRecipients($request, $mailTemplate);
        foreach ($recipientList as $recipient) {
            $message = CurlyMessage::create($mailTemplate->getTemplate());
            $message->set('_mailTemplate', $mailTemplate);
            if (!$subject) {
                $subject = '[' . $request->getPathCase()->getPathologyId() . '] Pathology Request - ' . ucfirst($status->getName());
            }
            $message->setSubject($subject);

            $message->setFrom(Message::joinEmail($config->get('site.email'),
                $request->getPathCase()->getInstitution()->getName()));
            $message->setReplyTo(Message::joinEmail($request->getPathCase()->getInstitution()->getEmail(),
                $request->getPathCase()->getInstitution()->getName()));

            $message->addTo($recipient['email']);
            $message->replace(Collection::prefixArrayKeys($recipient, 'recipient::'));

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

            if ($request->getTest()) {
                $message->replace(Collection::prefixArrayKeys(\App\Db\TestMap::create()
                    ->unmapForm($request->getTest()), 'test::'));
                $message->set('test::block', true);
            }

            if ($request->getCassette())
                $message->replace(Collection::prefixArrayKeys(\App\Db\CassetteMap::create()
                    ->unmapForm($request->getCassette()), 'cassette::'));

            if ($request->getService())
                $message->replace(Collection::prefixArrayKeys(\App\Db\ServiceMap::create()
                    ->unmapForm($request->getService()), 'service::'));

            if ($request->getPathCase()) {
                $message->replace(Collection::prefixArrayKeys(\App\Db\PathCaseMap::create()
                    ->unmapForm($request->getPathCase()), 'pathCase::'));
                $message->set('pathCase::disposeOn', $request->getPathCase()->getDisposeOn(Date::FORMAT_MED_DATE) ?? '');
            }
            $cnt = 1;
            if ($request->getStatusMessage()) {
                $cnt = (int)$request->getStatusMessage();
            }
            $message->set('request::requestCount' , $cnt);

            $messageList[] = $message;
        }
        return $messageList;
    }
}