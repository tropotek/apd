<?php
namespace App\Form;


use Uni\Db\User;
use Dom\Loader;
use Dom\Renderer\DisplayInterface;
use Dom\Renderer\Renderer;
use Dom\Template;
use Exception;
use Tk\Alert;
use Tk\ConfigTrait;
use Tk\Db\ModelInterface;
use Tk\Request;
use Tk\Uri;
use Uni\Db\UserIface;


/**
 * @author Mick Mifsud
 * @created 2019-02-14
 * @link http://tropotek.com.au/
 * @license Copyright 2019 Tropotek
 */
class Note extends Renderer implements DisplayInterface
{
    use ConfigTrait;

    /**
     * @var null|ModelInterface
     */
    protected $model = null;

    /**
     * @var null|User
     */
    protected $author = null;

    /**
     * @var bool
     */
    protected $reminderEnabled = false;


    /**
     * @param ModelInterface $model
     * @param UserIface $author
     * @throws Exception
     */
    public function __construct($model, $author)
    {
        $this->model = $model;
        $this->author = $author;
        $request = $this->getConfig()->getRequest();
        if($request->has('notes-post')) $this->doSubmit($request);

    }

    /**
     * @param Request $request
     * @throws Exception
     */
    public function doSubmit(Request $request)
    {
        $message = trim(strip_tags($request->get('message')));
        if (!$message) {
            Alert::addError('Please enter a valid message.');
            return;
        }

        $obj = new \App\Db\Note();
        $obj->setFkey($request->get('fkey', ''));
        $obj->setFId($request->get('fid', 0));
        $obj->setUserId($this->author->getId());
        $obj->message = strip_tags($message);
        $obj->save();

//        if ($request->get('date')) {
//            try {
//                $date = Date::createFormDate($request->get('date'));
//                if ($date && $date > Date::create()) {
//                    // Setup a reminder message
//                    $notice = \App\Db\Notice::create($obj->getModel(), $this->getConfig()->getAuthUser());
//                    $notice->noteId = $obj->getId();
//                    $notice->type = 'reminder';
//                    $notice->msgSubject = \Tk\ObjectUtil::basename($obj->fkey) . ' Reminder';
//                    $notice->body = $obj->message;
//                    $notice->save();
//
//                    if ($request->get('group') == 0) {
//                        $notice->addRecipient($this->getConfig()->getAuthUser(), $date);
//                    } else {
//                        $staffList = $this->getConfig()->getUserMapper()->findFiltered(array(
//                            'courseId' => $this->getConfig()->getCourseId(),
//                            'type' => array(
//                                \Uni\Db\User::TYPE_STAFF
//                            )
//                        ));
//                        foreach ($staffList as $user) {
//                            $notice->addRecipient($user, $date);
//                        }
//                    }
//                }
//            }catch (Exception $e) { Log::warning('Reminder Not Set. ' . $e->__toString()); }
//        }

        Alert::addSuccess('Note saved.');
        Uri::create()->redirect();
    }


