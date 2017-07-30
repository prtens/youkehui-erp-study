import notify from 'common/notify';

let $form = $('#goods-form');

let validator = $form.validate({
  rules: {
    'category_id': {
      required: true,
    },
    'name': {
      required: true,
    },
    'spec': {
      required: true,
    },
    'unit': {
      required: true,
    },
    'min_amount': {
      required: true,
      digits: true,
    },
  },
  messages: {
    'category_id': {
      required: '请选择分类',
    },
    'name': {
      required: '请输入设备名称',
    },
    'spec': {
      required: '请输入设备规格',
    },
    'unit': {
      required: '请输入单位',
    },
    'min_amount': {
      required: '请输入下限预警',
    },
  },
  submitHandler: function(form) {
    let $btn = $('[data-toggle=form-submit]').button('loading');
    $(form).ajaxSubmit({
      dataType: 'json',
      success: (result) => {
        $('.modal').modal('hide');

        notify('success', '设备保存成功');

        setTimeout(function() {
          window.location.reload();
        }, 1000);
      },
      error: (data) => {
        notify('danger', `设备保存失败，请重试 ${data.responseText}`);
        $btn.button('reset');
      }
    });
    
  }
});
