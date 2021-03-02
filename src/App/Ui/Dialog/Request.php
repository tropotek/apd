<?php
namespace App\Ui\Dialog;

use Tk\Ui\Dialog\JsonForm;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class Request extends JsonForm
{

    public function __construct()
    {
        $request = new \App\Db\Request();
        $request->setPathCaseId($this->getRequest()->get('pathCaseId'));
        $form = \App\Form\Request::create()->setDialog($this)->setModel($request);
        $this->addCss('create-request-dialog');
        parent::__construct($form, 'Create Request');
    }

    /**
     * @return static
     */
    public static function createRequest()
    {
        $obj = new static();
        return $obj;
    }


    /**
     * @return \Dom\Template
     */
    public function show()
    {
        $template = parent::show();

        $js = <<<JS
jQuery(function ($) {
  
  function init() {
    var form = $(this);
    var dialog = form.closest('.modal');
    var fields = dialog.find('input, select, textarea');
    var cassetteId = [];
    
    // Reset form
    dialog.on('shown.bs.modal', function (e) {
      cassetteId = [];
      if ($(e.relatedTarget).data('cassetteId')) {
        var val = $(e.relatedTarget).data('cassetteId')+"";
        if (val.indexOf(',') > -1) {  // Check if this is an array value
          cassetteId = val.split(',');
        } else {
          cassetteId.push(val);
        }
        // Clear any existing hidden fields add the new ones
        form.find('input.tk-cassette-id').remove();
        $.each(cassetteId, function (i, o) {
          form.append('<input type="hidden" class="tk-cassette-id" name="cassetteId[]" value="'+o+'" />');
        });
      }
    });
    
    dialog.on('DialogForm:submit', function (e) {
      var table = $('div.tk-request-list .tk-table');
      if (table.length !== 1) return;
      $.get(document.location, {}, function (html) {
        var doc = $(html);
        table.parent().empty().append(doc.find('div.tk-request-list .tk-table'));
      }, 'html');
      
    });
    
  }
  //$('.modal-body form').on('init', document, init).each(init);
  $('.create-request-dialog form').on('init', 'body', init).each(init);
});
JS;

        $template->appendJs($js);

        return $template;
    }






}