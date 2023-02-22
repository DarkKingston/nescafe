/**
 * @file
 * Plugin definition and settings.
 */
'use strict';

CKEDITOR.plugins.add('responsivearea', {
  icons: 'responsivearea',
  init: pluginInit
});

function pluginInit(editor) {
  editor.responsivearea_path = this.path;

  editor.ui.addButton('AddResponsiveArea', {
    label: Drupal.t('Insert a responsive area'),
    command: 'beResponsive',
    icon: this.path + 'icons/responsivearea.png'
  });


  editor.addCommand('beResponsive', {
    exec: function (editor, data) {
      editor.openDialog('AddResponsiveAreaDialog' + editor.name);
    }
  });

  CKEDITOR.dialog.add('AddResponsiveAreaDialog' + editor.name, beResponsive);
}

function beResponsive(editor, data) {
  var custom_theme_path = drupalSettings.active.theme_path;
  return {
    title: 'Responsive Areas',
    minWidth: 400,
    minHeight: 200,
    contents: [
      {
        id: 'tab-basic',
        label: 'Basic Settings',
        elements: [
          {
            type: 'radio',
            id: 'layout',
            label: '<p><strong>Please choose the layout you want :</strong></p><br />',
            items: [
              ['<br /><small>1 area <br />100%</small><br /><br /><img src="' + editor.responsivearea_path + '/images/1_100.png" />', '1_100'],
              ['<br /><small>2 areas <br />50% each</small><br /><br /><img src="' + editor.responsivearea_path + '/images/2_50_50.png" />', '2_50_50'],
              ['<br /><small>2 areas <br />75% 25%</small><br /><br /><img src="' + editor.responsivearea_path + '/images/2_75_25.png" />', '2_75_25'],
              ['<br /><small>2 areas <br />25% 75%</small><br /><br /><img src="' + editor.responsivearea_path + '/images/2_25_75.png" />', '2_25_75'],
              ['<br /><small>2 areas <br />33% 66%</small><br /><br /><img src="' + editor.responsivearea_path + '/images/2_33_66.png" />', '2_33_66'],
              ['<br /><small>2 areas <br />66% 33%</small><br /><br /><img src="' + editor.responsivearea_path + '/images/2_66_33.png" />', '2_66_33'],
              ['<br /><small>3 areas <br />33% each</small><br /><br /><img src="' + editor.responsivearea_path + '/images/3_33_34_33.png" />', '3_33_34_33'],
              ['<br /><small>3 areas <br />25% 50% 25%</small><br /><br /><img src="' + editor.responsivearea_path + '/images/3_25_50_25.png" />', '3_25_50_25'],
              ['<br /><small>3 areas <br />25% 25% 50%</small><br /><br /><img src="' + editor.responsivearea_path + '/images/3_25_25_50.png" />', '3_25_25_50'],
              ['<br /><small>3 areas <br />50% 25% 25%</small><br /><br /><img src="' + editor.responsivearea_path + '/images/3_50_25_25.png" />', '3_50_25_25'],
              ['<br /><small>4 areas <br />25% each</small><br /><br /><img src="' + editor.responsivearea_path + '/images/4_25_25_25_25.png" />', '4_25_25_25_25'],
              ['<br /><small>5 areas <br />20% each</small><br /><br /><img src="' + editor.responsivearea_path + '/images/5_20_20_20_20_20.png" />', '5_20_20_20_20_20']
            ],
            style: 'display: block;text-align:center',
            default: '2_50_50'
          }
        ]
      }
    ],
    onOk: function () {
      var dialog = this;
      var mode = dialog.getValueOf('tab-basic', 'layout');
      var tpl = responsiveness_get_template(mode);
      if (tpl !== "") {
        editor.insertHtml(tpl);
      }
    }
  };
}

