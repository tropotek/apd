<?php
namespace App\Controller\MailTemplate;

use Bs\Controller\AdminManagerIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('mail-template-manager', Route::create('/staff/mailTemplateManager.html', 'App\Controller\MailTemplate\Manager::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-08-17
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Manager extends AdminManagerIface
{

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Mail Template Manager');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setTable(\App\Table\MailTemplate::create());
        $this->getTable()->setEditUrl(\Bs\Uri::createHomeUrl('/mailTemplateEdit.html'));
        $this->getTable()->init();

        $filter = [];
        $this->getTable()->setList($this->getTable()->findList($filter));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New Mail Template',
            $this->getTable()->getEditUrl(), 'fa fa-book fa-add-action'));
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->initActionPanel();
        $template = parent::show();

        $template->appendTemplate('panel', $this->getTable()->show());

        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-panel" data-panel-title="Mail Templates" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}