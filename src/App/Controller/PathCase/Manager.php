<?php
namespace App\Controller\PathCase;

use Bs\Controller\AdminManagerIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('path-case-manager', Route::create('/staff/path/caseManager.html', 'App\Controller\PathCase\Manager::doDefault'));
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
        $this->setPageTitle('Case Manager');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setTable(\App\Table\PathCase::create());
        $this->getTable()->setEditUrl(\Bs\Uri::createHomeUrl('/pathCaseEdit.html'));
        $this->getTable()->init();

        $filter = array();
        $this->getTable()->setList($this->getTable()->findList($filter));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New Case',
            $this->getTable()->getEditUrl(), 'fa fa-paw fa-add-action'));
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
<div class="tk-panel" data-panel-title="Case List" data-panel-icon="fa fa-paw" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}