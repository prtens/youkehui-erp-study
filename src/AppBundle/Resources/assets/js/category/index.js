import confirm from 'common/confirm';
import notify from 'common/notify';

$('.list-table .td.name>i').click(function() {
    var $parentNode = $(this).closest('.row');
    if ($parentNode.hasClass('row-collapse')) {
        $parentNode.removeClass('row-collapse').addClass('row-expand');
        $(this).removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
        $parentNode.next('ul.list-table').find('>li').slideDown();
    } else if ($parentNode.hasClass('row-expand')) {
        $parentNode.removeClass('row-expand').addClass('row-collapse');
        $(this).removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
        $parentNode.next('ul.list-table').find('>li').slideUp();
    }
    
});

$('[data-toggle=item-delete]').on('click', function() {

  let name = $(this).data('name');
  confirm(`确认要删除该分类“<span class='text-danger'>${name}</span>”吗？`, () => {
    $.post($(this).data('url'), function(json) {
      if(json.data == true){
        window.location.reload();
      }else{
        $('.modal').modal('hide');
        notify('danger', json.data);
      }
      });
  }, {confirm: {level: 'danger', text: '删除'}});

});