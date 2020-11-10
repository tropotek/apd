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
$Routes = $config->getRouteCollection();
if (!$Routes) return;


// Default Home catchall
$Routes->add('home', Route::create('/index.html', 'App\Controller\Index::doDefault'));
$Routes->add('home-base', Route::create('/', 'App\Controller\Index::doDefault'));

$Routes->add('login', Route::create('/login.html', 'App\Controller\Login::doInsLogin'));
$Routes->add('institution-login', Route::create('/inst/{instHash}/login.html', 'App\Controller\Login::doInsLogin'));
$Routes->add('admin-login', Route::create('/xlogin.html', 'App\Controller\Login::doDefault'));

$Routes->add('recover', Route::create('/recover.html', 'App\Controller\Recover::doDefault'));
$Routes->add('install', Route::create('/install.html', 'App\Controller\Install::doDefault'));

// Admin Pages
$Routes->add('admin-dashboard', Route::create('/admin/index.html', 'App\Controller\User\AdminDashboard::doDefault'));
$Routes->add('admin-dashboard-base', Route::create('/admin/', 'App\Controller\User\AdminDashboard::doDefault'));
$Routes->add('admin-settings', Route::create('/admin/settings.html', 'App\Controller\User\AdminSettings::doDefault'));
$Routes->add('admin-institution-edit', Route::create('/admin/institutionEdit.html', 'App\Controller\Institution\Edit::doDefault'));

// Uni user tpoe Client Pages (not pathology clients)
//$Routes->add('client-dashboard', Route::create('/client/index.html', 'App\Controller\Client\Dashboard::doDefault'));
//$Routes->add('client-dashboard-base', Route::create('/client/', 'App\Controller\Client\Dashboard::doDefault'));
$Routes->add('client-dashboard', Route::create('/client/index.html', 'App\Controller\User\ClientDashboard::doDefault'));
$Routes->add('client-dashboard-base', Route::create('/client/', 'App\Controller\User\ClientDashboard::doDefault'));
$Routes->add('client-settings', Route::create('/client/settings.html', 'App\Controller\Institution\Edit::doDefault'));



// Staff Pages
$Routes->add('staff-dashboard', Route::create('/staff/index.html', 'App\Controller\Staff\Dashboard::doDefault'));
$Routes->add('staff-dashboard-base', Route::create('/staff/', 'App\Controller\Staff\Dashboard::doDefault'));
$Routes->add('staff-subject-dashboard', Route::create('/staff/{subjectCode}/index.html', 'App\Controller\Staff\SubjectDashboard::doDefault'));
$Routes->add('staff-institution-edit', Route::create('/staff/settings.html', 'App\Controller\Institution\Edit::doDefault'));



$Routes->add('cassette-manager', Route::create('/staff/cassetteManager.html', 'App\Controller\Cassette\Manager::doDefault'));
$Routes->add('cassette-edit', Route::create('/staff/cassetteEdit.html', 'App\Controller\Cassette\Edit::doDefault'));

$Routes->add('contact-manager', Route::create('/staff/contactManager.html', 'App\Controller\Contact\Manager::doDefault'));
$Routes->add('contact-edit', Route::create('/staff/contactEdit.html', 'App\Controller\Contact\Edit::doDefault'));

$Routes->add('storage-manager', Route::create('/staff/storageManager.html', 'App\Controller\Storage\Manager::doDefault'));
$Routes->add('storage-edit', Route::create('/staff/storageEdit.html', 'App\Controller\Storage\Edit::doDefault'));

$Routes->add('service-manager', Route::create('/staff/serviceManager.html', 'App\Controller\Service\Manager::doDefault'));
$Routes->add('service-edit', Route::create('/staff/serviceEdit.html', 'App\Controller\Service\Edit::doDefault'));

$Routes->add('path-case-manager', Route::create('/staff/pathCaseManager.html', 'App\Controller\PathCase\Manager::doDefault'));
$Routes->add('path-case-edit', Route::create('/staff/pathCaseEdit.html', 'App\Controller\PathCase\Edit::doDefault'));

$Routes->add('mail-template-manager', Route::create('/staff/mailTemplateManager.html', 'App\Controller\MailTemplate\Manager::doDefault'));
$Routes->add('mail-template-edit', Route::create('/staff/mailTemplateEdit.html', 'App\Controller\MailTemplate\Edit::doDefault'));

$Routes->add('request-manager', Route::create('/staff/requestManager.html', 'App\Controller\Request\Manager::doDefault'));
$Routes->add('request-edit', Route::create('/staff/requestEdit.html', 'App\Controller\Request\Edit::doDefault'));



