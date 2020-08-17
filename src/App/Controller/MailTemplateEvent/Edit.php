<?php
namespace App\Controller\MailTemplateEvent;

use Bs\Controller\AdminEditIface;
use Dom\Template;
use Tk\Request;

/**
 * TODO: Add Route to routes.php:
 *      $routes->add('mail-template-event-edit', Route::create('/staff/mail/template/eventEdit.html', 'App\Controller\MailTemplateEvent\Edit::doDefault'));
 *
 * @author Mick Mifsud
 * @created 2020-08-17
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Edit extends AdminEditIface
{

    /**
     * @var \App\Db\MailTemplateEvent
     */
    protected $mailTemplateEvent = null;


    /**
     * Iface constructor.
     */
    public function __construct()
    {
        $this->setPageTitle('Mail Template Event Edit');
    }

    /**
     * @param Request $request
     * @throws \Exception
     */
    public function doDefault(Request $request)
    {
        $this->mailTemplateEvent = new \App\Db\MailTemplateEvent();
        if ($request->get('mailTemplateEventId')) {
            $this->mailTemplateEvent = \App\Db\MailTemplateEventMap::create()->find($request->get('mailTemplateEventId'));
        }

        $this->setForm(\App\Form\MailTemplateEvent::create()->setModel($this->mailTemplateEvent));
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
<div class="tk-panel" data-panel-title="Mail Template Event Edit" data-panel-icon="fa fa-book" var="panel"></div>
HTML;
        return \Dom\Loader::load($xhtml);
    }

}