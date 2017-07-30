import notify from 'common/notify';
import select2 from '../../common/select2';

let $form = $('#stock-form');

let validator = $form.validate({
  rules: {
    'goods_id': {
      required: true,
    },
    'min_amount': {
      required: true,
      number: true,
    },
  },
  messages: {
    'goods_id': {
      required: '请选择商品',
    },
    'min_amount': {
      required: '请输入下限预警',
    },
  },
  submitHandler: function (form) {
    let $btn = $('[data-toggle=form-submit]').button('loading');
    $(form).ajaxSubmit({
      beforeSubmit: () => {
        
        let hasError = false;
        let errorMessage = '';
        
        if (hasError) {
          notify('danger', errorMessage);
          $btn.button('reset');
          return false;
        }
        
      },
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

select2('#goods-search', {idKey: 'id', textKey: 'name'});