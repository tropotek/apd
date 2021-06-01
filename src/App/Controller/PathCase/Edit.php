<?php
namespace App\Controller\PathCase;

use App\Config;
use App\Db\EditLog;
use App\Db\EditLogMap;
use App\Db\PathCase;
use App\Db\Permission;
use App\Form\Note;
use App\Ui\Dialog\EmailReport;
use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Alert;
use Tk\Crumbs;
use Tk\Form;
use Tk\Form\Event\Submit;
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
     * @var null|\App\Table\InvoiceItemMin
     */
    protected $invoiceTable = null;

    /**
     * @var null|\App\Table\EditLog
     */
    protected $editLogTable = null;

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
        //$this->pathCase->setPathologistId($this->getAuthUser()->getId()); // May cause user not to concisely select it
        $this->pathCase->setPathologyId($this->pathCase->getVolatilePathologyId());
        if ($request->has('editLogId')) {
            /** @var EditLog $log */
            $log = EditLogMap::create()->find($request->get('editLogId'));
            if ($log) {
                $this->pathCase = $log->getState();
                $request->request->set('pathCaseId', $this->pathCase->getId());
            }
        } else {
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
        }



        if ($this->pathCase->getId()) {
            $this->setPageTitle($this->pathCase->getPathologyId() . ' - (' . ucwords($this->pathCase->getType()) . ')');
        }
        if ($this->pathCase->getType())
            $this->setPageTitle('Edit Case ['.ucwords($this->pathCase->getType()).']');



        $this->setForm(\App\Form\PathCase::create()->setModel($this->pathCase));
        $this->initForm($request);
        // Only allow save for new path case
        if (!$this->pathCase->getId()) {
            $this->getForm()->removeField('update');
        }
        if ($request->has('editLogId')) {
            $this->getForm()->removeField('update');
            $this->getForm()->removeField('save');
            $this->getForm()->removeField('cancel');
            $btn = $this->getForm()->appendField(new Submit('Revert', function (Form $form, Form\Event\Iface $event) {
                /** @var EditLog $log */
                $log = EditLogMap::create()->find($this->getConfig()->getRequest()->get('editLogId'));
                if ($log) {
                    vd('Revert the case to here');
                    vd($log->getState());
                    $log->getState()->save();
                }
                $event->setRedirect(\Bs\Uri::create()->remove('editLogId')->set('pathCaseId', $log->getFid()));
            }), 'Cancel')->setAttr('title', 'Revert the path case to this revision.');
            $btn->removeCss('btn-default');
            $btn->addCss('btn-success');
            $btn->setIcon('fa fa-undo');
            $this->getForm()->appendField(new Form\Event\Link('cancel', \Bs\Uri::create()->remove('editLogId')->set('pathCaseId', $this->pathCase->getId())));
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
            'pathCaseId' => $this->pathCase->getId(),
            'status' => [\App\Db\Request::STATUS_PENDING, \App\Db\Request::STATUS_COMPLETED]
        );
        $this->requestTable->setList($this->requestTable->findList($filter, \Tk\Db\Tool::create('created DESC')));

        $this->invoiceTable = \App\Table\InvoiceItemMin::create();
        $this->invoiceTable->setEditUrl(\Bs\Uri::createHomeUrl('/invoiceItemEdit.html'));
        $this->invoiceTable->init();
        $filter = array(
            'pathCaseId' => $this->pathCase->getId()
        );
        $this->invoiceTable->setList($this->invoiceTable->findList($filter, \Tk\Db\Tool::create('created DESC')));

        $this->editLogTable = \App\Table\EditLog::create();
        $this->editLogTable->setEditUrl(\Bs\Uri::createHomeUrl('/pathCaseEdit.html')->set('pathCaseId',$this->pathCase->getId()));
        $this->editLogTable->init();
        $filter = array(
            'model' => $this->pathCase
        );
        $this->editLogTable->setList($this->editLogTable->findList($filter, \Tk\Db\Tool::create('created DESC')));

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
        $int = '';
        if ($this->pathCase->getReportStatus() == PathCase::REPORT_STATUS_INTERIM)
            $int = '-' . PathCase::REPORT_STATUS_INTERIM;
        $filename = 'AnatomicPathologyReport-' . $this->pathCase->getPathologyId() . '-' . $this->pathCase->getPatientNumber().$int.'.pdf';
        if (!$request->has('isHtml'))
            $pdf->output($filename);     // comment this to see html version
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
                'Invoice Items',
                \Uni\Uri::createHomeUrl('/invoiceItemManager.html')->set('pathCaseId', $this->pathCase->getId()),
                'fa fa-money fa-add-action'));

