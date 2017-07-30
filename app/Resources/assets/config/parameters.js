const parameters = {
  paths: {
    globalAssets: 'app/Resources/assets/',
    happypackTempDir: 'app/cache/dev/',
    bundles: 'src/',
    libs: 'app/Resources/assets/libs/',
    output: 'web/static/', //webpack file output path
    publicPath: '/static/',  //relative to website domain
  },
  bundles: [ //register php bundles
    'AppBundle',
  ],
  libs: { // path realtive to globalAssets path
    'vendor': ['vendor.js'], //can be a js file
    'ckeditor': ['ckeditor'], //or can be a node module name
    'fix-ie': ['html5shiv', 'respond-js'],
    'jquery-validation': ['js/jquery-validation.js'],
    'jquery-form': ['jquery-form'],
    'jquery-fancybox': ['jquery-plugins/jquery-fancybox/jquery-fancybox.js'],
    'jquery-treegrid': ['jquery-plugins/jquery-treegrid/jquery-treegrid.js'],
    'select2': ['jquery-plugins/select2/select2.js'],
    'bootstrap-datetime-picker': ['jquery-plugins/bootstrap-datetime-picker/bootstrap-datetime-picker.js'],
  },
  noParseDeps: [ //these node modules will use a dist version to speed up compilation
    'jquery/dist/jquery.js',
    'bootstrap/dist/js/bootstrap.js',
    'admin-lte/dist/js/app.js',
    'jquery-validation/dist/jquery.validate.js',
    'jquery-form/jquery.form.js',
    'fancybox/dist/js/jquery.fancybox.js',
    'bootstrap-notify/bootstrap-notify.js',
    // The `.` will auto be replaced to `-` for compatibility 
    'respond.js/dest/respond.src.js',
    'jquery-slimscroll/jquery.slimscroll.js',
    'bootstrap-datetime-picker/js/bootstrap-datetimepicker.js',
  ],
};

export default parameters;