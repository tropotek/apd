<?php
namespace App\Controller\Cassette;

use Bs\Controller\AdminManagerIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('cassette-manager', Route::create('/staff/cassetteManager.html', 'App\Controller\Cassette\Manager::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Manager extends AdminManagerIface
{

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Cassette Manager');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setTable(\App\Table\Cassette::create());
        $this->getTable()->setEditUrl(\Bs\Uri::createHomeUrl('/cassetteEdit.html')->set('pathCaseId', $request->get('pathCaseId')));
        $this->getTable()->init();

        $filter = [];
        $filter['pathCaseId'] = $request->get('pathCaseId');
        $this->getTable()->setList($this->getTable()->findList($filter));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New Cassette',
            $this->getTable()->getEditUrl(), 'fa fa-stack-overflow fa-add-action'));
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
<div class="tk-panel" data-panel-title="Cassettes" data-panel-icon="fa fa-stack-overflow" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}