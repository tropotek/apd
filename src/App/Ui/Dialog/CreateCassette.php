<?php
namespace App\Ui\Dialog;

use App\Db\Cassette;
use Tk\Form;
use Tk\Ui\Dialog\JsonForm;
use Tk\Form\Event;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2016 Michael Mifsud
 */
class CreateCassette extends JsonForm
{

    public function __construct()
    {
        $form = $this->getConfig()->createForm('createCassette');
        $form->appendField(\Tk\Form\Field\Input::create('limit')->setType('number'))->setLabel('Number Of Cassettes')
            ->setValue(1);
        $form->appendField(\Tk\Form\Event\Submit::create('save', array($this, 'doSubmit')))->setLabel('Create');
        $form->appendField(new \Tk\Form\Event\Link('cancel', $this->getBackUrl()));
        $form->execute($this->getConfig()->getRequest());

        $this->addCss('create-cassette-dialog');
        parent::__construct($form, 'Create Cassette');
    }

    /**
     * @param Form $form
     * @param Event\Iface $event
     * @throws \Exception
     */
    public function doSubmit($form, $event)
    {
        $limit = $form->getFieldValue('limit');
        $pid = $this->getConfig()->getRequest()->get('pathCaseId');
        if ($limit > 0) {
            for ($i = 0; $i < $limit; $i++) {
                $c = new Cassette();
                $c->setNumber(Cassette::getNextNumber($pid));
                $c->setPathCaseId($pid);
                $c->save();
            }
        }
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
    
    // Reset form
    dialog.on('shown.bs.modal', function (e) {
      form.find('[name="limit"]').val(1);
    });
    
    dialog.on('DialogForm:submit', function (e) {
      var table = $('div.tk-cassette-list .tk-table');
      if (table.length !== 1) return;
      $.get(document.location, {}, function (html) {
        var doc = $(html);
        var el = doc.find('div.tk-cassette-list .tk-table form > div');
        el.detach();
        $('div.tk-cassette-list  .tk-table form').empty().append(el);
        var f = table.find('form');
        f.trigger('init');
      }, 'html');
    });
    
  }
  $('.create-cassette-dialog .modal-body form').on('init', document, init).each(init);
});
JS;

        $template->appendJs($js);

        return $template;
    }






}