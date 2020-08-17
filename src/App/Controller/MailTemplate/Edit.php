<?php
namespace App\Controller\MailTemplate;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('mail-template-edit', Route::create('/staff/mail/templateEdit.html', 'App\Controller\MailTemplate\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-08-17
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\MailTemplate
     */
    protected $mailTemplate = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Mail Template Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->mailTemplate = new \App\Db\MailTemplate();
        if ($request->get('mailTemplateId')) {
            $this->mailTemplate = \App\Db\MailTemplateMap::create()->find($request->get('mailTemplateId'));
        }

        $this->setForm(\App\Form\MailTemplate::create()->setModel($this->mailTemplate));
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
<div class="tk-panel" data-panel-title="Mail Template Edit" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}