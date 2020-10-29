<?php
namespace App\Controller\Contact;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('client-edit', Route::create('/staff/clientEdit.html', 'App\Controller\Client\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\Contact
     */
    protected $contact = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Contact Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->contact = new \App\Db\Contact();
        if ($request->get('contactId')) {
            $this->contact = \App\Db\ContactMap::create()->find($request->get('contactId'));
        }

        $this->setForm(\App\Form\Contact::create()->setModel($this->contact));
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
<div class="tk-panel" data-panel-title="Contact Edit" data-panel-icon="fa fa-user-o" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}