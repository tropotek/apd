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
     * @var \Uni\Table\Subject
     */
    protected $subjectTable = null;

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

        $this->subjectTable = \Uni\Table\Subject::create()->init();
        $this->subjectTable->removeAction('delete');
        $this->subjectTable->findCell('name')->addOnPropertyValue(function ($cell, $obj, $value) {
            /** @var UserIface $obj */
            return $obj->getName();
        })->setUrl(function ($cell, $obj) {
            /** @var \Tk\Table\Cell\Iface $cell */
            $url = \Uni\Uri::createSubjectUrl('/index.html', $obj);
            $cell->setUrlProperty('');
            return $url;
        });

        $filter = array();
        $filter['institutionId'] = $this->getConfig()->getInstitutionId();
        $filter['userId'] = $this->getAuthUser()->getId();
        $this->subjectTable->setList($this->subjectTable->findList($filter));


    }

    public function show()
    {
        $template = parent::show();

        $template->appendTemplate('table', $this->subjectTable->getRenderer()->show());

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

  <div class="tk-panel" data-panel-title="Subject List" data-panel-icon="fa fa-university" var="table"></div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}