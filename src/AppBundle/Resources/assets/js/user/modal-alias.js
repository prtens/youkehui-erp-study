import notify from 'common/notify';

let $form = $('#alias-form');

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
      alphanumeric: true,
      remote: {
        url: $form.find('input[name=mobile]').data('validation'),
        type: "post",
      },
    },
    'region_id': {
      required: true,
    },
  },
  messages: {
    'nickname': {
      required: '请输入登录账号名称，推荐输入加盟商简称',
    },
    'password': {
      required: '请输入密码',
    },
    'password_confirm': {
      equalTo: "两次密码输入不相同"
    },
    'mobile': {
      required: '请输入联系人手机号',
    },
    'region_id': {
      required: '请选择区域',
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