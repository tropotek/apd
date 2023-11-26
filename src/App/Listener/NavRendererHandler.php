<?php
namespace App\Listener;

use App\Db\Permission;
use Tk\ConfigTrait;
use Tk\Event\Subscriber;
use Symfony\Component\HttpKernel\KernelEvents;
use Tk\Ml\Db\MailLog;
use Tk\Ui\Menu\Item;
use Bs\Ui\Menu;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Michael Mifsud
 */
class NavRendererHandler implements Subscriber
{
    use ConfigTrait;

    /**
     * @return string
     */
    public function getRoleType()
    {
        $t = 'public';
        if ($this->getConfig()->getAuthUser())
            $t = $this->getConfig()->getAuthUser()->getType();
        return $t;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     * @throws \Exception
     */
    public function onRequest($event)
    {
        $config = $this->getConfig();

        $dropdownMenu = $config->getMenuManager()->getMenu('nav-dropdown');
        $sideMenu = $config->getMenuManager()->getMenu('nav-side');

        $dropdownMenu->setAttr('style', 'visibility:hidden;');
        $sideMenu->setAttr('style', 'visibility:hidden;');

        $this->initDropdownMenu($dropdownMenu);
        $this->initSideMenu($sideMenu);
    }

    /**
     * @param Menu $menu
     */
    protected function initDropdownMenu($menu)
    {
        $user = $this->getConfig()->getAuthUser();
        if (!$user) return;

        $menu->append(Item::create('My Profile', \Uni\Uri::createHomeUrl('/profile.html'), 'fa fa-user'));
        $menu->append(Item::create()->addCss('divider'));

        if ($user->isAdmin()) {
            $menu->prepend(Item::create('Site Preview', \Uni\Uri::create('/index.html'), 'fa fa-home'))->getLink()
                ->setAttr('target', '_blank');
        }
        if ($user->hasPermission(\Uni\Db\Permission::MANAGE_STAFF)) {
            $menu->append(Item::create('Staff', \Uni\Uri::createHomeUrl('/staffUserManager.html'), 'fa fa-user-md'));

            $menu->append(Item::create('Storage Locations', \Uni\Uri::createHomeUrl('/storageManager.html'), 'fa fa-archive'));
            $menu->append(Item::create('Available Services', \Uni\Uri::createHomeUrl('/serviceManager.html'), 'fa fa-tags'));
            $menu->append(Item::create('Contact', \Uni\Uri::createHomeUrl('/contactManager.html'), 'fa fa-user-o'));
        }
        if ($user->hasPermission(Permission::MANAGE_SITE))
            $menu->append(Item::create('Settings', \Uni\Uri::createHomeUrl('/settings.html'), 'fa fa-cogs'), 'Staff');

        $menu->append(Item::create()->addCss('divider'));
        $menu->append(Item::create('About', '#', 'fa fa-info-circle')
            ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#aboutModal'));
        $menu->append(Item::create('Logout', '#', 'fa fa-sign-out')
            ->setAttr('data-toggle', 'modal')->setAttr('data-target', '#logoutModal'));
    }

    /**
     * @param Menu $menu
     * @throws \Exception
     */
    protected function initSideMenu($menu)
    {
        $user = $this->getConfig()->getAuthUser();
        if (!$user) return;

        $menu->append(Item::create('Dashboard', \Uni\Uri::createHomeUrl('/index.html'), 'fa fa-dashboard'));

        if ($user->isAdmin()) {
            $menu->append(Item::create('Settings', \Uni\Uri::createHomeUrl('/settings.html'), 'fa fa-cogs'));
            if ($this->getConfig()->isDebug()) {
                $sub = $menu->append(Item::create('Development', '#', 'fa fa-bug'));
                // <a href="/admin/tailLog.html"><i class="fa fa-road"></i> Tail Log</a>
                $sub->append(Item::create('Tail Log', \Uni\Uri::createHomeUrl('/dev/tailLog.html'), 'fa fa-road'));
                $sub->append(Item::create('Events', \Uni\Uri::createHomeUrl('/dev/dispatcherEvents.html'), 'fa fa-empire'));
                $sub->append(Item::create('Forms', \Uni\Uri::createHomeUrl('/dev/forms.html'), 'fa fa-rebel'));
            }
        }
        if ($user->isClient()) {
            $menu->append(Item::create('Settings', \Uni\Uri::createHomeUrl('/settings.html'), 'fa fa-cogs'));
            $menu->append(Item::create('Staff', \Uni\Uri::createHomeUrl('/staffUserManager.html'), 'fa fa-users'));
            $menu->append(Item::create('Mail Log', \Uni\Uri::createHomeUrl(MailLog::createMailLogUrl('/manager.html', $this->getAuthUser()->getInstitution())), 'fa fa-envelope'));
        }
        if ($user->isStaff()) {
            if ($user->hasPermission(Permission::MANAGE_SITE))
                $menu->append(Item::create('Settings', \Uni\Uri::createHomeUrl('/settings.html'), 'fa fa-cogs'));

            $menu->append(Item::create('Cases', \Uni\Uri::createHomeUrl('/pathCaseManager.html'), 'fa fa-paw'));
            $menu->append(Item::create('Requests', \Uni\Uri::createHomeUrl('/requestManager.html'), 'fa fa-medkit'));

            $menu->append(Item::create('Contacts', \Uni\Uri::createHomeUrl('/contactManager.html'), 'fa fa-user'));

            $menu->append(Item::create('Clients', \Uni\Uri::createHomeUrl('/clientManager.html'), 'fa fa-user'));
            $menu->append(Item::create('Students', \Uni\Uri::createHomeUrl('/studentManager.html'), 'fa fa-user-o'));
            $menu->append(Item::create('Roster', \Uni\Uri::createHomeUrl('/roster.html'), 'fa fa-calendar'));
            $menu->append(Item::create('Sampling Protocols', \Uni\Uri::createHomeUrl('/protocols.html'), 'fa fa-flask'));
            $menu->append(Item::create('Tissue Sample Requests', \Uni\Uri::createHomeUrl('/sampling.html'), 'fa fa-heartbeat'));
            $menu->append(Item::create('Stored Specimens', \Uni\Uri::createHomeUrl('/specimens.html'), 'fa fa-archive'));
            //$menu->append(Item::create('Storage List', \Uni\Uri::createHomeUrl('/storageManager.html'), 'fa fa-question'));
            //$menu->append(Item::create('Service List', \Uni\Uri::createHomeUrl('/serviceManager.html'), 'fa fa-question'));
            //$menu->append(Item::create('Request List', \Uni\Uri::createHomeUrl('/requestManager.html'), 'fa fa-flask'));\
            //$menu->append(Item::create('Cassette List', \Uni\Uri::createHomeUrl('/cassetteManager.html'), 'fa fa-question'));
            //$menu->append(Item::create('Mail Log', \Uni\Uri::createHomeUrl(MailLog::createMailLogUrl('/manager.html', $this->getConfig()->getInstitution())), 'fa fa-envelope'));


            if ($user->hasPermission(Permission::MANAGE_SITE)) {
                $sub = $menu->append(Item::create('Reporting', '#', 'fa fa-list-alt'));
                $sub->append(Item::create('All Invoiced Items', \Uni\Uri::createHomeUrl('/invoiceItemReport.html'), 'fa fa-money'));
            }

        }

    }


    /**
     * @param \Tk\Event\Event $event
     */
    public function onShow(\Tk\Event\Event $event)
    {
        $controller = $event->get('controller');
        if ($controller instanceof \Bs\Controller\Iface) {
            /** @var \Uni\Page $page */
            $page = $controller->getPage();
            $template = $page->getTemplate();

            foreach ($this->getConfig()->getMenuManager()->getMenuList() as $menu) {
                $renderer = \Tk\Ui\Menu\ListRenderer::create($menu);
                $tpl = $renderer->show();
                $template->replaceTemplate($menu->getName(), $tpl);
            }
        }
    }


    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST =>  array('onRequest', 0),
            \Tk\PageEvents::PAGE_SHOW =>  array('onShow', 0)
        );
    }

}