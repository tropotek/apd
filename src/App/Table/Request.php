<?php
namespace App\Table;


use App\Db\ContactMap;
use App\Db\ServiceMap;
use App\Db\TestMap;
use Tk\Db\Tool;
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
 *
 * @author Mick Mifsud
 * @created 2020-07-30
 * @link http://tropotek.com.au/
 * @license Copyright 2020 Tropotek
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
        $aCell->addButton(Cell\ActionButton::create('Select All Case Requests', Uri::create('#'), 'fa fa-check-square-o')->addCss('btn-light'))
            ->setShowLabel(false)
            ->addCss('btn-select-all');

//        $aCell->addButton(Cell\ActionButton::create('Cancel', Uri::create(), 'fa fa-thumbs-down')->addCss('btn-warning'))
//            ->setShowLabel(false)
//            ->addOnShow(function ($cell, \App\Db\Request $obj, Cell\ActionButton $button) {
//                $button->getUrl()->set('rCancel', $obj->getId());
//                $button->setAttr('data-confirm', 'Are you sure you want to cancel this request?');
//                if ($obj->getStatus() == \App\Db\Request::STATUS_CANCELLED) {
//                    $button->setAttr('disabled')->addCss('disabled');
//                }
//            });

        if ($this->isMinMode()) {
            $aCell->addButton(Cell\ActionButton::create('Delete', Uri::create(), 'fa fa-trash')->addCss('btn-danger'))
                ->setShowLabel(false)
                ->addOnShow(function ($cell, \App\Db\Request $obj, Cell\ActionButton $button) {
                    $button->getUrl()->set('rDel', $obj->getId());
                    $button->setAttr('data-confirm', 'Are you sure you want to remove this request?');
                });
        }
        $this->appendCell($this->getActionCell())->setLabel('');

        $this->appendCell(new Cell\Text('cassetteId'))->addCss('key')->setUrl($this->getEditUrl())->
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
        $this->appendCell(new Cell\Text('status'));
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

            $this->appendFilter(new Field\Input('pathCaseId'))->setAttr('placeholder', 'Case ID');

            $serviceList = ServiceMap::create()->findFiltered(['institutionId' => $this->getConfig()->getInstitutionId()]);
            $this->appendFilter(Field\Select::createSelect('serviceId', $serviceList)->prependOption('-- Service --'));

            $serviceList = TestMap::create()->findFiltered(['institutionId' => $this->getConfig()->getInstitutionId()], Tool::create('name'));
            $this->appendFilter(Field\Select::createSelect('testId', $serviceList)->prependOption('-- Test --'));

            $list = \Tk\ObjectUtil::getClassConstants(\App\Db\Request::class, 'STATUS', true);
            $this->appendFilter(Field\Select::createSelect('status', $list)->prependOption('-- Status --'))
                ->setValue(\App\Db\Request::STATUS_PENDING);


            $list = $this->getConfig()->getUserMapper()->findFiltered(array(
                'institutionId' => $this->getConfig()->getInstitutionId(),
                'type' => User::TYPE_STAFF
            ));
            $this->appendFilter(Field\Select::createSelect('pathologistId', $list)->prependOption('-- Pathologist --'));

            $list = ContactMap::create()->findFiltered(array(
                'institutionId' => $this->getConfig()->getInstitutionId(),
                'type' => \App\Db\Contact::TYPE_CLIENT
            ));
            $this->appendFilter(Field\Select::createSelect('clientId', $list)->prependOption('-- Submitter/Client --'));
            $list = ContactMap::create()->findFiltered(array(
                'institutionId' => $this->getConfig()->getInstitutionId(),
                'type' => \App\Db\Contact::TYPE_OWNER
            ));
            $this->appendFilter(Field\Select::createSelect('ownerId', $list)->prependOption('-- Owner --'));

            $this->appendFilter(new Field\DateRange('date'));

        }


        // Actions
        //$this->appendAction(\Tk\Table\Action\Link::createLink('New Request', \Bs\Uri::createHomeUrl('/requestEdit.html'), 'fa fa-plus'));
        $this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified')));

        if (!$this->isMinMode()) {
            $this->appendAction(\Tk\Table\Action\Delete::create());
        }
        $this->appendAction(\Tk\Table\Action\Csv::create());
        $this->appendAction(\App\Table\Action\Status::create(\App\Db\Request::getStatusList()));

        // load table
        //$this->setList($this->findList());

        return $this;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\App\Db\Request[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
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
        var selectedCheckbox = $(this).closest('tr').find('.tk-tcb-cell input');
        //console.log(pathologyId);
        form.find('tr .tk-tcb-cell input').prop('checked', false);
        var rows = form.find('tr[data-pathology-id='+pathologyId+']');
        rows.find('.tk-tcb-cell input').trigger('click');
        // if (selectedCheckbox.is(':checked')) {
        //   rows.find('.tk-tcb-cell input').prop('checked', false).trigger('change');
        // } else {
        //   rows.find('.tk-tcb-cell input').prop('checked', true).trigger('change');
        // }
        //console.log(rows);
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