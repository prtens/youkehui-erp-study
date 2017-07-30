import confirm from 'common/confirm';

$('[data-toggle=enable]').on('click', function() {

  let nickname = $(this).data('nickname');
  confirm(`确认要启用该账户“<span class='text-danger'>${nickname}</span>”吗？`, () => {
    $.post($(this).data('url'), function(html) {
      window.location.reload();
    });
  })

});
$('[data-toggle=disable]').on('click', function() {

  let nickname = $(this).data('nickname');
  confirm(`确认要禁用该账户“<span class='text-danger'>${nickname}</span>”吗？`, () => {
    $.post($(this).data('url'), function(html) {
      window.location.reload();
    });
  })

});

$('.fancybox').fancybox();