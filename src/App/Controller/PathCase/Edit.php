<?php
namespace App\Controller\PathCase;

use App\Db\PathCase;
use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

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
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Case Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->pathCase = new \App\Db\PathCase();
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

        $this->setForm(\App\Form\PathCase::create()->setModel($this->pathCase));
        $this->initForm($request);
        // Only allow save for new path case
        if (!$this->pathCase->getId()) {
            $this->getForm()->removeField('update');
        }
        $this->getForm()->execute();

        if (!$this->pathCase->getId()) return;

        $this->statusTable = \Bs\Table\Status::create()
            ->setSelectedColumns(array('name', 'message', 'created'))->init();
            //->setEditUrl(\Uni\Uri::createHomeUrl('/pathCaseManager.html'))->init();
        $filter = array(
            'model' => $this->pathCase
        );
        $this->statusTable->setList($this->statusTable->findList($filter, \Tk\Db\Tool::create('created DESC', 0)));

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->appendTemplate('panel', $this->getForm()->show());

        if ($this->statusTable) {
            $template->appendTemplate('side-panel-status', $this->statusTable->show());
            $template->setVisible('side-panel-status');
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
  <div class="col" var="panel1">
    <div class="tk-panel" data-panel-title="Case Edit" data-panel-icon="fa fa-paw" var="panel"></div>
  </div>
  <div class="col-4" var="panel2" choice="panel2">
    <div class="tk-panel" data-panel-title="Sample Slides" data-panel-icon="fa fa-list-alt" var="side-panel-slides" choice1="side-panel-slides"></div>
    <div class="tk-panel" data-panel-title="Sample requests" data-panel-icon="fa fa-medkit" var="side-panel-requests" choice1="side-panel-requests"></div>
    <div class="tk-panel" data-panel-title="Files" data-panel-icon="fa fa-floppy-o" var="side-panel-files" choice="side-panel-files"></div>
    <div class="tk-panel" data-panel-title="Status Log" data-panel-icon="fa fa-list" var="side-panel-status" choice="side-panel-status"></div>
  </div>
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}