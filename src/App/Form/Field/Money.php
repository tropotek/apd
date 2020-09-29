<?php
namespace App\Form\Field;

use Tk\Form\Exception;

/**
 * @author Tropotek <info@tropotek.com>
 * @created: 29/07/18
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Tropotek
 */
class Money extends \Tk\Form\Field\Input
{


    /**
     * Get the element HTML
     *
     * @return string|\Dom\Template
     */
    public function show()
    {
        $t = parent::show();

        return $t;
    }


    /**
     * makeTemplate
     *
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="tk-money">
  <div class="input-group">
    <div class="input-group-prepend">
      <span class="input-group-text" var="currency-symbol">$</span>
    </div>
    <input type="text" var="element" class="form-control" />
  </div>
</div>
HTML;
        
        return \Dom\Loader::load($xhtml);
    }
}