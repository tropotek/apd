<?php
namespace App\Controller\Company;

use Bs\Controller\AdminManagerIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('company-manager', Route::create('/staff/companyManager.html', 'App\Controller\Company\Manager::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2023-11-27
 * @link http://tropotek.com.au/
 * @license Copyright 2023 Tropotek
 */
class Manager extends AdminManagerIface
{

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Client Manager');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setTable(\App\Table\Company::create());
        $this->getTable()->setEditUrl(\Bs\Uri::createHomeUrl('/companyEdit.html'));
        $this->getTable()->init();

        $filter = [
            'institutionId' => $this->getConfig()->getInstitutionId()
        ];
        $this->getTable()->setList($this->getTable()->findList($filter));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New Client',
            $this->getTable()->getEditUrl(), 'fa fa-building-o fa-add-action'));
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
<div class="tk-panel" data-panel-title="Clients" data-panel-icon="fa fa-building-o" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}