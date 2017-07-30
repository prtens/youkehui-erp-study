import confirm from 'common/confirm';
$('[data-toggle=item-enable]').on('click', function() {

  let nickname = $(this).data('nickname');
  confirm(`确认要启用该加盟商“<span class='text-danger'>${nickname}</span>”吗？`, () => {
    $.post($(this).data('url'), function(html) {
      window.location.reload();
    });
  })

});


$('[data-toggle=item-disable]').on('click', function() {

  let nickname = $(this).data('nickname');
  confirm(`确认要禁用该加盟商“<span class='text-danger'>${nickname}</span>”吗？`, () => {
    $.post($(this).data('url'), function(html) {
      window.location.reload();
    });
  })

});


$('.check-all').click(function() {
  $('[name="id[]"]').each(function(){
    if ($(this).is(":checked")) {
      $(this).prop("checked", false);
    } else {
      $(this).prop("checked", true);
    }
  });
});

$('[data-toggle=item-batch-disable]').on('click', function() {

  confirm('确认要批量禁用这些加盟商吗？', () => {
    let ids = [];
    $('[name="id[]"]:checkbox:checked').each(function() {
      ids.push($(this).val());
    });

    $.post($(this).data('url'), {ids},function(html) {
      window.location.reload();
    });
  })

});