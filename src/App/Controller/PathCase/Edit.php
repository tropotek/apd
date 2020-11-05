<?php
namespace App\Controller\PathCase;

use App\Config;
use App\Db\PathCase;
use App\Form\Note;
use App\Ui\Dialog\EmailReport;
use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Crumbs;
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
     * @var null|EmailReport
     */
    protected $emailReportDialog = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Edit Case');
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
        if ($request->has('type') && in_array($request->get('type'), $types)) {
            $this->pathCase->setType($request->get('type'));
        }
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

        if ($this->pathCase->getType())
            $this->setPageTitle('Edit Case ['.ucwords($this->pathCase->getType()).']');

        $this->setForm(\App\Form\PathCase::create()->setModel($this->pathCase));
        $this->initForm($request);
        // Only allow save for new path case
        if (!$this->pathCase->getId()) {
            $this->getForm()->removeField('update');
        }
        $this->getForm()->execute();

        // No need to do the reset for new cases
        if (!$this->pathCase->getId()) return null;


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

        $this->requestTable = \App\Table\RequestMin::create();
        $this->requestTable->setEditUrl(\Bs\Uri::createHomeUrl('/requestEdit.html'));
        $this->requestTable->setMinMode(true);
        $this->requestTable->init();
        $filter = array(
            'pathCaseId' => $this->pathCase->getId()
        );
        $this->requestTable->setList($this->requestTable->findList($filter, \Tk\Db\Tool::create('created DESC')));

        $this->emailReportDialog = new EmailReport($this->pathCase);
        $this->emailReportDialog->execute();

        // Add view count to Content
        if ($request->has('pdf')) {
            return $this->doPdf($request);
        }
    }

    /**
     * @param \Tk\Request $request
     * @return \Dom\Renderer\Renderer|\Dom\Template|null
     * @throws \Exception
     */
    public function doPdf(\Tk\Request $request)
    {
        $pdf = \App\Ui\CaseReportPdf::createReport($this->pathCase);
        $pdf->output();     // comment this to see html version
        return $pdf->show();
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

            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Report', \Uni\Uri::create()->set('pdf')->set(Crumbs::CRUMB_IGNORE), 'fa fa-file-pdf-o'))
                ->setAttr('target', '_blank');

            // Need a dialog here
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Email Report', \Uni\Uri::create()->set('pdf')->set(Crumbs::CRUMB_IGNORE), 'fa fa-envelope-o'))
                ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->emailReportDialog->getId());
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

        if ($this->pathCase->getType()) {
            $template->setAttr('panel', 'data-panel-title', $this->pathCase->getPathologyId() . ' - (' . ucwords($this->pathCase->getType()) . ')');
        } else {
            $template->setAttr('panel', 'data-panel-title', $this->pathCase->getPathologyId());
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

        if ($this->emailReportDialog)
            $template->appendBodyTemplate($this->emailReportDialog->show());

        if ($this->pathCase->getId()) {
            $notesList = \App\Table\Note::create('note-table')->init();
            $filter = array('model' => $this->pathCase);
            $notesList->setList($notesList->findList($filter, $notesList->getTool('created DESC')));
            if ($notesList->getList()->count()) {
                $template->appendTemplate('notes-body', $notesList->show());
            }
            $notesForm = new Note($this->pathCase, Config::getInstance()->getAuthUser());
            $template->appendTemplate('notes-body', $notesForm->show());
            $template->setVisible('notes-panel');
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
    <div class="tk-panel" data-panel-title="Staff Notes" data-panel-icon="fa fa-sticky-note" var="notes-body" choice="notes-panel"></div>
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