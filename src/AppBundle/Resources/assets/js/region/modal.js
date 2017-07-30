import notify from 'common/notify';

let $form = $('#region-form');

let validator = $form.validate({
  rules: {
    'name': {
      required: true,
    },
    'seq': {
      required: true,
      digits: true,
    },
  },
  messages: {
    'name': {
      required: '请输入区域名称',
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

      notify('success', '区域保存成功');

      setTimeout(function() {
        window.location.reload();
      }, 1000);

    }).fail(function() {
      notify('danger', '区域保存失败，请重试');
      $btn.button('reset');
    });
  }
});