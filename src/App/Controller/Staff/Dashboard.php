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

    protected $caseTable = null;

    protected $requestTable = null;

    protected $filesTable = null;

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
        $this->caseTable = \App\Table\PathCase::create();
        $this->caseTable->setEditUrl(\Bs\Uri::createHomeUrl('/pathCaseEdit.html'));
        $this->caseTable->init();

        $filter = array(
            'userId' => $this->getAuthUser()->getId()
        );
        $this->caseTable->setList($this->caseTable->findList($filter));



    }

    public function show()
    {
        $template = parent::show();

        $template->appendTemplate('cases', $this->caseTable->show());


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
  <div class="row">
    <div class="col-12">
      <div class="tk-panel" data-panel-title="My Case List" data-panel-icon="fa fa-paw" var="cases"></div>
    </div>
<!--    <div class="col-4">-->
<!--      <div class="tk-panel" data-panel-title="Requests" data-panel-icon="fa fa-paw" var="Requests"></div>-->
<!--    </div>-->
  </div>
  <div class="row">
    <div class="col-12">
      <div class="tk-panel" data-panel-title="My Recent Files" data-panel-icon="fa fa-image" var="files"></div>
    </div>
  </div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}