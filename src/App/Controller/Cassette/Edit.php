<?php
namespace App\Controller\Cassette;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('cassette-edit', Route::create('/staff/cassetteEdit.html', 'App\Controller\Cassette\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\Cassette
     */
    protected $cassette = null;

    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Cassette Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->cassette = new \App\Db\Cassette();
        if ($request->get('pathCaseId')) {
            $this->cassette->setPathCaseId($request->get('pathCaseId'));
        }
        $this->cassette->setNumber(\App\Db\Cassette::getNextNumber($this->cassette->getPathCaseId()));
        if ($request->get('cassetteId')) {
            $this->cassette = \App\Db\CassetteMap::create()->find($request->get('cassetteId'));
        }

        $this->setForm(\App\Form\Cassette::create()->setModel($this->cassette));
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
<div class="tk-panel" data-panel-title="Cassette Edit" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}