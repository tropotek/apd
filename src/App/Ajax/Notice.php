<?php
namespace App\Ajax;

use App\Config;
use App\Db\NoticeMap;
use Uni\Db\UserMap;
use Exception;
use Tk\ConfigTrait;
use Tk\Db\Tool;
use Tk\Request;
use Tk\Response;
use Tk\ResponseJson;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Notice
{
    use ConfigTrait;

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function doDefault(Request $request, $action)
    {
        $response = ResponseJson::createJson(array('error' => 'Error'), 500);
        if (method_exists($this, $action)) {
            $response = $this->$action($request);
        }
        return $response;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function doGetNoticeList(Request $request)
    {
        $status = 200;  // change this on error
        // h = userHash
        $out = $this->getRecipientList($request->get('h'));
        return ResponseJson::createJson($out, $status);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function doMarkRead(Request $request)
    {
        $status = 200;  // change this on error

        // h = userHash
        // d = true/false
        // nid = noticeId

        /** @var \App\Db\Notice $notice */
        $notice = NoticeMap::create()->find($request->get('nid'));
        /** @var \Uni\Db\User $user */
        $user = UserMap::create()->findByHash($request->get('h'), Config::getInstance()->getInstitutionId());
        $recipient = $notice->getNoticeRecipient($user);
        if (!$recipient) return ResponseJson::createJson(array('error' => 'Invalid recipient Record!'), 500);

        $unread = true;
        if ($request->has('d')) {
            $unread = false;
        }

        if ($notice && $user) {
            if ($unread) {
                if (!$recipient->getViewed())
                    $recipient->setViewed(\Tk\Date::create());
                $recipient->setRead(\Tk\Date::create());
                //$notice->markRead($user);
            } else {
                // set the message to unread
                $recipient->setRead(null);
            }
            $recipient->save();
        }

        $out = $this->getRecipientList($request->get('h'));

        return ResponseJson::createJson($out, $status);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function doMarkViewed(Request $request)
    {
        $status = 200;  // change this on error

        // h = userHash
        // d = true/false
        // nid = noticeId

        /** @var \App\Db\Notice $notice */
        $notice = NoticeMap::create()->find($request->get('nid'));
        /** @var \Uni\Db\User $user */
        $user = UserMap::create()->findByHash($request->get('h'), Config::getInstance()->getInstitutionId());
        $recipient = $notice->getNoticeRecipient($user);
        if (!$recipient) return ResponseJson::createJson(array('error' => 'Invalid recipient Record!'), 500);

        $unread = true;
        if ($request->has('d')) {
            $unread = false;
        }

        if ($notice && $user) {
            if ($unread) {
                if (!$recipient->getViewed())
                    $recipient->setViewed(\Tk\Date::create());
                $recipient->setViewed(\Tk\Date::create());
            } else {
                // set the message to unread
                $recipient->setViewed(null);
            }
            $recipient->save();
        }

        $out = $this->getRecipientList($request->get('h'));

        return ResponseJson::createJson($out, $status);
    }


    /**
     * @param $userHash
     * @return array
     * @throws Exception
     */
    protected function getRecipientList($userHash)
    {
        $out = array();
        /** @var \Uni\Db\User $user */
        $user = UserMap::create()->findByHash($userHash, $this->getConfig()->getInstitutionId());
        if ($user) {
            $filter = array(
                'recipientId' => $user->getId()
            );
            $list = NoticeMap::create()->findFiltered($filter, Tool::create('created DESC', 15));
            if (count($list)) {
                $unread = 0;
                $total = $list->count();
                $out['list'] = array();
                foreach ($list as $notice) {
                    $recipient = $notice->getNoticeRecipient($user);
                    if (!$recipient) continue;
                    $notice->icon = $notice->getIconCss();
                    $notice->time =  str_replace(' ago', '', \Tk\Date::toRelativeString($recipient->getCreated()));


                    $out['list'][] = $notice;
                    if (!$recipient->isViewed()) {
                        $unread++;
                    }
                }
                $out['total'] = $total;
                $out['unread'] = $unread;
            }
        }
        return $out;
    }

}