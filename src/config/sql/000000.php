<?php
/**
 * @version 3.0
 *
 * @author: Michael Mifsud <info@tropotek.com>
 */

$config = \Uni\Config::getInstance();
try {
    $data = \Tk\Db\Data::create();
    if (!$data->get('site.title')) {
        $data->set('site.title', '');
        $data->set('site.short.title', 'APD');
    }
    if (!$data->get('site.email'))
        $data->set('site.email', 'anat-vet@unimelb.edu.au');

    if (!$data->get('site.meta.keywords'))
        $data->set('site.meta.keywords', '');
    if (!$data->get('site.meta.description'))
        $data->set('site.meta.description', '');
    if (!$data->get('site.global.js'))
        $data->set('site.global.js', '');
    if (!$data->get('site.global.css'))
        $data->set('site.global.css', '');


    $data->save();

} catch (\Exception $e) {}





