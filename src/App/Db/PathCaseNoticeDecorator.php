<?php
namespace App\Db;


class PathCaseNoticeDecorator extends NoticeDecoratorInterface
{

    /**
     * @param Notice $notice
     */
    public function onCreateNotice($notice)
    {
        /** @var PathCase $case */
        $case = $notice->getModel();
        if ($case->getPathologist()) {
            $notice->setUserId($case->getUserId());
            if (!$notice->getSubject())
                $notice->setSubject('New ' . $case->getType() . ' created.');
            $notice->setType('PathCase::create');
            $notice->addRecipient($case->getPathologist());
            $notice->save();
        }
    }

}