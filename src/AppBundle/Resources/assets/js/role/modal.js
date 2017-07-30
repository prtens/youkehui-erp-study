import notify from 'common/notify';

let $form = $('#role-form');

let validator = $form.validate({
  rules: {
    'name': {
      required: true,
      remote: {
        url: $form.find('input[name=name]').data('validation'),
        type: "post",
      },
    },
  },
  messages: {
    'name': {
      required: '请输入角色名称',
    },
  },
  submitHandler: function(form) {
    let $btn = $('[data-toggle=form-submit]').button('loading');

    $.post($(form).attr('action'), $(form).serialize()).done((html) => {
      $('.modal').modal('hide');

      notify('success', '保存成功');

      setTimeout(function() {
        window.location.reload();
      }, 1000);

    }).fail(function() {
      notify('danger', '保存失败，请重试');
      $btn.button('reset');
    });
  }
});