const confirm = (message, confirmCallback, btn={}) => {
  
  let btnConfig = {
    cancel: {level: 'default', text:'取消'},
    confirm: {level: 'primary', text:'确认'},
  }
  
  Object.assign(btnConfig, btn);
  
  let html = `<div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title">提示</h4>
                  </div>
                  <div class="modal-body">
                    ${message}
                  </div>
                  <div class="modal-footer">
                      <a class="btn btn-${btnConfig.cancel.level}" data-toggle="cancel">${btnConfig.cancel.text}</a>
                      <a class="btn btn-${btnConfig.confirm.level}" data-toggle="confirm">${btnConfig.confirm.text}</a>
                  </div>
                </div>
              </div>`;

  let $modal = $('.modal');
  $modal.html(html);
  $modal.modal('show');
  
  $modal.delegate('[data-toggle=cancel]', 'click', function() {
    $modal.modal('hide');
    $modal.undelegate();
  });
  
  $modal.delegate('[data-toggle=confirm]', 'click', function() {
    confirmCallback && confirmCallback();
    $modal.modal('hide');
    $modal.undelegate();
  });
};

export default confirm;