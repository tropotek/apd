<?php
namespace App\Controller\Institution;

use Tk\Ml\Db\MailLog;
use Tk\Form\Field;

class Edit extends \Uni\Controller\Institution\Edit
{
    const INSTITUTION_FAX = 'inst.fax';
    const INSTITUTION_REPORT_TEMPLATE = 'inst.report.mail.tpl';
    const INSTITUTION_AUTOCOMPLETE_REPORT_STATUS = 'inst.pathCase.autocomplete.reportStatus';

    public function initForm(\Tk\Request $request)
    {

        // Needed for the PDF report
        $this->getForm()->removeField('description');
        $this->getForm()->removeField('feature');

        $this->getForm()->appendField(new Field\Input(self::INSTITUTION_FAX), 'phone')
            ->setLabel('Fax')
            ->setTabGroup('Details');

        // TODO: this should be moved to the mail templates (see reminders in the Cron)
        $this->getForm()->appendField(new Field\Textarea(self::INSTITUTION_REPORT_TEMPLATE))->setTabGroup('Details')
            ->addCss('mce-min')
            ->setLabel('Report Email Template')
            ->setAttr('data-elfinder-path', $this->getInstitution()->getDataPath().'/media');

        $this->getForm()->appendField(new Field\Checkbox(self::INSTITUTION_AUTOCOMPLETE_REPORT_STATUS), 'address')
            ->setTabGroup('Settings')
            ->setLabel('Auto-complete Report Status')
            ->setCheckboxLabel('Automatically set the pathCase.reportStatus to completed once the case status is completed');

    }

    /**
     * @throws \Exception
     */
    public function initActionPanel()
    {
        parent::initActionPanel();
        if ($this->getAuthUser()->isClient() || $this->getAuthUser()->isStaff()) {
            $this->getActionPanel()->remove('Courses');
        }

        if (\Tk\Plugin\Factory::getInstance($this->getConfig()->getDb())->isActive('mailog')) {
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Mail Log',
                \Uni\Uri::createHomeUrl(MailLog::createMailLogUrl('/manager.html', $this->getConfig()->getInstitution())), 'fa fa-envelope-o'));
        }
        if ($this->getAuthUser()->isStaff()) {
            $this->getActionPanel()->remove('Plugins');

            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Mail Templates',
                \Uni\Uri::createHomeUrl('/mailTemplateManager.html'), 'fa fa-envelope'));
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Storage Locations',
                \Uni\Uri::createHomeUrl('/storageManager.html'), 'fa fa-archive'));
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Services',
                \Uni\Uri::createHomeUrl('/serviceManager.html'), 'fa fa-tags'));
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Animal Types',
                \Uni\Uri::createHomeUrl('/animalTypeManager.html'), 'fa fa-paw'));
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Test Types',
                \Uni\Uri::createHomeUrl('/testManager.html'), 'fa fa-flask'));
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Products',
                \Uni\Uri::createHomeUrl('/productManager.html'), 'fa fa-cube'));
//            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Students',
//                \Uni\Uri::createHomeUrl('/studentManager.html'), 'fa fa-user-o'));
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Clients',
                \Uni\Uri::createHomeUrl('/companyManager.html'), 'fa fa-building-o'));

        }

    }

}