<?php
namespace App\Db;


class RequestNoticeDecorator extends NoticeDecoratorInterface
{

    /**
     * @param Notice $notice
     */
    public function onCreateNotice($notice)
    {
//        vd('************************************************');
//        vd('New Request created and Notify object initiated');
        /** @var Request $request */
        $request = $notice->getModel();
        $case = $request->getPathCase();

        if ($case->getPathologist()) {
            $notice->setUserId($case->getUserId());
            $subject = 'New Request Created: ' . $case->getPatientNumber();
            if ($notice->getParam('requestList')) {
                $subject = count($notice->getParam('requestList')) . ' New Request`s Created: ' . $case->getPathologyId();
                $notice->removeParam('requestList'); // no longer needed
            }

            if (!$notice->getSubject())
                $notice->setSubject($subject);
            $notice->setType('Request::create');
            //$notice->setBody('');
            $notice->addRecipient($case->getPathologist());
            $notice->save();
        }



    }


}