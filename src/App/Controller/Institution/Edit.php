<?php
namespace App\Controller\Institution;


use Tk\Ml\Db\MailLog;
use Uni\Db\Permission;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Edit extends \Uni\Controller\Institution\Edit
{


    /**
     * @throws \Exception
     */
    public function initActionPanel()
    {
        parent::initActionPanel();
        if ($this->getAuthUser()->isClient() || $this->getAuthUser()->isStaff()) {
            $this->getActionPanel()->remove('Courses');
        }
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Mail Log',
            \Uni\Uri::createHomeUrl(MailLog::createMailLogUrl('/manager.html', $this->getConfig()->getInstitution())), 'fa fa-envelope'));

        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Storage Locations',
            \Uni\Uri::createHomeUrl('/storageManager.html'), 'fa fa-archive'));
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Available Services',
            \Uni\Uri::createHomeUrl('/serviceManager.html'), 'fa fa-tags'));
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Clients',
            \Uni\Uri::createHomeUrl('/clientManager.html'), 'fa fa-user-md'));

    }

}