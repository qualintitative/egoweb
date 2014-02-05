(function() {
  this.MasterClass = (function() {
    function MasterClass(name) {
      this.name = name;
    }

    MasterClass.prototype._init = function() {
      jQuery(function() {
        if (typeof jp.ready === "function") {
          return jp.ready();
        } else {
          return jp._ready();
        }
      });
      return null;
    };

    MasterClass.prototype._ready = function() {
      return null;
    };

    return MasterClass;

  })();

}).call(this);
