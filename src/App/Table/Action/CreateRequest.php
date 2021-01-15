<?php
namespace App\Table\Action;


use Tk\Callback;
use Tk\Ui\Dialog\JsonForm;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class CreateRequest extends \Tk\Table\Action\Button
{

    /**
     * @var string
     */
    protected $checkboxName = 'id';

    /**
     * @var null|JsonForm
     */
    protected $requestDialog = null;



    /**
     * @param string $name
     * @param string $checkboxName The checkbox name to get the selected id's from
     * @param string $icon
     */
    public function __construct($name = 'Create Request', $checkboxName = 'id', $icon = 'fa fa-medkit')
    {
        parent::__construct($name, $icon);
        $this->setAttr('type', 'button');
        $this->addCss('tk-action-create-request');
        $this->setCheckboxName($checkboxName);
    }

    /**
     * @param string $name
     * @param string $checkboxName
     * @param string $icon
     * @return static
     */
    static function create($name = 'Create Request', $checkboxName = 'id', $icon = 'fa fa-medkit')
    {
        return new static($name, $checkboxName, $icon);
    }

    /**
     * @return string
     */
    public function getCheckboxName(): string
    {
        return $this->checkboxName;
    }

    /**
     * @param string $checkboxName
     * @return $this
     */
    public function setCheckboxName(string $checkboxName)
    {
        $this->checkboxName = $checkboxName;
        return $this;
    }

    /**
     * @return string|\Dom\Template
     */
    public function show()
    {

        if (!$this->hasAttr('title'))
            $this->setAttr('title', 'Create Requests From Selected');

        $this->setAttr('disabled');
        $this->setAttr('data-cb-name', $this->getCheckboxName());

        $this->setAttr('data-toggle', 'modal');
        $this->setAttr('data-target', '#'.$this->requestDialog->getId());

        if ($this->getRequestDialog()) {
            $this->getTemplate()->appendBodyTemplate($this->getRequestDialog()->show());
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
    function updateBtn(btn) {
      var cbName = btn.data('cb-name');
      if(btn.closest('.tk-table').find('.table-body input[name^="'+cbName+'"]:checked').length) {
        btn.removeAttr('disabled');
      } else {
        btn.attr('disabled', 'disabled');
      }
    }
    
    $('.tk-action-create-request').each(function () {
      var btn = $(this);
      var cbName = btn.data('cb-name');
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
});
JS;
        return $js;
    }

    /**
     * @return JsonForm|null
     */
    public function getRequestDialog(): ?JsonForm
    {
        return $this->requestDialog;
    }

    /**
     * @param JsonForm|null $requestDialog
     * @return CreateRequest
     */
    public function setRequestDialog(?JsonForm $requestDialog): CreateRequest
    {
        $this->requestDialog = $requestDialog;
        return $this;
    }

}
