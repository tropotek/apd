<?php
namespace App\Listener;

use Tk\ConfigTrait;
use Tk\Util\IpThrottle;
use Uni\Db\User;
use Uni\Uri;

/**
 * This object helps cleanup the structure of the controller code
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class PageTemplateHandler extends \Uni\Listener\PageTemplateHandler
{
    use ConfigTrait;

    /**
     * @param \Tk\Event\Event $event
     * @throws \Exception
     */
    public function showPage(\Tk\Event\Event $event)
    {
        $controller = $event->get('controller');
        if (!$controller) return;

        /** @var \Uni\Page $page */
        $page = $controller->getPage();
        $template = $page->getTemplate();

        parent::showPage($event);

        $controller = $event->get('controller');
        if ($controller instanceof \Bs\Controller\Iface) {
            $page = $controller->getPage();
            if (!$page) return;
            $template = $page->getTemplate();
            /** @var \Uni\Db\User $user */
            $user = $controller->getAuthUser();

            if ($user) {
                // About dialog
                $dialog = new \Bs\Ui\AboutDialog();
                $template->appendTemplate($template->getBodyElement(), $dialog->show());

                // Logout dialog
                $dialog = new \Bs\Ui\LogoutDialog();
                $template->appendTemplate($template->getBodyElement(), $dialog->show());

                // Set permission choices
                $perms = $user->getPermissions();
                foreach ($perms as $perm) {
                    $template->setVisible($perm);
                    $controller->getTemplate()->setVisible($perm);
                }
                $template->setVisible($user->getType());
                $controller->getTemplate()->setVisible($user->getType());

                //show user icon 'user-image'
                $img = $user->getImageUrl();
                if ($img) {
                    $template->setAttr('user-image', 'src', $img);
                }

                // Show create new item menu
                if ($user->isStaff()) {
                    $template->setVisible('isStaff');
                }
                $hash = $user->getHash();
                $js = <<<JS
config.userHash = '$hash';
JS;
                $template->appendJs($js, array('data-jsl-priority' => -1000));
            }

            if ($this->getConfig()->getInstitution()) {
                $template->insertText('login-title', $this->getConfig()->getInstitution()->getName());
                $template->setVisible('has-inst');
            } else {
                $template->insertText('login-title', $this->getConfig()->get('site.title'));
                $template->setVisible('no-inst');
            }


            // Add anything to the page template here ...
            //$url = \Bs\Uri::create('/html/app/img/unimelb-logo-lge.png');
            //$template->appendHtml('nav-footer', sprintf('<a href="https://fvas.unimelb.edu.au/" target="_blank" title="Visit FVAS"><img src="%s" class="img-fluid" alt="Logo" /></a>', $url));

        }
    }

}