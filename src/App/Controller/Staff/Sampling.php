<?php
namespace App\Controller\Staff;

use App\Ui\CmsPanel;
use Tk\Db\Tool;
use Tk\Request;
use Dom\Template;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Sampling extends \Uni\Controller\AdminIface
{
    protected $cmsPanel = null;

    /**
     * Dashboard constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setPageTitle('Tissue Sample Requests');
        //$this->getCrumbs()->setVisible(false);
        //$this->getActionPanel()->setVisible(false);
        //$this->getConfig()->unsetSubject();

    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->cmsPanel = CmsPanel::create('Tissue Sample Requests', 'fa fa-heartbeat', 'inst.cms.sampling');
        $this->cmsPanel->doDefault($request);

    }

    public function show()
    {
        $template = parent::show();

        if ($this->cmsPanel) {
            $template->prependTemplate('content', $this->cmsPanel->show());
        }

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
<div class="" var="content"></div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}