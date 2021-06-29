<?php
namespace App\Ui;

use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\ConfigTrait;
use Tk\Request;
use Tk\Str;


/**
 * Class CmsPanel
 *
 * @package App\Ui
 */
class CmsPanel extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{
    use ConfigTrait;

    protected $title = 'News';

    protected $icon = 'fa-newspaper-o';

    protected $contentKey = 'inst.dash.content';


    /**
     * CmsPanel constructor.
     */
    public function __construct($title, $icon, $contentKey)
    {
        $this->title = $title;
        $this->icon = $icon;
        $this->contentKey = $contentKey;
    }

    /**
     * @return static
     */
    public static function create($title, $icon, $contentKey)
    {
        $obj = new static($title, $icon, $contentKey);
        return $obj;
    }

    /**
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        if ($request->has('cmsSave')) {
            //vd($request->all());
            $this->setContent($request->get('cmsSave'));
        }

    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        $data = $this->getConfig()->getInstitution()->getData();
        return $data->get($this->contentKey, '');
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $data = $this->getConfig()->getInstitution()->getData();
        $data->set($this->contentKey, $content);
        $data->save();
        return $this;
    }


    /**
     * @return Renderer|Template|void|null
     */
    public function show()
    {
        $template = $this->getTemplate();

        $template->setAttr('textarea', 'data-content-key', $this->contentKey);
        $template->setAttr('textarea', 'data-elfinder-path', $this->getConfig()->getInstitution()->getDataPath().'/cms-dash');
        $template->setAttr('panel', 'data-panel-title', $this->title);
        $template->setAttr('panel', 'data-panel-icon', $this->icon);
        $cssClass = 'cms-' . Str::toCamelCase($this->title);
        $template->addCss('panel', $cssClass);

        if ($this->getContent()) {
            $template->appendHtml('cms-content', $this->getContent());
            $template->appendHtml('textarea', $this->getContent());
        }

        $js = <<<JS
config.mceOpts = {
  plugins: ['save lists advlist autolink link image media code'],
  toolbar1: 'save | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist ' +
          '| link unlink image media | removeformat code',
  toolbar2 : '',
  save_onsavecallback: function () {
    $(this.formElement).closest('div.cms-panel').find('a.tk-save').trigger('click');
  }
};
jQuery(function ($) {
  
  $('.cms-panel').each(function () {
    var panel = $(this);
    var contentEl = panel.find('div.cms-content');
    var editBtn = panel.find('a.tk-edit');
    var saveBtn = panel.find('a.tk-save');
    var form = panel.find('form.cms-content-form');
    var el = form.find('textarea');   // textarea
    saveBtn.hide();
    form.hide();
    
    function cmsSave(textarea, html) {
      editBtn.show();
      saveBtn.hide();
      form.hide();  // textarea
      $.get(document.location, {
          cmsSave: html,
          crumb_ignore: 'crumb_ignore',
          key: el.data('contentKey'),
          //nolog: 'nolog'
      }, function (html) { }, 'html');
      contentEl.empty().html(html).show();
    }
    
    editBtn.on('click', function () {
      editBtn.hide();
      saveBtn.show();
      form.show();
      var html = contentEl.html();
      contentEl.hide();
      el.html(html);
    });
    saveBtn.on('click', function () {
      cmsSave(el, tinymce.activeEditor.getContent());
    });
    
  });
  
});
JS;
        $template->appendJs($js);

        return $template;
    }

    /**
     * DomTemplate magic method
     *
     * @return Template
     */
    public function __makeTemplate()
    {
        $xhtml = <<<HTML
<div class="row">
  <div class="col-12">
    <div class="tk-panel cms-panel" data-panel-title="News" data-panel-icon="fa fa-newspaper-o" var="panel">
      <div class="tk-panel-title-right icon-box">
        <a href="#" class="btn float-left tk-edit"><i class="fa fa-edit"></i></a>
        <a href="#" class="btn float-left tk-save"><i class="fa fa-save"></i></a>
      </div>
      <div class="cms-content" var="cms-content"></div>
      <form class="cms-content-form"><textarea class="form-control mce-min cms-textarea" data-elfinder-path="" var="textarea"></textarea></form>
    </div>
  </div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}