function responsiveness_get_template(tpl) {
  'use strict';
  var grid = "";
  switch (tpl) {
    case '1_100':
      grid = '<div class="ckeditor-col-container clearfix row">';
      grid += '<div class="grid-12 col-md-12 col-sm-12 col-xs-12 col-lg-12 first-col"><p></p></div>';
      grid += '</div><br />';
      break;

    case '2_50_50':
      grid = '<div class="ckeditor-col-container clearfix row">';
      grid += '<div class="grid-6 sixcol first-col col-md-6 col-sm-6 col-xs-6 col-lg-6"><p></p></div>';
      grid += '<div class="grid-6 sixcol last-col col-md-6 col-sm-6 col-xs-6 col-lg-6"><p></p></div>';
      grid += '</div><br />';
      break;

    case '2_75_25':
      grid = '<div class="ckeditor-col-container clearfix row">';
      grid += '<div class="grid-8 eightcol first-col col-md-9 col-sm-9 col-xs-9 col-lg-9"><p></p></div>';
      grid += '<div class="grid-4 fourcol last-col col-md-3 col-sm-3 col-xs-3 col-lg-3"><p></p></div>';
      grid += '</div><br />';
      break;

    case '2_25_75':
      grid = '<div class="ckeditor-col-container clearfix row">';
      grid += '<div class="grid-4 fourcol first-col col-md-3 col-sm-3 col-xs-3 col-lg-3"><p></p></div>';
      grid += '<div class="grid-8 eightcol last-col col-md-9 col-sm-9 col-xs-9 col-lg-9"><p></p></div>';
      grid += '</div><br />';
      break;

    case '2_33_66':
      grid = '<div class="ckeditor-col-container clearfix row">';
      grid += '<div class="grid-5 fivecol first-col col-md-4 col-sm-4 col-xs-4 col-lg-4"><p></p></div>';
      grid += '<div class="grid-7 sevencol last-col col-md-8 col-sm-8 col-xs-8 col-lg-8"><p></p></div>';
      grid += '</div><br />';
      break;

    case '2_66_33':
      grid = '<div class="ckeditor-col-container clearfix row">';
      grid += '<div class="grid-7 sevencol first-col col-md-8 col-sm-8 col-xs-8 col-lg-8"><p></p></div>';
      grid += '<div class="grid-5 fivecol last-col col-md-4 col-sm-4 col-xs-4 col-lg-4"><p></p></div>';
      grid += '</div><br />';
      break;

    case '3_33_34_33':
      grid = '<div class="ckeditor-col-container clearfix row">';
      grid += '<div class="grid-4 fourcol first-col col-md-4 col-sm-4 col-xs-4 col-lg-4"><p></p></div>';
      grid += '<div class="grid-4 fourcol col-md-4 col-sm-4 col-xs-4 col-lg-4"><p></p></div>';
      grid += '<div class="grid-4 fourcol last-col col-md-4 col-sm-4 col-xs-4 col-lg-4"><p></p></div>';
      grid += '</div><br />';
      break;

    case '3_25_50_25':
      grid = '<div class="ckeditor-col-container clearfix row">';
      grid += '<div class="grid-3 threecol first-col col-md-3 col-sm-3 col-xs-3 col-lg-3"><p></p></div>';
      grid += '<div class="grid-6 sixcol col-md-6 col-sm-6 col-xs-6 col-lg-6"><p></p></div>';
      grid += '<div class="grid-3 threecol last-col col-md-3 col-sm-3 col-xs-3 col-lg-3"><p></p></div>';
      grid += '</div><br />';
      break;

    case '3_25_25_50':
      grid = '<div class="ckeditor-col-container clearfix row">';
      grid += '<div class="grid-3 threecol first-col col-md-3 col-sm-3 col-xs-3 col-lg-3"><p></p></div>';
      grid += '<div class="grid-3 threecol col-md-3 col-sm-3 col-xs-3 col-lg-3"><p></p></div>';
      grid += '<div class="grid-6 sixcol last-col col-md-6 col-sm-6 col-xs-6 col-lg-6"><p></p></div>';
      grid += '</div><br />';
      break;

    case '3_50_25_25':
      grid = '<div class="ckeditor-col-container clearfix row">';
      grid += '<div class="grid-6 sixcol first-col col-md-6 col-sm-6 col-xs-6 col-lg-6"><p></p></div>';
      grid += '<div class="grid-3 threecol col-md-3 col-sm-3 col-xs-3 col-lg-3"><p></p></div>';
      grid += '<div class="grid-3 threecol last-col col-md-3 col-sm-3 col-xs-3 col-lg-3"><p></p></div>';
      grid += '</div><br />';
      break;

    case '4_25_25_25_25':
      grid = '<div class="ckeditor-col-container clearfix row">';
      grid += '<div class="grid-3 threecol first-col col-md-3 col-sm-3 col-xs-3 col-lg-3"><p></p></div>';
      grid += '<div class="grid-3 threecol col-md-3 col-sm-3 col-xs-3 col-lg-3"><p></p></div>';
      grid += '<div class="grid-3 threecol col-md-3 col-sm-3 col-xs-3 col-lg-3"><p></p></div>';
      grid += '<div class="grid-3 threecol last-col col-md-3 col-sm-3 col-xs-3 col-lg-3"><p></p></div>';
      grid += '</div><br />';
      break;

    case '5_20_20_20_20_20':
      grid = '<div class="ckeditor-col-container clearfix col-5-wrapper row">';
      grid += '<div class="grid-3 first-col col-md-3 col-sm-3 col-xs-3 col-lg-3 layout-5-col"><p></p></div>';
      grid += '<div class="grid-2 twocol col-sm-2 col-xs-2 col-lg-2 layout-5-col"><p></p></div>';
      grid += '<div class="grid-2 twocol col-sm-2 col-xs-2 col-lg-2 layout-5-col"><p></p></div>';
      grid += '<div class="grid-2 twocol col-sm-2 col-xs-2 col-lg-2 layout-5-col"><p></p></div>';
      grid += '<div class="grid-3 last-col col-md-3 col-sm-3 col-xs-3 col-lg-3 layout-5-col"><p></p></div>';
      grid += '</div><br />';

  }
  return grid;
}
