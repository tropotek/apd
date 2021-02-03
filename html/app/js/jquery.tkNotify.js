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
      markAlert: 'doMarkAlert',            // h => userHash, d = true/false
      getNoticeList: 'doGetNoticeList',     // h => userHash
      //refreshMins: 5,                       // Number of minutes to refresh (5-10 min recommended)
      refreshMins: 1,                       // Number of minutes to refresh (5-10 min recommended)
      noticeTpl:
        '  <li class="tk-notify dropdown d-none d-md-block">\n' +
        '    <a class="btn dropdown-toggle dropdown-nocaret" data-toggle="dropdown"><i class="fa fa-bell"></i><span class="count" style="display:none;">0</span></a>\n' +
        '    <ul class="dropdown-menu notifications-list">\n' +
        '      <li class="pointer"><div class="pointer-inner"><div class="arrow"></div></div></li>\n' +
        '      <li class="item-header">You have <span>0</span> notifications</li>\n' +
        '      <li class="item tpl">\n' +
        '        <a href="javascript:;">\n' +
        '          <i class="fa fa-comment"></i>\n' +
        '          <span class="content">Default comment on system</span>\n' +
        '          <span class="time"><i class="fa fa-clock-o"></i><span>13 min.</span></span>\n' +
        '        </a>\n' +
        '      </li>\n' +
//        '      <li class="item-footer"><a href="#">View all notifications</a></li>\n' +
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
    /**
     *
     * @param params
     * @param callback
     * @private
     */
    var _markAlert = function(params, callback) {
      var url = plugin.settings.ajax + plugin.settings.markAlert;
      var p = $.extend({
        h : config.userHash,
        crumb_ignore: 'crumb_ignore',
        nolog: 'nolog'
        //d: 1,
        //nid: n.id,
      }, params);
      $.get(url, params, function (data) {
        if (callback) callback.apply(this, [data]);
      }, 'json');
    };
    /**
     *
     * @param params
     * @param callback
     * @private
     */
    var _markViewed = function(params, callback) {
      var url = plugin.settings.ajax + plugin.settings.markViewed;
      var p = $.extend({
        h : config.userHash,
        crumb_ignore: 'crumb_ignore',
        nolog: 'nolog'
        //d: 1,
        //nid: n.id,
      }, params);
      $.get(url, params, function (data) {
        if (callback) callback.apply(this, [data]);
      }, 'json');
    };

    var _updateTotals = function(countEl) {
      var uv = countEl.closest('li.tk-notify').find('li.item:not(.viewed)').length;
      countEl.text(uv).css('display', 'block');
      if (uv == 0)
        countEl.css('display', 'none');
    };

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
          _markAlert({d: 1}); // mark all user messages as alerted done so alert only repeats on new messages
        } catch (e) { console.error('===> ' + e.message); }
      }
    }

    plugin.refresh = function() {
      var params = {
        h : config.userHash,
        crumb_ignore: 'crumb_ignore',
        //nolog: 'nolog'
      };
      var url = plugin.settings.ajax + plugin.settings.getNoticeList;
      $.get(url, params, function (data) {
        var tpl = $(plugin.settings.noticeTpl);
        var btnEl = tpl.find('a.btn.dropdown-toggle');
        var countEl = tpl.find('.count');
        var totalEl = tpl.find('.item-header span');
        var footerEl = tpl.find('.item-footer');
        var soundEl = tpl.find('audio.alert-new');
        var itemTpl = tpl.find('.item.tpl');


        // NOTE: user must click the document (Maybe add an audio toggle somewhere on the page?)
        soundEl.attr('src', plugin.settings.sound);
        if (data.unAlert > 0) {
          newMessage = true;
          plugin.playAlertNew(soundEl);
        }

        // btnEl.on('click', function () {
        //   var newEls = $(this).closest('ul.nav').find('li.item:not(.alerted)');
        //   if (newEls.length == 0) return;
        //   //_updateTotals(countEl);
        // });

        //var newItems = 0;
        // Add items
        $.each(data.list, function (i, n) {
          var li = itemTpl.clone().removeClass('tpl');
          li.find('a > .fa').attr('class', n.icon);
          li.find('.content').text(n.subject);
          li.find('.time span').text(n.time);

          if (n.isAlert) {
            li.addClass('alerted');
          }
          if (n.isViewed) {
            li.addClass('viewed');
          }

          li.insertBefore(itemTpl);
          li.on('click', function () {
            var markViewed = false;
            if (n.type == 'Request::create') {
              var rows = $('.tk-request-table tr[data-obj-id='+n.fid+']');
              if (rows.length) {
                rows.get(0).scrollIntoView({behavior: "smooth", block: "center"});
                rows.effect("highlight", {color: '#6c757d'}, 2500);
                markViewed = true;
              }
            } else if (n.type == 'PathCase::create') {
              var rows = $('.tk-pathCase-table tr[data-obj-id='+n.fid+']');
              if (rows.length) {
                rows.get(0).scrollIntoView({behavior: "smooth", block: "center"});
                rows.effect("highlight", {color: '#6c757d'}, 2500);
                markViewed = true;
              }
            }
            if (markViewed) {
              _markViewed({d: 1, nid: n.id});
              li.addClass('viewed');
            }
            _updateTotals(countEl);
          });
        });
        itemTpl.remove();

        totalEl.text(data.total);

        // Update total unread counts
        _updateTotals(countEl);

        // TODO: Add a url to a page that makes sense for the message
        // TODO: add a view all notice's page/manager???
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
