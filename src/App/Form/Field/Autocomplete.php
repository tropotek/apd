<?php
namespace App\Form\Field;


/**
 * @author Tropotek <info@tropotek.com>
 * @created: 29/07/18
 * @link http://www.tropotek.com/
 * @license Copyright 2018 Tropotek
 */
class Autocomplete extends \Tk\Form\Field\Input
{



    /**
     * @param string $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->addCss('autocomplete');
        //$this->setAttr('autocomplete', 'off');
    }

    /**
     * @param $name
     * @param null|string|\Tk\Uri $lookupUrl
     * @return static
     */
    public static function createAutocomplete($name, $lookupUrl = null)
    {
        $obj = new static($name);
        if ($lookupUrl)
            $obj->setAttr('data-src', \Tk\Uri::create($lookupUrl));
        return $obj;
    }

    /**
     * Get the element HTML
     *
     * @return string|\Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $js = <<<JS
jQuery(function ($) {
  
  function init() {
    var form = $(this);
    $('input.autocomplete', form).each(function () {
      var el = $(this);
      var hidden = $('<input type="hidden" />');
      el.parent().append(hidden);
      hidden.attr('name', el.attr('name'));
      el.attr('name', 'ac-' + el.attr('name'));

      if (el.val() > 0 && el.val() !== '') {
          $.get(el.data('src'), {id : el.val()}, function (data) {
            if (data[0]) {
              el.val(data[0].label);
              hidden.val(data[0].id);
            }
          });
      }
      
      el.autocomplete({
        source: el.data('src'),
        minLength: 0,
        appendTo: el.parent(),
        select: function( event, ui ) {
          if (el.data('valueType') !== 'label') {
            hidden.val(ui.item.id);
            el.val(ui.item.label);
            return false; // Prevent the widget from inserting the value.
          }
          hidden.val(ui.item.label);
          el.val(ui.item.label);
        }
      }).autocomplete('instance')._renderItem = function(ul, item) {
        return $('<li>')
          .append(item.label)
          .appendTo(ul);
      };
      el.focus(function(){     
        $(this).trigger(jQuery.Event("keydown"));
      });
      
    });
  }
  
  $('form').on('init', document, init).each(init);
  
});
JS;
        $template->appendJs($js);


        return $template;
    }


}