<?php

namespace App\Db\Traits;

use App\Db\EditLog;
use App\Db\EditLogMap;
use Bs\Config;
use Exception;
use Tk\Db\Map\ArrayObject;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2019 Michael Mifsud
 */
trait EditLogTrait
{
    /**
     * Create a log from the state of this object.
     * Must call $log->save(); after creating this log.
     *
     * @param string $message
     * @return EditLog
     */
    public function createEditLog(string $message = ''): EditLog
    {
        $log = new EditLog();
        $log->setUserId(Config::getInstance()->getAuthUser()->getId());
        $log->setModel($this);
        $log->setState($this);
        $log->setMessage($message);
        return $log;
    }


    /**
     * Get the all edit logs for this object
     *
     * @return EditLog[]|ArrayObject|array
     */
    public function getEditLog()
    {
        try {
            return EditLogMap::create()->findFiltered(['model' => $this]);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * @param array $errors
     * @return array
     */
    public function validateEditLogId($errors = [])
    {
        return $errors;
    }

}