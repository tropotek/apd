<?php
namespace App\Controller;

use Bs\Uri;
use Tk\Alert;
use Tk\Request;
use Tk\Form;
use Tk\Form\Field;
use Tk\Form\Event;


/**
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @link http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Activate extends \Uni\Controller\Activate
{


    /**
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-login-panel tk-login">
  <p>Please create a new password to access your account.</p>
  <p><small>Passwords must be longer than 8 characters and include one number, one uppercase letter and one symbol.</small></p>
  <div var="form"></div>

</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }
}