<?php
namespace App\Table;

use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\Form\Field;
use Tk\Table\Cell;
use Tk\Ui\Dialog\JsonForm;

/**
 * Example:
 * <code>
 *   $table = new InvoiceItem::create();
 *   $table->init();
 *   $list = ObjectMap::getObjectListing();
 *   $table->setList($list);
 *   $tableTemplate = $table->show();
 *   $template->appendTemplate($tableTemplate);
 * </code>
 *
 * @author Mick Mifsud
 * @created 2021-01-27
 * @link http://tropotek.com.au/
 * @license Copyright 2021 Tropotek
 */
class InvoiceItemMin extends \Bs\TableIface
{

    /**
     * @var null|\App\Ui\Dialog\InvoiceItem
     */
    protected $dialog = null;

    /**
     * @return $this
     * @throws \Exception
     */
    public function init()
    {
        $this->setDialog(\App\Ui\Dialog\InvoiceItem::createInvoiceItem());
        $this->getDialog()->init();

        $this->appendCell(new Cell\Checkbox('id'));
        $this->appendCell(new Cell\Text('description'))->addCss('key')->setUrl($this->getEditUrl());
        $this->appendCell(new Cell\Text('qty'));
        $this->appendCell(new Cell\Text('price'));
        $this->appendCell(new Cell\Text('total'))->setOrderProperty('')
            ->addOnPropertyValue(function (\Tk\Table\Cell\Iface $cell, \App\Db\InvoiceItem $obj, $value) {
                $value = $obj->getTotal()->toString();
                return $value;
            }
        );
        //$this->appendCell(new Cell\Date('created'));

        // Filters
        //$this->appendFilter(new Field\Input('keywords'))->setAttr('placeholder', 'Search');

        // Actions
        $this->appendAction(\Tk\Table\Action\Link::createLink('New Invoice Item', \Bs\Uri::createHomeUrl('/invoice/itemEdit.html'), 'fa fa-plus'))
        ->addOnInit(function (\Tk\Table\Action\Link $action) {
            $action->setAttr('data-toggle', 'modal');
            $action->setAttr('data-target', '#'.$this->getDialog()->getId());
        });

        //$this->appendAction(\Tk\Table\Action\ColumnSelect::create()->setUnselected(array('modified', 'created')));
        $this->appendAction(\Tk\Table\Action\Delete::create());
        $this->appendAction(\Tk\Table\Action\Csv::create());

        // load table
        //$this->setList($this->findList());

        $this->getDialog()->execute();
        return $this;
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
        $template->appendBodyTemplate($this->getDialog()->show());

        $js = <<<JS
jQuery(function($) {
  $('.tk-invoice-list .tk-table').each(function () {
    // Add a totals row at the footer of the table
    $(this).on('invoice::updateTotal', function () {
      var total = 0.0;
      $(this).find('td.mTotal').each(function () {
        total += parseFloat($(this).text().substring(1), 2);
      });
      $(this).find('tr.tk-invoice-total').remove();
      var html = '<tr class="tk-invoice-total">' +
       ' <td colspan="4" class="total-label">Total:</td>' +
       ' <td class="total-val">$'+total.toFixed(2)+'</td>' +
       '</tr>';
      $(this).find('table').append(html);
    }).trigger('invoice::updateTotal');
  });
  

});
JS;
        $template->appendJs($js);

        $css = <<<CSS
td.total-label {
  text-align: right;
  font-weight: 600 !important;
  padding-right: 10px;
}
td.mTotal, td.mPrice, td.total-val {
  text-align: right;
}
td.mQty {
  text-align: center;
}
CSS;
        $template->appendCss($css);

        return $template;
    }

    /**
     * @param array $filter
     * @param null|\Tk\Db\Tool $tool
     * @return \Tk\Db\Map\ArrayObject|\App\Db\InvoiceItem[]
     * @throws \Exception
     */
    public function findList($filter = array(), $tool = null)
    {
        if (!$tool) $tool = $this->getTool();
        $filter = array_merge($this->getFilterValues(), $filter);
        $list = \App\Db\InvoiceItemMap::create()->findFiltered($filter, $tool);
        return $list;
    }

    /**
     * @return \App\Ui\Dialog\InvoiceItem|null
     */
    public function getDialog(): ?\App\Ui\Dialog\InvoiceItem
    {
        return $this->dialog;
    }

    /**
     * @param \App\Ui\Dialog\InvoiceItem|null $dialog
     * @return InvoiceItemMin
     */
    public function setDialog(?\App\Ui\Dialog\InvoiceItem $dialog): InvoiceItemMin
    {
        $this->dialog = $dialog;
        return $this;
    }

}