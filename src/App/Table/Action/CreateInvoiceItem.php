<?php
namespace App\Table\Action;


use Tk\Callback;
use Tk\Ui\Dialog\JsonForm;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class CreateInvoiceItem extends \Tk\Table\Action\Button
{

    /**
     * @var null|JsonForm
     */
    protected $dialog = null;

    /**
     * @param string $name
     * @param string $icon
     */
    public function __construct($name = 'Create Invoice Item', $icon = 'fa fa-money')
    {
        parent::__construct($name, $icon);
        $this->setAttr('type', 'button');
        $this->addCss('tk-action-create-invoiceItem');
    }

    /**
     * @param string $name
     * @param string $icon
     * @return static
     */
    static function create($name = 'Create Invoice Item', $icon = 'fa fa-money')
    {
        return new static($name, $icon);
    }

    /**
     * @return string|\Dom\Template
     */
    public function show()
    {

        if (!$this->hasAttr('title'))
            $this->setAttr('title', 'Create Requests From Selected');

        $this->setAttr('disabled');
        //$this->setAttr('data-cb-name', $this->getCheckboxName());

        $this->setAttr('data-toggle', 'modal');
        $this->setAttr('data-target', '#'.$this->dialog->getId());

        if ($this->getDialog()) {
            $this->getTemplate()->appendBodyTemplate($this->getDialog()->show());
        }
        $template = parent::show();

        $template->appendJs($this->getJs());
        return $template;
    }
    /**
     * @return string
     */
    protected function getJs()
    {
        $js = <<<JS
jQuery(function($) {
  var init = function () {
    
    $('.tk-action-create-invoiceItem').each(function () {
      var btn = $(this);
      
      //var confirmStr = btn.data('cb-confirm');
      
      btn.on('click', function (e) {
        var selected = $(this).closest('.tk-table').find('.table-body input[name^="'+cbName+'"]:checked');
        var selectedArr = [];
        if (selected.length > 0) {
          // Add selected cassettes to button
          selected.each(function () {
            selectedArr.push($(this).val());
          });
          btn.attr('data-cassette-id', selectedArr.join(','));
        }
        
        return selected.length > 0;
      });
      btn.closest('.tk-table').on('change', '.table-body input[name^="'+cbName+'"]', function () { updateBtn(btn); });
      
      updateBtn(btn);
    });
  }
    
  $('form').on('init', document, init).each(init);
});
JS;
        return $js;
    }

    /**
     * @return JsonForm|null
     */
    public function getDialog(): ?JsonForm
    {
        return $this->dialog;
    }

    /**
     * @param JsonForm|null $dialog
     * @return $this
     */
    public function setDialog(?JsonForm $dialog): CreateRequest
    {
        $this->dialog = $dialog;
        return $this;
    }

}
