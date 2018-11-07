+function ($) {

  const pluginName = 'responsiveMedia',

    defaults = {
      rules: true,
      name: 'responsive',
      threshold: 600,
      images: ["img:not(.latex)"],
      videos: [
        "iframe[src*='player.vimeo.com']",
        "iframe[src*='youtube.com']",
        "iframe[src*='youtu.be']",
        "iframe[src*='youtube-nocookie.com']",
        "iframe[src*='ehutb.ehu.es']",
        "iframe[src*='www.eitb.eus']",
        "video",
        "object",
        "embed",
      ],
    },

    media = {
      get: function (item, dimension) {
        return !isNaN(parseInt(item.attr(dimension), 10)) ? parseInt(item.attr(dimension), 10) : item[dimension]();
      },

      format: function (value) {
        return parseFloat(value).toFixed(4);
      },

      parse: function (item) {
        let info = {
          type: item.prop('tagName').toLowerCase(),
        };

        if (info.type === 'embed' && item.parent('object').length) {
          return false;
        }

        if ((info.type !== 'img') && (isNaN(item.attr('height')) || isNaN(item.attr('width')))) {
          item.attr('width', 16);
          item.attr('height', 9);
        }

        info.width = media.get(item, 'width');
        info.height = media.get(item, 'height');
        info.ratio = media.format((info.height / info.width) * 100);
        info.relative = media.format(info.width / parseInt(item.css('font-size'), 10));

        return info;
      },
    };

  function Plugin(element, options) {
    this.element = $(element);
    this.options = $.extend({}, defaults, this.element.data(), options);
    this.selectors = defaults.videos.concat(this.options.videos).concat(this.options.images);

    this.init();
  }

  Plugin.prototype = {
    init: function () {
      const className = this.options.name;
      const element = this.element;
      const elements = element
        .find($.unique(this.selectors).join())
        .not('object object')
        .not('.' + className);

      const rules = [];
      const options = this.options;

      elements.each(function (index) {
        const item = $(this);
        const classItem = className + '-' + (index + 1);
        let classType = className;

        if (item.hasClass(className)) {
          return;
        }

        const info = media.parse(item);

        if (!info) {
          return;
        }

        if (info.type === 'img') {
          classType += '-image';

          if (info.width >= options.threshold) {
            item
              .addClass(className)
              .addClass(classType)
              .removeAttr('width')
              .removeAttr('height');
          }
        } else {
          classType += '-video';

          rules.push('.' + classItem + ' {padding-bottom:' + info.ratio + '% !important;}');

          item
            .removeAttr('width')
            .removeAttr('height')
            .wrap('<div></div>')
            .parent()
            .addClass(classType)
            .addClass(classItem);
        }
      });

      this.style(rules);
    },

    style: function (rules) {
      if (!this.options.rules) {
        return;
      }

      const className = '.' + this.options.name;

      if (rules.length > 0) {
        rules.push(className + '-video iframe, ' + className + '-video object, ' + className + '-video embed, ' + className + '-video video {position:absolute; top:0; left:0; width:100%; height:100%;}');
        rules.push(className + '-video {position:relative; margin:0 auto; padding:0; height:0;}');
      }

      rules.push(className + '-image {width:100%;}');

      $('<style>')
        .prop('type', 'text/css')
        .html(rules.join("\n"))
        .appendTo('head');
    },
  };

  $.fn[pluginName] = function (options) {
    return this.each(function () {
      if (!$.data(this, 'plugin_' + pluginName)) {
        $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
      }
    });
  };

}(jQuery);