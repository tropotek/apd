<?php
namespace App\Controller\Service;

use App\Db\ServiceMap;
use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;
use Tk\Ui\Dialog\AjaxSelect;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('service-edit', Route::create('/staff/serviceEdit.html', 'App\Controller\Service\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\Service
     */
    protected $service = null;

    /**
     * @var null|\Uni\Table\UserList
     */
    protected $userTable = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Service Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->service = new \App\Db\Service();
        if ($request->get('serviceId')) {
            $this->service = \App\Db\ServiceMap::create()->find($request->get('serviceId'));
        }

        $this->setForm(\App\Form\Service::create()->setModel($this->service));
        $this->initForm($request);
        $this->getForm()->execute();


        if ($this->service->getId()) {
            $this->userTable = \Uni\Table\UserList::create();

            $this->userTable->setUserType(\Uni\Db\User::TYPE_STAFF);
            $this->userTable->setEditUrl(\Uni\Uri::createSubjectUrl('/staffUserEdit.html'));
            $this->userTable->setAjaxParams(array(
                'institutionId' => $this->getConfig()->getInstitutionId(),
                'active' => 1,
                'type' => \Uni\Db\User::TYPE_STAFF
            ));
            $this->userTable->setOnSelect(function (AjaxSelect $dialog) {
                /** @var \Uni\Db\User $user */
                $config = $dialog->getConfig();
                $data = $config->getRequest()->all();
                $user = $config->getUserMapper()->find($data['selectedId']);
                if (!$user) {
                    \Tk\Alert::addWarning('User not found!');
                } else if (!$user->isStaff()) {
                    \Tk\Alert::addWarning('User is not a staff member!');
                } else if (!ServiceMap::create()->hasUser($this->service->getVolatileId(), $user->getId())) {
                    ServiceMap::create()->addUser($this->service->getVolatileId(), $user->getId());
                    \Tk\Alert::addSuccess($user->getName() . ' has been linked to this Service.');
                } else {
                    \Tk\Alert::addInfo($user->getName() . ' is already linked to this Service.');
                }
                return \Uni\Uri::create();
            });
            $this->userTable->init();
            $this->userTable->removeAction('csv');
            $this->userTable->removeAction('delete');
            $this->userTable->appendAction(\Tk\Table\Action\Delete::create()->setLabel('Remove')
                ->setConfirmStr('Are you sure you want to remove the user from this service.')
                ->addOnDelete(function (\Tk\Table\Action\Delete $action, \Uni\Db\User $obj) {
                    if (ServiceMap::create()->hasUser($this->service->getVolatileId(), $obj->getId())) {
                        ServiceMap::create()->removeUser($this->service->getVolatileId(), $obj->getId());
                        \Tk\Alert::addSuccess($obj->getName() . ' has been removed from this Service.');
                    }
                    return false;
                }));
            $filter = array(
                'id' => ServiceMap::create()->findUsers($this->service->getId())
            );
            if (count($filter['id']))
                $this->userTable->setList($this->userTable->findList($filter));
        }

    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->appendTemplate('panel', $this->getForm()->show());
        if (!$this->service->getId()) {
            $template->setVisible('right-panel', false);
            $template->removeCss('left-panel', 'col-8')->addCss('left-panel', 'col-md-12 col-12');
        } else {
            if ($this->userTable) {
                $template->appendTemplate('right-panel-01', $this->userTable->show());
            }
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
  <div class="col-8" var="left-panel">
    <div class="tk-panel" data-panel-title="Service Edit" data-panel-icon="fa fa-tags" var="panel"></div>
  </div>
  <div class="col-4" var="right-panel">
    <div class="tk-panel" data-panel-title="Staff" data-panel-icon="fa fa-group" var="right-panel-01"></div>
  </div>
</div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}