import notify from 'common/notify';
import select2 from '../../common/select2';

let $form = $('#goods-form');

let validator = $form.validate({
  rules: {
    'category_id': {
      required: true,
    },
    'provider_id': {
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
    'sale_price': {
      required: true,
      number: true,
    },
    'cost_price': {
      required: true,
      number: true,
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
    'provider_id': {
      required: '请选择供应商',
    },
    'name': {
      required: '请输入产品名称',
    },
    'spec': {
      required: '请输入产品规格',
    },
    'unit': {
      required: '请输入单位',
    },
    'sale_price': {
      required: '请输入零售价',
    },
    'cost_price': {
      required: '请输入成本价',
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

        notify('success', '产品保存成功');

        setTimeout(function() {
          window.location.reload();
        }, 1000);
      },
      error: (data) => {
        notify('danger', `产品保存失败，请重试 ${data.responseText}`);
        $btn.button('reset');
      }
    });
    
  }
});

select2('#modal-provider-id', {idKey: 'id', textKey: 'nickname'});