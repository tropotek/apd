<?php
namespace App\Controller\PathCase;

use Bs\Controller\AdminManagerIface;
use Bs\Uri;
use Dom\Template;
use Tk\Db\Tool;
use Tk\Request;
use Tk\Ui\Button;
use Tk\Ui\ButtonDropdown;

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
        $this->getTable()->setList($this->getTable()->findList($filter, $this->getTable()->getTool('created DESC')));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
        $links = [
            \Tk\Ui\Link::create('New Necropsy Case', Uri::createHomeUrl('/pathCaseEdit.html?type=necropsy'), 'fa fa-heart'),
            \Tk\Ui\Link::create('New Biopsy Case', Uri::createHomeUrl('/pathCaseEdit.html?type=biopsy'), 'fa fa-heartbeat'),
        ];
        $this->getActionPanel()->append(ButtonDropdown::createButtonDropdown('New Case', '', $links));

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
<div class="tk-panel" data-panel-title="Case List" data-panel-icon="fa fa-heart" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}