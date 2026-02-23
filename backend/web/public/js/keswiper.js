(function($) {
  var styles = '.ke-icon-slider{width:16px;height:16px;background:#ee0a3b}.slider-container{line-height:2;padding:12px}.slider-container label{display:inline-block;width:120px;text-align:right}.slider-row{margin:12px 0}.__btn{line-height:3;padding:0 40px;font-size:18px;color:#fff;background:#ee0a3b;border:none;margin:12px;cursor:pointer}.__btn--dotted{background:#e00}.__btn--small{line-height:2;font-size:14px;padding:0 20px}';
  var _link = $('<style>');
  _link.html(styles);
  $('head').append(_link);

  function getPlacement(src) {
    return '<img class="__swiper" src="' + src + '">';
  }

  function getSliderTemplate(items, openAt) {
    if (openAt === undefined) {
      openAt = '_blank';
    }
    return function() {
      var html = '<link rel="stylesheet" href="' + STATIC_URL + '/js/libs/swiper/swiper.min.css">';
      html += '<style>.swiper-container{width:100%;text-align:center}.swiper-container img{max-with:100% !important;}</style>';
      html += '<script src="' + STATIC_URL + '/js/libs/swiper/swiper.min.js"><' + '/script>';
      html += '<div class="swiper-container">';
      html += '<div class="swiper-wrapper">';
      $.each(items, function(i, item) {
        html += '<a target="' + openAt + '" href="' + (item.link ? item.link : 'javascript:void(0)" style="cursor:default') + '" class="swiper-slide"><img src="' + item.image + '"></a>';
      });
      html += '</div>';
      html += '<div class="swiper-pagination"></div>';
      html += '</div>';
      html += '<script>var mySwiper = new Swiper(\'.swiper-container\', {' + (items.length > 1 ? 'loop:true,autoplay:3000,pagination:\'.swiper-pagination\',paginationClickable:true,' : '') + 'autoHeight:true});<' + '/script>';
      html = '<div class="__swiper">' + html + '</div>';
      return html;
    }
  }
  $.fn.editorSwiper = function(previewId, openAt, opts) {
    opts = $.extend({
      width: '100%',
      height: 400,
      filterMode: false,
      syncType: '',
      items: [
        'source', '|', 'undo', 'redo', '|', 'preview', 'print', 'template', 'code', 'cut', 'copy', 'paste',
        'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright',
        'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript',
        'superscript', 'clearhtml', 'quickformat', 'selectall', '|', 'fullscreen', '/',
        'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold',
        'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'image', 'multiimage',
        'flash', 'media', 'insertfile', 'table', 'hr', 'emoticons', 'baidumap', 'pagebreak',
        'anchor', 'link', 'unlink', '|', 'about', 'slider'
      ]
    }, opts);
    var $editor = this;
    var id = $editor.attr('id');
    var editor;
    var $sliderContainer = $('<div class="slider-container"><div class="slider-row"><label>图片地址：</label><input type="text" name="image"><label>链接地址：</label><input type="text" name="link"></div><div style="text-align: right"><button class="__btn __btn--small slider-add">新增一行</button></div></div>');
    var swiperItems = [];
    var $sliderAdd = $sliderContainer.find('.slider-add');

    $sliderAdd.click(function() {
      $sliderAdd.parent().before('<div class="slider-row"><label>图片地址：</label><input type="text" name="image"><label>链接地址：</label><input type="text" name="link"></div>');
    });

    function filterSlider(val) {
      var $ele = $('<div>').html(val);
      var $swiper = $ele.find('.__swiper');
      if ($swiper.length > 0) {
        var _html = '';
        $swiper.find('a').each(function() {
          var $this = $(this);
          var link = $this.attr('href');
          var image = $this.find('img').attr('src');
          swiperItems.push({
            image: image,
            link: link
          });
          _html += '<div class="slider-row"><label>图片地址：</label><input type="text" name="image" value="' + image + '"><label>链接地址：</label><input type="text" name="link" value="' + link + '"></div>';
        });
        if (swiperItems.length > 0) {
          $swiper.replaceWith(getPlacement(swiperItems[0].image));
          $sliderContainer.prepend(_html);
        }
      }
      return $ele.html();
    }
    $editor.val(function(i, v) {
      return filterSlider(v);
    });
    KindEditor.plugin('slider', function(K) {
      var editor = this,
        name = 'slider';
      // 点击图标时执行
      editor.clickToolbar(name, function() {
        var dialog = K.dialog({
          width: 700,
          height: 500,
          title: '轮播图',
          body: $sliderContainer[0],
          closeBtn: {
            name: '关闭',
            click: function(e) {
              dialog.remove();
            }
          },
          yesBtn: {
            name: '确定',
            click: function(e) {
              var $images = $sliderContainer.find('input[name=image]');
              var $inputs = $sliderContainer.find('input[name=link]');
              swiperItems = [];
              $.each($images, function(i, ele) {
                var image = ele.value.trim();
                if (!image) {
                  return;
                }
                swiperItems.push({
                  image: image,
                  link: $inputs[i].value.trim()
                });
              });
              var _ss = KindEditor('.__swiper', editor.cmd.doc);
              var _tpl = getPlacement(swiperItems[0].image);
              if (_ss.length > 0) {
                _ss.replaceWith(_tpl)
              } else {
                editor.insertHtml(_tpl);
              }

              dialog.remove();
            }
          },
          noBtn: {
            name: '取消',
            click: function(e) {
              dialog.remove();
            }
          }
        });
        // editor.insertHtml('你好');
      });
    });
    KindEditor.lang({
      slider: '插入轮播图'
    });
    editor = KindEditor.create($editor[0], opts);

    function syncTextarea() {
      var __swiper = KindEditor('.__swiper', editor.cmd.doc);
      if (__swiper.length > 0) {
        var __parent = __swiper.parent();
        if (__parent.name === 'p') {
          var __node = KindEditor('<div>', editor.cmd.doc);
          __node.html(__parent.html());
          __parent.replaceWith(__node);
        }
      }
      editor.sync();
      var v = $editor.val();
      v = v.replace(/<img(?=[^>]* class="__swiper")[^>]+>/, getSliderTemplate(swiperItems, openAt));
      $editor.val(v);
      return v;
    }
    $(this[0].form).submit(function() {
      syncTextarea();
    });
    if (previewId) {
      $('#' + previewId).click(function() {
        var v = syncTextarea();
        var win = window.open('', '_blank');
        win.document.write(v);
      });
    }
  }
})(window.jQuery);
