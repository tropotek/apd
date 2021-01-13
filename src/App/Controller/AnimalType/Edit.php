<?php
namespace App\Controller\AnimalType;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('animal-type-edit', Route::create('/staff/animal/typeEdit.html', 'App\Controller\AnimalType\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2021-01-13
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\AnimalType
     */
    protected $animalType = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Animal Type Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->animalType = new \App\Db\AnimalType();
        if ($request->get('animalTypeId')) {
            $this->animalType = \App\Db\AnimalTypeMap::create()->find($request->get('animalTypeId'));
        }

        $this->setForm(\App\Form\AnimalType::create()->setModel($this->animalType));
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
<div class="tk-panel" data-panel-title="Animal Type Edit" data-panel-icon="fa fa-paw" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}