    /**
     * Execute the renderer.
     * Return an object that your framework can interpret and display.
     *
     * @return null|Template|Renderer
     */
    public function show()
    {
        $template = $this->getTemplate();

        //if ($this->isReminderEnabled()) {
            //$template->setAttr('share-widget', 'data-reminder-enabled', 'true');
        //}

        $css = <<<CSS
.error { border: 1px solid #b94a48 !important; background-color: #fee !important; }
CSS;
        $template->appendCss($css);

        $js = <<<JS
jQuery(function ($) {
  
  function init() {
    var form = $(this);
    var message = form.find('textarea');

    if (form.data('reminderEnabled')) {
      form.find('.reminder').show();

      var bell = form.find('.bell');
      var date = form.find('input.notes-date');
      var clr = form.find('.btn-clr');
      var grp = form.find('.btn-grp');
      var grpInput = form.find('input[name="group"]');

      grp.on('click', function () {
        if (grpInput.val() === '0') { // set group to true
          grpInput.val(1);
          grp.find('.fa').removeClass('fa-user').addClass('fa-group');
          grp.attr('title', 'Send reminder to all staff [ON]');
        } else {
          grpInput.val(0);
          grp.find('.fa').removeClass('fa-group').addClass('fa-user');
          grp.attr('title', 'Send reminder to all staff [Off]');
        }
      });
      
      var start = new Date();
      start.setDate(start.getDate() + 1);

      date.datetimepicker({
        format: config.datepickerFormat,
        autoclose: true,
        todayBtn: false,
        todayHighlight: false,
        initialDate: start,
        startDate: start,
        minView: 2,
        maxView: 2
      });

      date.on('change', function () {
        bell.removeClass('fa-bell-slash-o').removeClass('fa-bell-o');
        if (date.val()) {
          clr.removeClass('disabled');
          grp.removeClass('disabled');
          bell.addClass('fa-bell-o');
        } else {
          clr.addClass('disabled');
          grp.addClass('disabled');
          bell.addClass('fa-bell-slash-o');
        }
      });

      clr.on('click', function () {
        date.val('');
        bell.removeClass('fa-bell-slash-o').removeClass('fa-bell-o');
        bell.addClass('fa-bell-slash-o');
        clr.addClass('disabled');
        grp.addClass('disabled');
      });
    }

    message.on('focus', function () {
      $(this).removeClass('error').tooltip('destroy');
    });

    form.on('submit', function () {
      if (message.val() === '') {
        message.tooltip('destroy')
          .data('title', 'Please enter a valid message.')
          .addClass('error').tooltip({trigger: 'manual'}).tooltip('show');
        return false;
      }
      if (form.data('reminderEnabled') && date.val()) {
        var from = date.datetimepicker('getDate').floor();
        var to = new Date().floor();
        if (from <= to) {
          alert('Reminder date must be set to a future date.');
          return false;
        }
      }
    });
  }

  $(document).on('init', '.notes-widget #notes-form', init);    // For a Dashboard with ajax call:  .find('form').trigger('init');
  $('.notes-widget #notes-form').on('init', document, init).each(init);              // For all other standard forms

});
JS;
        $template->appendJs($js);

        $template->setAttr('fkey', 'value', get_class($this->model));
        $template->setAttr('fid', 'value', $this->model->getId());

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
<div class="notes-widget">
  <div class="share-widget-notes" var="notes-table"></div>
  <div class="share-widget clearfix">
    <form class="t-tk-form" id="notes-form" var="share-widget" method="post">
      <input type="hidden" name="fkey" value="" var="fkey" />
      <input type="hidden" name="fid" value="0" var="fid" />
      <input type="hidden" name="group" value="0" />
      
      <textarea name="message" rows="2" class="form-control share-widget-textarea" placeholder="Add Notes..." tabindex="1"></textarea>
      
      <div class="share-widget-actions">
        <div class="share-widget-types pull-left" choice1="hidden">
          <div class="form-group" style="margin-bottom: 0;">
              <div class="input-group reminder" style="display:none;">
                <span class="input-group-addon"><i class="bell fa fa-bell-slash-o" title="reminder"></i></span>
                <input type="text" name="date" data-today-btn="false" class="form-control notes-date" placeholder="Add Reminder" />
                <span class="input-group-btn">
                  <button class="btn-clr btn btn-default disabled" title="Clear Reminder" type="button"><i class="fa fa-times"></i></button>
                  <button class="btn-grp btn btn-default disabled" title="Send reminder to all staff [Off]" type="button"><i class="fa fa-user"></i></button>
                </span>
              </div>
          </div>
        </div>
        
        <div class="pull-right"><button type="submit" name="notes-post" class="btn btn-primary btn-sm btn-post" tabindex="2">Post</button></div>
      </div>
    </form>
  </div>
</div>
HTML;

        return Loader::load($xhtml);
    }

//    /**
//     * @return bool
//     */
//    public function isReminderEnabled()
//    {
//        return $this->reminderEnabled;
//    }
//
//    /**
//     * @param bool $reminderEnabled
//     * @return Note
//     */
//    public function setReminderEnabled($reminderEnabled = true)
//    {
//        $this->reminderEnabled = $reminderEnabled;
//        return $this;
//    }

}