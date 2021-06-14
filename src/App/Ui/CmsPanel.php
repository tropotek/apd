<?php
namespace App\Ui;

use Dom\Renderer\Renderer;
use Dom\Template;
use Tk\ConfigTrait;
use Tk\Request;


/**
 * Class CmsPanel
 *
 * @package App\Ui
 */
class CmsPanel extends \Dom\Renderer\Renderer implements \Dom\Renderer\DisplayInterface
{
    use ConfigTrait;

    const CONTENT_KEY = 'inst.dash.content';


    /**
     * CmsPanel constructor.
     */
    public function __construct()
    {
//        $data = $this->getConfig()->getInstitution()->getData();
//        $data->set(self::CONTENT_KEY, '<p>This is a test111</p>');

    }

    /**
     * @return static
     */
    public static function create()
    {
        $obj = new static();
        return $obj;
    }

    /**
     * @param Request $request
     */
    public function doDefault(Request $request)
    {
        if ($request->has('cmsSave')) {
            $this->setContent($request->get('cmsSave'));
        }

    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        $data = $this->getConfig()->getInstitution()->getData();
        return $data->get(self::CONTENT_KEY, '');
    }

    /**
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        $data = $this->getConfig()->getInstitution()->getData();
        $data->set(self::CONTENT_KEY, $content);
        $data->save();
        return $this;
    }


    /**
     * @return Renderer|Template|void|null
     */
    public function show()
    {
        $template = $this->getTemplate();

        if ($this->getContent()) {
            $template->appendHtml('news-content', $this->getContent());
            $template->appendHtml('textarea', $this->getContent());
            $template->setAttr('textarea', 'data-elfinder-path', $this->getConfig()->getInstitution()->getDataPath().'/cms-dash');
        }

        $js = <<<JS
config.mceOpts = {
  plugins: ['save lists advlist autolink link image media code'],
  toolbar1: 'save | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist ' +
          '| link unlink image media | removeformat code',
  toolbar2 : '',
  save_onsavecallback: function () { 
    $('div.cms-news a.tk-save').trigger('click');
  }
};
jQuery(function ($) {
  
  $('.cms-news').each(function () {
    var panel = $(this);
    var contentEl = panel.find('div.news-content');
    var editBtn = panel.find('a.tk-edit');
    var saveBtn = panel.find('a.tk-save');
    var mceEl =  $('#news-content-form textarea');
    saveBtn.hide();
    $('#news-content-form').hide();
    
    function cmsSave() {
      var html = tinymce.activeEditor.getContent();
      editBtn.show();
      saveBtn.hide();
      $('#news-content-form').hide();
      $.get(document.location, {
          cmsSave: html,
          crumb_ignore: 'crumb_ignore',
          nolog: 'nolog'
      }, function (html) { }, 'html');
      tinymce.remove('#news-content textarea');
      contentEl.empty().html(html);
    }
    
    editBtn.on('click', function () {
      editBtn.hide();
      saveBtn.show();
      $('#news-content-form').show();
      var html = contentEl.html();
      contentEl.empty().append(mceEl.find('textarea').html(html));
    });
    saveBtn.on('click', function () {
      cmsSave();
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
    <div class="tk-panel cms-news" data-panel-title="News" data-panel-icon="fa fa-newspaper-o" var="news">
      <div class="tk-panel-title-right icon-box">
        <a href="#" class="btn float-left tk-edit"><i class="fa fa-edit"></i></a>
        <a href="#" class="btn float-left tk-save"><i class="fa fa-save"></i></a>
      </div>
      <div id="news-content" class="news-content" var="news-content"></div>
      <form id="news-content-form"><textarea class="form-control mce-min" data-elfinder-path="" var="textarea"></textarea></form>
    </div>
  </div>
</div>
HTML;

        return \Dom\Loader::load($xhtml);
    }


}