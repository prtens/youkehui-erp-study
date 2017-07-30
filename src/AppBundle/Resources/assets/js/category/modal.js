import notify from 'common/notify';

let $form = $('#category-form');

let validator = $form.validate({
  rules: {
    'name': {
      required: true,
    },
    'code': {
      required: true,
      alphanumeric: true,
      remote: {
        url: $form.find('input[name=code]').data('validation'),
        type: "post",
      },
    },
    'seq': {
      required: true,
      digits: true,
    },
  },
  messages: {
    'name': {
      required: '请输入分类名称',
    },
    'code': {
      required: '请输入分类编码',
    },
    'seq': {
      required: '请输入显示序号',
      digits: '显示序号必须为数字',
    },
  },
  submitHandler: function(form) {
    let $btn = $('[data-toggle=form-submit]').button('loading');

    $.post($(form).attr('action'), $(form).serialize()).done((html) => {
      $('.modal').modal('hide');

      notify('success', '分类保存成功');

      // 分类有树形展开收缩功能，用replace html方法后，展开收缩功能会失效，所以用reload
      // $('.tbody').replaceWith(html);

      setTimeout(function() {
        window.location.reload();
      }, 1000);

    }).fail(function() {
      notify('danger', '分类保存失败，请重试');
      $btn.button('reset');
    });
  }
});