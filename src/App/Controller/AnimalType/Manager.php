<?php
namespace App\Controller\AnimalType;

use Bs\Controller\AdminManagerIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('animal-type-manager', Route::create('/staff/animal/typeManager.html', 'App\Controller\AnimalType\Manager::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2021-01-13
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class Manager extends AdminManagerIface
{

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Animal Type Manager');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->setTable(\App\Table\AnimalType::create());
        $this->getTable()->setEditUrl(\Bs\Uri::createHomeUrl('/animalTypeEdit.html'));
        $this->getTable()->init();

        $filter = array();
        $this->getTable()->setList($this->getTable()->findList($filter));
    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New Animal Type',
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
<div class="tk-panel" data-panel-title="Animal Types" data-panel-icon="fa fa-paw" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}