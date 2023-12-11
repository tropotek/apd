<?php
namespace App\Controller\User;



use Tk\Form\Field\Html;

class Edit extends \Uni\Controller\User\Edit
{

    public function initForm(\Tk\Request $request)
    {
        parent::initForm($request);

        $html = <<<HTML
            <p><i>
                A user with no permissions will be able to create a Case and only edit cases they 
                have created.<br/> They can view existing Cases but cannot send reports for cases. 
            </i></p>
        HTML;

        $this->getForm()->prependField(Html::createHtml('defaultPermission', $html), 'permission')
            ->setTabGroup('Permissions');

    }

    public function initActionPanel()
    {
        parent::initActionPanel();
    }

    public function show()
    {
        $template = parent::show();

        return $template;
    }

}