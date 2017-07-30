$('[data-toggle=item-delete]').on('click', function() {
  $.post($(this).data('url'), () => {
    $(this).parents('tr').remove();
  });
});

$('[data-toggle=record-create]').on('click', function() {
  let $modal = $('#modal');
  $modal.load($(this).data('url'));
});

