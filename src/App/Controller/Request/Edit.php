<?php
namespace App\Controller\Request;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('request-edit', Route::create('/staff/requestEdit.html', 'App\Controller\Request\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-07-30
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\Request
     */
    protected $request = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Request Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->request = new \App\Db\Request();
        if ($request->get('cassetteId')) {
            $this->request->setCassetteId($request->get('cassetteId'));
        }
        if ($request->get('requestId')) {
            $this->request = \App\Db\RequestMap::create()->find($request->get('requestId'));
        }
        if (!$this->request->getPathCaseId())
            $this->request->setPathCaseId($this->request->getCassette()->getPathCaseId());

        $this->setForm(\App\Form\Request::create()->setModel($this->request));
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
<div class="tk-panel" data-panel-title="Request Edit" data-panel-icon="fa fa-flask" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}