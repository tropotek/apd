<?php
namespace App\Controller\Student;

use Bs\Controller\AdminManagerIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('student-manager', Route::create('/staff/studentManager.html', 'App\Controller\Student\Manager::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2023-11-26
 * @link http://tropotek.com.au/
 * @license Copyright 2023 Tropotek
 */
class Manager extends AdminManagerIface
{

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Student Manager');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setTable(\App\Table\Student::create());
        $this->getTable()->setEditUrl(\Bs\Uri::createHomeUrl('/studentEdit.html'));
        $this->getTable()->init();

        $filter = [];
        $this->getTable()->setList($this->getTable()->findList($filter));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New Student',
            $this->getTable()->getEditUrl(), 'fa fa-user-o fa-add-action'));
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
<div class="tk-panel" data-panel-title="Students" data-panel-icon="fa fa-user-o" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}