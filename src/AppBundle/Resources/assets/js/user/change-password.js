import notify from 'common/notify';

let $form = $('#change-password-form');

let validator = $form.validate({
  rules: {
    'password': {
      required: true,
    },
    'password_confirm': {
      equalTo: "#password"
    },
  },
  messages: {
    'password': {
      required: '请输入新密码',
    },
    'password_confirm': {
      equalTo: "两次密码输入不相同"
    },
  },
  submitHandler: function(form) {
    let $btn = $('[data-toggle=form-submit]').button('loading');
    $(form).ajaxSubmit({
      dataType: 'json',
      success: (result) => {
        $('.modal').modal('hide');

        notify('success', '修改成功');

        setTimeout(function() {
          window.location.reload();
        }, 1000);
      },
      error: (data) => {
        notify('danger', `修改失败，请重试 ${data.responseText}`);
        $btn.button('reset');
      }
    });
    
  }
});