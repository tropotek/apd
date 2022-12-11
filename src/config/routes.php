<?php
/*
 * NOTE: Be sure to add Routes in correct order as the first match will win
 * 
 * Route Structure
 * $Route = Route::create(
 *     '/archive/{month}',              // path
 *     '\Namespace\Class::method',      // Callable or class::method string
 *     array('month' => 'Jan'),         // Params and defaults to path params... all will be sent to the request object.
 *     array('GET', 'POST', 'HEAD')     // methods
 * );
 */

use \Tk\Routing\Route;

$config = \App\Config::getInstance();
$routes = $config->getRouteCollection();
if (!$routes) return;
$routes->remove('install');

// Default Home catchall
$routes->add('home', Route::create('/index.html', 'App\Controller\Index::doDefault'));
$routes->add('home-base', Route::create('/', 'App\Controller\Index::doDefault'));

$routes->add('login', Route::create('/login.html', 'App\Controller\Login::doInsLogin'));
$routes->add('recover', Route::create('/recover.html', 'App\Controller\Recover::doInsRecover'));
$routes->add('institution-login', Route::create('/inst/{instHash}/login.html', 'App\Controller\Login::doInsLogin'));
$routes->add('institution-recover', Route::create('/inst/{instHash}/recover.html', 'App\Controller\Recover::doInsRecover'));
$routes->add('admin-login', Route::create('/xlogin.html', 'App\Controller\Login::doDefault'));

$routes->add('contact', Route::create('/contact.html', 'App\Controller\Contact::doDefault'));
$routes->add('terms', Route::create('/terms.html', 'App\Controller\Terms::doDefault'));
$routes->add('privacy', Route::create('/privacy.html', 'App\Controller\Privacy::doDefault'));


// Admin Pages
$routes->add('admin-dashboard', Route::create('/admin/index.html', 'App\Controller\User\AdminDashboard::doDefault'));
$routes->add('admin-dashboard-base', Route::create('/admin/', 'App\Controller\User\AdminDashboard::doDefault'));
$routes->add('admin-settings', Route::create('/admin/settings.html', 'App\Controller\User\AdminSettings::doDefault'));
$routes->add('admin-institution-edit', Route::create('/admin/institutionEdit.html', 'App\Controller\Institution\Edit::doDefault'));

// Uni user tpoe Client Pages (not pathology clients)
//$Routes->add('client-dashboard', Route::create('/client/index.html', 'App\Controller\Client\Dashboard::doDefault'));
//$Routes->add('client-dashboard-base', Route::create('/client/', 'App\Controller\Client\Dashboard::doDefault'));
$routes->add('client-dashboard', Route::create('/client/index.html', 'App\Controller\User\ClientDashboard::doDefault'));
$routes->add('client-dashboard-base', Route::create('/client/', 'App\Controller\User\ClientDashboard::doDefault'));
$routes->add('client-settings', Route::create('/client/settings.html', 'App\Controller\Institution\Edit::doDefault'));



// Staff Pages
$routes->add('staff-dashboard', Route::create('/staff/index.html', 'App\Controller\Staff\Dashboard::doDefault'));
$routes->add('staff-dashboard-base', Route::create('/staff/', 'App\Controller\Staff\Dashboard::doDefault'));
$routes->add('staff-subject-dashboard', Route::create('/staff/{subjectCode}/index.html', 'App\Controller\Staff\SubjectDashboard::doDefault'));
$routes->add('staff-institution-edit', Route::create('/staff/settings.html', 'App\Controller\Institution\Edit::doDefault'));

$routes->add('staff-roster', Route::create('/staff/roster.html', 'App\Controller\Staff\Roster::doDefault'));
$routes->add('staff-protocols', Route::create('/staff/protocols.html', 'App\Controller\Staff\Protocols::doDefault'));
$routes->add('staff-sampling', Route::create('/staff/sampling.html', 'App\Controller\Staff\Sampling::doDefault'));
$routes->add('staff-specimens', Route::create('/staff/specimens.html', 'App\Controller\Staff\Specimens::doDefault'));