//            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn(
//                'New Cassette',
//                \Uni\Uri::createHomeUrl('/cassetteEdit.html')->set('pathCaseId', $this->pathCase->getId()),
//                'fa fa-stack-overflow fa-add-action'));

            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('Email Report', \Uni\Uri::create()->set('pdf')->set(Crumbs::CRUMB_IGNORE), 'fa fa-envelope-o'))
                ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#'.$this->emailReportDialog->getId());

            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('HTML Report', \Uni\Uri::create()->set('pdf')->set('isHtml')->set(Crumbs::CRUMB_IGNORE), 'fa fa-code'))
                ->setAttr('target', '_blank')->setAttr('title', 'Download/View Report');
            $this->getActionPanel()->append(\Tk\Ui\Link::createBtn('PDF Report', \Uni\Uri::create()->set('pdf')->set(Crumbs::CRUMB_IGNORE), 'fa fa-file-pdf-o'))
                ->setAttr('target', '_blank')->setAttr('title', 'Download/View Report');
        }


    }
    
    /**
     * @return \Dom\Template
     */
    public function show()
    {
        if ($this->pathCase->hasStatus(PathCase::STATUS_COMPLETED) && $this->pathCase->isBilled() && $this->getAuthUser()->hasPermission(Permission::CASE_FULL_EDIT)) {
            Alert::addInfo('This case has been marked COMPLETED! You have permission to modify completed cases.');
        }

        $this->initActionPanel();
        $template = parent::show();

        $editable = (int)$this->pathCase->isEditable($this->getAuthUser());
        $js = <<<JS
config.caseEditable = $editable;
JS;
        $template->appendJs($js, array('data-jsl-priority' => -1000));
        $js = <<<JS
jQuery(function ($) {
  if (!config.caseEditable) {
    //var actions = $('.tk-ui-action-panel');
    var content = $('.path-case-edit');
    content.find('input, select, textarea, button, .btn').attr('disabled', 'disabled').addClass('disabled');
  }
});
JS;
        $template->appendJs($js);

        if ($this->pathCase->isIssueAlert()) {
            \Tk\Alert::addWarning($this->pathCase->getIssue(), 'Animal Issue Alert!');
        }
        if ($this->pathCase->isZoonoticAlert()) {
            \Tk\Alert::addError($this->pathCase->getZoonotic(), 'Zoonotic Alert!');
        }
        if (!$this->pathCase->isEditable($this->getAuthUser())) {
            \Tk\Alert::addWarning($this->pathCase->getZoonotic(), 'This case has been locked due to it`s completion.');
        }

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
        if ($this->invoiceTable) {
            $template->appendTemplate('side-panel-invoice', $this->invoiceTable->show());
            $template->setVisible('side-panel-invoice');
        }
        if ($this->editLogTable) {
            $template->appendTemplate('side-panel-edit-log', $this->editLogTable->show());
            $template->setVisible('side-panel-edit-log');
        }

        if ($this->pathCase->getId()) {
            $template->setVisible('panel2');
        } else {
            $template->setAttr('panel1', 'class', 'col-12');
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
<div class="row path-case-edit">
  <div class="col-8" var="panel1">
    <div class="tk-panel" data-panel-title="Case Edit" data-panel-icon="fa fa-paw" var="panel"></div>
  </div>
  <div class="col-4" var="panel2" choice="panel2">
    <div class="tk-panel" data-panel-title="Staff Notes" data-panel-icon="fa fa-sticky-note" var="notes-body" choice="notes-panel"></div>
    <div class="tk-panel tk-cassette-list" data-panel-title="Cassettes" data-panel-icon="fa fa-stack-overflow" var="side-panel-cassette" choice="side-panel-cassette"></div>
    <div class="tk-panel tk-request-list" data-panel-title="Histology Requests" data-panel-icon="fa fa-medkit" var="side-panel-requests" choice="side-panel-requests">
      <p choice="hide"><small>* = the service or cassette record has been deleted.</small></p>
    </div>
    <div class="tk-panel" data-panel-title="Files" data-panel-icon="fa fa-floppy-o" var="side-panel-files" choice="side-panel-files"></div>
    <div class="tk-panel" data-panel-title="Status Log" data-panel-icon="fa fa-list" var="side-panel-status" choice="side-panel-status"></div>

    <div class="tk-panel tk-invoice-list" data-panel-title="Invoice Items" data-panel-icon="fa fa-money" var="side-panel-invoice" choice="side-panel-invoice"></div>
    <div class="tk-panel tk-edit-log" data-panel-title="Edit History" data-panel-icon="fa fa-list" var="side-panel-edit-log" choice="side-panel-edit-log"></div>
  </div>
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}