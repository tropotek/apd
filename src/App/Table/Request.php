<?php
namespace App\Table;

use App\Db\CompanyMap;
use App\Db\PathCaseMap;
use App\Db\RequestMap;
use Tk\Date;
use Tk\Form\Field;
use Tk\Table\Cell;
use Uni\Db\User;
use Uni\Uri;

/**
 * Example:
 * <code>
 *   $table = new Request::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 */
class Request extends \Bs\TableIface
{
    /**
     * @var bool
     */
    private $minMode = false;

    public function __construct($tableId = '')
    {
        parent::__construct($tableId);
        if ($this->getConfig()->getRequest()->has('rComplete')) {
            $this->doComplete($this->getConfig()->getRequest()->get('rComplete'));
        }
        if ($this->getConfig()->getRequest()->has('rCancel')) {
            $this->doCancel($this->getConfig()->getRequest()->get('rCancel'));
        }
        if ($this->getConfig()->getRequest()->has('rDel')) {
            $this->doDelete($this->getConfig()->getRequest()->get('rDel'));
        }
        if ($this->getRequest()->has('ur')) {
            $this->doRequestUpdate($this->getConfig()->getRequest());
        }
    }

    /**
     * @param \Tk\Request $request
     */
    public function doRequestUpdate($request)
    {
        $obj = RequestMap::create()->find($request->get('ur'));
        if ($obj) {
            $v = \Tk\Str::stripEntities(strip_tags($request->get('value')));
            $obj->setComments($v);
            $obj->save();
            \Tk\Log::debug('Comment updated ['.$obj->getId().']');
        }
    }

    public function doComplete($requestId)
    {
        /** @var \App\Db\Request $request */
        $request = \App\Db\RequestMap::create()->find($requestId);
        if ($request) {
            $request->setStatus(\App\Db\Request::STATUS_COMPLETED);
            $request->save();
        }
        \Tk\Uri::create()->remove('rComplete')->redirect();
    }

    public function doCancel($requestId)
    {
        /** @var \App\Db\Request $request */
        $request = \App\Db\RequestMap::create()->find($requestId);
        if ($request) {
            $request->setStatus(\App\Db\Request::STATUS_CANCELLED);
            $request->save();
        }
        \Tk\Uri::create()->remove('rCancel')->redirect();
    }

    public function doDelete($requestId)
    {
        /** @var \App\Db\Request $request */
        $request = \App\Db\RequestMap::create()->find($requestId);
        if ($request) {
            $request->delete();
        }
        \Tk\Uri::create()->remove('rDel')->redirect();
    }


    /**
     * @return bool
     */
    public function isMinMode(): bool
    {
        return $this->minMode;
    }