$routes->add('cassette-manager', Route::create('/staff/cassetteManager.html', 'App\Controller\Cassette\Manager::doDefault'));
$routes->add('cassette-edit', Route::create('/staff/cassetteEdit.html', 'App\Controller\Cassette\Edit::doDefault'));

$routes->add('contact-manager', Route::create('/staff/contactManager.html', 'App\Controller\Contact\Manager::doDefault'));
$routes->add('contact-edit', Route::create('/staff/contactEdit.html', 'App\Controller\Contact\Edit::doDefault'));

$routes->add('storage-manager', Route::create('/staff/storageManager.html', 'App\Controller\Storage\Manager::doDefault'));
$routes->add('storage-edit', Route::create('/staff/storageEdit.html', 'App\Controller\Storage\Edit::doDefault'));

$routes->add('service-manager', Route::create('/staff/serviceManager.html', 'App\Controller\Service\Manager::doDefault'));
$routes->add('service-edit', Route::create('/staff/serviceEdit.html', 'App\Controller\Service\Edit::doDefault'));

$routes->add('path-case-manager', Route::create('/staff/pathCaseManager.html', 'App\Controller\PathCase\Manager::doDefault'));
$routes->add('path-case-edit', Route::create('/staff/pathCaseEdit.html', 'App\Controller\PathCase\Edit::doDefault'));

$routes->add('mail-template-manager', Route::create('/staff/mailTemplateManager.html', 'App\Controller\MailTemplate\Manager::doDefault'));
$routes->add('mail-template-edit', Route::create('/staff/mailTemplateEdit.html', 'App\Controller\MailTemplate\Edit::doDefault'));

$routes->add('request-manager', Route::create('/staff/requestManager.html', 'App\Controller\Request\Manager::doDefault'));
$routes->add('request-edit', Route::create('/staff/requestEdit.html', 'App\Controller\Request\Edit::doDefault'));

$routes->add('test-manager', Route::create('/staff/testManager.html', 'App\Controller\Test\Manager::doDefault'));
$routes->add('test-edit', Route::create('/staff/testEdit.html', 'App\Controller\Test\Edit::doDefault'));

$routes->add('animalType-manager', Route::create('/staff/animalTypeManager.html', 'App\Controller\AnimalType\Manager::doDefault'));
$routes->add('animalType-edit', Route::create('/staff/animalTypeEdit.html', 'App\Controller\AnimalType\Edit::doDefault'));

$routes->add('invoiceItem-manager', Route::create('/staff/invoiceItemManager.html', 'App\Controller\InvoiceItem\Manager::doDefault'));
$routes->add('invoiceItem-edit', Route::create('/staff/invoiceItemEdit.html', 'App\Controller\InvoiceItem\Edit::doDefault'));
$routes->add('invoiceItem-report', Route::create('/staff/invoiceItemReport.html', 'App\Controller\InvoiceItem\Report::doDefault'));

$routes->add('product-manager', Route::create('/staff/productManager.html', 'App\Controller\Product\Manager::doDefault'));
$routes->add('product-edit', Route::create('/staff/productEdit.html', 'App\Controller\Product\Edit::doDefault'));




// -------------------------   Ajax Urls -------------------------
//$routes->add('ajax-notice-mark-read', Route::create('/ajax/notice/markRead', 'App\Ajax\Notice::doMarkRead'));
//$routes->add('ajax-notice-mark-viewed', Route::create('/ajax/notice/markViewed', 'App\Ajax\Notice::doMarkViewed'));
//$routes->add('ajax-notice-get-list', Route::create('/ajax/notice/getList', 'App\Ajax\Notice::doGetList'));
$routes->add('ajax-notice-mark-read', Route::create('/ajax/notice/{action}', 'App\Ajax\Notice::doDefault'));
$routes->add('ajax-case-update', Route::create('/ajax/mceAutosave', 'App\Ajax\Mce::doMceAutosave'));

$routes->add('ajax-product-findByName', new \Tk\Routing\Route('/ajax/product/findByName.html', 'App\Ajax\Product::doFindByName'));

