import notify from 'common/notify';

let $btn = $('[data-toggle=form-submit]');

let type = $btn.data('type');

$btn.click(function(e) {
  e.preventDefault();
  $(this).button('loading')
  $('#wave-form').ajaxSubmit({
    dataType: 'json',
    beforeSubmit: () => {
      
      let hasError = false;
      let errorMessage = '';
      
      $('[name="wave_amount[]"]').each(function() {
        let value = $(this).val();
        if (!/^([0-9])+$/i.test(value)) {
          errorMessage += '请输入正确的数量，必须为正整数';
          hasError = true;
          return false;
        }

        if(value == 0){
          errorMessage += '数量不能为零';
          hasError = true;
          return false;
        }

        let type = $(this).data("type");
        if(type == 'loss' || type == 'out'){
          let typeName = type == 'loss'?'报损':'出库';
          if(value > $(this).data('amount')){
            errorMessage += `${typeName}数量不得超过当前库存`;
            hasError = true;
            return false;
          }
        }
      });
      
      if (type == 'in') {
        $('[name="cost_price[]"').each(function() {
          let value = $(this).val();
          if (!/^([0-9])+$|^([0-9]+\.[0-9]{0,2})$/i.test(value)) {
            if (errorMessage) {
              errorMessage += ';&nbsp;&nbsp;';
            }
            errorMessage += '请输入正确的价格，最多支持两位小数';
            hasError = true;
            return false;
          }
        });
      }
      
      if (hasError) {
        notify('danger', errorMessage);
        $btn.button('reset');
        return false;
      }
      
    },
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
});