    /**
     * Set this to true to enable minimum mode that will render for side panels
     *
     * @param bool $minMode
     * @return Request
     */
    public function setMinMode(bool $minMode)
    {
        $this->minMode = $minMode;
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {
        $this->addCss('tk-request-table');

        if($this->isMinMode())
            $this->getRenderer()->enableFooter(false);

        if (!$this->isMinMode()) {
            $this->appendCell(new Cell\Checkbox('id'));
        }

        $aCell = $this->getActionCell();
        $aCell->addButton(Cell\ActionButton::create('Complete', Uri::create(), 'fa fa-thumbs-up')->addCss('btn-success'))
            ->setShowLabel(false)
            ->addOnShow(function ($cell, \App\Db\Request $obj, Cell\ActionButton $button) {
                $button->getUrl()->set('rComplete', $obj->getId());
                $button->setAttr('data-confirm', 'Are you sure you want to mark request completed?');
                if ($obj->getStatus() == \App\Db\Request::STATUS_COMPLETED) {
                    $button->setAttr('disabled')->addCss('disabled');
                }
            });
        $aCell->addButton(Cell\ActionButton::create('Select All Case Requests', Uri::create('#'), 'fa fa-check-square-o')
            ->addCss('btn-light'))
            ->setShowLabel(false)
            ->addCss('btn-select-all');

        if ($this->isMinMode()) {
            $aCell->addButton(Cell\ActionButton::create('Delete', Uri::create(), 'fa fa-trash')->addCss('btn-danger'))
                ->setShowLabel(false)
                ->addOnShow(function ($cell, \App\Db\Request $obj, Cell\ActionButton $button) {
                    $button->getUrl()->set('rDel', $obj->getId());
                    $button->setAttr('data-confirm', 'Are you sure you want to remove this request?');
                });
        }
        $this->appendCell($this->getActionCell())->setLabel('');

        $this->appendCell(new Cell\Text('cassetteId'))->setUrl($this->getEditUrl())->
        addOnPropertyValue(function (Cell\Text $cell, \App\Db\Request $obj, $value) {
            \Tk\Db\Map\Mapper::$HIDE_DELETED = false;
            if ($obj->getCassette()) {
                $value = $obj->getCassette()->getNumber();
                    if ($obj->getCassette()->isDel()) {
                        $value = '* ' . $value;
                    }
            }
            \Tk\Db\Map\Mapper::$HIDE_DELETED = true;
            return $value;
        });
        $this->appendCell(new Cell\Html('comments'))->addCss('key')
            ->addOnPropertyValue(function (\Tk\Table\Cell\Iface $cell, \App\Db\Request $obj, $value) {
                //$value = '<div style="max-width: 400px;overflow: auto;">' . $obj->getComments() . '</div>';
                $cell->setAttr('style', 'min-width: 200px;white-space: unset;');
                return htmlspecialchars($value);
            });
        $this->appendCell(new Cell\Text('status'));
        $this->appendCell(\Tk\Table\Cell\Text::create('pathologist'))
            ->setOrderProperty('p.name_first')
            ->addOnPropertyValue(function (\Tk\Table\Cell\Iface $cell, \App\Db\Request $obj, $value) {
                $value = 'N/A';
                if ($obj->getPathCase() && $obj->getPathCase()->getPathologist()) {
                    //$cell->getRow()->setAttr('data-pathology-id', $obj->getPathCase()->getPathologyId());
                    //$cell->setUrl(\Bs\Uri::createHomeUrl('/pathCaseEdit.html')->set('pathCaseId', $obj->getPathCaseId()));
                    $value = $obj->getPathCase()->getPathologist()->getName();
                }
                return $value;
            });
        $this->appendCell(\Tk\Table\Cell\Text::create('pathologyId'))
            ->setOrderProperty('b.pathology_id')->setLabel('Pathology #')
            ->addOnPropertyValue(function (\Tk\Table\Cell\Iface $cell, \App\Db\Request $obj, $value) {
                if ($obj->getPathCase()) {
                    $cell->getRow()->setAttr('data-pathology-id', $obj->getPathCase()->getPathologyId());
                    $cell->setUrl(\Bs\Uri::createHomeUrl('/pathCaseEdit.html')->set('pathCaseId', $obj->getPathCaseId()));
                    $value = $obj->getPathCase()->getPathologyId();
                }
                return $value;
            });
        $this->appendCell(\Tk\Table\Cell\Text::create('type'))
            ->setOrderProperty('b.type')->setLabel('Case Type')
            ->addOnPropertyValue(function (\Tk\Table\Cell\Iface $cell, \App\Db\Request $obj, $value) {
                if ($obj->getPathCase()) {
                    $value = $obj->getPathCase()->getType();
                }
                return $value;
            });
        $this->appendCell(\Tk\Table\Cell\Boolean::create('billable'))
            ->setOrderProperty('b.billable')
            ->addOnPropertyValue(function (\Tk\Table\Cell\Iface $cell, \App\Db\Request $obj, $value) {
                if ($obj->getPathCase()) {
                    $value = $obj->getPathCase()->isBillable();
                }
                return $value;
            });


        $this->appendCell(new Cell\Text('qty'));
        $this->appendCell(new Cell\Text('serviceId'))->
            addOnPropertyValue(function (Cell\Text $cell, \App\Db\Request $obj, $value) {
                \Tk\Db\Map\Mapper::$HIDE_DELETED = false;
                if ($obj->getService()) {
                    $value = $obj->getService()->getName();
                    if ($obj->getService()->isDel()) {
                        $value = '* ' . $value;
                    }
                }
                \Tk\Db\Map\Mapper::$HIDE_DELETED = true;
                return $value;
            });
        $this->appendCell(new Cell\Text('testId'))->
            addOnPropertyValue(function (Cell\Text $cell, \App\Db\Request $obj, $value) {
                $value = '';
                if ($obj->getTest()) {
                    $value = $obj->getTest()->getName();
                }
                return $value;
            });


        if (!$this->isMinMode()) {
            $this->appendCell(new Cell\Date('modified'));
            $this->appendCell(new Cell\Date('created'));
        }

        // Filters
        if (!$this->isMinMode()) {
            $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

            $this->appendFilter(new Field\Input('pathologyId'))->setAttr('placeholder', 'Case ID');

            $list = \Tk\ObjectUtil::getClassConstants(\App\Db\Request::class, 'STATUS', true);
            $this->appendFilter(Field\CheckboxSelect::createSelect('status', $list))
                ->setValue([\App\Db\Request::STATUS_PENDING]);


            $list = $this->getConfig()->getUserMapper()->findFiltered(array(
                'institutionId' => $this->getConfig()->getInstitutionId(),
                'type' => User::TYPE_STAFF,
                'active' => true
            ));
            $this->appendFilter(Field\Select::createSelect('pathologistId', $list)->prependOption('-- Pathologist --'));

            $list = CompanyMap::create()->findFiltered([
                'institutionId' => $this->getConfig()->getInstitutionId(),
            ]);
            $this->appendFilter(Field\Select::createSelect('companyId', $list)->prependOption('-- Submitter/Client --'));

            $this->appendFilter(new Field\DateRange('date'));
        }

        // Actions
        if (!$this->isMinMode()) {
            $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified')));
            $this->appendAction(\Tk\Table\Action\Delete::create());
        }
        $this->appendAction(\Tk\Table\Action\Csv::create());
        $this->appendAction(\App\Table\Action\Status::create(\App\Db\Request::getStatusList()));


        return $this;
    }


    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\App\Db\Request[]
     * @throws \Exception
     */
    public function findList($filter = [], $tool = null)
    {
        if (!$tool) $tool = $this->getTool('b.pathology_id, c.number DESC');
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\RequestMap::create()->findFiltered($filter, $tool);
        return $list;
    }

    public function show()
    {
        $template = parent::show();

        $js = <<<JS
jQuery(function($) {
  var init = function () {
    var form = $(this);
    $(this).find('.btn-select-all').each(function () {
      $(this).on('click', function() {
        var pathologyId = $(this).closest('tr').find('.mPathologyId').attr('title');
        //var selectedCheckbox = $(this).closest('tr').find('.tk-tcb-cell input');
        //console.log(pathologyId);
        form.find('tr .tk-tcb-cell input').prop('checked', false);
        var rows = form.find('tr[data-pathology-id='+pathologyId+']');
        rows.find('.tk-tcb-cell input').trigger('click');
      });
    });
  }
  $('form').on('init', document, init).each(init);
});
JS;
        $template->appendJs($js);

        if (!$this->isMinMode()) {
            $js = <<<JS
jQuery(function($) {
  var init = function () {
    var form = $(this);
    
    // Dynamic event handler to allow for when new cassettes are created
    form.on('click', '.mComments', function (e) {
      if ($(this).find('.tdVal').length) return;
      e.stopPropagation();
      $(this).attr('title', 'Click To Edit');
      var value = br2nl($(this).html());
      $(this).focus();
      updateVal($(this), value);
    });
    
    function updateVal(el, value) {
      el.html('<textarea class="tdVal form-control" style="min-width: 350px;" title="Click To Edit">' + value + '</textarea>');
      var tdval = el.find('.tdVal');
      tdval.focus();
      tdval.keypress(function (e) {
        e.stopPropagation();
        if (!e.shiftKey && e.keyCode === 13) {
          saveVal(el, tdval.val().trim());
          return false;
        }
      });
    }

    function saveVal(el, val) {
      val = br2nl(val);
      el.css({'cursor': 'wait'});
      var tdval =  el.parent().find('td.mId').find('input');
      tdval.attr('disabled', 'disabled');
      var id = tdval.val();
      // Send update to DB
      $.get(document.location, {
          'ur': id, 
          'value': val,
          crumb_ignore: 'crumb_ignore',
          nolog: 'nolog'
        }, function (data) {
        el.html(nl2br(val));
        el.removeAttr('disabled').css({'cursor': 'inherit'}); 
      }, 'html');
    }

    $(document).mouseup(function () {
      form.find('.tdVal').each(function () {
        if (!$(this).parent().is(':hover'))
          saveVal($(this).parent(), $(this).val().trim());
      });
    });
  
    /**
     * This function is same as PHP's nl2br() with default parameters.
     *
     * @param {string} str Input text
     * @param {boolean} replaceMode Use replace instead of insert
     * @param {boolean} isXhtml Use XHTML 
     * @return {string} Filtered text
     */
    function nl2br (str, replaceMode, isXhtml) {
      var breakTag = (isXhtml) ? '<br />' : '<br>';
      var replaceStr = (replaceMode) ? '$1'+ breakTag : '$1'+ breakTag +'$2';
      return (str + '').replace(/([^>\\r\\n]?)(\\r\\n|\\n\\r|\\r|\\n)/g, replaceStr);
    }
    /**
     * This function inverses text from PHP's nl2br() with default parameters.
     *
     * @param {string} str Input text
     * @param {boolean} replaceMode Use replace instead of insert
     * @return {string} Filtered text
     */
    function br2nl (str, replaceMode) {        
      var replaceStr = (replaceMode) ? "\\n" : '';
      // Includes <br>, <BR>, <br />, </br>
      return str.replace(/<\\s*\\/?br\\s*[\\/]?>/gi, replaceStr);
    }
      
  };
  $('form').on('init', $('.tk-request-table'), init).each(init);
  //$('.tk-request-table form').on('init', document, init).each(init);

});
JS;
            $template->appendJs($js);
            $css = <<<CSS
.tk-table td.mComments {
  position: relative;
  cursor: pointer;
  line-height: 1.5em;
}
.mComments:after {
   font: normal normal normal 14px/1 FontAwesome;
   content: "\\f040";
   display: inline-block;
   position: absolute;
   float: left;
   right: 2px;
   top: 2px;
   padding-right: 3px;
   vertical-align: middle;
   font-weight:900;
   cursor: pointer;
   opacity: 0.50;
}
CSS;
            $template->appendCss($css);
        }
        return $template;
    }


}