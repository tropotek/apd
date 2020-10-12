<?php
namespace App\Controller\PathCase;

use App\Db\PathCase;
use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;
use Uni\Uri;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('path-case-edit', Route::create('/staff/path/caseEdit.html', 'App\Controller\PathCase\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\PathCase
     */
    protected $pathCase = null;

    /**
     * @var null|\Bs\Table\Status
     */
    protected $statusTable = null;

    /**
     * @var null|\App\Table\Cassette
     */
    protected $cassetteTable = null;

    /**
     * @var null|\App\Table\Request
     */
    protected $requestTable = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Create Case');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->pathCase = new \App\Db\PathCase();
        $this->pathCase->setPathologyId($this->pathCase->getVolatilePathologyId());
        if ($request->has('clientId'))
            $this->pathCase->setClientId((int)$request->get('clientId'));
        $types = \Tk\ObjectUtil::getClassConstants($this->pathCase, 'TYPE_');
        if ($request->has('type') && in_array($request->get('type'), $types))
            $this->pathCase->setType($request->get('type'));
        $sTypes = \Tk\ObjectUtil::getClassConstants($this->pathCase, 'SUBMISSION_');
        if ($request->has('submissionType') && in_array($request->get('submissionType'), $sTypes))
            $this->pathCase->setSubmissionType($request->get('submissionType'));

        if ($request->get('pathCaseId')) {
            $this->pathCase = \App\Db\PathCaseMap::create()->find($request->get('pathCaseId'));
        }
        if ($this->pathCase->getId()) {
            $this->setPageTitle($this->pathCase->getPathologyId() . ' - (' . ucwords($this->pathCase->getType()) . ')');
        }

        if (!$request->has('action')) {     // Avoid duplicated from columns table plugin
            if ($this->pathCase->isIssueAlert()) {
                \Tk\Alert::addWarning($this->pathCase->getIssue(), 'Animal Issue Alert!');
            }
            if ($this->pathCase->isZoonoticAlert()) {
                \Tk\Alert::addError($this->pathCase->getZoonotic(), 'Zoonotic Alert!');
            }
        }


        $this->setForm(\App\Form\PathCase::create()->setModel($this->pathCase));
        $this->initForm($request);
        // Only allow save for new path case
        if (!$this->pathCase->getId()) {
            $this->getForm()->removeField('update');
        }
        $this->getForm()->execute();

        // No need to do the reset for new cases
        if (!$this->pathCase->getId()) return;


        $this->statusTable = \Bs\Table\Status::create()
            ->setSelectedColumns(array('name', 'message', 'created'))->init();
            //->setEditUrl(\Uni\Uri::createHomeUrl('/pathCaseManager.html'))->init();
        $filter = array(
            'model' => $this->pathCase
        );
        $this->statusTable->setList($this->statusTable->findList($filter, \Tk\Db\Tool::create('created DESC', 0)));


        $this->cassetteTable = \App\Table\Cassette::create();
        $this->cassetteTable->setEditUrl(\Bs\Uri::createHomeUrl('/cassetteEdit.html'));
        $this->cassetteTable->setMinMode(true);
        $this->cassetteTable->init();
        $filter = array(
            'pathCaseId' => $this->pathCase->getId()
        );
        $this->cassetteTable->setList($this->cassetteTable->findList($filter, \Tk\Db\Tool::create('number')));


        $this->requestTable = \App\Table\Request::create();
        $this->requestTable->setEditUrl(\Bs\Uri::createHomeUrl('/requestEdit.html'));
        $this->requestTable->setMinMode(true);
        $this->requestTable->init();
        $filter = array(
            'pathCaseId' => $this->pathCase->getId()
        );
        $this->requestTable->setList($this->requestTable->findList($filter, \Tk\Db\Tool::create('created DESC')));

    }

    /**
     * Add actions here
     */
    public function initActionPanel()
    {
        if ($this->pathCase->getId()) {
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn(
                'Cassette List',
                \Uni\Uri::createHomeUrl('/cassetteManager.html')->set('pathCaseId', $this->pathCase->getId()),
                'fa fa-stack-overflow fa-add-action'));
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn(
                'Request List',
                \Uni\Uri::createHomeUrl('/requestManager.html')->set('pathCaseId', $this->pathCase->getId()),
                'fa fa-medkit fa-add-action'));


            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn(
                'New Cassette',
                \Uni\Uri::createHomeUrl('/cassetteEdit.html')->set('pathCaseId', $this->pathCase->getId()),
                'fa fa-stack-overflow fa-add-action'));
//        $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('New Request',
//            Uri::createHomeUrl('#'), 'fa fa-medkit fa-add-action'));
        }
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $this->initActionPanel();
        $template = parent::show();

        // Render the form
        $template->appendTemplate('panel', $this->getForm()->show());
        if ($this->pathCase->getId()) {
            $template->setAttr('panel', 'data-panel-title', $this->pathCase->getPathologyId() . ' - (' . ucwords($this->pathCase->getType()) . ')');
        }

        if ($this->statusTable) {
            $template->appendTemplate('side-panel-status', $this->statusTable->show());
            $template->setVisible('side-panel-status');
        }
        if ($this->cassetteTable) {
            $template->appendTemplate('side-panel-cassette', $this->cassetteTable->show());
            $template->setVisible('side-panel-cassette');
        }
        if ($this->requestTable) {
            $template->appendTemplate('side-panel-requests', $this->requestTable->show());
            $template->setVisible('side-panel-requests');
        }

        if ($this->pathCase->getId()) {
            $template->setVisible('panel2');
        }

        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="row">
  <div class="col-8" var="panel1">
    <div class="tk-panel" data-panel-title="Case Edit" data-panel-icon="fa fa-paw" var="panel"></div>
  </div>
  <div class="col-4" var="panel2" choice="panel2">
    <div class="tk-panel" data-panel-title="Cassettes" data-panel-icon="fa fa-stack-overflow" var="side-panel-cassette" choice="side-panel-cassette"></div>
    <div class="tk-panel tk-request-list" data-panel-title="Histology Requests" data-panel-icon="fa fa-medkit" var="side-panel-requests" choice="side-panel-requests"></div>
    <div class="tk-panel" data-panel-title="Files" data-panel-icon="fa fa-floppy-o" var="side-panel-files" choice="side-panel-files"></div>
    <div class="tk-panel" data-panel-title="Status Log" data-panel-icon="fa fa-list" var="side-panel-status" choice="side-panel-status"></div>
  </div>
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}