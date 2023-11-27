<?php
namespace App\Controller\CompanyContact;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('company-contact-edit', Route::create('/staff/company/contactEdit.html', 'App\Controller\CompanyContact\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2023-11-27
 * @link http://tropotek.com.au/
 * @license Copyright 2023 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\CompanyContact
     */
    protected $companyContact = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Contact Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->companyContact = new \App\Db\CompanyContact();
        $this->companyContact->setCompanyId($request->request->getInt('companyId'));
        if ($request->get('companyContactId')) {
            $this->companyContact = \App\Db\CompanyContactMap::create()->find($request->get('companyContactId'));
        }

        $this->setForm(\App\Form\CompanyContact::create()->setModel($this->companyContact));
        $this->initForm($request);
        $this->getForm()->execute();
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->appendTemplate('panel', $this->getForm()->show());

        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-panel" data-panel-title="Contact Edit" data-panel-icon="fa fa-user-o" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}