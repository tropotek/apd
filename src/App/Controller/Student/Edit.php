<?php
namespace App\Controller\Student;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('student-edit', Route::create('/staff/studentEdit.html', 'App\Controller\Student\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2023-11-26
 * @link http://tropotek.com.au/
 * @license Copyright 2023 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\Student
     */
    protected $student = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Student Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->student = new \App\Db\Student();
        if ($request->get('studentId')) {
            $this->student = \App\Db\StudentMap::create()->find($request->get('studentId'));
        }

        $this->setForm(\App\Form\Student::create()->setModel($this->student));
        $this->initForm($request);
        $this->getForm()->execute();
    }

    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        // Render the form
        $template->appendTemplate('panel', $this->getForm()->show());

        return $template;
    }

    /**
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-panel" data-panel-title="Student Edit" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}