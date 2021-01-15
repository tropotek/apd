<?php
namespace App\Table\Action;


use Tk\Callback;
use Tk\Ui\Dialog\JsonForm;

/**
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2015 Michael Mifsud
 */
class CreateCassette extends \Tk\Table\Action\Button
{


    /**
     * @var null|JsonForm
     */
    protected $cassetteDialog = null;



    /**
     * @param string $name
     * @param string $checkboxName The checkbox name to get the selected id's from
     * @param string $icon
     */
    public function __construct($name = 'Create Cassette', $icon = 'fa fa-stack-overflow')
    {
        parent::__construct($name, $icon);
        $this->setAttr('type', 'button');
        $this->addCss('tk-action-create-cassette');
        $this->setCassetteDialog(new \App\Ui\Dialog\CreateCassette());
    }

    /**
     * @param string $name
     * @param string $checkboxName
     * @param string $icon
     * @return static
     */
    static function create($name = 'Create Cassette', $icon = 'fa fa-stack-overflow')
    {
        return new static($name, $icon);
    }

    /**
     * @return string|\Dom\Template
     */
    public function show()
    {

        if (!$this->hasAttr('title'))
            $this->setAttr('title', 'Create Cassette`s');

        $this->setAttr('data-toggle', 'modal');
        $this->setAttr('data-target', '#'.$this->cassetteDialog->getId());

        if ($this->getCassetteDialog()) {
            $this->getTemplate()->appendBodyTemplate($this->getCassetteDialog()->show());
        }
        $template = parent::show();

        //$template->appendJs($this->getJs());
        return $template;
    }
    /**
     * @return string
     */
    protected function getJs()
    {
        $js = <<<JS
jQuery(function($) {
    
    $('.tk-action-create-request').each(function () {
      var btn = $(this);
      btn.on('click', function (e) {
        console.log('Button Pressed');
      });
      
      
    });
});
JS;
        return $js;
    }

    /**
     * @return JsonForm|null
     */
    public function getCassetteDialog(): ?JsonForm
    {
        return $this->cassetteDialog;
    }

    /**
     * @param JsonForm|null $cassetteDialog
     * @return CreateCassette
     */
    public function setCassetteDialog(?JsonForm $cassetteDialog): CreateCassette
    {
        $this->cassetteDialog = $cassetteDialog;
        return $this;
    }

}
