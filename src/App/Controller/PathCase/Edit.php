<?php
namespace App\Controller\PathCase;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('path-case-edit', Route::create('/staff/path/caseEdit.html', 'App\Controller\PathCase\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\PathCase
     */
    protected $pathCase = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Case Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->pathCase = new \App\Db\PathCase();
        if ($request->get('pathCaseId')) {
            $this->pathCase = \App\Db\PathCaseMap::create()->find($request->get('pathCaseId'));
        }

        $this->setForm(\App\Form\PathCase::create()->setModel($this->pathCase));
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
<div class="tk-panel" data-panel-title="Case Edit" data-panel-icon="fa fa-paw" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}