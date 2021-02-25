<?php
namespace App\Controller\Staff;

use Tk\Db\Tool;
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
        $this->caseTable->removeFilter('userId');
        $this->caseTable->removeFilter('clientId');
        $this->caseTable->removeFilter('ownerId');
        $this->caseTable->removeFilter('type');
        $this->caseTable->removeFilter('submissionType');
        $this->caseTable->removeFilter('status');
        //$this->caseTable->resetSession();
        $filter = array(
            'userId' => $this->getAuthUser()->getId()
        );
        $this->caseTable->setList($this->caseTable->findList($filter, $this->caseTable->getTool('created DESC')));


        $this->requestTable = \App\Table\Request::create();
        $this->requestTable->setEditUrl(\Bs\Uri::createHomeUrl('/requestEdit.html'));
        $this->requestTable->init();
        $filter = array(
            'userId' => $this->getAuthUser()->getId(),
            //'pathologistId' => $this->getAuthUser()->getId()
        );
        $this->requestTable->setList($this->requestTable->findList($filter, $this->requestTable->getTool('created DESC')));


    }

    public function show()
    {
        $template = parent::show();

        if ($this->caseTable)
            $template->appendTemplate('cases', $this->caseTable->show());

        if ($this->requestTable)
            $template->appendTemplate('requests', $this->requestTable->show());

        //$template->appendHtml('files', '<p><i>{TODO: Add an image/file gallery here...}</i></p>');

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
  </div>
  <div class="row">
    <div class="col-12">
      <div class="tk-panel" data-panel-title="My Requests" data-panel-icon="fa fa-medkit" var="requests"></div>
    </div>
  </div>
  
  
  <div class="row" choice="todo">
    <div class="col-12">
      <div class="tk-panel" data-panel-title="My Recent Files" data-panel-icon="fa fa-image" var="files"></div>
    </div>
  </div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}