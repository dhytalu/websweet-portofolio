;(function($){
  $(function(){
    var $overlay = $('#wssp-overlay');
    var $text = $overlay.find('.wssp-overlay__text');
    var $bar = $overlay.find('.wssp-progress__fill');
    var $label = $overlay.find('.wssp-progress__label');
    var pollTimer = null;

    function showOverlay(message){
      if(message){ $text.text(message); }
      $overlay.addClass('active').attr('aria-hidden','false');
    }

    function hideOverlay(){
      $overlay.removeClass('active').attr('aria-hidden','true');
      $bar.css('width', '0%');
      $label.text('0%');
      $text.text('');
      if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    }

    function startPolling(){
      pollTimer = setInterval(function(){
        $.ajax({
          url: ajaxurl,
          data: { action: 'wssp_get_progress' },
          method: 'GET'
        }).done(function(resp){
          if (!resp || !resp.success || !resp.data) return;
          var d = resp.data;
          var pct = Math.max(0, Math.min(100, parseInt(d.percent || 0, 10)));
          $bar.css('width', pct + '%');
          $label.text(pct + '%');
          if (d.message) { $text.text(d.message); }
          if (d.status === 'done' || d.status === 'error') {
            clearInterval(pollTimer); pollTimer = null;
            if (d.status === 'error') {
              // biarkan overlay, teks sudah menampilkan pesan error
            } else {
              // beri waktu sebentar, lalu reload untuk menampilkan notices
              setTimeout(function(){ window.location.reload(); }, 600);
            }
          }
        });
      }, 600);
    }

    // Tampilkan overlay saat form submit berdasarkan nilai wssp_action
    $(document).on('submit', 'form', function(e){
      var $form = $(this);
      var action = $form.find('input[name="wssp_action"]').val();
      if (action === 'import_posts') {
        e.preventDefault();
        var nonce = $form.find('input[name="wssp_nonce_field"]').val();
        var forceFull = $form.find('input[name="wssp_force_full"]').is(':checked') ? 1 : 0;
        showOverlay('Menyiapkan import...');
        startPolling();
        $.ajax({
          url: ajaxurl,
          method: 'POST',
          data: {
            action: 'wssp_import_posts_ajax',
            wssp_nonce_field: nonce,
            wssp_force_full: forceFull
          }
        }).done(function(resp){
          if (!resp || !resp.success) {
            $text.text((resp && resp.data && resp.data.message) ? resp.data.message : 'Import gagal');
            $bar.css('width', '100%');
            $label.text('100%');
          } else {
            $text.text(resp.data && resp.data.message ? resp.data.message : 'Import selesai');
          }
        }).fail(function(){
          $text.text('Terjadi kesalahan jaringan saat import');
        });
      } else if(action === 'import_categories') {
        e.preventDefault();
        var nonceC = $form.find('input[name="wssp_nonce_field"]').val();
        showOverlay('Menyiapkan import kategori...');
        startPolling();
        $.ajax({
          url: ajaxurl,
          method: 'POST',
          data: {
            action: 'wssp_import_categories_ajax',
            wssp_nonce_field: nonceC
          }
        }).done(function(resp){
          if (!resp || !resp.success) {
            $text.text((resp && resp.data && resp.data.message) ? resp.data.message : 'Import kategori gagal');
            $bar.css('width', '100%');
            $label.text('100%');
          } else {
            $text.text(resp.data && resp.data.message ? resp.data.message : 'Import kategori selesai');
          }
        }).fail(function(){
          $text.text('Terjadi kesalahan jaringan saat import kategori');
        });
      } else if(action === 'clear_cache') {
        showOverlay('Membersihkan cache...');
      }
    });

    // Jika tombol submit di dalam form ditekan, overlay juga ditampilkan
    $(document).on('click', 'form input[type="submit"], form button[type="submit"]', function(){
      var $form = $(this).closest('form');
      var action = $form.find('input[name="wssp_action"]').val();
      if(action === 'import_posts') {
        // biarkan handler submit di atas yang menangani AJAX & overlay
      } else if(action === 'import_categories') {
        showOverlay('Mengimpor kategori...');
      } else if(action === 'clear_cache') {
        showOverlay('Membersihkan cache...');
      }
    });
  });
})(jQuery);