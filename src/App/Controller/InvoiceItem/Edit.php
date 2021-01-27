<?php
namespace App\Controller\InvoiceItem;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('invoice-item-edit', Route::create('/staff/invoice/itemEdit.html', 'App\Controller\InvoiceItem\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2021-01-27
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\InvoiceItem
     */
    protected $invoiceItem = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Invoice Item Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->invoiceItem = new \App\Db\InvoiceItem();
        $this->invoiceItem->setPathCaseId($request->get('pathCaseId'));
        if ($request->get('invoiceItemId')) {
            $this->invoiceItem = \App\Db\InvoiceItemMap::create()->find($request->get('invoiceItemId'));
        }

        if (!$this->invoiceItem->getPathCase())
            throw new \Tk\Exception('No PathCaseId associated to this object.');

        $this->setForm(\App\Form\InvoiceItem::create()->setModel($this->invoiceItem));
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
<div class="tk-panel" data-panel-title="Invoice Item Edit" data-panel-icon="fa fa-money" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}