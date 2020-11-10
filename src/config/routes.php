<?php
/*
 * NOTE: Be sure to add routes in correct order as the first match will win
 * 
 * Route Structure
 * $route = route::create(
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


// Default Home catchall
$routes->add('home', route::create('/index.html', 'App\Controller\Index::doDefault'));
$routes->add('home-base', route::create('/', 'App\Controller\Index::doDefault'));

$routes->add('login', route::create('/login.html', 'App\Controller\Login::doDefault'));
$routes->add('institution-login', route::create('/inst/{instHash}/login.html', 'App\Controller\Login::doInsLogin'));
$routes->add('recover', route::create('/recover.html', 'App\Controller\Recover::doDefault'));

$routes->add('install', Route::create('/install.html', 'App\Controller\Install::doDefault'));

// Admin Pages
$routes->add('admin-dashboard', route::create('/admin/index.html', 'App\Controller\User\AdminDashboard::doDefault'));
$routes->add('admin-dashboard-base', route::create('/admin/', 'App\Controller\User\AdminDashboard::doDefault'));
$routes->add('admin-settings', route::create('/admin/settings.html', 'App\Controller\User\AdminSettings::doDefault'));
$routes->add('admin-institution-edit', Route::create('/admin/institutionEdit.html', 'App\Controller\Institution\Edit::doDefault'));

// Uni user tpoe Client Pages (not pathology clients)
//$routes->add('client-dashboard', route::create('/client/index.html', 'App\Controller\Client\Dashboard::doDefault'));
//$routes->add('client-dashboard-base', route::create('/client/', 'App\Controller\Client\Dashboard::doDefault'));
$routes->add('client-dashboard', route::create('/client/index.html', 'App\Controller\User\ClientDashboard::doDefault'));
$routes->add('client-dashboard-base', route::create('/client/', 'App\Controller\User\ClientDashboard::doDefault'));
$routes->add('client-settings', Route::create('/client/settings.html', 'App\Controller\Institution\Edit::doDefault'));



// Staff Pages
$routes->add('staff-dashboard', route::create('/staff/index.html', 'App\Controller\Staff\Dashboard::doDefault'));
$routes->add('staff-dashboard-base', route::create('/staff/', 'App\Controller\Staff\Dashboard::doDefault'));
$routes->add('staff-subject-dashboard', route::create('/staff/{subjectCode}/index.html', 'App\Controller\Staff\SubjectDashboard::doDefault'));
$routes->add('staff-institution-edit', Route::create('/staff/settings.html', 'App\Controller\Institution\Edit::doDefault'));



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



