const select2 = (cssSelector, options = {}) => {
  options = Object.assign({
    dataFn: (term, page) => {
      return {
          keyword: term,
      };
    },
    resultsFn: (data, page) => {
      let results = [];
      data.forEach((item, i) => {
        results[i] = item;
        results[i]['id'] = options.idKey ? item[options.idKey] : item['id'];
        results[i]['text'] = options.textKey ? item[options.textKey] : item['text'];
      });
      return { results };
    }
  }, options);
  
  $(cssSelector).select2({
    placeholder: $(cssSelector).attr('placeholder') || '',
    // minimumInputLength: 1,
    ajax: {
      url: $(cssSelector).data('url'),
      dataType: 'json',
      quietMillis: 250,
      data: options.dataFn,
      results: options.resultsFn,
      cache: true
    },
    initSelection: (element, callback) => {
      let id = $(element).val();
      if (id) {
        $.ajax($(cssSelector).data('url'), {
            dataType: 'json',
            data: {id: id},
        }).done((data) => {
          let result = data[0];
          if (result) {
            callback({id: result[options.idKey], text: result[options.textKey]});
          }
        });
      }
    },
  });
}

export default select2;