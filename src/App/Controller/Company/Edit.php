<?php
namespace App\Controller\Company;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('company-edit', Route::create('/staff/companyEdit.html', 'App\Controller\Company\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2023-11-27
 * @link http://tropotek.com.au/
 * @license Copyright 2023 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\Company
     */
    protected $company = null;

    /**
     * @var null|\App\Table\CompanyContact
     */
    protected $contactTable = null;


    public function __construct()
    {
        $this->setPageTitle('Client Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->company = new \App\Db\Company();
        if ($request->get('companyId')) {
            $this->company = \App\Db\CompanyMap::create()->find($request->get('companyId'));
        }

        $this->setForm(\App\Form\Company::create()->setModel($this->company));
        $this->initForm($request);
        $this->getForm()->execute();

        $this->contactTable = \App\Table\CompanyContact::create('contact-list');
        $this->contactTable->setEditUrl(\Bs\Uri::createHomeUrl('/companyContactEdit.html')->set('companyId', $this->company->getId()));
        $this->contactTable->init();
        $filter = array(
            'companyId' => $this->company->getId()
        );
        $this->contactTable->setList($this->contactTable->findList($filter, $this->contactTable->getTool('name', 15)));
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->appendTemplate('panel', $this->getForm()->show());

        if ($this->contactTable) {
            $template->appendTemplate('contact-panel', $this->contactTable->show());
        }

        if ($this->company->getId()) {
            $template->setVisible('panel2');
        } else {
            $template->setAttr('panel1', 'class', 'col-12');
        }

        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML


<div class="row company-edit">
  <div class="col-7" var="panel1">
    <div class="tk-panel" data-panel-title="Client Edit" data-panel-icon="fa fa-building-o" var="panel"></div>
  </div>
  <div class="col-5" var="panel2" choice="panel2">
    <div class="tk-panel tk-contact-list" data-panel-title="Client Contacts" data-panel-icon="fa fa-user" var="contact-panel"></div>
  </div>
</div>

HTML;
        return \Dom\Loader::load($xhtml);
    }

}