// create the nodeType constants if the Node object is not defined
if (!window.Node){
    var Node = {
        ELEMENT_NODE                :  1,
        ATTRIBUTE_NODE              :  2,
        TEXT_NODE                   :  3,
        CDATA_SECTION_NODE          :  4,
        ENTITY_REFERENCE_NODE       :  5,
        ENTITY_NODE                 :  6,
        PROCESSING_INSTRUCTION_NODE :  7,
        COMMENT_NODE                :  8,
        DOCUMENT_NODE               :  9,
        DOCUMENT_TYPE_NODE          : 10,
        DOCUMENT_FRAGMENT_NODE      : 11,
        NOTATION_NODE               : 12
    };
}

(function() {
    if (!Object.keys) {
        Object.keys = function(obj) {
            if (obj !== Object(obj)) {
                throw new TypeError('Object.keys called on a non-object');
            }

            var keys = [];

            for (var i in obj) {
                if (Object.prototype.hasOwnProperty.call(obj, i)) {
                    keys.push(i);
                }
            }

            return keys;
        };
    }
}());

(function() {
    if (!Object.create) {
        Object.create = function(proto, props) {
            if (typeof props !== "undefined") {
                throw "The multiple-argument version of Object.create is not provided by this browser and cannot be shimmed.";
            }
            function ctor() { }
            ctor.prototype = proto;

            return new ctor();
        };
    }
}());

(function() {
    if (!Array.prototype.forEach) {
        Array.prototype.forEach = function(fn, scope) {
            for(var i = 0, len = this.length; i < len; ++i) {
                fn.call(scope, this[i], i, this);
            }
        }
    }
}());

// ES 15.2.3.6 Object.defineProperty ( O, P, Attributes )
// Partial support for most common case - getters, setters, and values
(function() {
    if (!Object.defineProperty ||
       !(function () { try { Object.defineProperty({}, 'x', {}); return true; } catch (e) { return false; } } ())) {
        var orig = Object.defineProperty;
        Object.defineProperty = function (o, prop, desc) {
            // In IE8 try built-in implementation for defining properties on DOM prototypes.
            if (orig) { try { return orig(o, prop, desc); } catch (e) {} }

            if (o !== Object(o)) { throw TypeError("Object.defineProperty called on non-object"); }
            if (Object.prototype.__defineGetter__ && ('get' in desc)) {
                Object.prototype.__defineGetter__.call(o, prop, desc.get);
            }
            if (Object.prototype.__defineSetter__ && ('set' in desc)) {
                Object.prototype.__defineSetter__.call(o, prop, desc.set);
            }
            if ('value' in desc) {
                o[prop] = desc.value;
            }

            return o;
        };
    }
}());

(function() {
    if (!Function.prototype.bind) {
        Function.prototype.bind = function (oThis) {
            if (typeof this !== "function") {
                // closest thing possible to the ECMAScript 5 internal IsCallable function
                throw new TypeError("Function.prototype.bind - what is trying to be bound is not callable");
            }

        var aArgs = Array.prototype.slice.call(arguments, 1),
            fToBind = this,
            fNOP = function () {},
            fBound = function () {
                return fToBind.apply(this instanceof fNOP && oThis
                         ? this
                         : oThis,
                         aArgs.concat(Array.prototype.slice.call(arguments)));
            };

            fNOP.prototype = this.prototype;
            fBound.prototype = new fNOP();

            return fBound;
        };
    }
}());

(function() {
    if (!Array.prototype.indexOf) {
        Array.prototype.indexOf = function(elt /*, from*/) {
            var len = this.length >>> 0;

            var from = Number(arguments[1]) || 0;
            from = (from < 0) ? Math.ceil(from) : Math.floor(from);
            if (from < 0) {
                from += len;
            }
            for (; from < len; from++) {
                if (from in this && this[from] === elt) {
                    return from;
                }
            }

            return -1;
        };
    }
}());
