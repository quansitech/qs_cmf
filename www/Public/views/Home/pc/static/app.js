;/*!/js/mod.js*/
/**
 * file: mod.js
 * ver: 1.0.11
 * update: 2015/05/14
 *
 * https://github.com/fex-team/mod
 */
'use strict';

var require, define;

(function (global) {
    if (require) return; // 避免重复加载而导致已定义模块丢失

    var head = document.getElementsByTagName('head')[0],
        loadingMap = {},
        factoryMap = {},
        modulesMap = {},
        scriptsMap = {},
        resMap = {},
        pkgMap = {};

    function createScript(url, onerror) {
        if (url in scriptsMap) return;
        scriptsMap[url] = true;

        var script = document.createElement('script');
        if (onerror) {
            var tid;

            (function () {
                var onload = function onload() {
                    clearTimeout(tid);
                };

                tid = setTimeout(onerror, require.timeout);

                script.onerror = function () {
                    clearTimeout(tid);
                    onerror();
                };

                if ('onload' in script) {
                    script.onload = onload;
                } else {
                    script.onreadystatechange = function () {
                        if (this.readyState == 'loaded' || this.readyState == 'complete') {
                            onload();
                        }
                    };
                }
            })();
        }
        script.type = 'text/javascript';
        script.src = url;
        head.appendChild(script);
        return script;
    }

    function loadScript(id, callback, onerror) {
        var queue = loadingMap[id] || (loadingMap[id] = []);
        queue.push(callback);

        //
        // resource map query
        //
        var res = resMap[id] || resMap[id + '.js'] || {};
        var pkg = res.pkg;
        var url;

        if (pkg) {
            url = pkgMap[pkg].url;
        } else {
            url = res.url || id;
        }

        createScript(url, onerror && function () {
            onerror(id);
        });
    }

    define = function (id, factory) {
        id = id.replace(/\.js$/i, '');
        factoryMap[id] = factory;

        var queue = loadingMap[id];
        if (queue) {
            for (var i = 0, n = queue.length; i < n; i++) {
                queue[i]();
            }
            delete loadingMap[id];
        }
    };

    require = function (id) {

        // compatible with require([dep, dep2...]) syntax.
        if (id && id.splice) {
            return require.async.apply(this, arguments);
        }

        id = require.alias(id);

        var mod = modulesMap[id];
        if (mod) {
            return mod.exports;
        }

        //
        // init module
        //
        var factory = factoryMap[id];
        if (!factory) {
            throw '[ModJS] Cannot find module `' + id + '`';
        }

        mod = modulesMap[id] = {
            exports: {}
        };

        //
        // factory: function OR value
        //
        var ret = typeof factory == 'function' ? factory.apply(mod, [require, mod.exports, mod]) : factory;

        if (ret) {
            mod.exports = ret;
        }
        return mod.exports;
    };

    require.async = function (names, onload, onerror) {
        if (typeof names == 'string') {
            names = [names];
        }

        var needMap = {};
        var needNum = 0;

        function findNeed(depArr) {
            for (var i = 0, n = depArr.length; i < n; i++) {
                //
                // skip loading or loaded
                //
                var dep = require.alias(depArr[i]);

                if (dep in factoryMap) {
                    // check whether loaded resource's deps is loaded or not
                    var child = resMap[dep] || resMap[dep + '.js'];
                    if (child && 'deps' in child) {
                        findNeed(child.deps);
                    }
                    continue;
                }

                if (dep in needMap) {
                    continue;
                }

                needMap[dep] = true;
                needNum++;
                loadScript(dep, updateNeed, onerror);

                var child = resMap[dep] || resMap[dep + '.js'];
                if (child && 'deps' in child) {
                    findNeed(child.deps);
                }
            }
        }

        function updateNeed() {
            if (0 == needNum--) {
                var args = [];
                for (var i = 0, n = names.length; i < n; i++) {
                    args[i] = require(names[i]);
                }

                onload && onload.apply(global, args);
            }
        }

        findNeed(names);
        updateNeed();
    };

    require.resourceMap = function (obj) {
        var k, col;

        // merge `res` & `pkg` fields
        col = obj.res;
        for (k in col) {
            if (col.hasOwnProperty(k)) {
                resMap[k] = col[k];
            }
        }

        col = obj.pkg;
        for (k in col) {
            if (col.hasOwnProperty(k)) {
                pkgMap[k] = col[k];
            }
        }
    };

    require.loadJs = function (url) {
        createScript(url);
    };

    require.loadCss = function (cfg) {
        if (cfg.content) {
            var sty = document.createElement('style');
            sty.type = 'text/css';

            if (sty.styleSheet) {
                // IE
                sty.styleSheet.cssText = cfg.content;
            } else {
                sty.innerHTML = cfg.content;
            }
            head.appendChild(sty);
        } else if (cfg.url) {
            var link = document.createElement('link');
            link.href = cfg.url;
            link.rel = 'stylesheet';
            link.type = 'text/css';
            head.appendChild(link);
        }
    };

    require.alias = function (id) {
        return id.replace(/\.js$/i, '');
    };

    require.timeout = 5000;
})(undefined);
;/*!/modules/ignore/jquery-3.7.1/jquery.js*/
define('modules/ignore/jquery-3.7.1/jquery', function(require, exports, module) {

  /*!
   * jQuery JavaScript Library v1.8.3
   * http://jquery.com/
   *
   * Includes Sizzle.js
   * http://sizzlejs.com/
   *
   * Copyright 2012 jQuery Foundation and other contributors
   * Released under the MIT license
   * http://jquery.org/license
   *
   * Date: Tue Nov 13 2012 08:20:33 GMT-0500 (Eastern Standard Time)
   */
  (function( window, undefined ) {
  var
  	// A central reference to the root jQuery(document)
  	rootjQuery,
  
  	// The deferred used on DOM ready
  	readyList,
  
  	// Use the correct document accordingly with window argument (sandbox)
  	document = window.document,
  	location = window.location,
  	navigator = window.navigator,
  
  	// Map over jQuery in case of overwrite
  	_jQuery = window.jQuery,
  
  	// Map over the $ in case of overwrite
  	_$ = window.$,
  
  	// Save a reference to some core methods
  	core_push = Array.prototype.push,
  	core_slice = Array.prototype.slice,
  	core_indexOf = Array.prototype.indexOf,
  	core_toString = Object.prototype.toString,
  	core_hasOwn = Object.prototype.hasOwnProperty,
  	core_trim = String.prototype.trim,
  
  	// Define a local copy of jQuery
  	jQuery = function( selector, context ) {
  		// The jQuery object is actually just the init constructor 'enhanced'
  		return new jQuery.fn.init( selector, context, rootjQuery );
  	},
  
  	// Used for matching numbers
  	core_pnum = /[\-+]?(?:\d*\.|)\d+(?:[eE][\-+]?\d+|)/.source,
  
  	// Used for detecting and trimming whitespace
  	core_rnotwhite = /\S/,
  	core_rspace = /\s+/,
  
  	// Make sure we trim BOM and NBSP (here's looking at you, Safari 5.0 and IE)
  	rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,
  
  	// A simple way to check for HTML strings
  	// Prioritize #id over <tag> to avoid XSS via location.hash (#9521)
  	rquickExpr = /^(?:[^#<]*(<[\w\W]+>)[^>]*$|#([\w\-]*)$)/,
  
  	// Match a standalone tag
  	rsingleTag = /^<(\w+)\s*\/?>(?:<\/\1>|)$/,
  
  	// JSON RegExp
  	rvalidchars = /^[\],:{}\s]*$/,
  	rvalidbraces = /(?:^|:|,)(?:\s*\[)+/g,
  	rvalidescape = /\\(?:["\\\/bfnrt]|u[\da-fA-F]{4})/g,
  	rvalidtokens = /"[^"\\\r\n]*"|true|false|null|-?(?:\d\d*\.|)\d+(?:[eE][\-+]?\d+|)/g,
  
  	// Matches dashed string for camelizing
  	rmsPrefix = /^-ms-/,
  	rdashAlpha = /-([\da-z])/gi,
  
  	// Used by jQuery.camelCase as callback to replace()
  	fcamelCase = function( all, letter ) {
  		return ( letter + "" ).toUpperCase();
  	},
  
  	// The ready event handler and self cleanup method
  	DOMContentLoaded = function() {
  		if ( document.addEventListener ) {
  			document.removeEventListener( "DOMContentLoaded", DOMContentLoaded, false );
  			jQuery.ready();
  		} else if ( document.readyState === "complete" ) {
  			// we're here because readyState === "complete" in oldIE
  			// which is good enough for us to call the dom ready!
  			document.detachEvent( "onreadystatechange", DOMContentLoaded );
  			jQuery.ready();
  		}
  	},
  
  	// [[Class]] -> type pairs
  	class2type = {};
  
  jQuery.fn = jQuery.prototype = {
  	constructor: jQuery,
  	init: function( selector, context, rootjQuery ) {
  		var match, elem, ret, doc;
  
  		// Handle $(""), $(null), $(undefined), $(false)
  		if ( !selector ) {
  			return this;
  		}
  
  		// Handle $(DOMElement)
  		if ( selector.nodeType ) {
  			this.context = this[0] = selector;
  			this.length = 1;
  			return this;
  		}
  
  		// Handle HTML strings
  		if ( typeof selector === "string" ) {
  			if ( selector.charAt(0) === "<" && selector.charAt( selector.length - 1 ) === ">" && selector.length >= 3 ) {
  				// Assume that strings that start and end with <> are HTML and skip the regex check
  				match = [ null, selector, null ];
  
  			} else {
  				match = rquickExpr.exec( selector );
  			}
  
  			// Match html or make sure no context is specified for #id
  			if ( match && (match[1] || !context) ) {
  
  				// HANDLE: $(html) -> $(array)
  				if ( match[1] ) {
  					context = context instanceof jQuery ? context[0] : context;
  					doc = ( context && context.nodeType ? context.ownerDocument || context : document );
  
  					// scripts is true for back-compat
  					selector = jQuery.parseHTML( match[1], doc, true );
  					if ( rsingleTag.test( match[1] ) && jQuery.isPlainObject( context ) ) {
  						this.attr.call( selector, context, true );
  					}
  
  					return jQuery.merge( this, selector );
  
  				// HANDLE: $(#id)
  				} else {
  					elem = document.getElementById( match[2] );
  
  					// Check parentNode to catch when Blackberry 4.6 returns
  					// nodes that are no longer in the document #6963
  					if ( elem && elem.parentNode ) {
  						// Handle the case where IE and Opera return items
  						// by name instead of ID
  						if ( elem.id !== match[2] ) {
  							return rootjQuery.find( selector );
  						}
  
  						// Otherwise, we inject the element directly into the jQuery object
  						this.length = 1;
  						this[0] = elem;
  					}
  
  					this.context = document;
  					this.selector = selector;
  					return this;
  				}
  
  			// HANDLE: $(expr, $(...))
  			} else if ( !context || context.jquery ) {
  				return ( context || rootjQuery ).find( selector );
  
  			// HANDLE: $(expr, context)
  			// (which is just equivalent to: $(context).find(expr)
  			} else {
  				return this.constructor( context ).find( selector );
  			}
  
  		// HANDLE: $(function)
  		// Shortcut for document ready
  		} else if ( jQuery.isFunction( selector ) ) {
  			return rootjQuery.ready( selector );
  		}
  
  		if ( selector.selector !== undefined ) {
  			this.selector = selector.selector;
  			this.context = selector.context;
  		}
  
  		return jQuery.makeArray( selector, this );
  	},
  
  	// Start with an empty selector
  	selector: "",
  
  	// The current version of jQuery being used
  	jquery: "1.8.3",
  
  	// The default length of a jQuery object is 0
  	length: 0,
  
  	// The number of elements contained in the matched element set
  	size: function() {
  		return this.length;
  	},
  
  	toArray: function() {
  		return core_slice.call( this );
  	},
  
  	// Get the Nth element in the matched element set OR
  	// Get the whole matched element set as a clean array
  	get: function( num ) {
  		return num == null ?
  
  			// Return a 'clean' array
  			this.toArray() :
  
  			// Return just the object
  			( num < 0 ? this[ this.length + num ] : this[ num ] );
  	},
  
  	// Take an array of elements and push it onto the stack
  	// (returning the new matched element set)
  	pushStack: function( elems, name, selector ) {
  
  		// Build a new jQuery matched element set
  		var ret = jQuery.merge( this.constructor(), elems );
  
  		// Add the old object onto the stack (as a reference)
  		ret.prevObject = this;
  
  		ret.context = this.context;
  
  		if ( name === "find" ) {
  			ret.selector = this.selector + ( this.selector ? " " : "" ) + selector;
  		} else if ( name ) {
  			ret.selector = this.selector + "." + name + "(" + selector + ")";
  		}
  
  		// Return the newly-formed element set
  		return ret;
  	},
  
  	// Execute a callback for every element in the matched set.
  	// (You can seed the arguments with an array of args, but this is
  	// only used internally.)
  	each: function( callback, args ) {
  		return jQuery.each( this, callback, args );
  	},
  
  	ready: function( fn ) {
  		// Add the callback
  		jQuery.ready.promise().done( fn );
  
  		return this;
  	},
  
  	eq: function( i ) {
  		i = +i;
  		return i === -1 ?
  			this.slice( i ) :
  			this.slice( i, i + 1 );
  	},
  
  	first: function() {
  		return this.eq( 0 );
  	},
  
  	last: function() {
  		return this.eq( -1 );
  	},
  
  	slice: function() {
  		return this.pushStack( core_slice.apply( this, arguments ),
  			"slice", core_slice.call(arguments).join(",") );
  	},
  
  	map: function( callback ) {
  		return this.pushStack( jQuery.map(this, function( elem, i ) {
  			return callback.call( elem, i, elem );
  		}));
  	},
  
  	end: function() {
  		return this.prevObject || this.constructor(null);
  	},
  
  	// For internal use only.
  	// Behaves like an Array's method, not like a jQuery method.
  	push: core_push,
  	sort: [].sort,
  	splice: [].splice
  };
  
  // Give the init function the jQuery prototype for later instantiation
  jQuery.fn.init.prototype = jQuery.fn;
  
  jQuery.extend = jQuery.fn.extend = function() {
  	var options, name, src, copy, copyIsArray, clone,
  		target = arguments[0] || {},
  		i = 1,
  		length = arguments.length,
  		deep = false;
  
  	// Handle a deep copy situation
  	if ( typeof target === "boolean" ) {
  		deep = target;
  		target = arguments[1] || {};
  		// skip the boolean and the target
  		i = 2;
  	}
  
  	// Handle case when target is a string or something (possible in deep copy)
  	if ( typeof target !== "object" && !jQuery.isFunction(target) ) {
  		target = {};
  	}
  
  	// extend jQuery itself if only one argument is passed
  	if ( length === i ) {
  		target = this;
  		--i;
  	}
  
  	for ( ; i < length; i++ ) {
  		// Only deal with non-null/undefined values
  		if ( (options = arguments[ i ]) != null ) {
  			// Extend the base object
  			for ( name in options ) {
  				src = target[ name ];
  				copy = options[ name ];
  
  				// Prevent never-ending loop
  				if ( target === copy ) {
  					continue;
  				}
  
  				// Recurse if we're merging plain objects or arrays
  				if ( deep && copy && ( jQuery.isPlainObject(copy) || (copyIsArray = jQuery.isArray(copy)) ) ) {
  					if ( copyIsArray ) {
  						copyIsArray = false;
  						clone = src && jQuery.isArray(src) ? src : [];
  
  					} else {
  						clone = src && jQuery.isPlainObject(src) ? src : {};
  					}
  
  					// Never move original objects, clone them
  					target[ name ] = jQuery.extend( deep, clone, copy );
  
  				// Don't bring in undefined values
  				} else if ( copy !== undefined ) {
  					target[ name ] = copy;
  				}
  			}
  		}
  	}
  
  	// Return the modified object
  	return target;
  };
  
  jQuery.extend({
  	noConflict: function( deep ) {
  		if ( window.$ === jQuery ) {
  			window.$ = _$;
  		}
  
  		if ( deep && window.jQuery === jQuery ) {
  			window.jQuery = _jQuery;
  		}
  
  		return jQuery;
  	},
  
  	// Is the DOM ready to be used? Set to true once it occurs.
  	isReady: false,
  
  	// A counter to track how many items to wait for before
  	// the ready event fires. See #6781
  	readyWait: 1,
  
  	// Hold (or release) the ready event
  	holdReady: function( hold ) {
  		if ( hold ) {
  			jQuery.readyWait++;
  		} else {
  			jQuery.ready( true );
  		}
  	},
  
  	// Handle when the DOM is ready
  	ready: function( wait ) {
  
  		// Abort if there are pending holds or we're already ready
  		if ( wait === true ? --jQuery.readyWait : jQuery.isReady ) {
  			return;
  		}
  
  		// Make sure body exists, at least, in case IE gets a little overzealous (ticket #5443).
  		if ( !document.body ) {
  			return setTimeout( jQuery.ready, 1 );
  		}
  
  		// Remember that the DOM is ready
  		jQuery.isReady = true;
  
  		// If a normal DOM Ready event fired, decrement, and wait if need be
  		if ( wait !== true && --jQuery.readyWait > 0 ) {
  			return;
  		}
  
  		// If there are functions bound, to execute
  		readyList.resolveWith( document, [ jQuery ] );
  
  		// Trigger any bound ready events
  		if ( jQuery.fn.trigger ) {
  			jQuery( document ).trigger("ready").off("ready");
  		}
  	},
  
  	// See test/unit/core.js for details concerning isFunction.
  	// Since version 1.3, DOM methods and functions like alert
  	// aren't supported. They return false on IE (#2968).
  	isFunction: function( obj ) {
  		return jQuery.type(obj) === "function";
  	},
  
  	isArray: Array.isArray || function( obj ) {
  		return jQuery.type(obj) === "array";
  	},
  
  	isWindow: function( obj ) {
  		return obj != null && obj == obj.window;
  	},
  
  	isNumeric: function( obj ) {
  		return !isNaN( parseFloat(obj) ) && isFinite( obj );
  	},
  
  	type: function( obj ) {
  		return obj == null ?
  			String( obj ) :
  			class2type[ core_toString.call(obj) ] || "object";
  	},
  
  	isPlainObject: function( obj ) {
  		// Must be an Object.
  		// Because of IE, we also have to check the presence of the constructor property.
  		// Make sure that DOM nodes and window objects don't pass through, as well
  		if ( !obj || jQuery.type(obj) !== "object" || obj.nodeType || jQuery.isWindow( obj ) ) {
  			return false;
  		}
  
  		try {
  			// Not own constructor property must be Object
  			if ( obj.constructor &&
  				!core_hasOwn.call(obj, "constructor") &&
  				!core_hasOwn.call(obj.constructor.prototype, "isPrototypeOf") ) {
  				return false;
  			}
  		} catch ( e ) {
  			// IE8,9 Will throw exceptions on certain host objects #9897
  			return false;
  		}
  
  		// Own properties are enumerated firstly, so to speed up,
  		// if last one is own, then all properties are own.
  
  		var key;
  		for ( key in obj ) {}
  
  		return key === undefined || core_hasOwn.call( obj, key );
  	},
  
  	isEmptyObject: function( obj ) {
  		var name;
  		for ( name in obj ) {
  			return false;
  		}
  		return true;
  	},
  
  	error: function( msg ) {
  		throw new Error( msg );
  	},
  
  	// data: string of html
  	// context (optional): If specified, the fragment will be created in this context, defaults to document
  	// scripts (optional): If true, will include scripts passed in the html string
  	parseHTML: function( data, context, scripts ) {
  		var parsed;
  		if ( !data || typeof data !== "string" ) {
  			return null;
  		}
  		if ( typeof context === "boolean" ) {
  			scripts = context;
  			context = 0;
  		}
  		context = context || document;
  
  		// Single tag
  		if ( (parsed = rsingleTag.exec( data )) ) {
  			return [ context.createElement( parsed[1] ) ];
  		}
  
  		parsed = jQuery.buildFragment( [ data ], context, scripts ? null : [] );
  		return jQuery.merge( [],
  			(parsed.cacheable ? jQuery.clone( parsed.fragment ) : parsed.fragment).childNodes );
  	},
  
  	parseJSON: function( data ) {
  		if ( !data || typeof data !== "string") {
  			return null;
  		}
  
  		// Make sure leading/trailing whitespace is removed (IE can't handle it)
  		data = jQuery.trim( data );
  
  		// Attempt to parse using the native JSON parser first
  		if ( window.JSON && window.JSON.parse ) {
  			return window.JSON.parse( data );
  		}
  
  		// Make sure the incoming data is actual JSON
  		// Logic borrowed from http://json.org/json2.js
  		if ( rvalidchars.test( data.replace( rvalidescape, "@" )
  			.replace( rvalidtokens, "]" )
  			.replace( rvalidbraces, "")) ) {
  
  			return ( new Function( "return " + data ) )();
  
  		}
  		jQuery.error( "Invalid JSON: " + data );
  	},
  
  	// Cross-browser xml parsing
  	parseXML: function( data ) {
  		var xml, tmp;
  		if ( !data || typeof data !== "string" ) {
  			return null;
  		}
  		try {
  			if ( window.DOMParser ) { // Standard
  				tmp = new DOMParser();
  				xml = tmp.parseFromString( data , "text/xml" );
  			} else { // IE
  				xml = new ActiveXObject( "Microsoft.XMLDOM" );
  				xml.async = "false";
  				xml.loadXML( data );
  			}
  		} catch( e ) {
  			xml = undefined;
  		}
  		if ( !xml || !xml.documentElement || xml.getElementsByTagName( "parsererror" ).length ) {
  			jQuery.error( "Invalid XML: " + data );
  		}
  		return xml;
  	},
  
  	noop: function() {},
  
  	// Evaluates a script in a global context
  	// Workarounds based on findings by Jim Driscoll
  	// http://weblogs.java.net/blog/driscoll/archive/2009/09/08/eval-javascript-global-context
  	globalEval: function( data ) {
  		if ( data && core_rnotwhite.test( data ) ) {
  			// We use execScript on Internet Explorer
  			// We use an anonymous function so that context is window
  			// rather than jQuery in Firefox
  			( window.execScript || function( data ) {
  				window[ "eval" ].call( window, data );
  			} )( data );
  		}
  	},
  
  	// Convert dashed to camelCase; used by the css and data modules
  	// Microsoft forgot to hump their vendor prefix (#9572)
  	camelCase: function( string ) {
  		return string.replace( rmsPrefix, "ms-" ).replace( rdashAlpha, fcamelCase );
  	},
  
  	nodeName: function( elem, name ) {
  		return elem.nodeName && elem.nodeName.toLowerCase() === name.toLowerCase();
  	},
  
  	// args is for internal usage only
  	each: function( obj, callback, args ) {
  		var name,
  			i = 0,
  			length = obj.length,
  			isObj = length === undefined || jQuery.isFunction( obj );
  
  		if ( args ) {
  			if ( isObj ) {
  				for ( name in obj ) {
  					if ( callback.apply( obj[ name ], args ) === false ) {
  						break;
  					}
  				}
  			} else {
  				for ( ; i < length; ) {
  					if ( callback.apply( obj[ i++ ], args ) === false ) {
  						break;
  					}
  				}
  			}
  
  		// A special, fast, case for the most common use of each
  		} else {
  			if ( isObj ) {
  				for ( name in obj ) {
  					if ( callback.call( obj[ name ], name, obj[ name ] ) === false ) {
  						break;
  					}
  				}
  			} else {
  				for ( ; i < length; ) {
  					if ( callback.call( obj[ i ], i, obj[ i++ ] ) === false ) {
  						break;
  					}
  				}
  			}
  		}
  
  		return obj;
  	},
  
  	// Use native String.trim function wherever possible
  	trim: core_trim && !core_trim.call("\uFEFF\xA0") ?
  		function( text ) {
  			return text == null ?
  				"" :
  				core_trim.call( text );
  		} :
  
  		// Otherwise use our own trimming functionality
  		function( text ) {
  			return text == null ?
  				"" :
  				( text + "" ).replace( rtrim, "" );
  		},
  
  	// results is for internal usage only
  	makeArray: function( arr, results ) {
  		var type,
  			ret = results || [];
  
  		if ( arr != null ) {
  			// The window, strings (and functions) also have 'length'
  			// Tweaked logic slightly to handle Blackberry 4.7 RegExp issues #6930
  			type = jQuery.type( arr );
  
  			if ( arr.length == null || type === "string" || type === "function" || type === "regexp" || jQuery.isWindow( arr ) ) {
  				core_push.call( ret, arr );
  			} else {
  				jQuery.merge( ret, arr );
  			}
  		}
  
  		return ret;
  	},
  
  	inArray: function( elem, arr, i ) {
  		var len;
  
  		if ( arr ) {
  			if ( core_indexOf ) {
  				return core_indexOf.call( arr, elem, i );
  			}
  
  			len = arr.length;
  			i = i ? i < 0 ? Math.max( 0, len + i ) : i : 0;
  
  			for ( ; i < len; i++ ) {
  				// Skip accessing in sparse arrays
  				if ( i in arr && arr[ i ] === elem ) {
  					return i;
  				}
  			}
  		}
  
  		return -1;
  	},
  
  	merge: function( first, second ) {
  		var l = second.length,
  			i = first.length,
  			j = 0;
  
  		if ( typeof l === "number" ) {
  			for ( ; j < l; j++ ) {
  				first[ i++ ] = second[ j ];
  			}
  
  		} else {
  			while ( second[j] !== undefined ) {
  				first[ i++ ] = second[ j++ ];
  			}
  		}
  
  		first.length = i;
  
  		return first;
  	},
  
  	grep: function( elems, callback, inv ) {
  		var retVal,
  			ret = [],
  			i = 0,
  			length = elems.length;
  		inv = !!inv;
  
  		// Go through the array, only saving the items
  		// that pass the validator function
  		for ( ; i < length; i++ ) {
  			retVal = !!callback( elems[ i ], i );
  			if ( inv !== retVal ) {
  				ret.push( elems[ i ] );
  			}
  		}
  
  		return ret;
  	},
  
  	// arg is for internal usage only
  	map: function( elems, callback, arg ) {
  		var value, key,
  			ret = [],
  			i = 0,
  			length = elems.length,
  			// jquery objects are treated as arrays
  			isArray = elems instanceof jQuery || length !== undefined && typeof length === "number" && ( ( length > 0 && elems[ 0 ] && elems[ length -1 ] ) || length === 0 || jQuery.isArray( elems ) ) ;
  
  		// Go through the array, translating each of the items to their
  		if ( isArray ) {
  			for ( ; i < length; i++ ) {
  				value = callback( elems[ i ], i, arg );
  
  				if ( value != null ) {
  					ret[ ret.length ] = value;
  				}
  			}
  
  		// Go through every key on the object,
  		} else {
  			for ( key in elems ) {
  				value = callback( elems[ key ], key, arg );
  
  				if ( value != null ) {
  					ret[ ret.length ] = value;
  				}
  			}
  		}
  
  		// Flatten any nested arrays
  		return ret.concat.apply( [], ret );
  	},
  
  	// A global GUID counter for objects
  	guid: 1,
  
  	// Bind a function to a context, optionally partially applying any
  	// arguments.
  	proxy: function( fn, context ) {
  		var tmp, args, proxy;
  
  		if ( typeof context === "string" ) {
  			tmp = fn[ context ];
  			context = fn;
  			fn = tmp;
  		}
  
  		// Quick check to determine if target is callable, in the spec
  		// this throws a TypeError, but we will just return undefined.
  		if ( !jQuery.isFunction( fn ) ) {
  			return undefined;
  		}
  
  		// Simulated bind
  		args = core_slice.call( arguments, 2 );
  		proxy = function() {
  			return fn.apply( context, args.concat( core_slice.call( arguments ) ) );
  		};
  
  		// Set the guid of unique handler to the same of original handler, so it can be removed
  		proxy.guid = fn.guid = fn.guid || jQuery.guid++;
  
  		return proxy;
  	},
  
  	// Multifunctional method to get and set values of a collection
  	// The value/s can optionally be executed if it's a function
  	access: function( elems, fn, key, value, chainable, emptyGet, pass ) {
  		var exec,
  			bulk = key == null,
  			i = 0,
  			length = elems.length;
  
  		// Sets many values
  		if ( key && typeof key === "object" ) {
  			for ( i in key ) {
  				jQuery.access( elems, fn, i, key[i], 1, emptyGet, value );
  			}
  			chainable = 1;
  
  		// Sets one value
  		} else if ( value !== undefined ) {
  			// Optionally, function values get executed if exec is true
  			exec = pass === undefined && jQuery.isFunction( value );
  
  			if ( bulk ) {
  				// Bulk operations only iterate when executing function values
  				if ( exec ) {
  					exec = fn;
  					fn = function( elem, key, value ) {
  						return exec.call( jQuery( elem ), value );
  					};
  
  				// Otherwise they run against the entire set
  				} else {
  					fn.call( elems, value );
  					fn = null;
  				}
  			}
  
  			if ( fn ) {
  				for (; i < length; i++ ) {
  					fn( elems[i], key, exec ? value.call( elems[i], i, fn( elems[i], key ) ) : value, pass );
  				}
  			}
  
  			chainable = 1;
  		}
  
  		return chainable ?
  			elems :
  
  			// Gets
  			bulk ?
  				fn.call( elems ) :
  				length ? fn( elems[0], key ) : emptyGet;
  	},
  
  	now: function() {
  		return ( new Date() ).getTime();
  	}
  });
  
  jQuery.ready.promise = function( obj ) {
  	if ( !readyList ) {
  
  		readyList = jQuery.Deferred();
  
  		// Catch cases where $(document).ready() is called after the browser event has already occurred.
  		// we once tried to use readyState "interactive" here, but it caused issues like the one
  		// discovered by ChrisS here: http://bugs.jquery.com/ticket/12282#comment:15
  		if ( document.readyState === "complete" ) {
  			// Handle it asynchronously to allow scripts the opportunity to delay ready
  			setTimeout( jQuery.ready, 1 );
  
  		// Standards-based browsers support DOMContentLoaded
  		} else if ( document.addEventListener ) {
  			// Use the handy event callback
  			document.addEventListener( "DOMContentLoaded", DOMContentLoaded, false );
  
  			// A fallback to window.onload, that will always work
  			window.addEventListener( "load", jQuery.ready, false );
  
  		// If IE event model is used
  		} else {
  			// Ensure firing before onload, maybe late but safe also for iframes
  			document.attachEvent( "onreadystatechange", DOMContentLoaded );
  
  			// A fallback to window.onload, that will always work
  			window.attachEvent( "onload", jQuery.ready );
  
  			// If IE and not a frame
  			// continually check to see if the document is ready
  			var top = false;
  
  			try {
  				top = window.frameElement == null && document.documentElement;
  			} catch(e) {}
  
  			if ( top && top.doScroll ) {
  				(function doScrollCheck() {
  					if ( !jQuery.isReady ) {
  
  						try {
  							// Use the trick by Diego Perini
  							// http://javascript.nwbox.com/IEContentLoaded/
  							top.doScroll("left");
  						} catch(e) {
  							return setTimeout( doScrollCheck, 50 );
  						}
  
  						// and execute any waiting functions
  						jQuery.ready();
  					}
  				})();
  			}
  		}
  	}
  	return readyList.promise( obj );
  };
  
  // Populate the class2type map
  jQuery.each("Boolean Number String Function Array Date RegExp Object".split(" "), function(i, name) {
  	class2type[ "[object " + name + "]" ] = name.toLowerCase();
  });
  
  // All jQuery objects should point back to these
  rootjQuery = jQuery(document);
  // String to Object options format cache
  var optionsCache = {};
  
  // Convert String-formatted options into Object-formatted ones and store in cache
  function createOptions( options ) {
  	var object = optionsCache[ options ] = {};
  	jQuery.each( options.split( core_rspace ), function( _, flag ) {
  		object[ flag ] = true;
  	});
  	return object;
  }
  
  /*
   * Create a callback list using the following parameters:
   *
   *	options: an optional list of space-separated options that will change how
   *			the callback list behaves or a more traditional option object
   *
   * By default a callback list will act like an event callback list and can be
   * "fired" multiple times.
   *
   * Possible options:
   *
   *	once:			will ensure the callback list can only be fired once (like a Deferred)
   *
   *	memory:			will keep track of previous values and will call any callback added
   *					after the list has been fired right away with the latest "memorized"
   *					values (like a Deferred)
   *
   *	unique:			will ensure a callback can only be added once (no duplicate in the list)
   *
   *	stopOnFalse:	interrupt callings when a callback returns false
   *
   */
  jQuery.Callbacks = function( options ) {
  
  	// Convert options from String-formatted to Object-formatted if needed
  	// (we check in cache first)
  	options = typeof options === "string" ?
  		( optionsCache[ options ] || createOptions( options ) ) :
  		jQuery.extend( {}, options );
  
  	var // Last fire value (for non-forgettable lists)
  		memory,
  		// Flag to know if list was already fired
  		fired,
  		// Flag to know if list is currently firing
  		firing,
  		// First callback to fire (used internally by add and fireWith)
  		firingStart,
  		// End of the loop when firing
  		firingLength,
  		// Index of currently firing callback (modified by remove if needed)
  		firingIndex,
  		// Actual callback list
  		list = [],
  		// Stack of fire calls for repeatable lists
  		stack = !options.once && [],
  		// Fire callbacks
  		fire = function( data ) {
  			memory = options.memory && data;
  			fired = true;
  			firingIndex = firingStart || 0;
  			firingStart = 0;
  			firingLength = list.length;
  			firing = true;
  			for ( ; list && firingIndex < firingLength; firingIndex++ ) {
  				if ( list[ firingIndex ].apply( data[ 0 ], data[ 1 ] ) === false && options.stopOnFalse ) {
  					memory = false; // To prevent further calls using add
  					break;
  				}
  			}
  			firing = false;
  			if ( list ) {
  				if ( stack ) {
  					if ( stack.length ) {
  						fire( stack.shift() );
  					}
  				} else if ( memory ) {
  					list = [];
  				} else {
  					self.disable();
  				}
  			}
  		},
  		// Actual Callbacks object
  		self = {
  			// Add a callback or a collection of callbacks to the list
  			add: function() {
  				if ( list ) {
  					// First, we save the current length
  					var start = list.length;
  					(function add( args ) {
  						jQuery.each( args, function( _, arg ) {
  							var type = jQuery.type( arg );
  							if ( type === "function" ) {
  								if ( !options.unique || !self.has( arg ) ) {
  									list.push( arg );
  								}
  							} else if ( arg && arg.length && type !== "string" ) {
  								// Inspect recursively
  								add( arg );
  							}
  						});
  					})( arguments );
  					// Do we need to add the callbacks to the
  					// current firing batch?
  					if ( firing ) {
  						firingLength = list.length;
  					// With memory, if we're not firing then
  					// we should call right away
  					} else if ( memory ) {
  						firingStart = start;
  						fire( memory );
  					}
  				}
  				return this;
  			},
  			// Remove a callback from the list
  			remove: function() {
  				if ( list ) {
  					jQuery.each( arguments, function( _, arg ) {
  						var index;
  						while( ( index = jQuery.inArray( arg, list, index ) ) > -1 ) {
  							list.splice( index, 1 );
  							// Handle firing indexes
  							if ( firing ) {
  								if ( index <= firingLength ) {
  									firingLength--;
  								}
  								if ( index <= firingIndex ) {
  									firingIndex--;
  								}
  							}
  						}
  					});
  				}
  				return this;
  			},
  			// Control if a given callback is in the list
  			has: function( fn ) {
  				return jQuery.inArray( fn, list ) > -1;
  			},
  			// Remove all callbacks from the list
  			empty: function() {
  				list = [];
  				return this;
  			},
  			// Have the list do nothing anymore
  			disable: function() {
  				list = stack = memory = undefined;
  				return this;
  			},
  			// Is it disabled?
  			disabled: function() {
  				return !list;
  			},
  			// Lock the list in its current state
  			lock: function() {
  				stack = undefined;
  				if ( !memory ) {
  					self.disable();
  				}
  				return this;
  			},
  			// Is it locked?
  			locked: function() {
  				return !stack;
  			},
  			// Call all callbacks with the given context and arguments
  			fireWith: function( context, args ) {
  				args = args || [];
  				args = [ context, args.slice ? args.slice() : args ];
  				if ( list && ( !fired || stack ) ) {
  					if ( firing ) {
  						stack.push( args );
  					} else {
  						fire( args );
  					}
  				}
  				return this;
  			},
  			// Call all the callbacks with the given arguments
  			fire: function() {
  				self.fireWith( this, arguments );
  				return this;
  			},
  			// To know if the callbacks have already been called at least once
  			fired: function() {
  				return !!fired;
  			}
  		};
  
  	return self;
  };
  jQuery.extend({
  
  	Deferred: function( func ) {
  		var tuples = [
  				// action, add listener, listener list, final state
  				[ "resolve", "done", jQuery.Callbacks("once memory"), "resolved" ],
  				[ "reject", "fail", jQuery.Callbacks("once memory"), "rejected" ],
  				[ "notify", "progress", jQuery.Callbacks("memory") ]
  			],
  			state = "pending",
  			promise = {
  				state: function() {
  					return state;
  				},
  				always: function() {
  					deferred.done( arguments ).fail( arguments );
  					return this;
  				},
  				then: function( /* fnDone, fnFail, fnProgress */ ) {
  					var fns = arguments;
  					return jQuery.Deferred(function( newDefer ) {
  						jQuery.each( tuples, function( i, tuple ) {
  							var action = tuple[ 0 ],
  								fn = fns[ i ];
  							// deferred[ done | fail | progress ] for forwarding actions to newDefer
  							deferred[ tuple[1] ]( jQuery.isFunction( fn ) ?
  								function() {
  									var returned = fn.apply( this, arguments );
  									if ( returned && jQuery.isFunction( returned.promise ) ) {
  										returned.promise()
  											.done( newDefer.resolve )
  											.fail( newDefer.reject )
  											.progress( newDefer.notify );
  									} else {
  										newDefer[ action + "With" ]( this === deferred ? newDefer : this, [ returned ] );
  									}
  								} :
  								newDefer[ action ]
  							);
  						});
  						fns = null;
  					}).promise();
  				},
  				// Get a promise for this deferred
  				// If obj is provided, the promise aspect is added to the object
  				promise: function( obj ) {
  					return obj != null ? jQuery.extend( obj, promise ) : promise;
  				}
  			},
  			deferred = {};
  
  		// Keep pipe for back-compat
  		promise.pipe = promise.then;
  
  		// Add list-specific methods
  		jQuery.each( tuples, function( i, tuple ) {
  			var list = tuple[ 2 ],
  				stateString = tuple[ 3 ];
  
  			// promise[ done | fail | progress ] = list.add
  			promise[ tuple[1] ] = list.add;
  
  			// Handle state
  			if ( stateString ) {
  				list.add(function() {
  					// state = [ resolved | rejected ]
  					state = stateString;
  
  				// [ reject_list | resolve_list ].disable; progress_list.lock
  				}, tuples[ i ^ 1 ][ 2 ].disable, tuples[ 2 ][ 2 ].lock );
  			}
  
  			// deferred[ resolve | reject | notify ] = list.fire
  			deferred[ tuple[0] ] = list.fire;
  			deferred[ tuple[0] + "With" ] = list.fireWith;
  		});
  
  		// Make the deferred a promise
  		promise.promise( deferred );
  
  		// Call given func if any
  		if ( func ) {
  			func.call( deferred, deferred );
  		}
  
  		// All done!
  		return deferred;
  	},
  
  	// Deferred helper
  	when: function( subordinate /* , ..., subordinateN */ ) {
  		var i = 0,
  			resolveValues = core_slice.call( arguments ),
  			length = resolveValues.length,
  
  			// the count of uncompleted subordinates
  			remaining = length !== 1 || ( subordinate && jQuery.isFunction( subordinate.promise ) ) ? length : 0,
  
  			// the master Deferred. If resolveValues consist of only a single Deferred, just use that.
  			deferred = remaining === 1 ? subordinate : jQuery.Deferred(),
  
  			// Update function for both resolve and progress values
  			updateFunc = function( i, contexts, values ) {
  				return function( value ) {
  					contexts[ i ] = this;
  					values[ i ] = arguments.length > 1 ? core_slice.call( arguments ) : value;
  					if( values === progressValues ) {
  						deferred.notifyWith( contexts, values );
  					} else if ( !( --remaining ) ) {
  						deferred.resolveWith( contexts, values );
  					}
  				};
  			},
  
  			progressValues, progressContexts, resolveContexts;
  
  		// add listeners to Deferred subordinates; treat others as resolved
  		if ( length > 1 ) {
  			progressValues = new Array( length );
  			progressContexts = new Array( length );
  			resolveContexts = new Array( length );
  			for ( ; i < length; i++ ) {
  				if ( resolveValues[ i ] && jQuery.isFunction( resolveValues[ i ].promise ) ) {
  					resolveValues[ i ].promise()
  						.done( updateFunc( i, resolveContexts, resolveValues ) )
  						.fail( deferred.reject )
  						.progress( updateFunc( i, progressContexts, progressValues ) );
  				} else {
  					--remaining;
  				}
  			}
  		}
  
  		// if we're not waiting on anything, resolve the master
  		if ( !remaining ) {
  			deferred.resolveWith( resolveContexts, resolveValues );
  		}
  
  		return deferred.promise();
  	}
  });
  jQuery.support = (function() {
  
  	var support,
  		all,
  		a,
  		select,
  		opt,
  		input,
  		fragment,
  		eventName,
  		i,
  		isSupported,
  		clickFn,
  		div = document.createElement("div");
  
  	// Setup
  	div.setAttribute( "className", "t" );
  	div.innerHTML = "  <link/><table></table><a href='/a'>a</a><input type='checkbox'/>";
  
  	// Support tests won't run in some limited or non-browser environments
  	all = div.getElementsByTagName("*");
  	a = div.getElementsByTagName("a")[ 0 ];
  	if ( !all || !a || !all.length ) {
  		return {};
  	}
  
  	// First batch of tests
  	select = document.createElement("select");
  	opt = select.appendChild( document.createElement("option") );
  	input = div.getElementsByTagName("input")[ 0 ];
  
  	a.style.cssText = "top:1px;float:left;opacity:.5";
  	support = {
  		// IE strips leading whitespace when .innerHTML is used
  		leadingWhitespace: ( div.firstChild.nodeType === 3 ),
  
  		// Make sure that tbody elements aren't automatically inserted
  		// IE will insert them into empty tables
  		tbody: !div.getElementsByTagName("tbody").length,
  
  		// Make sure that link elements get serialized correctly by innerHTML
  		// This requires a wrapper element in IE
  		htmlSerialize: !!div.getElementsByTagName("link").length,
  
  		// Get the style information from getAttribute
  		// (IE uses .cssText instead)
  		style: /top/.test( a.getAttribute("style") ),
  
  		// Make sure that URLs aren't manipulated
  		// (IE normalizes it by default)
  		hrefNormalized: ( a.getAttribute("href") === "/a" ),
  
  		// Make sure that element opacity exists
  		// (IE uses filter instead)
  		// Use a regex to work around a WebKit issue. See #5145
  		opacity: /^0.5/.test( a.style.opacity ),
  
  		// Verify style float existence
  		// (IE uses styleFloat instead of cssFloat)
  		cssFloat: !!a.style.cssFloat,
  
  		// Make sure that if no value is specified for a checkbox
  		// that it defaults to "on".
  		// (WebKit defaults to "" instead)
  		checkOn: ( input.value === "on" ),
  
  		// Make sure that a selected-by-default option has a working selected property.
  		// (WebKit defaults to false instead of true, IE too, if it's in an optgroup)
  		optSelected: opt.selected,
  
  		// Test setAttribute on camelCase class. If it works, we need attrFixes when doing get/setAttribute (ie6/7)
  		getSetAttribute: div.className !== "t",
  
  		// Tests for enctype support on a form (#6743)
  		enctype: !!document.createElement("form").enctype,
  
  		// Makes sure cloning an html5 element does not cause problems
  		// Where outerHTML is undefined, this still works
  		html5Clone: document.createElement("nav").cloneNode( true ).outerHTML !== "<:nav></:nav>",
  
  		// jQuery.support.boxModel DEPRECATED in 1.8 since we don't support Quirks Mode
  		boxModel: ( document.compatMode === "CSS1Compat" ),
  
  		// Will be defined later
  		submitBubbles: true,
  		changeBubbles: true,
  		focusinBubbles: false,
  		deleteExpando: true,
  		noCloneEvent: true,
  		inlineBlockNeedsLayout: false,
  		shrinkWrapBlocks: false,
  		reliableMarginRight: true,
  		boxSizingReliable: true,
  		pixelPosition: false
  	};
  
  	// Make sure checked status is properly cloned
  	input.checked = true;
  	support.noCloneChecked = input.cloneNode( true ).checked;
  
  	// Make sure that the options inside disabled selects aren't marked as disabled
  	// (WebKit marks them as disabled)
  	select.disabled = true;
  	support.optDisabled = !opt.disabled;
  
  	// Test to see if it's possible to delete an expando from an element
  	// Fails in Internet Explorer
  	try {
  		delete div.test;
  	} catch( e ) {
  		support.deleteExpando = false;
  	}
  
  	if ( !div.addEventListener && div.attachEvent && div.fireEvent ) {
  		div.attachEvent( "onclick", clickFn = function() {
  			// Cloning a node shouldn't copy over any
  			// bound event handlers (IE does this)
  			support.noCloneEvent = false;
  		});
  		div.cloneNode( true ).fireEvent("onclick");
  		div.detachEvent( "onclick", clickFn );
  	}
  
  	// Check if a radio maintains its value
  	// after being appended to the DOM
  	input = document.createElement("input");
  	input.value = "t";
  	input.setAttribute( "type", "radio" );
  	support.radioValue = input.value === "t";
  
  	input.setAttribute( "checked", "checked" );
  
  	// #11217 - WebKit loses check when the name is after the checked attribute
  	input.setAttribute( "name", "t" );
  
  	div.appendChild( input );
  	fragment = document.createDocumentFragment();
  	fragment.appendChild( div.lastChild );
  
  	// WebKit doesn't clone checked state correctly in fragments
  	support.checkClone = fragment.cloneNode( true ).cloneNode( true ).lastChild.checked;
  
  	// Check if a disconnected checkbox will retain its checked
  	// value of true after appended to the DOM (IE6/7)
  	support.appendChecked = input.checked;
  
  	fragment.removeChild( input );
  	fragment.appendChild( div );
  
  	// Technique from Juriy Zaytsev
  	// http://perfectionkills.com/detecting-event-support-without-browser-sniffing/
  	// We only care about the case where non-standard event systems
  	// are used, namely in IE. Short-circuiting here helps us to
  	// avoid an eval call (in setAttribute) which can cause CSP
  	// to go haywire. See: https://developer.mozilla.org/en/Security/CSP
  	if ( div.attachEvent ) {
  		for ( i in {
  			submit: true,
  			change: true,
  			focusin: true
  		}) {
  			eventName = "on" + i;
  			isSupported = ( eventName in div );
  			if ( !isSupported ) {
  				div.setAttribute( eventName, "return;" );
  				isSupported = ( typeof div[ eventName ] === "function" );
  			}
  			support[ i + "Bubbles" ] = isSupported;
  		}
  	}
  
  	// Run tests that need a body at doc ready
  	jQuery(function() {
  		var container, div, tds, marginDiv,
  			divReset = "padding:0;margin:0;border:0;display:block;overflow:hidden;",
  			body = document.getElementsByTagName("body")[0];
  
  		if ( !body ) {
  			// Return for frameset docs that don't have a body
  			return;
  		}
  
  		container = document.createElement("div");
  		container.style.cssText = "visibility:hidden;border:0;width:0;height:0;position:static;top:0;margin-top:1px";
  		body.insertBefore( container, body.firstChild );
  
  		// Construct the test element
  		div = document.createElement("div");
  		container.appendChild( div );
  
  		// Check if table cells still have offsetWidth/Height when they are set
  		// to display:none and there are still other visible table cells in a
  		// table row; if so, offsetWidth/Height are not reliable for use when
  		// determining if an element has been hidden directly using
  		// display:none (it is still safe to use offsets if a parent element is
  		// hidden; don safety goggles and see bug #4512 for more information).
  		// (only IE 8 fails this test)
  		div.innerHTML = "<table><tr><td></td><td>t</td></tr></table>";
  		tds = div.getElementsByTagName("td");
  		tds[ 0 ].style.cssText = "padding:0;margin:0;border:0;display:none";
  		isSupported = ( tds[ 0 ].offsetHeight === 0 );
  
  		tds[ 0 ].style.display = "";
  		tds[ 1 ].style.display = "none";
  
  		// Check if empty table cells still have offsetWidth/Height
  		// (IE <= 8 fail this test)
  		support.reliableHiddenOffsets = isSupported && ( tds[ 0 ].offsetHeight === 0 );
  
  		// Check box-sizing and margin behavior
  		div.innerHTML = "";
  		div.style.cssText = "box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;padding:1px;border:1px;display:block;width:4px;margin-top:1%;position:absolute;top:1%;";
  		support.boxSizing = ( div.offsetWidth === 4 );
  		support.doesNotIncludeMarginInBodyOffset = ( body.offsetTop !== 1 );
  
  		// NOTE: To any future maintainer, we've window.getComputedStyle
  		// because jsdom on node.js will break without it.
  		if ( window.getComputedStyle ) {
  			support.pixelPosition = ( window.getComputedStyle( div, null ) || {} ).top !== "1%";
  			support.boxSizingReliable = ( window.getComputedStyle( div, null ) || { width: "4px" } ).width === "4px";
  
  			// Check if div with explicit width and no margin-right incorrectly
  			// gets computed margin-right based on width of container. For more
  			// info see bug #3333
  			// Fails in WebKit before Feb 2011 nightlies
  			// WebKit Bug 13343 - getComputedStyle returns wrong value for margin-right
  			marginDiv = document.createElement("div");
  			marginDiv.style.cssText = div.style.cssText = divReset;
  			marginDiv.style.marginRight = marginDiv.style.width = "0";
  			div.style.width = "1px";
  			div.appendChild( marginDiv );
  			support.reliableMarginRight =
  				!parseFloat( ( window.getComputedStyle( marginDiv, null ) || {} ).marginRight );
  		}
  
  		if ( typeof div.style.zoom !== "undefined" ) {
  			// Check if natively block-level elements act like inline-block
  			// elements when setting their display to 'inline' and giving
  			// them layout
  			// (IE < 8 does this)
  			div.innerHTML = "";
  			div.style.cssText = divReset + "width:1px;padding:1px;display:inline;zoom:1";
  			support.inlineBlockNeedsLayout = ( div.offsetWidth === 3 );
  
  			// Check if elements with layout shrink-wrap their children
  			// (IE 6 does this)
  			div.style.display = "block";
  			div.style.overflow = "visible";
  			div.innerHTML = "<div></div>";
  			div.firstChild.style.width = "5px";
  			support.shrinkWrapBlocks = ( div.offsetWidth !== 3 );
  
  			container.style.zoom = 1;
  		}
  
  		// Null elements to avoid leaks in IE
  		body.removeChild( container );
  		container = div = tds = marginDiv = null;
  	});
  
  	// Null elements to avoid leaks in IE
  	fragment.removeChild( div );
  	all = a = select = opt = input = fragment = div = null;
  
  	return support;
  })();
  var rbrace = /(?:\{[\s\S]*\}|\[[\s\S]*\])$/,
  	rmultiDash = /([A-Z])/g;
  
  jQuery.extend({
  	cache: {},
  
  	deletedIds: [],
  
  	// Remove at next major release (1.9/2.0)
  	uuid: 0,
  
  	// Unique for each copy of jQuery on the page
  	// Non-digits removed to match rinlinejQuery
  	expando: "jQuery" + ( jQuery.fn.jquery + Math.random() ).replace( /\D/g, "" ),
  
  	// The following elements throw uncatchable exceptions if you
  	// attempt to add expando properties to them.
  	noData: {
  		"embed": true,
  		// Ban all objects except for Flash (which handle expandos)
  		"object": "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000",
  		"applet": true
  	},
  
  	hasData: function( elem ) {
  		elem = elem.nodeType ? jQuery.cache[ elem[jQuery.expando] ] : elem[ jQuery.expando ];
  		return !!elem && !isEmptyDataObject( elem );
  	},
  
  	data: function( elem, name, data, pvt /* Internal Use Only */ ) {
  		if ( !jQuery.acceptData( elem ) ) {
  			return;
  		}
  
  		var thisCache, ret,
  			internalKey = jQuery.expando,
  			getByName = typeof name === "string",
  
  			// We have to handle DOM nodes and JS objects differently because IE6-7
  			// can't GC object references properly across the DOM-JS boundary
  			isNode = elem.nodeType,
  
  			// Only DOM nodes need the global jQuery cache; JS object data is
  			// attached directly to the object so GC can occur automatically
  			cache = isNode ? jQuery.cache : elem,
  
  			// Only defining an ID for JS objects if its cache already exists allows
  			// the code to shortcut on the same path as a DOM node with no cache
  			id = isNode ? elem[ internalKey ] : elem[ internalKey ] && internalKey;
  
  		// Avoid doing any more work than we need to when trying to get data on an
  		// object that has no data at all
  		if ( (!id || !cache[id] || (!pvt && !cache[id].data)) && getByName && data === undefined ) {
  			return;
  		}
  
  		if ( !id ) {
  			// Only DOM nodes need a new unique ID for each element since their data
  			// ends up in the global cache
  			if ( isNode ) {
  				elem[ internalKey ] = id = jQuery.deletedIds.pop() || jQuery.guid++;
  			} else {
  				id = internalKey;
  			}
  		}
  
  		if ( !cache[ id ] ) {
  			cache[ id ] = {};
  
  			// Avoids exposing jQuery metadata on plain JS objects when the object
  			// is serialized using JSON.stringify
  			if ( !isNode ) {
  				cache[ id ].toJSON = jQuery.noop;
  			}
  		}
  
  		// An object can be passed to jQuery.data instead of a key/value pair; this gets
  		// shallow copied over onto the existing cache
  		if ( typeof name === "object" || typeof name === "function" ) {
  			if ( pvt ) {
  				cache[ id ] = jQuery.extend( cache[ id ], name );
  			} else {
  				cache[ id ].data = jQuery.extend( cache[ id ].data, name );
  			}
  		}
  
  		thisCache = cache[ id ];
  
  		// jQuery data() is stored in a separate object inside the object's internal data
  		// cache in order to avoid key collisions between internal data and user-defined
  		// data.
  		if ( !pvt ) {
  			if ( !thisCache.data ) {
  				thisCache.data = {};
  			}
  
  			thisCache = thisCache.data;
  		}
  
  		if ( data !== undefined ) {
  			thisCache[ jQuery.camelCase( name ) ] = data;
  		}
  
  		// Check for both converted-to-camel and non-converted data property names
  		// If a data property was specified
  		if ( getByName ) {
  
  			// First Try to find as-is property data
  			ret = thisCache[ name ];
  
  			// Test for null|undefined property data
  			if ( ret == null ) {
  
  				// Try to find the camelCased property
  				ret = thisCache[ jQuery.camelCase( name ) ];
  			}
  		} else {
  			ret = thisCache;
  		}
  
  		return ret;
  	},
  
  	removeData: function( elem, name, pvt /* Internal Use Only */ ) {
  		if ( !jQuery.acceptData( elem ) ) {
  			return;
  		}
  
  		var thisCache, i, l,
  
  			isNode = elem.nodeType,
  
  			// See jQuery.data for more information
  			cache = isNode ? jQuery.cache : elem,
  			id = isNode ? elem[ jQuery.expando ] : jQuery.expando;
  
  		// If there is already no cache entry for this object, there is no
  		// purpose in continuing
  		if ( !cache[ id ] ) {
  			return;
  		}
  
  		if ( name ) {
  
  			thisCache = pvt ? cache[ id ] : cache[ id ].data;
  
  			if ( thisCache ) {
  
  				// Support array or space separated string names for data keys
  				if ( !jQuery.isArray( name ) ) {
  
  					// try the string as a key before any manipulation
  					if ( name in thisCache ) {
  						name = [ name ];
  					} else {
  
  						// split the camel cased version by spaces unless a key with the spaces exists
  						name = jQuery.camelCase( name );
  						if ( name in thisCache ) {
  							name = [ name ];
  						} else {
  							name = name.split(" ");
  						}
  					}
  				}
  
  				for ( i = 0, l = name.length; i < l; i++ ) {
  					delete thisCache[ name[i] ];
  				}
  
  				// If there is no data left in the cache, we want to continue
  				// and let the cache object itself get destroyed
  				if ( !( pvt ? isEmptyDataObject : jQuery.isEmptyObject )( thisCache ) ) {
  					return;
  				}
  			}
  		}
  
  		// See jQuery.data for more information
  		if ( !pvt ) {
  			delete cache[ id ].data;
  
  			// Don't destroy the parent cache unless the internal data object
  			// had been the only thing left in it
  			if ( !isEmptyDataObject( cache[ id ] ) ) {
  				return;
  			}
  		}
  
  		// Destroy the cache
  		if ( isNode ) {
  			jQuery.cleanData( [ elem ], true );
  
  		// Use delete when supported for expandos or `cache` is not a window per isWindow (#10080)
  		} else if ( jQuery.support.deleteExpando || cache != cache.window ) {
  			delete cache[ id ];
  
  		// When all else fails, null
  		} else {
  			cache[ id ] = null;
  		}
  	},
  
  	// For internal use only.
  	_data: function( elem, name, data ) {
  		return jQuery.data( elem, name, data, true );
  	},
  
  	// A method for determining if a DOM node can handle the data expando
  	acceptData: function( elem ) {
  		var noData = elem.nodeName && jQuery.noData[ elem.nodeName.toLowerCase() ];
  
  		// nodes accept data unless otherwise specified; rejection can be conditional
  		return !noData || noData !== true && elem.getAttribute("classid") === noData;
  	}
  });
  
  jQuery.fn.extend({
  	data: function( key, value ) {
  		var parts, part, attr, name, l,
  			elem = this[0],
  			i = 0,
  			data = null;
  
  		// Gets all values
  		if ( key === undefined ) {
  			if ( this.length ) {
  				data = jQuery.data( elem );
  
  				if ( elem.nodeType === 1 && !jQuery._data( elem, "parsedAttrs" ) ) {
  					attr = elem.attributes;
  					for ( l = attr.length; i < l; i++ ) {
  						name = attr[i].name;
  
  						if ( !name.indexOf( "data-" ) ) {
  							name = jQuery.camelCase( name.substring(5) );
  
  							dataAttr( elem, name, data[ name ] );
  						}
  					}
  					jQuery._data( elem, "parsedAttrs", true );
  				}
  			}
  
  			return data;
  		}
  
  		// Sets multiple values
  		if ( typeof key === "object" ) {
  			return this.each(function() {
  				jQuery.data( this, key );
  			});
  		}
  
  		parts = key.split( ".", 2 );
  		parts[1] = parts[1] ? "." + parts[1] : "";
  		part = parts[1] + "!";
  
  		return jQuery.access( this, function( value ) {
  
  			if ( value === undefined ) {
  				data = this.triggerHandler( "getData" + part, [ parts[0] ] );
  
  				// Try to fetch any internally stored data first
  				if ( data === undefined && elem ) {
  					data = jQuery.data( elem, key );
  					data = dataAttr( elem, key, data );
  				}
  
  				return data === undefined && parts[1] ?
  					this.data( parts[0] ) :
  					data;
  			}
  
  			parts[1] = value;
  			this.each(function() {
  				var self = jQuery( this );
  
  				self.triggerHandler( "setData" + part, parts );
  				jQuery.data( this, key, value );
  				self.triggerHandler( "changeData" + part, parts );
  			});
  		}, null, value, arguments.length > 1, null, false );
  	},
  
  	removeData: function( key ) {
  		return this.each(function() {
  			jQuery.removeData( this, key );
  		});
  	}
  });
  
  function dataAttr( elem, key, data ) {
  	// If nothing was found internally, try to fetch any
  	// data from the HTML5 data-* attribute
  	if ( data === undefined && elem.nodeType === 1 ) {
  
  		var name = "data-" + key.replace( rmultiDash, "-$1" ).toLowerCase();
  
  		data = elem.getAttribute( name );
  
  		if ( typeof data === "string" ) {
  			try {
  				data = data === "true" ? true :
  				data === "false" ? false :
  				data === "null" ? null :
  				// Only convert to a number if it doesn't change the string
  				+data + "" === data ? +data :
  				rbrace.test( data ) ? jQuery.parseJSON( data ) :
  					data;
  			} catch( e ) {}
  
  			// Make sure we set the data so it isn't changed later
  			jQuery.data( elem, key, data );
  
  		} else {
  			data = undefined;
  		}
  	}
  
  	return data;
  }
  
  // checks a cache object for emptiness
  function isEmptyDataObject( obj ) {
  	var name;
  	for ( name in obj ) {
  
  		// if the public data object is empty, the private is still empty
  		if ( name === "data" && jQuery.isEmptyObject( obj[name] ) ) {
  			continue;
  		}
  		if ( name !== "toJSON" ) {
  			return false;
  		}
  	}
  
  	return true;
  }
  jQuery.extend({
  	queue: function( elem, type, data ) {
  		var queue;
  
  		if ( elem ) {
  			type = ( type || "fx" ) + "queue";
  			queue = jQuery._data( elem, type );
  
  			// Speed up dequeue by getting out quickly if this is just a lookup
  			if ( data ) {
  				if ( !queue || jQuery.isArray(data) ) {
  					queue = jQuery._data( elem, type, jQuery.makeArray(data) );
  				} else {
  					queue.push( data );
  				}
  			}
  			return queue || [];
  		}
  	},
  
  	dequeue: function( elem, type ) {
  		type = type || "fx";
  
  		var queue = jQuery.queue( elem, type ),
  			startLength = queue.length,
  			fn = queue.shift(),
  			hooks = jQuery._queueHooks( elem, type ),
  			next = function() {
  				jQuery.dequeue( elem, type );
  			};
  
  		// If the fx queue is dequeued, always remove the progress sentinel
  		if ( fn === "inprogress" ) {
  			fn = queue.shift();
  			startLength--;
  		}
  
  		if ( fn ) {
  
  			// Add a progress sentinel to prevent the fx queue from being
  			// automatically dequeued
  			if ( type === "fx" ) {
  				queue.unshift( "inprogress" );
  			}
  
  			// clear up the last queue stop function
  			delete hooks.stop;
  			fn.call( elem, next, hooks );
  		}
  
  		if ( !startLength && hooks ) {
  			hooks.empty.fire();
  		}
  	},
  
  	// not intended for public consumption - generates a queueHooks object, or returns the current one
  	_queueHooks: function( elem, type ) {
  		var key = type + "queueHooks";
  		return jQuery._data( elem, key ) || jQuery._data( elem, key, {
  			empty: jQuery.Callbacks("once memory").add(function() {
  				jQuery.removeData( elem, type + "queue", true );
  				jQuery.removeData( elem, key, true );
  			})
  		});
  	}
  });
  
  jQuery.fn.extend({
  	queue: function( type, data ) {
  		var setter = 2;
  
  		if ( typeof type !== "string" ) {
  			data = type;
  			type = "fx";
  			setter--;
  		}
  
  		if ( arguments.length < setter ) {
  			return jQuery.queue( this[0], type );
  		}
  
  		return data === undefined ?
  			this :
  			this.each(function() {
  				var queue = jQuery.queue( this, type, data );
  
  				// ensure a hooks for this queue
  				jQuery._queueHooks( this, type );
  
  				if ( type === "fx" && queue[0] !== "inprogress" ) {
  					jQuery.dequeue( this, type );
  				}
  			});
  	},
  	dequeue: function( type ) {
  		return this.each(function() {
  			jQuery.dequeue( this, type );
  		});
  	},
  	// Based off of the plugin by Clint Helfers, with permission.
  	// http://blindsignals.com/index.php/2009/07/jquery-delay/
  	delay: function( time, type ) {
  		time = jQuery.fx ? jQuery.fx.speeds[ time ] || time : time;
  		type = type || "fx";
  
  		return this.queue( type, function( next, hooks ) {
  			var timeout = setTimeout( next, time );
  			hooks.stop = function() {
  				clearTimeout( timeout );
  			};
  		});
  	},
  	clearQueue: function( type ) {
  		return this.queue( type || "fx", [] );
  	},
  	// Get a promise resolved when queues of a certain type
  	// are emptied (fx is the type by default)
  	promise: function( type, obj ) {
  		var tmp,
  			count = 1,
  			defer = jQuery.Deferred(),
  			elements = this,
  			i = this.length,
  			resolve = function() {
  				if ( !( --count ) ) {
  					defer.resolveWith( elements, [ elements ] );
  				}
  			};
  
  		if ( typeof type !== "string" ) {
  			obj = type;
  			type = undefined;
  		}
  		type = type || "fx";
  
  		while( i-- ) {
  			tmp = jQuery._data( elements[ i ], type + "queueHooks" );
  			if ( tmp && tmp.empty ) {
  				count++;
  				tmp.empty.add( resolve );
  			}
  		}
  		resolve();
  		return defer.promise( obj );
  	}
  });
  var nodeHook, boolHook, fixSpecified,
  	rclass = /[\t\r\n]/g,
  	rreturn = /\r/g,
  	rtype = /^(?:button|input)$/i,
  	rfocusable = /^(?:button|input|object|select|textarea)$/i,
  	rclickable = /^a(?:rea|)$/i,
  	rboolean = /^(?:autofocus|autoplay|async|checked|controls|defer|disabled|hidden|loop|multiple|open|readonly|required|scoped|selected)$/i,
  	getSetAttribute = jQuery.support.getSetAttribute;
  
  jQuery.fn.extend({
  	attr: function( name, value ) {
  		return jQuery.access( this, jQuery.attr, name, value, arguments.length > 1 );
  	},
  
  	removeAttr: function( name ) {
  		return this.each(function() {
  			jQuery.removeAttr( this, name );
  		});
  	},
  
  	prop: function( name, value ) {
  		return jQuery.access( this, jQuery.prop, name, value, arguments.length > 1 );
  	},
  
  	removeProp: function( name ) {
  		name = jQuery.propFix[ name ] || name;
  		return this.each(function() {
  			// try/catch handles cases where IE balks (such as removing a property on window)
  			try {
  				this[ name ] = undefined;
  				delete this[ name ];
  			} catch( e ) {}
  		});
  	},
  
  	addClass: function( value ) {
  		var classNames, i, l, elem,
  			setClass, c, cl;
  
  		if ( jQuery.isFunction( value ) ) {
  			return this.each(function( j ) {
  				jQuery( this ).addClass( value.call(this, j, this.className) );
  			});
  		}
  
  		if ( value && typeof value === "string" ) {
  			classNames = value.split( core_rspace );
  
  			for ( i = 0, l = this.length; i < l; i++ ) {
  				elem = this[ i ];
  
  				if ( elem.nodeType === 1 ) {
  					if ( !elem.className && classNames.length === 1 ) {
  						elem.className = value;
  
  					} else {
  						setClass = " " + elem.className + " ";
  
  						for ( c = 0, cl = classNames.length; c < cl; c++ ) {
  							if ( setClass.indexOf( " " + classNames[ c ] + " " ) < 0 ) {
  								setClass += classNames[ c ] + " ";
  							}
  						}
  						elem.className = jQuery.trim( setClass );
  					}
  				}
  			}
  		}
  
  		return this;
  	},
  
  	removeClass: function( value ) {
  		var removes, className, elem, c, cl, i, l;
  
  		if ( jQuery.isFunction( value ) ) {
  			return this.each(function( j ) {
  				jQuery( this ).removeClass( value.call(this, j, this.className) );
  			});
  		}
  		if ( (value && typeof value === "string") || value === undefined ) {
  			removes = ( value || "" ).split( core_rspace );
  
  			for ( i = 0, l = this.length; i < l; i++ ) {
  				elem = this[ i ];
  				if ( elem.nodeType === 1 && elem.className ) {
  
  					className = (" " + elem.className + " ").replace( rclass, " " );
  
  					// loop over each item in the removal list
  					for ( c = 0, cl = removes.length; c < cl; c++ ) {
  						// Remove until there is nothing to remove,
  						while ( className.indexOf(" " + removes[ c ] + " ") >= 0 ) {
  							className = className.replace( " " + removes[ c ] + " " , " " );
  						}
  					}
  					elem.className = value ? jQuery.trim( className ) : "";
  				}
  			}
  		}
  
  		return this;
  	},
  
  	toggleClass: function( value, stateVal ) {
  		var type = typeof value,
  			isBool = typeof stateVal === "boolean";
  
  		if ( jQuery.isFunction( value ) ) {
  			return this.each(function( i ) {
  				jQuery( this ).toggleClass( value.call(this, i, this.className, stateVal), stateVal );
  			});
  		}
  
  		return this.each(function() {
  			if ( type === "string" ) {
  				// toggle individual class names
  				var className,
  					i = 0,
  					self = jQuery( this ),
  					state = stateVal,
  					classNames = value.split( core_rspace );
  
  				while ( (className = classNames[ i++ ]) ) {
  					// check each className given, space separated list
  					state = isBool ? state : !self.hasClass( className );
  					self[ state ? "addClass" : "removeClass" ]( className );
  				}
  
  			} else if ( type === "undefined" || type === "boolean" ) {
  				if ( this.className ) {
  					// store className if set
  					jQuery._data( this, "__className__", this.className );
  				}
  
  				// toggle whole className
  				this.className = this.className || value === false ? "" : jQuery._data( this, "__className__" ) || "";
  			}
  		});
  	},
  
  	hasClass: function( selector ) {
  		var className = " " + selector + " ",
  			i = 0,
  			l = this.length;
  		for ( ; i < l; i++ ) {
  			if ( this[i].nodeType === 1 && (" " + this[i].className + " ").replace(rclass, " ").indexOf( className ) >= 0 ) {
  				return true;
  			}
  		}
  
  		return false;
  	},
  
  	val: function( value ) {
  		var hooks, ret, isFunction,
  			elem = this[0];
  
  		if ( !arguments.length ) {
  			if ( elem ) {
  				hooks = jQuery.valHooks[ elem.type ] || jQuery.valHooks[ elem.nodeName.toLowerCase() ];
  
  				if ( hooks && "get" in hooks && (ret = hooks.get( elem, "value" )) !== undefined ) {
  					return ret;
  				}
  
  				ret = elem.value;
  
  				return typeof ret === "string" ?
  					// handle most common string cases
  					ret.replace(rreturn, "") :
  					// handle cases where value is null/undef or number
  					ret == null ? "" : ret;
  			}
  
  			return;
  		}
  
  		isFunction = jQuery.isFunction( value );
  
  		return this.each(function( i ) {
  			var val,
  				self = jQuery(this);
  
  			if ( this.nodeType !== 1 ) {
  				return;
  			}
  
  			if ( isFunction ) {
  				val = value.call( this, i, self.val() );
  			} else {
  				val = value;
  			}
  
  			// Treat null/undefined as ""; convert numbers to string
  			if ( val == null ) {
  				val = "";
  			} else if ( typeof val === "number" ) {
  				val += "";
  			} else if ( jQuery.isArray( val ) ) {
  				val = jQuery.map(val, function ( value ) {
  					return value == null ? "" : value + "";
  				});
  			}
  
  			hooks = jQuery.valHooks[ this.type ] || jQuery.valHooks[ this.nodeName.toLowerCase() ];
  
  			// If set returns undefined, fall back to normal setting
  			if ( !hooks || !("set" in hooks) || hooks.set( this, val, "value" ) === undefined ) {
  				this.value = val;
  			}
  		});
  	}
  });
  
  jQuery.extend({
  	valHooks: {
  		option: {
  			get: function( elem ) {
  				// attributes.value is undefined in Blackberry 4.7 but
  				// uses .value. See #6932
  				var val = elem.attributes.value;
  				return !val || val.specified ? elem.value : elem.text;
  			}
  		},
  		select: {
  			get: function( elem ) {
  				var value, option,
  					options = elem.options,
  					index = elem.selectedIndex,
  					one = elem.type === "select-one" || index < 0,
  					values = one ? null : [],
  					max = one ? index + 1 : options.length,
  					i = index < 0 ?
  						max :
  						one ? index : 0;
  
  				// Loop through all the selected options
  				for ( ; i < max; i++ ) {
  					option = options[ i ];
  
  					// oldIE doesn't update selected after form reset (#2551)
  					if ( ( option.selected || i === index ) &&
  							// Don't return options that are disabled or in a disabled optgroup
  							( jQuery.support.optDisabled ? !option.disabled : option.getAttribute("disabled") === null ) &&
  							( !option.parentNode.disabled || !jQuery.nodeName( option.parentNode, "optgroup" ) ) ) {
  
  						// Get the specific value for the option
  						value = jQuery( option ).val();
  
  						// We don't need an array for one selects
  						if ( one ) {
  							return value;
  						}
  
  						// Multi-Selects return an array
  						values.push( value );
  					}
  				}
  
  				return values;
  			},
  
  			set: function( elem, value ) {
  				var values = jQuery.makeArray( value );
  
  				jQuery(elem).find("option").each(function() {
  					this.selected = jQuery.inArray( jQuery(this).val(), values ) >= 0;
  				});
  
  				if ( !values.length ) {
  					elem.selectedIndex = -1;
  				}
  				return values;
  			}
  		}
  	},
  
  	// Unused in 1.8, left in so attrFn-stabbers won't die; remove in 1.9
  	attrFn: {},
  
  	attr: function( elem, name, value, pass ) {
  		var ret, hooks, notxml,
  			nType = elem.nodeType;
  
  		// don't get/set attributes on text, comment and attribute nodes
  		if ( !elem || nType === 3 || nType === 8 || nType === 2 ) {
  			return;
  		}
  
  		if ( pass && jQuery.isFunction( jQuery.fn[ name ] ) ) {
  			return jQuery( elem )[ name ]( value );
  		}
  
  		// Fallback to prop when attributes are not supported
  		if ( typeof elem.getAttribute === "undefined" ) {
  			return jQuery.prop( elem, name, value );
  		}
  
  		notxml = nType !== 1 || !jQuery.isXMLDoc( elem );
  
  		// All attributes are lowercase
  		// Grab necessary hook if one is defined
  		if ( notxml ) {
  			name = name.toLowerCase();
  			hooks = jQuery.attrHooks[ name ] || ( rboolean.test( name ) ? boolHook : nodeHook );
  		}
  
  		if ( value !== undefined ) {
  
  			if ( value === null ) {
  				jQuery.removeAttr( elem, name );
  				return;
  
  			} else if ( hooks && "set" in hooks && notxml && (ret = hooks.set( elem, value, name )) !== undefined ) {
  				return ret;
  
  			} else {
  				elem.setAttribute( name, value + "" );
  				return value;
  			}
  
  		} else if ( hooks && "get" in hooks && notxml && (ret = hooks.get( elem, name )) !== null ) {
  			return ret;
  
  		} else {
  
  			ret = elem.getAttribute( name );
  
  			// Non-existent attributes return null, we normalize to undefined
  			return ret === null ?
  				undefined :
  				ret;
  		}
  	},
  
  	removeAttr: function( elem, value ) {
  		var propName, attrNames, name, isBool,
  			i = 0;
  
  		if ( value && elem.nodeType === 1 ) {
  
  			attrNames = value.split( core_rspace );
  
  			for ( ; i < attrNames.length; i++ ) {
  				name = attrNames[ i ];
  
  				if ( name ) {
  					propName = jQuery.propFix[ name ] || name;
  					isBool = rboolean.test( name );
  
  					// See #9699 for explanation of this approach (setting first, then removal)
  					// Do not do this for boolean attributes (see #10870)
  					if ( !isBool ) {
  						jQuery.attr( elem, name, "" );
  					}
  					elem.removeAttribute( getSetAttribute ? name : propName );
  
  					// Set corresponding property to false for boolean attributes
  					if ( isBool && propName in elem ) {
  						elem[ propName ] = false;
  					}
  				}
  			}
  		}
  	},
  
  	attrHooks: {
  		type: {
  			set: function( elem, value ) {
  				// We can't allow the type property to be changed (since it causes problems in IE)
  				if ( rtype.test( elem.nodeName ) && elem.parentNode ) {
  					jQuery.error( "type property can't be changed" );
  				} else if ( !jQuery.support.radioValue && value === "radio" && jQuery.nodeName(elem, "input") ) {
  					// Setting the type on a radio button after the value resets the value in IE6-9
  					// Reset value to it's default in case type is set after value
  					// This is for element creation
  					var val = elem.value;
  					elem.setAttribute( "type", value );
  					if ( val ) {
  						elem.value = val;
  					}
  					return value;
  				}
  			}
  		},
  		// Use the value property for back compat
  		// Use the nodeHook for button elements in IE6/7 (#1954)
  		value: {
  			get: function( elem, name ) {
  				if ( nodeHook && jQuery.nodeName( elem, "button" ) ) {
  					return nodeHook.get( elem, name );
  				}
  				return name in elem ?
  					elem.value :
  					null;
  			},
  			set: function( elem, value, name ) {
  				if ( nodeHook && jQuery.nodeName( elem, "button" ) ) {
  					return nodeHook.set( elem, value, name );
  				}
  				// Does not return so that setAttribute is also used
  				elem.value = value;
  			}
  		}
  	},
  
  	propFix: {
  		tabindex: "tabIndex",
  		readonly: "readOnly",
  		"for": "htmlFor",
  		"class": "className",
  		maxlength: "maxLength",
  		cellspacing: "cellSpacing",
  		cellpadding: "cellPadding",
  		rowspan: "rowSpan",
  		colspan: "colSpan",
  		usemap: "useMap",
  		frameborder: "frameBorder",
  		contenteditable: "contentEditable"
  	},
  
  	prop: function( elem, name, value ) {
  		var ret, hooks, notxml,
  			nType = elem.nodeType;
  
  		// don't get/set properties on text, comment and attribute nodes
  		if ( !elem || nType === 3 || nType === 8 || nType === 2 ) {
  			return;
  		}
  
  		notxml = nType !== 1 || !jQuery.isXMLDoc( elem );
  
  		if ( notxml ) {
  			// Fix name and attach hooks
  			name = jQuery.propFix[ name ] || name;
  			hooks = jQuery.propHooks[ name ];
  		}
  
  		if ( value !== undefined ) {
  			if ( hooks && "set" in hooks && (ret = hooks.set( elem, value, name )) !== undefined ) {
  				return ret;
  
  			} else {
  				return ( elem[ name ] = value );
  			}
  
  		} else {
  			if ( hooks && "get" in hooks && (ret = hooks.get( elem, name )) !== null ) {
  				return ret;
  
  			} else {
  				return elem[ name ];
  			}
  		}
  	},
  
  	propHooks: {
  		tabIndex: {
  			get: function( elem ) {
  				// elem.tabIndex doesn't always return the correct value when it hasn't been explicitly set
  				// http://fluidproject.org/blog/2008/01/09/getting-setting-and-removing-tabindex-values-with-javascript/
  				var attributeNode = elem.getAttributeNode("tabindex");
  
  				return attributeNode && attributeNode.specified ?
  					parseInt( attributeNode.value, 10 ) :
  					rfocusable.test( elem.nodeName ) || rclickable.test( elem.nodeName ) && elem.href ?
  						0 :
  						undefined;
  			}
  		}
  	}
  });
  
  // Hook for boolean attributes
  boolHook = {
  	get: function( elem, name ) {
  		// Align boolean attributes with corresponding properties
  		// Fall back to attribute presence where some booleans are not supported
  		var attrNode,
  			property = jQuery.prop( elem, name );
  		return property === true || typeof property !== "boolean" && ( attrNode = elem.getAttributeNode(name) ) && attrNode.nodeValue !== false ?
  			name.toLowerCase() :
  			undefined;
  	},
  	set: function( elem, value, name ) {
  		var propName;
  		if ( value === false ) {
  			// Remove boolean attributes when set to false
  			jQuery.removeAttr( elem, name );
  		} else {
  			// value is true since we know at this point it's type boolean and not false
  			// Set boolean attributes to the same name and set the DOM property
  			propName = jQuery.propFix[ name ] || name;
  			if ( propName in elem ) {
  				// Only set the IDL specifically if it already exists on the element
  				elem[ propName ] = true;
  			}
  
  			elem.setAttribute( name, name.toLowerCase() );
  		}
  		return name;
  	}
  };
  
  // IE6/7 do not support getting/setting some attributes with get/setAttribute
  if ( !getSetAttribute ) {
  
  	fixSpecified = {
  		name: true,
  		id: true,
  		coords: true
  	};
  
  	// Use this for any attribute in IE6/7
  	// This fixes almost every IE6/7 issue
  	nodeHook = jQuery.valHooks.button = {
  		get: function( elem, name ) {
  			var ret;
  			ret = elem.getAttributeNode( name );
  			return ret && ( fixSpecified[ name ] ? ret.value !== "" : ret.specified ) ?
  				ret.value :
  				undefined;
  		},
  		set: function( elem, value, name ) {
  			// Set the existing or create a new attribute node
  			var ret = elem.getAttributeNode( name );
  			if ( !ret ) {
  				ret = document.createAttribute( name );
  				elem.setAttributeNode( ret );
  			}
  			return ( ret.value = value + "" );
  		}
  	};
  
  	// Set width and height to auto instead of 0 on empty string( Bug #8150 )
  	// This is for removals
  	jQuery.each([ "width", "height" ], function( i, name ) {
  		jQuery.attrHooks[ name ] = jQuery.extend( jQuery.attrHooks[ name ], {
  			set: function( elem, value ) {
  				if ( value === "" ) {
  					elem.setAttribute( name, "auto" );
  					return value;
  				}
  			}
  		});
  	});
  
  	// Set contenteditable to false on removals(#10429)
  	// Setting to empty string throws an error as an invalid value
  	jQuery.attrHooks.contenteditable = {
  		get: nodeHook.get,
  		set: function( elem, value, name ) {
  			if ( value === "" ) {
  				value = "false";
  			}
  			nodeHook.set( elem, value, name );
  		}
  	};
  }
  
  
  // Some attributes require a special call on IE
  if ( !jQuery.support.hrefNormalized ) {
  	jQuery.each([ "href", "src", "width", "height" ], function( i, name ) {
  		jQuery.attrHooks[ name ] = jQuery.extend( jQuery.attrHooks[ name ], {
  			get: function( elem ) {
  				var ret = elem.getAttribute( name, 2 );
  				return ret === null ? undefined : ret;
  			}
  		});
  	});
  }
  
  if ( !jQuery.support.style ) {
  	jQuery.attrHooks.style = {
  		get: function( elem ) {
  			// Return undefined in the case of empty string
  			// Normalize to lowercase since IE uppercases css property names
  			return elem.style.cssText.toLowerCase() || undefined;
  		},
  		set: function( elem, value ) {
  			return ( elem.style.cssText = value + "" );
  		}
  	};
  }
  
  // Safari mis-reports the default selected property of an option
  // Accessing the parent's selectedIndex property fixes it
  if ( !jQuery.support.optSelected ) {
  	jQuery.propHooks.selected = jQuery.extend( jQuery.propHooks.selected, {
  		get: function( elem ) {
  			var parent = elem.parentNode;
  
  			if ( parent ) {
  				parent.selectedIndex;
  
  				// Make sure that it also works with optgroups, see #5701
  				if ( parent.parentNode ) {
  					parent.parentNode.selectedIndex;
  				}
  			}
  			return null;
  		}
  	});
  }
  
  // IE6/7 call enctype encoding
  if ( !jQuery.support.enctype ) {
  	jQuery.propFix.enctype = "encoding";
  }
  
  // Radios and checkboxes getter/setter
  if ( !jQuery.support.checkOn ) {
  	jQuery.each([ "radio", "checkbox" ], function() {
  		jQuery.valHooks[ this ] = {
  			get: function( elem ) {
  				// Handle the case where in Webkit "" is returned instead of "on" if a value isn't specified
  				return elem.getAttribute("value") === null ? "on" : elem.value;
  			}
  		};
  	});
  }
  jQuery.each([ "radio", "checkbox" ], function() {
  	jQuery.valHooks[ this ] = jQuery.extend( jQuery.valHooks[ this ], {
  		set: function( elem, value ) {
  			if ( jQuery.isArray( value ) ) {
  				return ( elem.checked = jQuery.inArray( jQuery(elem).val(), value ) >= 0 );
  			}
  		}
  	});
  });
  var rformElems = /^(?:textarea|input|select)$/i,
  	rtypenamespace = /^([^\.]*|)(?:\.(.+)|)$/,
  	rhoverHack = /(?:^|\s)hover(\.\S+|)\b/,
  	rkeyEvent = /^key/,
  	rmouseEvent = /^(?:mouse|contextmenu)|click/,
  	rfocusMorph = /^(?:focusinfocus|focusoutblur)$/,
  	hoverHack = function( events ) {
  		return jQuery.event.special.hover ? events : events.replace( rhoverHack, "mouseenter$1 mouseleave$1" );
  	};
  
  /*
   * Helper functions for managing events -- not part of the public interface.
   * Props to Dean Edwards' addEvent library for many of the ideas.
   */
  jQuery.event = {
  
  	add: function( elem, types, handler, data, selector ) {
  
  		var elemData, eventHandle, events,
  			t, tns, type, namespaces, handleObj,
  			handleObjIn, handlers, special;
  
  		// Don't attach events to noData or text/comment nodes (allow plain objects tho)
  		if ( elem.nodeType === 3 || elem.nodeType === 8 || !types || !handler || !(elemData = jQuery._data( elem )) ) {
  			return;
  		}
  
  		// Caller can pass in an object of custom data in lieu of the handler
  		if ( handler.handler ) {
  			handleObjIn = handler;
  			handler = handleObjIn.handler;
  			selector = handleObjIn.selector;
  		}
  
  		// Make sure that the handler has a unique ID, used to find/remove it later
  		if ( !handler.guid ) {
  			handler.guid = jQuery.guid++;
  		}
  
  		// Init the element's event structure and main handler, if this is the first
  		events = elemData.events;
  		if ( !events ) {
  			elemData.events = events = {};
  		}
  		eventHandle = elemData.handle;
  		if ( !eventHandle ) {
  			elemData.handle = eventHandle = function( e ) {
  				// Discard the second event of a jQuery.event.trigger() and
  				// when an event is called after a page has unloaded
  				return typeof jQuery !== "undefined" && (!e || jQuery.event.triggered !== e.type) ?
  					jQuery.event.dispatch.apply( eventHandle.elem, arguments ) :
  					undefined;
  			};
  			// Add elem as a property of the handle fn to prevent a memory leak with IE non-native events
  			eventHandle.elem = elem;
  		}
  
  		// Handle multiple events separated by a space
  		// jQuery(...).bind("mouseover mouseout", fn);
  		types = jQuery.trim( hoverHack(types) ).split( " " );
  		for ( t = 0; t < types.length; t++ ) {
  
  			tns = rtypenamespace.exec( types[t] ) || [];
  			type = tns[1];
  			namespaces = ( tns[2] || "" ).split( "." ).sort();
  
  			// If event changes its type, use the special event handlers for the changed type
  			special = jQuery.event.special[ type ] || {};
  
  			// If selector defined, determine special event api type, otherwise given type
  			type = ( selector ? special.delegateType : special.bindType ) || type;
  
  			// Update special based on newly reset type
  			special = jQuery.event.special[ type ] || {};
  
  			// handleObj is passed to all event handlers
  			handleObj = jQuery.extend({
  				type: type,
  				origType: tns[1],
  				data: data,
  				handler: handler,
  				guid: handler.guid,
  				selector: selector,
  				needsContext: selector && jQuery.expr.match.needsContext.test( selector ),
  				namespace: namespaces.join(".")
  			}, handleObjIn );
  
  			// Init the event handler queue if we're the first
  			handlers = events[ type ];
  			if ( !handlers ) {
  				handlers = events[ type ] = [];
  				handlers.delegateCount = 0;
  
  				// Only use addEventListener/attachEvent if the special events handler returns false
  				if ( !special.setup || special.setup.call( elem, data, namespaces, eventHandle ) === false ) {
  					// Bind the global event handler to the element
  					if ( elem.addEventListener ) {
  						elem.addEventListener( type, eventHandle, false );
  
  					} else if ( elem.attachEvent ) {
  						elem.attachEvent( "on" + type, eventHandle );
  					}
  				}
  			}
  
  			if ( special.add ) {
  				special.add.call( elem, handleObj );
  
  				if ( !handleObj.handler.guid ) {
  					handleObj.handler.guid = handler.guid;
  				}
  			}
  
  			// Add to the element's handler list, delegates in front
  			if ( selector ) {
  				handlers.splice( handlers.delegateCount++, 0, handleObj );
  			} else {
  				handlers.push( handleObj );
  			}
  
  			// Keep track of which events have ever been used, for event optimization
  			jQuery.event.global[ type ] = true;
  		}
  
  		// Nullify elem to prevent memory leaks in IE
  		elem = null;
  	},
  
  	global: {},
  
  	// Detach an event or set of events from an element
  	remove: function( elem, types, handler, selector, mappedTypes ) {
  
  		var t, tns, type, origType, namespaces, origCount,
  			j, events, special, eventType, handleObj,
  			elemData = jQuery.hasData( elem ) && jQuery._data( elem );
  
  		if ( !elemData || !(events = elemData.events) ) {
  			return;
  		}
  
  		// Once for each type.namespace in types; type may be omitted
  		types = jQuery.trim( hoverHack( types || "" ) ).split(" ");
  		for ( t = 0; t < types.length; t++ ) {
  			tns = rtypenamespace.exec( types[t] ) || [];
  			type = origType = tns[1];
  			namespaces = tns[2];
  
  			// Unbind all events (on this namespace, if provided) for the element
  			if ( !type ) {
  				for ( type in events ) {
  					jQuery.event.remove( elem, type + types[ t ], handler, selector, true );
  				}
  				continue;
  			}
  
  			special = jQuery.event.special[ type ] || {};
  			type = ( selector? special.delegateType : special.bindType ) || type;
  			eventType = events[ type ] || [];
  			origCount = eventType.length;
  			namespaces = namespaces ? new RegExp("(^|\\.)" + namespaces.split(".").sort().join("\\.(?:.*\\.|)") + "(\\.|$)") : null;
  
  			// Remove matching events
  			for ( j = 0; j < eventType.length; j++ ) {
  				handleObj = eventType[ j ];
  
  				if ( ( mappedTypes || origType === handleObj.origType ) &&
  					 ( !handler || handler.guid === handleObj.guid ) &&
  					 ( !namespaces || namespaces.test( handleObj.namespace ) ) &&
  					 ( !selector || selector === handleObj.selector || selector === "**" && handleObj.selector ) ) {
  					eventType.splice( j--, 1 );
  
  					if ( handleObj.selector ) {
  						eventType.delegateCount--;
  					}
  					if ( special.remove ) {
  						special.remove.call( elem, handleObj );
  					}
  				}
  			}
  
  			// Remove generic event handler if we removed something and no more handlers exist
  			// (avoids potential for endless recursion during removal of special event handlers)
  			if ( eventType.length === 0 && origCount !== eventType.length ) {
  				if ( !special.teardown || special.teardown.call( elem, namespaces, elemData.handle ) === false ) {
  					jQuery.removeEvent( elem, type, elemData.handle );
  				}
  
  				delete events[ type ];
  			}
  		}
  
  		// Remove the expando if it's no longer used
  		if ( jQuery.isEmptyObject( events ) ) {
  			delete elemData.handle;
  
  			// removeData also checks for emptiness and clears the expando if empty
  			// so use it instead of delete
  			jQuery.removeData( elem, "events", true );
  		}
  	},
  
  	// Events that are safe to short-circuit if no handlers are attached.
  	// Native DOM events should not be added, they may have inline handlers.
  	customEvent: {
  		"getData": true,
  		"setData": true,
  		"changeData": true
  	},
  
  	trigger: function( event, data, elem, onlyHandlers ) {
  		// Don't do events on text and comment nodes
  		if ( elem && (elem.nodeType === 3 || elem.nodeType === 8) ) {
  			return;
  		}
  
  		// Event object or event type
  		var cache, exclusive, i, cur, old, ontype, special, handle, eventPath, bubbleType,
  			type = event.type || event,
  			namespaces = [];
  
  		// focus/blur morphs to focusin/out; ensure we're not firing them right now
  		if ( rfocusMorph.test( type + jQuery.event.triggered ) ) {
  			return;
  		}
  
  		if ( type.indexOf( "!" ) >= 0 ) {
  			// Exclusive events trigger only for the exact event (no namespaces)
  			type = type.slice(0, -1);
  			exclusive = true;
  		}
  
  		if ( type.indexOf( "." ) >= 0 ) {
  			// Namespaced trigger; create a regexp to match event type in handle()
  			namespaces = type.split(".");
  			type = namespaces.shift();
  			namespaces.sort();
  		}
  
  		if ( (!elem || jQuery.event.customEvent[ type ]) && !jQuery.event.global[ type ] ) {
  			// No jQuery handlers for this event type, and it can't have inline handlers
  			return;
  		}
  
  		// Caller can pass in an Event, Object, or just an event type string
  		event = typeof event === "object" ?
  			// jQuery.Event object
  			event[ jQuery.expando ] ? event :
  			// Object literal
  			new jQuery.Event( type, event ) :
  			// Just the event type (string)
  			new jQuery.Event( type );
  
  		event.type = type;
  		event.isTrigger = true;
  		event.exclusive = exclusive;
  		event.namespace = namespaces.join( "." );
  		event.namespace_re = event.namespace? new RegExp("(^|\\.)" + namespaces.join("\\.(?:.*\\.|)") + "(\\.|$)") : null;
  		ontype = type.indexOf( ":" ) < 0 ? "on" + type : "";
  
  		// Handle a global trigger
  		if ( !elem ) {
  
  			// TODO: Stop taunting the data cache; remove global events and always attach to document
  			cache = jQuery.cache;
  			for ( i in cache ) {
  				if ( cache[ i ].events && cache[ i ].events[ type ] ) {
  					jQuery.event.trigger( event, data, cache[ i ].handle.elem, true );
  				}
  			}
  			return;
  		}
  
  		// Clean up the event in case it is being reused
  		event.result = undefined;
  		if ( !event.target ) {
  			event.target = elem;
  		}
  
  		// Clone any incoming data and prepend the event, creating the handler arg list
  		data = data != null ? jQuery.makeArray( data ) : [];
  		data.unshift( event );
  
  		// Allow special events to draw outside the lines
  		special = jQuery.event.special[ type ] || {};
  		if ( special.trigger && special.trigger.apply( elem, data ) === false ) {
  			return;
  		}
  
  		// Determine event propagation path in advance, per W3C events spec (#9951)
  		// Bubble up to document, then to window; watch for a global ownerDocument var (#9724)
  		eventPath = [[ elem, special.bindType || type ]];
  		if ( !onlyHandlers && !special.noBubble && !jQuery.isWindow( elem ) ) {
  
  			bubbleType = special.delegateType || type;
  			cur = rfocusMorph.test( bubbleType + type ) ? elem : elem.parentNode;
  			for ( old = elem; cur; cur = cur.parentNode ) {
  				eventPath.push([ cur, bubbleType ]);
  				old = cur;
  			}
  
  			// Only add window if we got to document (e.g., not plain obj or detached DOM)
  			if ( old === (elem.ownerDocument || document) ) {
  				eventPath.push([ old.defaultView || old.parentWindow || window, bubbleType ]);
  			}
  		}
  
  		// Fire handlers on the event path
  		for ( i = 0; i < eventPath.length && !event.isPropagationStopped(); i++ ) {
  
  			cur = eventPath[i][0];
  			event.type = eventPath[i][1];
  
  			handle = ( jQuery._data( cur, "events" ) || {} )[ event.type ] && jQuery._data( cur, "handle" );
  			if ( handle ) {
  				handle.apply( cur, data );
  			}
  			// Note that this is a bare JS function and not a jQuery handler
  			handle = ontype && cur[ ontype ];
  			if ( handle && jQuery.acceptData( cur ) && handle.apply && handle.apply( cur, data ) === false ) {
  				event.preventDefault();
  			}
  		}
  		event.type = type;
  
  		// If nobody prevented the default action, do it now
  		if ( !onlyHandlers && !event.isDefaultPrevented() ) {
  
  			if ( (!special._default || special._default.apply( elem.ownerDocument, data ) === false) &&
  				!(type === "click" && jQuery.nodeName( elem, "a" )) && jQuery.acceptData( elem ) ) {
  
  				// Call a native DOM method on the target with the same name name as the event.
  				// Can't use an .isFunction() check here because IE6/7 fails that test.
  				// Don't do default actions on window, that's where global variables be (#6170)
  				// IE<9 dies on focus/blur to hidden element (#1486)
  				if ( ontype && elem[ type ] && ((type !== "focus" && type !== "blur") || event.target.offsetWidth !== 0) && !jQuery.isWindow( elem ) ) {
  
  					// Don't re-trigger an onFOO event when we call its FOO() method
  					old = elem[ ontype ];
  
  					if ( old ) {
  						elem[ ontype ] = null;
  					}
  
  					// Prevent re-triggering of the same event, since we already bubbled it above
  					jQuery.event.triggered = type;
  					elem[ type ]();
  					jQuery.event.triggered = undefined;
  
  					if ( old ) {
  						elem[ ontype ] = old;
  					}
  				}
  			}
  		}
  
  		return event.result;
  	},
  
  	dispatch: function( event ) {
  
  		// Make a writable jQuery.Event from the native event object
  		event = jQuery.event.fix( event || window.event );
  
  		var i, j, cur, ret, selMatch, matched, matches, handleObj, sel, related,
  			handlers = ( (jQuery._data( this, "events" ) || {} )[ event.type ] || []),
  			delegateCount = handlers.delegateCount,
  			args = core_slice.call( arguments ),
  			run_all = !event.exclusive && !event.namespace,
  			special = jQuery.event.special[ event.type ] || {},
  			handlerQueue = [];
  
  		// Use the fix-ed jQuery.Event rather than the (read-only) native event
  		args[0] = event;
  		event.delegateTarget = this;
  
  		// Call the preDispatch hook for the mapped type, and let it bail if desired
  		if ( special.preDispatch && special.preDispatch.call( this, event ) === false ) {
  			return;
  		}
  
  		// Determine handlers that should run if there are delegated events
  		// Avoid non-left-click bubbling in Firefox (#3861)
  		if ( delegateCount && !(event.button && event.type === "click") ) {
  
  			for ( cur = event.target; cur != this; cur = cur.parentNode || this ) {
  
  				// Don't process clicks (ONLY) on disabled elements (#6911, #8165, #11382, #11764)
  				if ( cur.disabled !== true || event.type !== "click" ) {
  					selMatch = {};
  					matches = [];
  					for ( i = 0; i < delegateCount; i++ ) {
  						handleObj = handlers[ i ];
  						sel = handleObj.selector;
  
  						if ( selMatch[ sel ] === undefined ) {
  							selMatch[ sel ] = handleObj.needsContext ?
  								jQuery( sel, this ).index( cur ) >= 0 :
  								jQuery.find( sel, this, null, [ cur ] ).length;
  						}
  						if ( selMatch[ sel ] ) {
  							matches.push( handleObj );
  						}
  					}
  					if ( matches.length ) {
  						handlerQueue.push({ elem: cur, matches: matches });
  					}
  				}
  			}
  		}
  
  		// Add the remaining (directly-bound) handlers
  		if ( handlers.length > delegateCount ) {
  			handlerQueue.push({ elem: this, matches: handlers.slice( delegateCount ) });
  		}
  
  		// Run delegates first; they may want to stop propagation beneath us
  		for ( i = 0; i < handlerQueue.length && !event.isPropagationStopped(); i++ ) {
  			matched = handlerQueue[ i ];
  			event.currentTarget = matched.elem;
  
  			for ( j = 0; j < matched.matches.length && !event.isImmediatePropagationStopped(); j++ ) {
  				handleObj = matched.matches[ j ];
  
  				// Triggered event must either 1) be non-exclusive and have no namespace, or
  				// 2) have namespace(s) a subset or equal to those in the bound event (both can have no namespace).
  				if ( run_all || (!event.namespace && !handleObj.namespace) || event.namespace_re && event.namespace_re.test( handleObj.namespace ) ) {
  
  					event.data = handleObj.data;
  					event.handleObj = handleObj;
  
  					ret = ( (jQuery.event.special[ handleObj.origType ] || {}).handle || handleObj.handler )
  							.apply( matched.elem, args );
  
  					if ( ret !== undefined ) {
  						event.result = ret;
  						if ( ret === false ) {
  							event.preventDefault();
  							event.stopPropagation();
  						}
  					}
  				}
  			}
  		}
  
  		// Call the postDispatch hook for the mapped type
  		if ( special.postDispatch ) {
  			special.postDispatch.call( this, event );
  		}
  
  		return event.result;
  	},
  
  	// Includes some event props shared by KeyEvent and MouseEvent
  	// *** attrChange attrName relatedNode srcElement  are not normalized, non-W3C, deprecated, will be removed in 1.8 ***
  	props: "attrChange attrName relatedNode srcElement altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),
  
  	fixHooks: {},
  
  	keyHooks: {
  		props: "char charCode key keyCode".split(" "),
  		filter: function( event, original ) {
  
  			// Add which for key events
  			if ( event.which == null ) {
  				event.which = original.charCode != null ? original.charCode : original.keyCode;
  			}
  
  			return event;
  		}
  	},
  
  	mouseHooks: {
  		props: "button buttons clientX clientY fromElement offsetX offsetY pageX pageY screenX screenY toElement".split(" "),
  		filter: function( event, original ) {
  			var eventDoc, doc, body,
  				button = original.button,
  				fromElement = original.fromElement;
  
  			// Calculate pageX/Y if missing and clientX/Y available
  			if ( event.pageX == null && original.clientX != null ) {
  				eventDoc = event.target.ownerDocument || document;
  				doc = eventDoc.documentElement;
  				body = eventDoc.body;
  
  				event.pageX = original.clientX + ( doc && doc.scrollLeft || body && body.scrollLeft || 0 ) - ( doc && doc.clientLeft || body && body.clientLeft || 0 );
  				event.pageY = original.clientY + ( doc && doc.scrollTop  || body && body.scrollTop  || 0 ) - ( doc && doc.clientTop  || body && body.clientTop  || 0 );
  			}
  
  			// Add relatedTarget, if necessary
  			if ( !event.relatedTarget && fromElement ) {
  				event.relatedTarget = fromElement === event.target ? original.toElement : fromElement;
  			}
  
  			// Add which for click: 1 === left; 2 === middle; 3 === right
  			// Note: button is not normalized, so don't use it
  			if ( !event.which && button !== undefined ) {
  				event.which = ( button & 1 ? 1 : ( button & 2 ? 3 : ( button & 4 ? 2 : 0 ) ) );
  			}
  
  			return event;
  		}
  	},
  
  	fix: function( event ) {
  		if ( event[ jQuery.expando ] ) {
  			return event;
  		}
  
  		// Create a writable copy of the event object and normalize some properties
  		var i, prop,
  			originalEvent = event,
  			fixHook = jQuery.event.fixHooks[ event.type ] || {},
  			copy = fixHook.props ? this.props.concat( fixHook.props ) : this.props;
  
  		event = jQuery.Event( originalEvent );
  
  		for ( i = copy.length; i; ) {
  			prop = copy[ --i ];
  			event[ prop ] = originalEvent[ prop ];
  		}
  
  		// Fix target property, if necessary (#1925, IE 6/7/8 & Safari2)
  		if ( !event.target ) {
  			event.target = originalEvent.srcElement || document;
  		}
  
  		// Target should not be a text node (#504, Safari)
  		if ( event.target.nodeType === 3 ) {
  			event.target = event.target.parentNode;
  		}
  
  		// For mouse/key events, metaKey==false if it's undefined (#3368, #11328; IE6/7/8)
  		event.metaKey = !!event.metaKey;
  
  		return fixHook.filter? fixHook.filter( event, originalEvent ) : event;
  	},
  
  	special: {
  		load: {
  			// Prevent triggered image.load events from bubbling to window.load
  			noBubble: true
  		},
  
  		focus: {
  			delegateType: "focusin"
  		},
  		blur: {
  			delegateType: "focusout"
  		},
  
  		beforeunload: {
  			setup: function( data, namespaces, eventHandle ) {
  				// We only want to do this special case on windows
  				if ( jQuery.isWindow( this ) ) {
  					this.onbeforeunload = eventHandle;
  				}
  			},
  
  			teardown: function( namespaces, eventHandle ) {
  				if ( this.onbeforeunload === eventHandle ) {
  					this.onbeforeunload = null;
  				}
  			}
  		}
  	},
  
  	simulate: function( type, elem, event, bubble ) {
  		// Piggyback on a donor event to simulate a different one.
  		// Fake originalEvent to avoid donor's stopPropagation, but if the
  		// simulated event prevents default then we do the same on the donor.
  		var e = jQuery.extend(
  			new jQuery.Event(),
  			event,
  			{ type: type,
  				isSimulated: true,
  				originalEvent: {}
  			}
  		);
  		if ( bubble ) {
  			jQuery.event.trigger( e, null, elem );
  		} else {
  			jQuery.event.dispatch.call( elem, e );
  		}
  		if ( e.isDefaultPrevented() ) {
  			event.preventDefault();
  		}
  	}
  };
  
  // Some plugins are using, but it's undocumented/deprecated and will be removed.
  // The 1.7 special event interface should provide all the hooks needed now.
  jQuery.event.handle = jQuery.event.dispatch;
  
  jQuery.removeEvent = document.removeEventListener ?
  	function( elem, type, handle ) {
  		if ( elem.removeEventListener ) {
  			elem.removeEventListener( type, handle, false );
  		}
  	} :
  	function( elem, type, handle ) {
  		var name = "on" + type;
  
  		if ( elem.detachEvent ) {
  
  			// #8545, #7054, preventing memory leaks for custom events in IE6-8
  			// detachEvent needed property on element, by name of that event, to properly expose it to GC
  			if ( typeof elem[ name ] === "undefined" ) {
  				elem[ name ] = null;
  			}
  
  			elem.detachEvent( name, handle );
  		}
  	};
  
  jQuery.Event = function( src, props ) {
  	// Allow instantiation without the 'new' keyword
  	if ( !(this instanceof jQuery.Event) ) {
  		return new jQuery.Event( src, props );
  	}
  
  	// Event object
  	if ( src && src.type ) {
  		this.originalEvent = src;
  		this.type = src.type;
  
  		// Events bubbling up the document may have been marked as prevented
  		// by a handler lower down the tree; reflect the correct value.
  		this.isDefaultPrevented = ( src.defaultPrevented || src.returnValue === false ||
  			src.getPreventDefault && src.getPreventDefault() ) ? returnTrue : returnFalse;
  
  	// Event type
  	} else {
  		this.type = src;
  	}
  
  	// Put explicitly provided properties onto the event object
  	if ( props ) {
  		jQuery.extend( this, props );
  	}
  
  	// Create a timestamp if incoming event doesn't have one
  	this.timeStamp = src && src.timeStamp || jQuery.now();
  
  	// Mark it as fixed
  	this[ jQuery.expando ] = true;
  };
  
  function returnFalse() {
  	return false;
  }
  function returnTrue() {
  	return true;
  }
  
  // jQuery.Event is based on DOM3 Events as specified by the ECMAScript Language Binding
  // http://www.w3.org/TR/2003/WD-DOM-Level-3-Events-20030331/ecma-script-binding.html
  jQuery.Event.prototype = {
  	preventDefault: function() {
  		this.isDefaultPrevented = returnTrue;
  
  		var e = this.originalEvent;
  		if ( !e ) {
  			return;
  		}
  
  		// if preventDefault exists run it on the original event
  		if ( e.preventDefault ) {
  			e.preventDefault();
  
  		// otherwise set the returnValue property of the original event to false (IE)
  		} else {
  			e.returnValue = false;
  		}
  	},
  	stopPropagation: function() {
  		this.isPropagationStopped = returnTrue;
  
  		var e = this.originalEvent;
  		if ( !e ) {
  			return;
  		}
  		// if stopPropagation exists run it on the original event
  		if ( e.stopPropagation ) {
  			e.stopPropagation();
  		}
  		// otherwise set the cancelBubble property of the original event to true (IE)
  		e.cancelBubble = true;
  	},
  	stopImmediatePropagation: function() {
  		this.isImmediatePropagationStopped = returnTrue;
  		this.stopPropagation();
  	},
  	isDefaultPrevented: returnFalse,
  	isPropagationStopped: returnFalse,
  	isImmediatePropagationStopped: returnFalse
  };
  
  // Create mouseenter/leave events using mouseover/out and event-time checks
  jQuery.each({
  	mouseenter: "mouseover",
  	mouseleave: "mouseout"
  }, function( orig, fix ) {
  	jQuery.event.special[ orig ] = {
  		delegateType: fix,
  		bindType: fix,
  
  		handle: function( event ) {
  			var ret,
  				target = this,
  				related = event.relatedTarget,
  				handleObj = event.handleObj,
  				selector = handleObj.selector;
  
  			// For mousenter/leave call the handler if related is outside the target.
  			// NB: No relatedTarget if the mouse left/entered the browser window
  			if ( !related || (related !== target && !jQuery.contains( target, related )) ) {
  				event.type = handleObj.origType;
  				ret = handleObj.handler.apply( this, arguments );
  				event.type = fix;
  			}
  			return ret;
  		}
  	};
  });
  
  // IE submit delegation
  if ( !jQuery.support.submitBubbles ) {
  
  	jQuery.event.special.submit = {
  		setup: function() {
  			// Only need this for delegated form submit events
  			if ( jQuery.nodeName( this, "form" ) ) {
  				return false;
  			}
  
  			// Lazy-add a submit handler when a descendant form may potentially be submitted
  			jQuery.event.add( this, "click._submit keypress._submit", function( e ) {
  				// Node name check avoids a VML-related crash in IE (#9807)
  				var elem = e.target,
  					form = jQuery.nodeName( elem, "input" ) || jQuery.nodeName( elem, "button" ) ? elem.form : undefined;
  				if ( form && !jQuery._data( form, "_submit_attached" ) ) {
  					jQuery.event.add( form, "submit._submit", function( event ) {
  						event._submit_bubble = true;
  					});
  					jQuery._data( form, "_submit_attached", true );
  				}
  			});
  			// return undefined since we don't need an event listener
  		},
  
  		postDispatch: function( event ) {
  			// If form was submitted by the user, bubble the event up the tree
  			if ( event._submit_bubble ) {
  				delete event._submit_bubble;
  				if ( this.parentNode && !event.isTrigger ) {
  					jQuery.event.simulate( "submit", this.parentNode, event, true );
  				}
  			}
  		},
  
  		teardown: function() {
  			// Only need this for delegated form submit events
  			if ( jQuery.nodeName( this, "form" ) ) {
  				return false;
  			}
  
  			// Remove delegated handlers; cleanData eventually reaps submit handlers attached above
  			jQuery.event.remove( this, "._submit" );
  		}
  	};
  }
  
  // IE change delegation and checkbox/radio fix
  if ( !jQuery.support.changeBubbles ) {
  
  	jQuery.event.special.change = {
  
  		setup: function() {
  
  			if ( rformElems.test( this.nodeName ) ) {
  				// IE doesn't fire change on a check/radio until blur; trigger it on click
  				// after a propertychange. Eat the blur-change in special.change.handle.
  				// This still fires onchange a second time for check/radio after blur.
  				if ( this.type === "checkbox" || this.type === "radio" ) {
  					jQuery.event.add( this, "propertychange._change", function( event ) {
  						if ( event.originalEvent.propertyName === "checked" ) {
  							this._just_changed = true;
  						}
  					});
  					jQuery.event.add( this, "click._change", function( event ) {
  						if ( this._just_changed && !event.isTrigger ) {
  							this._just_changed = false;
  						}
  						// Allow triggered, simulated change events (#11500)
  						jQuery.event.simulate( "change", this, event, true );
  					});
  				}
  				return false;
  			}
  			// Delegated event; lazy-add a change handler on descendant inputs
  			jQuery.event.add( this, "beforeactivate._change", function( e ) {
  				var elem = e.target;
  
  				if ( rformElems.test( elem.nodeName ) && !jQuery._data( elem, "_change_attached" ) ) {
  					jQuery.event.add( elem, "change._change", function( event ) {
  						if ( this.parentNode && !event.isSimulated && !event.isTrigger ) {
  							jQuery.event.simulate( "change", this.parentNode, event, true );
  						}
  					});
  					jQuery._data( elem, "_change_attached", true );
  				}
  			});
  		},
  
  		handle: function( event ) {
  			var elem = event.target;
  
  			// Swallow native change events from checkbox/radio, we already triggered them above
  			if ( this !== elem || event.isSimulated || event.isTrigger || (elem.type !== "radio" && elem.type !== "checkbox") ) {
  				return event.handleObj.handler.apply( this, arguments );
  			}
  		},
  
  		teardown: function() {
  			jQuery.event.remove( this, "._change" );
  
  			return !rformElems.test( this.nodeName );
  		}
  	};
  }
  
  // Create "bubbling" focus and blur events
  if ( !jQuery.support.focusinBubbles ) {
  	jQuery.each({ focus: "focusin", blur: "focusout" }, function( orig, fix ) {
  
  		// Attach a single capturing handler while someone wants focusin/focusout
  		var attaches = 0,
  			handler = function( event ) {
  				jQuery.event.simulate( fix, event.target, jQuery.event.fix( event ), true );
  			};
  
  		jQuery.event.special[ fix ] = {
  			setup: function() {
  				if ( attaches++ === 0 ) {
  					document.addEventListener( orig, handler, true );
  				}
  			},
  			teardown: function() {
  				if ( --attaches === 0 ) {
  					document.removeEventListener( orig, handler, true );
  				}
  			}
  		};
  	});
  }
  
  jQuery.fn.extend({
  
  	on: function( types, selector, data, fn, /*INTERNAL*/ one ) {
  		var origFn, type;
  
  		// Types can be a map of types/handlers
  		if ( typeof types === "object" ) {
  			// ( types-Object, selector, data )
  			if ( typeof selector !== "string" ) { // && selector != null
  				// ( types-Object, data )
  				data = data || selector;
  				selector = undefined;
  			}
  			for ( type in types ) {
  				this.on( type, selector, data, types[ type ], one );
  			}
  			return this;
  		}
  
  		if ( data == null && fn == null ) {
  			// ( types, fn )
  			fn = selector;
  			data = selector = undefined;
  		} else if ( fn == null ) {
  			if ( typeof selector === "string" ) {
  				// ( types, selector, fn )
  				fn = data;
  				data = undefined;
  			} else {
  				// ( types, data, fn )
  				fn = data;
  				data = selector;
  				selector = undefined;
  			}
  		}
  		if ( fn === false ) {
  			fn = returnFalse;
  		} else if ( !fn ) {
  			return this;
  		}
  
  		if ( one === 1 ) {
  			origFn = fn;
  			fn = function( event ) {
  				// Can use an empty set, since event contains the info
  				jQuery().off( event );
  				return origFn.apply( this, arguments );
  			};
  			// Use same guid so caller can remove using origFn
  			fn.guid = origFn.guid || ( origFn.guid = jQuery.guid++ );
  		}
  		return this.each( function() {
  			jQuery.event.add( this, types, fn, data, selector );
  		});
  	},
  	one: function( types, selector, data, fn ) {
  		return this.on( types, selector, data, fn, 1 );
  	},
  	off: function( types, selector, fn ) {
  		var handleObj, type;
  		if ( types && types.preventDefault && types.handleObj ) {
  			// ( event )  dispatched jQuery.Event
  			handleObj = types.handleObj;
  			jQuery( types.delegateTarget ).off(
  				handleObj.namespace ? handleObj.origType + "." + handleObj.namespace : handleObj.origType,
  				handleObj.selector,
  				handleObj.handler
  			);
  			return this;
  		}
  		if ( typeof types === "object" ) {
  			// ( types-object [, selector] )
  			for ( type in types ) {
  				this.off( type, selector, types[ type ] );
  			}
  			return this;
  		}
  		if ( selector === false || typeof selector === "function" ) {
  			// ( types [, fn] )
  			fn = selector;
  			selector = undefined;
  		}
  		if ( fn === false ) {
  			fn = returnFalse;
  		}
  		return this.each(function() {
  			jQuery.event.remove( this, types, fn, selector );
  		});
  	},
  
  	bind: function( types, data, fn ) {
  		return this.on( types, null, data, fn );
  	},
  	unbind: function( types, fn ) {
  		return this.off( types, null, fn );
  	},
  
  	live: function( types, data, fn ) {
  		jQuery( this.context ).on( types, this.selector, data, fn );
  		return this;
  	},
  	die: function( types, fn ) {
  		jQuery( this.context ).off( types, this.selector || "**", fn );
  		return this;
  	},
  
  	delegate: function( selector, types, data, fn ) {
  		return this.on( types, selector, data, fn );
  	},
  	undelegate: function( selector, types, fn ) {
  		// ( namespace ) or ( selector, types [, fn] )
  		return arguments.length === 1 ? this.off( selector, "**" ) : this.off( types, selector || "**", fn );
  	},
  
  	trigger: function( type, data ) {
  		return this.each(function() {
  			jQuery.event.trigger( type, data, this );
  		});
  	},
  	triggerHandler: function( type, data ) {
  		if ( this[0] ) {
  			return jQuery.event.trigger( type, data, this[0], true );
  		}
  	},
  
  	toggle: function( fn ) {
  		// Save reference to arguments for access in closure
  		var args = arguments,
  			guid = fn.guid || jQuery.guid++,
  			i = 0,
  			toggler = function( event ) {
  				// Figure out which function to execute
  				var lastToggle = ( jQuery._data( this, "lastToggle" + fn.guid ) || 0 ) % i;
  				jQuery._data( this, "lastToggle" + fn.guid, lastToggle + 1 );
  
  				// Make sure that clicks stop
  				event.preventDefault();
  
  				// and execute the function
  				return args[ lastToggle ].apply( this, arguments ) || false;
  			};
  
  		// link all the functions, so any of them can unbind this click handler
  		toggler.guid = guid;
  		while ( i < args.length ) {
  			args[ i++ ].guid = guid;
  		}
  
  		return this.click( toggler );
  	},
  
  	hover: function( fnOver, fnOut ) {
  		return this.mouseenter( fnOver ).mouseleave( fnOut || fnOver );
  	}
  });
  
  jQuery.each( ("blur focus focusin focusout load resize scroll unload click dblclick " +
  	"mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave " +
  	"change select submit keydown keypress keyup error contextmenu").split(" "), function( i, name ) {
  
  	// Handle event binding
  	jQuery.fn[ name ] = function( data, fn ) {
  		if ( fn == null ) {
  			fn = data;
  			data = null;
  		}
  
  		return arguments.length > 0 ?
  			this.on( name, null, data, fn ) :
  			this.trigger( name );
  	};
  
  	if ( rkeyEvent.test( name ) ) {
  		jQuery.event.fixHooks[ name ] = jQuery.event.keyHooks;
  	}
  
  	if ( rmouseEvent.test( name ) ) {
  		jQuery.event.fixHooks[ name ] = jQuery.event.mouseHooks;
  	}
  });
  /*!
   * Sizzle CSS Selector Engine
   * Copyright 2012 jQuery Foundation and other contributors
   * Released under the MIT license
   * http://sizzlejs.com/
   */
  (function( window, undefined ) {
  
  var cachedruns,
  	assertGetIdNotName,
  	Expr,
  	getText,
  	isXML,
  	contains,
  	compile,
  	sortOrder,
  	hasDuplicate,
  	outermostContext,
  
  	baseHasDuplicate = true,
  	strundefined = "undefined",
  
  	expando = ( "sizcache" + Math.random() ).replace( ".", "" ),
  
  	Token = String,
  	document = window.document,
  	docElem = document.documentElement,
  	dirruns = 0,
  	done = 0,
  	pop = [].pop,
  	push = [].push,
  	slice = [].slice,
  	// Use a stripped-down indexOf if a native one is unavailable
  	indexOf = [].indexOf || function( elem ) {
  		var i = 0,
  			len = this.length;
  		for ( ; i < len; i++ ) {
  			if ( this[i] === elem ) {
  				return i;
  			}
  		}
  		return -1;
  	},
  
  	// Augment a function for special use by Sizzle
  	markFunction = function( fn, value ) {
  		fn[ expando ] = value == null || value;
  		return fn;
  	},
  
  	createCache = function() {
  		var cache = {},
  			keys = [];
  
  		return markFunction(function( key, value ) {
  			// Only keep the most recent entries
  			if ( keys.push( key ) > Expr.cacheLength ) {
  				delete cache[ keys.shift() ];
  			}
  
  			// Retrieve with (key + " ") to avoid collision with native Object.prototype properties (see Issue #157)
  			return (cache[ key + " " ] = value);
  		}, cache );
  	},
  
  	classCache = createCache(),
  	tokenCache = createCache(),
  	compilerCache = createCache(),
  
  	// Regex
  
  	// Whitespace characters http://www.w3.org/TR/css3-selectors/#whitespace
  	whitespace = "[\\x20\\t\\r\\n\\f]",
  	// http://www.w3.org/TR/css3-syntax/#characters
  	characterEncoding = "(?:\\\\.|[-\\w]|[^\\x00-\\xa0])+",
  
  	// Loosely modeled on CSS identifier characters
  	// An unquoted value should be a CSS identifier (http://www.w3.org/TR/css3-selectors/#attribute-selectors)
  	// Proper syntax: http://www.w3.org/TR/CSS21/syndata.html#value-def-identifier
  	identifier = characterEncoding.replace( "w", "w#" ),
  
  	// Acceptable operators http://www.w3.org/TR/selectors/#attribute-selectors
  	operators = "([*^$|!~]?=)",
  	attributes = "\\[" + whitespace + "*(" + characterEncoding + ")" + whitespace +
  		"*(?:" + operators + whitespace + "*(?:(['\"])((?:\\\\.|[^\\\\])*?)\\3|(" + identifier + ")|)|)" + whitespace + "*\\]",
  
  	// Prefer arguments not in parens/brackets,
  	//   then attribute selectors and non-pseudos (denoted by :),
  	//   then anything else
  	// These preferences are here to reduce the number of selectors
  	//   needing tokenize in the PSEUDO preFilter
  	pseudos = ":(" + characterEncoding + ")(?:\\((?:(['\"])((?:\\\\.|[^\\\\])*?)\\2|([^()[\\]]*|(?:(?:" + attributes + ")|[^:]|\\\\.)*|.*))\\)|)",
  
  	// For matchExpr.POS and matchExpr.needsContext
  	pos = ":(even|odd|eq|gt|lt|nth|first|last)(?:\\(" + whitespace +
  		"*((?:-\\d)?\\d*)" + whitespace + "*\\)|)(?=[^-]|$)",
  
  	// Leading and non-escaped trailing whitespace, capturing some non-whitespace characters preceding the latter
  	rtrim = new RegExp( "^" + whitespace + "+|((?:^|[^\\\\])(?:\\\\.)*)" + whitespace + "+$", "g" ),
  
  	rcomma = new RegExp( "^" + whitespace + "*," + whitespace + "*" ),
  	rcombinators = new RegExp( "^" + whitespace + "*([\\x20\\t\\r\\n\\f>+~])" + whitespace + "*" ),
  	rpseudo = new RegExp( pseudos ),
  
  	// Easily-parseable/retrievable ID or TAG or CLASS selectors
  	rquickExpr = /^(?:#([\w\-]+)|(\w+)|\.([\w\-]+))$/,
  
  	rnot = /^:not/,
  	rsibling = /[\x20\t\r\n\f]*[+~]/,
  	rendsWithNot = /:not\($/,
  
  	rheader = /h\d/i,
  	rinputs = /input|select|textarea|button/i,
  
  	rbackslash = /\\(?!\\)/g,
  
  	matchExpr = {
  		"ID": new RegExp( "^#(" + characterEncoding + ")" ),
  		"CLASS": new RegExp( "^\\.(" + characterEncoding + ")" ),
  		"NAME": new RegExp( "^\\[name=['\"]?(" + characterEncoding + ")['\"]?\\]" ),
  		"TAG": new RegExp( "^(" + characterEncoding.replace( "w", "w*" ) + ")" ),
  		"ATTR": new RegExp( "^" + attributes ),
  		"PSEUDO": new RegExp( "^" + pseudos ),
  		"POS": new RegExp( pos, "i" ),
  		"CHILD": new RegExp( "^:(only|nth|first|last)-child(?:\\(" + whitespace +
  			"*(even|odd|(([+-]|)(\\d*)n|)" + whitespace + "*(?:([+-]|)" + whitespace +
  			"*(\\d+)|))" + whitespace + "*\\)|)", "i" ),
  		// For use in libraries implementing .is()
  		"needsContext": new RegExp( "^" + whitespace + "*[>+~]|" + pos, "i" )
  	},
  
  	// Support
  
  	// Used for testing something on an element
  	assert = function( fn ) {
  		var div = document.createElement("div");
  
  		try {
  			return fn( div );
  		} catch (e) {
  			return false;
  		} finally {
  			// release memory in IE
  			div = null;
  		}
  	},
  
  	// Check if getElementsByTagName("*") returns only elements
  	assertTagNameNoComments = assert(function( div ) {
  		div.appendChild( document.createComment("") );
  		return !div.getElementsByTagName("*").length;
  	}),
  
  	// Check if getAttribute returns normalized href attributes
  	assertHrefNotNormalized = assert(function( div ) {
  		div.innerHTML = "<a href='#'></a>";
  		return div.firstChild && typeof div.firstChild.getAttribute !== strundefined &&
  			div.firstChild.getAttribute("href") === "#";
  	}),
  
  	// Check if attributes should be retrieved by attribute nodes
  	assertAttributes = assert(function( div ) {
  		div.innerHTML = "<select></select>";
  		var type = typeof div.lastChild.getAttribute("multiple");
  		// IE8 returns a string for some attributes even when not present
  		return type !== "boolean" && type !== "string";
  	}),
  
  	// Check if getElementsByClassName can be trusted
  	assertUsableClassName = assert(function( div ) {
  		// Opera can't find a second classname (in 9.6)
  		div.innerHTML = "<div class='hidden e'></div><div class='hidden'></div>";
  		if ( !div.getElementsByClassName || !div.getElementsByClassName("e").length ) {
  			return false;
  		}
  
  		// Safari 3.2 caches class attributes and doesn't catch changes
  		div.lastChild.className = "e";
  		return div.getElementsByClassName("e").length === 2;
  	}),
  
  	// Check if getElementById returns elements by name
  	// Check if getElementsByName privileges form controls or returns elements by ID
  	assertUsableName = assert(function( div ) {
  		// Inject content
  		div.id = expando + 0;
  		div.innerHTML = "<a name='" + expando + "'></a><div name='" + expando + "'></div>";
  		docElem.insertBefore( div, docElem.firstChild );
  
  		// Test
  		var pass = document.getElementsByName &&
  			// buggy browsers will return fewer than the correct 2
  			document.getElementsByName( expando ).length === 2 +
  			// buggy browsers will return more than the correct 0
  			document.getElementsByName( expando + 0 ).length;
  		assertGetIdNotName = !document.getElementById( expando );
  
  		// Cleanup
  		docElem.removeChild( div );
  
  		return pass;
  	});
  
  // If slice is not available, provide a backup
  try {
  	slice.call( docElem.childNodes, 0 )[0].nodeType;
  } catch ( e ) {
  	slice = function( i ) {
  		var elem,
  			results = [];
  		for ( ; (elem = this[i]); i++ ) {
  			results.push( elem );
  		}
  		return results;
  	};
  }
  
  function Sizzle( selector, context, results, seed ) {
  	results = results || [];
  	context = context || document;
  	var match, elem, xml, m,
  		nodeType = context.nodeType;
  
  	if ( !selector || typeof selector !== "string" ) {
  		return results;
  	}
  
  	if ( nodeType !== 1 && nodeType !== 9 ) {
  		return [];
  	}
  
  	xml = isXML( context );
  
  	if ( !xml && !seed ) {
  		if ( (match = rquickExpr.exec( selector )) ) {
  			// Speed-up: Sizzle("#ID")
  			if ( (m = match[1]) ) {
  				if ( nodeType === 9 ) {
  					elem = context.getElementById( m );
  					// Check parentNode to catch when Blackberry 4.6 returns
  					// nodes that are no longer in the document #6963
  					if ( elem && elem.parentNode ) {
  						// Handle the case where IE, Opera, and Webkit return items
  						// by name instead of ID
  						if ( elem.id === m ) {
  							results.push( elem );
  							return results;
  						}
  					} else {
  						return results;
  					}
  				} else {
  					// Context is not a document
  					if ( context.ownerDocument && (elem = context.ownerDocument.getElementById( m )) &&
  						contains( context, elem ) && elem.id === m ) {
  						results.push( elem );
  						return results;
  					}
  				}
  
  			// Speed-up: Sizzle("TAG")
  			} else if ( match[2] ) {
  				push.apply( results, slice.call(context.getElementsByTagName( selector ), 0) );
  				return results;
  
  			// Speed-up: Sizzle(".CLASS")
  			} else if ( (m = match[3]) && assertUsableClassName && context.getElementsByClassName ) {
  				push.apply( results, slice.call(context.getElementsByClassName( m ), 0) );
  				return results;
  			}
  		}
  	}
  
  	// All others
  	return select( selector.replace( rtrim, "$1" ), context, results, seed, xml );
  }
  
  Sizzle.matches = function( expr, elements ) {
  	return Sizzle( expr, null, null, elements );
  };
  
  Sizzle.matchesSelector = function( elem, expr ) {
  	return Sizzle( expr, null, null, [ elem ] ).length > 0;
  };
  
  // Returns a function to use in pseudos for input types
  function createInputPseudo( type ) {
  	return function( elem ) {
  		var name = elem.nodeName.toLowerCase();
  		return name === "input" && elem.type === type;
  	};
  }
  
  // Returns a function to use in pseudos for buttons
  function createButtonPseudo( type ) {
  	return function( elem ) {
  		var name = elem.nodeName.toLowerCase();
  		return (name === "input" || name === "button") && elem.type === type;
  	};
  }
  
  // Returns a function to use in pseudos for positionals
  function createPositionalPseudo( fn ) {
  	return markFunction(function( argument ) {
  		argument = +argument;
  		return markFunction(function( seed, matches ) {
  			var j,
  				matchIndexes = fn( [], seed.length, argument ),
  				i = matchIndexes.length;
  
  			// Match elements found at the specified indexes
  			while ( i-- ) {
  				if ( seed[ (j = matchIndexes[i]) ] ) {
  					seed[j] = !(matches[j] = seed[j]);
  				}
  			}
  		});
  	});
  }
  
  /**
   * Utility function for retrieving the text value of an array of DOM nodes
   * @param {Array|Element} elem
   */
  getText = Sizzle.getText = function( elem ) {
  	var node,
  		ret = "",
  		i = 0,
  		nodeType = elem.nodeType;
  
  	if ( nodeType ) {
  		if ( nodeType === 1 || nodeType === 9 || nodeType === 11 ) {
  			// Use textContent for elements
  			// innerText usage removed for consistency of new lines (see #11153)
  			if ( typeof elem.textContent === "string" ) {
  				return elem.textContent;
  			} else {
  				// Traverse its children
  				for ( elem = elem.firstChild; elem; elem = elem.nextSibling ) {
  					ret += getText( elem );
  				}
  			}
  		} else if ( nodeType === 3 || nodeType === 4 ) {
  			return elem.nodeValue;
  		}
  		// Do not include comment or processing instruction nodes
  	} else {
  
  		// If no nodeType, this is expected to be an array
  		for ( ; (node = elem[i]); i++ ) {
  			// Do not traverse comment nodes
  			ret += getText( node );
  		}
  	}
  	return ret;
  };
  
  isXML = Sizzle.isXML = function( elem ) {
  	// documentElement is verified for cases where it doesn't yet exist
  	// (such as loading iframes in IE - #4833)
  	var documentElement = elem && (elem.ownerDocument || elem).documentElement;
  	return documentElement ? documentElement.nodeName !== "HTML" : false;
  };
  
  // Element contains another
  contains = Sizzle.contains = docElem.contains ?
  	function( a, b ) {
  		var adown = a.nodeType === 9 ? a.documentElement : a,
  			bup = b && b.parentNode;
  		return a === bup || !!( bup && bup.nodeType === 1 && adown.contains && adown.contains(bup) );
  	} :
  	docElem.compareDocumentPosition ?
  	function( a, b ) {
  		return b && !!( a.compareDocumentPosition( b ) & 16 );
  	} :
  	function( a, b ) {
  		while ( (b = b.parentNode) ) {
  			if ( b === a ) {
  				return true;
  			}
  		}
  		return false;
  	};
  
  Sizzle.attr = function( elem, name ) {
  	var val,
  		xml = isXML( elem );
  
  	if ( !xml ) {
  		name = name.toLowerCase();
  	}
  	if ( (val = Expr.attrHandle[ name ]) ) {
  		return val( elem );
  	}
  	if ( xml || assertAttributes ) {
  		return elem.getAttribute( name );
  	}
  	val = elem.getAttributeNode( name );
  	return val ?
  		typeof elem[ name ] === "boolean" ?
  			elem[ name ] ? name : null :
  			val.specified ? val.value : null :
  		null;
  };
  
  Expr = Sizzle.selectors = {
  
  	// Can be adjusted by the user
  	cacheLength: 50,
  
  	createPseudo: markFunction,
  
  	match: matchExpr,
  
  	// IE6/7 return a modified href
  	attrHandle: assertHrefNotNormalized ?
  		{} :
  		{
  			"href": function( elem ) {
  				return elem.getAttribute( "href", 2 );
  			},
  			"type": function( elem ) {
  				return elem.getAttribute("type");
  			}
  		},
  
  	find: {
  		"ID": assertGetIdNotName ?
  			function( id, context, xml ) {
  				if ( typeof context.getElementById !== strundefined && !xml ) {
  					var m = context.getElementById( id );
  					// Check parentNode to catch when Blackberry 4.6 returns
  					// nodes that are no longer in the document #6963
  					return m && m.parentNode ? [m] : [];
  				}
  			} :
  			function( id, context, xml ) {
  				if ( typeof context.getElementById !== strundefined && !xml ) {
  					var m = context.getElementById( id );
  
  					return m ?
  						m.id === id || typeof m.getAttributeNode !== strundefined && m.getAttributeNode("id").value === id ?
  							[m] :
  							undefined :
  						[];
  				}
  			},
  
  		"TAG": assertTagNameNoComments ?
  			function( tag, context ) {
  				if ( typeof context.getElementsByTagName !== strundefined ) {
  					return context.getElementsByTagName( tag );
  				}
  			} :
  			function( tag, context ) {
  				var results = context.getElementsByTagName( tag );
  
  				// Filter out possible comments
  				if ( tag === "*" ) {
  					var elem,
  						tmp = [],
  						i = 0;
  
  					for ( ; (elem = results[i]); i++ ) {
  						if ( elem.nodeType === 1 ) {
  							tmp.push( elem );
  						}
  					}
  
  					return tmp;
  				}
  				return results;
  			},
  
  		"NAME": assertUsableName && function( tag, context ) {
  			if ( typeof context.getElementsByName !== strundefined ) {
  				return context.getElementsByName( name );
  			}
  		},
  
  		"CLASS": assertUsableClassName && function( className, context, xml ) {
  			if ( typeof context.getElementsByClassName !== strundefined && !xml ) {
  				return context.getElementsByClassName( className );
  			}
  		}
  	},
  
  	relative: {
  		">": { dir: "parentNode", first: true },
  		" ": { dir: "parentNode" },
  		"+": { dir: "previousSibling", first: true },
  		"~": { dir: "previousSibling" }
  	},
  
  	preFilter: {
  		"ATTR": function( match ) {
  			match[1] = match[1].replace( rbackslash, "" );
  
  			// Move the given value to match[3] whether quoted or unquoted
  			match[3] = ( match[4] || match[5] || "" ).replace( rbackslash, "" );
  
  			if ( match[2] === "~=" ) {
  				match[3] = " " + match[3] + " ";
  			}
  
  			return match.slice( 0, 4 );
  		},
  
  		"CHILD": function( match ) {
  			/* matches from matchExpr["CHILD"]
  				1 type (only|nth|...)
  				2 argument (even|odd|\d*|\d*n([+-]\d+)?|...)
  				3 xn-component of xn+y argument ([+-]?\d*n|)
  				4 sign of xn-component
  				5 x of xn-component
  				6 sign of y-component
  				7 y of y-component
  			*/
  			match[1] = match[1].toLowerCase();
  
  			if ( match[1] === "nth" ) {
  				// nth-child requires argument
  				if ( !match[2] ) {
  					Sizzle.error( match[0] );
  				}
  
  				// numeric x and y parameters for Expr.filter.CHILD
  				// remember that false/true cast respectively to 0/1
  				match[3] = +( match[3] ? match[4] + (match[5] || 1) : 2 * ( match[2] === "even" || match[2] === "odd" ) );
  				match[4] = +( ( match[6] + match[7] ) || match[2] === "odd" );
  
  			// other types prohibit arguments
  			} else if ( match[2] ) {
  				Sizzle.error( match[0] );
  			}
  
  			return match;
  		},
  
  		"PSEUDO": function( match ) {
  			var unquoted, excess;
  			if ( matchExpr["CHILD"].test( match[0] ) ) {
  				return null;
  			}
  
  			if ( match[3] ) {
  				match[2] = match[3];
  			} else if ( (unquoted = match[4]) ) {
  				// Only check arguments that contain a pseudo
  				if ( rpseudo.test(unquoted) &&
  					// Get excess from tokenize (recursively)
  					(excess = tokenize( unquoted, true )) &&
  					// advance to the next closing parenthesis
  					(excess = unquoted.indexOf( ")", unquoted.length - excess ) - unquoted.length) ) {
  
  					// excess is a negative index
  					unquoted = unquoted.slice( 0, excess );
  					match[0] = match[0].slice( 0, excess );
  				}
  				match[2] = unquoted;
  			}
  
  			// Return only captures needed by the pseudo filter method (type and argument)
  			return match.slice( 0, 3 );
  		}
  	},
  
  	filter: {
  		"ID": assertGetIdNotName ?
  			function( id ) {
  				id = id.replace( rbackslash, "" );
  				return function( elem ) {
  					return elem.getAttribute("id") === id;
  				};
  			} :
  			function( id ) {
  				id = id.replace( rbackslash, "" );
  				return function( elem ) {
  					var node = typeof elem.getAttributeNode !== strundefined && elem.getAttributeNode("id");
  					return node && node.value === id;
  				};
  			},
  
  		"TAG": function( nodeName ) {
  			if ( nodeName === "*" ) {
  				return function() { return true; };
  			}
  			nodeName = nodeName.replace( rbackslash, "" ).toLowerCase();
  
  			return function( elem ) {
  				return elem.nodeName && elem.nodeName.toLowerCase() === nodeName;
  			};
  		},
  
  		"CLASS": function( className ) {
  			var pattern = classCache[ expando ][ className + " " ];
  
  			return pattern ||
  				(pattern = new RegExp( "(^|" + whitespace + ")" + className + "(" + whitespace + "|$)" )) &&
  				classCache( className, function( elem ) {
  					return pattern.test( elem.className || (typeof elem.getAttribute !== strundefined && elem.getAttribute("class")) || "" );
  				});
  		},
  
  		"ATTR": function( name, operator, check ) {
  			return function( elem, context ) {
  				var result = Sizzle.attr( elem, name );
  
  				if ( result == null ) {
  					return operator === "!=";
  				}
  				if ( !operator ) {
  					return true;
  				}
  
  				result += "";
  
  				return operator === "=" ? result === check :
  					operator === "!=" ? result !== check :
  					operator === "^=" ? check && result.indexOf( check ) === 0 :
  					operator === "*=" ? check && result.indexOf( check ) > -1 :
  					operator === "$=" ? check && result.substr( result.length - check.length ) === check :
  					operator === "~=" ? ( " " + result + " " ).indexOf( check ) > -1 :
  					operator === "|=" ? result === check || result.substr( 0, check.length + 1 ) === check + "-" :
  					false;
  			};
  		},
  
  		"CHILD": function( type, argument, first, last ) {
  
  			if ( type === "nth" ) {
  				return function( elem ) {
  					var node, diff,
  						parent = elem.parentNode;
  
  					if ( first === 1 && last === 0 ) {
  						return true;
  					}
  
  					if ( parent ) {
  						diff = 0;
  						for ( node = parent.firstChild; node; node = node.nextSibling ) {
  							if ( node.nodeType === 1 ) {
  								diff++;
  								if ( elem === node ) {
  									break;
  								}
  							}
  						}
  					}
  
  					// Incorporate the offset (or cast to NaN), then check against cycle size
  					diff -= last;
  					return diff === first || ( diff % first === 0 && diff / first >= 0 );
  				};
  			}
  
  			return function( elem ) {
  				var node = elem;
  
  				switch ( type ) {
  					case "only":
  					case "first":
  						while ( (node = node.previousSibling) ) {
  							if ( node.nodeType === 1 ) {
  								return false;
  							}
  						}
  
  						if ( type === "first" ) {
  							return true;
  						}
  
  						node = elem;
  
  						/* falls through */
  					case "last":
  						while ( (node = node.nextSibling) ) {
  							if ( node.nodeType === 1 ) {
  								return false;
  							}
  						}
  
  						return true;
  				}
  			};
  		},
  
  		"PSEUDO": function( pseudo, argument ) {
  			// pseudo-class names are case-insensitive
  			// http://www.w3.org/TR/selectors/#pseudo-classes
  			// Prioritize by case sensitivity in case custom pseudos are added with uppercase letters
  			// Remember that setFilters inherits from pseudos
  			var args,
  				fn = Expr.pseudos[ pseudo ] || Expr.setFilters[ pseudo.toLowerCase() ] ||
  					Sizzle.error( "unsupported pseudo: " + pseudo );
  
  			// The user may use createPseudo to indicate that
  			// arguments are needed to create the filter function
  			// just as Sizzle does
  			if ( fn[ expando ] ) {
  				return fn( argument );
  			}
  
  			// But maintain support for old signatures
  			if ( fn.length > 1 ) {
  				args = [ pseudo, pseudo, "", argument ];
  				return Expr.setFilters.hasOwnProperty( pseudo.toLowerCase() ) ?
  					markFunction(function( seed, matches ) {
  						var idx,
  							matched = fn( seed, argument ),
  							i = matched.length;
  						while ( i-- ) {
  							idx = indexOf.call( seed, matched[i] );
  							seed[ idx ] = !( matches[ idx ] = matched[i] );
  						}
  					}) :
  					function( elem ) {
  						return fn( elem, 0, args );
  					};
  			}
  
  			return fn;
  		}
  	},
  
  	pseudos: {
  		"not": markFunction(function( selector ) {
  			// Trim the selector passed to compile
  			// to avoid treating leading and trailing
  			// spaces as combinators
  			var input = [],
  				results = [],
  				matcher = compile( selector.replace( rtrim, "$1" ) );
  
  			return matcher[ expando ] ?
  				markFunction(function( seed, matches, context, xml ) {
  					var elem,
  						unmatched = matcher( seed, null, xml, [] ),
  						i = seed.length;
  
  					// Match elements unmatched by `matcher`
  					while ( i-- ) {
  						if ( (elem = unmatched[i]) ) {
  							seed[i] = !(matches[i] = elem);
  						}
  					}
  				}) :
  				function( elem, context, xml ) {
  					input[0] = elem;
  					matcher( input, null, xml, results );
  					return !results.pop();
  				};
  		}),
  
  		"has": markFunction(function( selector ) {
  			return function( elem ) {
  				return Sizzle( selector, elem ).length > 0;
  			};
  		}),
  
  		"contains": markFunction(function( text ) {
  			return function( elem ) {
  				return ( elem.textContent || elem.innerText || getText( elem ) ).indexOf( text ) > -1;
  			};
  		}),
  
  		"enabled": function( elem ) {
  			return elem.disabled === false;
  		},
  
  		"disabled": function( elem ) {
  			return elem.disabled === true;
  		},
  
  		"checked": function( elem ) {
  			// In CSS3, :checked should return both checked and selected elements
  			// http://www.w3.org/TR/2011/REC-css3-selectors-20110929/#checked
  			var nodeName = elem.nodeName.toLowerCase();
  			return (nodeName === "input" && !!elem.checked) || (nodeName === "option" && !!elem.selected);
  		},
  
  		"selected": function( elem ) {
  			// Accessing this property makes selected-by-default
  			// options in Safari work properly
  			if ( elem.parentNode ) {
  				elem.parentNode.selectedIndex;
  			}
  
  			return elem.selected === true;
  		},
  
  		"parent": function( elem ) {
  			return !Expr.pseudos["empty"]( elem );
  		},
  
  		"empty": function( elem ) {
  			// http://www.w3.org/TR/selectors/#empty-pseudo
  			// :empty is only affected by element nodes and content nodes(including text(3), cdata(4)),
  			//   not comment, processing instructions, or others
  			// Thanks to Diego Perini for the nodeName shortcut
  			//   Greater than "@" means alpha characters (specifically not starting with "#" or "?")
  			var nodeType;
  			elem = elem.firstChild;
  			while ( elem ) {
  				if ( elem.nodeName > "@" || (nodeType = elem.nodeType) === 3 || nodeType === 4 ) {
  					return false;
  				}
  				elem = elem.nextSibling;
  			}
  			return true;
  		},
  
  		"header": function( elem ) {
  			return rheader.test( elem.nodeName );
  		},
  
  		"text": function( elem ) {
  			var type, attr;
  			// IE6 and 7 will map elem.type to 'text' for new HTML5 types (search, etc)
  			// use getAttribute instead to test this case
  			return elem.nodeName.toLowerCase() === "input" &&
  				(type = elem.type) === "text" &&
  				( (attr = elem.getAttribute("type")) == null || attr.toLowerCase() === type );
  		},
  
  		// Input types
  		"radio": createInputPseudo("radio"),
  		"checkbox": createInputPseudo("checkbox"),
  		"file": createInputPseudo("file"),
  		"password": createInputPseudo("password"),
  		"image": createInputPseudo("image"),
  
  		"submit": createButtonPseudo("submit"),
  		"reset": createButtonPseudo("reset"),
  
  		"button": function( elem ) {
  			var name = elem.nodeName.toLowerCase();
  			return name === "input" && elem.type === "button" || name === "button";
  		},
  
  		"input": function( elem ) {
  			return rinputs.test( elem.nodeName );
  		},
  
  		"focus": function( elem ) {
  			var doc = elem.ownerDocument;
  			return elem === doc.activeElement && (!doc.hasFocus || doc.hasFocus()) && !!(elem.type || elem.href || ~elem.tabIndex);
  		},
  
  		"active": function( elem ) {
  			return elem === elem.ownerDocument.activeElement;
  		},
  
  		// Positional types
  		"first": createPositionalPseudo(function() {
  			return [ 0 ];
  		}),
  
  		"last": createPositionalPseudo(function( matchIndexes, length ) {
  			return [ length - 1 ];
  		}),
  
  		"eq": createPositionalPseudo(function( matchIndexes, length, argument ) {
  			return [ argument < 0 ? argument + length : argument ];
  		}),
  
  		"even": createPositionalPseudo(function( matchIndexes, length ) {
  			for ( var i = 0; i < length; i += 2 ) {
  				matchIndexes.push( i );
  			}
  			return matchIndexes;
  		}),
  
  		"odd": createPositionalPseudo(function( matchIndexes, length ) {
  			for ( var i = 1; i < length; i += 2 ) {
  				matchIndexes.push( i );
  			}
  			return matchIndexes;
  		}),
  
  		"lt": createPositionalPseudo(function( matchIndexes, length, argument ) {
  			for ( var i = argument < 0 ? argument + length : argument; --i >= 0; ) {
  				matchIndexes.push( i );
  			}
  			return matchIndexes;
  		}),
  
  		"gt": createPositionalPseudo(function( matchIndexes, length, argument ) {
  			for ( var i = argument < 0 ? argument + length : argument; ++i < length; ) {
  				matchIndexes.push( i );
  			}
  			return matchIndexes;
  		})
  	}
  };
  
  function siblingCheck( a, b, ret ) {
  	if ( a === b ) {
  		return ret;
  	}
  
  	var cur = a.nextSibling;
  
  	while ( cur ) {
  		if ( cur === b ) {
  			return -1;
  		}
  
  		cur = cur.nextSibling;
  	}
  
  	return 1;
  }
  
  sortOrder = docElem.compareDocumentPosition ?
  	function( a, b ) {
  		if ( a === b ) {
  			hasDuplicate = true;
  			return 0;
  		}
  
  		return ( !a.compareDocumentPosition || !b.compareDocumentPosition ?
  			a.compareDocumentPosition :
  			a.compareDocumentPosition(b) & 4
  		) ? -1 : 1;
  	} :
  	function( a, b ) {
  		// The nodes are identical, we can exit early
  		if ( a === b ) {
  			hasDuplicate = true;
  			return 0;
  
  		// Fallback to using sourceIndex (in IE) if it's available on both nodes
  		} else if ( a.sourceIndex && b.sourceIndex ) {
  			return a.sourceIndex - b.sourceIndex;
  		}
  
  		var al, bl,
  			ap = [],
  			bp = [],
  			aup = a.parentNode,
  			bup = b.parentNode,
  			cur = aup;
  
  		// If the nodes are siblings (or identical) we can do a quick check
  		if ( aup === bup ) {
  			return siblingCheck( a, b );
  
  		// If no parents were found then the nodes are disconnected
  		} else if ( !aup ) {
  			return -1;
  
  		} else if ( !bup ) {
  			return 1;
  		}
  
  		// Otherwise they're somewhere else in the tree so we need
  		// to build up a full list of the parentNodes for comparison
  		while ( cur ) {
  			ap.unshift( cur );
  			cur = cur.parentNode;
  		}
  
  		cur = bup;
  
  		while ( cur ) {
  			bp.unshift( cur );
  			cur = cur.parentNode;
  		}
  
  		al = ap.length;
  		bl = bp.length;
  
  		// Start walking down the tree looking for a discrepancy
  		for ( var i = 0; i < al && i < bl; i++ ) {
  			if ( ap[i] !== bp[i] ) {
  				return siblingCheck( ap[i], bp[i] );
  			}
  		}
  
  		// We ended someplace up the tree so do a sibling check
  		return i === al ?
  			siblingCheck( a, bp[i], -1 ) :
  			siblingCheck( ap[i], b, 1 );
  	};
  
  // Always assume the presence of duplicates if sort doesn't
  // pass them to our comparison function (as in Google Chrome).
  [0, 0].sort( sortOrder );
  baseHasDuplicate = !hasDuplicate;
  
  // Document sorting and removing duplicates
  Sizzle.uniqueSort = function( results ) {
  	var elem,
  		duplicates = [],
  		i = 1,
  		j = 0;
  
  	hasDuplicate = baseHasDuplicate;
  	results.sort( sortOrder );
  
  	if ( hasDuplicate ) {
  		for ( ; (elem = results[i]); i++ ) {
  			if ( elem === results[ i - 1 ] ) {
  				j = duplicates.push( i );
  			}
  		}
  		while ( j-- ) {
  			results.splice( duplicates[ j ], 1 );
  		}
  	}
  
  	return results;
  };
  
  Sizzle.error = function( msg ) {
  	throw new Error( "Syntax error, unrecognized expression: " + msg );
  };
  
  function tokenize( selector, parseOnly ) {
  	var matched, match, tokens, type,
  		soFar, groups, preFilters,
  		cached = tokenCache[ expando ][ selector + " " ];
  
  	if ( cached ) {
  		return parseOnly ? 0 : cached.slice( 0 );
  	}
  
  	soFar = selector;
  	groups = [];
  	preFilters = Expr.preFilter;
  
  	while ( soFar ) {
  
  		// Comma and first run
  		if ( !matched || (match = rcomma.exec( soFar )) ) {
  			if ( match ) {
  				// Don't consume trailing commas as valid
  				soFar = soFar.slice( match[0].length ) || soFar;
  			}
  			groups.push( tokens = [] );
  		}
  
  		matched = false;
  
  		// Combinators
  		if ( (match = rcombinators.exec( soFar )) ) {
  			tokens.push( matched = new Token( match.shift() ) );
  			soFar = soFar.slice( matched.length );
  
  			// Cast descendant combinators to space
  			matched.type = match[0].replace( rtrim, " " );
  		}
  
  		// Filters
  		for ( type in Expr.filter ) {
  			if ( (match = matchExpr[ type ].exec( soFar )) && (!preFilters[ type ] ||
  				(match = preFilters[ type ]( match ))) ) {
  
  				tokens.push( matched = new Token( match.shift() ) );
  				soFar = soFar.slice( matched.length );
  				matched.type = type;
  				matched.matches = match;
  			}
  		}
  
  		if ( !matched ) {
  			break;
  		}
  	}
  
  	// Return the length of the invalid excess
  	// if we're just parsing
  	// Otherwise, throw an error or return tokens
  	return parseOnly ?
  		soFar.length :
  		soFar ?
  			Sizzle.error( selector ) :
  			// Cache the tokens
  			tokenCache( selector, groups ).slice( 0 );
  }
  
  function addCombinator( matcher, combinator, base ) {
  	var dir = combinator.dir,
  		checkNonElements = base && combinator.dir === "parentNode",
  		doneName = done++;
  
  	return combinator.first ?
  		// Check against closest ancestor/preceding element
  		function( elem, context, xml ) {
  			while ( (elem = elem[ dir ]) ) {
  				if ( checkNonElements || elem.nodeType === 1  ) {
  					return matcher( elem, context, xml );
  				}
  			}
  		} :
  
  		// Check against all ancestor/preceding elements
  		function( elem, context, xml ) {
  			// We can't set arbitrary data on XML nodes, so they don't benefit from dir caching
  			if ( !xml ) {
  				var cache,
  					dirkey = dirruns + " " + doneName + " ",
  					cachedkey = dirkey + cachedruns;
  				while ( (elem = elem[ dir ]) ) {
  					if ( checkNonElements || elem.nodeType === 1 ) {
  						if ( (cache = elem[ expando ]) === cachedkey ) {
  							return elem.sizset;
  						} else if ( typeof cache === "string" && cache.indexOf(dirkey) === 0 ) {
  							if ( elem.sizset ) {
  								return elem;
  							}
  						} else {
  							elem[ expando ] = cachedkey;
  							if ( matcher( elem, context, xml ) ) {
  								elem.sizset = true;
  								return elem;
  							}
  							elem.sizset = false;
  						}
  					}
  				}
  			} else {
  				while ( (elem = elem[ dir ]) ) {
  					if ( checkNonElements || elem.nodeType === 1 ) {
  						if ( matcher( elem, context, xml ) ) {
  							return elem;
  						}
  					}
  				}
  			}
  		};
  }
  
  function elementMatcher( matchers ) {
  	return matchers.length > 1 ?
  		function( elem, context, xml ) {
  			var i = matchers.length;
  			while ( i-- ) {
  				if ( !matchers[i]( elem, context, xml ) ) {
  					return false;
  				}
  			}
  			return true;
  		} :
  		matchers[0];
  }
  
  function condense( unmatched, map, filter, context, xml ) {
  	var elem,
  		newUnmatched = [],
  		i = 0,
  		len = unmatched.length,
  		mapped = map != null;
  
  	for ( ; i < len; i++ ) {
  		if ( (elem = unmatched[i]) ) {
  			if ( !filter || filter( elem, context, xml ) ) {
  				newUnmatched.push( elem );
  				if ( mapped ) {
  					map.push( i );
  				}
  			}
  		}
  	}
  
  	return newUnmatched;
  }
  
  function setMatcher( preFilter, selector, matcher, postFilter, postFinder, postSelector ) {
  	if ( postFilter && !postFilter[ expando ] ) {
  		postFilter = setMatcher( postFilter );
  	}
  	if ( postFinder && !postFinder[ expando ] ) {
  		postFinder = setMatcher( postFinder, postSelector );
  	}
  	return markFunction(function( seed, results, context, xml ) {
  		var temp, i, elem,
  			preMap = [],
  			postMap = [],
  			preexisting = results.length,
  
  			// Get initial elements from seed or context
  			elems = seed || multipleContexts( selector || "*", context.nodeType ? [ context ] : context, [] ),
  
  			// Prefilter to get matcher input, preserving a map for seed-results synchronization
  			matcherIn = preFilter && ( seed || !selector ) ?
  				condense( elems, preMap, preFilter, context, xml ) :
  				elems,
  
  			matcherOut = matcher ?
  				// If we have a postFinder, or filtered seed, or non-seed postFilter or preexisting results,
  				postFinder || ( seed ? preFilter : preexisting || postFilter ) ?
  
  					// ...intermediate processing is necessary
  					[] :
  
  					// ...otherwise use results directly
  					results :
  				matcherIn;
  
  		// Find primary matches
  		if ( matcher ) {
  			matcher( matcherIn, matcherOut, context, xml );
  		}
  
  		// Apply postFilter
  		if ( postFilter ) {
  			temp = condense( matcherOut, postMap );
  			postFilter( temp, [], context, xml );
  
  			// Un-match failing elements by moving them back to matcherIn
  			i = temp.length;
  			while ( i-- ) {
  				if ( (elem = temp[i]) ) {
  					matcherOut[ postMap[i] ] = !(matcherIn[ postMap[i] ] = elem);
  				}
  			}
  		}
  
  		if ( seed ) {
  			if ( postFinder || preFilter ) {
  				if ( postFinder ) {
  					// Get the final matcherOut by condensing this intermediate into postFinder contexts
  					temp = [];
  					i = matcherOut.length;
  					while ( i-- ) {
  						if ( (elem = matcherOut[i]) ) {
  							// Restore matcherIn since elem is not yet a final match
  							temp.push( (matcherIn[i] = elem) );
  						}
  					}
  					postFinder( null, (matcherOut = []), temp, xml );
  				}
  
  				// Move matched elements from seed to results to keep them synchronized
  				i = matcherOut.length;
  				while ( i-- ) {
  					if ( (elem = matcherOut[i]) &&
  						(temp = postFinder ? indexOf.call( seed, elem ) : preMap[i]) > -1 ) {
  
  						seed[temp] = !(results[temp] = elem);
  					}
  				}
  			}
  
  		// Add elements to results, through postFinder if defined
  		} else {
  			matcherOut = condense(
  				matcherOut === results ?
  					matcherOut.splice( preexisting, matcherOut.length ) :
  					matcherOut
  			);
  			if ( postFinder ) {
  				postFinder( null, results, matcherOut, xml );
  			} else {
  				push.apply( results, matcherOut );
  			}
  		}
  	});
  }
  
  function matcherFromTokens( tokens ) {
  	var checkContext, matcher, j,
  		len = tokens.length,
  		leadingRelative = Expr.relative[ tokens[0].type ],
  		implicitRelative = leadingRelative || Expr.relative[" "],
  		i = leadingRelative ? 1 : 0,
  
  		// The foundational matcher ensures that elements are reachable from top-level context(s)
  		matchContext = addCombinator( function( elem ) {
  			return elem === checkContext;
  		}, implicitRelative, true ),
  		matchAnyContext = addCombinator( function( elem ) {
  			return indexOf.call( checkContext, elem ) > -1;
  		}, implicitRelative, true ),
  		matchers = [ function( elem, context, xml ) {
  			return ( !leadingRelative && ( xml || context !== outermostContext ) ) || (
  				(checkContext = context).nodeType ?
  					matchContext( elem, context, xml ) :
  					matchAnyContext( elem, context, xml ) );
  		} ];
  
  	for ( ; i < len; i++ ) {
  		if ( (matcher = Expr.relative[ tokens[i].type ]) ) {
  			matchers = [ addCombinator( elementMatcher( matchers ), matcher ) ];
  		} else {
  			matcher = Expr.filter[ tokens[i].type ].apply( null, tokens[i].matches );
  
  			// Return special upon seeing a positional matcher
  			if ( matcher[ expando ] ) {
  				// Find the next relative operator (if any) for proper handling
  				j = ++i;
  				for ( ; j < len; j++ ) {
  					if ( Expr.relative[ tokens[j].type ] ) {
  						break;
  					}
  				}
  				return setMatcher(
  					i > 1 && elementMatcher( matchers ),
  					i > 1 && tokens.slice( 0, i - 1 ).join("").replace( rtrim, "$1" ),
  					matcher,
  					i < j && matcherFromTokens( tokens.slice( i, j ) ),
  					j < len && matcherFromTokens( (tokens = tokens.slice( j )) ),
  					j < len && tokens.join("")
  				);
  			}
  			matchers.push( matcher );
  		}
  	}
  
  	return elementMatcher( matchers );
  }
  
  function matcherFromGroupMatchers( elementMatchers, setMatchers ) {
  	var bySet = setMatchers.length > 0,
  		byElement = elementMatchers.length > 0,
  		superMatcher = function( seed, context, xml, results, expandContext ) {
  			var elem, j, matcher,
  				setMatched = [],
  				matchedCount = 0,
  				i = "0",
  				unmatched = seed && [],
  				outermost = expandContext != null,
  				contextBackup = outermostContext,
  				// We must always have either seed elements or context
  				elems = seed || byElement && Expr.find["TAG"]( "*", expandContext && context.parentNode || context ),
  				// Nested matchers should use non-integer dirruns
  				dirrunsUnique = (dirruns += contextBackup == null ? 1 : Math.E);
  
  			if ( outermost ) {
  				outermostContext = context !== document && context;
  				cachedruns = superMatcher.el;
  			}
  
  			// Add elements passing elementMatchers directly to results
  			for ( ; (elem = elems[i]) != null; i++ ) {
  				if ( byElement && elem ) {
  					for ( j = 0; (matcher = elementMatchers[j]); j++ ) {
  						if ( matcher( elem, context, xml ) ) {
  							results.push( elem );
  							break;
  						}
  					}
  					if ( outermost ) {
  						dirruns = dirrunsUnique;
  						cachedruns = ++superMatcher.el;
  					}
  				}
  
  				// Track unmatched elements for set filters
  				if ( bySet ) {
  					// They will have gone through all possible matchers
  					if ( (elem = !matcher && elem) ) {
  						matchedCount--;
  					}
  
  					// Lengthen the array for every element, matched or not
  					if ( seed ) {
  						unmatched.push( elem );
  					}
  				}
  			}
  
  			// Apply set filters to unmatched elements
  			matchedCount += i;
  			if ( bySet && i !== matchedCount ) {
  				for ( j = 0; (matcher = setMatchers[j]); j++ ) {
  					matcher( unmatched, setMatched, context, xml );
  				}
  
  				if ( seed ) {
  					// Reintegrate element matches to eliminate the need for sorting
  					if ( matchedCount > 0 ) {
  						while ( i-- ) {
  							if ( !(unmatched[i] || setMatched[i]) ) {
  								setMatched[i] = pop.call( results );
  							}
  						}
  					}
  
  					// Discard index placeholder values to get only actual matches
  					setMatched = condense( setMatched );
  				}
  
  				// Add matches to results
  				push.apply( results, setMatched );
  
  				// Seedless set matches succeeding multiple successful matchers stipulate sorting
  				if ( outermost && !seed && setMatched.length > 0 &&
  					( matchedCount + setMatchers.length ) > 1 ) {
  
  					Sizzle.uniqueSort( results );
  				}
  			}
  
  			// Override manipulation of globals by nested matchers
  			if ( outermost ) {
  				dirruns = dirrunsUnique;
  				outermostContext = contextBackup;
  			}
  
  			return unmatched;
  		};
  
  	superMatcher.el = 0;
  	return bySet ?
  		markFunction( superMatcher ) :
  		superMatcher;
  }
  
  compile = Sizzle.compile = function( selector, group /* Internal Use Only */ ) {
  	var i,
  		setMatchers = [],
  		elementMatchers = [],
  		cached = compilerCache[ expando ][ selector + " " ];
  
  	if ( !cached ) {
  		// Generate a function of recursive functions that can be used to check each element
  		if ( !group ) {
  			group = tokenize( selector );
  		}
  		i = group.length;
  		while ( i-- ) {
  			cached = matcherFromTokens( group[i] );
  			if ( cached[ expando ] ) {
  				setMatchers.push( cached );
  			} else {
  				elementMatchers.push( cached );
  			}
  		}
  
  		// Cache the compiled function
  		cached = compilerCache( selector, matcherFromGroupMatchers( elementMatchers, setMatchers ) );
  	}
  	return cached;
  };
  
  function multipleContexts( selector, contexts, results ) {
  	var i = 0,
  		len = contexts.length;
  	for ( ; i < len; i++ ) {
  		Sizzle( selector, contexts[i], results );
  	}
  	return results;
  }
  
  function select( selector, context, results, seed, xml ) {
  	var i, tokens, token, type, find,
  		match = tokenize( selector ),
  		j = match.length;
  
  	if ( !seed ) {
  		// Try to minimize operations if there is only one group
  		if ( match.length === 1 ) {
  
  			// Take a shortcut and set the context if the root selector is an ID
  			tokens = match[0] = match[0].slice( 0 );
  			if ( tokens.length > 2 && (token = tokens[0]).type === "ID" &&
  					context.nodeType === 9 && !xml &&
  					Expr.relative[ tokens[1].type ] ) {
  
  				context = Expr.find["ID"]( token.matches[0].replace( rbackslash, "" ), context, xml )[0];
  				if ( !context ) {
  					return results;
  				}
  
  				selector = selector.slice( tokens.shift().length );
  			}
  
  			// Fetch a seed set for right-to-left matching
  			for ( i = matchExpr["POS"].test( selector ) ? -1 : tokens.length - 1; i >= 0; i-- ) {
  				token = tokens[i];
  
  				// Abort if we hit a combinator
  				if ( Expr.relative[ (type = token.type) ] ) {
  					break;
  				}
  				if ( (find = Expr.find[ type ]) ) {
  					// Search, expanding context for leading sibling combinators
  					if ( (seed = find(
  						token.matches[0].replace( rbackslash, "" ),
  						rsibling.test( tokens[0].type ) && context.parentNode || context,
  						xml
  					)) ) {
  
  						// If seed is empty or no tokens remain, we can return early
  						tokens.splice( i, 1 );
  						selector = seed.length && tokens.join("");
  						if ( !selector ) {
  							push.apply( results, slice.call( seed, 0 ) );
  							return results;
  						}
  
  						break;
  					}
  				}
  			}
  		}
  	}
  
  	// Compile and execute a filtering function
  	// Provide `match` to avoid retokenization if we modified the selector above
  	compile( selector, match )(
  		seed,
  		context,
  		xml,
  		results,
  		rsibling.test( selector )
  	);
  	return results;
  }
  
  if ( document.querySelectorAll ) {
  	(function() {
  		var disconnectedMatch,
  			oldSelect = select,
  			rescape = /'|\\/g,
  			rattributeQuotes = /\=[\x20\t\r\n\f]*([^'"\]]*)[\x20\t\r\n\f]*\]/g,
  
  			// qSa(:focus) reports false when true (Chrome 21), no need to also add to buggyMatches since matches checks buggyQSA
  			// A support test would require too much code (would include document ready)
  			rbuggyQSA = [ ":focus" ],
  
  			// matchesSelector(:active) reports false when true (IE9/Opera 11.5)
  			// A support test would require too much code (would include document ready)
  			// just skip matchesSelector for :active
  			rbuggyMatches = [ ":active" ],
  			matches = docElem.matchesSelector ||
  				docElem.mozMatchesSelector ||
  				docElem.webkitMatchesSelector ||
  				docElem.oMatchesSelector ||
  				docElem.msMatchesSelector;
  
  		// Build QSA regex
  		// Regex strategy adopted from Diego Perini
  		assert(function( div ) {
  			// Select is set to empty string on purpose
  			// This is to test IE's treatment of not explictly
  			// setting a boolean content attribute,
  			// since its presence should be enough
  			// http://bugs.jquery.com/ticket/12359
  			div.innerHTML = "<select><option selected=''></option></select>";
  
  			// IE8 - Some boolean attributes are not treated correctly
  			if ( !div.querySelectorAll("[selected]").length ) {
  				rbuggyQSA.push( "\\[" + whitespace + "*(?:checked|disabled|ismap|multiple|readonly|selected|value)" );
  			}
  
  			// Webkit/Opera - :checked should return selected option elements
  			// http://www.w3.org/TR/2011/REC-css3-selectors-20110929/#checked
  			// IE8 throws error here (do not put tests after this one)
  			if ( !div.querySelectorAll(":checked").length ) {
  				rbuggyQSA.push(":checked");
  			}
  		});
  
  		assert(function( div ) {
  
  			// Opera 10-12/IE9 - ^= $= *= and empty values
  			// Should not select anything
  			div.innerHTML = "<p test=''></p>";
  			if ( div.querySelectorAll("[test^='']").length ) {
  				rbuggyQSA.push( "[*^$]=" + whitespace + "*(?:\"\"|'')" );
  			}
  
  			// FF 3.5 - :enabled/:disabled and hidden elements (hidden elements are still enabled)
  			// IE8 throws error here (do not put tests after this one)
  			div.innerHTML = "<input type='hidden'/>";
  			if ( !div.querySelectorAll(":enabled").length ) {
  				rbuggyQSA.push(":enabled", ":disabled");
  			}
  		});
  
  		// rbuggyQSA always contains :focus, so no need for a length check
  		rbuggyQSA = /* rbuggyQSA.length && */ new RegExp( rbuggyQSA.join("|") );
  
  		select = function( selector, context, results, seed, xml ) {
  			// Only use querySelectorAll when not filtering,
  			// when this is not xml,
  			// and when no QSA bugs apply
  			if ( !seed && !xml && !rbuggyQSA.test( selector ) ) {
  				var groups, i,
  					old = true,
  					nid = expando,
  					newContext = context,
  					newSelector = context.nodeType === 9 && selector;
  
  				// qSA works strangely on Element-rooted queries
  				// We can work around this by specifying an extra ID on the root
  				// and working up from there (Thanks to Andrew Dupont for the technique)
  				// IE 8 doesn't work on object elements
  				if ( context.nodeType === 1 && context.nodeName.toLowerCase() !== "object" ) {
  					groups = tokenize( selector );
  
  					if ( (old = context.getAttribute("id")) ) {
  						nid = old.replace( rescape, "\\$&" );
  					} else {
  						context.setAttribute( "id", nid );
  					}
  					nid = "[id='" + nid + "'] ";
  
  					i = groups.length;
  					while ( i-- ) {
  						groups[i] = nid + groups[i].join("");
  					}
  					newContext = rsibling.test( selector ) && context.parentNode || context;
  					newSelector = groups.join(",");
  				}
  
  				if ( newSelector ) {
  					try {
  						push.apply( results, slice.call( newContext.querySelectorAll(
  							newSelector
  						), 0 ) );
  						return results;
  					} catch(qsaError) {
  					} finally {
  						if ( !old ) {
  							context.removeAttribute("id");
  						}
  					}
  				}
  			}
  
  			return oldSelect( selector, context, results, seed, xml );
  		};
  
  		if ( matches ) {
  			assert(function( div ) {
  				// Check to see if it's possible to do matchesSelector
  				// on a disconnected node (IE 9)
  				disconnectedMatch = matches.call( div, "div" );
  
  				// This should fail with an exception
  				// Gecko does not error, returns false instead
  				try {
  					matches.call( div, "[test!='']:sizzle" );
  					rbuggyMatches.push( "!=", pseudos );
  				} catch ( e ) {}
  			});
  
  			// rbuggyMatches always contains :active and :focus, so no need for a length check
  			rbuggyMatches = /* rbuggyMatches.length && */ new RegExp( rbuggyMatches.join("|") );
  
  			Sizzle.matchesSelector = function( elem, expr ) {
  				// Make sure that attribute selectors are quoted
  				expr = expr.replace( rattributeQuotes, "='$1']" );
  
  				// rbuggyMatches always contains :active, so no need for an existence check
  				if ( !isXML( elem ) && !rbuggyMatches.test( expr ) && !rbuggyQSA.test( expr ) ) {
  					try {
  						var ret = matches.call( elem, expr );
  
  						// IE 9's matchesSelector returns false on disconnected nodes
  						if ( ret || disconnectedMatch ||
  								// As well, disconnected nodes are said to be in a document
  								// fragment in IE 9
  								elem.document && elem.document.nodeType !== 11 ) {
  							return ret;
  						}
  					} catch(e) {}
  				}
  
  				return Sizzle( expr, null, null, [ elem ] ).length > 0;
  			};
  		}
  	})();
  }
  
  // Deprecated
  Expr.pseudos["nth"] = Expr.pseudos["eq"];
  
  // Back-compat
  function setFilters() {}
  Expr.filters = setFilters.prototype = Expr.pseudos;
  Expr.setFilters = new setFilters();
  
  // Override sizzle attribute retrieval
  Sizzle.attr = jQuery.attr;
  jQuery.find = Sizzle;
  jQuery.expr = Sizzle.selectors;
  jQuery.expr[":"] = jQuery.expr.pseudos;
  jQuery.unique = Sizzle.uniqueSort;
  jQuery.text = Sizzle.getText;
  jQuery.isXMLDoc = Sizzle.isXML;
  jQuery.contains = Sizzle.contains;
  
  
  })( window );
  var runtil = /Until$/,
  	rparentsprev = /^(?:parents|prev(?:Until|All))/,
  	isSimple = /^.[^:#\[\.,]*$/,
  	rneedsContext = jQuery.expr.match.needsContext,
  	// methods guaranteed to produce a unique set when starting from a unique set
  	guaranteedUnique = {
  		children: true,
  		contents: true,
  		next: true,
  		prev: true
  	};
  
  jQuery.fn.extend({
  	find: function( selector ) {
  		var i, l, length, n, r, ret,
  			self = this;
  
  		if ( typeof selector !== "string" ) {
  			return jQuery( selector ).filter(function() {
  				for ( i = 0, l = self.length; i < l; i++ ) {
  					if ( jQuery.contains( self[ i ], this ) ) {
  						return true;
  					}
  				}
  			});
  		}
  
  		ret = this.pushStack( "", "find", selector );
  
  		for ( i = 0, l = this.length; i < l; i++ ) {
  			length = ret.length;
  			jQuery.find( selector, this[i], ret );
  
  			if ( i > 0 ) {
  				// Make sure that the results are unique
  				for ( n = length; n < ret.length; n++ ) {
  					for ( r = 0; r < length; r++ ) {
  						if ( ret[r] === ret[n] ) {
  							ret.splice(n--, 1);
  							break;
  						}
  					}
  				}
  			}
  		}
  
  		return ret;
  	},
  
  	has: function( target ) {
  		var i,
  			targets = jQuery( target, this ),
  			len = targets.length;
  
  		return this.filter(function() {
  			for ( i = 0; i < len; i++ ) {
  				if ( jQuery.contains( this, targets[i] ) ) {
  					return true;
  				}
  			}
  		});
  	},
  
  	not: function( selector ) {
  		return this.pushStack( winnow(this, selector, false), "not", selector);
  	},
  
  	filter: function( selector ) {
  		return this.pushStack( winnow(this, selector, true), "filter", selector );
  	},
  
  	is: function( selector ) {
  		return !!selector && (
  			typeof selector === "string" ?
  				// If this is a positional/relative selector, check membership in the returned set
  				// so $("p:first").is("p:last") won't return true for a doc with two "p".
  				rneedsContext.test( selector ) ?
  					jQuery( selector, this.context ).index( this[0] ) >= 0 :
  					jQuery.filter( selector, this ).length > 0 :
  				this.filter( selector ).length > 0 );
  	},
  
  	closest: function( selectors, context ) {
  		var cur,
  			i = 0,
  			l = this.length,
  			ret = [],
  			pos = rneedsContext.test( selectors ) || typeof selectors !== "string" ?
  				jQuery( selectors, context || this.context ) :
  				0;
  
  		for ( ; i < l; i++ ) {
  			cur = this[i];
  
  			while ( cur && cur.ownerDocument && cur !== context && cur.nodeType !== 11 ) {
  				if ( pos ? pos.index(cur) > -1 : jQuery.find.matchesSelector(cur, selectors) ) {
  					ret.push( cur );
  					break;
  				}
  				cur = cur.parentNode;
  			}
  		}
  
  		ret = ret.length > 1 ? jQuery.unique( ret ) : ret;
  
  		return this.pushStack( ret, "closest", selectors );
  	},
  
  	// Determine the position of an element within
  	// the matched set of elements
  	index: function( elem ) {
  
  		// No argument, return index in parent
  		if ( !elem ) {
  			return ( this[0] && this[0].parentNode ) ? this.prevAll().length : -1;
  		}
  
  		// index in selector
  		if ( typeof elem === "string" ) {
  			return jQuery.inArray( this[0], jQuery( elem ) );
  		}
  
  		// Locate the position of the desired element
  		return jQuery.inArray(
  			// If it receives a jQuery object, the first element is used
  			elem.jquery ? elem[0] : elem, this );
  	},
  
  	add: function( selector, context ) {
  		var set = typeof selector === "string" ?
  				jQuery( selector, context ) :
  				jQuery.makeArray( selector && selector.nodeType ? [ selector ] : selector ),
  			all = jQuery.merge( this.get(), set );
  
  		return this.pushStack( isDisconnected( set[0] ) || isDisconnected( all[0] ) ?
  			all :
  			jQuery.unique( all ) );
  	},
  
  	addBack: function( selector ) {
  		return this.add( selector == null ?
  			this.prevObject : this.prevObject.filter(selector)
  		);
  	}
  });
  
  jQuery.fn.andSelf = jQuery.fn.addBack;
  
  // A painfully simple check to see if an element is disconnected
  // from a document (should be improved, where feasible).
  function isDisconnected( node ) {
  	return !node || !node.parentNode || node.parentNode.nodeType === 11;
  }
  
  function sibling( cur, dir ) {
  	do {
  		cur = cur[ dir ];
  	} while ( cur && cur.nodeType !== 1 );
  
  	return cur;
  }
  
  jQuery.each({
  	parent: function( elem ) {
  		var parent = elem.parentNode;
  		return parent && parent.nodeType !== 11 ? parent : null;
  	},
  	parents: function( elem ) {
  		return jQuery.dir( elem, "parentNode" );
  	},
  	parentsUntil: function( elem, i, until ) {
  		return jQuery.dir( elem, "parentNode", until );
  	},
  	next: function( elem ) {
  		return sibling( elem, "nextSibling" );
  	},
  	prev: function( elem ) {
  		return sibling( elem, "previousSibling" );
  	},
  	nextAll: function( elem ) {
  		return jQuery.dir( elem, "nextSibling" );
  	},
  	prevAll: function( elem ) {
  		return jQuery.dir( elem, "previousSibling" );
  	},
  	nextUntil: function( elem, i, until ) {
  		return jQuery.dir( elem, "nextSibling", until );
  	},
  	prevUntil: function( elem, i, until ) {
  		return jQuery.dir( elem, "previousSibling", until );
  	},
  	siblings: function( elem ) {
  		return jQuery.sibling( ( elem.parentNode || {} ).firstChild, elem );
  	},
  	children: function( elem ) {
  		return jQuery.sibling( elem.firstChild );
  	},
  	contents: function( elem ) {
  		return jQuery.nodeName( elem, "iframe" ) ?
  			elem.contentDocument || elem.contentWindow.document :
  			jQuery.merge( [], elem.childNodes );
  	}
  }, function( name, fn ) {
  	jQuery.fn[ name ] = function( until, selector ) {
  		var ret = jQuery.map( this, fn, until );
  
  		if ( !runtil.test( name ) ) {
  			selector = until;
  		}
  
  		if ( selector && typeof selector === "string" ) {
  			ret = jQuery.filter( selector, ret );
  		}
  
  		ret = this.length > 1 && !guaranteedUnique[ name ] ? jQuery.unique( ret ) : ret;
  
  		if ( this.length > 1 && rparentsprev.test( name ) ) {
  			ret = ret.reverse();
  		}
  
  		return this.pushStack( ret, name, core_slice.call( arguments ).join(",") );
  	};
  });
  
  jQuery.extend({
  	filter: function( expr, elems, not ) {
  		if ( not ) {
  			expr = ":not(" + expr + ")";
  		}
  
  		return elems.length === 1 ?
  			jQuery.find.matchesSelector(elems[0], expr) ? [ elems[0] ] : [] :
  			jQuery.find.matches(expr, elems);
  	},
  
  	dir: function( elem, dir, until ) {
  		var matched = [],
  			cur = elem[ dir ];
  
  		while ( cur && cur.nodeType !== 9 && (until === undefined || cur.nodeType !== 1 || !jQuery( cur ).is( until )) ) {
  			if ( cur.nodeType === 1 ) {
  				matched.push( cur );
  			}
  			cur = cur[dir];
  		}
  		return matched;
  	},
  
  	sibling: function( n, elem ) {
  		var r = [];
  
  		for ( ; n; n = n.nextSibling ) {
  			if ( n.nodeType === 1 && n !== elem ) {
  				r.push( n );
  			}
  		}
  
  		return r;
  	}
  });
  
  // Implement the identical functionality for filter and not
  function winnow( elements, qualifier, keep ) {
  
  	// Can't pass null or undefined to indexOf in Firefox 4
  	// Set to 0 to skip string check
  	qualifier = qualifier || 0;
  
  	if ( jQuery.isFunction( qualifier ) ) {
  		return jQuery.grep(elements, function( elem, i ) {
  			var retVal = !!qualifier.call( elem, i, elem );
  			return retVal === keep;
  		});
  
  	} else if ( qualifier.nodeType ) {
  		return jQuery.grep(elements, function( elem, i ) {
  			return ( elem === qualifier ) === keep;
  		});
  
  	} else if ( typeof qualifier === "string" ) {
  		var filtered = jQuery.grep(elements, function( elem ) {
  			return elem.nodeType === 1;
  		});
  
  		if ( isSimple.test( qualifier ) ) {
  			return jQuery.filter(qualifier, filtered, !keep);
  		} else {
  			qualifier = jQuery.filter( qualifier, filtered );
  		}
  	}
  
  	return jQuery.grep(elements, function( elem, i ) {
  		return ( jQuery.inArray( elem, qualifier ) >= 0 ) === keep;
  	});
  }
  function createSafeFragment( document ) {
  	var list = nodeNames.split( "|" ),
  	safeFrag = document.createDocumentFragment();
  
  	if ( safeFrag.createElement ) {
  		while ( list.length ) {
  			safeFrag.createElement(
  				list.pop()
  			);
  		}
  	}
  	return safeFrag;
  }
  
  var nodeNames = "abbr|article|aside|audio|bdi|canvas|data|datalist|details|figcaption|figure|footer|" +
  		"header|hgroup|mark|meter|nav|output|progress|section|summary|time|video",
  	rinlinejQuery = / jQuery\d+="(?:null|\d+)"/g,
  	rleadingWhitespace = /^\s+/,
  	rxhtmlTag = /<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,
  	rtagName = /<([\w:]+)/,
  	rtbody = /<tbody/i,
  	rhtml = /<|&#?\w+;/,
  	rnoInnerhtml = /<(?:script|style|link)/i,
  	rnocache = /<(?:script|object|embed|option|style)/i,
  	rnoshimcache = new RegExp("<(?:" + nodeNames + ")[\\s/>]", "i"),
  	rcheckableType = /^(?:checkbox|radio)$/,
  	// checked="checked" or checked
  	rchecked = /checked\s*(?:[^=]|=\s*.checked.)/i,
  	rscriptType = /\/(java|ecma)script/i,
  	rcleanScript = /^\s*<!(?:\[CDATA\[|\-\-)|[\]\-]{2}>\s*$/g,
  	wrapMap = {
  		option: [ 1, "<select multiple='multiple'>", "</select>" ],
  		legend: [ 1, "<fieldset>", "</fieldset>" ],
  		thead: [ 1, "<table>", "</table>" ],
  		tr: [ 2, "<table><tbody>", "</tbody></table>" ],
  		td: [ 3, "<table><tbody><tr>", "</tr></tbody></table>" ],
  		col: [ 2, "<table><tbody></tbody><colgroup>", "</colgroup></table>" ],
  		area: [ 1, "<map>", "</map>" ],
  		_default: [ 0, "", "" ]
  	},
  	safeFragment = createSafeFragment( document ),
  	fragmentDiv = safeFragment.appendChild( document.createElement("div") );
  
  wrapMap.optgroup = wrapMap.option;
  wrapMap.tbody = wrapMap.tfoot = wrapMap.colgroup = wrapMap.caption = wrapMap.thead;
  wrapMap.th = wrapMap.td;
  
  // IE6-8 can't serialize link, script, style, or any html5 (NoScope) tags,
  // unless wrapped in a div with non-breaking characters in front of it.
  if ( !jQuery.support.htmlSerialize ) {
  	wrapMap._default = [ 1, "X<div>", "</div>" ];
  }
  
  jQuery.fn.extend({
  	text: function( value ) {
  		return jQuery.access( this, function( value ) {
  			return value === undefined ?
  				jQuery.text( this ) :
  				this.empty().append( ( this[0] && this[0].ownerDocument || document ).createTextNode( value ) );
  		}, null, value, arguments.length );
  	},
  
  	wrapAll: function( html ) {
  		if ( jQuery.isFunction( html ) ) {
  			return this.each(function(i) {
  				jQuery(this).wrapAll( html.call(this, i) );
  			});
  		}
  
  		if ( this[0] ) {
  			// The elements to wrap the target around
  			var wrap = jQuery( html, this[0].ownerDocument ).eq(0).clone(true);
  
  			if ( this[0].parentNode ) {
  				wrap.insertBefore( this[0] );
  			}
  
  			wrap.map(function() {
  				var elem = this;
  
  				while ( elem.firstChild && elem.firstChild.nodeType === 1 ) {
  					elem = elem.firstChild;
  				}
  
  				return elem;
  			}).append( this );
  		}
  
  		return this;
  	},
  
  	wrapInner: function( html ) {
  		if ( jQuery.isFunction( html ) ) {
  			return this.each(function(i) {
  				jQuery(this).wrapInner( html.call(this, i) );
  			});
  		}
  
  		return this.each(function() {
  			var self = jQuery( this ),
  				contents = self.contents();
  
  			if ( contents.length ) {
  				contents.wrapAll( html );
  
  			} else {
  				self.append( html );
  			}
  		});
  	},
  
  	wrap: function( html ) {
  		var isFunction = jQuery.isFunction( html );
  
  		return this.each(function(i) {
  			jQuery( this ).wrapAll( isFunction ? html.call(this, i) : html );
  		});
  	},
  
  	unwrap: function() {
  		return this.parent().each(function() {
  			if ( !jQuery.nodeName( this, "body" ) ) {
  				jQuery( this ).replaceWith( this.childNodes );
  			}
  		}).end();
  	},
  
  	append: function() {
  		return this.domManip(arguments, true, function( elem ) {
  			if ( this.nodeType === 1 || this.nodeType === 11 ) {
  				this.appendChild( elem );
  			}
  		});
  	},
  
  	prepend: function() {
  		return this.domManip(arguments, true, function( elem ) {
  			if ( this.nodeType === 1 || this.nodeType === 11 ) {
  				this.insertBefore( elem, this.firstChild );
  			}
  		});
  	},
  
  	before: function() {
  		if ( !isDisconnected( this[0] ) ) {
  			return this.domManip(arguments, false, function( elem ) {
  				this.parentNode.insertBefore( elem, this );
  			});
  		}
  
  		if ( arguments.length ) {
  			var set = jQuery.clean( arguments );
  			return this.pushStack( jQuery.merge( set, this ), "before", this.selector );
  		}
  	},
  
  	after: function() {
  		if ( !isDisconnected( this[0] ) ) {
  			return this.domManip(arguments, false, function( elem ) {
  				this.parentNode.insertBefore( elem, this.nextSibling );
  			});
  		}
  
  		if ( arguments.length ) {
  			var set = jQuery.clean( arguments );
  			return this.pushStack( jQuery.merge( this, set ), "after", this.selector );
  		}
  	},
  
  	// keepData is for internal use only--do not document
  	remove: function( selector, keepData ) {
  		var elem,
  			i = 0;
  
  		for ( ; (elem = this[i]) != null; i++ ) {
  			if ( !selector || jQuery.filter( selector, [ elem ] ).length ) {
  				if ( !keepData && elem.nodeType === 1 ) {
  					jQuery.cleanData( elem.getElementsByTagName("*") );
  					jQuery.cleanData( [ elem ] );
  				}
  
  				if ( elem.parentNode ) {
  					elem.parentNode.removeChild( elem );
  				}
  			}
  		}
  
  		return this;
  	},
  
  	empty: function() {
  		var elem,
  			i = 0;
  
  		for ( ; (elem = this[i]) != null; i++ ) {
  			// Remove element nodes and prevent memory leaks
  			if ( elem.nodeType === 1 ) {
  				jQuery.cleanData( elem.getElementsByTagName("*") );
  			}
  
  			// Remove any remaining nodes
  			while ( elem.firstChild ) {
  				elem.removeChild( elem.firstChild );
  			}
  		}
  
  		return this;
  	},
  
  	clone: function( dataAndEvents, deepDataAndEvents ) {
  		dataAndEvents = dataAndEvents == null ? false : dataAndEvents;
  		deepDataAndEvents = deepDataAndEvents == null ? dataAndEvents : deepDataAndEvents;
  
  		return this.map( function () {
  			return jQuery.clone( this, dataAndEvents, deepDataAndEvents );
  		});
  	},
  
  	html: function( value ) {
  		return jQuery.access( this, function( value ) {
  			var elem = this[0] || {},
  				i = 0,
  				l = this.length;
  
  			if ( value === undefined ) {
  				return elem.nodeType === 1 ?
  					elem.innerHTML.replace( rinlinejQuery, "" ) :
  					undefined;
  			}
  
  			// See if we can take a shortcut and just use innerHTML
  			if ( typeof value === "string" && !rnoInnerhtml.test( value ) &&
  				( jQuery.support.htmlSerialize || !rnoshimcache.test( value )  ) &&
  				( jQuery.support.leadingWhitespace || !rleadingWhitespace.test( value ) ) &&
  				!wrapMap[ ( rtagName.exec( value ) || ["", ""] )[1].toLowerCase() ] ) {
  
  				value = value.replace( rxhtmlTag, "<$1></$2>" );
  
  				try {
  					for (; i < l; i++ ) {
  						// Remove element nodes and prevent memory leaks
  						elem = this[i] || {};
  						if ( elem.nodeType === 1 ) {
  							jQuery.cleanData( elem.getElementsByTagName( "*" ) );
  							elem.innerHTML = value;
  						}
  					}
  
  					elem = 0;
  
  				// If using innerHTML throws an exception, use the fallback method
  				} catch(e) {}
  			}
  
  			if ( elem ) {
  				this.empty().append( value );
  			}
  		}, null, value, arguments.length );
  	},
  
  	replaceWith: function( value ) {
  		if ( !isDisconnected( this[0] ) ) {
  			// Make sure that the elements are removed from the DOM before they are inserted
  			// this can help fix replacing a parent with child elements
  			if ( jQuery.isFunction( value ) ) {
  				return this.each(function(i) {
  					var self = jQuery(this), old = self.html();
  					self.replaceWith( value.call( this, i, old ) );
  				});
  			}
  
  			if ( typeof value !== "string" ) {
  				value = jQuery( value ).detach();
  			}
  
  			return this.each(function() {
  				var next = this.nextSibling,
  					parent = this.parentNode;
  
  				jQuery( this ).remove();
  
  				if ( next ) {
  					jQuery(next).before( value );
  				} else {
  					jQuery(parent).append( value );
  				}
  			});
  		}
  
  		return this.length ?
  			this.pushStack( jQuery(jQuery.isFunction(value) ? value() : value), "replaceWith", value ) :
  			this;
  	},
  
  	detach: function( selector ) {
  		return this.remove( selector, true );
  	},
  
  	domManip: function( args, table, callback ) {
  
  		// Flatten any nested arrays
  		args = [].concat.apply( [], args );
  
  		var results, first, fragment, iNoClone,
  			i = 0,
  			value = args[0],
  			scripts = [],
  			l = this.length;
  
  		// We can't cloneNode fragments that contain checked, in WebKit
  		if ( !jQuery.support.checkClone && l > 1 && typeof value === "string" && rchecked.test( value ) ) {
  			return this.each(function() {
  				jQuery(this).domManip( args, table, callback );
  			});
  		}
  
  		if ( jQuery.isFunction(value) ) {
  			return this.each(function(i) {
  				var self = jQuery(this);
  				args[0] = value.call( this, i, table ? self.html() : undefined );
  				self.domManip( args, table, callback );
  			});
  		}
  
  		if ( this[0] ) {
  			results = jQuery.buildFragment( args, this, scripts );
  			fragment = results.fragment;
  			first = fragment.firstChild;
  
  			if ( fragment.childNodes.length === 1 ) {
  				fragment = first;
  			}
  
  			if ( first ) {
  				table = table && jQuery.nodeName( first, "tr" );
  
  				// Use the original fragment for the last item instead of the first because it can end up
  				// being emptied incorrectly in certain situations (#8070).
  				// Fragments from the fragment cache must always be cloned and never used in place.
  				for ( iNoClone = results.cacheable || l - 1; i < l; i++ ) {
  					callback.call(
  						table && jQuery.nodeName( this[i], "table" ) ?
  							findOrAppend( this[i], "tbody" ) :
  							this[i],
  						i === iNoClone ?
  							fragment :
  							jQuery.clone( fragment, true, true )
  					);
  				}
  			}
  
  			// Fix #11809: Avoid leaking memory
  			fragment = first = null;
  
  			if ( scripts.length ) {
  				jQuery.each( scripts, function( i, elem ) {
  					if ( elem.src ) {
  						if ( jQuery.ajax ) {
  							jQuery.ajax({
  								url: elem.src,
  								type: "GET",
  								dataType: "script",
  								async: false,
  								global: false,
  								"throws": true
  							});
  						} else {
  							jQuery.error("no ajax");
  						}
  					} else {
  						jQuery.globalEval( ( elem.text || elem.textContent || elem.innerHTML || "" ).replace( rcleanScript, "" ) );
  					}
  
  					if ( elem.parentNode ) {
  						elem.parentNode.removeChild( elem );
  					}
  				});
  			}
  		}
  
  		return this;
  	}
  });
  
  function findOrAppend( elem, tag ) {
  	return elem.getElementsByTagName( tag )[0] || elem.appendChild( elem.ownerDocument.createElement( tag ) );
  }
  
  function cloneCopyEvent( src, dest ) {
  
  	if ( dest.nodeType !== 1 || !jQuery.hasData( src ) ) {
  		return;
  	}
  
  	var type, i, l,
  		oldData = jQuery._data( src ),
  		curData = jQuery._data( dest, oldData ),
  		events = oldData.events;
  
  	if ( events ) {
  		delete curData.handle;
  		curData.events = {};
  
  		for ( type in events ) {
  			for ( i = 0, l = events[ type ].length; i < l; i++ ) {
  				jQuery.event.add( dest, type, events[ type ][ i ] );
  			}
  		}
  	}
  
  	// make the cloned public data object a copy from the original
  	if ( curData.data ) {
  		curData.data = jQuery.extend( {}, curData.data );
  	}
  }
  
  function cloneFixAttributes( src, dest ) {
  	var nodeName;
  
  	// We do not need to do anything for non-Elements
  	if ( dest.nodeType !== 1 ) {
  		return;
  	}
  
  	// clearAttributes removes the attributes, which we don't want,
  	// but also removes the attachEvent events, which we *do* want
  	if ( dest.clearAttributes ) {
  		dest.clearAttributes();
  	}
  
  	// mergeAttributes, in contrast, only merges back on the
  	// original attributes, not the events
  	if ( dest.mergeAttributes ) {
  		dest.mergeAttributes( src );
  	}
  
  	nodeName = dest.nodeName.toLowerCase();
  
  	if ( nodeName === "object" ) {
  		// IE6-10 improperly clones children of object elements using classid.
  		// IE10 throws NoModificationAllowedError if parent is null, #12132.
  		if ( dest.parentNode ) {
  			dest.outerHTML = src.outerHTML;
  		}
  
  		// This path appears unavoidable for IE9. When cloning an object
  		// element in IE9, the outerHTML strategy above is not sufficient.
  		// If the src has innerHTML and the destination does not,
  		// copy the src.innerHTML into the dest.innerHTML. #10324
  		if ( jQuery.support.html5Clone && (src.innerHTML && !jQuery.trim(dest.innerHTML)) ) {
  			dest.innerHTML = src.innerHTML;
  		}
  
  	} else if ( nodeName === "input" && rcheckableType.test( src.type ) ) {
  		// IE6-8 fails to persist the checked state of a cloned checkbox
  		// or radio button. Worse, IE6-7 fail to give the cloned element
  		// a checked appearance if the defaultChecked value isn't also set
  
  		dest.defaultChecked = dest.checked = src.checked;
  
  		// IE6-7 get confused and end up setting the value of a cloned
  		// checkbox/radio button to an empty string instead of "on"
  		if ( dest.value !== src.value ) {
  			dest.value = src.value;
  		}
  
  	// IE6-8 fails to return the selected option to the default selected
  	// state when cloning options
  	} else if ( nodeName === "option" ) {
  		dest.selected = src.defaultSelected;
  
  	// IE6-8 fails to set the defaultValue to the correct value when
  	// cloning other types of input fields
  	} else if ( nodeName === "input" || nodeName === "textarea" ) {
  		dest.defaultValue = src.defaultValue;
  
  	// IE blanks contents when cloning scripts
  	} else if ( nodeName === "script" && dest.text !== src.text ) {
  		dest.text = src.text;
  	}
  
  	// Event data gets referenced instead of copied if the expando
  	// gets copied too
  	dest.removeAttribute( jQuery.expando );
  }
  
  jQuery.buildFragment = function( args, context, scripts ) {
  	var fragment, cacheable, cachehit,
  		first = args[ 0 ];
  
  	// Set context from what may come in as undefined or a jQuery collection or a node
  	// Updated to fix #12266 where accessing context[0] could throw an exception in IE9/10 &
  	// also doubles as fix for #8950 where plain objects caused createDocumentFragment exception
  	context = context || document;
  	context = !context.nodeType && context[0] || context;
  	context = context.ownerDocument || context;
  
  	// Only cache "small" (1/2 KB) HTML strings that are associated with the main document
  	// Cloning options loses the selected state, so don't cache them
  	// IE 6 doesn't like it when you put <object> or <embed> elements in a fragment
  	// Also, WebKit does not clone 'checked' attributes on cloneNode, so don't cache
  	// Lastly, IE6,7,8 will not correctly reuse cached fragments that were created from unknown elems #10501
  	if ( args.length === 1 && typeof first === "string" && first.length < 512 && context === document &&
  		first.charAt(0) === "<" && !rnocache.test( first ) &&
  		(jQuery.support.checkClone || !rchecked.test( first )) &&
  		(jQuery.support.html5Clone || !rnoshimcache.test( first )) ) {
  
  		// Mark cacheable and look for a hit
  		cacheable = true;
  		fragment = jQuery.fragments[ first ];
  		cachehit = fragment !== undefined;
  	}
  
  	if ( !fragment ) {
  		fragment = context.createDocumentFragment();
  		jQuery.clean( args, context, fragment, scripts );
  
  		// Update the cache, but only store false
  		// unless this is a second parsing of the same content
  		if ( cacheable ) {
  			jQuery.fragments[ first ] = cachehit && fragment;
  		}
  	}
  
  	return { fragment: fragment, cacheable: cacheable };
  };
  
  jQuery.fragments = {};
  
  jQuery.each({
  	appendTo: "append",
  	prependTo: "prepend",
  	insertBefore: "before",
  	insertAfter: "after",
  	replaceAll: "replaceWith"
  }, function( name, original ) {
  	jQuery.fn[ name ] = function( selector ) {
  		var elems,
  			i = 0,
  			ret = [],
  			insert = jQuery( selector ),
  			l = insert.length,
  			parent = this.length === 1 && this[0].parentNode;
  
  		if ( (parent == null || parent && parent.nodeType === 11 && parent.childNodes.length === 1) && l === 1 ) {
  			insert[ original ]( this[0] );
  			return this;
  		} else {
  			for ( ; i < l; i++ ) {
  				elems = ( i > 0 ? this.clone(true) : this ).get();
  				jQuery( insert[i] )[ original ]( elems );
  				ret = ret.concat( elems );
  			}
  
  			return this.pushStack( ret, name, insert.selector );
  		}
  	};
  });
  
  function getAll( elem ) {
  	if ( typeof elem.getElementsByTagName !== "undefined" ) {
  		return elem.getElementsByTagName( "*" );
  
  	} else if ( typeof elem.querySelectorAll !== "undefined" ) {
  		return elem.querySelectorAll( "*" );
  
  	} else {
  		return [];
  	}
  }
  
  // Used in clean, fixes the defaultChecked property
  function fixDefaultChecked( elem ) {
  	if ( rcheckableType.test( elem.type ) ) {
  		elem.defaultChecked = elem.checked;
  	}
  }
  
  jQuery.extend({
  	clone: function( elem, dataAndEvents, deepDataAndEvents ) {
  		var srcElements,
  			destElements,
  			i,
  			clone;
  
  		if ( jQuery.support.html5Clone || jQuery.isXMLDoc(elem) || !rnoshimcache.test( "<" + elem.nodeName + ">" ) ) {
  			clone = elem.cloneNode( true );
  
  		// IE<=8 does not properly clone detached, unknown element nodes
  		} else {
  			fragmentDiv.innerHTML = elem.outerHTML;
  			fragmentDiv.removeChild( clone = fragmentDiv.firstChild );
  		}
  
  		if ( (!jQuery.support.noCloneEvent || !jQuery.support.noCloneChecked) &&
  				(elem.nodeType === 1 || elem.nodeType === 11) && !jQuery.isXMLDoc(elem) ) {
  			// IE copies events bound via attachEvent when using cloneNode.
  			// Calling detachEvent on the clone will also remove the events
  			// from the original. In order to get around this, we use some
  			// proprietary methods to clear the events. Thanks to MooTools
  			// guys for this hotness.
  
  			cloneFixAttributes( elem, clone );
  
  			// Using Sizzle here is crazy slow, so we use getElementsByTagName instead
  			srcElements = getAll( elem );
  			destElements = getAll( clone );
  
  			// Weird iteration because IE will replace the length property
  			// with an element if you are cloning the body and one of the
  			// elements on the page has a name or id of "length"
  			for ( i = 0; srcElements[i]; ++i ) {
  				// Ensure that the destination node is not null; Fixes #9587
  				if ( destElements[i] ) {
  					cloneFixAttributes( srcElements[i], destElements[i] );
  				}
  			}
  		}
  
  		// Copy the events from the original to the clone
  		if ( dataAndEvents ) {
  			cloneCopyEvent( elem, clone );
  
  			if ( deepDataAndEvents ) {
  				srcElements = getAll( elem );
  				destElements = getAll( clone );
  
  				for ( i = 0; srcElements[i]; ++i ) {
  					cloneCopyEvent( srcElements[i], destElements[i] );
  				}
  			}
  		}
  
  		srcElements = destElements = null;
  
  		// Return the cloned set
  		return clone;
  	},
  
  	clean: function( elems, context, fragment, scripts ) {
  		var i, j, elem, tag, wrap, depth, div, hasBody, tbody, len, handleScript, jsTags,
  			safe = context === document && safeFragment,
  			ret = [];
  
  		// Ensure that context is a document
  		if ( !context || typeof context.createDocumentFragment === "undefined" ) {
  			context = document;
  		}
  
  		// Use the already-created safe fragment if context permits
  		for ( i = 0; (elem = elems[i]) != null; i++ ) {
  			if ( typeof elem === "number" ) {
  				elem += "";
  			}
  
  			if ( !elem ) {
  				continue;
  			}
  
  			// Convert html string into DOM nodes
  			if ( typeof elem === "string" ) {
  				if ( !rhtml.test( elem ) ) {
  					elem = context.createTextNode( elem );
  				} else {
  					// Ensure a safe container in which to render the html
  					safe = safe || createSafeFragment( context );
  					div = context.createElement("div");
  					safe.appendChild( div );
  
  					// Fix "XHTML"-style tags in all browsers
  					elem = elem.replace(rxhtmlTag, "<$1></$2>");
  
  					// Go to html and back, then peel off extra wrappers
  					tag = ( rtagName.exec( elem ) || ["", ""] )[1].toLowerCase();
  					wrap = wrapMap[ tag ] || wrapMap._default;
  					depth = wrap[0];
  					div.innerHTML = wrap[1] + elem + wrap[2];
  
  					// Move to the right depth
  					while ( depth-- ) {
  						div = div.lastChild;
  					}
  
  					// Remove IE's autoinserted <tbody> from table fragments
  					if ( !jQuery.support.tbody ) {
  
  						// String was a <table>, *may* have spurious <tbody>
  						hasBody = rtbody.test(elem);
  							tbody = tag === "table" && !hasBody ?
  								div.firstChild && div.firstChild.childNodes :
  
  								// String was a bare <thead> or <tfoot>
  								wrap[1] === "<table>" && !hasBody ?
  									div.childNodes :
  									[];
  
  						for ( j = tbody.length - 1; j >= 0 ; --j ) {
  							if ( jQuery.nodeName( tbody[ j ], "tbody" ) && !tbody[ j ].childNodes.length ) {
  								tbody[ j ].parentNode.removeChild( tbody[ j ] );
  							}
  						}
  					}
  
  					// IE completely kills leading whitespace when innerHTML is used
  					if ( !jQuery.support.leadingWhitespace && rleadingWhitespace.test( elem ) ) {
  						div.insertBefore( context.createTextNode( rleadingWhitespace.exec(elem)[0] ), div.firstChild );
  					}
  
  					elem = div.childNodes;
  
  					// Take out of fragment container (we need a fresh div each time)
  					div.parentNode.removeChild( div );
  				}
  			}
  
  			if ( elem.nodeType ) {
  				ret.push( elem );
  			} else {
  				jQuery.merge( ret, elem );
  			}
  		}
  
  		// Fix #11356: Clear elements from safeFragment
  		if ( div ) {
  			elem = div = safe = null;
  		}
  
  		// Reset defaultChecked for any radios and checkboxes
  		// about to be appended to the DOM in IE 6/7 (#8060)
  		if ( !jQuery.support.appendChecked ) {
  			for ( i = 0; (elem = ret[i]) != null; i++ ) {
  				if ( jQuery.nodeName( elem, "input" ) ) {
  					fixDefaultChecked( elem );
  				} else if ( typeof elem.getElementsByTagName !== "undefined" ) {
  					jQuery.grep( elem.getElementsByTagName("input"), fixDefaultChecked );
  				}
  			}
  		}
  
  		// Append elements to a provided document fragment
  		if ( fragment ) {
  			// Special handling of each script element
  			handleScript = function( elem ) {
  				// Check if we consider it executable
  				if ( !elem.type || rscriptType.test( elem.type ) ) {
  					// Detach the script and store it in the scripts array (if provided) or the fragment
  					// Return truthy to indicate that it has been handled
  					return scripts ?
  						scripts.push( elem.parentNode ? elem.parentNode.removeChild( elem ) : elem ) :
  						fragment.appendChild( elem );
  				}
  			};
  
  			for ( i = 0; (elem = ret[i]) != null; i++ ) {
  				// Check if we're done after handling an executable script
  				if ( !( jQuery.nodeName( elem, "script" ) && handleScript( elem ) ) ) {
  					// Append to fragment and handle embedded scripts
  					fragment.appendChild( elem );
  					if ( typeof elem.getElementsByTagName !== "undefined" ) {
  						// handleScript alters the DOM, so use jQuery.merge to ensure snapshot iteration
  						jsTags = jQuery.grep( jQuery.merge( [], elem.getElementsByTagName("script") ), handleScript );
  
  						// Splice the scripts into ret after their former ancestor and advance our index beyond them
  						ret.splice.apply( ret, [i + 1, 0].concat( jsTags ) );
  						i += jsTags.length;
  					}
  				}
  			}
  		}
  
  		return ret;
  	},
  
  	cleanData: function( elems, /* internal */ acceptData ) {
  		var data, id, elem, type,
  			i = 0,
  			internalKey = jQuery.expando,
  			cache = jQuery.cache,
  			deleteExpando = jQuery.support.deleteExpando,
  			special = jQuery.event.special;
  
  		for ( ; (elem = elems[i]) != null; i++ ) {
  
  			if ( acceptData || jQuery.acceptData( elem ) ) {
  
  				id = elem[ internalKey ];
  				data = id && cache[ id ];
  
  				if ( data ) {
  					if ( data.events ) {
  						for ( type in data.events ) {
  							if ( special[ type ] ) {
  								jQuery.event.remove( elem, type );
  
  							// This is a shortcut to avoid jQuery.event.remove's overhead
  							} else {
  								jQuery.removeEvent( elem, type, data.handle );
  							}
  						}
  					}
  
  					// Remove cache only if it was not already removed by jQuery.event.remove
  					if ( cache[ id ] ) {
  
  						delete cache[ id ];
  
  						// IE does not allow us to delete expando properties from nodes,
  						// nor does it have a removeAttribute function on Document nodes;
  						// we must handle all of these cases
  						if ( deleteExpando ) {
  							delete elem[ internalKey ];
  
  						} else if ( elem.removeAttribute ) {
  							elem.removeAttribute( internalKey );
  
  						} else {
  							elem[ internalKey ] = null;
  						}
  
  						jQuery.deletedIds.push( id );
  					}
  				}
  			}
  		}
  	}
  });
  // Limit scope pollution from any deprecated API
  (function() {
  
  var matched, browser;
  
  // Use of jQuery.browser is frowned upon.
  // More details: http://api.jquery.com/jQuery.browser
  // jQuery.uaMatch maintained for back-compat
  jQuery.uaMatch = function( ua ) {
  	ua = ua.toLowerCase();
  
  	var match = /(chrome)[ \/]([\w.]+)/.exec( ua ) ||
  		/(webkit)[ \/]([\w.]+)/.exec( ua ) ||
  		/(opera)(?:.*version|)[ \/]([\w.]+)/.exec( ua ) ||
  		/(msie) ([\w.]+)/.exec( ua ) ||
  		ua.indexOf("compatible") < 0 && /(mozilla)(?:.*? rv:([\w.]+)|)/.exec( ua ) ||
  		[];
  
  	return {
  		browser: match[ 1 ] || "",
  		version: match[ 2 ] || "0"
  	};
  };
  
  matched = jQuery.uaMatch( navigator.userAgent );
  browser = {};
  
  if ( matched.browser ) {
  	browser[ matched.browser ] = true;
  	browser.version = matched.version;
  }
  
  // Chrome is Webkit, but Webkit is also Safari.
  if ( browser.chrome ) {
  	browser.webkit = true;
  } else if ( browser.webkit ) {
  	browser.safari = true;
  }
  
  jQuery.browser = browser;
  
  jQuery.sub = function() {
  	function jQuerySub( selector, context ) {
  		return new jQuerySub.fn.init( selector, context );
  	}
  	jQuery.extend( true, jQuerySub, this );
  	jQuerySub.superclass = this;
  	jQuerySub.fn = jQuerySub.prototype = this();
  	jQuerySub.fn.constructor = jQuerySub;
  	jQuerySub.sub = this.sub;
  	jQuerySub.fn.init = function init( selector, context ) {
  		if ( context && context instanceof jQuery && !(context instanceof jQuerySub) ) {
  			context = jQuerySub( context );
  		}
  
  		return jQuery.fn.init.call( this, selector, context, rootjQuerySub );
  	};
  	jQuerySub.fn.init.prototype = jQuerySub.fn;
  	var rootjQuerySub = jQuerySub(document);
  	return jQuerySub;
  };
  
  })();
  var curCSS, iframe, iframeDoc,
  	ralpha = /alpha\([^)]*\)/i,
  	ropacity = /opacity=([^)]*)/,
  	rposition = /^(top|right|bottom|left)$/,
  	// swappable if display is none or starts with table except "table", "table-cell", or "table-caption"
  	// see here for display values: https://developer.mozilla.org/en-US/docs/CSS/display
  	rdisplayswap = /^(none|table(?!-c[ea]).+)/,
  	rmargin = /^margin/,
  	rnumsplit = new RegExp( "^(" + core_pnum + ")(.*)$", "i" ),
  	rnumnonpx = new RegExp( "^(" + core_pnum + ")(?!px)[a-z%]+$", "i" ),
  	rrelNum = new RegExp( "^([-+])=(" + core_pnum + ")", "i" ),
  	elemdisplay = { BODY: "block" },
  
  	cssShow = { position: "absolute", visibility: "hidden", display: "block" },
  	cssNormalTransform = {
  		letterSpacing: 0,
  		fontWeight: 400
  	},
  
  	cssExpand = [ "Top", "Right", "Bottom", "Left" ],
  	cssPrefixes = [ "Webkit", "O", "Moz", "ms" ],
  
  	eventsToggle = jQuery.fn.toggle;
  
  // return a css property mapped to a potentially vendor prefixed property
  function vendorPropName( style, name ) {
  
  	// shortcut for names that are not vendor prefixed
  	if ( name in style ) {
  		return name;
  	}
  
  	// check for vendor prefixed names
  	var capName = name.charAt(0).toUpperCase() + name.slice(1),
  		origName = name,
  		i = cssPrefixes.length;
  
  	while ( i-- ) {
  		name = cssPrefixes[ i ] + capName;
  		if ( name in style ) {
  			return name;
  		}
  	}
  
  	return origName;
  }
  
  function isHidden( elem, el ) {
  	elem = el || elem;
  	return jQuery.css( elem, "display" ) === "none" || !jQuery.contains( elem.ownerDocument, elem );
  }
  
  function showHide( elements, show ) {
  	var elem, display,
  		values = [],
  		index = 0,
  		length = elements.length;
  
  	for ( ; index < length; index++ ) {
  		elem = elements[ index ];
  		if ( !elem.style ) {
  			continue;
  		}
  		values[ index ] = jQuery._data( elem, "olddisplay" );
  		if ( show ) {
  			// Reset the inline display of this element to learn if it is
  			// being hidden by cascaded rules or not
  			if ( !values[ index ] && elem.style.display === "none" ) {
  				elem.style.display = "";
  			}
  
  			// Set elements which have been overridden with display: none
  			// in a stylesheet to whatever the default browser style is
  			// for such an element
  			if ( elem.style.display === "" && isHidden( elem ) ) {
  				values[ index ] = jQuery._data( elem, "olddisplay", css_defaultDisplay(elem.nodeName) );
  			}
  		} else {
  			display = curCSS( elem, "display" );
  
  			if ( !values[ index ] && display !== "none" ) {
  				jQuery._data( elem, "olddisplay", display );
  			}
  		}
  	}
  
  	// Set the display of most of the elements in a second loop
  	// to avoid the constant reflow
  	for ( index = 0; index < length; index++ ) {
  		elem = elements[ index ];
  		if ( !elem.style ) {
  			continue;
  		}
  		if ( !show || elem.style.display === "none" || elem.style.display === "" ) {
  			elem.style.display = show ? values[ index ] || "" : "none";
  		}
  	}
  
  	return elements;
  }
  
  jQuery.fn.extend({
  	css: function( name, value ) {
  		return jQuery.access( this, function( elem, name, value ) {
  			return value !== undefined ?
  				jQuery.style( elem, name, value ) :
  				jQuery.css( elem, name );
  		}, name, value, arguments.length > 1 );
  	},
  	show: function() {
  		return showHide( this, true );
  	},
  	hide: function() {
  		return showHide( this );
  	},
  	toggle: function( state, fn2 ) {
  		var bool = typeof state === "boolean";
  
  		if ( jQuery.isFunction( state ) && jQuery.isFunction( fn2 ) ) {
  			return eventsToggle.apply( this, arguments );
  		}
  
  		return this.each(function() {
  			if ( bool ? state : isHidden( this ) ) {
  				jQuery( this ).show();
  			} else {
  				jQuery( this ).hide();
  			}
  		});
  	}
  });
  
  jQuery.extend({
  	// Add in style property hooks for overriding the default
  	// behavior of getting and setting a style property
  	cssHooks: {
  		opacity: {
  			get: function( elem, computed ) {
  				if ( computed ) {
  					// We should always get a number back from opacity
  					var ret = curCSS( elem, "opacity" );
  					return ret === "" ? "1" : ret;
  
  				}
  			}
  		}
  	},
  
  	// Exclude the following css properties to add px
  	cssNumber: {
  		"fillOpacity": true,
  		"fontWeight": true,
  		"lineHeight": true,
  		"opacity": true,
  		"orphans": true,
  		"widows": true,
  		"zIndex": true,
  		"zoom": true
  	},
  
  	// Add in properties whose names you wish to fix before
  	// setting or getting the value
  	cssProps: {
  		// normalize float css property
  		"float": jQuery.support.cssFloat ? "cssFloat" : "styleFloat"
  	},
  
  	// Get and set the style property on a DOM Node
  	style: function( elem, name, value, extra ) {
  		// Don't set styles on text and comment nodes
  		if ( !elem || elem.nodeType === 3 || elem.nodeType === 8 || !elem.style ) {
  			return;
  		}
  
  		// Make sure that we're working with the right name
  		var ret, type, hooks,
  			origName = jQuery.camelCase( name ),
  			style = elem.style;
  
  		name = jQuery.cssProps[ origName ] || ( jQuery.cssProps[ origName ] = vendorPropName( style, origName ) );
  
  		// gets hook for the prefixed version
  		// followed by the unprefixed version
  		hooks = jQuery.cssHooks[ name ] || jQuery.cssHooks[ origName ];
  
  		// Check if we're setting a value
  		if ( value !== undefined ) {
  			type = typeof value;
  
  			// convert relative number strings (+= or -=) to relative numbers. #7345
  			if ( type === "string" && (ret = rrelNum.exec( value )) ) {
  				value = ( ret[1] + 1 ) * ret[2] + parseFloat( jQuery.css( elem, name ) );
  				// Fixes bug #9237
  				type = "number";
  			}
  
  			// Make sure that NaN and null values aren't set. See: #7116
  			if ( value == null || type === "number" && isNaN( value ) ) {
  				return;
  			}
  
  			// If a number was passed in, add 'px' to the (except for certain CSS properties)
  			if ( type === "number" && !jQuery.cssNumber[ origName ] ) {
  				value += "px";
  			}
  
  			// If a hook was provided, use that value, otherwise just set the specified value
  			if ( !hooks || !("set" in hooks) || (value = hooks.set( elem, value, extra )) !== undefined ) {
  				// Wrapped to prevent IE from throwing errors when 'invalid' values are provided
  				// Fixes bug #5509
  				try {
  					style[ name ] = value;
  				} catch(e) {}
  			}
  
  		} else {
  			// If a hook was provided get the non-computed value from there
  			if ( hooks && "get" in hooks && (ret = hooks.get( elem, false, extra )) !== undefined ) {
  				return ret;
  			}
  
  			// Otherwise just get the value from the style object
  			return style[ name ];
  		}
  	},
  
  	css: function( elem, name, numeric, extra ) {
  		var val, num, hooks,
  			origName = jQuery.camelCase( name );
  
  		// Make sure that we're working with the right name
  		name = jQuery.cssProps[ origName ] || ( jQuery.cssProps[ origName ] = vendorPropName( elem.style, origName ) );
  
  		// gets hook for the prefixed version
  		// followed by the unprefixed version
  		hooks = jQuery.cssHooks[ name ] || jQuery.cssHooks[ origName ];
  
  		// If a hook was provided get the computed value from there
  		if ( hooks && "get" in hooks ) {
  			val = hooks.get( elem, true, extra );
  		}
  
  		// Otherwise, if a way to get the computed value exists, use that
  		if ( val === undefined ) {
  			val = curCSS( elem, name );
  		}
  
  		//convert "normal" to computed value
  		if ( val === "normal" && name in cssNormalTransform ) {
  			val = cssNormalTransform[ name ];
  		}
  
  		// Return, converting to number if forced or a qualifier was provided and val looks numeric
  		if ( numeric || extra !== undefined ) {
  			num = parseFloat( val );
  			return numeric || jQuery.isNumeric( num ) ? num || 0 : val;
  		}
  		return val;
  	},
  
  	// A method for quickly swapping in/out CSS properties to get correct calculations
  	swap: function( elem, options, callback ) {
  		var ret, name,
  			old = {};
  
  		// Remember the old values, and insert the new ones
  		for ( name in options ) {
  			old[ name ] = elem.style[ name ];
  			elem.style[ name ] = options[ name ];
  		}
  
  		ret = callback.call( elem );
  
  		// Revert the old values
  		for ( name in options ) {
  			elem.style[ name ] = old[ name ];
  		}
  
  		return ret;
  	}
  });
  
  // NOTE: To any future maintainer, we've window.getComputedStyle
  // because jsdom on node.js will break without it.
  if ( window.getComputedStyle ) {
  	curCSS = function( elem, name ) {
  		var ret, width, minWidth, maxWidth,
  			computed = window.getComputedStyle( elem, null ),
  			style = elem.style;
  
  		if ( computed ) {
  
  			// getPropertyValue is only needed for .css('filter') in IE9, see #12537
  			ret = computed.getPropertyValue( name ) || computed[ name ];
  
  			if ( ret === "" && !jQuery.contains( elem.ownerDocument, elem ) ) {
  				ret = jQuery.style( elem, name );
  			}
  
  			// A tribute to the "awesome hack by Dean Edwards"
  			// Chrome < 17 and Safari 5.0 uses "computed value" instead of "used value" for margin-right
  			// Safari 5.1.7 (at least) returns percentage for a larger set of values, but width seems to be reliably pixels
  			// this is against the CSSOM draft spec: http://dev.w3.org/csswg/cssom/#resolved-values
  			if ( rnumnonpx.test( ret ) && rmargin.test( name ) ) {
  				width = style.width;
  				minWidth = style.minWidth;
  				maxWidth = style.maxWidth;
  
  				style.minWidth = style.maxWidth = style.width = ret;
  				ret = computed.width;
  
  				style.width = width;
  				style.minWidth = minWidth;
  				style.maxWidth = maxWidth;
  			}
  		}
  
  		return ret;
  	};
  } else if ( document.documentElement.currentStyle ) {
  	curCSS = function( elem, name ) {
  		var left, rsLeft,
  			ret = elem.currentStyle && elem.currentStyle[ name ],
  			style = elem.style;
  
  		// Avoid setting ret to empty string here
  		// so we don't default to auto
  		if ( ret == null && style && style[ name ] ) {
  			ret = style[ name ];
  		}
  
  		// From the awesome hack by Dean Edwards
  		// http://erik.eae.net/archives/2007/07/27/18.54.15/#comment-102291
  
  		// If we're not dealing with a regular pixel number
  		// but a number that has a weird ending, we need to convert it to pixels
  		// but not position css attributes, as those are proportional to the parent element instead
  		// and we can't measure the parent instead because it might trigger a "stacking dolls" problem
  		if ( rnumnonpx.test( ret ) && !rposition.test( name ) ) {
  
  			// Remember the original values
  			left = style.left;
  			rsLeft = elem.runtimeStyle && elem.runtimeStyle.left;
  
  			// Put in the new values to get a computed value out
  			if ( rsLeft ) {
  				elem.runtimeStyle.left = elem.currentStyle.left;
  			}
  			style.left = name === "fontSize" ? "1em" : ret;
  			ret = style.pixelLeft + "px";
  
  			// Revert the changed values
  			style.left = left;
  			if ( rsLeft ) {
  				elem.runtimeStyle.left = rsLeft;
  			}
  		}
  
  		return ret === "" ? "auto" : ret;
  	};
  }
  
  function setPositiveNumber( elem, value, subtract ) {
  	var matches = rnumsplit.exec( value );
  	return matches ?
  			Math.max( 0, matches[ 1 ] - ( subtract || 0 ) ) + ( matches[ 2 ] || "px" ) :
  			value;
  }
  
  function augmentWidthOrHeight( elem, name, extra, isBorderBox ) {
  	var i = extra === ( isBorderBox ? "border" : "content" ) ?
  		// If we already have the right measurement, avoid augmentation
  		4 :
  		// Otherwise initialize for horizontal or vertical properties
  		name === "width" ? 1 : 0,
  
  		val = 0;
  
  	for ( ; i < 4; i += 2 ) {
  		// both box models exclude margin, so add it if we want it
  		if ( extra === "margin" ) {
  			// we use jQuery.css instead of curCSS here
  			// because of the reliableMarginRight CSS hook!
  			val += jQuery.css( elem, extra + cssExpand[ i ], true );
  		}
  
  		// From this point on we use curCSS for maximum performance (relevant in animations)
  		if ( isBorderBox ) {
  			// border-box includes padding, so remove it if we want content
  			if ( extra === "content" ) {
  				val -= parseFloat( curCSS( elem, "padding" + cssExpand[ i ] ) ) || 0;
  			}
  
  			// at this point, extra isn't border nor margin, so remove border
  			if ( extra !== "margin" ) {
  				val -= parseFloat( curCSS( elem, "border" + cssExpand[ i ] + "Width" ) ) || 0;
  			}
  		} else {
  			// at this point, extra isn't content, so add padding
  			val += parseFloat( curCSS( elem, "padding" + cssExpand[ i ] ) ) || 0;
  
  			// at this point, extra isn't content nor padding, so add border
  			if ( extra !== "padding" ) {
  				val += parseFloat( curCSS( elem, "border" + cssExpand[ i ] + "Width" ) ) || 0;
  			}
  		}
  	}
  
  	return val;
  }
  
  function getWidthOrHeight( elem, name, extra ) {
  
  	// Start with offset property, which is equivalent to the border-box value
  	var val = name === "width" ? elem.offsetWidth : elem.offsetHeight,
  		valueIsBorderBox = true,
  		isBorderBox = jQuery.support.boxSizing && jQuery.css( elem, "boxSizing" ) === "border-box";
  
  	// some non-html elements return undefined for offsetWidth, so check for null/undefined
  	// svg - https://bugzilla.mozilla.org/show_bug.cgi?id=649285
  	// MathML - https://bugzilla.mozilla.org/show_bug.cgi?id=491668
  	if ( val <= 0 || val == null ) {
  		// Fall back to computed then uncomputed css if necessary
  		val = curCSS( elem, name );
  		if ( val < 0 || val == null ) {
  			val = elem.style[ name ];
  		}
  
  		// Computed unit is not pixels. Stop here and return.
  		if ( rnumnonpx.test(val) ) {
  			return val;
  		}
  
  		// we need the check for style in case a browser which returns unreliable values
  		// for getComputedStyle silently falls back to the reliable elem.style
  		valueIsBorderBox = isBorderBox && ( jQuery.support.boxSizingReliable || val === elem.style[ name ] );
  
  		// Normalize "", auto, and prepare for extra
  		val = parseFloat( val ) || 0;
  	}
  
  	// use the active box-sizing model to add/subtract irrelevant styles
  	return ( val +
  		augmentWidthOrHeight(
  			elem,
  			name,
  			extra || ( isBorderBox ? "border" : "content" ),
  			valueIsBorderBox
  		)
  	) + "px";
  }
  
  
  // Try to determine the default display value of an element
  function css_defaultDisplay( nodeName ) {
  	if ( elemdisplay[ nodeName ] ) {
  		return elemdisplay[ nodeName ];
  	}
  
  	var elem = jQuery( "<" + nodeName + ">" ).appendTo( document.body ),
  		display = elem.css("display");
  	elem.remove();
  
  	// If the simple way fails,
  	// get element's real default display by attaching it to a temp iframe
  	if ( display === "none" || display === "" ) {
  		// Use the already-created iframe if possible
  		iframe = document.body.appendChild(
  			iframe || jQuery.extend( document.createElement("iframe"), {
  				frameBorder: 0,
  				width: 0,
  				height: 0
  			})
  		);
  
  		// Create a cacheable copy of the iframe document on first call.
  		// IE and Opera will allow us to reuse the iframeDoc without re-writing the fake HTML
  		// document to it; WebKit & Firefox won't allow reusing the iframe document.
  		if ( !iframeDoc || !iframe.createElement ) {
  			iframeDoc = ( iframe.contentWindow || iframe.contentDocument ).document;
  			iframeDoc.write("<!doctype html><html><body>");
  			iframeDoc.close();
  		}
  
  		elem = iframeDoc.body.appendChild( iframeDoc.createElement(nodeName) );
  
  		display = curCSS( elem, "display" );
  		document.body.removeChild( iframe );
  	}
  
  	// Store the correct default display
  	elemdisplay[ nodeName ] = display;
  
  	return display;
  }
  
  jQuery.each([ "height", "width" ], function( i, name ) {
  	jQuery.cssHooks[ name ] = {
  		get: function( elem, computed, extra ) {
  			if ( computed ) {
  				// certain elements can have dimension info if we invisibly show them
  				// however, it must have a current display style that would benefit from this
  				if ( elem.offsetWidth === 0 && rdisplayswap.test( curCSS( elem, "display" ) ) ) {
  					return jQuery.swap( elem, cssShow, function() {
  						return getWidthOrHeight( elem, name, extra );
  					});
  				} else {
  					return getWidthOrHeight( elem, name, extra );
  				}
  			}
  		},
  
  		set: function( elem, value, extra ) {
  			return setPositiveNumber( elem, value, extra ?
  				augmentWidthOrHeight(
  					elem,
  					name,
  					extra,
  					jQuery.support.boxSizing && jQuery.css( elem, "boxSizing" ) === "border-box"
  				) : 0
  			);
  		}
  	};
  });
  
  if ( !jQuery.support.opacity ) {
  	jQuery.cssHooks.opacity = {
  		get: function( elem, computed ) {
  			// IE uses filters for opacity
  			return ropacity.test( (computed && elem.currentStyle ? elem.currentStyle.filter : elem.style.filter) || "" ) ?
  				( 0.01 * parseFloat( RegExp.$1 ) ) + "" :
  				computed ? "1" : "";
  		},
  
  		set: function( elem, value ) {
  			var style = elem.style,
  				currentStyle = elem.currentStyle,
  				opacity = jQuery.isNumeric( value ) ? "alpha(opacity=" + value * 100 + ")" : "",
  				filter = currentStyle && currentStyle.filter || style.filter || "";
  
  			// IE has trouble with opacity if it does not have layout
  			// Force it by setting the zoom level
  			style.zoom = 1;
  
  			// if setting opacity to 1, and no other filters exist - attempt to remove filter attribute #6652
  			if ( value >= 1 && jQuery.trim( filter.replace( ralpha, "" ) ) === "" &&
  				style.removeAttribute ) {
  
  				// Setting style.filter to null, "" & " " still leave "filter:" in the cssText
  				// if "filter:" is present at all, clearType is disabled, we want to avoid this
  				// style.removeAttribute is IE Only, but so apparently is this code path...
  				style.removeAttribute( "filter" );
  
  				// if there there is no filter style applied in a css rule, we are done
  				if ( currentStyle && !currentStyle.filter ) {
  					return;
  				}
  			}
  
  			// otherwise, set new filter values
  			style.filter = ralpha.test( filter ) ?
  				filter.replace( ralpha, opacity ) :
  				filter + " " + opacity;
  		}
  	};
  }
  
  // These hooks cannot be added until DOM ready because the support test
  // for it is not run until after DOM ready
  jQuery(function() {
  	if ( !jQuery.support.reliableMarginRight ) {
  		jQuery.cssHooks.marginRight = {
  			get: function( elem, computed ) {
  				// WebKit Bug 13343 - getComputedStyle returns wrong value for margin-right
  				// Work around by temporarily setting element display to inline-block
  				return jQuery.swap( elem, { "display": "inline-block" }, function() {
  					if ( computed ) {
  						return curCSS( elem, "marginRight" );
  					}
  				});
  			}
  		};
  	}
  
  	// Webkit bug: https://bugs.webkit.org/show_bug.cgi?id=29084
  	// getComputedStyle returns percent when specified for top/left/bottom/right
  	// rather than make the css module depend on the offset module, we just check for it here
  	if ( !jQuery.support.pixelPosition && jQuery.fn.position ) {
  		jQuery.each( [ "top", "left" ], function( i, prop ) {
  			jQuery.cssHooks[ prop ] = {
  				get: function( elem, computed ) {
  					if ( computed ) {
  						var ret = curCSS( elem, prop );
  						// if curCSS returns percentage, fallback to offset
  						return rnumnonpx.test( ret ) ? jQuery( elem ).position()[ prop ] + "px" : ret;
  					}
  				}
  			};
  		});
  	}
  
  });
  
  if ( jQuery.expr && jQuery.expr.filters ) {
  	jQuery.expr.filters.hidden = function( elem ) {
  		return ( elem.offsetWidth === 0 && elem.offsetHeight === 0 ) || (!jQuery.support.reliableHiddenOffsets && ((elem.style && elem.style.display) || curCSS( elem, "display" )) === "none");
  	};
  
  	jQuery.expr.filters.visible = function( elem ) {
  		return !jQuery.expr.filters.hidden( elem );
  	};
  }
  
  // These hooks are used by animate to expand properties
  jQuery.each({
  	margin: "",
  	padding: "",
  	border: "Width"
  }, function( prefix, suffix ) {
  	jQuery.cssHooks[ prefix + suffix ] = {
  		expand: function( value ) {
  			var i,
  
  				// assumes a single number if not a string
  				parts = typeof value === "string" ? value.split(" ") : [ value ],
  				expanded = {};
  
  			for ( i = 0; i < 4; i++ ) {
  				expanded[ prefix + cssExpand[ i ] + suffix ] =
  					parts[ i ] || parts[ i - 2 ] || parts[ 0 ];
  			}
  
  			return expanded;
  		}
  	};
  
  	if ( !rmargin.test( prefix ) ) {
  		jQuery.cssHooks[ prefix + suffix ].set = setPositiveNumber;
  	}
  });
  var r20 = /%20/g,
  	rbracket = /\[\]$/,
  	rCRLF = /\r?\n/g,
  	rinput = /^(?:color|date|datetime|datetime-local|email|hidden|month|number|password|range|search|tel|text|time|url|week)$/i,
  	rselectTextarea = /^(?:select|textarea)/i;
  
  jQuery.fn.extend({
  	serialize: function() {
  		return jQuery.param( this.serializeArray() );
  	},
  	serializeArray: function() {
  		return this.map(function(){
  			return this.elements ? jQuery.makeArray( this.elements ) : this;
  		})
  		.filter(function(){
  			return this.name && !this.disabled &&
  				( this.checked || rselectTextarea.test( this.nodeName ) ||
  					rinput.test( this.type ) );
  		})
  		.map(function( i, elem ){
  			var val = jQuery( this ).val();
  
  			return val == null ?
  				null :
  				jQuery.isArray( val ) ?
  					jQuery.map( val, function( val, i ){
  						return { name: elem.name, value: val.replace( rCRLF, "\r\n" ) };
  					}) :
  					{ name: elem.name, value: val.replace( rCRLF, "\r\n" ) };
  		}).get();
  	}
  });
  
  //Serialize an array of form elements or a set of
  //key/values into a query string
  jQuery.param = function( a, traditional ) {
  	var prefix,
  		s = [],
  		add = function( key, value ) {
  			// If value is a function, invoke it and return its value
  			value = jQuery.isFunction( value ) ? value() : ( value == null ? "" : value );
  			s[ s.length ] = encodeURIComponent( key ) + "=" + encodeURIComponent( value );
  		};
  
  	// Set traditional to true for jQuery <= 1.3.2 behavior.
  	if ( traditional === undefined ) {
  		traditional = jQuery.ajaxSettings && jQuery.ajaxSettings.traditional;
  	}
  
  	// If an array was passed in, assume that it is an array of form elements.
  	if ( jQuery.isArray( a ) || ( a.jquery && !jQuery.isPlainObject( a ) ) ) {
  		// Serialize the form elements
  		jQuery.each( a, function() {
  			add( this.name, this.value );
  		});
  
  	} else {
  		// If traditional, encode the "old" way (the way 1.3.2 or older
  		// did it), otherwise encode params recursively.
  		for ( prefix in a ) {
  			buildParams( prefix, a[ prefix ], traditional, add );
  		}
  	}
  
  	// Return the resulting serialization
  	return s.join( "&" ).replace( r20, "+" );
  };
  
  function buildParams( prefix, obj, traditional, add ) {
  	var name;
  
  	if ( jQuery.isArray( obj ) ) {
  		// Serialize array item.
  		jQuery.each( obj, function( i, v ) {
  			if ( traditional || rbracket.test( prefix ) ) {
  				// Treat each array item as a scalar.
  				add( prefix, v );
  
  			} else {
  				// If array item is non-scalar (array or object), encode its
  				// numeric index to resolve deserialization ambiguity issues.
  				// Note that rack (as of 1.0.0) can't currently deserialize
  				// nested arrays properly, and attempting to do so may cause
  				// a server error. Possible fixes are to modify rack's
  				// deserialization algorithm or to provide an option or flag
  				// to force array serialization to be shallow.
  				buildParams( prefix + "[" + ( typeof v === "object" ? i : "" ) + "]", v, traditional, add );
  			}
  		});
  
  	} else if ( !traditional && jQuery.type( obj ) === "object" ) {
  		// Serialize object item.
  		for ( name in obj ) {
  			buildParams( prefix + "[" + name + "]", obj[ name ], traditional, add );
  		}
  
  	} else {
  		// Serialize scalar item.
  		add( prefix, obj );
  	}
  }
  var
  	// Document location
  	ajaxLocParts,
  	ajaxLocation,
  
  	rhash = /#.*$/,
  	rheaders = /^(.*?):[ \t]*([^\r\n]*)\r?$/mg, // IE leaves an \r character at EOL
  	// #7653, #8125, #8152: local protocol detection
  	rlocalProtocol = /^(?:about|app|app\-storage|.+\-extension|file|res|widget):$/,
  	rnoContent = /^(?:GET|HEAD)$/,
  	rprotocol = /^\/\//,
  	rquery = /\?/,
  	rscript = /<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,
  	rts = /([?&])_=[^&]*/,
  	rurl = /^([\w\+\.\-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/,
  
  	// Keep a copy of the old load method
  	_load = jQuery.fn.load,
  
  	/* Prefilters
  	 * 1) They are useful to introduce custom dataTypes (see ajax/jsonp.js for an example)
  	 * 2) These are called:
  	 *    - BEFORE asking for a transport
  	 *    - AFTER param serialization (s.data is a string if s.processData is true)
  	 * 3) key is the dataType
  	 * 4) the catchall symbol "*" can be used
  	 * 5) execution will start with transport dataType and THEN continue down to "*" if needed
  	 */
  	prefilters = {},
  
  	/* Transports bindings
  	 * 1) key is the dataType
  	 * 2) the catchall symbol "*" can be used
  	 * 3) selection will start with transport dataType and THEN go to "*" if needed
  	 */
  	transports = {},
  
  	// Avoid comment-prolog char sequence (#10098); must appease lint and evade compression
  	allTypes = ["*/"] + ["*"];
  
  // #8138, IE may throw an exception when accessing
  // a field from window.location if document.domain has been set
  try {
  	ajaxLocation = location.href;
  } catch( e ) {
  	// Use the href attribute of an A element
  	// since IE will modify it given document.location
  	ajaxLocation = document.createElement( "a" );
  	ajaxLocation.href = "";
  	ajaxLocation = ajaxLocation.href;
  }
  
  // Segment location into parts
  ajaxLocParts = rurl.exec( ajaxLocation.toLowerCase() ) || [];
  
  // Base "constructor" for jQuery.ajaxPrefilter and jQuery.ajaxTransport
  function addToPrefiltersOrTransports( structure ) {
  
  	// dataTypeExpression is optional and defaults to "*"
  	return function( dataTypeExpression, func ) {
  
  		if ( typeof dataTypeExpression !== "string" ) {
  			func = dataTypeExpression;
  			dataTypeExpression = "*";
  		}
  
  		var dataType, list, placeBefore,
  			dataTypes = dataTypeExpression.toLowerCase().split( core_rspace ),
  			i = 0,
  			length = dataTypes.length;
  
  		if ( jQuery.isFunction( func ) ) {
  			// For each dataType in the dataTypeExpression
  			for ( ; i < length; i++ ) {
  				dataType = dataTypes[ i ];
  				// We control if we're asked to add before
  				// any existing element
  				placeBefore = /^\+/.test( dataType );
  				if ( placeBefore ) {
  					dataType = dataType.substr( 1 ) || "*";
  				}
  				list = structure[ dataType ] = structure[ dataType ] || [];
  				// then we add to the structure accordingly
  				list[ placeBefore ? "unshift" : "push" ]( func );
  			}
  		}
  	};
  }
  
  // Base inspection function for prefilters and transports
  function inspectPrefiltersOrTransports( structure, options, originalOptions, jqXHR,
  		dataType /* internal */, inspected /* internal */ ) {
  
  	dataType = dataType || options.dataTypes[ 0 ];
  	inspected = inspected || {};
  
  	inspected[ dataType ] = true;
  
  	var selection,
  		list = structure[ dataType ],
  		i = 0,
  		length = list ? list.length : 0,
  		executeOnly = ( structure === prefilters );
  
  	for ( ; i < length && ( executeOnly || !selection ); i++ ) {
  		selection = list[ i ]( options, originalOptions, jqXHR );
  		// If we got redirected to another dataType
  		// we try there if executing only and not done already
  		if ( typeof selection === "string" ) {
  			if ( !executeOnly || inspected[ selection ] ) {
  				selection = undefined;
  			} else {
  				options.dataTypes.unshift( selection );
  				selection = inspectPrefiltersOrTransports(
  						structure, options, originalOptions, jqXHR, selection, inspected );
  			}
  		}
  	}
  	// If we're only executing or nothing was selected
  	// we try the catchall dataType if not done already
  	if ( ( executeOnly || !selection ) && !inspected[ "*" ] ) {
  		selection = inspectPrefiltersOrTransports(
  				structure, options, originalOptions, jqXHR, "*", inspected );
  	}
  	// unnecessary when only executing (prefilters)
  	// but it'll be ignored by the caller in that case
  	return selection;
  }
  
  // A special extend for ajax options
  // that takes "flat" options (not to be deep extended)
  // Fixes #9887
  function ajaxExtend( target, src ) {
  	var key, deep,
  		flatOptions = jQuery.ajaxSettings.flatOptions || {};
  	for ( key in src ) {
  		if ( src[ key ] !== undefined ) {
  			( flatOptions[ key ] ? target : ( deep || ( deep = {} ) ) )[ key ] = src[ key ];
  		}
  	}
  	if ( deep ) {
  		jQuery.extend( true, target, deep );
  	}
  }
  
  jQuery.fn.load = function( url, params, callback ) {
  	if ( typeof url !== "string" && _load ) {
  		return _load.apply( this, arguments );
  	}
  
  	// Don't do a request if no elements are being requested
  	if ( !this.length ) {
  		return this;
  	}
  
  	var selector, type, response,
  		self = this,
  		off = url.indexOf(" ");
  
  	if ( off >= 0 ) {
  		selector = url.slice( off, url.length );
  		url = url.slice( 0, off );
  	}
  
  	// If it's a function
  	if ( jQuery.isFunction( params ) ) {
  
  		// We assume that it's the callback
  		callback = params;
  		params = undefined;
  
  	// Otherwise, build a param string
  	} else if ( params && typeof params === "object" ) {
  		type = "POST";
  	}
  
  	// Request the remote document
  	jQuery.ajax({
  		url: url,
  
  		// if "type" variable is undefined, then "GET" method will be used
  		type: type,
  		dataType: "html",
  		data: params,
  		complete: function( jqXHR, status ) {
  			if ( callback ) {
  				self.each( callback, response || [ jqXHR.responseText, status, jqXHR ] );
  			}
  		}
  	}).done(function( responseText ) {
  
  		// Save response for use in complete callback
  		response = arguments;
  
  		// See if a selector was specified
  		self.html( selector ?
  
  			// Create a dummy div to hold the results
  			jQuery("<div>")
  
  				// inject the contents of the document in, removing the scripts
  				// to avoid any 'Permission Denied' errors in IE
  				.append( responseText.replace( rscript, "" ) )
  
  				// Locate the specified elements
  				.find( selector ) :
  
  			// If not, just inject the full result
  			responseText );
  
  	});
  
  	return this;
  };
  
  // Attach a bunch of functions for handling common AJAX events
  jQuery.each( "ajaxStart ajaxStop ajaxComplete ajaxError ajaxSuccess ajaxSend".split( " " ), function( i, o ){
  	jQuery.fn[ o ] = function( f ){
  		return this.on( o, f );
  	};
  });
  
  jQuery.each( [ "get", "post" ], function( i, method ) {
  	jQuery[ method ] = function( url, data, callback, type ) {
  		// shift arguments if data argument was omitted
  		if ( jQuery.isFunction( data ) ) {
  			type = type || callback;
  			callback = data;
  			data = undefined;
  		}
  
  		return jQuery.ajax({
  			type: method,
  			url: url,
  			data: data,
  			success: callback,
  			dataType: type
  		});
  	};
  });
  
  jQuery.extend({
  
  	getScript: function( url, callback ) {
  		return jQuery.get( url, undefined, callback, "script" );
  	},
  
  	getJSON: function( url, data, callback ) {
  		return jQuery.get( url, data, callback, "json" );
  	},
  
  	// Creates a full fledged settings object into target
  	// with both ajaxSettings and settings fields.
  	// If target is omitted, writes into ajaxSettings.
  	ajaxSetup: function( target, settings ) {
  		if ( settings ) {
  			// Building a settings object
  			ajaxExtend( target, jQuery.ajaxSettings );
  		} else {
  			// Extending ajaxSettings
  			settings = target;
  			target = jQuery.ajaxSettings;
  		}
  		ajaxExtend( target, settings );
  		return target;
  	},
  
  	ajaxSettings: {
  		url: ajaxLocation,
  		isLocal: rlocalProtocol.test( ajaxLocParts[ 1 ] ),
  		global: true,
  		type: "GET",
  		contentType: "application/x-www-form-urlencoded; charset=UTF-8",
  		processData: true,
  		async: true,
  		/*
  		timeout: 0,
  		data: null,
  		dataType: null,
  		username: null,
  		password: null,
  		cache: null,
  		throws: false,
  		traditional: false,
  		headers: {},
  		*/
  
  		accepts: {
  			xml: "application/xml, text/xml",
  			html: "text/html",
  			text: "text/plain",
  			json: "application/json, text/javascript",
  			"*": allTypes
  		},
  
  		contents: {
  			xml: /xml/,
  			html: /html/,
  			json: /json/
  		},
  
  		responseFields: {
  			xml: "responseXML",
  			text: "responseText"
  		},
  
  		// List of data converters
  		// 1) key format is "source_type destination_type" (a single space in-between)
  		// 2) the catchall symbol "*" can be used for source_type
  		converters: {
  
  			// Convert anything to text
  			"* text": window.String,
  
  			// Text to html (true = no transformation)
  			"text html": true,
  
  			// Evaluate text as a json expression
  			"text json": jQuery.parseJSON,
  
  			// Parse text as xml
  			"text xml": jQuery.parseXML
  		},
  
  		// For options that shouldn't be deep extended:
  		// you can add your own custom options here if
  		// and when you create one that shouldn't be
  		// deep extended (see ajaxExtend)
  		flatOptions: {
  			context: true,
  			url: true
  		}
  	},
  
  	ajaxPrefilter: addToPrefiltersOrTransports( prefilters ),
  	ajaxTransport: addToPrefiltersOrTransports( transports ),
  
  	// Main method
  	ajax: function( url, options ) {
  
  		// If url is an object, simulate pre-1.5 signature
  		if ( typeof url === "object" ) {
  			options = url;
  			url = undefined;
  		}
  
  		// Force options to be an object
  		options = options || {};
  
  		var // ifModified key
  			ifModifiedKey,
  			// Response headers
  			responseHeadersString,
  			responseHeaders,
  			// transport
  			transport,
  			// timeout handle
  			timeoutTimer,
  			// Cross-domain detection vars
  			parts,
  			// To know if global events are to be dispatched
  			fireGlobals,
  			// Loop variable
  			i,
  			// Create the final options object
  			s = jQuery.ajaxSetup( {}, options ),
  			// Callbacks context
  			callbackContext = s.context || s,
  			// Context for global events
  			// It's the callbackContext if one was provided in the options
  			// and if it's a DOM node or a jQuery collection
  			globalEventContext = callbackContext !== s &&
  				( callbackContext.nodeType || callbackContext instanceof jQuery ) ?
  						jQuery( callbackContext ) : jQuery.event,
  			// Deferreds
  			deferred = jQuery.Deferred(),
  			completeDeferred = jQuery.Callbacks( "once memory" ),
  			// Status-dependent callbacks
  			statusCode = s.statusCode || {},
  			// Headers (they are sent all at once)
  			requestHeaders = {},
  			requestHeadersNames = {},
  			// The jqXHR state
  			state = 0,
  			// Default abort message
  			strAbort = "canceled",
  			// Fake xhr
  			jqXHR = {
  
  				readyState: 0,
  
  				// Caches the header
  				setRequestHeader: function( name, value ) {
  					if ( !state ) {
  						var lname = name.toLowerCase();
  						name = requestHeadersNames[ lname ] = requestHeadersNames[ lname ] || name;
  						requestHeaders[ name ] = value;
  					}
  					return this;
  				},
  
  				// Raw string
  				getAllResponseHeaders: function() {
  					return state === 2 ? responseHeadersString : null;
  				},
  
  				// Builds headers hashtable if needed
  				getResponseHeader: function( key ) {
  					var match;
  					if ( state === 2 ) {
  						if ( !responseHeaders ) {
  							responseHeaders = {};
  							while( ( match = rheaders.exec( responseHeadersString ) ) ) {
  								responseHeaders[ match[1].toLowerCase() ] = match[ 2 ];
  							}
  						}
  						match = responseHeaders[ key.toLowerCase() ];
  					}
  					return match === undefined ? null : match;
  				},
  
  				// Overrides response content-type header
  				overrideMimeType: function( type ) {
  					if ( !state ) {
  						s.mimeType = type;
  					}
  					return this;
  				},
  
  				// Cancel the request
  				abort: function( statusText ) {
  					statusText = statusText || strAbort;
  					if ( transport ) {
  						transport.abort( statusText );
  					}
  					done( 0, statusText );
  					return this;
  				}
  			};
  
  		// Callback for when everything is done
  		// It is defined here because jslint complains if it is declared
  		// at the end of the function (which would be more logical and readable)
  		function done( status, nativeStatusText, responses, headers ) {
  			var isSuccess, success, error, response, modified,
  				statusText = nativeStatusText;
  
  			// Called once
  			if ( state === 2 ) {
  				return;
  			}
  
  			// State is "done" now
  			state = 2;
  
  			// Clear timeout if it exists
  			if ( timeoutTimer ) {
  				clearTimeout( timeoutTimer );
  			}
  
  			// Dereference transport for early garbage collection
  			// (no matter how long the jqXHR object will be used)
  			transport = undefined;
  
  			// Cache response headers
  			responseHeadersString = headers || "";
  
  			// Set readyState
  			jqXHR.readyState = status > 0 ? 4 : 0;
  
  			// Get response data
  			if ( responses ) {
  				response = ajaxHandleResponses( s, jqXHR, responses );
  			}
  
  			// If successful, handle type chaining
  			if ( status >= 200 && status < 300 || status === 304 ) {
  
  				// Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
  				if ( s.ifModified ) {
  
  					modified = jqXHR.getResponseHeader("Last-Modified");
  					if ( modified ) {
  						jQuery.lastModified[ ifModifiedKey ] = modified;
  					}
  					modified = jqXHR.getResponseHeader("Etag");
  					if ( modified ) {
  						jQuery.etag[ ifModifiedKey ] = modified;
  					}
  				}
  
  				// If not modified
  				if ( status === 304 ) {
  
  					statusText = "notmodified";
  					isSuccess = true;
  
  				// If we have data
  				} else {
  
  					isSuccess = ajaxConvert( s, response );
  					statusText = isSuccess.state;
  					success = isSuccess.data;
  					error = isSuccess.error;
  					isSuccess = !error;
  				}
  			} else {
  				// We extract error from statusText
  				// then normalize statusText and status for non-aborts
  				error = statusText;
  				if ( !statusText || status ) {
  					statusText = "error";
  					if ( status < 0 ) {
  						status = 0;
  					}
  				}
  			}
  
  			// Set data for the fake xhr object
  			jqXHR.status = status;
  			jqXHR.statusText = ( nativeStatusText || statusText ) + "";
  
  			// Success/Error
  			if ( isSuccess ) {
  				deferred.resolveWith( callbackContext, [ success, statusText, jqXHR ] );
  			} else {
  				deferred.rejectWith( callbackContext, [ jqXHR, statusText, error ] );
  			}
  
  			// Status-dependent callbacks
  			jqXHR.statusCode( statusCode );
  			statusCode = undefined;
  
  			if ( fireGlobals ) {
  				globalEventContext.trigger( "ajax" + ( isSuccess ? "Success" : "Error" ),
  						[ jqXHR, s, isSuccess ? success : error ] );
  			}
  
  			// Complete
  			completeDeferred.fireWith( callbackContext, [ jqXHR, statusText ] );
  
  			if ( fireGlobals ) {
  				globalEventContext.trigger( "ajaxComplete", [ jqXHR, s ] );
  				// Handle the global AJAX counter
  				if ( !( --jQuery.active ) ) {
  					jQuery.event.trigger( "ajaxStop" );
  				}
  			}
  		}
  
  		// Attach deferreds
  		deferred.promise( jqXHR );
  		jqXHR.success = jqXHR.done;
  		jqXHR.error = jqXHR.fail;
  		jqXHR.complete = completeDeferred.add;
  
  		// Status-dependent callbacks
  		jqXHR.statusCode = function( map ) {
  			if ( map ) {
  				var tmp;
  				if ( state < 2 ) {
  					for ( tmp in map ) {
  						statusCode[ tmp ] = [ statusCode[tmp], map[tmp] ];
  					}
  				} else {
  					tmp = map[ jqXHR.status ];
  					jqXHR.always( tmp );
  				}
  			}
  			return this;
  		};
  
  		// Remove hash character (#7531: and string promotion)
  		// Add protocol if not provided (#5866: IE7 issue with protocol-less urls)
  		// We also use the url parameter if available
  		s.url = ( ( url || s.url ) + "" ).replace( rhash, "" ).replace( rprotocol, ajaxLocParts[ 1 ] + "//" );
  
  		// Extract dataTypes list
  		s.dataTypes = jQuery.trim( s.dataType || "*" ).toLowerCase().split( core_rspace );
  
  		// A cross-domain request is in order when we have a protocol:host:port mismatch
  		if ( s.crossDomain == null ) {
  			parts = rurl.exec( s.url.toLowerCase() );
  			s.crossDomain = !!( parts &&
  				( parts[ 1 ] !== ajaxLocParts[ 1 ] || parts[ 2 ] !== ajaxLocParts[ 2 ] ||
  					( parts[ 3 ] || ( parts[ 1 ] === "http:" ? 80 : 443 ) ) !=
  						( ajaxLocParts[ 3 ] || ( ajaxLocParts[ 1 ] === "http:" ? 80 : 443 ) ) )
  			);
  		}
  
  		// Convert data if not already a string
  		if ( s.data && s.processData && typeof s.data !== "string" ) {
  			s.data = jQuery.param( s.data, s.traditional );
  		}
  
  		// Apply prefilters
  		inspectPrefiltersOrTransports( prefilters, s, options, jqXHR );
  
  		// If request was aborted inside a prefilter, stop there
  		if ( state === 2 ) {
  			return jqXHR;
  		}
  
  		// We can fire global events as of now if asked to
  		fireGlobals = s.global;
  
  		// Uppercase the type
  		s.type = s.type.toUpperCase();
  
  		// Determine if request has content
  		s.hasContent = !rnoContent.test( s.type );
  
  		// Watch for a new set of requests
  		if ( fireGlobals && jQuery.active++ === 0 ) {
  			jQuery.event.trigger( "ajaxStart" );
  		}
  
  		// More options handling for requests with no content
  		if ( !s.hasContent ) {
  
  			// If data is available, append data to url
  			if ( s.data ) {
  				s.url += ( rquery.test( s.url ) ? "&" : "?" ) + s.data;
  				// #9682: remove data so that it's not used in an eventual retry
  				delete s.data;
  			}
  
  			// Get ifModifiedKey before adding the anti-cache parameter
  			ifModifiedKey = s.url;
  
  			// Add anti-cache in url if needed
  			if ( s.cache === false ) {
  
  				var ts = jQuery.now(),
  					// try replacing _= if it is there
  					ret = s.url.replace( rts, "$1_=" + ts );
  
  				// if nothing was replaced, add timestamp to the end
  				s.url = ret + ( ( ret === s.url ) ? ( rquery.test( s.url ) ? "&" : "?" ) + "_=" + ts : "" );
  			}
  		}
  
  		// Set the correct header, if data is being sent
  		if ( s.data && s.hasContent && s.contentType !== false || options.contentType ) {
  			jqXHR.setRequestHeader( "Content-Type", s.contentType );
  		}
  
  		// Set the If-Modified-Since and/or If-None-Match header, if in ifModified mode.
  		if ( s.ifModified ) {
  			ifModifiedKey = ifModifiedKey || s.url;
  			if ( jQuery.lastModified[ ifModifiedKey ] ) {
  				jqXHR.setRequestHeader( "If-Modified-Since", jQuery.lastModified[ ifModifiedKey ] );
  			}
  			if ( jQuery.etag[ ifModifiedKey ] ) {
  				jqXHR.setRequestHeader( "If-None-Match", jQuery.etag[ ifModifiedKey ] );
  			}
  		}
  
  		// Set the Accepts header for the server, depending on the dataType
  		jqXHR.setRequestHeader(
  			"Accept",
  			s.dataTypes[ 0 ] && s.accepts[ s.dataTypes[0] ] ?
  				s.accepts[ s.dataTypes[0] ] + ( s.dataTypes[ 0 ] !== "*" ? ", " + allTypes + "; q=0.01" : "" ) :
  				s.accepts[ "*" ]
  		);
  
  		// Check for headers option
  		for ( i in s.headers ) {
  			jqXHR.setRequestHeader( i, s.headers[ i ] );
  		}
  
  		// Allow custom headers/mimetypes and early abort
  		if ( s.beforeSend && ( s.beforeSend.call( callbackContext, jqXHR, s ) === false || state === 2 ) ) {
  				// Abort if not done already and return
  				return jqXHR.abort();
  
  		}
  
  		// aborting is no longer a cancellation
  		strAbort = "abort";
  
  		// Install callbacks on deferreds
  		for ( i in { success: 1, error: 1, complete: 1 } ) {
  			jqXHR[ i ]( s[ i ] );
  		}
  
  		// Get transport
  		transport = inspectPrefiltersOrTransports( transports, s, options, jqXHR );
  
  		// If no transport, we auto-abort
  		if ( !transport ) {
  			done( -1, "No Transport" );
  		} else {
  			jqXHR.readyState = 1;
  			// Send global event
  			if ( fireGlobals ) {
  				globalEventContext.trigger( "ajaxSend", [ jqXHR, s ] );
  			}
  			// Timeout
  			if ( s.async && s.timeout > 0 ) {
  				timeoutTimer = setTimeout( function(){
  					jqXHR.abort( "timeout" );
  				}, s.timeout );
  			}
  
  			try {
  				state = 1;
  				transport.send( requestHeaders, done );
  			} catch (e) {
  				// Propagate exception as error if not done
  				if ( state < 2 ) {
  					done( -1, e );
  				// Simply rethrow otherwise
  				} else {
  					throw e;
  				}
  			}
  		}
  
  		return jqXHR;
  	},
  
  	// Counter for holding the number of active queries
  	active: 0,
  
  	// Last-Modified header cache for next request
  	lastModified: {},
  	etag: {}
  
  });
  
  /* Handles responses to an ajax request:
   * - sets all responseXXX fields accordingly
   * - finds the right dataType (mediates between content-type and expected dataType)
   * - returns the corresponding response
   */
  function ajaxHandleResponses( s, jqXHR, responses ) {
  
  	var ct, type, finalDataType, firstDataType,
  		contents = s.contents,
  		dataTypes = s.dataTypes,
  		responseFields = s.responseFields;
  
  	// Fill responseXXX fields
  	for ( type in responseFields ) {
  		if ( type in responses ) {
  			jqXHR[ responseFields[type] ] = responses[ type ];
  		}
  	}
  
  	// Remove auto dataType and get content-type in the process
  	while( dataTypes[ 0 ] === "*" ) {
  		dataTypes.shift();
  		if ( ct === undefined ) {
  			ct = s.mimeType || jqXHR.getResponseHeader( "content-type" );
  		}
  	}
  
  	// Check if we're dealing with a known content-type
  	if ( ct ) {
  		for ( type in contents ) {
  			if ( contents[ type ] && contents[ type ].test( ct ) ) {
  				dataTypes.unshift( type );
  				break;
  			}
  		}
  	}
  
  	// Check to see if we have a response for the expected dataType
  	if ( dataTypes[ 0 ] in responses ) {
  		finalDataType = dataTypes[ 0 ];
  	} else {
  		// Try convertible dataTypes
  		for ( type in responses ) {
  			if ( !dataTypes[ 0 ] || s.converters[ type + " " + dataTypes[0] ] ) {
  				finalDataType = type;
  				break;
  			}
  			if ( !firstDataType ) {
  				firstDataType = type;
  			}
  		}
  		// Or just use first one
  		finalDataType = finalDataType || firstDataType;
  	}
  
  	// If we found a dataType
  	// We add the dataType to the list if needed
  	// and return the corresponding response
  	if ( finalDataType ) {
  		if ( finalDataType !== dataTypes[ 0 ] ) {
  			dataTypes.unshift( finalDataType );
  		}
  		return responses[ finalDataType ];
  	}
  }
  
  // Chain conversions given the request and the original response
  function ajaxConvert( s, response ) {
  
  	var conv, conv2, current, tmp,
  		// Work with a copy of dataTypes in case we need to modify it for conversion
  		dataTypes = s.dataTypes.slice(),
  		prev = dataTypes[ 0 ],
  		converters = {},
  		i = 0;
  
  	// Apply the dataFilter if provided
  	if ( s.dataFilter ) {
  		response = s.dataFilter( response, s.dataType );
  	}
  
  	// Create converters map with lowercased keys
  	if ( dataTypes[ 1 ] ) {
  		for ( conv in s.converters ) {
  			converters[ conv.toLowerCase() ] = s.converters[ conv ];
  		}
  	}
  
  	// Convert to each sequential dataType, tolerating list modification
  	for ( ; (current = dataTypes[++i]); ) {
  
  		// There's only work to do if current dataType is non-auto
  		if ( current !== "*" ) {
  
  			// Convert response if prev dataType is non-auto and differs from current
  			if ( prev !== "*" && prev !== current ) {
  
  				// Seek a direct converter
  				conv = converters[ prev + " " + current ] || converters[ "* " + current ];
  
  				// If none found, seek a pair
  				if ( !conv ) {
  					for ( conv2 in converters ) {
  
  						// If conv2 outputs current
  						tmp = conv2.split(" ");
  						if ( tmp[ 1 ] === current ) {
  
  							// If prev can be converted to accepted input
  							conv = converters[ prev + " " + tmp[ 0 ] ] ||
  								converters[ "* " + tmp[ 0 ] ];
  							if ( conv ) {
  								// Condense equivalence converters
  								if ( conv === true ) {
  									conv = converters[ conv2 ];
  
  								// Otherwise, insert the intermediate dataType
  								} else if ( converters[ conv2 ] !== true ) {
  									current = tmp[ 0 ];
  									dataTypes.splice( i--, 0, current );
  								}
  
  								break;
  							}
  						}
  					}
  				}
  
  				// Apply converter (if not an equivalence)
  				if ( conv !== true ) {
  
  					// Unless errors are allowed to bubble, catch and return them
  					if ( conv && s["throws"] ) {
  						response = conv( response );
  					} else {
  						try {
  							response = conv( response );
  						} catch ( e ) {
  							return { state: "parsererror", error: conv ? e : "No conversion from " + prev + " to " + current };
  						}
  					}
  				}
  			}
  
  			// Update prev for next iteration
  			prev = current;
  		}
  	}
  
  	return { state: "success", data: response };
  }
  var oldCallbacks = [],
  	rquestion = /\?/,
  	rjsonp = /(=)\?(?=&|$)|\?\?/,
  	nonce = jQuery.now();
  
  // Default jsonp settings
  jQuery.ajaxSetup({
  	jsonp: "callback",
  	jsonpCallback: function() {
  		var callback = oldCallbacks.pop() || ( jQuery.expando + "_" + ( nonce++ ) );
  		this[ callback ] = true;
  		return callback;
  	}
  });
  
  // Detect, normalize options and install callbacks for jsonp requests
  jQuery.ajaxPrefilter( "json jsonp", function( s, originalSettings, jqXHR ) {
  
  	var callbackName, overwritten, responseContainer,
  		data = s.data,
  		url = s.url,
  		hasCallback = s.jsonp !== false,
  		replaceInUrl = hasCallback && rjsonp.test( url ),
  		replaceInData = hasCallback && !replaceInUrl && typeof data === "string" &&
  			!( s.contentType || "" ).indexOf("application/x-www-form-urlencoded") &&
  			rjsonp.test( data );
  
  	// Handle iff the expected data type is "jsonp" or we have a parameter to set
  	if ( s.dataTypes[ 0 ] === "jsonp" || replaceInUrl || replaceInData ) {
  
  		// Get callback name, remembering preexisting value associated with it
  		callbackName = s.jsonpCallback = jQuery.isFunction( s.jsonpCallback ) ?
  			s.jsonpCallback() :
  			s.jsonpCallback;
  		overwritten = window[ callbackName ];
  
  		// Insert callback into url or form data
  		if ( replaceInUrl ) {
  			s.url = url.replace( rjsonp, "$1" + callbackName );
  		} else if ( replaceInData ) {
  			s.data = data.replace( rjsonp, "$1" + callbackName );
  		} else if ( hasCallback ) {
  			s.url += ( rquestion.test( url ) ? "&" : "?" ) + s.jsonp + "=" + callbackName;
  		}
  
  		// Use data converter to retrieve json after script execution
  		s.converters["script json"] = function() {
  			if ( !responseContainer ) {
  				jQuery.error( callbackName + " was not called" );
  			}
  			return responseContainer[ 0 ];
  		};
  
  		// force json dataType
  		s.dataTypes[ 0 ] = "json";
  
  		// Install callback
  		window[ callbackName ] = function() {
  			responseContainer = arguments;
  		};
  
  		// Clean-up function (fires after converters)
  		jqXHR.always(function() {
  			// Restore preexisting value
  			window[ callbackName ] = overwritten;
  
  			// Save back as free
  			if ( s[ callbackName ] ) {
  				// make sure that re-using the options doesn't screw things around
  				s.jsonpCallback = originalSettings.jsonpCallback;
  
  				// save the callback name for future use
  				oldCallbacks.push( callbackName );
  			}
  
  			// Call if it was a function and we have a response
  			if ( responseContainer && jQuery.isFunction( overwritten ) ) {
  				overwritten( responseContainer[ 0 ] );
  			}
  
  			responseContainer = overwritten = undefined;
  		});
  
  		// Delegate to script
  		return "script";
  	}
  });
  // Install script dataType
  jQuery.ajaxSetup({
  	accepts: {
  		script: "text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"
  	},
  	contents: {
  		script: /javascript|ecmascript/
  	},
  	converters: {
  		"text script": function( text ) {
  			jQuery.globalEval( text );
  			return text;
  		}
  	}
  });
  
  // Handle cache's special case and global
  jQuery.ajaxPrefilter( "script", function( s ) {
  	if ( s.cache === undefined ) {
  		s.cache = false;
  	}
  	if ( s.crossDomain ) {
  		s.type = "GET";
  		s.global = false;
  	}
  });
  
  // Bind script tag hack transport
  jQuery.ajaxTransport( "script", function(s) {
  
  	// This transport only deals with cross domain requests
  	if ( s.crossDomain ) {
  
  		var script,
  			head = document.head || document.getElementsByTagName( "head" )[0] || document.documentElement;
  
  		return {
  
  			send: function( _, callback ) {
  
  				script = document.createElement( "script" );
  
  				script.async = "async";
  
  				if ( s.scriptCharset ) {
  					script.charset = s.scriptCharset;
  				}
  
  				script.src = s.url;
  
  				// Attach handlers for all browsers
  				script.onload = script.onreadystatechange = function( _, isAbort ) {
  
  					if ( isAbort || !script.readyState || /loaded|complete/.test( script.readyState ) ) {
  
  						// Handle memory leak in IE
  						script.onload = script.onreadystatechange = null;
  
  						// Remove the script
  						if ( head && script.parentNode ) {
  							head.removeChild( script );
  						}
  
  						// Dereference the script
  						script = undefined;
  
  						// Callback if not abort
  						if ( !isAbort ) {
  							callback( 200, "success" );
  						}
  					}
  				};
  				// Use insertBefore instead of appendChild  to circumvent an IE6 bug.
  				// This arises when a base node is used (#2709 and #4378).
  				head.insertBefore( script, head.firstChild );
  			},
  
  			abort: function() {
  				if ( script ) {
  					script.onload( 0, 1 );
  				}
  			}
  		};
  	}
  });
  var xhrCallbacks,
  	// #5280: Internet Explorer will keep connections alive if we don't abort on unload
  	xhrOnUnloadAbort = window.ActiveXObject ? function() {
  		// Abort all pending requests
  		for ( var key in xhrCallbacks ) {
  			xhrCallbacks[ key ]( 0, 1 );
  		}
  	} : false,
  	xhrId = 0;
  
  // Functions to create xhrs
  function createStandardXHR() {
  	try {
  		return new window.XMLHttpRequest();
  	} catch( e ) {}
  }
  
  function createActiveXHR() {
  	try {
  		return new window.ActiveXObject( "Microsoft.XMLHTTP" );
  	} catch( e ) {}
  }
  
  // Create the request object
  // (This is still attached to ajaxSettings for backward compatibility)
  jQuery.ajaxSettings.xhr = window.ActiveXObject ?
  	/* Microsoft failed to properly
  	 * implement the XMLHttpRequest in IE7 (can't request local files),
  	 * so we use the ActiveXObject when it is available
  	 * Additionally XMLHttpRequest can be disabled in IE7/IE8 so
  	 * we need a fallback.
  	 */
  	function() {
  		return !this.isLocal && createStandardXHR() || createActiveXHR();
  	} :
  	// For all other browsers, use the standard XMLHttpRequest object
  	createStandardXHR;
  
  // Determine support properties
  (function( xhr ) {
  	jQuery.extend( jQuery.support, {
  		ajax: !!xhr,
  		cors: !!xhr && ( "withCredentials" in xhr )
  	});
  })( jQuery.ajaxSettings.xhr() );
  
  // Create transport if the browser can provide an xhr
  if ( jQuery.support.ajax ) {
  
  	jQuery.ajaxTransport(function( s ) {
  		// Cross domain only allowed if supported through XMLHttpRequest
  		if ( !s.crossDomain || jQuery.support.cors ) {
  
  			var callback;
  
  			return {
  				send: function( headers, complete ) {
  
  					// Get a new xhr
  					var handle, i,
  						xhr = s.xhr();
  
  					// Open the socket
  					// Passing null username, generates a login popup on Opera (#2865)
  					if ( s.username ) {
  						xhr.open( s.type, s.url, s.async, s.username, s.password );
  					} else {
  						xhr.open( s.type, s.url, s.async );
  					}
  
  					// Apply custom fields if provided
  					if ( s.xhrFields ) {
  						for ( i in s.xhrFields ) {
  							xhr[ i ] = s.xhrFields[ i ];
  						}
  					}
  
  					// Override mime type if needed
  					if ( s.mimeType && xhr.overrideMimeType ) {
  						xhr.overrideMimeType( s.mimeType );
  					}
  
  					// X-Requested-With header
  					// For cross-domain requests, seeing as conditions for a preflight are
  					// akin to a jigsaw puzzle, we simply never set it to be sure.
  					// (it can always be set on a per-request basis or even using ajaxSetup)
  					// For same-domain requests, won't change header if already provided.
  					if ( !s.crossDomain && !headers["X-Requested-With"] ) {
  						headers[ "X-Requested-With" ] = "XMLHttpRequest";
  					}
  
  					// Need an extra try/catch for cross domain requests in Firefox 3
  					try {
  						for ( i in headers ) {
  							xhr.setRequestHeader( i, headers[ i ] );
  						}
  					} catch( _ ) {}
  
  					// Do send the request
  					// This may raise an exception which is actually
  					// handled in jQuery.ajax (so no try/catch here)
  					xhr.send( ( s.hasContent && s.data ) || null );
  
  					// Listener
  					callback = function( _, isAbort ) {
  
  						var status,
  							statusText,
  							responseHeaders,
  							responses,
  							xml;
  
  						// Firefox throws exceptions when accessing properties
  						// of an xhr when a network error occurred
  						// http://helpful.knobs-dials.com/index.php/Component_returned_failure_code:_0x80040111_(NS_ERROR_NOT_AVAILABLE)
  						try {
  
  							// Was never called and is aborted or complete
  							if ( callback && ( isAbort || xhr.readyState === 4 ) ) {
  
  								// Only called once
  								callback = undefined;
  
  								// Do not keep as active anymore
  								if ( handle ) {
  									xhr.onreadystatechange = jQuery.noop;
  									if ( xhrOnUnloadAbort ) {
  										delete xhrCallbacks[ handle ];
  									}
  								}
  
  								// If it's an abort
  								if ( isAbort ) {
  									// Abort it manually if needed
  									if ( xhr.readyState !== 4 ) {
  										xhr.abort();
  									}
  								} else {
  									status = xhr.status;
  									responseHeaders = xhr.getAllResponseHeaders();
  									responses = {};
  									xml = xhr.responseXML;
  
  									// Construct response list
  									if ( xml && xml.documentElement /* #4958 */ ) {
  										responses.xml = xml;
  									}
  
  									// When requesting binary data, IE6-9 will throw an exception
  									// on any attempt to access responseText (#11426)
  									try {
  										responses.text = xhr.responseText;
  									} catch( e ) {
  									}
  
  									// Firefox throws an exception when accessing
  									// statusText for faulty cross-domain requests
  									try {
  										statusText = xhr.statusText;
  									} catch( e ) {
  										// We normalize with Webkit giving an empty statusText
  										statusText = "";
  									}
  
  									// Filter status for non standard behaviors
  
  									// If the request is local and we have data: assume a success
  									// (success with no data won't get notified, that's the best we
  									// can do given current implementations)
  									if ( !status && s.isLocal && !s.crossDomain ) {
  										status = responses.text ? 200 : 404;
  									// IE - #1450: sometimes returns 1223 when it should be 204
  									} else if ( status === 1223 ) {
  										status = 204;
  									}
  								}
  							}
  						} catch( firefoxAccessException ) {
  							if ( !isAbort ) {
  								complete( -1, firefoxAccessException );
  							}
  						}
  
  						// Call complete if needed
  						if ( responses ) {
  							complete( status, statusText, responses, responseHeaders );
  						}
  					};
  
  					if ( !s.async ) {
  						// if we're in sync mode we fire the callback
  						callback();
  					} else if ( xhr.readyState === 4 ) {
  						// (IE6 & IE7) if it's in cache and has been
  						// retrieved directly we need to fire the callback
  						setTimeout( callback, 0 );
  					} else {
  						handle = ++xhrId;
  						if ( xhrOnUnloadAbort ) {
  							// Create the active xhrs callbacks list if needed
  							// and attach the unload handler
  							if ( !xhrCallbacks ) {
  								xhrCallbacks = {};
  								jQuery( window ).unload( xhrOnUnloadAbort );
  							}
  							// Add to list of active xhrs callbacks
  							xhrCallbacks[ handle ] = callback;
  						}
  						xhr.onreadystatechange = callback;
  					}
  				},
  
  				abort: function() {
  					if ( callback ) {
  						callback(0,1);
  					}
  				}
  			};
  		}
  	});
  }
  var fxNow, timerId,
  	rfxtypes = /^(?:toggle|show|hide)$/,
  	rfxnum = new RegExp( "^(?:([-+])=|)(" + core_pnum + ")([a-z%]*)$", "i" ),
  	rrun = /queueHooks$/,
  	animationPrefilters = [ defaultPrefilter ],
  	tweeners = {
  		"*": [function( prop, value ) {
  			var end, unit,
  				tween = this.createTween( prop, value ),
  				parts = rfxnum.exec( value ),
  				target = tween.cur(),
  				start = +target || 0,
  				scale = 1,
  				maxIterations = 20;
  
  			if ( parts ) {
  				end = +parts[2];
  				unit = parts[3] || ( jQuery.cssNumber[ prop ] ? "" : "px" );
  
  				// We need to compute starting value
  				if ( unit !== "px" && start ) {
  					// Iteratively approximate from a nonzero starting point
  					// Prefer the current property, because this process will be trivial if it uses the same units
  					// Fallback to end or a simple constant
  					start = jQuery.css( tween.elem, prop, true ) || end || 1;
  
  					do {
  						// If previous iteration zeroed out, double until we get *something*
  						// Use a string for doubling factor so we don't accidentally see scale as unchanged below
  						scale = scale || ".5";
  
  						// Adjust and apply
  						start = start / scale;
  						jQuery.style( tween.elem, prop, start + unit );
  
  					// Update scale, tolerating zero or NaN from tween.cur()
  					// And breaking the loop if scale is unchanged or perfect, or if we've just had enough
  					} while ( scale !== (scale = tween.cur() / target) && scale !== 1 && --maxIterations );
  				}
  
  				tween.unit = unit;
  				tween.start = start;
  				// If a +=/-= token was provided, we're doing a relative animation
  				tween.end = parts[1] ? start + ( parts[1] + 1 ) * end : end;
  			}
  			return tween;
  		}]
  	};
  
  // Animations created synchronously will run synchronously
  function createFxNow() {
  	setTimeout(function() {
  		fxNow = undefined;
  	}, 0 );
  	return ( fxNow = jQuery.now() );
  }
  
  function createTweens( animation, props ) {
  	jQuery.each( props, function( prop, value ) {
  		var collection = ( tweeners[ prop ] || [] ).concat( tweeners[ "*" ] ),
  			index = 0,
  			length = collection.length;
  		for ( ; index < length; index++ ) {
  			if ( collection[ index ].call( animation, prop, value ) ) {
  
  				// we're done with this property
  				return;
  			}
  		}
  	});
  }
  
  function Animation( elem, properties, options ) {
  	var result,
  		index = 0,
  		tweenerIndex = 0,
  		length = animationPrefilters.length,
  		deferred = jQuery.Deferred().always( function() {
  			// don't match elem in the :animated selector
  			delete tick.elem;
  		}),
  		tick = function() {
  			var currentTime = fxNow || createFxNow(),
  				remaining = Math.max( 0, animation.startTime + animation.duration - currentTime ),
  				// archaic crash bug won't allow us to use 1 - ( 0.5 || 0 ) (#12497)
  				temp = remaining / animation.duration || 0,
  				percent = 1 - temp,
  				index = 0,
  				length = animation.tweens.length;
  
  			for ( ; index < length ; index++ ) {
  				animation.tweens[ index ].run( percent );
  			}
  
  			deferred.notifyWith( elem, [ animation, percent, remaining ]);
  
  			if ( percent < 1 && length ) {
  				return remaining;
  			} else {
  				deferred.resolveWith( elem, [ animation ] );
  				return false;
  			}
  		},
  		animation = deferred.promise({
  			elem: elem,
  			props: jQuery.extend( {}, properties ),
  			opts: jQuery.extend( true, { specialEasing: {} }, options ),
  			originalProperties: properties,
  			originalOptions: options,
  			startTime: fxNow || createFxNow(),
  			duration: options.duration,
  			tweens: [],
  			createTween: function( prop, end, easing ) {
  				var tween = jQuery.Tween( elem, animation.opts, prop, end,
  						animation.opts.specialEasing[ prop ] || animation.opts.easing );
  				animation.tweens.push( tween );
  				return tween;
  			},
  			stop: function( gotoEnd ) {
  				var index = 0,
  					// if we are going to the end, we want to run all the tweens
  					// otherwise we skip this part
  					length = gotoEnd ? animation.tweens.length : 0;
  
  				for ( ; index < length ; index++ ) {
  					animation.tweens[ index ].run( 1 );
  				}
  
  				// resolve when we played the last frame
  				// otherwise, reject
  				if ( gotoEnd ) {
  					deferred.resolveWith( elem, [ animation, gotoEnd ] );
  				} else {
  					deferred.rejectWith( elem, [ animation, gotoEnd ] );
  				}
  				return this;
  			}
  		}),
  		props = animation.props;
  
  	propFilter( props, animation.opts.specialEasing );
  
  	for ( ; index < length ; index++ ) {
  		result = animationPrefilters[ index ].call( animation, elem, props, animation.opts );
  		if ( result ) {
  			return result;
  		}
  	}
  
  	createTweens( animation, props );
  
  	if ( jQuery.isFunction( animation.opts.start ) ) {
  		animation.opts.start.call( elem, animation );
  	}
  
  	jQuery.fx.timer(
  		jQuery.extend( tick, {
  			anim: animation,
  			queue: animation.opts.queue,
  			elem: elem
  		})
  	);
  
  	// attach callbacks from options
  	return animation.progress( animation.opts.progress )
  		.done( animation.opts.done, animation.opts.complete )
  		.fail( animation.opts.fail )
  		.always( animation.opts.always );
  }
  
  function propFilter( props, specialEasing ) {
  	var index, name, easing, value, hooks;
  
  	// camelCase, specialEasing and expand cssHook pass
  	for ( index in props ) {
  		name = jQuery.camelCase( index );
  		easing = specialEasing[ name ];
  		value = props[ index ];
  		if ( jQuery.isArray( value ) ) {
  			easing = value[ 1 ];
  			value = props[ index ] = value[ 0 ];
  		}
  
  		if ( index !== name ) {
  			props[ name ] = value;
  			delete props[ index ];
  		}
  
  		hooks = jQuery.cssHooks[ name ];
  		if ( hooks && "expand" in hooks ) {
  			value = hooks.expand( value );
  			delete props[ name ];
  
  			// not quite $.extend, this wont overwrite keys already present.
  			// also - reusing 'index' from above because we have the correct "name"
  			for ( index in value ) {
  				if ( !( index in props ) ) {
  					props[ index ] = value[ index ];
  					specialEasing[ index ] = easing;
  				}
  			}
  		} else {
  			specialEasing[ name ] = easing;
  		}
  	}
  }
  
  jQuery.Animation = jQuery.extend( Animation, {
  
  	tweener: function( props, callback ) {
  		if ( jQuery.isFunction( props ) ) {
  			callback = props;
  			props = [ "*" ];
  		} else {
  			props = props.split(" ");
  		}
  
  		var prop,
  			index = 0,
  			length = props.length;
  
  		for ( ; index < length ; index++ ) {
  			prop = props[ index ];
  			tweeners[ prop ] = tweeners[ prop ] || [];
  			tweeners[ prop ].unshift( callback );
  		}
  	},
  
  	prefilter: function( callback, prepend ) {
  		if ( prepend ) {
  			animationPrefilters.unshift( callback );
  		} else {
  			animationPrefilters.push( callback );
  		}
  	}
  });
  
  function defaultPrefilter( elem, props, opts ) {
  	var index, prop, value, length, dataShow, toggle, tween, hooks, oldfire,
  		anim = this,
  		style = elem.style,
  		orig = {},
  		handled = [],
  		hidden = elem.nodeType && isHidden( elem );
  
  	// handle queue: false promises
  	if ( !opts.queue ) {
  		hooks = jQuery._queueHooks( elem, "fx" );
  		if ( hooks.unqueued == null ) {
  			hooks.unqueued = 0;
  			oldfire = hooks.empty.fire;
  			hooks.empty.fire = function() {
  				if ( !hooks.unqueued ) {
  					oldfire();
  				}
  			};
  		}
  		hooks.unqueued++;
  
  		anim.always(function() {
  			// doing this makes sure that the complete handler will be called
  			// before this completes
  			anim.always(function() {
  				hooks.unqueued--;
  				if ( !jQuery.queue( elem, "fx" ).length ) {
  					hooks.empty.fire();
  				}
  			});
  		});
  	}
  
  	// height/width overflow pass
  	if ( elem.nodeType === 1 && ( "height" in props || "width" in props ) ) {
  		// Make sure that nothing sneaks out
  		// Record all 3 overflow attributes because IE does not
  		// change the overflow attribute when overflowX and
  		// overflowY are set to the same value
  		opts.overflow = [ style.overflow, style.overflowX, style.overflowY ];
  
  		// Set display property to inline-block for height/width
  		// animations on inline elements that are having width/height animated
  		if ( jQuery.css( elem, "display" ) === "inline" &&
  				jQuery.css( elem, "float" ) === "none" ) {
  
  			// inline-level elements accept inline-block;
  			// block-level elements need to be inline with layout
  			if ( !jQuery.support.inlineBlockNeedsLayout || css_defaultDisplay( elem.nodeName ) === "inline" ) {
  				style.display = "inline-block";
  
  			} else {
  				style.zoom = 1;
  			}
  		}
  	}
  
  	if ( opts.overflow ) {
  		style.overflow = "hidden";
  		if ( !jQuery.support.shrinkWrapBlocks ) {
  			anim.done(function() {
  				style.overflow = opts.overflow[ 0 ];
  				style.overflowX = opts.overflow[ 1 ];
  				style.overflowY = opts.overflow[ 2 ];
  			});
  		}
  	}
  
  
  	// show/hide pass
  	for ( index in props ) {
  		value = props[ index ];
  		if ( rfxtypes.exec( value ) ) {
  			delete props[ index ];
  			toggle = toggle || value === "toggle";
  			if ( value === ( hidden ? "hide" : "show" ) ) {
  				continue;
  			}
  			handled.push( index );
  		}
  	}
  
  	length = handled.length;
  	if ( length ) {
  		dataShow = jQuery._data( elem, "fxshow" ) || jQuery._data( elem, "fxshow", {} );
  		if ( "hidden" in dataShow ) {
  			hidden = dataShow.hidden;
  		}
  
  		// store state if its toggle - enables .stop().toggle() to "reverse"
  		if ( toggle ) {
  			dataShow.hidden = !hidden;
  		}
  		if ( hidden ) {
  			jQuery( elem ).show();
  		} else {
  			anim.done(function() {
  				jQuery( elem ).hide();
  			});
  		}
  		anim.done(function() {
  			var prop;
  			jQuery.removeData( elem, "fxshow", true );
  			for ( prop in orig ) {
  				jQuery.style( elem, prop, orig[ prop ] );
  			}
  		});
  		for ( index = 0 ; index < length ; index++ ) {
  			prop = handled[ index ];
  			tween = anim.createTween( prop, hidden ? dataShow[ prop ] : 0 );
  			orig[ prop ] = dataShow[ prop ] || jQuery.style( elem, prop );
  
  			if ( !( prop in dataShow ) ) {
  				dataShow[ prop ] = tween.start;
  				if ( hidden ) {
  					tween.end = tween.start;
  					tween.start = prop === "width" || prop === "height" ? 1 : 0;
  				}
  			}
  		}
  	}
  }
  
  function Tween( elem, options, prop, end, easing ) {
  	return new Tween.prototype.init( elem, options, prop, end, easing );
  }
  jQuery.Tween = Tween;
  
  Tween.prototype = {
  	constructor: Tween,
  	init: function( elem, options, prop, end, easing, unit ) {
  		this.elem = elem;
  		this.prop = prop;
  		this.easing = easing || "swing";
  		this.options = options;
  		this.start = this.now = this.cur();
  		this.end = end;
  		this.unit = unit || ( jQuery.cssNumber[ prop ] ? "" : "px" );
  	},
  	cur: function() {
  		var hooks = Tween.propHooks[ this.prop ];
  
  		return hooks && hooks.get ?
  			hooks.get( this ) :
  			Tween.propHooks._default.get( this );
  	},
  	run: function( percent ) {
  		var eased,
  			hooks = Tween.propHooks[ this.prop ];
  
  		if ( this.options.duration ) {
  			this.pos = eased = jQuery.easing[ this.easing ](
  				percent, this.options.duration * percent, 0, 1, this.options.duration
  			);
  		} else {
  			this.pos = eased = percent;
  		}
  		this.now = ( this.end - this.start ) * eased + this.start;
  
  		if ( this.options.step ) {
  			this.options.step.call( this.elem, this.now, this );
  		}
  
  		if ( hooks && hooks.set ) {
  			hooks.set( this );
  		} else {
  			Tween.propHooks._default.set( this );
  		}
  		return this;
  	}
  };
  
  Tween.prototype.init.prototype = Tween.prototype;
  
  Tween.propHooks = {
  	_default: {
  		get: function( tween ) {
  			var result;
  
  			if ( tween.elem[ tween.prop ] != null &&
  				(!tween.elem.style || tween.elem.style[ tween.prop ] == null) ) {
  				return tween.elem[ tween.prop ];
  			}
  
  			// passing any value as a 4th parameter to .css will automatically
  			// attempt a parseFloat and fallback to a string if the parse fails
  			// so, simple values such as "10px" are parsed to Float.
  			// complex values such as "rotate(1rad)" are returned as is.
  			result = jQuery.css( tween.elem, tween.prop, false, "" );
  			// Empty strings, null, undefined and "auto" are converted to 0.
  			return !result || result === "auto" ? 0 : result;
  		},
  		set: function( tween ) {
  			// use step hook for back compat - use cssHook if its there - use .style if its
  			// available and use plain properties where available
  			if ( jQuery.fx.step[ tween.prop ] ) {
  				jQuery.fx.step[ tween.prop ]( tween );
  			} else if ( tween.elem.style && ( tween.elem.style[ jQuery.cssProps[ tween.prop ] ] != null || jQuery.cssHooks[ tween.prop ] ) ) {
  				jQuery.style( tween.elem, tween.prop, tween.now + tween.unit );
  			} else {
  				tween.elem[ tween.prop ] = tween.now;
  			}
  		}
  	}
  };
  
  // Remove in 2.0 - this supports IE8's panic based approach
  // to setting things on disconnected nodes
  
  Tween.propHooks.scrollTop = Tween.propHooks.scrollLeft = {
  	set: function( tween ) {
  		if ( tween.elem.nodeType && tween.elem.parentNode ) {
  			tween.elem[ tween.prop ] = tween.now;
  		}
  	}
  };
  
  jQuery.each([ "toggle", "show", "hide" ], function( i, name ) {
  	var cssFn = jQuery.fn[ name ];
  	jQuery.fn[ name ] = function( speed, easing, callback ) {
  		return speed == null || typeof speed === "boolean" ||
  			// special check for .toggle( handler, handler, ... )
  			( !i && jQuery.isFunction( speed ) && jQuery.isFunction( easing ) ) ?
  			cssFn.apply( this, arguments ) :
  			this.animate( genFx( name, true ), speed, easing, callback );
  	};
  });
  
  jQuery.fn.extend({
  	fadeTo: function( speed, to, easing, callback ) {
  
  		// show any hidden elements after setting opacity to 0
  		return this.filter( isHidden ).css( "opacity", 0 ).show()
  
  			// animate to the value specified
  			.end().animate({ opacity: to }, speed, easing, callback );
  	},
  	animate: function( prop, speed, easing, callback ) {
  		var empty = jQuery.isEmptyObject( prop ),
  			optall = jQuery.speed( speed, easing, callback ),
  			doAnimation = function() {
  				// Operate on a copy of prop so per-property easing won't be lost
  				var anim = Animation( this, jQuery.extend( {}, prop ), optall );
  
  				// Empty animations resolve immediately
  				if ( empty ) {
  					anim.stop( true );
  				}
  			};
  
  		return empty || optall.queue === false ?
  			this.each( doAnimation ) :
  			this.queue( optall.queue, doAnimation );
  	},
  	stop: function( type, clearQueue, gotoEnd ) {
  		var stopQueue = function( hooks ) {
  			var stop = hooks.stop;
  			delete hooks.stop;
  			stop( gotoEnd );
  		};
  
  		if ( typeof type !== "string" ) {
  			gotoEnd = clearQueue;
  			clearQueue = type;
  			type = undefined;
  		}
  		if ( clearQueue && type !== false ) {
  			this.queue( type || "fx", [] );
  		}
  
  		return this.each(function() {
  			var dequeue = true,
  				index = type != null && type + "queueHooks",
  				timers = jQuery.timers,
  				data = jQuery._data( this );
  
  			if ( index ) {
  				if ( data[ index ] && data[ index ].stop ) {
  					stopQueue( data[ index ] );
  				}
  			} else {
  				for ( index in data ) {
  					if ( data[ index ] && data[ index ].stop && rrun.test( index ) ) {
  						stopQueue( data[ index ] );
  					}
  				}
  			}
  
  			for ( index = timers.length; index--; ) {
  				if ( timers[ index ].elem === this && (type == null || timers[ index ].queue === type) ) {
  					timers[ index ].anim.stop( gotoEnd );
  					dequeue = false;
  					timers.splice( index, 1 );
  				}
  			}
  
  			// start the next in the queue if the last step wasn't forced
  			// timers currently will call their complete callbacks, which will dequeue
  			// but only if they were gotoEnd
  			if ( dequeue || !gotoEnd ) {
  				jQuery.dequeue( this, type );
  			}
  		});
  	}
  });
  
  // Generate parameters to create a standard animation
  function genFx( type, includeWidth ) {
  	var which,
  		attrs = { height: type },
  		i = 0;
  
  	// if we include width, step value is 1 to do all cssExpand values,
  	// if we don't include width, step value is 2 to skip over Left and Right
  	includeWidth = includeWidth? 1 : 0;
  	for( ; i < 4 ; i += 2 - includeWidth ) {
  		which = cssExpand[ i ];
  		attrs[ "margin" + which ] = attrs[ "padding" + which ] = type;
  	}
  
  	if ( includeWidth ) {
  		attrs.opacity = attrs.width = type;
  	}
  
  	return attrs;
  }
  
  // Generate shortcuts for custom animations
  jQuery.each({
  	slideDown: genFx("show"),
  	slideUp: genFx("hide"),
  	slideToggle: genFx("toggle"),
  	fadeIn: { opacity: "show" },
  	fadeOut: { opacity: "hide" },
  	fadeToggle: { opacity: "toggle" }
  }, function( name, props ) {
  	jQuery.fn[ name ] = function( speed, easing, callback ) {
  		return this.animate( props, speed, easing, callback );
  	};
  });
  
  jQuery.speed = function( speed, easing, fn ) {
  	var opt = speed && typeof speed === "object" ? jQuery.extend( {}, speed ) : {
  		complete: fn || !fn && easing ||
  			jQuery.isFunction( speed ) && speed,
  		duration: speed,
  		easing: fn && easing || easing && !jQuery.isFunction( easing ) && easing
  	};
  
  	opt.duration = jQuery.fx.off ? 0 : typeof opt.duration === "number" ? opt.duration :
  		opt.duration in jQuery.fx.speeds ? jQuery.fx.speeds[ opt.duration ] : jQuery.fx.speeds._default;
  
  	// normalize opt.queue - true/undefined/null -> "fx"
  	if ( opt.queue == null || opt.queue === true ) {
  		opt.queue = "fx";
  	}
  
  	// Queueing
  	opt.old = opt.complete;
  
  	opt.complete = function() {
  		if ( jQuery.isFunction( opt.old ) ) {
  			opt.old.call( this );
  		}
  
  		if ( opt.queue ) {
  			jQuery.dequeue( this, opt.queue );
  		}
  	};
  
  	return opt;
  };
  
  jQuery.easing = {
  	linear: function( p ) {
  		return p;
  	},
  	swing: function( p ) {
  		return 0.5 - Math.cos( p*Math.PI ) / 2;
  	}
  };
  
  jQuery.timers = [];
  jQuery.fx = Tween.prototype.init;
  jQuery.fx.tick = function() {
  	var timer,
  		timers = jQuery.timers,
  		i = 0;
  
  	fxNow = jQuery.now();
  
  	for ( ; i < timers.length; i++ ) {
  		timer = timers[ i ];
  		// Checks the timer has not already been removed
  		if ( !timer() && timers[ i ] === timer ) {
  			timers.splice( i--, 1 );
  		}
  	}
  
  	if ( !timers.length ) {
  		jQuery.fx.stop();
  	}
  	fxNow = undefined;
  };
  
  jQuery.fx.timer = function( timer ) {
  	if ( timer() && jQuery.timers.push( timer ) && !timerId ) {
  		timerId = setInterval( jQuery.fx.tick, jQuery.fx.interval );
  	}
  };
  
  jQuery.fx.interval = 13;
  
  jQuery.fx.stop = function() {
  	clearInterval( timerId );
  	timerId = null;
  };
  
  jQuery.fx.speeds = {
  	slow: 600,
  	fast: 200,
  	// Default speed
  	_default: 400
  };
  
  // Back Compat <1.8 extension point
  jQuery.fx.step = {};
  
  if ( jQuery.expr && jQuery.expr.filters ) {
  	jQuery.expr.filters.animated = function( elem ) {
  		return jQuery.grep(jQuery.timers, function( fn ) {
  			return elem === fn.elem;
  		}).length;
  	};
  }
  var rroot = /^(?:body|html)$/i;
  
  jQuery.fn.offset = function( options ) {
  	if ( arguments.length ) {
  		return options === undefined ?
  			this :
  			this.each(function( i ) {
  				jQuery.offset.setOffset( this, options, i );
  			});
  	}
  
  	var docElem, body, win, clientTop, clientLeft, scrollTop, scrollLeft,
  		box = { top: 0, left: 0 },
  		elem = this[ 0 ],
  		doc = elem && elem.ownerDocument;
  
  	if ( !doc ) {
  		return;
  	}
  
  	if ( (body = doc.body) === elem ) {
  		return jQuery.offset.bodyOffset( elem );
  	}
  
  	docElem = doc.documentElement;
  
  	// Make sure it's not a disconnected DOM node
  	if ( !jQuery.contains( docElem, elem ) ) {
  		return box;
  	}
  
  	// If we don't have gBCR, just use 0,0 rather than error
  	// BlackBerry 5, iOS 3 (original iPhone)
  	if ( typeof elem.getBoundingClientRect !== "undefined" ) {
  		box = elem.getBoundingClientRect();
  	}
  	win = getWindow( doc );
  	clientTop  = docElem.clientTop  || body.clientTop  || 0;
  	clientLeft = docElem.clientLeft || body.clientLeft || 0;
  	scrollTop  = win.pageYOffset || docElem.scrollTop;
  	scrollLeft = win.pageXOffset || docElem.scrollLeft;
  	return {
  		top: box.top  + scrollTop  - clientTop,
  		left: box.left + scrollLeft - clientLeft
  	};
  };
  
  jQuery.offset = {
  
  	bodyOffset: function( body ) {
  		var top = body.offsetTop,
  			left = body.offsetLeft;
  
  		if ( jQuery.support.doesNotIncludeMarginInBodyOffset ) {
  			top  += parseFloat( jQuery.css(body, "marginTop") ) || 0;
  			left += parseFloat( jQuery.css(body, "marginLeft") ) || 0;
  		}
  
  		return { top: top, left: left };
  	},
  
  	setOffset: function( elem, options, i ) {
  		var position = jQuery.css( elem, "position" );
  
  		// set position first, in-case top/left are set even on static elem
  		if ( position === "static" ) {
  			elem.style.position = "relative";
  		}
  
  		var curElem = jQuery( elem ),
  			curOffset = curElem.offset(),
  			curCSSTop = jQuery.css( elem, "top" ),
  			curCSSLeft = jQuery.css( elem, "left" ),
  			calculatePosition = ( position === "absolute" || position === "fixed" ) && jQuery.inArray("auto", [curCSSTop, curCSSLeft]) > -1,
  			props = {}, curPosition = {}, curTop, curLeft;
  
  		// need to be able to calculate position if either top or left is auto and position is either absolute or fixed
  		if ( calculatePosition ) {
  			curPosition = curElem.position();
  			curTop = curPosition.top;
  			curLeft = curPosition.left;
  		} else {
  			curTop = parseFloat( curCSSTop ) || 0;
  			curLeft = parseFloat( curCSSLeft ) || 0;
  		}
  
  		if ( jQuery.isFunction( options ) ) {
  			options = options.call( elem, i, curOffset );
  		}
  
  		if ( options.top != null ) {
  			props.top = ( options.top - curOffset.top ) + curTop;
  		}
  		if ( options.left != null ) {
  			props.left = ( options.left - curOffset.left ) + curLeft;
  		}
  
  		if ( "using" in options ) {
  			options.using.call( elem, props );
  		} else {
  			curElem.css( props );
  		}
  	}
  };
  
  
  jQuery.fn.extend({
  
  	position: function() {
  		if ( !this[0] ) {
  			return;
  		}
  
  		var elem = this[0],
  
  		// Get *real* offsetParent
  		offsetParent = this.offsetParent(),
  
  		// Get correct offsets
  		offset       = this.offset(),
  		parentOffset = rroot.test(offsetParent[0].nodeName) ? { top: 0, left: 0 } : offsetParent.offset();
  
  		// Subtract element margins
  		// note: when an element has margin: auto the offsetLeft and marginLeft
  		// are the same in Safari causing offset.left to incorrectly be 0
  		offset.top  -= parseFloat( jQuery.css(elem, "marginTop") ) || 0;
  		offset.left -= parseFloat( jQuery.css(elem, "marginLeft") ) || 0;
  
  		// Add offsetParent borders
  		parentOffset.top  += parseFloat( jQuery.css(offsetParent[0], "borderTopWidth") ) || 0;
  		parentOffset.left += parseFloat( jQuery.css(offsetParent[0], "borderLeftWidth") ) || 0;
  
  		// Subtract the two offsets
  		return {
  			top:  offset.top  - parentOffset.top,
  			left: offset.left - parentOffset.left
  		};
  	},
  
  	offsetParent: function() {
  		return this.map(function() {
  			var offsetParent = this.offsetParent || document.body;
  			while ( offsetParent && (!rroot.test(offsetParent.nodeName) && jQuery.css(offsetParent, "position") === "static") ) {
  				offsetParent = offsetParent.offsetParent;
  			}
  			return offsetParent || document.body;
  		});
  	}
  });
  
  
  // Create scrollLeft and scrollTop methods
  jQuery.each( {scrollLeft: "pageXOffset", scrollTop: "pageYOffset"}, function( method, prop ) {
  	var top = /Y/.test( prop );
  
  	jQuery.fn[ method ] = function( val ) {
  		return jQuery.access( this, function( elem, method, val ) {
  			var win = getWindow( elem );
  
  			if ( val === undefined ) {
  				return win ? (prop in win) ? win[ prop ] :
  					win.document.documentElement[ method ] :
  					elem[ method ];
  			}
  
  			if ( win ) {
  				win.scrollTo(
  					!top ? val : jQuery( win ).scrollLeft(),
  					 top ? val : jQuery( win ).scrollTop()
  				);
  
  			} else {
  				elem[ method ] = val;
  			}
  		}, method, val, arguments.length, null );
  	};
  });
  
  function getWindow( elem ) {
  	return jQuery.isWindow( elem ) ?
  		elem :
  		elem.nodeType === 9 ?
  			elem.defaultView || elem.parentWindow :
  			false;
  }
  // Create innerHeight, innerWidth, height, width, outerHeight and outerWidth methods
  jQuery.each( { Height: "height", Width: "width" }, function( name, type ) {
  	jQuery.each( { padding: "inner" + name, content: type, "": "outer" + name }, function( defaultExtra, funcName ) {
  		// margin is only for outerHeight, outerWidth
  		jQuery.fn[ funcName ] = function( margin, value ) {
  			var chainable = arguments.length && ( defaultExtra || typeof margin !== "boolean" ),
  				extra = defaultExtra || ( margin === true || value === true ? "margin" : "border" );
  
  			return jQuery.access( this, function( elem, type, value ) {
  				var doc;
  
  				if ( jQuery.isWindow( elem ) ) {
  					// As of 5/8/2012 this will yield incorrect results for Mobile Safari, but there
  					// isn't a whole lot we can do. See pull request at this URL for discussion:
  					// https://github.com/jquery/jquery/pull/764
  					return elem.document.documentElement[ "client" + name ];
  				}
  
  				// Get document width or height
  				if ( elem.nodeType === 9 ) {
  					doc = elem.documentElement;
  
  					// Either scroll[Width/Height] or offset[Width/Height] or client[Width/Height], whichever is greatest
  					// unfortunately, this causes bug #3838 in IE6/8 only, but there is currently no good, small way to fix it.
  					return Math.max(
  						elem.body[ "scroll" + name ], doc[ "scroll" + name ],
  						elem.body[ "offset" + name ], doc[ "offset" + name ],
  						doc[ "client" + name ]
  					);
  				}
  
  				return value === undefined ?
  					// Get width or height on the element, requesting but not forcing parseFloat
  					jQuery.css( elem, type, value, extra ) :
  
  					// Set width or height on the element
  					jQuery.style( elem, type, value, extra );
  			}, type, chainable ? margin : undefined, chainable, null );
  		};
  	});
  });
  // Expose jQuery to the global object
  window.jQuery = window.$ = jQuery;
  
  // Expose jQuery as an AMD module, but only for AMD loaders that
  // understand the issues with loading multiple versions of jQuery
  // in a page that all might call define(). The loader will indicate
  // they have special allowances for multiple jQuery versions by
  // specifying define.amd.jQuery = true. Register as a named module,
  // since jQuery can be concatenated with other files that may use define,
  // but not use a proper concatenation script that understands anonymous
  // AMD modules. A named AMD is safest and most robust way to register.
  // Lowercase jquery is used because AMD module names are derived from
  // file names, and jQuery is normally delivered in a lowercase file name.
  // Do this after creating the global so that if an AMD module wants to call
  // noConflict to hide this version of jQuery, it will work.
  if ( typeof define === "function" && define.amd && define.amd.jQuery ) {
  	define( "jquery", [], function () { return jQuery; } );
  }
  
  })( window );
  

});

;/*!/js/index.js*/
'use strict';

(function () {
    require('modules/ignore/jquery-3.7.1/jquery');

    function main() {
        utils().bannerH();
        EventHanlder();
    }

    function utils() {
        return {
            bannerH: function bannerH() {
                $('.banner,.banner .bd li,.banner .bd ul').height($(window).height());
            },
            newsFocus: function newsFocus(page, hd, bd) {
                hd.eq(page).addClass('on').siblings('li').removeClass('on');
                bd.eq(page).css('z-index', '1').stop().animate({
                    'opacity': '1'
                }).siblings('li').css('z-index', '0').stop().animate({
                    'opacity': '0'
                });
            }
        };
    }

    //事件
    function EventHanlder() {
        //返回顶部
        $('.item4').click(function () {
            $('body,html').stop().animate({
                scrollTop: 0
            });
        });
        //信息发布切换
        $('#xz-2-item-a-r-list li').css('opacity', '0').eq(0).css('opacity', '1');
        $('.xz-2-item-a-r-nav span').mouseenter(function () {
            $('#xz-2-item-a-r-list li').eq($(this).index()).css('z-index', '1').animate({
                'opacity': '1'
            }).siblings('li').css('z-index', '0').animate({
                'opacity': '0'
            });
            $(this).addClass('on').siblings('span').removeClass('on');
        });

        // 常见问题
        $('.common-problem dt').click(function () {
            var _this = this;
            var _dt = $('.common-problem dt');
            _dt.each(function (index) {
                if (_dt.get(index) == _this) {
                    _dt.eq(index).addClass('on').next('dd').slideDown(300);
                } else {
                    _dt.eq(index).removeClass('on').next('dd').slideUp(300);
                }
            });
        });
        $('dd .hide_btn').click(function () {
            $(this).parent().slideUp().prev('dt').removeClass('on');
        });

        // 活动图集
        $('body').click(function () {
            $('.atlas-data').fadeOut();
        });
        $('.activity-atlas img').click(function (e) {
            e.stopPropagation();
            $('.atlas-data').fadeIn().children('img').attr('src', $(this).attr('data-img'));
        });
        // $('.atlas-close').click(function(e){
        //     e.stopPropagation()
        //     $('.atlas-data').fadeOut();
        // });

        // function newsFocus(page){

        // }
        //
        // 新闻列表切换
        var newsFocus_hd = $('.news-focus .hd li');
        var hd_size = newsFocus_hd.size() - 1;
        var newsFocus_bd = $('.news-focus .bd li');
        var index = 0;
        newsFocus_hd.mouseenter(function () {
            index = $(this).index();
            utils().newsFocus(index, newsFocus_hd, newsFocus_bd);
        });
        $('.news-next').click(function () {
            if (index >= hd_size) {
                index = -1;
            }
            index++;
            utils().newsFocus(index, newsFocus_hd, newsFocus_bd);
        });
        $('.news-prev').click(function () {
            if (index <= 0) {
                index = 3;
                console.log(index);
            }
            index--;
            utils().newsFocus(index, newsFocus_hd, newsFocus_bd);
        });

        // 创建队伍筹款目标
        $('.form-span-radio span').click(function () {
            $(this).addClass('on').siblings('span').removeClass('on');;
        });
        $('.form-span-radio .input-text').focus(function () {
            $('.form-span-radio span').removeClass('on');
        });

        // 队伍创建成功
        $('.pop-success-close').click(function () {
            $('.pop-success').fadeOut(300);
        });

        // 系统消息展开与收起
        $('.system-message-item').find('a').click(function () {
            $(this).nextAll('.system-message-con').fadeIn();
        });
        $('.system-message-close').click(function () {
            $(this).parent('.system-message-con').hide();
        });

        /*个人资料展开与收起*/
        $('.public-page-l-1>dd').click(function () {
            var _this = this;
            $(this).children('ul').slideDown(200);
            $(this).siblings('dd').children('ul').slideUp(300);
            $(this).children('a').addClass('on');
        });
        $('.makeup-donation').click(function(){
            $('.pop-success').fadeIn(300);
        });
        
        $('.ment-modifyPOP-btn').click(function () {
            $('.pop-success').fadeIn();
        });
    }
    $(function () {
        main();
    });
})();
;/*!/modules/ignore/fileupload/jquery.ui.widget.js*/
define('modules/ignore/fileupload/jquery.ui.widget', function(require, exports, module) {

  /*
   * jQuery UI Widget 1.10.3+amd
   * https://github.com/blueimp/jQuery-File-Upload
   *
   * Copyright 2013 jQuery Foundation and other contributors
   * Released under the MIT license.
   * http://jquery.org/license
   *
   * http://api.jqueryui.com/jQuery.widget/
   */
  (function (factory) {
      if (typeof define === "function" && define.amd) {
          // Register as an anonymous AMD module:
          define(["jquery"], factory);
      } else {
          // Browser globals:
          factory(jQuery);
      }
  }(function( $, undefined ) {
  
  var uuid = 0,
  	slice = Array.prototype.slice,
  	_cleanData = $.cleanData;
  $.cleanData = function( elems ) {
  	for ( var i = 0, elem; (elem = elems[i]) != null; i++ ) {
  		try {
  			$( elem ).triggerHandler( "remove" );
  		// http://bugs.jquery.com/ticket/8235
  		} catch( e ) {}
  	}
  	_cleanData( elems );
  };
  
  $.widget = function( name, base, prototype ) {
  	var fullName, existingConstructor, constructor, basePrototype,
  		// proxiedPrototype allows the provided prototype to remain unmodified
  		// so that it can be used as a mixin for multiple widgets (#8876)
  		proxiedPrototype = {},
  		namespace = name.split( "." )[ 0 ];
  
  	name = name.split( "." )[ 1 ];
  	fullName = namespace + "-" + name;
  
  	if ( !prototype ) {
  		prototype = base;
  		base = $.Widget;
  	}
  
  	// create selector for plugin
  	$.expr[ ":" ][ fullName.toLowerCase() ] = function( elem ) {
  		return !!$.data( elem, fullName );
  	};
  
  	$[ namespace ] = $[ namespace ] || {};
  	existingConstructor = $[ namespace ][ name ];
  	constructor = $[ namespace ][ name ] = function( options, element ) {
  		// allow instantiation without "new" keyword
  		if ( !this._createWidget ) {
  			return new constructor( options, element );
  		}
  
  		// allow instantiation without initializing for simple inheritance
  		// must use "new" keyword (the code above always passes args)
  		if ( arguments.length ) {
  			this._createWidget( options, element );
  		}
  	};
  	// extend with the existing constructor to carry over any static properties
  	$.extend( constructor, existingConstructor, {
  		version: prototype.version,
  		// copy the object used to create the prototype in case we need to
  		// redefine the widget later
  		_proto: $.extend( {}, prototype ),
  		// track widgets that inherit from this widget in case this widget is
  		// redefined after a widget inherits from it
  		_childConstructors: []
  	});
  
  	basePrototype = new base();
  	// we need to make the options hash a property directly on the new instance
  	// otherwise we'll modify the options hash on the prototype that we're
  	// inheriting from
  	basePrototype.options = $.widget.extend( {}, basePrototype.options );
  	$.each( prototype, function( prop, value ) {
  		if ( !$.isFunction( value ) ) {
  			proxiedPrototype[ prop ] = value;
  			return;
  		}
  		proxiedPrototype[ prop ] = (function() {
  			var _super = function() {
  					return base.prototype[ prop ].apply( this, arguments );
  				},
  				_superApply = function( args ) {
  					return base.prototype[ prop ].apply( this, args );
  				};
  			return function() {
  				var __super = this._super,
  					__superApply = this._superApply,
  					returnValue;
  
  				this._super = _super;
  				this._superApply = _superApply;
  
  				returnValue = value.apply( this, arguments );
  
  				this._super = __super;
  				this._superApply = __superApply;
  
  				return returnValue;
  			};
  		})();
  	});
  	constructor.prototype = $.widget.extend( basePrototype, {
  		// TODO: remove support for widgetEventPrefix
  		// always use the name + a colon as the prefix, e.g., draggable:start
  		// don't prefix for widgets that aren't DOM-based
  		widgetEventPrefix: existingConstructor ? basePrototype.widgetEventPrefix : name
  	}, proxiedPrototype, {
  		constructor: constructor,
  		namespace: namespace,
  		widgetName: name,
  		widgetFullName: fullName
  	});
  
  	// If this widget is being redefined then we need to find all widgets that
  	// are inheriting from it and redefine all of them so that they inherit from
  	// the new version of this widget. We're essentially trying to replace one
  	// level in the prototype chain.
  	if ( existingConstructor ) {
  		$.each( existingConstructor._childConstructors, function( i, child ) {
  			var childPrototype = child.prototype;
  
  			// redefine the child widget using the same prototype that was
  			// originally used, but inherit from the new version of the base
  			$.widget( childPrototype.namespace + "." + childPrototype.widgetName, constructor, child._proto );
  		});
  		// remove the list of existing child constructors from the old constructor
  		// so the old child constructors can be garbage collected
  		delete existingConstructor._childConstructors;
  	} else {
  		base._childConstructors.push( constructor );
  	}
  
  	$.widget.bridge( name, constructor );
  };
  
  $.widget.extend = function( target ) {
  	var input = slice.call( arguments, 1 ),
  		inputIndex = 0,
  		inputLength = input.length,
  		key,
  		value;
  	for ( ; inputIndex < inputLength; inputIndex++ ) {
  		for ( key in input[ inputIndex ] ) {
  			value = input[ inputIndex ][ key ];
  			if ( input[ inputIndex ].hasOwnProperty( key ) && value !== undefined ) {
  				// Clone objects
  				if ( $.isPlainObject( value ) ) {
  					target[ key ] = $.isPlainObject( target[ key ] ) ?
  						$.widget.extend( {}, target[ key ], value ) :
  						// Don't extend strings, arrays, etc. with objects
  						$.widget.extend( {}, value );
  				// Copy everything else by reference
  				} else {
  					target[ key ] = value;
  				}
  			}
  		}
  	}
  	return target;
  };
  
  $.widget.bridge = function( name, object ) {
  	var fullName = object.prototype.widgetFullName || name;
  	$.fn[ name ] = function( options ) {
  		var isMethodCall = typeof options === "string",
  			args = slice.call( arguments, 1 ),
  			returnValue = this;
  
  		// allow multiple hashes to be passed on init
  		options = !isMethodCall && args.length ?
  			$.widget.extend.apply( null, [ options ].concat(args) ) :
  			options;
  
  		if ( isMethodCall ) {
  			this.each(function() {
  				var methodValue,
  					instance = $.data( this, fullName );
  				if ( !instance ) {
  					return $.error( "cannot call methods on " + name + " prior to initialization; " +
  						"attempted to call method '" + options + "'" );
  				}
  				if ( !$.isFunction( instance[options] ) || options.charAt( 0 ) === "_" ) {
  					return $.error( "no such method '" + options + "' for " + name + " widget instance" );
  				}
  				methodValue = instance[ options ].apply( instance, args );
  				if ( methodValue !== instance && methodValue !== undefined ) {
  					returnValue = methodValue && methodValue.jquery ?
  						returnValue.pushStack( methodValue.get() ) :
  						methodValue;
  					return false;
  				}
  			});
  		} else {
  			this.each(function() {
  				var instance = $.data( this, fullName );
  				if ( instance ) {
  					instance.option( options || {} )._init();
  				} else {
  					$.data( this, fullName, new object( options, this ) );
  				}
  			});
  		}
  
  		return returnValue;
  	};
  };
  
  $.Widget = function( /* options, element */ ) {};
  $.Widget._childConstructors = [];
  
  $.Widget.prototype = {
  	widgetName: "widget",
  	widgetEventPrefix: "",
  	defaultElement: "<div>",
  	options: {
  		disabled: false,
  
  		// callbacks
  		create: null
  	},
  	_createWidget: function( options, element ) {
  		element = $( element || this.defaultElement || this )[ 0 ];
  		this.element = $( element );
  		this.uuid = uuid++;
  		this.eventNamespace = "." + this.widgetName + this.uuid;
  		this.options = $.widget.extend( {},
  			this.options,
  			this._getCreateOptions(),
  			options );
  
  		this.bindings = $();
  		this.hoverable = $();
  		this.focusable = $();
  
  		if ( element !== this ) {
  			$.data( element, this.widgetFullName, this );
  			this._on( true, this.element, {
  				remove: function( event ) {
  					if ( event.target === element ) {
  						this.destroy();
  					}
  				}
  			});
  			this.document = $( element.style ?
  				// element within the document
  				element.ownerDocument :
  				// element is window or document
  				element.document || element );
  			this.window = $( this.document[0].defaultView || this.document[0].parentWindow );
  		}
  
  		this._create();
  		this._trigger( "create", null, this._getCreateEventData() );
  		this._init();
  	},
  	_getCreateOptions: $.noop,
  	_getCreateEventData: $.noop,
  	_create: $.noop,
  	_init: $.noop,
  
  	destroy: function() {
  		this._destroy();
  		// we can probably remove the unbind calls in 2.0
  		// all event bindings should go through this._on()
  		this.element
  			.unbind( this.eventNamespace )
  			// 1.9 BC for #7810
  			// TODO remove dual storage
  			.removeData( this.widgetName )
  			.removeData( this.widgetFullName )
  			// support: jquery <1.6.3
  			// http://bugs.jquery.com/ticket/9413
  			.removeData( $.camelCase( this.widgetFullName ) );
  		this.widget()
  			.unbind( this.eventNamespace )
  			.removeAttr( "aria-disabled" )
  			.removeClass(
  				this.widgetFullName + "-disabled " +
  				"ui-state-disabled" );
  
  		// clean up events and states
  		this.bindings.unbind( this.eventNamespace );
  		this.hoverable.removeClass( "ui-state-hover" );
  		this.focusable.removeClass( "ui-state-focus" );
  	},
  	_destroy: $.noop,
  
  	widget: function() {
  		return this.element;
  	},
  
  	option: function( key, value ) {
  		var options = key,
  			parts,
  			curOption,
  			i;
  
  		if ( arguments.length === 0 ) {
  			// don't return a reference to the internal hash
  			return $.widget.extend( {}, this.options );
  		}
  
  		if ( typeof key === "string" ) {
  			// handle nested keys, e.g., "foo.bar" => { foo: { bar: ___ } }
  			options = {};
  			parts = key.split( "." );
  			key = parts.shift();
  			if ( parts.length ) {
  				curOption = options[ key ] = $.widget.extend( {}, this.options[ key ] );
  				for ( i = 0; i < parts.length - 1; i++ ) {
  					curOption[ parts[ i ] ] = curOption[ parts[ i ] ] || {};
  					curOption = curOption[ parts[ i ] ];
  				}
  				key = parts.pop();
  				if ( value === undefined ) {
  					return curOption[ key ] === undefined ? null : curOption[ key ];
  				}
  				curOption[ key ] = value;
  			} else {
  				if ( value === undefined ) {
  					return this.options[ key ] === undefined ? null : this.options[ key ];
  				}
  				options[ key ] = value;
  			}
  		}
  
  		this._setOptions( options );
  
  		return this;
  	},
  	_setOptions: function( options ) {
  		var key;
  
  		for ( key in options ) {
  			this._setOption( key, options[ key ] );
  		}
  
  		return this;
  	},
  	_setOption: function( key, value ) {
  		this.options[ key ] = value;
  
  		if ( key === "disabled" ) {
  			this.widget()
  				.toggleClass( this.widgetFullName + "-disabled ui-state-disabled", !!value )
  				.attr( "aria-disabled", value );
  			this.hoverable.removeClass( "ui-state-hover" );
  			this.focusable.removeClass( "ui-state-focus" );
  		}
  
  		return this;
  	},
  
  	enable: function() {
  		return this._setOption( "disabled", false );
  	},
  	disable: function() {
  		return this._setOption( "disabled", true );
  	},
  
  	_on: function( suppressDisabledCheck, element, handlers ) {
  		var delegateElement,
  			instance = this;
  
  		// no suppressDisabledCheck flag, shuffle arguments
  		if ( typeof suppressDisabledCheck !== "boolean" ) {
  			handlers = element;
  			element = suppressDisabledCheck;
  			suppressDisabledCheck = false;
  		}
  
  		// no element argument, shuffle and use this.element
  		if ( !handlers ) {
  			handlers = element;
  			element = this.element;
  			delegateElement = this.widget();
  		} else {
  			// accept selectors, DOM elements
  			element = delegateElement = $( element );
  			this.bindings = this.bindings.add( element );
  		}
  
  		$.each( handlers, function( event, handler ) {
  			function handlerProxy() {
  				// allow widgets to customize the disabled handling
  				// - disabled as an array instead of boolean
  				// - disabled class as method for disabling individual parts
  				if ( !suppressDisabledCheck &&
  						( instance.options.disabled === true ||
  							$( this ).hasClass( "ui-state-disabled" ) ) ) {
  					return;
  				}
  				return ( typeof handler === "string" ? instance[ handler ] : handler )
  					.apply( instance, arguments );
  			}
  
  			// copy the guid so direct unbinding works
  			if ( typeof handler !== "string" ) {
  				handlerProxy.guid = handler.guid =
  					handler.guid || handlerProxy.guid || $.guid++;
  			}
  
  			var match = event.match( /^(\w+)\s*(.*)$/ ),
  				eventName = match[1] + instance.eventNamespace,
  				selector = match[2];
  			if ( selector ) {
  				delegateElement.delegate( selector, eventName, handlerProxy );
  			} else {
  				element.bind( eventName, handlerProxy );
  			}
  		});
  	},
  
  	_off: function( element, eventName ) {
  		eventName = (eventName || "").split( " " ).join( this.eventNamespace + " " ) + this.eventNamespace;
  		element.unbind( eventName ).undelegate( eventName );
  	},
  
  	_delay: function( handler, delay ) {
  		function handlerProxy() {
  			return ( typeof handler === "string" ? instance[ handler ] : handler )
  				.apply( instance, arguments );
  		}
  		var instance = this;
  		return setTimeout( handlerProxy, delay || 0 );
  	},
  
  	_hoverable: function( element ) {
  		this.hoverable = this.hoverable.add( element );
  		this._on( element, {
  			mouseenter: function( event ) {
  				$( event.currentTarget ).addClass( "ui-state-hover" );
  			},
  			mouseleave: function( event ) {
  				$( event.currentTarget ).removeClass( "ui-state-hover" );
  			}
  		});
  	},
  
  	_focusable: function( element ) {
  		this.focusable = this.focusable.add( element );
  		this._on( element, {
  			focusin: function( event ) {
  				$( event.currentTarget ).addClass( "ui-state-focus" );
  			},
  			focusout: function( event ) {
  				$( event.currentTarget ).removeClass( "ui-state-focus" );
  			}
  		});
  	},
  
  	_trigger: function( type, event, data ) {
  		var prop, orig,
  			callback = this.options[ type ];
  
  		data = data || {};
  		event = $.Event( event );
  		event.type = ( type === this.widgetEventPrefix ?
  			type :
  			this.widgetEventPrefix + type ).toLowerCase();
  		// the original event may come from any element
  		// so we need to reset the target on the new event
  		event.target = this.element[ 0 ];
  
  		// copy original event properties over to the new event
  		orig = event.originalEvent;
  		if ( orig ) {
  			for ( prop in orig ) {
  				if ( !( prop in event ) ) {
  					event[ prop ] = orig[ prop ];
  				}
  			}
  		}
  
  		this.element.trigger( event, data );
  		return !( $.isFunction( callback ) &&
  			callback.apply( this.element[0], [ event ].concat( data ) ) === false ||
  			event.isDefaultPrevented() );
  	}
  };
  
  $.each( { show: "fadeIn", hide: "fadeOut" }, function( method, defaultEffect ) {
  	$.Widget.prototype[ "_" + method ] = function( element, options, callback ) {
  		if ( typeof options === "string" ) {
  			options = { effect: options };
  		}
  		var hasOptions,
  			effectName = !options ?
  				method :
  				options === true || typeof options === "number" ?
  					defaultEffect :
  					options.effect || defaultEffect;
  		options = options || {};
  		if ( typeof options === "number" ) {
  			options = { duration: options };
  		}
  		hasOptions = !$.isEmptyObject( options );
  		options.complete = callback;
  		if ( options.delay ) {
  			element.delay( options.delay );
  		}
  		if ( hasOptions && $.effects && $.effects.effect[ effectName ] ) {
  			element[ method ]( options );
  		} else if ( effectName !== method && element[ effectName ] ) {
  			element[ effectName ]( options.duration, options.easing, callback );
  		} else {
  			element.queue(function( next ) {
  				$( this )[ method ]();
  				if ( callback ) {
  					callback.call( element[ 0 ] );
  				}
  				next();
  			});
  		}
  	};
  });
  
  }));
  

});

;/*!/modules/ignore/fileupload/jquery.iframe-transport.js*/
define('modules/ignore/fileupload/jquery.iframe-transport', function(require, exports, module) {

  /*
   * jQuery Iframe Transport Plugin 1.8.2
   * https://github.com/blueimp/jQuery-File-Upload
   *
   * Copyright 2011, Sebastian Tschan
   * https://blueimp.net
   *
   * Licensed under the MIT license:
   * http://www.opensource.org/licenses/MIT
   */
  
  /* global define, window, document */
  (function (factory) {
      'use strict';
      if (typeof define === 'function' && define.amd) {
          // Register as an anonymous AMD module:
          define(['jquery'], factory);
      } else {
          // Browser globals:
          factory(window.jQuery);
      }
  }(function ($) {
      'use strict';
      // Helper variable to create unique names for the transport iframes:
      var counter = 0;
  
      // The iframe transport accepts four additional options:
      // options.fileInput: a jQuery collection of file input fields
      // options.paramName: the parameter name for the file form data,
      //  overrides the name property of the file input field(s),
      //  can be a string or an array of strings.
      // options.formData: an array of objects with name and value properties,
      //  equivalent to the return data of .serializeArray(), e.g.:
      //  [{name: 'a', value: 1}, {name: 'b', value: 2}]
      // options.initialIframeSrc: the URL of the initial iframe src,
      //  by default set to "javascript:false;"
      $.ajaxTransport('iframe', function (options) {
          if (options.async) {
              // javascript:false as initial iframe src
              // prevents warning popups on HTTPS in IE6:
              /*jshint scripturl: true */
              var initialIframeSrc = options.initialIframeSrc || 'javascript:false;',
              /*jshint scripturl: false */
                  form,
                  iframe,
                  addParamChar;
              return {
                  send: function (_, completeCallback) {
                      form = $('<form style="display:none;"></form>');
                      form.attr('accept-charset', options.formAcceptCharset);
                      addParamChar = /\?/.test(options.url) ? '&' : '?';
                      // XDomainRequest only supports GET and POST:
                      if (options.type === 'DELETE') {
                          options.url = options.url + addParamChar + '_method=DELETE';
                          options.type = 'POST';
                      } else if (options.type === 'PUT') {
                          options.url = options.url + addParamChar + '_method=PUT';
                          options.type = 'POST';
                      } else if (options.type === 'PATCH') {
                          options.url = options.url + addParamChar + '_method=PATCH';
                          options.type = 'POST';
                      }
                      // IE versions below IE8 cannot set the name property of
                      // elements that have already been added to the DOM,
                      // so we set the name along with the iframe HTML markup:
                      counter += 1;
                      iframe = $(
                          '<iframe src="' + initialIframeSrc +
                              '" name="iframe-transport-' + counter + '"></iframe>'
                      ).bind('load', function () {
                          var fileInputClones,
                              paramNames = $.isArray(options.paramName) ?
                                      options.paramName : [options.paramName];
                          iframe
                              .unbind('load')
                              .bind('load', function () {
                                  var response;
                                  // Wrap in a try/catch block to catch exceptions thrown
                                  // when trying to access cross-domain iframe contents:
                                  try {
                                      response = iframe.contents();
                                      // Google Chrome and Firefox do not throw an
                                      // exception when calling iframe.contents() on
                                      // cross-domain requests, so we unify the response:
                                      if (!response.length || !response[0].firstChild) {
                                          throw new Error();
                                      }
                                  } catch (e) {
                                      response = undefined;
                                  }
                                  // The complete callback returns the
                                  // iframe content document as response object:
                                  completeCallback(
                                      200,
                                      'success',
                                      {'iframe': response}
                                  );
                                  // Fix for IE endless progress bar activity bug
                                  // (happens on form submits to iframe targets):
                                  $('<iframe src="' + initialIframeSrc + '"></iframe>')
                                      .appendTo(form);
                                  window.setTimeout(function () {
                                      // Removing the form in a setTimeout call
                                      // allows Chrome's developer tools to display
                                      // the response result
                                      form.remove();
                                  }, 0);
                              });
                          form
                              .prop('target', iframe.prop('name'))
                              .prop('action', options.url)
                              .prop('method', options.type);
                          if (options.formData) {
                              $.each(options.formData, function (index, field) {
                                  $('<input type="hidden"/>')
                                      .prop('name', field.name)
                                      .val(field.value)
                                      .appendTo(form);
                              });
                          }
                          if (options.fileInput && options.fileInput.length &&
                                  options.type === 'POST') {
                              fileInputClones = options.fileInput.clone();
                              // Insert a clone for each file input field:
                              options.fileInput.after(function (index) {
                                  return fileInputClones[index];
                              });
                              if (options.paramName) {
                                  options.fileInput.each(function (index) {
                                      $(this).prop(
                                          'name',
                                          paramNames[index] || options.paramName
                                      );
                                  });
                              }
                              // Appending the file input fields to the hidden form
                              // removes them from their original location:
                              form
                                  .append(options.fileInput)
                                  .prop('enctype', 'multipart/form-data')
                                  // enctype must be set as encoding for IE:
                                  .prop('encoding', 'multipart/form-data');
                              // Remove the HTML5 form attribute from the input(s):
                              options.fileInput.removeAttr('form');
                          }
                          form.submit();
                          // Insert the file input fields at their original location
                          // by replacing the clones with the originals:
                          if (fileInputClones && fileInputClones.length) {
                              options.fileInput.each(function (index, input) {
                                  var clone = $(fileInputClones[index]);
                                  // Restore the original name and form properties:
                                  $(input)
                                      .prop('name', clone.prop('name'))
                                      .attr('form', clone.attr('form'));
                                  clone.replaceWith(input);
                              });
                          }
                      });
                      form.append(iframe).appendTo(document.body);
                  },
                  abort: function () {
                      if (iframe) {
                          // javascript:false as iframe src aborts the request
                          // and prevents warning popups on HTTPS in IE6.
                          // concat is used to avoid the "Script URL" JSLint error:
                          iframe
                              .unbind('load')
                              .prop('src', initialIframeSrc);
                      }
                      if (form) {
                          form.remove();
                      }
                  }
              };
          }
      });
  
      // The iframe transport returns the iframe content document as response.
      // The following adds converters from iframe to text, json, html, xml
      // and script.
      // Please note that the Content-Type for JSON responses has to be text/plain
      // or text/html, if the browser doesn't include application/json in the
      // Accept header, else IE will show a download dialog.
      // The Content-Type for XML responses on the other hand has to be always
      // application/xml or text/xml, so IE properly parses the XML response.
      // See also
      // https://github.com/blueimp/jQuery-File-Upload/wiki/Setup#content-type-negotiation
      $.ajaxSetup({
          converters: {
              'iframe text': function (iframe) {
                  return iframe && $(iframe[0].body).text();
              },
              'iframe json': function (iframe) {
                  return iframe && $.parseJSON($(iframe[0].body).text());
              },
              'iframe html': function (iframe) {
                  return iframe && $(iframe[0].body).html();
              },
              'iframe xml': function (iframe) {
                  var xmlDoc = iframe && iframe[0];
                  return xmlDoc && $.isXMLDoc(xmlDoc) ? xmlDoc :
                          $.parseXML((xmlDoc.XMLDocument && xmlDoc.XMLDocument.xml) ||
                              $(xmlDoc.body).html());
              },
              'iframe script': function (iframe) {
                  return iframe && $.globalEval($(iframe[0].body).text());
              }
          }
      });
  
  }));
  

});

;/*!/modules/ignore/fileupload/jquery.fileupload.js*/
define('modules/ignore/fileupload/jquery.fileupload', function(require, exports, module) {

  /*
   * jQuery File Upload Plugin 5.40.0
   * https://github.com/blueimp/jQuery-File-Upload
   *
   * Copyright 2010, Sebastian Tschan
   * https://blueimp.net
   *
   * Licensed under the MIT license:
   * http://www.opensource.org/licenses/MIT
   */
  
  /* jshint nomen:false */
  /* global define, window, document, location, Blob, FormData */
  (function (factory) {
      'use strict';
      if (typeof define === 'function' && define.amd) {
          // Register as an anonymous AMD module:
          define([
              'jquery',
              'jquery.ui.widget'
          ], factory);
      } else {
          // Browser globals:
          factory(window.jQuery);
      }
  }(function ($) {
      'use strict';
      // Detect file input support, based on
      // http://viljamis.com/blog/2012/file-upload-support-on-mobile/
      $.support.fileInput = !(new RegExp(
          // Handle devices which give false positives for the feature detection:
          '(Android (1\\.[0156]|2\\.[01]))' +
              '|(Windows Phone (OS 7|8\\.0))|(XBLWP)|(ZuneWP)|(WPDesktop)' +
              '|(w(eb)?OSBrowser)|(webOS)' +
              '|(Kindle/(1\\.0|2\\.[05]|3\\.0))'
      ).test(window.navigator.userAgent) ||
          // Feature detection for all other devices:
          $('<input type="file">').prop('disabled'));
  
      // The FileReader API is not actually used, but works as feature detection,
      // as some Safari versions (5?) support XHR file uploads via the FormData API,
      // but not non-multipart XHR file uploads.
      // window.XMLHttpRequestUpload is not available on IE10, so we check for
      // window.ProgressEvent instead to detect XHR2 file upload capability:
      $.support.xhrFileUpload = !!(window.ProgressEvent && window.FileReader);
      $.support.xhrFormDataFileUpload = !!window.FormData;
  
      // Detect support for Blob slicing (required for chunked uploads):
      $.support.blobSlice = window.Blob && (Blob.prototype.slice ||
          Blob.prototype.webkitSlice || Blob.prototype.mozSlice);
  
      // The fileupload widget listens for change events on file input fields defined
      // via fileInput setting and paste or drop events of the given dropZone.
      // In addition to the default jQuery Widget methods, the fileupload widget
      // exposes the "add" and "send" methods, to add or directly send files using
      // the fileupload API.
      // By default, files added via file input selection, paste, drag & drop or
      // "add" method are uploaded immediately, but it is possible to override
      // the "add" callback option to queue file uploads.
      $.widget('blueimp.fileupload', {
  
          options: {
              // The drop target element(s), by the default the complete document.
              // Set to null to disable drag & drop support:
              dropZone: $(document),
              // The paste target element(s), by the default the complete document.
              // Set to null to disable paste support:
              pasteZone: $(document),
              // The file input field(s), that are listened to for change events.
              // If undefined, it is set to the file input fields inside
              // of the widget element on plugin initialization.
              // Set to null to disable the change listener.
              fileInput: undefined,
              // By default, the file input field is replaced with a clone after
              // each input field change event. This is required for iframe transport
              // queues and allows change events to be fired for the same file
              // selection, but can be disabled by setting the following option to false:
              replaceFileInput: true,
              // The parameter name for the file form data (the request argument name).
              // If undefined or empty, the name property of the file input field is
              // used, or "files[]" if the file input name property is also empty,
              // can be a string or an array of strings:
              paramName: undefined,
              // By default, each file of a selection is uploaded using an individual
              // request for XHR type uploads. Set to false to upload file
              // selections in one request each:
              singleFileUploads: true,
              // To limit the number of files uploaded with one XHR request,
              // set the following option to an integer greater than 0:
              limitMultiFileUploads: undefined,
              // The following option limits the number of files uploaded with one
              // XHR request to keep the request size under or equal to the defined
              // limit in bytes:
              limitMultiFileUploadSize: undefined,
              // Multipart file uploads add a number of bytes to each uploaded file,
              // therefore the following option adds an overhead for each file used
              // in the limitMultiFileUploadSize configuration:
              limitMultiFileUploadSizeOverhead: 512,
              // Set the following option to true to issue all file upload requests
              // in a sequential order:
              sequentialUploads: false,
              // To limit the number of concurrent uploads,
              // set the following option to an integer greater than 0:
              limitConcurrentUploads: undefined,
              // Set the following option to true to force iframe transport uploads:
              forceIframeTransport: false,
              // Set the following option to the location of a redirect url on the
              // origin server, for cross-domain iframe transport uploads:
              redirect: undefined,
              // The parameter name for the redirect url, sent as part of the form
              // data and set to 'redirect' if this option is empty:
              redirectParamName: undefined,
              // Set the following option to the location of a postMessage window,
              // to enable postMessage transport uploads:
              postMessage: undefined,
              // By default, XHR file uploads are sent as multipart/form-data.
              // The iframe transport is always using multipart/form-data.
              // Set to false to enable non-multipart XHR uploads:
              multipart: true,
              // To upload large files in smaller chunks, set the following option
              // to a preferred maximum chunk size. If set to 0, null or undefined,
              // or the browser does not support the required Blob API, files will
              // be uploaded as a whole.
              maxChunkSize: undefined,
              // When a non-multipart upload or a chunked multipart upload has been
              // aborted, this option can be used to resume the upload by setting
              // it to the size of the already uploaded bytes. This option is most
              // useful when modifying the options object inside of the "add" or
              // "send" callbacks, as the options are cloned for each file upload.
              uploadedBytes: undefined,
              // By default, failed (abort or error) file uploads are removed from the
              // global progress calculation. Set the following option to false to
              // prevent recalculating the global progress data:
              recalculateProgress: true,
              // Interval in milliseconds to calculate and trigger progress events:
              progressInterval: 100,
              // Interval in milliseconds to calculate progress bitrate:
              bitrateInterval: 500,
              // By default, uploads are started automatically when adding files:
              autoUpload: true,
  
              // Error and info messages:
              messages: {
                  uploadedBytes: 'Uploaded bytes exceed file size'
              },
  
              // Translation function, gets the message key to be translated
              // and an object with context specific data as arguments:
              i18n: function (message, context) {
                  message = this.messages[message] || message.toString();
                  if (context) {
                      $.each(context, function (key, value) {
                          message = message.replace('{' + key + '}', value);
                      });
                  }
                  return message;
              },
  
              // Additional form data to be sent along with the file uploads can be set
              // using this option, which accepts an array of objects with name and
              // value properties, a function returning such an array, a FormData
              // object (for XHR file uploads), or a simple object.
              // The form of the first fileInput is given as parameter to the function:
              formData: function (form) {
                  return form.serializeArray();
              },
  
              // The add callback is invoked as soon as files are added to the fileupload
              // widget (via file input selection, drag & drop, paste or add API call).
              // If the singleFileUploads option is enabled, this callback will be
              // called once for each file in the selection for XHR file uploads, else
              // once for each file selection.
              //
              // The upload starts when the submit method is invoked on the data parameter.
              // The data object contains a files property holding the added files
              // and allows you to override plugin options as well as define ajax settings.
              //
              // Listeners for this callback can also be bound the following way:
              // .bind('fileuploadadd', func);
              //
              // data.submit() returns a Promise object and allows to attach additional
              // handlers using jQuery's Deferred callbacks:
              // data.submit().done(func).fail(func).always(func);
              add: function (e, data) {
                  if (e.isDefaultPrevented()) {
                      return false;
                  }
                  if (data.autoUpload || (data.autoUpload !== false &&
                          $(this).fileupload('option', 'autoUpload'))) {
                      data.process().done(function () {
                          data.submit();
                      });
                  }
              },
  
              // Other callbacks:
  
              // Callback for the submit event of each file upload:
              // submit: function (e, data) {}, // .bind('fileuploadsubmit', func);
  
              // Callback for the start of each file upload request:
              // send: function (e, data) {}, // .bind('fileuploadsend', func);
  
              // Callback for successful uploads:
              // done: function (e, data) {}, // .bind('fileuploaddone', func);
  
              // Callback for failed (abort or error) uploads:
              // fail: function (e, data) {}, // .bind('fileuploadfail', func);
  
              // Callback for completed (success, abort or error) requests:
              // always: function (e, data) {}, // .bind('fileuploadalways', func);
  
              // Callback for upload progress events:
              // progress: function (e, data) {}, // .bind('fileuploadprogress', func);
  
              // Callback for global upload progress events:
              // progressall: function (e, data) {}, // .bind('fileuploadprogressall', func);
  
              // Callback for uploads start, equivalent to the global ajaxStart event:
              // start: function (e) {}, // .bind('fileuploadstart', func);
  
              // Callback for uploads stop, equivalent to the global ajaxStop event:
              // stop: function (e) {}, // .bind('fileuploadstop', func);
  
              // Callback for change events of the fileInput(s):
              // change: function (e, data) {}, // .bind('fileuploadchange', func);
  
              // Callback for paste events to the pasteZone(s):
              // paste: function (e, data) {}, // .bind('fileuploadpaste', func);
  
              // Callback for drop events of the dropZone(s):
              // drop: function (e, data) {}, // .bind('fileuploaddrop', func);
  
              // Callback for dragover events of the dropZone(s):
              // dragover: function (e) {}, // .bind('fileuploaddragover', func);
  
              // Callback for the start of each chunk upload request:
              // chunksend: function (e, data) {}, // .bind('fileuploadchunksend', func);
  
              // Callback for successful chunk uploads:
              // chunkdone: function (e, data) {}, // .bind('fileuploadchunkdone', func);
  
              // Callback for failed (abort or error) chunk uploads:
              // chunkfail: function (e, data) {}, // .bind('fileuploadchunkfail', func);
  
              // Callback for completed (success, abort or error) chunk upload requests:
              // chunkalways: function (e, data) {}, // .bind('fileuploadchunkalways', func);
  
              // The plugin options are used as settings object for the ajax calls.
              // The following are jQuery ajax settings required for the file uploads:
              processData: false,
              contentType: false,
              cache: false
          },
  
          // A list of options that require reinitializing event listeners and/or
          // special initialization code:
          _specialOptions: [
              'fileInput',
              'dropZone',
              'pasteZone',
              'multipart',
              'forceIframeTransport'
          ],
  
          _blobSlice: $.support.blobSlice && function () {
              var slice = this.slice || this.webkitSlice || this.mozSlice;
              return slice.apply(this, arguments);
          },
  
          _BitrateTimer: function () {
              this.timestamp = ((Date.now) ? Date.now() : (new Date()).getTime());
              this.loaded = 0;
              this.bitrate = 0;
              this.getBitrate = function (now, loaded, interval) {
                  var timeDiff = now - this.timestamp;
                  if (!this.bitrate || !interval || timeDiff > interval) {
                      this.bitrate = (loaded - this.loaded) * (1000 / timeDiff) * 8;
                      this.loaded = loaded;
                      this.timestamp = now;
                  }
                  return this.bitrate;
              };
          },
  
          _isXHRUpload: function (options) {
              return !options.forceIframeTransport &&
                  ((!options.multipart && $.support.xhrFileUpload) ||
                  $.support.xhrFormDataFileUpload);
          },
  
          _getFormData: function (options) {
              var formData;
              if ($.type(options.formData) === 'function') {
                  return options.formData(options.form);
              }
              if ($.isArray(options.formData)) {
                  return options.formData;
              }
              if ($.type(options.formData) === 'object') {
                  formData = [];
                  $.each(options.formData, function (name, value) {
                      formData.push({name: name, value: value});
                  });
                  return formData;
              }
              return [];
          },
  
          _getTotal: function (files) {
              var total = 0;
              $.each(files, function (index, file) {
                  total += file.size || 1;
              });
              return total;
          },
  
          _initProgressObject: function (obj) {
              var progress = {
                  loaded: 0,
                  total: 0,
                  bitrate: 0
              };
              if (obj._progress) {
                  $.extend(obj._progress, progress);
              } else {
                  obj._progress = progress;
              }
          },
  
          _initResponseObject: function (obj) {
              var prop;
              if (obj._response) {
                  for (prop in obj._response) {
                      if (obj._response.hasOwnProperty(prop)) {
                          delete obj._response[prop];
                      }
                  }
              } else {
                  obj._response = {};
              }
          },
  
          _onProgress: function (e, data) {
              if (e.lengthComputable) {
                  var now = ((Date.now) ? Date.now() : (new Date()).getTime()),
                      loaded;
                  if (data._time && data.progressInterval &&
                          (now - data._time < data.progressInterval) &&
                          e.loaded !== e.total) {
                      return;
                  }
                  data._time = now;
                  loaded = Math.floor(
                      e.loaded / e.total * (data.chunkSize || data._progress.total)
                  ) + (data.uploadedBytes || 0);
                  // Add the difference from the previously loaded state
                  // to the global loaded counter:
                  this._progress.loaded += (loaded - data._progress.loaded);
                  this._progress.bitrate = this._bitrateTimer.getBitrate(
                      now,
                      this._progress.loaded,
                      data.bitrateInterval
                  );
                  data._progress.loaded = data.loaded = loaded;
                  data._progress.bitrate = data.bitrate = data._bitrateTimer.getBitrate(
                      now,
                      loaded,
                      data.bitrateInterval
                  );
                  // Trigger a custom progress event with a total data property set
                  // to the file size(s) of the current upload and a loaded data
                  // property calculated accordingly:
                  this._trigger(
                      'progress',
                      $.Event('progress', {delegatedEvent: e}),
                      data
                  );
                  // Trigger a global progress event for all current file uploads,
                  // including ajax calls queued for sequential file uploads:
                  this._trigger(
                      'progressall',
                      $.Event('progressall', {delegatedEvent: e}),
                      this._progress
                  );
              }
          },
  
          _initProgressListener: function (options) {
              var that = this,
                  xhr = options.xhr ? options.xhr() : $.ajaxSettings.xhr();
              // Accesss to the native XHR object is required to add event listeners
              // for the upload progress event:
              if (xhr.upload) {
                  $(xhr.upload).bind('progress', function (e) {
                      var oe = e.originalEvent;
                      // Make sure the progress event properties get copied over:
                      e.lengthComputable = oe.lengthComputable;
                      e.loaded = oe.loaded;
                      e.total = oe.total;
                      that._onProgress(e, options);
                  });
                  options.xhr = function () {
                      return xhr;
                  };
              }
          },
  
          _isInstanceOf: function (type, obj) {
              // Cross-frame instanceof check
              return Object.prototype.toString.call(obj) === '[object ' + type + ']';
          },
  
          _initXHRData: function (options) {
              var that = this,
                  formData,
                  file = options.files[0],
                  // Ignore non-multipart setting if not supported:
                  multipart = options.multipart || !$.support.xhrFileUpload,
                  paramName = $.type(options.paramName) === 'array' ?
                      options.paramName[0] : options.paramName;
              options.headers = $.extend({}, options.headers);
              if (options.contentRange) {
                  options.headers['Content-Range'] = options.contentRange;
              }
              if (!multipart || options.blob || !this._isInstanceOf('File', file)) {
                  options.headers['Content-Disposition'] = 'attachment; filename="' +
                      encodeURI(file.name) + '"';
              }
              if (!multipart) {
                  options.contentType = file.type || 'application/octet-stream';
                  options.data = options.blob || file;
              } else if ($.support.xhrFormDataFileUpload) {
                  if (options.postMessage) {
                      // window.postMessage does not allow sending FormData
                      // objects, so we just add the File/Blob objects to
                      // the formData array and let the postMessage window
                      // create the FormData object out of this array:
                      formData = this._getFormData(options);
                      if (options.blob) {
                          formData.push({
                              name: paramName,
                              value: options.blob
                          });
                      } else {
                          $.each(options.files, function (index, file) {
                              formData.push({
                                  name: ($.type(options.paramName) === 'array' &&
                                      options.paramName[index]) || paramName,
                                  value: file
                              });
                          });
                      }
                  } else {
                      if (that._isInstanceOf('FormData', options.formData)) {
                          formData = options.formData;
                      } else {
                          formData = new FormData();
                          $.each(this._getFormData(options), function (index, field) {
                              formData.append(field.name, field.value);
                          });
                      }
                      if (options.blob) {
                          formData.append(paramName, options.blob, file.name);
                      } else {
                          $.each(options.files, function (index, file) {
                              // This check allows the tests to run with
                              // dummy objects:
                              if (that._isInstanceOf('File', file) ||
                                      that._isInstanceOf('Blob', file)) {
                                  formData.append(
                                      ($.type(options.paramName) === 'array' &&
                                          options.paramName[index]) || paramName,
                                      file,
                                      file.uploadName || file.name
                                  );
                              }
                          });
                      }
                  }
                  options.data = formData;
              }
              // Blob reference is not needed anymore, free memory:
              options.blob = null;
          },
  
          _initIframeSettings: function (options) {
              var targetHost = $('<a></a>').prop('href', options.url).prop('host');
              // Setting the dataType to iframe enables the iframe transport:
              options.dataType = 'iframe ' + (options.dataType || '');
              // The iframe transport accepts a serialized array as form data:
              options.formData = this._getFormData(options);
              // Add redirect url to form data on cross-domain uploads:
              if (options.redirect && targetHost && targetHost !== location.host) {
                  options.formData.push({
                      name: options.redirectParamName || 'redirect',
                      value: options.redirect
                  });
              }
          },
  
          _initDataSettings: function (options) {
              if (this._isXHRUpload(options)) {
                  if (!this._chunkedUpload(options, true)) {
                      if (!options.data) {
                          this._initXHRData(options);
                      }
                      this._initProgressListener(options);
                  }
                  if (options.postMessage) {
                      // Setting the dataType to postmessage enables the
                      // postMessage transport:
                      options.dataType = 'postmessage ' + (options.dataType || '');
                  }
              } else {
                  this._initIframeSettings(options);
              }
          },
  
          _getParamName: function (options) {
              var fileInput = $(options.fileInput),
                  paramName = options.paramName;
              if (!paramName) {
                  paramName = [];
                  fileInput.each(function () {
                      var input = $(this),
                          name = input.prop('name') || 'files[]',
                          i = (input.prop('files') || [1]).length;
                      while (i) {
                          paramName.push(name);
                          i -= 1;
                      }
                  });
                  if (!paramName.length) {
                      paramName = [fileInput.prop('name') || 'files[]'];
                  }
              } else if (!$.isArray(paramName)) {
                  paramName = [paramName];
              }
              return paramName;
          },
  
          _initFormSettings: function (options) {
              // Retrieve missing options from the input field and the
              // associated form, if available:
              if (!options.form || !options.form.length) {
                  options.form = $(options.fileInput.prop('form'));
                  // If the given file input doesn't have an associated form,
                  // use the default widget file input's form:
                  if (!options.form.length) {
                      options.form = $(this.options.fileInput.prop('form'));
                  }
              }
              options.paramName = this._getParamName(options);
              if (!options.url) {
                  options.url = options.form.prop('action') || location.href;
              }
              // The HTTP request method must be "POST" or "PUT":
              options.type = (options.type ||
                  ($.type(options.form.prop('method')) === 'string' &&
                      options.form.prop('method')) || ''
                  ).toUpperCase();
              if (options.type !== 'POST' && options.type !== 'PUT' &&
                      options.type !== 'PATCH') {
                  options.type = 'POST';
              }
              if (!options.formAcceptCharset) {
                  options.formAcceptCharset = options.form.attr('accept-charset');
              }
          },
  
          _getAJAXSettings: function (data) {
              var options = $.extend({}, this.options, data);
              this._initFormSettings(options);
              this._initDataSettings(options);
              return options;
          },
  
          // jQuery 1.6 doesn't provide .state(),
          // while jQuery 1.8+ removed .isRejected() and .isResolved():
          _getDeferredState: function (deferred) {
              if (deferred.state) {
                  return deferred.state();
              }
              if (deferred.isResolved()) {
                  return 'resolved';
              }
              if (deferred.isRejected()) {
                  return 'rejected';
              }
              return 'pending';
          },
  
          // Maps jqXHR callbacks to the equivalent
          // methods of the given Promise object:
          _enhancePromise: function (promise) {
              promise.success = promise.done;
              promise.error = promise.fail;
              promise.complete = promise.always;
              return promise;
          },
  
          // Creates and returns a Promise object enhanced with
          // the jqXHR methods abort, success, error and complete:
          _getXHRPromise: function (resolveOrReject, context, args) {
              var dfd = $.Deferred(),
                  promise = dfd.promise();
              context = context || this.options.context || promise;
              if (resolveOrReject === true) {
                  dfd.resolveWith(context, args);
              } else if (resolveOrReject === false) {
                  dfd.rejectWith(context, args);
              }
              promise.abort = dfd.promise;
              return this._enhancePromise(promise);
          },
  
          // Adds convenience methods to the data callback argument:
          _addConvenienceMethods: function (e, data) {
              var that = this,
                  getPromise = function (args) {
                      return $.Deferred().resolveWith(that, args).promise();
                  };
              data.process = function (resolveFunc, rejectFunc) {
                  if (resolveFunc || rejectFunc) {
                      data._processQueue = this._processQueue =
                          (this._processQueue || getPromise([this])).pipe(
                              function () {
                                  if (data.errorThrown) {
                                      return $.Deferred()
                                          .rejectWith(that, [data]).promise();
                                  }
                                  return getPromise(arguments);
                              }
                          ).pipe(resolveFunc, rejectFunc);
                  }
                  return this._processQueue || getPromise([this]);
              };
              data.submit = function () {
                  if (this.state() !== 'pending') {
                      data.jqXHR = this.jqXHR =
                          (that._trigger(
                              'submit',
                              $.Event('submit', {delegatedEvent: e}),
                              this
                          ) !== false) && that._onSend(e, this);
                  }
                  return this.jqXHR || that._getXHRPromise();
              };
              data.abort = function () {
                  if (this.jqXHR) {
                      return this.jqXHR.abort();
                  }
                  this.errorThrown = 'abort';
                  that._trigger('fail', null, this);
                  return that._getXHRPromise(false);
              };
              data.state = function () {
                  if (this.jqXHR) {
                      return that._getDeferredState(this.jqXHR);
                  }
                  if (this._processQueue) {
                      return that._getDeferredState(this._processQueue);
                  }
              };
              data.processing = function () {
                  return !this.jqXHR && this._processQueue && that
                      ._getDeferredState(this._processQueue) === 'pending';
              };
              data.progress = function () {
                  return this._progress;
              };
              data.response = function () {
                  return this._response;
              };
          },
  
          // Parses the Range header from the server response
          // and returns the uploaded bytes:
          _getUploadedBytes: function (jqXHR) {
              var range = jqXHR.getResponseHeader('Range'),
                  parts = range && range.split('-'),
                  upperBytesPos = parts && parts.length > 1 &&
                      parseInt(parts[1], 10);
              return upperBytesPos && upperBytesPos + 1;
          },
  
          // Uploads a file in multiple, sequential requests
          // by splitting the file up in multiple blob chunks.
          // If the second parameter is true, only tests if the file
          // should be uploaded in chunks, but does not invoke any
          // upload requests:
          _chunkedUpload: function (options, testOnly) {
              options.uploadedBytes = options.uploadedBytes || 0;
              var that = this,
                  file = options.files[0],
                  fs = file.size,
                  ub = options.uploadedBytes,
                  mcs = options.maxChunkSize || fs,
                  slice = this._blobSlice,
                  dfd = $.Deferred(),
                  promise = dfd.promise(),
                  jqXHR,
                  upload;
              if (!(this._isXHRUpload(options) && slice && (ub || mcs < fs)) ||
                      options.data) {
                  return false;
              }
              if (testOnly) {
                  return true;
              }
              if (ub >= fs) {
                  file.error = options.i18n('uploadedBytes');
                  return this._getXHRPromise(
                      false,
                      options.context,
                      [null, 'error', file.error]
                  );
              }
              // The chunk upload method:
              upload = function () {
                  // Clone the options object for each chunk upload:
                  var o = $.extend({}, options),
                      currentLoaded = o._progress.loaded;
                  o.blob = slice.call(
                      file,
                      ub,
                      ub + mcs,
                      file.type
                  );
                  // Store the current chunk size, as the blob itself
                  // will be dereferenced after data processing:
                  o.chunkSize = o.blob.size;
                  // Expose the chunk bytes position range:
                  o.contentRange = 'bytes ' + ub + '-' +
                      (ub + o.chunkSize - 1) + '/' + fs;
                  // Process the upload data (the blob and potential form data):
                  that._initXHRData(o);
                  // Add progress listeners for this chunk upload:
                  that._initProgressListener(o);
                  jqXHR = ((that._trigger('chunksend', null, o) !== false && $.ajax(o)) ||
                          that._getXHRPromise(false, o.context))
                      .done(function (result, textStatus, jqXHR) {
                          ub = that._getUploadedBytes(jqXHR) ||
                              (ub + o.chunkSize);
                          // Create a progress event if no final progress event
                          // with loaded equaling total has been triggered
                          // for this chunk:
                          if (currentLoaded + o.chunkSize - o._progress.loaded) {
                              that._onProgress($.Event('progress', {
                                  lengthComputable: true,
                                  loaded: ub - o.uploadedBytes,
                                  total: ub - o.uploadedBytes
                              }), o);
                          }
                          options.uploadedBytes = o.uploadedBytes = ub;
                          o.result = result;
                          o.textStatus = textStatus;
                          o.jqXHR = jqXHR;
                          that._trigger('chunkdone', null, o);
                          that._trigger('chunkalways', null, o);
                          if (ub < fs) {
                              // File upload not yet complete,
                              // continue with the next chunk:
                              upload();
                          } else {
                              dfd.resolveWith(
                                  o.context,
                                  [result, textStatus, jqXHR]
                              );
                          }
                      })
                      .fail(function (jqXHR, textStatus, errorThrown) {
                          o.jqXHR = jqXHR;
                          o.textStatus = textStatus;
                          o.errorThrown = errorThrown;
                          that._trigger('chunkfail', null, o);
                          that._trigger('chunkalways', null, o);
                          dfd.rejectWith(
                              o.context,
                              [jqXHR, textStatus, errorThrown]
                          );
                      });
              };
              this._enhancePromise(promise);
              promise.abort = function () {
                  return jqXHR.abort();
              };
              upload();
              return promise;
          },
  
          _beforeSend: function (e, data) {
              if (this._active === 0) {
                  // the start callback is triggered when an upload starts
                  // and no other uploads are currently running,
                  // equivalent to the global ajaxStart event:
                  this._trigger('start');
                  // Set timer for global bitrate progress calculation:
                  this._bitrateTimer = new this._BitrateTimer();
                  // Reset the global progress values:
                  this._progress.loaded = this._progress.total = 0;
                  this._progress.bitrate = 0;
              }
              // Make sure the container objects for the .response() and
              // .progress() methods on the data object are available
              // and reset to their initial state:
              this._initResponseObject(data);
              this._initProgressObject(data);
              data._progress.loaded = data.loaded = data.uploadedBytes || 0;
              data._progress.total = data.total = this._getTotal(data.files) || 1;
              data._progress.bitrate = data.bitrate = 0;
              this._active += 1;
              // Initialize the global progress values:
              this._progress.loaded += data.loaded;
              this._progress.total += data.total;
          },
  
          _onDone: function (result, textStatus, jqXHR, options) {
              var total = options._progress.total,
                  response = options._response;
              if (options._progress.loaded < total) {
                  // Create a progress event if no final progress event
                  // with loaded equaling total has been triggered:
                  this._onProgress($.Event('progress', {
                      lengthComputable: true,
                      loaded: total,
                      total: total
                  }), options);
              }
              response.result = options.result = result;
              response.textStatus = options.textStatus = textStatus;
              response.jqXHR = options.jqXHR = jqXHR;
              this._trigger('done', null, options);
          },
  
          _onFail: function (jqXHR, textStatus, errorThrown, options) {
              var response = options._response;
              if (options.recalculateProgress) {
                  // Remove the failed (error or abort) file upload from
                  // the global progress calculation:
                  this._progress.loaded -= options._progress.loaded;
                  this._progress.total -= options._progress.total;
              }
              response.jqXHR = options.jqXHR = jqXHR;
              response.textStatus = options.textStatus = textStatus;
              response.errorThrown = options.errorThrown = errorThrown;
              this._trigger('fail', null, options);
          },
  
          _onAlways: function (jqXHRorResult, textStatus, jqXHRorError, options) {
              // jqXHRorResult, textStatus and jqXHRorError are added to the
              // options object via done and fail callbacks
              this._trigger('always', null, options);
          },
  
          _onSend: function (e, data) {
              if (!data.submit) {
                  this._addConvenienceMethods(e, data);
              }
              var that = this,
                  jqXHR,
                  aborted,
                  slot,
                  pipe,
                  options = that._getAJAXSettings(data),
                  send = function () {
                      that._sending += 1;
                      // Set timer for bitrate progress calculation:
                      options._bitrateTimer = new that._BitrateTimer();
                      jqXHR = jqXHR || (
                          ((aborted || that._trigger(
                              'send',
                              $.Event('send', {delegatedEvent: e}),
                              options
                          ) === false) &&
                          that._getXHRPromise(false, options.context, aborted)) ||
                          that._chunkedUpload(options) || $.ajax(options)
                      ).done(function (result, textStatus, jqXHR) {
                          that._onDone(result, textStatus, jqXHR, options);
                      }).fail(function (jqXHR, textStatus, errorThrown) {
                          that._onFail(jqXHR, textStatus, errorThrown, options);
                      }).always(function (jqXHRorResult, textStatus, jqXHRorError) {
                          that._onAlways(
                              jqXHRorResult,
                              textStatus,
                              jqXHRorError,
                              options
                          );
                          that._sending -= 1;
                          that._active -= 1;
                          if (options.limitConcurrentUploads &&
                                  options.limitConcurrentUploads > that._sending) {
                              // Start the next queued upload,
                              // that has not been aborted:
                              var nextSlot = that._slots.shift();
                              while (nextSlot) {
                                  if (that._getDeferredState(nextSlot) === 'pending') {
                                      nextSlot.resolve();
                                      break;
                                  }
                                  nextSlot = that._slots.shift();
                              }
                          }
                          if (that._active === 0) {
                              // The stop callback is triggered when all uploads have
                              // been completed, equivalent to the global ajaxStop event:
                              that._trigger('stop');
                          }
                      });
                      return jqXHR;
                  };
              this._beforeSend(e, options);
              if (this.options.sequentialUploads ||
                      (this.options.limitConcurrentUploads &&
                      this.options.limitConcurrentUploads <= this._sending)) {
                  if (this.options.limitConcurrentUploads > 1) {
                      slot = $.Deferred();
                      this._slots.push(slot);
                      pipe = slot.pipe(send);
                  } else {
                      this._sequence = this._sequence.pipe(send, send);
                      pipe = this._sequence;
                  }
                  // Return the piped Promise object, enhanced with an abort method,
                  // which is delegated to the jqXHR object of the current upload,
                  // and jqXHR callbacks mapped to the equivalent Promise methods:
                  pipe.abort = function () {
                      aborted = [undefined, 'abort', 'abort'];
                      if (!jqXHR) {
                          if (slot) {
                              slot.rejectWith(options.context, aborted);
                          }
                          return send();
                      }
                      return jqXHR.abort();
                  };
                  return this._enhancePromise(pipe);
              }
              return send();
          },
  
          _onAdd: function (e, data) {
              var that = this,
                  result = true,
                  options = $.extend({}, this.options, data),
                  files = data.files,
                  filesLength = files.length,
                  limit = options.limitMultiFileUploads,
                  limitSize = options.limitMultiFileUploadSize,
                  overhead = options.limitMultiFileUploadSizeOverhead,
                  batchSize = 0,
                  paramName = this._getParamName(options),
                  paramNameSet,
                  paramNameSlice,
                  fileSet,
                  i,
                  j = 0;
              if (limitSize && (!filesLength || files[0].size === undefined)) {
                  limitSize = undefined;
              }
              if (!(options.singleFileUploads || limit || limitSize) ||
                      !this._isXHRUpload(options)) {
                  fileSet = [files];
                  paramNameSet = [paramName];
              } else if (!(options.singleFileUploads || limitSize) && limit) {
                  fileSet = [];
                  paramNameSet = [];
                  for (i = 0; i < filesLength; i += limit) {
                      fileSet.push(files.slice(i, i + limit));
                      paramNameSlice = paramName.slice(i, i + limit);
                      if (!paramNameSlice.length) {
                          paramNameSlice = paramName;
                      }
                      paramNameSet.push(paramNameSlice);
                  }
              } else if (!options.singleFileUploads && limitSize) {
                  fileSet = [];
                  paramNameSet = [];
                  for (i = 0; i < filesLength; i = i + 1) {
                      batchSize += files[i].size + overhead;
                      if (i + 1 === filesLength ||
                              ((batchSize + files[i + 1].size + overhead) > limitSize) ||
                              (limit && i + 1 - j >= limit)) {
                          fileSet.push(files.slice(j, i + 1));
                          paramNameSlice = paramName.slice(j, i + 1);
                          if (!paramNameSlice.length) {
                              paramNameSlice = paramName;
                          }
                          paramNameSet.push(paramNameSlice);
                          j = i + 1;
                          batchSize = 0;
                      }
                  }
              } else {
                  paramNameSet = paramName;
              }
              data.originalFiles = files;
              $.each(fileSet || files, function (index, element) {
                  var newData = $.extend({}, data);
                  newData.files = fileSet ? element : [element];
                  newData.paramName = paramNameSet[index];
                  that._initResponseObject(newData);
                  that._initProgressObject(newData);
                  that._addConvenienceMethods(e, newData);
                  result = that._trigger(
                      'add',
                      $.Event('add', {delegatedEvent: e}),
                      newData
                  );
                  return result;
              });
              return result;
          },
  
          _replaceFileInput: function (input) {
              var inputClone = input.clone(true);
              $('<form></form>').append(inputClone)[0].reset();
              // Detaching allows to insert the fileInput on another form
              // without loosing the file input value:
              input.after(inputClone).detach();
              // Avoid memory leaks with the detached file input:
              $.cleanData(input.unbind('remove'));
              // Replace the original file input element in the fileInput
              // elements set with the clone, which has been copied including
              // event handlers:
              this.options.fileInput = this.options.fileInput.map(function (i, el) {
                  if (el === input[0]) {
                      return inputClone[0];
                  }
                  return el;
              });
              // If the widget has been initialized on the file input itself,
              // override this.element with the file input clone:
              if (input[0] === this.element[0]) {
                  this.element = inputClone;
              }
          },
  
          _handleFileTreeEntry: function (entry, path) {
              var that = this,
                  dfd = $.Deferred(),
                  errorHandler = function (e) {
                      if (e && !e.entry) {
                          e.entry = entry;
                      }
                      // Since $.when returns immediately if one
                      // Deferred is rejected, we use resolve instead.
                      // This allows valid files and invalid items
                      // to be returned together in one set:
                      dfd.resolve([e]);
                  },
                  dirReader;
              path = path || '';
              if (entry.isFile) {
                  if (entry._file) {
                      // Workaround for Chrome bug #149735
                      entry._file.relativePath = path;
                      dfd.resolve(entry._file);
                  } else {
                      entry.file(function (file) {
                          file.relativePath = path;
                          dfd.resolve(file);
                      }, errorHandler);
                  }
              } else if (entry.isDirectory) {
                  dirReader = entry.createReader();
                  dirReader.readEntries(function (entries) {
                      that._handleFileTreeEntries(
                          entries,
                          path + entry.name + '/'
                      ).done(function (files) {
                          dfd.resolve(files);
                      }).fail(errorHandler);
                  }, errorHandler);
              } else {
                  // Return an empy list for file system items
                  // other than files or directories:
                  dfd.resolve([]);
              }
              return dfd.promise();
          },
  
          _handleFileTreeEntries: function (entries, path) {
              var that = this;
              return $.when.apply(
                  $,
                  $.map(entries, function (entry) {
                      return that._handleFileTreeEntry(entry, path);
                  })
              ).pipe(function () {
                  return Array.prototype.concat.apply(
                      [],
                      arguments
                  );
              });
          },
  
          _getDroppedFiles: function (dataTransfer) {
              dataTransfer = dataTransfer || {};
              var items = dataTransfer.items;
              if (items && items.length && (items[0].webkitGetAsEntry ||
                      items[0].getAsEntry)) {
                  return this._handleFileTreeEntries(
                      $.map(items, function (item) {
                          var entry;
                          if (item.webkitGetAsEntry) {
                              entry = item.webkitGetAsEntry();
                              if (entry) {
                                  // Workaround for Chrome bug #149735:
                                  entry._file = item.getAsFile();
                              }
                              return entry;
                          }
                          return item.getAsEntry();
                      })
                  );
              }
              return $.Deferred().resolve(
                  $.makeArray(dataTransfer.files)
              ).promise();
          },
  
          _getSingleFileInputFiles: function (fileInput) {
              fileInput = $(fileInput);
              var entries = fileInput.prop('webkitEntries') ||
                      fileInput.prop('entries'),
                  files,
                  value;
              if (entries && entries.length) {
                  return this._handleFileTreeEntries(entries);
              }
              files = $.makeArray(fileInput.prop('files'));
              if (!files.length) {
                  value = fileInput.prop('value');
                  if (!value) {
                      return $.Deferred().resolve([]).promise();
                  }
                  // If the files property is not available, the browser does not
                  // support the File API and we add a pseudo File object with
                  // the input value as name with path information removed:
                  files = [{name: value.replace(/^.*\\/, '')}];
              } else if (files[0].name === undefined && files[0].fileName) {
                  // File normalization for Safari 4 and Firefox 3:
                  $.each(files, function (index, file) {
                      file.name = file.fileName;
                      file.size = file.fileSize;
                  });
              }
              return $.Deferred().resolve(files).promise();
          },
  
          _getFileInputFiles: function (fileInput) {
              if (!(fileInput instanceof $) || fileInput.length === 1) {
                  return this._getSingleFileInputFiles(fileInput);
              }
              return $.when.apply(
                  $,
                  $.map(fileInput, this._getSingleFileInputFiles)
              ).pipe(function () {
                  return Array.prototype.concat.apply(
                      [],
                      arguments
                  );
              });
          },
  
          _onChange: function (e) {
              var that = this,
                  data = {
                      fileInput: $(e.target),
                      form: $(e.target.form)
                  };
              this._getFileInputFiles(data.fileInput).always(function (files) {
                  data.files = files;
                  if (that.options.replaceFileInput) {
                      that._replaceFileInput(data.fileInput);
                  }
                  if (that._trigger(
                          'change',
                          $.Event('change', {delegatedEvent: e}),
                          data
                      ) !== false) {
                      that._onAdd(e, data);
                  }
              });
          },
  
          _onPaste: function (e) {
              var items = e.originalEvent && e.originalEvent.clipboardData &&
                      e.originalEvent.clipboardData.items,
                  data = {files: []};
              if (items && items.length) {
                  $.each(items, function (index, item) {
                      var file = item.getAsFile && item.getAsFile();
                      if (file) {
                          data.files.push(file);
                      }
                  });
                  if (this._trigger(
                          'paste',
                          $.Event('paste', {delegatedEvent: e}),
                          data
                      ) !== false) {
                      this._onAdd(e, data);
                  }
              }
          },
  
          _onDrop: function (e) {
              e.dataTransfer = e.originalEvent && e.originalEvent.dataTransfer;
              var that = this,
                  dataTransfer = e.dataTransfer,
                  data = {};
              if (dataTransfer && dataTransfer.files && dataTransfer.files.length) {
                  e.preventDefault();
                  this._getDroppedFiles(dataTransfer).always(function (files) {
                      data.files = files;
                      if (that._trigger(
                              'drop',
                              $.Event('drop', {delegatedEvent: e}),
                              data
                          ) !== false) {
                          that._onAdd(e, data);
                      }
                  });
              }
          },
  
          _onDragOver: function (e) {
              e.dataTransfer = e.originalEvent && e.originalEvent.dataTransfer;
              var dataTransfer = e.dataTransfer;
              if (dataTransfer && $.inArray('Files', dataTransfer.types) !== -1 &&
                      this._trigger(
                          'dragover',
                          $.Event('dragover', {delegatedEvent: e})
                      ) !== false) {
                  e.preventDefault();
                  dataTransfer.dropEffect = 'copy';
              }
          },
  
          _initEventHandlers: function () {
              if (this._isXHRUpload(this.options)) {
                  this._on(this.options.dropZone, {
                      dragover: this._onDragOver,
                      drop: this._onDrop
                  });
                  this._on(this.options.pasteZone, {
                      paste: this._onPaste
                  });
              }
              if ($.support.fileInput) {
                  this._on(this.options.fileInput, {
                      change: this._onChange
                  });
              }
          },
  
          _destroyEventHandlers: function () {
              this._off(this.options.dropZone, 'dragover drop');
              this._off(this.options.pasteZone, 'paste');
              this._off(this.options.fileInput, 'change');
          },
  
          _setOption: function (key, value) {
              var reinit = $.inArray(key, this._specialOptions) !== -1;
              if (reinit) {
                  this._destroyEventHandlers();
              }
              this._super(key, value);
              if (reinit) {
                  this._initSpecialOptions();
                  this._initEventHandlers();
              }
          },
  
          _initSpecialOptions: function () {
              var options = this.options;
              if (options.fileInput === undefined) {
                  options.fileInput = this.element.is('input[type="file"]') ?
                          this.element : this.element.find('input[type="file"]');
              } else if (!(options.fileInput instanceof $)) {
                  options.fileInput = $(options.fileInput);
              }
              if (!(options.dropZone instanceof $)) {
                  options.dropZone = $(options.dropZone);
              }
              if (!(options.pasteZone instanceof $)) {
                  options.pasteZone = $(options.pasteZone);
              }
          },
  
          _getRegExp: function (str) {
              var parts = str.split('/'),
                  modifiers = parts.pop();
              parts.shift();
              return new RegExp(parts.join('/'), modifiers);
          },
  
          _isRegExpOption: function (key, value) {
              return key !== 'url' && $.type(value) === 'string' &&
                  /^\/.*\/[igm]{0,3}$/.test(value);
          },
  
          _initDataAttributes: function () {
              var that = this,
                  options = this.options;
              // Initialize options set via HTML5 data-attributes:
              $.each(
                  $(this.element[0].cloneNode(false)).data(),
                  function (key, value) {
                      if (that._isRegExpOption(key, value)) {
                          value = that._getRegExp(value);
                      }
                      options[key] = value;
                  }
              );
          },
  
          _create: function () {
              this._initDataAttributes();
              this._initSpecialOptions();
              this._slots = [];
              this._sequence = this._getXHRPromise(true);
              this._sending = this._active = 0;
              this._initProgressObject(this);
              this._initEventHandlers();
          },
  
          // This method is exposed to the widget API and allows to query
          // the number of active uploads:
          active: function () {
              return this._active;
          },
  
          // This method is exposed to the widget API and allows to query
          // the widget upload progress.
          // It returns an object with loaded, total and bitrate properties
          // for the running uploads:
          progress: function () {
              return this._progress;
          },
  
          // This method is exposed to the widget API and allows adding files
          // using the fileupload API. The data parameter accepts an object which
          // must have a files property and can contain additional options:
          // .fileupload('add', {files: filesList});
          add: function (data) {
              var that = this;
              if (!data || this.options.disabled) {
                  return;
              }
              if (data.fileInput && !data.files) {
                  this._getFileInputFiles(data.fileInput).always(function (files) {
                      data.files = files;
                      that._onAdd(null, data);
                  });
              } else {
                  data.files = $.makeArray(data.files);
                  this._onAdd(null, data);
              }
          },
  
          // This method is exposed to the widget API and allows sending files
          // using the fileupload API. The data parameter accepts an object which
          // must have a files or fileInput property and can contain additional options:
          // .fileupload('send', {files: filesList});
          // The method returns a Promise object for the file upload call.
          send: function (data) {
              if (data && !this.options.disabled) {
                  if (data.fileInput && !data.files) {
                      var that = this,
                          dfd = $.Deferred(),
                          promise = dfd.promise(),
                          jqXHR,
                          aborted;
                      promise.abort = function () {
                          aborted = true;
                          if (jqXHR) {
                              return jqXHR.abort();
                          }
                          dfd.reject(null, 'abort', 'abort');
                          return promise;
                      };
                      this._getFileInputFiles(data.fileInput).always(
                          function (files) {
                              if (aborted) {
                                  return;
                              }
                              if (!files.length) {
                                  dfd.reject();
                                  return;
                              }
                              data.files = files;
                              jqXHR = that._onSend(null, data).then(
                                  function (result, textStatus, jqXHR) {
                                      dfd.resolve(result, textStatus, jqXHR);
                                  },
                                  function (jqXHR, textStatus, errorThrown) {
                                      dfd.reject(jqXHR, textStatus, errorThrown);
                                  }
                              );
                          }
                      );
                      return this._enhancePromise(promise);
                  }
                  data.files = $.makeArray(data.files);
                  if (data.files.length) {
                      return this._onSend(null, data);
                  }
              }
              return this._getXHRPromise(false, data && data.context);
          }
  
      });
  
  }));
  

});


;/*!/modules/ignore/utils.js*/
define('modules/ignore/utils', function(require, exports, module) {

  require('modules/ignore/fileupload/jquery.ui.widget');
  require('modules/ignore/fileupload/jquery.iframe-transport');
  require('modules/ignore/fileupload/jquery.fileupload');
  module.exports = {
      //JQ-fileupload插件上传文件
        fileupload: function(fileImgBox) {
            var fileBtn = fileImgBox.find('.fileupload-file');
            var parent = $(fileBtn).parent();
            var progressBox = fileImgBox.children('.progress');
            var fileProgress = fileImgBox.children('.progress').children('.progress-bar');
            var fileHidden = fileImgBox.find('.files-hidden');
            var valArr = [];
            var fileBase64 = [];
            var url = fileBtn.attr('data-url');
            var fileId;
    
    
            fileBtn.change(function() {
                fileBase64.length = 0;
                for (var i = 0, l = this.files.length; i < l; i++) {
                    reader = new FileReader();
                    reader.readAsDataURL(this.files[i]);
                    reader.onload = function(e) {
                        // box.prepend('<img src='+ this.result +'>');  
                        fileBase64.push(this.result)
                    }
                }
    
            })
  
            var closeBtn = fileImgBox.find('.file-img-box a');
            closeBtn.unbind('click').one('click', function(e) {
                valArr.splice(valArr.indexOf($(this).prev('img').attr('data-id')), 1);
                fileHidden.val(valArr.join(','));
                if (fileImgBox.find('.file-img-box').length == 1) {
                    parent.show();
                }
                $(this).parent().remove();
            
            })
            // console.log(fileHidden.val().split(','));
            // t
            
            try{
              if(fileHidden.val() != 0){
                valArr=valArr.concat(fileHidden.val().split(','))
              }
          }catch(e) {
  
            }
  
  
           console.log(valArr);
  
  
            fileBtn.fileupload({
                    url: url,
                    dataType: 'json',
                    multipart: true,
                    done: function(e, data) {
                        $.each(data.result.files, function(index, file) {
    
                        });
    
                    },
                    beforeSend: function() {
                        fileProgress.css(
                            'width',
                            0 + '%'
                        );
                        progressBox.css('visibility', 'visible');
                        if (!fileBtn.attr('multiple')) {
                            parent.hide();
                        }
                    },
                    success: function(data) {
                        if (data.Status != 1) {
                            alert(data.info);
                            parent.show();
                            return;
                        }
                        valArr.push(data.Data.file_id);
                        fileHidden.val(valArr.join(','));
                        fileId = data.Data.file_id;
                        $.each(fileBase64, function(index, item) {
                            fileImgBox.prepend('<span class="file-img-box"><img src = "' + item + '" data-id = "' + fileId + '"/><a href="javascript:void(0);">×</a></span>');
                            var closeBtn = fileImgBox.find('.file-img-box a');
                            closeBtn.unbind('click').one('click', function(e) {
                                valArr.splice(valArr.indexOf($(this).prev('img').attr('data-id')), 1);
                                fileHidden.val(valArr.join(','));
                                if (fileImgBox.find('.file-img-box').length == 1) {
                                    parent.show();
                                }
                                $(this).parent().remove();
    
    
                            })
                        })
                        fileBase64.length = 0;
                    },
                    progressall: function(e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        fileProgress.animate({
                                'width': progress + '%'
                            })
                            // console.log(progress);
                        if (progress == 100) {
                            setTimeout(function() {
                                progressBox.css('visibility', 'hidden');
                            }, 1000);
                        }
                    }
                }).prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');
        },
      pageTab: function(tab_btn, tab_box) { 
          tab_btn.click(function() {
              var _this = this;
              tab_btn.each(function(index) {
                  if (tab_btn.get(index) == _this) {
                      tab_box.eq(index).fadeIn(300);
                      tab_btn.eq(index).addClass('on')
                  } else {
                      tab_box.eq(index).hide();
                      tab_btn.eq(index).removeClass('on')
                  }
              })
          })
      }
  }

});

;/*!/modules/ignore/SuperSlide/jquery.SuperSlide.2.1.1.js*/
define('modules/ignore/SuperSlide/jquery.SuperSlide.2.1.1', function(require, exports, module) {

  /*!
   * SuperSlide v2.1.1 
   * 轻松解决网站大部分特效展示问题
   * 详尽信息请看官网：http://www.SuperSlide2.com/
   *
   * Copyright 2011-2013, 大话主席
   *
   * 请尊重原创，保留头部版权
   * 在保留版权的前提下可应用于个人或商业用途
  
   * v2.1.1：修复当调用多个SuperSlide，并设置returnDefault:true 时返回defaultIndex索引错误
  
   */
  
  !function(a){a.fn.slide=function(b){return a.fn.slide.defaults={type:"slide",effect:"fade",autoPlay:!1,delayTime:500,interTime:2500,triggerTime:150,defaultIndex:0,titCell:".hd li",mainCell:".bd",targetCell:null,trigger:"mouseover",scroll:1,vis:1,titOnClassName:"on",autoPage:!1,prevCell:".prev",nextCell:".next",pageStateCell:".pageState",opp:!1,pnLoop:!0,easing:"swing",startFun:null,endFun:null,switchLoad:null,playStateCell:".playState",mouseOverStop:!0,defaultPlay:!0,returnDefault:!1},this.each(function(){var c=a.extend({},a.fn.slide.defaults,b),d=a(this),e=c.effect,f=a(c.prevCell,d),g=a(c.nextCell,d),h=a(c.pageStateCell,d),i=a(c.playStateCell,d),j=a(c.titCell,d),k=j.size(),l=a(c.mainCell,d),m=l.children().size(),n=c.switchLoad,o=a(c.targetCell,d),p=parseInt(c.defaultIndex),q=parseInt(c.delayTime),r=parseInt(c.interTime);parseInt(c.triggerTime);var Q,t=parseInt(c.scroll),u=parseInt(c.vis),v="false"==c.autoPlay||0==c.autoPlay?!1:!0,w="false"==c.opp||0==c.opp?!1:!0,x="false"==c.autoPage||0==c.autoPage?!1:!0,y="false"==c.pnLoop||0==c.pnLoop?!1:!0,z="false"==c.mouseOverStop||0==c.mouseOverStop?!1:!0,A="false"==c.defaultPlay||0==c.defaultPlay?!1:!0,B="false"==c.returnDefault||0==c.returnDefault?!1:!0,C=0,D=0,E=0,F=0,G=c.easing,H=null,I=null,J=null,K=c.titOnClassName,L=j.index(d.find("."+K)),M=p=-1==L?p:L,N=p,O=p,P=m>=u?0!=m%t?m%t:t:0,R="leftMarquee"==e||"topMarquee"==e?!0:!1,S=function(){a.isFunction(c.startFun)&&c.startFun(p,k,d,a(c.titCell,d),l,o,f,g)},T=function(){a.isFunction(c.endFun)&&c.endFun(p,k,d,a(c.titCell,d),l,o,f,g)},U=function(){j.removeClass(K),A&&j.eq(N).addClass(K)};if("menu"==c.type)return A&&j.removeClass(K).eq(p).addClass(K),j.hover(function(){Q=a(this).find(c.targetCell);var b=j.index(a(this));I=setTimeout(function(){switch(p=b,j.removeClass(K).eq(p).addClass(K),S(),e){case"fade":Q.stop(!0,!0).animate({opacity:"show"},q,G,T);break;case"slideDown":Q.stop(!0,!0).animate({height:"show"},q,G,T)}},c.triggerTime)},function(){switch(clearTimeout(I),e){case"fade":Q.animate({opacity:"hide"},q,G);break;case"slideDown":Q.animate({height:"hide"},q,G)}}),B&&d.hover(function(){clearTimeout(J)},function(){J=setTimeout(U,q)}),void 0;if(0==k&&(k=m),R&&(k=2),x){if(m>=u)if("leftLoop"==e||"topLoop"==e)k=0!=m%t?(0^m/t)+1:m/t;else{var V=m-u;k=1+parseInt(0!=V%t?V/t+1:V/t),0>=k&&(k=1)}else k=1;j.html("");var W="";if(1==c.autoPage||"true"==c.autoPage)for(var X=0;k>X;X++)W+="<li>"+(X+1)+"</li>";else for(var X=0;k>X;X++)W+=c.autoPage.replace("$",X+1);j.html(W);var j=j.children()}if(m>=u){l.children().each(function(){a(this).width()>E&&(E=a(this).width(),D=a(this).outerWidth(!0)),a(this).height()>F&&(F=a(this).height(),C=a(this).outerHeight(!0))});var Y=l.children(),Z=function(){for(var a=0;u>a;a++)Y.eq(a).clone().addClass("clone").appendTo(l);for(var a=0;P>a;a++)Y.eq(m-a-1).clone().addClass("clone").prependTo(l)};switch(e){case"fold":l.css({position:"relative",width:D,height:C}).children().css({position:"absolute",width:E,left:0,top:0,display:"none"});break;case"top":l.wrap('<div class="tempWrap" style="overflow:hidden; position:relative; height:'+u*C+'px"></div>').css({top:-(p*t)*C,position:"relative",padding:"0",margin:"0"}).children().css({height:F});break;case"left":l.wrap('<div class="tempWrap" style="overflow:hidden; position:relative; width:'+u*D+'px"></div>').css({width:m*D,left:-(p*t)*D,position:"relative",overflow:"hidden",padding:"0",margin:"0"}).children().css({"float":"left",width:E});break;case"leftLoop":case"leftMarquee":Z(),l.wrap('<div class="tempWrap" style="overflow:hidden; position:relative; width:'+u*D+'px"></div>').css({width:(m+u+P)*D,position:"relative",overflow:"hidden",padding:"0",margin:"0",left:-(P+p*t)*D}).children().css({"float":"left",width:E});break;case"topLoop":case"topMarquee":Z(),l.wrap('<div class="tempWrap" style="overflow:hidden; position:relative; height:'+u*C+'px"></div>').css({height:(m+u+P)*C,position:"relative",padding:"0",margin:"0",top:-(P+p*t)*C}).children().css({height:F})}}var $=function(a){var b=a*t;return a==k?b=m:-1==a&&0!=m%t&&(b=-m%t),b},_=function(b){var c=function(c){for(var d=c;u+c>d;d++)b.eq(d).find("img["+n+"]").each(function(){var b=a(this);if(b.attr("src",b.attr(n)).removeAttr(n),l.find(".clone")[0])for(var c=l.children(),d=0;d<c.size();d++)c.eq(d).find("img["+n+"]").each(function(){a(this).attr(n)==b.attr("src")&&a(this).attr("src",a(this).attr(n)).removeAttr(n)})})};switch(e){case"fade":case"fold":case"top":case"left":case"slideDown":c(p*t);break;case"leftLoop":case"topLoop":c(P+$(O));break;case"leftMarquee":case"topMarquee":var d="leftMarquee"==e?l.css("left").replace("px",""):l.css("top").replace("px",""),f="leftMarquee"==e?D:C,g=P;if(0!=d%f){var h=Math.abs(0^d/f);g=1==p?P+h:P+h-1}c(g)}},ab=function(a){if(!A||M!=p||a||R){if(R?p>=1?p=1:0>=p&&(p=0):(O=p,p>=k?p=0:0>p&&(p=k-1)),S(),null!=n&&_(l.children()),o[0]&&(Q=o.eq(p),null!=n&&_(o),"slideDown"==e?(o.not(Q).stop(!0,!0).slideUp(q),Q.slideDown(q,G,function(){l[0]||T()})):(o.not(Q).stop(!0,!0).hide(),Q.animate({opacity:"show"},q,function(){l[0]||T()}))),m>=u)switch(e){case"fade":l.children().stop(!0,!0).eq(p).animate({opacity:"show"},q,G,function(){T()}).siblings().hide();break;case"fold":l.children().stop(!0,!0).eq(p).animate({opacity:"show"},q,G,function(){T()}).siblings().animate({opacity:"hide"},q,G);break;case"top":l.stop(!0,!1).animate({top:-p*t*C},q,G,function(){T()});break;case"left":l.stop(!0,!1).animate({left:-p*t*D},q,G,function(){T()});break;case"leftLoop":var b=O;l.stop(!0,!0).animate({left:-($(O)+P)*D},q,G,function(){-1>=b?l.css("left",-(P+(k-1)*t)*D):b>=k&&l.css("left",-P*D),T()});break;case"topLoop":var b=O;l.stop(!0,!0).animate({top:-($(O)+P)*C},q,G,function(){-1>=b?l.css("top",-(P+(k-1)*t)*C):b>=k&&l.css("top",-P*C),T()});break;case"leftMarquee":var c=l.css("left").replace("px","");0==p?l.animate({left:++c},0,function(){l.css("left").replace("px","")>=0&&l.css("left",-m*D)}):l.animate({left:--c},0,function(){l.css("left").replace("px","")<=-(m+P)*D&&l.css("left",-P*D)});break;case"topMarquee":var d=l.css("top").replace("px","");0==p?l.animate({top:++d},0,function(){l.css("top").replace("px","")>=0&&l.css("top",-m*C)}):l.animate({top:--d},0,function(){l.css("top").replace("px","")<=-(m+P)*C&&l.css("top",-P*C)})}j.removeClass(K).eq(p).addClass(K),M=p,y||(g.removeClass("nextStop"),f.removeClass("prevStop"),0==p&&f.addClass("prevStop"),p==k-1&&g.addClass("nextStop")),h.html("<span>"+(p+1)+"</span>/"+k)}};A&&ab(!0),B&&d.hover(function(){clearTimeout(J)},function(){J=setTimeout(function(){p=N,A?ab():"slideDown"==e?Q.slideUp(q,U):Q.animate({opacity:"hide"},q,U),M=p},300)});var bb=function(a){H=setInterval(function(){w?p--:p++,ab()},a?a:r)},cb=function(a){H=setInterval(ab,a?a:r)},db=function(){z||(clearInterval(H),bb())},eb=function(){(y||p!=k-1)&&(p++,ab(),R||db())},fb=function(){(y||0!=p)&&(p--,ab(),R||db())},gb=function(){clearInterval(H),R?cb():bb(),i.removeClass("pauseState")},hb=function(){clearInterval(H),i.addClass("pauseState")};if(v?R?(w?p--:p++,cb(),z&&l.hover(hb,gb)):(bb(),z&&d.hover(hb,gb)):(R&&(w?p--:p++),i.addClass("pauseState")),i.click(function(){i.hasClass("pauseState")?gb():hb()}),"mouseover"==c.trigger?j.hover(function(){var a=j.index(this);I=setTimeout(function(){p=a,ab(),db()},c.triggerTime)},function(){clearTimeout(I)}):j.click(function(){p=j.index(this),ab(),db()}),R){if(g.mousedown(eb),f.mousedown(fb),y){var ib,jb=function(){ib=setTimeout(function(){clearInterval(H),cb(0^r/10)},150)},kb=function(){clearTimeout(ib),clearInterval(H),cb()};g.mousedown(jb),g.mouseup(kb),f.mousedown(jb),f.mouseup(kb)}"mouseover"==c.trigger&&(g.hover(eb,function(){}),f.hover(fb,function(){}))}else g.click(eb),f.click(fb)})}}(jQuery),jQuery.easing.jswing=jQuery.easing.swing,jQuery.extend(jQuery.easing,{def:"easeOutQuad",swing:function(a,b,c,d,e){return jQuery.easing[jQuery.easing.def](a,b,c,d,e)},easeInQuad:function(a,b,c,d,e){return d*(b/=e)*b+c},easeOutQuad:function(a,b,c,d,e){return-d*(b/=e)*(b-2)+c},easeInOutQuad:function(a,b,c,d,e){return(b/=e/2)<1?d/2*b*b+c:-d/2*(--b*(b-2)-1)+c},easeInCubic:function(a,b,c,d,e){return d*(b/=e)*b*b+c},easeOutCubic:function(a,b,c,d,e){return d*((b=b/e-1)*b*b+1)+c},easeInOutCubic:function(a,b,c,d,e){return(b/=e/2)<1?d/2*b*b*b+c:d/2*((b-=2)*b*b+2)+c},easeInQuart:function(a,b,c,d,e){return d*(b/=e)*b*b*b+c},easeOutQuart:function(a,b,c,d,e){return-d*((b=b/e-1)*b*b*b-1)+c},easeInOutQuart:function(a,b,c,d,e){return(b/=e/2)<1?d/2*b*b*b*b+c:-d/2*((b-=2)*b*b*b-2)+c},easeInQuint:function(a,b,c,d,e){return d*(b/=e)*b*b*b*b+c},easeOutQuint:function(a,b,c,d,e){return d*((b=b/e-1)*b*b*b*b+1)+c},easeInOutQuint:function(a,b,c,d,e){return(b/=e/2)<1?d/2*b*b*b*b*b+c:d/2*((b-=2)*b*b*b*b+2)+c},easeInSine:function(a,b,c,d,e){return-d*Math.cos(b/e*(Math.PI/2))+d+c},easeOutSine:function(a,b,c,d,e){return d*Math.sin(b/e*(Math.PI/2))+c},easeInOutSine:function(a,b,c,d,e){return-d/2*(Math.cos(Math.PI*b/e)-1)+c},easeInExpo:function(a,b,c,d,e){return 0==b?c:d*Math.pow(2,10*(b/e-1))+c},easeOutExpo:function(a,b,c,d,e){return b==e?c+d:d*(-Math.pow(2,-10*b/e)+1)+c},easeInOutExpo:function(a,b,c,d,e){return 0==b?c:b==e?c+d:(b/=e/2)<1?d/2*Math.pow(2,10*(b-1))+c:d/2*(-Math.pow(2,-10*--b)+2)+c},easeInCirc:function(a,b,c,d,e){return-d*(Math.sqrt(1-(b/=e)*b)-1)+c},easeOutCirc:function(a,b,c,d,e){return d*Math.sqrt(1-(b=b/e-1)*b)+c},easeInOutCirc:function(a,b,c,d,e){return(b/=e/2)<1?-d/2*(Math.sqrt(1-b*b)-1)+c:d/2*(Math.sqrt(1-(b-=2)*b)+1)+c},easeInElastic:function(a,b,c,d,e){var f=1.70158,g=0,h=d;if(0==b)return c;if(1==(b/=e))return c+d;if(g||(g=.3*e),h<Math.abs(d)){h=d;var f=g/4}else var f=g/(2*Math.PI)*Math.asin(d/h);return-(h*Math.pow(2,10*(b-=1))*Math.sin((b*e-f)*2*Math.PI/g))+c},easeOutElastic:function(a,b,c,d,e){var f=1.70158,g=0,h=d;if(0==b)return c;if(1==(b/=e))return c+d;if(g||(g=.3*e),h<Math.abs(d)){h=d;var f=g/4}else var f=g/(2*Math.PI)*Math.asin(d/h);return h*Math.pow(2,-10*b)*Math.sin((b*e-f)*2*Math.PI/g)+d+c},easeInOutElastic:function(a,b,c,d,e){var f=1.70158,g=0,h=d;if(0==b)return c;if(2==(b/=e/2))return c+d;if(g||(g=e*.3*1.5),h<Math.abs(d)){h=d;var f=g/4}else var f=g/(2*Math.PI)*Math.asin(d/h);return 1>b?-.5*h*Math.pow(2,10*(b-=1))*Math.sin((b*e-f)*2*Math.PI/g)+c:.5*h*Math.pow(2,-10*(b-=1))*Math.sin((b*e-f)*2*Math.PI/g)+d+c},easeInBack:function(a,b,c,d,e,f){return void 0==f&&(f=1.70158),d*(b/=e)*b*((f+1)*b-f)+c},easeOutBack:function(a,b,c,d,e,f){return void 0==f&&(f=1.70158),d*((b=b/e-1)*b*((f+1)*b+f)+1)+c},easeInOutBack:function(a,b,c,d,e,f){return void 0==f&&(f=1.70158),(b/=e/2)<1?d/2*b*b*(((f*=1.525)+1)*b-f)+c:d/2*((b-=2)*b*(((f*=1.525)+1)*b+f)+2)+c},easeInBounce:function(a,b,c,d,e){return d-jQuery.easing.easeOutBounce(a,e-b,0,d,e)+c},easeOutBounce:function(a,b,c,d,e){return(b/=e)<1/2.75?d*7.5625*b*b+c:2/2.75>b?d*(7.5625*(b-=1.5/2.75)*b+.75)+c:2.5/2.75>b?d*(7.5625*(b-=2.25/2.75)*b+.9375)+c:d*(7.5625*(b-=2.625/2.75)*b+.984375)+c},easeInOutBounce:function(a,b,c,d,e){return e/2>b?.5*jQuery.easing.easeInBounce(a,2*b,0,d,e)+c:.5*jQuery.easing.easeOutBounce(a,2*b-e,0,d,e)+.5*d+c}});

});

;/*!/modules/ignore/int/countUp.min.js*/
define('modules/ignore/int/countUp.min', function(require, exports, module) {

  !function(a,b){"function"==typeof define&&define.amd?define(b):"object"==typeof exports?module.exports=b(require,exports,module):a.CountUp=b()}(this,function(){var d=function(a,b,c,d,e,f){for(var g=0,h=["webkit","moz","ms","o"],i=0;i<h.length&&!window.requestAnimationFrame;++i)window.requestAnimationFrame=window[h[i]+"RequestAnimationFrame"],window.cancelAnimationFrame=window[h[i]+"CancelAnimationFrame"]||window[h[i]+"CancelRequestAnimationFrame"];window.requestAnimationFrame||(window.requestAnimationFrame=function(a){var c=(new Date).getTime(),d=Math.max(0,16-(c-g)),e=window.setTimeout(function(){a(c+d)},d);return g=c+d,e}),window.cancelAnimationFrame||(window.cancelAnimationFrame=function(a){clearTimeout(a)}),this.options={useEasing:!0,useGrouping:!0,separator:",",decimal:"."};for(var j in f)f.hasOwnProperty(j)&&(this.options[j]=f[j]);""===this.options.separator&&(this.options.useGrouping=!1),this.options.prefix||(this.options.prefix=""),this.options.suffix||(this.options.suffix=""),this.d="string"==typeof a?document.getElementById(a):a,this.startVal=Number(b),isNaN(b)&&(this.startVal=Number(b.match(/[\d]+/g).join(""))),this.endVal=Number(c),isNaN(c)&&(this.endVal=Number(c.match(/[\d]+/g).join(""))),this.countDown=this.startVal>this.endVal,this.frameVal=this.startVal,this.decimals=Math.max(0,d||0),this.dec=Math.pow(10,this.decimals),this.duration=1e3*Number(e)||2e3;var k=this;this.version=function(){return"1.5.3"},this.printValue=function(a){var b=isNaN(a)?"--":k.formatNumber(a);"INPUT"==k.d.tagName?this.d.value=b:"text"==k.d.tagName?this.d.textContent=b:this.d.innerHTML=b},this.easeOutExpo=function(a,b,c,d){return 1024*c*(-Math.pow(2,-10*a/d)+1)/1023+b},this.count=function(a){k.startTime||(k.startTime=a),k.timestamp=a;var b=a-k.startTime;k.remaining=k.duration-b,k.frameVal=k.options.useEasing?k.countDown?k.startVal-k.easeOutExpo(b,0,k.startVal-k.endVal,k.duration):k.easeOutExpo(b,k.startVal,k.endVal-k.startVal,k.duration):k.countDown?k.startVal-(k.startVal-k.endVal)*(b/k.duration):k.startVal+(k.endVal-k.startVal)*(b/k.duration),k.frameVal=k.countDown?k.frameVal<k.endVal?k.endVal:k.frameVal:k.frameVal>k.endVal?k.endVal:k.frameVal,k.frameVal=Math.round(k.frameVal*k.dec)/k.dec,k.printValue(k.frameVal),b<k.duration?k.rAF=requestAnimationFrame(k.count):k.callback&&k.callback()},this.start=function(a){return k.callback=a,isNaN(k.endVal)||isNaN(k.startVal)||k.startVal===k.endVal?(console.log("countUp error: startVal or endVal is not a number"),k.printValue(c)):k.rAF=requestAnimationFrame(k.count),!1},this.pauseResume=function(){k.paused?(k.paused=!1,delete k.startTime,k.duration=k.remaining,k.startVal=k.frameVal,requestAnimationFrame(k.count)):(k.paused=!0,cancelAnimationFrame(k.rAF))},this.reset=function(){k.paused=!1,delete k.startTime,k.startVal=b,cancelAnimationFrame(k.rAF),k.printValue(k.startVal)},this.update=function(a){cancelAnimationFrame(k.rAF),k.paused=!1,delete k.startTime,k.startVal=k.frameVal,k.endVal=Number(a),k.countDown=k.startVal>k.endVal,k.rAF=requestAnimationFrame(k.count)},this.formatNumber=function(a){a=a.toFixed(k.decimals),a+="";var b,c,d,e;if(b=a.split("."),c=b[0],d=b.length>1?k.options.decimal+b[1]:"",e=/(\d+)(\d{3})/,k.options.useGrouping)for(;e.test(c);)c=c.replace(e,"$1"+k.options.separator+"$2");return k.options.prefix+c+d+k.options.suffix},k.printValue(k.startVal)};return d});

});

;/*!/modules/ignore/magnific/jquery.magnific-popup.js*/
define('modules/ignore/magnific/jquery.magnific-popup', function(require, exports, module) {

  /*! Magnific Popup - v0.9.9 - 2013-12-27
  * http://dimsemenov.com/plugins/magnific-popup/
  * Copyright (c) 2013 Dmitry Semenov; */
  (function(e){var t,n,i,o,r,a,s,l="Close",c="BeforeClose",d="AfterClose",u="BeforeAppend",p="MarkupParse",f="Open",m="Change",g="mfp",h="."+g,v="mfp-ready",C="mfp-removing",y="mfp-prevent-close",w=function(){},b=!!window.jQuery,I=e(window),x=function(e,n){t.ev.on(g+e+h,n)},k=function(t,n,i,o){var r=document.createElement("div");return r.className="mfp-"+t,i&&(r.innerHTML=i),o?n&&n.appendChild(r):(r=e(r),n&&r.appendTo(n)),r},T=function(n,i){t.ev.triggerHandler(g+n,i),t.st.callbacks&&(n=n.charAt(0).toLowerCase()+n.slice(1),t.st.callbacks[n]&&t.st.callbacks[n].apply(t,e.isArray(i)?i:[i]))},E=function(n){return n===s&&t.currTemplate.closeBtn||(t.currTemplate.closeBtn=e(t.st.closeMarkup.replace("%title%",t.st.tClose)),s=n),t.currTemplate.closeBtn},_=function(){e.magnificPopup.instance||(t=new w,t.init(),e.magnificPopup.instance=t)},S=function(){var e=document.createElement("p").style,t=["ms","O","Moz","Webkit"];if(void 0!==e.transition)return!0;for(;t.length;)if(t.pop()+"Transition"in e)return!0;return!1};w.prototype={constructor:w,init:function(){var n=navigator.appVersion;t.isIE7=-1!==n.indexOf("MSIE 7."),t.isIE8=-1!==n.indexOf("MSIE 8."),t.isLowIE=t.isIE7||t.isIE8,t.isAndroid=/android/gi.test(n),t.isIOS=/iphone|ipad|ipod/gi.test(n),t.supportsTransition=S(),t.probablyMobile=t.isAndroid||t.isIOS||/(Opera Mini)|Kindle|webOS|BlackBerry|(Opera Mobi)|(Windows Phone)|IEMobile/i.test(navigator.userAgent),o=e(document),t.popupsCache={}},open:function(n){i||(i=e(document.body));var r;if(n.isObj===!1){t.items=n.items.toArray(),t.index=0;var s,l=n.items;for(r=0;l.length>r;r++)if(s=l[r],s.parsed&&(s=s.el[0]),s===n.el[0]){t.index=r;break}}else t.items=e.isArray(n.items)?n.items:[n.items],t.index=n.index||0;if(t.isOpen)return t.updateItemHTML(),void 0;t.types=[],a="",t.ev=n.mainEl&&n.mainEl.length?n.mainEl.eq(0):o,n.key?(t.popupsCache[n.key]||(t.popupsCache[n.key]={}),t.currTemplate=t.popupsCache[n.key]):t.currTemplate={},t.st=e.extend(!0,{},e.magnificPopup.defaults,n),t.fixedContentPos="auto"===t.st.fixedContentPos?!t.probablyMobile:t.st.fixedContentPos,t.st.modal&&(t.st.closeOnContentClick=!1,t.st.closeOnBgClick=!1,t.st.showCloseBtn=!1,t.st.enableEscapeKey=!1),t.bgOverlay||(t.bgOverlay=k("bg").on("click"+h,function(){t.close()}),t.wrap=k("wrap").attr("tabindex",-1).on("click"+h,function(e){t._checkIfClose(e.target)&&t.close()}),t.container=k("container",t.wrap)),t.contentContainer=k("content"),t.st.preloader&&(t.preloader=k("preloader",t.container,t.st.tLoading));var c=e.magnificPopup.modules;for(r=0;c.length>r;r++){var d=c[r];d=d.charAt(0).toUpperCase()+d.slice(1),t["init"+d].call(t)}T("BeforeOpen"),t.st.showCloseBtn&&(t.st.closeBtnInside?(x(p,function(e,t,n,i){n.close_replaceWith=E(i.type)}),a+=" mfp-close-btn-in"):t.wrap.append(E())),t.st.alignTop&&(a+=" mfp-align-top"),t.fixedContentPos?t.wrap.css({overflow:t.st.overflowY,overflowX:"hidden",overflowY:t.st.overflowY}):t.wrap.css({top:I.scrollTop(),position:"absolute"}),(t.st.fixedBgPos===!1||"auto"===t.st.fixedBgPos&&!t.fixedContentPos)&&t.bgOverlay.css({height:o.height(),position:"absolute"}),t.st.enableEscapeKey&&o.on("keyup"+h,function(e){27===e.keyCode&&t.close()}),I.on("resize"+h,function(){t.updateSize()}),t.st.closeOnContentClick||(a+=" mfp-auto-cursor"),a&&t.wrap.addClass(a);var u=t.wH=I.height(),m={};if(t.fixedContentPos&&t._hasScrollBar(u)){var g=t._getScrollbarSize();g&&(m.marginRight=g)}t.fixedContentPos&&(t.isIE7?e("body, html").css("overflow","hidden"):m.overflow="hidden");var C=t.st.mainClass;return t.isIE7&&(C+=" mfp-ie7"),C&&t._addClassToMFP(C),t.updateItemHTML(),T("BuildControls"),e("html").css(m),t.bgOverlay.add(t.wrap).prependTo(t.st.prependTo||i),t._lastFocusedEl=document.activeElement,setTimeout(function(){t.content?(t._addClassToMFP(v),t._setFocus()):t.bgOverlay.addClass(v),o.on("focusin"+h,t._onFocusIn)},16),t.isOpen=!0,t.updateSize(u),T(f),n},close:function(){t.isOpen&&(T(c),t.isOpen=!1,t.st.removalDelay&&!t.isLowIE&&t.supportsTransition?(t._addClassToMFP(C),setTimeout(function(){t._close()},t.st.removalDelay)):t._close())},_close:function(){T(l);var n=C+" "+v+" ";if(t.bgOverlay.detach(),t.wrap.detach(),t.container.empty(),t.st.mainClass&&(n+=t.st.mainClass+" "),t._removeClassFromMFP(n),t.fixedContentPos){var i={marginRight:""};t.isIE7?e("body, html").css("overflow",""):i.overflow="",e("html").css(i)}o.off("keyup"+h+" focusin"+h),t.ev.off(h),t.wrap.attr("class","mfp-wrap").removeAttr("style"),t.bgOverlay.attr("class","mfp-bg"),t.container.attr("class","mfp-container"),!t.st.showCloseBtn||t.st.closeBtnInside&&t.currTemplate[t.currItem.type]!==!0||t.currTemplate.closeBtn&&t.currTemplate.closeBtn.detach(),t._lastFocusedEl&&e(t._lastFocusedEl).focus(),t.currItem=null,t.content=null,t.currTemplate=null,t.prevHeight=0,T(d)},updateSize:function(e){if(t.isIOS){var n=document.documentElement.clientWidth/window.innerWidth,i=window.innerHeight*n;t.wrap.css("height",i),t.wH=i}else t.wH=e||I.height();t.fixedContentPos||t.wrap.css("height",t.wH),T("Resize")},updateItemHTML:function(){var n=t.items[t.index];t.contentContainer.detach(),t.content&&t.content.detach(),n.parsed||(n=t.parseEl(t.index));var i=n.type;if(T("BeforeChange",[t.currItem?t.currItem.type:"",i]),t.currItem=n,!t.currTemplate[i]){var o=t.st[i]?t.st[i].markup:!1;T("FirstMarkupParse",o),t.currTemplate[i]=o?e(o):!0}r&&r!==n.type&&t.container.removeClass("mfp-"+r+"-holder");var a=t["get"+i.charAt(0).toUpperCase()+i.slice(1)](n,t.currTemplate[i]);t.appendContent(a,i),n.preloaded=!0,T(m,n),r=n.type,t.container.prepend(t.contentContainer),T("AfterChange")},appendContent:function(e,n){t.content=e,e?t.st.showCloseBtn&&t.st.closeBtnInside&&t.currTemplate[n]===!0?t.content.find(".mfp-close").length||t.content.append(E()):t.content=e:t.content="",T(u),t.container.addClass("mfp-"+n+"-holder"),t.contentContainer.append(t.content)},parseEl:function(n){var i,o=t.items[n];if(o.tagName?o={el:e(o)}:(i=o.type,o={data:o,src:o.src}),o.el){for(var r=t.types,a=0;r.length>a;a++)if(o.el.hasClass("mfp-"+r[a])){i=r[a];break}o.src=o.el.attr("data-mfp-src"),o.src||(o.src=o.el.attr("href"))}return o.type=i||t.st.type||"inline",o.index=n,o.parsed=!0,t.items[n]=o,T("ElementParse",o),t.items[n]},addGroup:function(e,n){var i=function(i){i.mfpEl=this,t._openClick(i,e,n)};n||(n={});var o="click.magnificPopup";n.mainEl=e,n.items?(n.isObj=!0,e.off(o).on(o,i)):(n.isObj=!1,n.delegate?e.off(o).on(o,n.delegate,i):(n.items=e,e.off(o).on(o,i)))},_openClick:function(n,i,o){var r=void 0!==o.midClick?o.midClick:e.magnificPopup.defaults.midClick;if(r||2!==n.which&&!n.ctrlKey&&!n.metaKey){var a=void 0!==o.disableOn?o.disableOn:e.magnificPopup.defaults.disableOn;if(a)if(e.isFunction(a)){if(!a.call(t))return!0}else if(a>I.width())return!0;n.type&&(n.preventDefault(),t.isOpen&&n.stopPropagation()),o.el=e(n.mfpEl),o.delegate&&(o.items=i.find(o.delegate)),t.open(o)}},updateStatus:function(e,i){if(t.preloader){n!==e&&t.container.removeClass("mfp-s-"+n),i||"loading"!==e||(i=t.st.tLoading);var o={status:e,text:i};T("UpdateStatus",o),e=o.status,i=o.text,t.preloader.html(i),t.preloader.find("a").on("click",function(e){e.stopImmediatePropagation()}),t.container.addClass("mfp-s-"+e),n=e}},_checkIfClose:function(n){if(!e(n).hasClass(y)){var i=t.st.closeOnContentClick,o=t.st.closeOnBgClick;if(i&&o)return!0;if(!t.content||e(n).hasClass("mfp-close")||t.preloader&&n===t.preloader[0])return!0;if(n===t.content[0]||e.contains(t.content[0],n)){if(i)return!0}else if(o&&e.contains(document,n))return!0;return!1}},_addClassToMFP:function(e){t.bgOverlay.addClass(e),t.wrap.addClass(e)},_removeClassFromMFP:function(e){this.bgOverlay.removeClass(e),t.wrap.removeClass(e)},_hasScrollBar:function(e){return(t.isIE7?o.height():document.body.scrollHeight)>(e||I.height())},_setFocus:function(){(t.st.focus?t.content.find(t.st.focus).eq(0):t.wrap).focus()},_onFocusIn:function(n){return n.target===t.wrap[0]||e.contains(t.wrap[0],n.target)?void 0:(t._setFocus(),!1)},_parseMarkup:function(t,n,i){var o;i.data&&(n=e.extend(i.data,n)),T(p,[t,n,i]),e.each(n,function(e,n){if(void 0===n||n===!1)return!0;if(o=e.split("_"),o.length>1){var i=t.find(h+"-"+o[0]);if(i.length>0){var r=o[1];"replaceWith"===r?i[0]!==n[0]&&i.replaceWith(n):"img"===r?i.is("img")?i.attr("src",n):i.replaceWith('<img src="'+n+'" class="'+i.attr("class")+'" />'):i.attr(o[1],n)}}else t.find(h+"-"+e).html(n)})},_getScrollbarSize:function(){if(void 0===t.scrollbarSize){var e=document.createElement("div");e.id="mfp-sbm",e.style.cssText="width: 99px; height: 99px; overflow: scroll; position: absolute; top: -9999px;",document.body.appendChild(e),t.scrollbarSize=e.offsetWidth-e.clientWidth,document.body.removeChild(e)}return t.scrollbarSize}},e.magnificPopup={instance:null,proto:w.prototype,modules:[],open:function(t,n){return _(),t=t?e.extend(!0,{},t):{},t.isObj=!0,t.index=n||0,this.instance.open(t)},close:function(){return e.magnificPopup.instance&&e.magnificPopup.instance.close()},registerModule:function(t,n){n.options&&(e.magnificPopup.defaults[t]=n.options),e.extend(this.proto,n.proto),this.modules.push(t)},defaults:{disableOn:0,key:null,midClick:!1,mainClass:"",preloader:!0,focus:"",closeOnContentClick:!1,closeOnBgClick:!0,closeBtnInside:!0,showCloseBtn:!0,enableEscapeKey:!0,modal:!1,alignTop:!1,removalDelay:0,prependTo:null,fixedContentPos:"auto",fixedBgPos:"auto",overflowY:"auto",closeMarkup:'<button title="%title%" type="button" class="mfp-close">&times;</button>',tClose:"Close (Esc)",tLoading:"Loading..."}},e.fn.magnificPopup=function(n){_();var i=e(this);if("string"==typeof n)if("open"===n){var o,r=b?i.data("magnificPopup"):i[0].magnificPopup,a=parseInt(arguments[1],10)||0;r.items?o=r.items[a]:(o=i,r.delegate&&(o=o.find(r.delegate)),o=o.eq(a)),t._openClick({mfpEl:o},i,r)}else t.isOpen&&t[n].apply(t,Array.prototype.slice.call(arguments,1));else n=e.extend(!0,{},n),b?i.data("magnificPopup",n):i[0].magnificPopup=n,t.addGroup(i,n);return i};var P,O,z,M="inline",B=function(){z&&(O.after(z.addClass(P)).detach(),z=null)};e.magnificPopup.registerModule(M,{options:{hiddenClass:"hide",markup:"",tNotFound:"Content not found"},proto:{initInline:function(){t.types.push(M),x(l+"."+M,function(){B()})},getInline:function(n,i){if(B(),n.src){var o=t.st.inline,r=e(n.src);if(r.length){var a=r[0].parentNode;a&&a.tagName&&(O||(P=o.hiddenClass,O=k(P),P="mfp-"+P),z=r.after(O).detach().removeClass(P)),t.updateStatus("ready")}else t.updateStatus("error",o.tNotFound),r=e("<div>");return n.inlineElement=r,r}return t.updateStatus("ready"),t._parseMarkup(i,{},n),i}}});var F,H="ajax",L=function(){F&&i.removeClass(F)},A=function(){L(),t.req&&t.req.abort()};e.magnificPopup.registerModule(H,{options:{settings:null,cursor:"mfp-ajax-cur",tError:'<a href="%url%">The content</a> could not be loaded.'},proto:{initAjax:function(){t.types.push(H),F=t.st.ajax.cursor,x(l+"."+H,A),x("BeforeChange."+H,A)},getAjax:function(n){F&&i.addClass(F),t.updateStatus("loading");var o=e.extend({url:n.src,success:function(i,o,r){var a={data:i,xhr:r};T("ParseAjax",a),t.appendContent(e(a.data),H),n.finished=!0,L(),t._setFocus(),setTimeout(function(){t.wrap.addClass(v)},16),t.updateStatus("ready"),T("AjaxContentAdded")},error:function(){L(),n.finished=n.loadError=!0,t.updateStatus("error",t.st.ajax.tError.replace("%url%",n.src))}},t.st.ajax.settings);return t.req=e.ajax(o),""}}});var j,N=function(n){if(n.data&&void 0!==n.data.title)return n.data.title;var i=t.st.image.titleSrc;if(i){if(e.isFunction(i))return i.call(t,n);if(n.el)return n.el.attr(i)||""}return""};e.magnificPopup.registerModule("image",{options:{markup:'<div class="mfp-figure"><div class="mfp-close"></div><figure><div class="mfp-img"></div><figcaption><div class="mfp-bottom-bar"><div class="mfp-title"></div><div class="mfp-counter"></div></div></figcaption></figure></div>',cursor:"mfp-zoom-out-cur",titleSrc:"title",verticalFit:!0,tError:'<a href="%url%">The image</a> could not be loaded.'},proto:{initImage:function(){var e=t.st.image,n=".image";t.types.push("image"),x(f+n,function(){"image"===t.currItem.type&&e.cursor&&i.addClass(e.cursor)}),x(l+n,function(){e.cursor&&i.removeClass(e.cursor),I.off("resize"+h)}),x("Resize"+n,t.resizeImage),t.isLowIE&&x("AfterChange",t.resizeImage)},resizeImage:function(){var e=t.currItem;if(e&&e.img&&t.st.image.verticalFit){var n=0;t.isLowIE&&(n=parseInt(e.img.css("padding-top"),10)+parseInt(e.img.css("padding-bottom"),10)),e.img.css("max-height",t.wH-n)}},_onImageHasSize:function(e){e.img&&(e.hasSize=!0,j&&clearInterval(j),e.isCheckingImgSize=!1,T("ImageHasSize",e),e.imgHidden&&(t.content&&t.content.removeClass("mfp-loading"),e.imgHidden=!1))},findImageSize:function(e){var n=0,i=e.img[0],o=function(r){j&&clearInterval(j),j=setInterval(function(){return i.naturalWidth>0?(t._onImageHasSize(e),void 0):(n>200&&clearInterval(j),n++,3===n?o(10):40===n?o(50):100===n&&o(500),void 0)},r)};o(1)},getImage:function(n,i){var o=0,r=function(){n&&(n.img[0].complete?(n.img.off(".mfploader"),n===t.currItem&&(t._onImageHasSize(n),t.updateStatus("ready")),n.hasSize=!0,n.loaded=!0,T("ImageLoadComplete")):(o++,200>o?setTimeout(r,100):a()))},a=function(){n&&(n.img.off(".mfploader"),n===t.currItem&&(t._onImageHasSize(n),t.updateStatus("error",s.tError.replace("%url%",n.src))),n.hasSize=!0,n.loaded=!0,n.loadError=!0)},s=t.st.image,l=i.find(".mfp-img");if(l.length){var c=document.createElement("img");c.className="mfp-img",n.img=e(c).on("load.mfploader",r).on("error.mfploader",a),c.src=n.src,l.is("img")&&(n.img=n.img.clone()),c=n.img[0],c.naturalWidth>0?n.hasSize=!0:c.width||(n.hasSize=!1)}return t._parseMarkup(i,{title:N(n),img_replaceWith:n.img},n),t.resizeImage(),n.hasSize?(j&&clearInterval(j),n.loadError?(i.addClass("mfp-loading"),t.updateStatus("error",s.tError.replace("%url%",n.src))):(i.removeClass("mfp-loading"),t.updateStatus("ready")),i):(t.updateStatus("loading"),n.loading=!0,n.hasSize||(n.imgHidden=!0,i.addClass("mfp-loading"),t.findImageSize(n)),i)}}});var W,R=function(){return void 0===W&&(W=void 0!==document.createElement("p").style.MozTransform),W};e.magnificPopup.registerModule("zoom",{options:{enabled:!1,easing:"ease-in-out",duration:300,opener:function(e){return e.is("img")?e:e.find("img")}},proto:{initZoom:function(){var e,n=t.st.zoom,i=".zoom";if(n.enabled&&t.supportsTransition){var o,r,a=n.duration,s=function(e){var t=e.clone().removeAttr("style").removeAttr("class").addClass("mfp-animated-image"),i="all "+n.duration/1e3+"s "+n.easing,o={position:"fixed",zIndex:9999,left:0,top:0,"-webkit-backface-visibility":"hidden"},r="transition";return o["-webkit-"+r]=o["-moz-"+r]=o["-o-"+r]=o[r]=i,t.css(o),t},d=function(){t.content.css("visibility","visible")};x("BuildControls"+i,function(){if(t._allowZoom()){if(clearTimeout(o),t.content.css("visibility","hidden"),e=t._getItemToZoom(),!e)return d(),void 0;r=s(e),r.css(t._getOffset()),t.wrap.append(r),o=setTimeout(function(){r.css(t._getOffset(!0)),o=setTimeout(function(){d(),setTimeout(function(){r.remove(),e=r=null,T("ZoomAnimationEnded")},16)},a)},16)}}),x(c+i,function(){if(t._allowZoom()){if(clearTimeout(o),t.st.removalDelay=a,!e){if(e=t._getItemToZoom(),!e)return;r=s(e)}r.css(t._getOffset(!0)),t.wrap.append(r),t.content.css("visibility","hidden"),setTimeout(function(){r.css(t._getOffset())},16)}}),x(l+i,function(){t._allowZoom()&&(d(),r&&r.remove(),e=null)})}},_allowZoom:function(){return"image"===t.currItem.type},_getItemToZoom:function(){return t.currItem.hasSize?t.currItem.img:!1},_getOffset:function(n){var i;i=n?t.currItem.img:t.st.zoom.opener(t.currItem.el||t.currItem);var o=i.offset(),r=parseInt(i.css("padding-top"),10),a=parseInt(i.css("padding-bottom"),10);o.top-=e(window).scrollTop()-r;var s={width:i.width(),height:(b?i.innerHeight():i[0].offsetHeight)-a-r};return R()?s["-moz-transform"]=s.transform="translate("+o.left+"px,"+o.top+"px)":(s.left=o.left,s.top=o.top),s}}});var Z="iframe",q="//about:blank",D=function(e){if(t.currTemplate[Z]){var n=t.currTemplate[Z].find("iframe");n.length&&(e||(n[0].src=q),t.isIE8&&n.css("display",e?"block":"none"))}};e.magnificPopup.registerModule(Z,{options:{markup:'<div class="mfp-iframe-scaler"><div class="mfp-close"></div><iframe class="mfp-iframe" src="//about:blank" frameborder="0" allowfullscreen></iframe></div>',srcAction:"iframe_src",patterns:{youtube:{index:"youtube.com",id:"v=",src:"//www.youtube.com/embed/%id%?autoplay=1"},vimeo:{index:"vimeo.com/",id:"/",src:"//player.vimeo.com/video/%id%?autoplay=1"},gmaps:{index:"//maps.google.",src:"%id%&output=embed"}}},proto:{initIframe:function(){t.types.push(Z),x("BeforeChange",function(e,t,n){t!==n&&(t===Z?D():n===Z&&D(!0))}),x(l+"."+Z,function(){D()})},getIframe:function(n,i){var o=n.src,r=t.st.iframe;e.each(r.patterns,function(){return o.indexOf(this.index)>-1?(this.id&&(o="string"==typeof this.id?o.substr(o.lastIndexOf(this.id)+this.id.length,o.length):this.id.call(this,o)),o=this.src.replace("%id%",o),!1):void 0});var a={};return r.srcAction&&(a[r.srcAction]=o),t._parseMarkup(i,a,n),t.updateStatus("ready"),i}}});var K=function(e){var n=t.items.length;return e>n-1?e-n:0>e?n+e:e},Y=function(e,t,n){return e.replace(/%curr%/gi,t+1).replace(/%total%/gi,n)};e.magnificPopup.registerModule("gallery",{options:{enabled:!1,arrowMarkup:'<button title="%title%" type="button" class="mfp-arrow mfp-arrow-%dir%"></button>',preload:[0,2],navigateByImgClick:!0,arrows:!0,tPrev:"Previous (Left arrow key)",tNext:"Next (Right arrow key)",tCounter:"%curr% of %total%"},proto:{initGallery:function(){var n=t.st.gallery,i=".mfp-gallery",r=Boolean(e.fn.mfpFastClick);return t.direction=!0,n&&n.enabled?(a+=" mfp-gallery",x(f+i,function(){n.navigateByImgClick&&t.wrap.on("click"+i,".mfp-img",function(){return t.items.length>1?(t.next(),!1):void 0}),o.on("keydown"+i,function(e){37===e.keyCode?t.prev():39===e.keyCode&&t.next()})}),x("UpdateStatus"+i,function(e,n){n.text&&(n.text=Y(n.text,t.currItem.index,t.items.length))}),x(p+i,function(e,i,o,r){var a=t.items.length;o.counter=a>1?Y(n.tCounter,r.index,a):""}),x("BuildControls"+i,function(){if(t.items.length>1&&n.arrows&&!t.arrowLeft){var i=n.arrowMarkup,o=t.arrowLeft=e(i.replace(/%title%/gi,n.tPrev).replace(/%dir%/gi,"left")).addClass(y),a=t.arrowRight=e(i.replace(/%title%/gi,n.tNext).replace(/%dir%/gi,"right")).addClass(y),s=r?"mfpFastClick":"click";o[s](function(){t.prev()}),a[s](function(){t.next()}),t.isIE7&&(k("b",o[0],!1,!0),k("a",o[0],!1,!0),k("b",a[0],!1,!0),k("a",a[0],!1,!0)),t.container.append(o.add(a))}}),x(m+i,function(){t._preloadTimeout&&clearTimeout(t._preloadTimeout),t._preloadTimeout=setTimeout(function(){t.preloadNearbyImages(),t._preloadTimeout=null},16)}),x(l+i,function(){o.off(i),t.wrap.off("click"+i),t.arrowLeft&&r&&t.arrowLeft.add(t.arrowRight).destroyMfpFastClick(),t.arrowRight=t.arrowLeft=null}),void 0):!1},next:function(){t.direction=!0,t.index=K(t.index+1),t.updateItemHTML()},prev:function(){t.direction=!1,t.index=K(t.index-1),t.updateItemHTML()},goTo:function(e){t.direction=e>=t.index,t.index=e,t.updateItemHTML()},preloadNearbyImages:function(){var e,n=t.st.gallery.preload,i=Math.min(n[0],t.items.length),o=Math.min(n[1],t.items.length);for(e=1;(t.direction?o:i)>=e;e++)t._preloadItem(t.index+e);for(e=1;(t.direction?i:o)>=e;e++)t._preloadItem(t.index-e)},_preloadItem:function(n){if(n=K(n),!t.items[n].preloaded){var i=t.items[n];i.parsed||(i=t.parseEl(n)),T("LazyLoad",i),"image"===i.type&&(i.img=e('<img class="mfp-img" />').on("load.mfploader",function(){i.hasSize=!0}).on("error.mfploader",function(){i.hasSize=!0,i.loadError=!0,T("LazyLoadError",i)}).attr("src",i.src)),i.preloaded=!0}}}});var U="retina";e.magnificPopup.registerModule(U,{options:{replaceSrc:function(e){return e.src.replace(/\.\w+$/,function(e){return"@2x"+e})},ratio:1},proto:{initRetina:function(){if(window.devicePixelRatio>1){var e=t.st.retina,n=e.ratio;n=isNaN(n)?n():n,n>1&&(x("ImageHasSize."+U,function(e,t){t.img.css({"max-width":t.img[0].naturalWidth/n,width:"100%"})}),x("ElementParse."+U,function(t,i){i.src=e.replaceSrc(i,n)}))}}}}),function(){var t=1e3,n="ontouchstart"in window,i=function(){I.off("touchmove"+r+" touchend"+r)},o="mfpFastClick",r="."+o;e.fn.mfpFastClick=function(o){return e(this).each(function(){var a,s=e(this);if(n){var l,c,d,u,p,f;s.on("touchstart"+r,function(e){u=!1,f=1,p=e.originalEvent?e.originalEvent.touches[0]:e.touches[0],c=p.clientX,d=p.clientY,I.on("touchmove"+r,function(e){p=e.originalEvent?e.originalEvent.touches:e.touches,f=p.length,p=p[0],(Math.abs(p.clientX-c)>10||Math.abs(p.clientY-d)>10)&&(u=!0,i())}).on("touchend"+r,function(e){i(),u||f>1||(a=!0,e.preventDefault(),clearTimeout(l),l=setTimeout(function(){a=!1},t),o())})})}s.on("click"+r,function(){a||o()})})},e.fn.destroyMfpFastClick=function(){e(this).off("touchstart"+r+" click"+r),n&&I.off("touchmove"+r+" touchend"+r)}}(),_()})(window.jQuery||window.Zepto);

});

;/*!/modules/main.js*/
define('modules/main', function(require, exports, module) {

  var utils = require('modules/ignore/utils');
  // SuperSlide
  require('modules/ignore/SuperSlide/jquery.SuperSlide.2.1.1');
  // 数字滚动
  var CountUp = require('modules/ignore/int/countUp.min');
  require('modules/ignore/magnific/jquery.magnific-popup');
  $(function(){ 
      // banner
      jQuery(".banner").slide({ titCell: ".hd ul", mainCell: ".bd ul", effect: "fold", autoPlay: true, autoPage: true, trigger: "click" });
      jQuery(".scrollBox_36").slide({ titCell:".list li", mainCell:".piclist", effect:"left",vis:6,scroll:1,delayTime:400,trigger:"click",easing:"easeOutCirc"});
      //数字滚动
      (function(){
          var options = {  
              useEasing: true,
                useGrouping: true,
                separator: ',',
                decimal: '.',
                prefix: '',
                suffix: ''
          };
          try{
              var myTargetElement = new CountUp("myTargetElement", 0, $('#myTargetElement').text(), 2, 2.5, options);
              var myTargetElement2 = new CountUp("myTargetElement2", 0, $('#myTargetElement2').text(), 0, 2.5, options);
              var myTargetElement3 = new CountUp("myTargetElement3", 0, $('#myTargetElement3').text(), 0, 2.5, options);
              var myTargetElement4 = new CountUp("myTargetElement4", 0, $('#myTargetElement4').text(), 0, 2.5, options);
              $(window).scroll(function() { 
                  if ($(window).scrollTop() >= 80) {
                      myTargetElement.start();
                      myTargetElement2.start();
                      myTargetElement3.start();
                      myTargetElement4.start();
                  }
              })
          }catch(e){
  
          }
      })()
  
      // 视频
      if ($('.video').length) {
          $('.video').magnificPopup({
              type: 'iframe'
          });
      }
  })
  
  // utils.fileupload($('#files-1'));
  
  $('div[id^="files-"]').each(function(){
      utils.fileupload($(this));
  
  });
  

  utils.pageTab($('.nav_js li'),$('.content_js'));
  utils.pageTab($('.nav_js2 li'),$('.content_js2'));
//  utils.pageTab($('.team-management-btn li'),$('.team-management-item'));

});
