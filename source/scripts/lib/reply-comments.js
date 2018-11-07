+function ($) {

  const pluginName = 'replyComment',
    defaults = {
      buttons: {
        reply: '.reply',
        cancel: '#cancel-reply',
      },
      nodes: {
        commentId: '#comment_parent',
        postId: '#comment_post_ID',
        form: '#respond',
        textarea: '#comment',
      },
    };

  function Plugin(element, options) {
    this.element = $(element);
    this.options = $.extend({}, defaults, this.element.data(), options);
    this.cancel = $(this.options.buttons.cancel).hide();
    this.placeholder = $('<div id="form-placeholder"></div>').hide();

    $.each(this.options.nodes, (name, selector) => {
      this[name] = $(selector);
    });

    this.init();
  }

  Plugin.prototype = {
    init: function () {
      this.form.after(this.placeholder);

      this.element.find(this.options.buttons.reply).each(function () {
        $(this).attr('role', 'button');
      });

      this.element.on('click', this.options.buttons.reply, (event) => {
        event.preventDefault();
        this.insertForm($(event.target).closest(this.options.buttons.reply));
      });
    },

    insertForm: function (reply) {
      this.postId.val(reply.data('post'));
      this.commentId.val(reply.data('comment'));
      this.cancel.show().on('click', () => {
        this.removeForm(reply);
      });

      reply.hide().after(this.form);

      try {
        this.textarea.focus();
      } catch (error) {
        // Do nothing
      }
    },

    removeForm: function (reply) {
      this.commentId.val(0);
      this.cancel.hide();
      this.placeholder.before(this.form);

      reply.show();
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