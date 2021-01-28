<?php

namespace App\Db;


/**
 *
 * @author Tropotek <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2017 Tropotek
 */
abstract class NoticeDecoratorInterface
{

    /**
     * Here you can fill in the subject, add recipients and message body
     *   as needed
     *
     * @param Notice $notice
     */
    abstract public function onCreateNotice($notice);


}