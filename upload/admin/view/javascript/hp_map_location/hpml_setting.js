(function ($) {
  var $root = $('#hpm-root');
  if (!$root.length) { return; }

  var saving = false;
  var FAIL = $root.data('fail');
  var SAVE = $root.data('save');

  // Success goes to a toast in the top-right corner. Errors stay in the page until dismissed.
  function toast(text) {
    var $box = $('#hpm-toasts');
    if (!$box.length) { $box = $('<div id="hpm-toasts" aria-live="polite"></div>').appendTo('body'); }
    var $t = $('<div class="hpm-toast" role="status"><i class="fa fa-check-circle"></i><span></span></div>');
    $t.find('span').text(text);
    $box.append($t);
    setTimeout(function () { $t.addClass('hpm-toast-in'); }, 10);
    var close = function () { $t.removeClass('hpm-toast-in'); setTimeout(function () { $t.remove(); }, 250); };
    $t.on('click', close);
    setTimeout(close, 3500);
  }

  function alertBox(type, text) {
    if (type === 'success') { $('#hpm-alert').empty(); toast(text); return; }
    var $a = $('<div class="alert alert-' + type + ' alert-dismissible"><i class="fa fa-exclamation-circle"></i> <span></span><button type="button" class="close" aria-label="Close">&times;</button></div>');
    $a.find('span').text(text);
    $('#hpm-alert').empty().append($a);
  }
  $(document).on('click', '#hpm-alert .close', function () { $(this).closest('.alert').remove(); });

  // Tabs
  function showTab(name) {
    $root.find('.hpm-sn [data-hpm-tab]').removeClass('hpm-on').filter('[data-hpm-tab="' + name + '"]').addClass('hpm-on');
    $root.find('.hpm-pane').removeClass('hpm-on').filter('#hpm-' + name).addClass('hpm-on');
    // Full URL: OpenCart's <base> tag would resolve a bare hash to the admin root
    try { history.replaceState(null, '', window.location.pathname + window.location.search + '#hpm-' + name); } catch (e) { /* ignore */ }
  }
  $root.on('click', '[data-hpm-tab]', function (e) { e.preventDefault(); showTab($(this).data('hpm-tab')); });

  // Language switch inside the Messages card
  function showLang(id) {
    $('#hpm-lang-seg button').removeClass('hpm-on').filter('[data-hpm-lang="' + id + '"]').addClass('hpm-on');
    $('.hpm-lang-pane').removeClass('hpm-on').filter('[data-hpm-lang-pane="' + id + '"]').addClass('hpm-on');
  }
  $root.on('click', '[data-hpm-lang]', function () { showLang($(this).data('hpm-lang')); });

  // Dirty state: the Save button stays disabled until a field changes
  function flat() {
    var out = {};
    $root.find('[data-hpm-field]').each(function () {
      var $f = $(this), name = $f.attr('name');
      out[name] = $f.is(':checkbox') ? ($f.is(':checked') ? 1 : 0) : $f.val();
    });
    return out;
  }
  var baseline = flat();
  function syncDirty() {
    var d = JSON.stringify(flat()) !== JSON.stringify(baseline);
    $('#hpm-save').prop('disabled', !d || saving);
    $('#hpm-un').prop('hidden', !d);
  }
  $root.on('input change', '[data-hpm-field]', syncDirty);

  $('#hpm-save').on('click', function () {
    if (saving) { return; }
    saving = true;
    $(this).prop('disabled', true);
    var sent = flat();
    $.ajax({ url: SAVE, type: 'POST', data: sent, dataType: 'json' })
      .done(function (r) {
        if (r.success) { alertBox('success', r.message); baseline = sent; }
        else { alertBox('danger', r.error || FAIL); }
      })
      .fail(function () { alertBox('danger', FAIL); })
      .always(function () { saving = false; syncDirty(); });
  });
})(jQuery);
