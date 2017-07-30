import confirm from 'common/confirm';

$('[data-toggle=item-delete]').on('click', function() {


  let nickname = $(this).data('nickname');
  confirm(`确认要删除该供应商“<span class='text-danger'>${nickname}</span>”吗？`, () => {
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

$('[data-toggle=item-batch-delete]').on('click', function() {

  confirm('确认要批量删除这些供应商吗？', () => {
    let ids = [];
    $('[name="id[]"]:checkbox:checked').each(function() {
      ids.push($(this).val());
    });

    $.post($(this).data('url'), {ids},function(html) {
      window.location.reload();
    });
  })

});