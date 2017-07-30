import select2 from '../../common/select2';

select2('#provider-id', {idKey: 'id', textKey: 'nickname'});

$('[data-role=item-todo-add]').on('click', function() {

  $.post($(this).data('url'), {target_id: $(this).data('goodsId'), target_type: 'goods'}, () => {
    // $(this).find('i').removeAttr('class').addClass('glyphicon glyphicon-ok disabled');
    // $(this).find('span').text('已加入');
    window.location.reload();
  });

});

$('.modal').on('hidden.bs.modal', function(e) {
  window.location.reload();
});

$('.fancybox').fancybox();
