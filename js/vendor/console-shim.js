/* https://github.com/liamnewmarch/console-shim 2014 CC-BY @liamnewmarch */
if (!('console' in window)) {
  (function() {
    var Console, __console;
    Console = function() {
      var check, key, log, methods, _i, _len, _ref;
      this.__buffer = [];
      log = function() {
        return this.__buffer.push(arguments);
      };
      methods = 'assert count debug dir dirxml error exception info log trace warn';
      _ref = methods.split(' ');
      for (_i = 0, _len = _ref.length; _i < _len; _i++) {
        key = _ref[_i];
        this[key] = log;
      }
      check = setInterval(function() {
        var data, func, _j, _len1, _ref1, _ref2;
        if ((((_ref1 = window.console) != null ? _ref1.log : void 0) != null) && !console.__buffer) {
          clearInterval(check);
          func = Function.prototype.bind ? Function.prototype.bind.call(console.log, console) : console.log;
          _ref2 = __console.__buffer;
          for (_j = 0, _len1 = _ref2.length; _j < _len1; _j++) {
            data = _ref2[_j];
            func.apply(console, data);
          }
        }
      }, 1000);
    };
    return __console = window.console = new Console();
  })();
}
