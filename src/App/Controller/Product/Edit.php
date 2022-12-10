<?php
namespace App\Controller\Product;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('product-edit', Route::create('/staff/productEdit.html', 'App\Controller\Product\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2022-12-10
 * @link http://tropotek.com.au/
 * @license Copyright 2022 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\Product
     */
    protected $product = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Product Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->product = new \App\Db\Product();
        if ($request->get('productId')) {
            $this->product = \App\Db\ProductMap::create()->find($request->get('productId'));
        }

        $this->setForm(\App\Form\Product::create()->setModel($this->product));
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
<div class="tk-panel" data-panel-title="Product Edit" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}