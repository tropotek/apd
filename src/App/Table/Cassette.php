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
        $this->appendCell(new Cell\Text('comments'))->addCss('key');
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
            $this->appendAction(\Tk\Table\Action\Delete::create());
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
            $cassette->setComments(strip_tags($request->get('value')));
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
  
  // Dynamic event handler to allow for when new cassettes are created
  $(document).on('dblclick', '.tk-table .mComments', function (e) {
      if ($(this).find('.tdVal').length) return;
      e.stopPropagation();
      $('.mComments').css({'cursor': 'pointer'}).attr('title', 'Double Click To Edit');
      var value = $(this).html();
      $(this).focus();
      updateVal($(this), value);
    });
  
  function updateVal(el, value) {
    el.html('<input class="tdVal form-control" type="text" value="' + value + '" title="Double Click To Edit" />');
    var tdval = el.find('.tdVal');
    tdval.focus();
    tdval.keypress(function (e) {
      e.stopPropagation();
      if (e.keyCode === 13) {
        saveVal(el, tdval.val().trim());
        return false;
      }
    });
  }

  function saveVal(el, val) {
    el.css({'cursor': 'wait'});
    var tdval =  el.parent().find('td.mId').find('input');
    tdval.attr('disabled', 'disabled');
    var id = tdval.val();
    // Send update to DB
    $.get(document.location, {'uc': id, 'value': val}, function (data) {
      el.html(val);
      el.removeAttr('disabled').css({'cursor': 'inherit'}); 
    }, 'html');
  }

  $(document).mouseup(function () {
    $('.tdVal').each(function () {
      if (!$(this).parent().is(':hover'))
        saveVal($(this).parent(), $(this).val().trim());
    });
  });
  
});
JS;
        $template->appendJs($js);

        return $template;
    }
}