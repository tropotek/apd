<?php
namespace App\Controller\Institution;

use Tk\Ml\Db\MailLog;
use Tk\Form\Field;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Edit extends \Uni\Controller\Institution\Edit
{
    const INSTITUTION_FAX = 'inst.fax';
    const INSTITUTION_REPORT_TEMPLATE = 'inst.report.mail.tpl';

    public function initForm(\Tk\Request $request)
    {
        // Nedded for the PDF report
        $this->getForm()->removeField('description');
        $this->getForm()->removeField('feature');

        $this->getForm()->appendField(new Field\Input(self::INSTITUTION_FAX), 'phone')
            ->setLabel('Fax')->setTabGroup('Details');
        $this->getForm()->appendField(new Field\Textarea(self::INSTITUTION_REPORT_TEMPLATE))->setTabGroup('Details')
            ->addCss('mce-min')->setLabel('Report Email Template')->setAttr('data-elfinder-path', $this->getInstitution()->getDataPath().'/media');
    }

    /**
     * @throws \Exception
     */
    public function initActionPanel()
    {
        parent::initActionPanel();
        if ($this->getAuthUser()->isClient() || $this->getAuthUser()->isStaff()) {
            $this->getActionPanel()->remove('Courses');
            $this->getActionPanel()->remove('Plugins');
        }
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Mail Log',
            \Uni\Uri::createHomeUrl(MailLog::createMailLogUrl('/manager.html', $this->getConfig()->getInstitution())), 'fa fa-envelope-o'));

        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Mail Templates',
            \Uni\Uri::createHomeUrl('/mailTemplateManager.html'), 'fa fa-envelope'));

        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Storage Locations',
            \Uni\Uri::createHomeUrl('/storageManager.html'), 'fa fa-archive'));
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Available Services',
            \Uni\Uri::createHomeUrl('/serviceManager.html'), 'fa fa-tags'));
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Contacts',
            \Uni\Uri::createHomeUrl('/contactManager.html'), 'fa fa-user-o'));

    }

}