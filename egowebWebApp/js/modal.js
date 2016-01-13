+function ($) {
  'use strict';

  // MODAL CLASS DEFINITION
  // ======================

  var Modal = function (element, options) {
    this.options   = options
    this.$element  = $(element)
    this.$backdrop =
    this.isShown   = null

    if (this.options.remote) {
      this.$element
        .find('.modal-content')
        .load(this.options.remote, $.proxy(function () {
          this.$element.trigger('loaded.bs.modal')
        }, this))
    }
  }

  Modal.DEFAULTS = {
    backdrop: true,
    keyboard: true,
    show: true
  }

  Modal.prototype.toggle = function (_relatedTarget) {
    return this[!this.isShown ? 'show' : 'hide'](_relatedTarget)
  }

  Modal.prototype.show = function (_relatedTarget) {
    var that = this
    var e    = $.Event('show.bs.modal', { relatedTarget: _relatedTarget })

    this.$element.trigger(e)

    if (this.isShown || e.isDefaultPrevented()) return

    this.isShown = true

    this.escape()

    this.$element.on('click.dismiss.bs.modal', '[data-dismiss="modal"]', $.proxy(this.hide, this))

    this.backdrop(function () {
      var transition = $.support.transition && that.$element.hasClass('fade')

      if (!that.$element.parent().length) {
        that.$element.appendTo(document.body) // don't move modals dom position
      }

      that.$element
        .show()
        .scrollTop(0)

      if (transition) {
        that.$element[0].offsetWidth // force reflow
      }

      that.$element
        .addClass('in')
        .attr('aria-hidden', false)

      that.enforceFocus()

      var e = $.Event('shown.bs.modal', { relatedTarget: _relatedTarget })

      transition ?
        that.$element.find('.modal-dialog') // wait for modal to slide in
          .one($.support.transition.end, function () {
            that.$element.focus().trigger(e)
          })
          .emulateTransitionEnd(300) :
        that.$element.focus().trigger(e)
    })
  }

  Modal.prototype.hide = function (e) {
    if (e) e.preventDefault()

    e = $.Event('hide.bs.modal')

    this.$element.trigger(e)

    if (!this.isShown || e.isDefaultPrevented()) return

    this.isShown = false

    this.escape()

    $(document).off('focusin.bs.modal')

    this.$element
      .removeClass('in')
      .attr('aria-hidden', true)
      .off('click.dismiss.bs.modal')

    $.support.transition && this.$element.hasClass('fade') ?
      this.$element
        .one($.support.transition.end, $.proxy(this.hideModal, this))
        .emulateTransitionEnd(300) :
      this.hideModal()
  }

  Modal.prototype.enforceFocus = function () {
    $(document)
      .off('focusin.bs.modal') // guard against infinite focus loop
      .on('focusin.bs.modal', $.proxy(function (e) {
        if (this.$element[0] !== e.target && !this.$element.has(e.target).length) {
          this.$element.focus()
        }
      }, this))
  }

  Modal.prototype.escape = function () {
    if (this.isShown && this.options.keyboard) {
      this.$element.on('keyup.dismiss.bs.modal', $.proxy(function (e) {
        e.which == 27 && this.hide()
      }, this))
    } else if (!this.isShown) {
      this.$element.off('keyup.dismiss.bs.modal')
    }
  }

  Modal.prototype.hideModal = function () {
    var that = this
    this.$element.hide()
    this.backdrop(function () {
      that.removeBackdrop()
      that.$element.trigger('hidden.bs.modal')
    })
  }

  Modal.prototype.removeBackdrop = function () {
    this.$backdrop && this.$backdrop.remove()
    this.$backdrop = null
  }

  Modal.prototype.backdrop = function (callback) {
    var animate = this.$element.hasClass('fade') ? 'fade' : ''

    if (this.isShown && this.options.backdrop) {
      var doAnimate = $.support.transition && animate

      this.$backdrop = $('<div class="modal-backdrop ' + animate + '" />')
        .appendTo(document.body)

      this.$element.on('click.dismiss.bs.modal', $.proxy(function (e) {
        if (e.target !== e.currentTarget) return
        this.options.backdrop == 'static'
          ? this.$element[0].focus.call(this.$element[0])
          : this.hide.call(this)
      }, this))

      if (doAnimate) this.$backdrop[0].offsetWidth // force reflow

      this.$backdrop.addClass('in')

      if (!callback) return

      doAnimate ?
        this.$backdrop
          .one($.support.transition.end, callback)
          .emulateTransitionEnd(150) :
        callback()

    } else if (!this.isShown && this.$backdrop) {
      this.$backdrop.removeClass('in')

      $.support.transition && this.$element.hasClass('fade') ?
        this.$backdrop
          .one($.support.transition.end, callback)
          .emulateTransitionEnd(150) :
        callback()

    } else if (callback) {
      callback()
    }
  }


  // MODAL PLUGIN DEFINITION
  // =======================

  var old = $.fn.modal

  $.fn.modal = function (option, _relatedTarget) {
    return this.each(function () {
      var $this   = $(this)
      var data    = $this.data('bs.modal')
      var options = $.extend({}, Modal.DEFAULTS, $this.data(), typeof option == 'object' && option)

      if (!data) $this.data('bs.modal', (data = new Modal(this, options)))
      if (typeof option == 'string') data[option](_relatedTarget)
      else if (options.show) data.show(_relatedTarget)
    })
  }

  $.fn.modal.Constructor = Modal


  // MODAL NO CONFLICT
  // =================

  $.fn.modal.noConflict = function () {
    $.fn.modal = old
    return this
  }


  // MODAL DATA-API
  // ==============

  $(document).on('click.bs.modal.data-api', '[data-toggle="modal"]', function (e) {
    var $this   = $(this)
    var href    = $this.attr('href')
    var $target = $($this.attr('data-target') || (href && href.replace(/.*(?=#[^\s]+$)/, ''))) //strip for ie7
    var option  = $target.data('bs.modal') ? 'toggle' : $.extend({ remote: !/#/.test(href) && href }, $target.data(), $this.data())

    if ($this.is('a')) e.preventDefault()

    $target
      .modal(option, this)
      .one('hide', function () {
        $this.is(':visible') && $this.focus()
      })
  })

  $(document)
    .on('show.bs.modal', '.modal', function () { $(document.body).addClass('modal-open') })
    .on('hidden.bs.modal', '.modal', function () { $(document.body).removeClass('modal-open') })

}(jQuery);

function getNote(node){
    var url = "/data/getnote?interviewId=" + interviewId + "&expressionId=" + expressionId + "&alterId=" + node.id;
    $.get(url, function(data){
        $("#left-container").html(data);

    });
}

function saveNote(){
    var noteContent = $("#Note_notes").val();
    $.post("/data/savenote", $("#note-form").serialize(), function(nodeId){
        var node = s.graph.nodes(nodeId);
        if(node && !node.id.match(/graphNote/) && !node.label.match("�"))
            node.label = node.label + " �";
        s.refresh();
        var url = "/data/getnote?interviewId=" + interviewId + "&expressionId=" + expressionId + "&alterId=" + nodeId;
        $.get(url, function(data){
            notes[nodeId] = noteContent;
            $("#left-container").html(data);
        });
    });
}

function deleteNote(){
    $.post("/data/deletenote", $("#note-form").serialize(), function(data){
        if(!isNaN(data)){
            var node = s.graph.nodes(data);
            node.label = node.label.replace(" �","");
            delete notes[data];
            s.refresh();
            $("#left-container").html("");
        }else{
            delete notes[data];
            s.graph.dropNode(data);
            saveNodes();
            $("#left-container").html("");
            graphNotes = 0;
            for(k in notes){
                if(k.match(/graphNote/)){
                    noteId = parseInt(k.match(/graphNote-(\d+)/)[1]);
                    if(noteId > graphNotes)
                        graphNotes = noteId;
                }
            }
            s.refresh();
        }
    });
}

function initNotes(s){
    graphNotes = 0;
    s.bind('clickNode',function(e){
        getNote(e.data.node);
    });
    var _dragListener = new sigma.events.drag(s.renderers[0]);
    _dragListener.bind('drop', function(e) {
        if(e.data.node){
            saveNodes();
        }
    });

    s.bind('doubleClickStage', function(e) {
        x = e.data.captor.x;
        y = e.data.captor.y;

        if(graphNotes == 0 || typeof notes['graphNote-' + graphNotes] != "undefined"){
            graphNotes++;
            s.graph.addNode({
                id: 'graphNote-' + graphNotes,
                label: graphNotes.toString(),
                size: 4,

                type:'arrow'
            });
            saveNodes();
        }
        var node = s.graph.nodes('graphNote-' + graphNotes);

        node.x = x / 10 ;
        node.y = y / 10 ;
        s.refresh();
    });

    for(k in notes){
        if(k.match(/graphNote/)){
            noteId = k.match(/graphNote-(\d+)/)[1];
            s.graph.addNode({
                id: 'graphNote-' + noteId,
                label: noteId.toString(),
                size: 4,
                x: Math.floor((Math.random() * 100) + 1),
                y: Math.floor((Math.random() * 100) + 1),
                dX: 0,
                dY: 0,
                type:'arrow'
            });
            graphNotes = parseInt(noteId);
        }
    }
}