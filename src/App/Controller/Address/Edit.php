<?php
namespace App\Controller\Address;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('address-edit', Route::create('/staff/addressEdit.html', 'App\Controller\Address\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\Address
     */
    protected $address = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Address Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->address = new \App\Db\Address();
        if ($request->get('addressId')) {
            $this->address = \App\Db\AddressMap::create()->find($request->get('addressId'));
        }

        $this->setForm(\App\Form\Address::create()->setModel($this->address));
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
<div class="tk-panel" data-panel-title="Address Edit" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}