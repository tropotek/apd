<?php
namespace App\Table;

use App\Db\CassetteMap;
use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Form\Field;
use Tk\Table\Cell;
use Tk\Table\Cell\ActionButton;
use Tk\Ui\Dialog\JsonForm;

/**
 * Example:
 * <code>
 *   $table = new Cassette::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2020-07-29
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
 */
class Cassette extends \Bs\TableIface
{
    /**
     * @var bool
     */
    private $minMode = false;

    /**
     * @var null|JsonForm
     */
    protected $requestDialog = null;


    /**
     * If true then this table is shown in the Case Edit page on the side bar and requires
     * the ability to add requests.
     *
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
     * @return Cassette
     */
    public function setMinMode(bool $minMode): Cassette
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
        $this->removeCss('table-hover');
        $this->addCss('cassette-table');

        if($this->isMinMode())
            $this->getRenderer()->enableFooter(false);

        $this->requestDialog = \App\Ui\Dialog\Request::createRequest();


        $this->appendCell(new Cell\Checkbox('id'));
        if ($this->isMinMode()) {
//            $this->appendCell(new Cell\Checkbox('id'));
//        } else {
            $dialog = $this->requestDialog;
            $aCell = $this->getActionCell();
            $url = \Uni\Uri::createHomeUrl('/requestEdit.html');
            $aCell->addButton(ActionButton::create('Create Request', $url, 'fa fa-medkit')->addCss('btn-primary'))
                ->setShowLabel(false)
                ->addOnShow(function ($cell, $obj, $button) use ($dialog) {
                    /* @var $obj \App\Db\Cassette */
                    /* @var $button ActionButton */
                    $button->getUrl()->set('cassetteId', $obj->getId());
                    $button->setAttr('data-toggle', 'modal');
                    $button->setAttr('data-target', '#'.$dialog->getId());
                    $button->setAttr('data-cassette-id', $obj->getId());
                });
            $this->appendCell($this->getActionCell())->setLabel('');
        }

        $this->appendCell(new Cell\Text('number'))->setLabel('#')->setAttr('title', 'Label')->setUrl($this->getEditUrl());
        $this->appendCell(new Cell\Text('comments'))->addCss('key')->addOnCellHtml(function ($cell, $obj, $value) {
            /** @var Cell\Text $cell */
            /** @var \App\Db\Note $obj */
            $value = \Tk\Str::stripEntities(strip_tags($value));
            return nl2br($value);
        });
//        $this->appendCell(new Cell\Date('created'));

        // Filters
        if (!$this->isMinMode())
            $this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        if ($this->isMinMode()) {
            $this->appendAction(\App\Table\Action\CreateRequest::create()->setRequestDialog($this->requestDialog));
            $this->appendAction(\App\Table\Action\CreateCassette::create());
            //$this->appendAction(\Tk\Table\Action\Link::createLink('New Cassette', \Bs\Uri::createHomeUrl('/cassetteEdit.html'), 'fa fa-plus'));
        }

//        $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('created')));

        if (!$this->isMinMode())
            $this->appendAction(\Tk\Table\Action\Delete::create()->setConfirmStr('Deleting a Cassette record will also delete any Requests created from that Cassette record! Continue?'));
        $this->appendAction(\Tk\Table\Action\Csv::create());

        // load table
        //$this->setList($this->findList());

        if ($this->getRequest()->has('uc')) {
            $this->doCassetteUpdate($this->getConfig()->getRequest());
        }


        return $this;
    }

    /**
     * @param \Tk\Request $request
     */
    public function doCassetteUpdate($request)
    {
        $cassette = CassetteMap::create()->find($request->get('uc'));
        if ($cassette) {
            $v = \Tk\Str::stripEntities(strip_tags($request->get('value')));
            $cassette->setComments($v);
            $cassette->save();
            \Tk\Log::debug('Cassette comment updated ['.$cassette->getId().']');
        }
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\App\Db\Cassette[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\CassetteMap::create()->findFiltered($filter, $tool);
        $this->requestDialog->execute();
        return $list;
    }

    /**
     * Execute the renderer.
     * Return an object that your framework can interpret and display.
     *
     * @return null|Template|Renderer
     */
    public function show()
    {
        $template = parent::show();

        $js = <<<JS
jQuery(function($) {
  var init = function () {
    var form = $(this);
    
    // Dynamic event handler to allow for when new cassettes are created
    ////$(document).on('dblclick', '.tk-table .mComments', function (e) {
    //$(document).on('click', '.tk-table .mComments', function (e) {
    form.on('click', '.mComments', function (e) {
      if ($(this).find('.tdVal').length) return;
      e.stopPropagation();
      $(this).attr('title', 'Click To Edit');
      var value = br2nl($(this).html());
      $(this).focus();
      updateVal($(this), value);
    });
    
    function updateVal(el, value) {
      el.html('<textarea class="tdVal form-control" title="Click To Edit">' + value + '</textarea>');
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
          'uc': id, 
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
  $('form').on('init', $('.cassette-table'), init).each(init);
  //$('.cassette-table form').on('init', document, init).each(init);

});
JS;
        $template->appendJs($js);
        $css = <<<CSS
.tk-table td.mComments {
  position: relative;
   cursor: pointer;
}
.mComments:after {
   font: normal normal normal 14px/1 FontAwesome;
   content: "\\f040";
   display: inline-block;
   position: absolute;
   float: left;
   right: 12px;
   top: 17px;
   padding-right: 3px;
   vertical-align: middle;
   font-weight:900;
   cursor: pointer;
}
CSS;
        $template->appendCss($css);

        return $template;
    }
}