/*
 * Plugin: tkNotify
 * Version: 1.0
 * Date: 11/05/17
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @see http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 * @source http://stefangabos.ro/jquery/jquery-plugin-boilerplate-revisited/
 */

/**
 * TODO: Change every instance of "tkNotify" to the name of your plugin!
 * Description:
 *   {Add a good description so you can identify the plugin when reading the code.}
 *
 * <code>
 *   $(document).ready(function() {
 *     // attach the plugin to an element
 *     $('#element').tkNotify({'foo': 'bar'});
 *
 *     // call a public method
 *     $('#element').data('tkNotify').foo_public_method();
 *
 *     // get the value of a property
 *     $('#element').data('tkNotify').settings.foo;
 *   
 *   });
 * </code>
 */
;(function($) {
  var tkNotify = function(element, options) {
    var plugin = this;
    plugin.settings = {};
    var $element = $(element);

    // plugin settings
    var defaults = {
      sound: config.siteUrl + '/html/app/js/noticeAlert.mp3',
      soundEnabled: true,
      ajax: config.siteUrl + '/ajax/notice/',                // Append one of the below actions for a complete ajax url
      markRead: 'doMarkRead',               // h => userHash, d = true/false
      markViewed: 'doMarkViewed',           // h => userHash, d = true/false
      getNoticeList: 'doGetNoticeList',     // h => userHash
      refreshMins: 5,                       // Number of minutes to refresh (5-10 min recommended)
      noticeTpl:
        '  <li class="tk-notify dropdown d-none d-md-block">\n' +
        '    <a class="btn dropdown-toggle dropdown-nocaret" data-toggle="dropdown"><i class="fa fa-bell"></i><span class="count" style="display:none;">0</span></a>\n' +
        '    <ul class="dropdown-menu notifications-list">\n' +
        '      <li class="pointer"><div class="pointer-inner"><div class="arrow"></div></div></li>\n' +
        '      <li class="item-header">You have <span>0</span> new notifications</li>\n' +
        '      <li class="item tpl">\n' +
        '        <a href="#">\n' +
        '          <i class="fa fa-comment"></i>\n' +
        '          <span class="content">Default comment on system</span>\n' +
        '          <span class="time"><i class="fa fa-clock-o"></i><span>13 min.</span></span>\n' +
        '        </a>\n' +
        '      </li>\n' +
        '      <li class="item-footer"><a href="#">View all notifications</a></li>\n' +
        '    </ul>\n' +
        '    <audio class="alert-new" src="#"></audio>' +
        '  </li>',
      onNewNotice: function() {}
    };

    // plugin vars
    var newMessage = false;

    // constructor method
    plugin.init = function() {
      plugin.settings = $.extend({}, defaults, $element.data(), options);

      plugin.refresh();

    };  // END init()

    // private methods
    //var _foo_private_method = function() { };

    // public methods
    plugin.enableSound = function(b) {
      if (b === true) {
        plugin.settings.soundEnabled = true;
      } else {
        plugin.settings.soundEnabled = false;
      }
    }

    plugin.playAlertNew = async function(soundEl) {
      if (newMessage && soundEl.length && plugin.settings.soundEnabled) {
        try {
          soundEl.get(0).muted = false;
          await soundEl.get(0).play();
          newMessage = false;
        } catch (e) { console.error('===> ' + e.message); }
      }
    }


    plugin.refresh = function() {
      var params = {h : config.userHash, crumb_ignore: 'crumb_ignore', nolog: 'nolog'};
      var url = plugin.settings.ajax + plugin.settings.getNoticeList;
      $.get(url, params, function (data) {
        var tpl = $(plugin.settings.noticeTpl);
        var countEl = tpl.find('.count');
        var totalEl = tpl.find('.item-header span');
        var footerEl = tpl.find('.item-footer');
        var soundEl = tpl.find('audio.alert-new');
        var itemTpl = tpl.find('.item.tpl');

        // Setup alert sound
        // NOTE: user must click the document (Maybe add an audio toggle somewhere on the page?)
        soundEl.attr('src', plugin.settings.sound);
        var orgTotal = parseInt($element.find('.count').text());
        if (data.unread > orgTotal) {
        //if (true) {
          newMessage = true;
          plugin.playAlertNew(soundEl);
        }

        // Update total unread counts
        countEl.text(data.unread).css('display', 'block');
        totalEl.text(data.unread);
        if (data.unread == 0)
          countEl.css('display', 'none');

        $.each(data.list, function (i, n) {
          var li = itemTpl.clone();
          li.find('a > .fa').attr('class', n.icon);
          li.find('.content').text(n.subject);
          li.find('.time span').text(n.time);
          li.insertBefore(itemTpl);
        });
        itemTpl.remove();

        // TODO: Add an event to mark notices viewed once the bell menu dropdown has been clicked

        // TODO: Add a url to a page that makes sense for the message

        // TODO: add a view all notice's page/manager???

        // Mark messages read/unread for red icon to show

        $element.empty().append(tpl.find(' > *'));
        setTimeout(plugin.refresh, 1000*60*plugin.settings.refreshMins);
      }, 'json');

    };

    // call the "constructor" method
    plugin.init();
  };

  // add the plugin to the jQuery.fn object
  $.fn.tkNotify = function(options) {
    return this.each(function() {
      if (undefined === $(this).data('tkNotify')) {
        var plugin = new tkNotify(this, options);
        $(this).data('tkNotify', plugin);
      }
    });
  }

})(jQuery);
