<?php
namespace App\Controller\Client;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('client-edit', Route::create('/staff/clientEdit.html', 'App\Controller\Client\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\Client
     */
    protected $client = null;


    /**
     * Iface constructor.
     */
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
        $this->client = new \App\Db\Client();
        if ($request->get('clientId')) {
            $this->client = \App\Db\ClientMap::create()->find($request->get('clientId'));
        }

        $this->setForm(\App\Form\Client::create()->setModel($this->client));
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
<div class="tk-panel" data-panel-title="Client Edit" data-panel-icon="fa fa-building" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}