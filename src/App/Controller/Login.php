<?php
namespace App\Controller;

use Tk\Db\Tool;
use Tk\Form\Field;
use Tk\Form\Event;
use Uni\Db\InstitutionMap;
use Uni\Uri;


/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Login extends \Uni\Controller\Login
{

//    public function doInsLogin(\Tk\Request $request, $instHash = '')
//    {
//        $this->getSession()->remove('auth.institutionId');
//
//        $this->institution = $this->getConfig()->getInstitutionMapper()->findByHash($instHash);
//        if (!$this->institution && $request->attributes->has('institutionId')) {
//            $this->institution = $this->getConfig()->getInstitutionMapper()->find($request->attributes->get('institutionId'));
//        }
//        // get institution by hostname
//        if (!$this->institution || !$this->institution->active ) {
//            $this->institution = $this->getConfig()->getInstitutionMapper()->findByDomain($request->getTkUri()->getHost());
//        }
//
//        if (!$this->institution || !$this->institution->active ) {
//            \Uni\Uri::create('/xlogin.html')->redirect();
//        }
//
//        if ($this->getAuthUser()) {
//            Uri::createHomeUrl('/index.html')->redirect();
//        }
//
//        $this->doDefault($request);
//    }

    /**
     * @throws \Exception
     */
    protected function init()
    {
        parent::init();

        $this->form->removeField('username');
        $this->form->removeField('password');

        /** @var \Tk\Form\Field\InputGroup $f */
        $f = $this->form->appendField(Field\InputGroup::create('username'))->setRequired()->setLabel(null)->setAttr('placeholder', 'Username');
        $f->prepend('<span class="input-group-text input-group-addon"><i class="fa fa-user mx-auto"></i></span>');

        $f = $this->form->appendField(Field\InputGroup::create('password'))->setRequired()->setType('password')->setLabel(null)->setAttr('placeholder', 'Password');
        $f->prepend('<span class="input-group-text input-group-addon"><i class="fa fa-key mx-auto"></i></span>');

        $this->form->getField('login')->addCss('col-12');
        $f = $this->form->getField('recoverPassword');
        if ($f) {
            $f->addCss('');
        }

        // If this is an institution login page
        if ($this->getConfig()->getRequest()->getTkUri()->basename() == 'login.html') {
            $list = InstitutionMap::create()->findActive(Tool::create('', 2));
            if ($list->count() > 1) {
                $this->form->appendField(new Event\Link('selectInstitution', \Tk\Uri::create('/')->setFragment('institutions'), ''))
                    ->removeCss('btn btn-sm btn-default btn-once')->addCss('tk-institutions-url');
            }
        }

    }

}