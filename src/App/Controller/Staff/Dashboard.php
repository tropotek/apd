<?php
namespace App\Controller\Staff;

use Bs\Db\UserIface;
use Tk\Request;
use Dom\Template;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Dashboard extends \Uni\Controller\AdminIface
{


    /**
     * Dashboard constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPageTitle('Dashboard');
        $this->getCrumbs()->setVisible(false);
        $this->getActionPanel()->setVisible(false);
        $this->getConfig()->unsetSubject();

    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {



    }

    public function show()
    {
        $template = parent::show();

        //$template->appendTemplate('table', $this->subjectTable->getRenderer()->show());

        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="">

  <div class="tk-panel" data-panel-title="Case List" data-panel-icon="fa fa-paw" var="table"></div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}