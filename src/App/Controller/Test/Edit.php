<?php
namespace App\Controller\Test;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('test-edit', Route::create('/staff/testEdit.html', 'App\Controller\Test\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2021-01-13
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\Test
     */
    protected $test = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Test Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->test = new \App\Db\Test();
        if ($request->get('testId')) {
            $this->test = \App\Db\TestMap::create()->find($request->get('testId'));
        }

        $this->setForm(\App\Form\Test::create()->setModel($this->test));
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
<div class="tk-panel" data-panel-title="Test Edit" data-panel-icon="fa fa-flask" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}