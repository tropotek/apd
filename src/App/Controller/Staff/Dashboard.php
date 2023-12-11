<?php
namespace App\Controller\Staff;

use App\Db\PathCaseMap;
use App\Db\Permission;
use App\Ui\CmsPanel;
use Tk\Alert;
use Tk\Request;
use Dom\Template;


class Dashboard extends \Uni\Controller\AdminIface
{

    protected $caseTable = null;

    protected $requestTable = null;

    protected $cmsPanel = null;

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
//        $this->cmsPanel = CmsPanel::create('Staff News', 'fa fa-newspaper-o', 'inst.cms.dashnews');
//        $this->cmsPanel = CmsPanel::create();
//        $this->cmsPanel->doDefault($request);


        $this->caseTable = \App\Table\PathCase::create();
        $this->caseTable->setEditUrl(\Bs\Uri::createHomeUrl('/pathCaseEdit.html'));
        $this->caseTable->init();
        $this->caseTable->findAction('columns')->setSelected(
            ['id', 'pathologyId', 'clientId', 'owner', 'age',
                'patientNumber', 'type', 'submissionType', 'status', 'reportStatus', 'arrival']
        );
        $this->caseTable->removeFilter('userId');
        $this->caseTable->removeFilter('pathologistId');
        $this->caseTable->removeFilter('companyId');
        $this->caseTable->removeFilter('animalTypeId');
        $this->caseTable->removeFilter('size');
        $this->caseTable->removeFilter('type');
        $this->caseTable->removeFilter('species');
        $this->caseTable->removeFilter('isDisposable');
        $this->caseTable->removeFilter('submissionType');
        $this->caseTable->removeFilter('disposeMethod');
        $this->caseTable->removeFilter('billable');

        $this->caseTable->getFilterForm()->getField('status')
            ->setValue([\App\Db\PathCase::STATUS_PENDING, \App\Db\PathCase::STATUS_EXAMINED,
                \App\Db\PathCase::STATUS_REPORTED, \App\Db\PathCase::STATUS_FROZEN_STORAGE, \App\Db\PathCase::STATUS_HOLD]);
        $filter = [
            'userId' => $this->getAuthUser()->getId(),
        ];
        $this->caseTable->setList($this->caseTable->findList($filter, $this->caseTable->getTool('created DESC')));


        if ($this->getAuthUser()->hasPermission([Permission::IS_TECHNICIAN])) {
            $this->requestTable = \App\Table\Request::create();
            $this->requestTable->setEditUrl(\Bs\Uri::createHomeUrl('/requestEdit.html'));
            $this->requestTable->init();
            $this->requestTable->removeFilter('pathologistId');
            $this->requestTable->removeFilter('clientId');
            $filter = [
                'userId' => $this->getAuthUser()->getId()
            ];
            $this->requestTable->setList($this->requestTable->findList($filter, $this->requestTable->getTool('created DESC')));
        }

        if ($this->getAuthUser()->hasPermission(Permission::IS_PATHOLOGIST)) {
            $filter = [
                'pathologistId' => $this->getAuthUser()->getId(),
                'status' => [
                    \App\Db\PathCase::STATUS_PENDING,
                    \App\Db\PathCase::STATUS_EXAMINED,
                    \App\Db\PathCase::STATUS_REPORTED,
                    \App\Db\PathCase::STATUS_FROZEN_STORAGE,
                    \App\Db\PathCase::STATUS_HOLD,
                ],
            ];
            $list = PathCaseMap::create()->findFiltered($filter);
            if ($list->count()) {
                Alert::addWarning('You have ' . $list->count() . ' waiting to be completed!');
            }
        }
    }

    public function show()
    {
        $template = parent::show();

        if ($this->cmsPanel) {
            $template->prependTemplate('content', $this->cmsPanel->show());
        }

        if ($this->caseTable) {
            $template->appendTemplate('cases', $this->caseTable->show());
            $template->setVisible('cases-panel', true);
        }

        if ($this->requestTable) {
            $template->appendTemplate('requests', $this->requestTable->show());
            $template->setVisible('requests-panel', true);
        }


        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="" var="content">
  
  <div class="row" choice="cases-panel">
    <div class="col-12">
      <div class="tk-panel" data-panel-title="My Case List" data-panel-icon="fa fa-heart" var="cases"></div>
    </div>
  </div>
  <div class="row" choice="requests-panel">
    <div class="col-12">
      <div class="tk-panel" data-panel-title="My Requests" data-panel-icon="fa fa-medkit" var="requests"></div>
    </div>
  </div>
  
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}