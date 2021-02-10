

jQuery(function($) {

  //
  project_core.initTinymce();
  project_core.initCodemirror();

  if ($.fn.tkNotify !== undefined) {
    $('li.tk-notify').tkNotify();
  }

  // Add some javascript to manage the toggling of sound for the menu notifications
  $('#user-left-box .user-box .status').on('click', function () {
      if ($(this).find('.fa').is('.fa-volume-up')) {
        $(this).find('.fa').removeClass('fa-volume-up').addClass('fa-volume-down').css('color', '#e84e40');
        $(this).find('span').text('Audio Off');
        $('li.tk-notify').data('tkNotify').enableSound(false);
      } else {
        $(this).removeClass('default');
        $(this).find('.fa').removeClass('fa-volume-down').addClass('fa-volume-up').css('color', '#8bc34a');
        $(this).find('span').text('Audio On');
        $('li.tk-notify').data('tkNotify').enableSound(true);
      }
  });
  // Take care of the initial click on the body to enable the sound automatically
  $('body').on('click', function () {
    var st = $('#user-left-box .user-box .status');
    if (st.is('.default') && st.find('.fa').is('.fa-volume-down')) {
      st.removeClass('default');
      st.find('.fa').removeClass('fa-volume-down').addClass('fa-volume-up').css('color', '#8bc34a');
      st.find('span').text('Audio On');
      $('li.tk-notify').data('tkNotify').enableSound(true);
    }
  });

  // Detect if the browser is able to play an audio file right now
  async function canAutoPlay() {
    try {
      var audio = $('<audio src="' + config.siteUrl + '/html/app/js/noticeAlert.mp3' + '" style="display: none;" />');
      $('body').append(audio);
      audio.get(0).volume = 0;
      await audio.get(0).play();
      audio.remove();
      return true;
    } catch (e) { console.log(e.message); }
    return false;
  }

  if (canAutoPlay()) {
    var st = $('#user-left-box .user-box .status');
    if (st.is('.default') && st.find('.fa').is('.fa-volume-down')) {
      st.removeClass('default');
      st.find('.fa').removeClass('fa-volume-down').addClass('fa-volume-up').css('color', '#8bc34a');
      st.find('span').text('Audio On');
      $('li.tk-notify').data('tkNotify').enableSound(true);
    }
  }

});





