<?php
namespace App\Table\Action;

use App\Db\Company;
use App\Db\CompanyMap;
use Dom\Template;
use Exception;
use Tk\Db\Tool;
use Tk\Form\Field\Select;
use Tk\Table\Action\Button;
use Tk\Ui\Dialog\Dialog;

class MoveCompany extends Button
{

    /**
     * @var string
     */
    protected $checkboxName = 'id';

    /**
     * @var Select|null
     */
    public $companySelect = null;

    /**
     * @var Dialog|null
     */
    public $dialog = null;

    /**
     * @var null|\Tk\Ui\Button
     */
    public $actionBtn = null;

    public $deleteAfterMove = false;




    /**
     * @param array|string[] $statusList
     * @param string $name
     * @param string $icon
     */
    public function __construct($name = 'move', $icon = 'fa fa-files-o')
    {
        parent::__construct($name, $icon);
        $this->setLabel('Move Cases');
        $this->addCss('no-loader');
        $this->addCss('tk-action-move');

        $t = "";
        $this->dialog = Dialog::create('Move Cases to Company');
        $this->actionBtn = $this->dialog->getButtonList()->append(\Tk\Ui\Button::createButton('Move')->addCss('btn-success'));

        $this->setAttr('type', 'button');
        $this->setAttr('data-toggle', 'modal');
        $this->setAttr('data-target', '#'.$this->dialog->getId());

    }

    public function isDeleteAfterMove(): bool
    {
        return $this->deleteAfterMove;
    }

    public function setDeleteAfterMove(bool $deleteAfterMove): MoveCompany
    {
        $this->deleteAfterMove = $deleteAfterMove;
        return $this;
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
     * @return Status
     */
    public function setCheckboxName(string $checkboxName): MoveCompany
    {
        $this->checkboxName = $checkboxName;
        return $this;
    }

    /**
     * @return mixed|void
     * @throws Exception
     */
    public function execute()
    {
        $request = $this->getConfig()->getRequest();
        $btnId = $this->getTable()->makeInstanceKey($this->getName());

        if ($request->get($btnId) == $btnId) {

            $list = $this->getTable()->getList();
            $selected = $request->get($this->getCheckboxName());
            $destCompanyId = (int)$request->get($btnId.'-companyId');
            /** @var Company $destCompany */
            $destCompany = CompanyMap::create()->find($destCompanyId);
            if (!$destCompany) {
                \Tk\Alert::addWarning('Please select a valid company.');
                $request->getTkUri()->redirect();
            }


            if (!is_array($selected)) {
                \Tk\Alert::addWarning('Please select records to update.');
                $request->getTkUri()->redirect();
            }

            /* @var \Bs\Db\Traits\StatusTrait|\Tk\Db\ModelInterface $obj */
            foreach($list as $obj) {
                $objValue = 0;
                if (property_exists($obj, $this->getCheckboxName())) {
                    $objValue = $obj->{$this->getCheckboxName()};
                }

                // Update obj status
                if (in_array($objValue, $selected)) {
                    if (!$this->isDeleteAfterMove()) {
                        \Tk\Log::notice('Moving selected company [id '.$obj->getId().'] cases to '. $destCompany->getName() . ' [id '.$obj->getId().']');
                    } else {
                        \Tk\Log::notice('deleting selected company [id '.$obj->getId().'] and moving cases to '. $destCompany->getName() . ' [id '.$obj->getId().']');
                    }
                    CompanyMap::create()->moveCases($obj->getId(), $destCompanyId);
                    if ($this->isDeleteAfterMove() && $obj->getId() != $destCompanyId) {
                        $obj->delete();
                    }
                }
            }

            \Tk\Alert::addSuccess('Moved selected company cases to '. $destCompany->getName());
            $request->getTkUri()->redirect();
        }

        parent::execute();
    }

    /**
     * @return string|Template
     */
    public function show()
    {
        $btnId = $this->getTable()->makeInstanceKey($this->getName());
        $this->setAttr('title', 'Move Cases');
        $this->actionBtn->setAttr('id', $btnId);
        $this->actionBtn->setAttr('name', $btnId);
        $this->actionBtn->setAttr('value', $btnId);

        $this->setAttr('disabled');
        $this->setAttr('data-cb-name', $this->getCheckboxName());
        $this->setAttr('data-dialog-id', '#' . $this->dialog->getId());

        $institutionId = $this->getConfig()->getInstitutionId();
        $list = CompanyMap::create()->findFiltered([
            'institutionId' => $institutionId
        ], Tool::create('name'));
        $this->companySelect = Select::createSelect($btnId . '-companyId', $list)
            ->setNotes('Select the company to move the cases to.')
            ->addCss('company-select')
            ->prependOption('-- Status --', '');
        $this->dialog->setContent($this->companySelect->show());

        $this->dialog->getTemplate()->prependHtml('content',
            '<p class="text-danger"><small><b>Note:</b> This action will clear the Company Contact field for the moved Cases.</small></p>');

        if ($this->isDeleteAfterMove()) {
            $this->dialog->setTitle('Delete selected and move Cases to Company');
        }



        $template = parent::show();

        if ($this->dialog) {
            $template->appendTemplate('btnWrapper', $this->dialog->show());
        }

        $js = <<<JS
jQuery(function ($) {
  var init = function () {
    var form = $(this);
    var table = form.closest('.tk-table');
    
    function updateBtn(btn) {
      var cbName = btn.data('cb-name');
      if ($('.table-body input[name^="' + cbName + '"]:checked', table).length) {
        btn.removeAttr('disabled');
      } else {
        btn.attr('disabled', 'disabled');
      }
    }
    
    $('.tk-action-move', form).each(function () {
      var btn = $(this);
      var cbName = btn.data('cb-name');
      var dialog = $(btn.data('dialogId'));
      var companySelect = $('.company-select', dialog);
  
      table.on('change', '.table-body input[name^="'+cbName+'"]', function () { updateBtn(btn); });
      updateBtn(btn);
            
      dialog.on('show.bs.modal', function() {
          companySelect.val('');
      });
      
      //form.on('submit', function (e) {
      $('.btn-success', dialog).on('click', function (e) {
        // Check if this submit was the actual move button
        if (companySelect.val() === '') {
          alert('Please select a company!');
          return false;
        }
        return true;
      });
      
    });
  }
  //$('.tk-table form').on('init', document, init).each(init);
  $('.tk-table form').each(init);

});
JS;
        $template->appendJs($js);

        return $template;
    }
    /**
     * @return \Dom\Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<XHTML
            <span var="btnWrapper">
                <button class="" var="btn"><i var="icon" choice="icon"></i> <span var="btnTitle"></span></button>
            </span>
        XHTML;
        return \Dom\Loader::load($xhtml);
    }

}
