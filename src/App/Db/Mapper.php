<?php

namespace App\Db;

use App\Config;
use Exception;
use Tk\Db\Pdo;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
abstract class Mapper extends \Bs\Db\Mapper
{

    /**
     * @param Pdo|null $db
     * @throws Exception
     */
    public function __construct($db = null)
    {
        parent::__construct($db);
        $this->setMarkDeleted('del');           // Default to have a del field (This will only mark the record deleted)
        $this->dispatcher = $this->getConfig()->getEventDispatcher();
    }

}