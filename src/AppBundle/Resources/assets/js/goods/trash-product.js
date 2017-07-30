import confirm from 'common/confirm';
import select2 from '../../common/select2';

$('[data-role=undelete]').on('click', function() {

  let name = $(this).data('name');
  confirm(`确认要还原该产品“<span class='text-danger'>${name}</span>”吗？`, () => {
    $.post($(this).data('url'), function(html) {
      window.location.reload();
    });
  })

});

$('[data-role="true-delete"]').on('click', function() {
  let name = $(this).data('name');
  confirm(`确认要彻底删除该产品“<span class='text-danger'>${name}</span>”吗？`, () => {
    $.post($(this).data('url'), function() {
      window.location.reload();
    });
  })

});

select2('#provider-id', {idKey: 'id', textKey: 'nickname'});