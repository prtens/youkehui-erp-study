import notify from 'common/notify';

let $form = $('#provider-form');

let validator = $form.validate({
  rules: {
    'nickname': {
      required: true,
      chinese_alphanumeric: true,
      remote: {
        url: $form.find('input[name=nickname]').data('validation'),
        type: "post",
      },
    },
    'mobile': {
      required: true,
      alphanumeric: true,
      remote: {
        url: $form.find('input[name=mobile]').data('validation'),
        type: "post",
      },
    },
  },
  messages: {
    'nickname': {
      required: '请输入供应商简称',
    },
    'mobile': {
      required: '请输入联系人手机号',
    },
  },
  submitHandler: function(form) {
    let $btn = $('[data-toggle=form-submit]').button('loading');
    $(form).ajaxSubmit({
      dataType: 'json',
      success: (result) => {
        $('.modal').modal('hide');

        notify('success', '保存成功');

        setTimeout(function() {
          window.location.reload();
        }, 1000);
      },
      error: (data) => {
        notify('danger', `保存失败，请重试 ${data.responseText}`);
        $btn.button('reset');
      }
    });
    
  }
});