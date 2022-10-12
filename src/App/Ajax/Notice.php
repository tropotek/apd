<?php
namespace App\Ajax;

use App\Config;
use App\Db\NoticeMap;
use Uni\Db\User;
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
        // h = userHash
        $out = $this->getRecipientList($request->get('h'));
        return ResponseJson::createJson($out);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function doMarkRead(Request $request)
    {        // h = userHash
        // d = true/false  //mark read/unread
        // nid = noticeId

        /** @var \Uni\Db\User $user */
        $user = UserMap::create()->findByHash($request->get('h'), Config::getInstance()->getInstitutionId());
        if (!$user)  return ResponseJson::createJson(array('error' => 'Invalid Request'), 500);
        $params = [];
        if ($request->has('nid'))
            $params['id'] = (int)$request->get('nid');
        $list = $this->getCurrentNoticeList($user, $params);
        foreach ($list as $notice) {
            $recipient = $notice->getNoticeRecipient($user);
            if (!$recipient) continue;
            $b = ($request->get('d') == true);
            if ($b) {
                if (!$recipient->getRead()) {
                    $recipient->setRead(\Tk\Date::create());
                    $recipient->setViewed(\Tk\Date::create());
                    $recipient->setAlert(true);
                }
            } else {
                $recipient->setRead(null);
                $recipient->setViewed(null);
                $recipient->setAlert(false);
            }
            $recipient->save();
        }

        $out = $this->getRecipientList($request->get('h'));
        return ResponseJson::createJson($out);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function doMarkViewed(Request $request)
    {
        // use for testing
        // UPDATE notice_recipient t SET t.read = null WHERE 1;

        // h = userHash
        // d = true/false  //mark read/unread
        // nid = noticeId
        /** @var \Uni\Db\User $user */
        $user = UserMap::create()->findByHash($request->get('h'), Config::getInstance()->getInstitutionId());
        if (!$user)  return ResponseJson::createJson(array('error' => 'Invalid Request'), 500);
        $params = [];
        if ($request->has('nid'))
            $params['id'] = (int)$request->get('nid');
        $list = $this->getCurrentNoticeList($user, $params);
        foreach ($list as $notice) {
            $recipient = $notice->getNoticeRecipient($user);
            if (!$recipient) continue;
            $b = ($request->get('d') == true);
            if ($b) {
                if (!$recipient->getViewed())
                    $recipient->setViewed(\Tk\Date::create());
                if (!$recipient->isAlert())
                    $recipient->setAlert(true);
            } else {
                $recipient->setViewed(null);
            }
            $recipient->save();
        }

        $out = $this->getRecipientList($request->get('h'));
        return ResponseJson::createJson($out);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function doMarkAlert(Request $request)
    {
        // use for testing
        // UPDATE notice_recipient t SET t.read = null WHERE 1;

        // h = userHash
        // d = true/false  //mark read/unread
        // nid = noticeId
        /** @var \Uni\Db\User $user */
        $user = UserMap::create()->findByHash($request->get('h'), Config::getInstance()->getInstitutionId());
        if (!$user)  return ResponseJson::createJson(array('error' => 'Invalid Request'), 500);
        $params = [];
        if ($request->has('nid'))
            $params['id'] = (int)$request->get('nid');
        $list = $this->getCurrentNoticeList($user, $params);
        foreach ($list as $notice) {
            $recipient = $notice->getNoticeRecipient($user);
            if (!$recipient) continue;
            $b = ($request->get('d') == true);
            if ($b) {
                if (!$recipient->isAlert())
                    $recipient->setAlert(true);
            } else {
                $recipient->setAlert(false);
            }
            $recipient->save();
        }

        $out = $this->getRecipientList($request->get('h'));
        return ResponseJson::createJson($out);
    }


    /**
     * @param $userHash
     * @return array
     * @throws Exception
     */
    protected function getRecipientList($userHash)
    {
        // use for testing
        // UPDATE notice_recipient t SET t.viewed = null WHERE 1;

        $out = array();
        /** @var \Uni\Db\User $user */
        $user = UserMap::create()->findByHash($userHash, $this->getConfig()->getInstitutionId());
        if ($user) {
            $list = $this->getCurrentNoticeList($user);
            if (count($list)) {
                //$unRead = 0;
                $unViewed = 0;
                $unAlert = 0;
                $total = $list->count();
                $out['list'] = array();
                foreach ($list as $notice) {
                    $recipient = $notice->getNoticeRecipient($user);
                    if (!$recipient) continue;
                    $notice->icon = $notice->getIconCss();
                    $notice->time = str_replace(' ago', '', \Tk\Date::toRelativeString($recipient->getCreated()));
                    $notice->isNew = (\Tk\Date::create()->sub(new \DateInterval('PT2H')) < $notice->getCreated());
                    //$notice->isRead = $recipient->isRead();
                    $notice->isViewed = $recipient->isViewed();
                    $notice->isAlert = $recipient->isAlert();
                    $out['list'][] = $notice;

//                    if (!$recipient->isRead()) {
//                        $unRead++;
//                    }
                    if (!$recipient->isViewed()) {
                        $unViewed++;
                    }
                    if (!$recipient->isAlert()) {
                        $unAlert++;
                    }
                }
                $out['total'] = $total;
                //$out['unRead'] = $unRead;
                $out['unViewed'] = $unViewed;
                $out['unAlert'] = $unAlert;
            }
        }
        return $out;
    }

    /**
     * @param User $user
     * @param array $params
     * @return \App\Db\Notice[]|\Tk\Db\Map\ArrayObject
     * @throws Exception
     */
    protected function getCurrentNoticeList(User $user, $params = [])
    {
        $filter = array(
            'recipientId' => $user->getId(),
            'read' => false,
            'created' => \Tk\Date::create()->sub(new \DateInterval('P2D'))
            //'created' => \Tk\Date::create()->sub(new \DateInterval('PT18H'))
        );
        $filter = array_merge($filter, $params);
        return NoticeMap::create()->findFiltered($filter, Tool::create('created DESC', 15));
    }


}