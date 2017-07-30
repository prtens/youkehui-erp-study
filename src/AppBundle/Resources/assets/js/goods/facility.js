import confirm from 'common/confirm';
import notify from 'common/notify';

$('[data-toggle=item-delete]').on('click', function() {
  let name = $(this).data('name');
  confirm(`确认要删除该设备“<span class='text-danger'>${name}</span>”吗？`, () => {
    $.post($(this).data('url'), function(json) {
      if(json.error_code == 0){
        window.location.reload();
      }else {
        notify('danger', json.message);
      }
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


  if($('[name="id[]"]:checkbox:checked').length == 0){
    return;
  }
  let names = [];
  $('[name="id[]"]:checkbox:checked').each(function() {
    names.push("“<span class='text-danger'>"+$(this).data('name')+"</span>”");
  });
  confirm(`确认要批量删除${names.join(",")}这些设备吗？`, () => {
    let ids = [];
    $('[name="id[]"]:checkbox:checked').each(function() {
      ids.push($(this).val());
    });

    $.post($(this).data('url'), {ids},function(html) {
      window.location.reload();
    });
  })

});



$('.fancybox').fancybox();