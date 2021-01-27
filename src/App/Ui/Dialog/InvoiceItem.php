<?php
namespace App\Ui\Dialog;

use Tk\Ui\Dialog\JsonForm;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class InvoiceItem extends JsonForm
{

    public function __construct()
    {
        $request = new \App\Db\InvoiceItem();
        $request->setPathCaseId($this->getRequest()->get('pathCaseId'));
        $form = \App\Form\InvoiceItem::create()->setDialog($this)->setModel($request);
        $this->addCss('create-invoice-dialog');
        parent::__construct($form, 'Create Invoice Item');
    }

    /**
     * @return static
     */
    public static function createInvoiceItem()
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
      if ($(e.relatedTarget).data('invoiceItemId')) {
        var val = $(e.relatedTarget).data('invoiceItemId')+"";
        if (val.indexOf(',') > -1) {  // Check if this is an array value
          cassetteId = val.split(',');
        } else {
          cassetteId.push(val);
        }
        // Clear any existing hidden fields add the new ones
        form.find('input.tk-invoiceItem-id').remove();
        $.each(cassetteId, function (i, o) {
          form.append('<input type="hidden" class="tk-invoiceItem-id" name="invoiceItemId[]" value="'+o+'" />');
        });
      }
    });
    
    dialog.on('DialogForm:submit', function (e) {
      var table = $('div.tk-invoice-list .tk-table');
      if (table.length !== 1) return;
      
      $.get(document.location, {}, function (html) {
        var doc = $(html);
        //table.parent().empty().append(doc.find('div.tk-invoice-list .tk-table'));
        var el = doc.find('div.tk-invoice-list .tk-table form > div');
        el.detach(); 
        table.find('form').empty().append(el).trigger('init');
        // Update delete events
        if ($.fn.bsConfirm === undefined) {
          table.find('[data-confirm]').on('click', function () {
            return confirm($('<p>' + $(this).data('confirm') + '</p>').text());
          });
        } else {
          table.find('[data-confirm]').bsConfirm({});
        }
        
        // refresh the totals row at the footer
        table.trigger('invoice::updateTotal');
        
      }, 'html');
      
    });
    
  }
  $('.create-invoice-dialog .modal-body form').on('init', document, init).each(init);
  
});
JS;

        $template->appendJs($js);

        return $template;
    }






}