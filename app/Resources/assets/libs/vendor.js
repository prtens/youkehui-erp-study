import './vendor.less';

import 'jquery';
import 'bootstrap';
import 'common/script';
import 'bootstrap-notify';
import notify from 'common/notify';

$(document).ajaxError(function (event, jqxhr, settings, exception) {

  let json = jQuery.parseJSON(jqxhr.responseText);
  if (json.error_code == 0) {
    return;
  }
  
  notify('danger', json.message);
});

$(document).ajaxSend(function(a, b, c) {
  if (c.type == 'POST') {
    b.setRequestHeader('X-CSRF-Token',
      $('meta[name=csrf-token]').attr('content'));
  }
});

$('.modal').on('show.bs.modal', function(e) {
  let url = $(e.relatedTarget).data('url');
  $(this).data('url', url);
  $(this).load(url);
});

$('.modal').on('hidden.bs.modal', function(e) {
  $(this).html('');
});

$('.modal').on('click', '[data-toggle=form-submit]', function(e) {
  e.preventDefault();
  $($(this).data('target')).submit();
});

$(".modal").on('click', '.pagination a', function(e) {
  e.preventDefault();
  let $modal = $(e.delegateTarget);
  $modal.load($(this).attr('href'));
});