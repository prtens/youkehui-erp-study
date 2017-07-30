import notify from 'common/notify';

let $form = $('#alias-subuser-form');

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
    'password': {
      required: true,
    },
    'password_confirm': {
      equalTo: "#password"
    },
    'mobile': {
      required: true,
      remote: {
        url: $form.find('input[name=mobile]').data('validation'),
        type: "post",
      },
    },
  },
  messages: {
    'nickname': {
      required: '请输入登录账号名称',
    },
    'password': {
      required: '请输入密码',
    },
    'password_confirm': {
      equalTo: "两次密码输入不相同"
    },
    'mobile': {
      required: "请输入手机号码"
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