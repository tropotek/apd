<?php
namespace App\Controller\Storage;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('storage-edit', Route::create('/staff/storageEdit.html', 'App\Controller\Storage\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\Storage
     */
    protected $storage = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Storage Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->storage = new \App\Db\Storage();
        if ($request->get('storageId')) {
            $this->storage = \App\Db\StorageMap::create()->find($request->get('storageId'));
        }

        $this->setForm(\App\Form\Storage::create()->setModel($this->storage));
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
<div class="tk-panel" data-panel-title="Storage Edit" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}