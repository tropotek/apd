<?php
namespace App\Controller\Request;

use Bs\Controller\AdminManagerIface;
use Dom\Template;
use Tk\Db\Tool;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('request-manager', Route::create('/staff/requestManager.html', 'App\Controller\Request\Manager::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-07-30
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
        $this->setPageTitle('Request Manager');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setTable(\App\Table\Request::create());
        $url = \Bs\Uri::createHomeUrl('/requestEdit.html');
        if ($request->query->get('pathCaseId'))
            $url->set('pathCaseId', $request->query->get('pathCaseId'));
        $this->getTable()->setEditUrl($url);
        $this->getTable()->init();

        $filter = [];
        if ($request->query->get('pathCaseId')) {
            $filter['pathCaseId'] = $request->get('pathCaseId');
        }
        $this->getTable()->setList($this->getTable()->findList($filter, $this->getTable()->getTool('b.pathology_id, c.number')));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
        if ($this->getConfig()->getRequest()->query->has('pathCaseId')) {
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New Request',
                $this->getTable()->getEditUrl(), 'fa fa-flask fa-add-action'));
        }
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
<div class="tk-panel" data-panel-title="Requests" data-panel-icon="fa fa-flask" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}