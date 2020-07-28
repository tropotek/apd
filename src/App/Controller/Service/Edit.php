<?php
namespace App\Controller\Service;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('service-edit', Route::create('/staff/serviceEdit.html', 'App\Controller\Service\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\Service
     */
    protected $service = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Service Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->service = new \App\Db\Service();
        if ($request->get('serviceId')) {
            $this->service = \App\Db\ServiceMap::create()->find($request->get('serviceId'));
        }

        $this->setForm(\App\Form\Service::create()->setModel($this->service));
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
<div class="tk-panel" data-panel-title="Service Edit" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}