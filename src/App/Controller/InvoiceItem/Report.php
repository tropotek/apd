<?php
namespace App\Controller\InvoiceItem;

use Bs\Controller\AdminManagerIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('invoice-item-manager', Route::create('/staff/invoice/itemManager.html', 'App\Controller\InvoiceItem\Manager::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2021-01-27
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class Report extends AdminManagerIface
{

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Invoice Item Manager');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {

        $this->setTable(\App\Table\InvoiceItemReport::create());
        //$this->getTable()->setEditUrl(\Bs\Uri::createHomeUrl('/invoiceItemEdit.html'));
        $this->getTable()->init();

        $filter = [];
        $this->getTable()->setList($this->getTable()->findList($filter));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New Invoice Item',
            $this->getTable()->getEditUrl(), 'fa fa-money fa-add-action'));
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
<div class="tk-panel" data-panel-title="Invoice Items" data-panel-icon="fa fa-money" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}