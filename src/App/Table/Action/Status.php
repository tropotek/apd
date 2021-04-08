<?php
namespace App\Table\Action;


use Tk\Db\Tool;
use \Uni\Form\Field\StatusSelect;
use Dom\Template;
use Exception;
use Tk\Log;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class Status extends \Tk\Table\Action\Link
{

    /**
     * @var string
     */
    protected $checkboxName = 'id';

    /**
     * @var StatusSelect|null
     */
    public $field = null;

    /**
     * @var \Tk\Ui\Dialog\Dialog|null
     */
    public $dialog = null;
    /**
     * @var null|\Tk\Ui\Button
     */
    public $updateBtn = null;

    /**
     * @var array|string[]
     */
    public $statusList = array();

    /**
     * @param array|string[] $statusList
     * @param string $name
     * @param null $url
     * @param string $icon
     * @param string $checkboxName
     */
    public function __construct($statusList, $name = 'status', $url = null, $icon = 'fa fa-code-fork', $checkboxName = 'id')
    {
        parent::__construct($name, $url, $icon);
        $this->setLabel('Change Status');
        $this->setStatusList($statusList);
        $this->setCheckboxName($checkboxName);
        $this->addCss('no-loader');
        $this->addCss('tk-action-status');

        $this->dialog = \Tk\Ui\Dialog\Dialog::create('Batch: Change Record Status');
        $this->updateBtn = $this->dialog->getButtonList()->append(\Tk\Ui\Button::createButton('Update')->addCss('btn-success'));

        $this->setAttr('data-toggle', 'modal');
        $this->setAttr('data-target', '#'.$this->dialog->getId());

    }

    /**
     * @param array|string[] $statusList
     * @param string $name
     * @param null $url
     * @param string $icon
     * @param string $checkboxName
     * @return Status
     */
    static function create($statusList, $name = 'status', $url = null, $icon = 'fa fa-code-fork', $checkboxName = 'id')
    {
        return new self($statusList, $name, $url, $icon, $checkboxName);
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
    public function setCheckboxName(string $checkboxName): Status
    {
        $this->checkboxName = $checkboxName;
        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getStatusList()
    {
        return $this->statusList;
    }

    /**
     * @param array|string[] $statusList
     * @return Status
     */
    public function setStatusList($statusList)
    {
        $this->statusList = $statusList;
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
            $status = $request->get($btnId.'-status').'';
            $notify = $request->get('_'.$btnId.'-statusNotify').'';
            $notes = $request->get('_'.$btnId.'-statusMessage').'';
            if (!$status) {
                \Tk\Alert::addWarning('Nothing updated please select a status first.');
                $request->getTkUri()->redirect();
            }
            $selected = $request->get($this->getCheckboxName());
            $list = $this->getTable()->getList();
            if (!is_array($selected)) {
                if ($request->has('pathCaseId')) {
                    $vars = $this->getTable()->getFilterValues();
                    $vars['pathCaseId'] = $request->get('pathCaseId');
                    $list = $this->getTable()->getList()->getMapper()->findFiltered($vars, Tool::create());
                } else {
                    \Tk\Alert::addWarning('Please select records to update.');
                    return;
                }
            }

            $updated = 0;
            /* @var \Bs\Db\Traits\StatusTrait|\Tk\Db\ModelInterface $obj */
            foreach($list as $obj) {
                if (!is_object($obj)) continue;
                $keyValue = 0;
                if (property_exists($obj, $this->getCheckboxName())) {
                    $keyValue = $obj->{$this->getCheckboxName()};
                }
                // Update obj status
                if ((!is_array($selected) && $request->has('pathCaseId')) || in_array($keyValue, $selected) ) {
                    \Tk\Log::notice('Bulk Record Status Change: [cs:'.$obj->getStatus(). '] [ns:'.$status . '] [pid:'.$obj->getId().']');
                    $obj->setStatus($status);
                    $obj->setStatusNotify($notify);
                    $obj->setStatusMessage($notes);
                    $obj->save();
                    $updated++;
                }
            }

            \Tk\Alert::addSuccess('Status changed to ' . $status . ' for ' . $updated . ' records');
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
        $this->updateBtn->setAttr('id', $btnId);
        $this->updateBtn->setAttr('name', $btnId);
        $this->updateBtn->setAttr('value', $btnId);

        $this->setAttr('data-cb-name', $this->getCheckboxName());
        $this->setAttr('data-select', '[name='.$btnId.'-status]');
        $this->setAttr('disabled');

        $this->field = \Bs\Form\Field\StatusSelect::createSelect($btnId . '-status', $this->getStatusList())
            ->setNotes('Set the status. Use the checkbox to disable notification emails.')
            ->prependOption('-- Status --', '');

        $this->setAttr('title', 'Batch change record status.');
        $this->dialog->setContent($this->field->show());
        $this->dialog->getTemplate()->prependHtml('content',
            '<p class="text-center text-danger"><b>WARNING:</b><br/>This will update all selected records to the new status.<br/>Be sure to check your selections as this cannot be undone!</p>');

        $template = parent::show();

        if ($this->dialog) {
            $template->appendTemplate('btnWrapper', $this->dialog->show());
        }

        $js = <<<JS
jQuery(function ($) {
  var init = function () {
    var tForm = $(this);
    
    function updateBtn(btn) {
      var cbName = btn.data('cb-name');
      if (btn.closest('.tk-table').find('.table-body input[name^="' + cbName + '"]:checked').length) {
        btn.removeAttr('disabled');
      } else {
        btn.attr('disabled', 'disabled');
      }
    }
  
    tForm.find('.tk-action-status').each(function () {
      var btn = $(this);
      var cbName = btn.data('cb-name');
      var status = $(this).parent().find(btn.data('select'));
      var form = $(status.get(0).form);
  
      btn.closest('.tk-table').find('.table-body input[name^="'+cbName+'"]').on('change', function () { updateBtn(btn); });
      updateBtn(btn);
      btn.on('mousedown', function (e) {
        form.data('isStatusBtn', true);
      });
      
      form.on('submit', function (e) {
        // Check if this submit was the actual status button
        if (!form.data('isStatusBtn')) return true;
        form.data('isStatusBtn', false);
        if (status.val() === '') {
          alert('Please select a status!');
          return false;
        }
        var selected = $(this).closest('.tk-table').find('.table-body input[name^="' + cbName + '"]:checked');
        // NOTE: If noe are selected then all requests are changed
        //if (selected.length <= 0) {
          //alert('Please select records!');
          //return false;
        //}
        return true;
      });
    });
  }
  $('.tk-table form').on('init', document, init).each(init);

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
    <a class="" href="javascript:;" var="btn"><i var="icon"></i> <span var="btnTitle"></span></a>
</span>
XHTML;
        return \Dom\Loader::load($xhtml);
    }

}
