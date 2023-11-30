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
   * 原来的版本：jQuery JavaScript Library v1.8.3
   */
  /*! jQuery v3.7.1 | (c) OpenJS Foundation and other contributors | jquery.org/license */
!function(e,t){"use strict";"object"==typeof module&&"object"==typeof module.exports?module.exports=e.document?t(e,!0):function(e){if(!e.document)throw new Error("jQuery requires a window with a document");return t(e)}:t(e)}("undefined"!=typeof window?window:this,function(ie,e){"use strict";var oe=[],r=Object.getPrototypeOf,ae=oe.slice,g=oe.flat?function(e){return oe.flat.call(e)}:function(e){return oe.concat.apply([],e)},s=oe.push,se=oe.indexOf,n={},i=n.toString,ue=n.hasOwnProperty,o=ue.toString,a=o.call(Object),le={},v=function(e){return"function"==typeof e&&"number"!=typeof e.nodeType&&"function"!=typeof e.item},y=function(e){return null!=e&&e===e.window},C=ie.document,u={type:!0,src:!0,nonce:!0,noModule:!0};function m(e,t,n){var r,i,o=(n=n||C).createElement("script");if(o.text=e,t)for(r in u)(i=t[r]||t.getAttribute&&t.getAttribute(r))&&o.setAttribute(r,i);n.head.appendChild(o).parentNode.removeChild(o)}function x(e){return null==e?e+"":"object"==typeof e||"function"==typeof e?n[i.call(e)]||"object":typeof e}var t="3.7.1",l=/HTML$/i,ce=function(e,t){return new ce.fn.init(e,t)};function c(e){var t=!!e&&"length"in e&&e.length,n=x(e);return!v(e)&&!y(e)&&("array"===n||0===t||"number"==typeof t&&0<t&&t-1 in e)}function fe(e,t){return e.nodeName&&e.nodeName.toLowerCase()===t.toLowerCase()}ce.fn=ce.prototype={jquery:t,constructor:ce,length:0,toArray:function(){return ae.call(this)},get:function(e){return null==e?ae.call(this):e<0?this[e+this.length]:this[e]},pushStack:function(e){var t=ce.merge(this.constructor(),e);return t.prevObject=this,t},each:function(e){return ce.each(this,e)},map:function(n){return this.pushStack(ce.map(this,function(e,t){return n.call(e,t,e)}))},slice:function(){return this.pushStack(ae.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},even:function(){return this.pushStack(ce.grep(this,function(e,t){return(t+1)%2}))},odd:function(){return this.pushStack(ce.grep(this,function(e,t){return t%2}))},eq:function(e){var t=this.length,n=+e+(e<0?t:0);return this.pushStack(0<=n&&n<t?[this[n]]:[])},end:function(){return this.prevObject||this.constructor()},push:s,sort:oe.sort,splice:oe.splice},ce.extend=ce.fn.extend=function(){var e,t,n,r,i,o,a=arguments[0]||{},s=1,u=arguments.length,l=!1;for("boolean"==typeof a&&(l=a,a=arguments[s]||{},s++),"object"==typeof a||v(a)||(a={}),s===u&&(a=this,s--);s<u;s++)if(null!=(e=arguments[s]))for(t in e)r=e[t],"__proto__"!==t&&a!==r&&(l&&r&&(ce.isPlainObject(r)||(i=Array.isArray(r)))?(n=a[t],o=i&&!Array.isArray(n)?[]:i||ce.isPlainObject(n)?n:{},i=!1,a[t]=ce.extend(l,o,r)):void 0!==r&&(a[t]=r));return a},ce.extend({expando:"jQuery"+(t+Math.random()).replace(/\D/g,""),isReady:!0,error:function(e){throw new Error(e)},noop:function(){},isPlainObject:function(e){var t,n;return!(!e||"[object Object]"!==i.call(e))&&(!(t=r(e))||"function"==typeof(n=ue.call(t,"constructor")&&t.constructor)&&o.call(n)===a)},isEmptyObject:function(e){var t;for(t in e)return!1;return!0},globalEval:function(e,t,n){m(e,{nonce:t&&t.nonce},n)},each:function(e,t){var n,r=0;if(c(e)){for(n=e.length;r<n;r++)if(!1===t.call(e[r],r,e[r]))break}else for(r in e)if(!1===t.call(e[r],r,e[r]))break;return e},text:function(e){var t,n="",r=0,i=e.nodeType;if(!i)while(t=e[r++])n+=ce.text(t);return 1===i||11===i?e.textContent:9===i?e.documentElement.textContent:3===i||4===i?e.nodeValue:n},makeArray:function(e,t){var n=t||[];return null!=e&&(c(Object(e))?ce.merge(n,"string"==typeof e?[e]:e):s.call(n,e)),n},inArray:function(e,t,n){return null==t?-1:se.call(t,e,n)},isXMLDoc:function(e){var t=e&&e.namespaceURI,n=e&&(e.ownerDocument||e).documentElement;return!l.test(t||n&&n.nodeName||"HTML")},merge:function(e,t){for(var n=+t.length,r=0,i=e.length;r<n;r++)e[i++]=t[r];return e.length=i,e},grep:function(e,t,n){for(var r=[],i=0,o=e.length,a=!n;i<o;i++)!t(e[i],i)!==a&&r.push(e[i]);return r},map:function(e,t,n){var r,i,o=0,a=[];if(c(e))for(r=e.length;o<r;o++)null!=(i=t(e[o],o,n))&&a.push(i);else for(o in e)null!=(i=t(e[o],o,n))&&a.push(i);return g(a)},guid:1,support:le}),"function"==typeof Symbol&&(ce.fn[Symbol.iterator]=oe[Symbol.iterator]),ce.each("Boolean Number String Function Array Date RegExp Object Error Symbol".split(" "),function(e,t){n["[object "+t+"]"]=t.toLowerCase()});var pe=oe.pop,de=oe.sort,he=oe.splice,ge="[\\x20\\t\\r\\n\\f]",ve=new RegExp("^"+ge+"+|((?:^|[^\\\\])(?:\\\\.)*)"+ge+"+$","g");ce.contains=function(e,t){var n=t&&t.parentNode;return e===n||!(!n||1!==n.nodeType||!(e.contains?e.contains(n):e.compareDocumentPosition&&16&e.compareDocumentPosition(n)))};var f=/([\0-\x1f\x7f]|^-?\d)|^-$|[^\x80-\uFFFF\w-]/g;function p(e,t){return t?"\0"===e?"\ufffd":e.slice(0,-1)+"\\"+e.charCodeAt(e.length-1).toString(16)+" ":"\\"+e}ce.escapeSelector=function(e){return(e+"").replace(f,p)};var ye=C,me=s;!function(){var e,b,w,o,a,T,r,C,d,i,k=me,S=ce.expando,E=0,n=0,s=W(),c=W(),u=W(),h=W(),l=function(e,t){return e===t&&(a=!0),0},f="checked|selected|async|autofocus|autoplay|controls|defer|disabled|hidden|ismap|loop|multiple|open|readonly|required|scoped",t="(?:\\\\[\\da-fA-F]{1,6}"+ge+"?|\\\\[^\\r\\n\\f]|[\\w-]|[^\0-\\x7f])+",p="\\["+ge+"*("+t+")(?:"+ge+"*([*^$|!~]?=)"+ge+"*(?:'((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\"|("+t+"))|)"+ge+"*\\]",g=":("+t+")(?:\\((('((?:\\\\.|[^\\\\'])*)'|\"((?:\\\\.|[^\\\\\"])*)\")|((?:\\\\.|[^\\\\()[\\]]|"+p+")*)|.*)\\)|)",v=new RegExp(ge+"+","g"),y=new RegExp("^"+ge+"*,"+ge+"*"),m=new RegExp("^"+ge+"*([>+~]|"+ge+")"+ge+"*"),x=new RegExp(ge+"|>"),j=new RegExp(g),A=new RegExp("^"+t+"$"),D={ID:new RegExp("^#("+t+")"),CLASS:new RegExp("^\\.("+t+")"),TAG:new RegExp("^("+t+"|[*])"),ATTR:new RegExp("^"+p),PSEUDO:new RegExp("^"+g),CHILD:new RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+ge+"*(even|odd|(([+-]|)(\\d*)n|)"+ge+"*(?:([+-]|)"+ge+"*(\\d+)|))"+ge+"*\\)|)","i"),bool:new RegExp("^(?:"+f+")$","i"),needsContext:new RegExp("^"+ge+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+ge+"*((?:-\\d)?\\d*)"+ge+"*\\)|)(?=[^-]|$)","i")},N=/^(?:input|select|textarea|button)$/i,q=/^h\d$/i,L=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,H=/[+~]/,O=new RegExp("\\\\[\\da-fA-F]{1,6}"+ge+"?|\\\\([^\\r\\n\\f])","g"),P=function(e,t){var n="0x"+e.slice(1)-65536;return t||(n<0?String.fromCharCode(n+65536):String.fromCharCode(n>>10|55296,1023&n|56320))},M=function(){V()},R=J(function(e){return!0===e.disabled&&fe(e,"fieldset")},{dir:"parentNode",next:"legend"});try{k.apply(oe=ae.call(ye.childNodes),ye.childNodes),oe[ye.childNodes.length].nodeType}catch(e){k={apply:function(e,t){me.apply(e,ae.call(t))},call:function(e){me.apply(e,ae.call(arguments,1))}}}function I(t,e,n,r){var i,o,a,s,u,l,c,f=e&&e.ownerDocument,p=e?e.nodeType:9;if(n=n||[],"string"!=typeof t||!t||1!==p&&9!==p&&11!==p)return n;if(!r&&(V(e),e=e||T,C)){if(11!==p&&(u=L.exec(t)))if(i=u[1]){if(9===p){if(!(a=e.getElementById(i)))return n;if(a.id===i)return k.call(n,a),n}else if(f&&(a=f.getElementById(i))&&I.contains(e,a)&&a.id===i)return k.call(n,a),n}else{if(u[2])return k.apply(n,e.getElementsByTagName(t)),n;if((i=u[3])&&e.getElementsByClassName)return k.apply(n,e.getElementsByClassName(i)),n}if(!(h[t+" "]||d&&d.test(t))){if(c=t,f=e,1===p&&(x.test(t)||m.test(t))){(f=H.test(t)&&U(e.parentNode)||e)==e&&le.scope||((s=e.getAttribute("id"))?s=ce.escapeSelector(s):e.setAttribute("id",s=S)),o=(l=Y(t)).length;while(o--)l[o]=(s?"#"+s:":scope")+" "+Q(l[o]);c=l.join(",")}try{return k.apply(n,f.querySelectorAll(c)),n}catch(e){h(t,!0)}finally{s===S&&e.removeAttribute("id")}}}return re(t.replace(ve,"$1"),e,n,r)}function W(){var r=[];return function e(t,n){return r.push(t+" ")>b.cacheLength&&delete e[r.shift()],e[t+" "]=n}}function F(e){return e[S]=!0,e}function $(e){var t=T.createElement("fieldset");try{return!!e(t)}catch(e){return!1}finally{t.parentNode&&t.parentNode.removeChild(t),t=null}}function B(t){return function(e){return fe(e,"input")&&e.type===t}}function _(t){return function(e){return(fe(e,"input")||fe(e,"button"))&&e.type===t}}function z(t){return function(e){return"form"in e?e.parentNode&&!1===e.disabled?"label"in e?"label"in e.parentNode?e.parentNode.disabled===t:e.disabled===t:e.isDisabled===t||e.isDisabled!==!t&&R(e)===t:e.disabled===t:"label"in e&&e.disabled===t}}function X(a){return F(function(o){return o=+o,F(function(e,t){var n,r=a([],e.length,o),i=r.length;while(i--)e[n=r[i]]&&(e[n]=!(t[n]=e[n]))})})}function U(e){return e&&"undefined"!=typeof e.getElementsByTagName&&e}function V(e){var t,n=e?e.ownerDocument||e:ye;return n!=T&&9===n.nodeType&&n.documentElement&&(r=(T=n).documentElement,C=!ce.isXMLDoc(T),i=r.matches||r.webkitMatchesSelector||r.msMatchesSelector,r.msMatchesSelector&&ye!=T&&(t=T.defaultView)&&t.top!==t&&t.addEventListener("unload",M),le.getById=$(function(e){return r.appendChild(e).id=ce.expando,!T.getElementsByName||!T.getElementsByName(ce.expando).length}),le.disconnectedMatch=$(function(e){return i.call(e,"*")}),le.scope=$(function(){return T.querySelectorAll(":scope")}),le.cssHas=$(function(){try{return T.querySelector(":has(*,:jqfake)"),!1}catch(e){return!0}}),le.getById?(b.filter.ID=function(e){var t=e.replace(O,P);return function(e){return e.getAttribute("id")===t}},b.find.ID=function(e,t){if("undefined"!=typeof t.getElementById&&C){var n=t.getElementById(e);return n?[n]:[]}}):(b.filter.ID=function(e){var n=e.replace(O,P);return function(e){var t="undefined"!=typeof e.getAttributeNode&&e.getAttributeNode("id");return t&&t.value===n}},b.find.ID=function(e,t){if("undefined"!=typeof t.getElementById&&C){var n,r,i,o=t.getElementById(e);if(o){if((n=o.getAttributeNode("id"))&&n.value===e)return[o];i=t.getElementsByName(e),r=0;while(o=i[r++])if((n=o.getAttributeNode("id"))&&n.value===e)return[o]}return[]}}),b.find.TAG=function(e,t){return"undefined"!=typeof t.getElementsByTagName?t.getElementsByTagName(e):t.querySelectorAll(e)},b.find.CLASS=function(e,t){if("undefined"!=typeof t.getElementsByClassName&&C)return t.getElementsByClassName(e)},d=[],$(function(e){var t;r.appendChild(e).innerHTML="<a id='"+S+"' href='' disabled='disabled'></a><select id='"+S+"-\r\\' disabled='disabled'><option selected=''></option></select>",e.querySelectorAll("[selected]").length||d.push("\\["+ge+"*(?:value|"+f+")"),e.querySelectorAll("[id~="+S+"-]").length||d.push("~="),e.querySelectorAll("a#"+S+"+*").length||d.push(".#.+[+~]"),e.querySelectorAll(":checked").length||d.push(":checked"),(t=T.createElement("input")).setAttribute("type","hidden"),e.appendChild(t).setAttribute("name","D"),r.appendChild(e).disabled=!0,2!==e.querySelectorAll(":disabled").length&&d.push(":enabled",":disabled"),(t=T.createElement("input")).setAttribute("name",""),e.appendChild(t),e.querySelectorAll("[name='']").length||d.push("\\["+ge+"*name"+ge+"*="+ge+"*(?:''|\"\")")}),le.cssHas||d.push(":has"),d=d.length&&new RegExp(d.join("|")),l=function(e,t){if(e===t)return a=!0,0;var n=!e.compareDocumentPosition-!t.compareDocumentPosition;return n||(1&(n=(e.ownerDocument||e)==(t.ownerDocument||t)?e.compareDocumentPosition(t):1)||!le.sortDetached&&t.compareDocumentPosition(e)===n?e===T||e.ownerDocument==ye&&I.contains(ye,e)?-1:t===T||t.ownerDocument==ye&&I.contains(ye,t)?1:o?se.call(o,e)-se.call(o,t):0:4&n?-1:1)}),T}for(e in I.matches=function(e,t){return I(e,null,null,t)},I.matchesSelector=function(e,t){if(V(e),C&&!h[t+" "]&&(!d||!d.test(t)))try{var n=i.call(e,t);if(n||le.disconnectedMatch||e.document&&11!==e.document.nodeType)return n}catch(e){h(t,!0)}return 0<I(t,T,null,[e]).length},I.contains=function(e,t){return(e.ownerDocument||e)!=T&&V(e),ce.contains(e,t)},I.attr=function(e,t){(e.ownerDocument||e)!=T&&V(e);var n=b.attrHandle[t.toLowerCase()],r=n&&ue.call(b.attrHandle,t.toLowerCase())?n(e,t,!C):void 0;return void 0!==r?r:e.getAttribute(t)},I.error=function(e){throw new Error("Syntax error, unrecognized expression: "+e)},ce.uniqueSort=function(e){var t,n=[],r=0,i=0;if(a=!le.sortStable,o=!le.sortStable&&ae.call(e,0),de.call(e,l),a){while(t=e[i++])t===e[i]&&(r=n.push(i));while(r--)he.call(e,n[r],1)}return o=null,e},ce.fn.uniqueSort=function(){return this.pushStack(ce.uniqueSort(ae.apply(this)))},(b=ce.expr={cacheLength:50,createPseudo:F,match:D,attrHandle:{},find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(e){return e[1]=e[1].replace(O,P),e[3]=(e[3]||e[4]||e[5]||"").replace(O,P),"~="===e[2]&&(e[3]=" "+e[3]+" "),e.slice(0,4)},CHILD:function(e){return e[1]=e[1].toLowerCase(),"nth"===e[1].slice(0,3)?(e[3]||I.error(e[0]),e[4]=+(e[4]?e[5]+(e[6]||1):2*("even"===e[3]||"odd"===e[3])),e[5]=+(e[7]+e[8]||"odd"===e[3])):e[3]&&I.error(e[0]),e},PSEUDO:function(e){var t,n=!e[6]&&e[2];return D.CHILD.test(e[0])?null:(e[3]?e[2]=e[4]||e[5]||"":n&&j.test(n)&&(t=Y(n,!0))&&(t=n.indexOf(")",n.length-t)-n.length)&&(e[0]=e[0].slice(0,t),e[2]=n.slice(0,t)),e.slice(0,3))}},filter:{TAG:function(e){var t=e.replace(O,P).toLowerCase();return"*"===e?function(){return!0}:function(e){return fe(e,t)}},CLASS:function(e){var t=s[e+" "];return t||(t=new RegExp("(^|"+ge+")"+e+"("+ge+"|$)"))&&s(e,function(e){return t.test("string"==typeof e.className&&e.className||"undefined"!=typeof e.getAttribute&&e.getAttribute("class")||"")})},ATTR:function(n,r,i){return function(e){var t=I.attr(e,n);return null==t?"!="===r:!r||(t+="","="===r?t===i:"!="===r?t!==i:"^="===r?i&&0===t.indexOf(i):"*="===r?i&&-1<t.indexOf(i):"$="===r?i&&t.slice(-i.length)===i:"~="===r?-1<(" "+t.replace(v," ")+" ").indexOf(i):"|="===r&&(t===i||t.slice(0,i.length+1)===i+"-"))}},CHILD:function(d,e,t,h,g){var v="nth"!==d.slice(0,3),y="last"!==d.slice(-4),m="of-type"===e;return 1===h&&0===g?function(e){return!!e.parentNode}:function(e,t,n){var r,i,o,a,s,u=v!==y?"nextSibling":"previousSibling",l=e.parentNode,c=m&&e.nodeName.toLowerCase(),f=!n&&!m,p=!1;if(l){if(v){while(u){o=e;while(o=o[u])if(m?fe(o,c):1===o.nodeType)return!1;s=u="only"===d&&!s&&"nextSibling"}return!0}if(s=[y?l.firstChild:l.lastChild],y&&f){p=(a=(r=(i=l[S]||(l[S]={}))[d]||[])[0]===E&&r[1])&&r[2],o=a&&l.childNodes[a];while(o=++a&&o&&o[u]||(p=a=0)||s.pop())if(1===o.nodeType&&++p&&o===e){i[d]=[E,a,p];break}}else if(f&&(p=a=(r=(i=e[S]||(e[S]={}))[d]||[])[0]===E&&r[1]),!1===p)while(o=++a&&o&&o[u]||(p=a=0)||s.pop())if((m?fe(o,c):1===o.nodeType)&&++p&&(f&&((i=o[S]||(o[S]={}))[d]=[E,p]),o===e))break;return(p-=g)===h||p%h==0&&0<=p/h}}},PSEUDO:function(e,o){var t,a=b.pseudos[e]||b.setFilters[e.toLowerCase()]||I.error("unsupported pseudo: "+e);return a[S]?a(o):1<a.length?(t=[e,e,"",o],b.setFilters.hasOwnProperty(e.toLowerCase())?F(function(e,t){var n,r=a(e,o),i=r.length;while(i--)e[n=se.call(e,r[i])]=!(t[n]=r[i])}):function(e){return a(e,0,t)}):a}},pseudos:{not:F(function(e){var r=[],i=[],s=ne(e.replace(ve,"$1"));return s[S]?F(function(e,t,n,r){var i,o=s(e,null,r,[]),a=e.length;while(a--)(i=o[a])&&(e[a]=!(t[a]=i))}):function(e,t,n){return r[0]=e,s(r,null,n,i),r[0]=null,!i.pop()}}),has:F(function(t){return function(e){return 0<I(t,e).length}}),contains:F(function(t){return t=t.replace(O,P),function(e){return-1<(e.textContent||ce.text(e)).indexOf(t)}}),lang:F(function(n){return A.test(n||"")||I.error("unsupported lang: "+n),n=n.replace(O,P).toLowerCase(),function(e){var t;do{if(t=C?e.lang:e.getAttribute("xml:lang")||e.getAttribute("lang"))return(t=t.toLowerCase())===n||0===t.indexOf(n+"-")}while((e=e.parentNode)&&1===e.nodeType);return!1}}),target:function(e){var t=ie.location&&ie.location.hash;return t&&t.slice(1)===e.id},root:function(e){return e===r},focus:function(e){return e===function(){try{return T.activeElement}catch(e){}}()&&T.hasFocus()&&!!(e.type||e.href||~e.tabIndex)},enabled:z(!1),disabled:z(!0),checked:function(e){return fe(e,"input")&&!!e.checked||fe(e,"option")&&!!e.selected},selected:function(e){return e.parentNode&&e.parentNode.selectedIndex,!0===e.selected},empty:function(e){for(e=e.firstChild;e;e=e.nextSibling)if(e.nodeType<6)return!1;return!0},parent:function(e){return!b.pseudos.empty(e)},header:function(e){return q.test(e.nodeName)},input:function(e){return N.test(e.nodeName)},button:function(e){return fe(e,"input")&&"button"===e.type||fe(e,"button")},text:function(e){var t;return fe(e,"input")&&"text"===e.type&&(null==(t=e.getAttribute("type"))||"text"===t.toLowerCase())},first:X(function(){return[0]}),last:X(function(e,t){return[t-1]}),eq:X(function(e,t,n){return[n<0?n+t:n]}),even:X(function(e,t){for(var n=0;n<t;n+=2)e.push(n);return e}),odd:X(function(e,t){for(var n=1;n<t;n+=2)e.push(n);return e}),lt:X(function(e,t,n){var r;for(r=n<0?n+t:t<n?t:n;0<=--r;)e.push(r);return e}),gt:X(function(e,t,n){for(var r=n<0?n+t:n;++r<t;)e.push(r);return e})}}).pseudos.nth=b.pseudos.eq,{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})b.pseudos[e]=B(e);for(e in{submit:!0,reset:!0})b.pseudos[e]=_(e);function G(){}function Y(e,t){var n,r,i,o,a,s,u,l=c[e+" "];if(l)return t?0:l.slice(0);a=e,s=[],u=b.preFilter;while(a){for(o in n&&!(r=y.exec(a))||(r&&(a=a.slice(r[0].length)||a),s.push(i=[])),n=!1,(r=m.exec(a))&&(n=r.shift(),i.push({value:n,type:r[0].replace(ve," ")}),a=a.slice(n.length)),b.filter)!(r=D[o].exec(a))||u[o]&&!(r=u[o](r))||(n=r.shift(),i.push({value:n,type:o,matches:r}),a=a.slice(n.length));if(!n)break}return t?a.length:a?I.error(e):c(e,s).slice(0)}function Q(e){for(var t=0,n=e.length,r="";t<n;t++)r+=e[t].value;return r}function J(a,e,t){var s=e.dir,u=e.next,l=u||s,c=t&&"parentNode"===l,f=n++;return e.first?function(e,t,n){while(e=e[s])if(1===e.nodeType||c)return a(e,t,n);return!1}:function(e,t,n){var r,i,o=[E,f];if(n){while(e=e[s])if((1===e.nodeType||c)&&a(e,t,n))return!0}else while(e=e[s])if(1===e.nodeType||c)if(i=e[S]||(e[S]={}),u&&fe(e,u))e=e[s]||e;else{if((r=i[l])&&r[0]===E&&r[1]===f)return o[2]=r[2];if((i[l]=o)[2]=a(e,t,n))return!0}return!1}}function K(i){return 1<i.length?function(e,t,n){var r=i.length;while(r--)if(!i[r](e,t,n))return!1;return!0}:i[0]}function Z(e,t,n,r,i){for(var o,a=[],s=0,u=e.length,l=null!=t;s<u;s++)(o=e[s])&&(n&&!n(o,r,i)||(a.push(o),l&&t.push(s)));return a}function ee(d,h,g,v,y,e){return v&&!v[S]&&(v=ee(v)),y&&!y[S]&&(y=ee(y,e)),F(function(e,t,n,r){var i,o,a,s,u=[],l=[],c=t.length,f=e||function(e,t,n){for(var r=0,i=t.length;r<i;r++)I(e,t[r],n);return n}(h||"*",n.nodeType?[n]:n,[]),p=!d||!e&&h?f:Z(f,u,d,n,r);if(g?g(p,s=y||(e?d:c||v)?[]:t,n,r):s=p,v){i=Z(s,l),v(i,[],n,r),o=i.length;while(o--)(a=i[o])&&(s[l[o]]=!(p[l[o]]=a))}if(e){if(y||d){if(y){i=[],o=s.length;while(o--)(a=s[o])&&i.push(p[o]=a);y(null,s=[],i,r)}o=s.length;while(o--)(a=s[o])&&-1<(i=y?se.call(e,a):u[o])&&(e[i]=!(t[i]=a))}}else s=Z(s===t?s.splice(c,s.length):s),y?y(null,t,s,r):k.apply(t,s)})}function te(e){for(var i,t,n,r=e.length,o=b.relative[e[0].type],a=o||b.relative[" "],s=o?1:0,u=J(function(e){return e===i},a,!0),l=J(function(e){return-1<se.call(i,e)},a,!0),c=[function(e,t,n){var r=!o&&(n||t!=w)||((i=t).nodeType?u(e,t,n):l(e,t,n));return i=null,r}];s<r;s++)if(t=b.relative[e[s].type])c=[J(K(c),t)];else{if((t=b.filter[e[s].type].apply(null,e[s].matches))[S]){for(n=++s;n<r;n++)if(b.relative[e[n].type])break;return ee(1<s&&K(c),1<s&&Q(e.slice(0,s-1).concat({value:" "===e[s-2].type?"*":""})).replace(ve,"$1"),t,s<n&&te(e.slice(s,n)),n<r&&te(e=e.slice(n)),n<r&&Q(e))}c.push(t)}return K(c)}function ne(e,t){var n,v,y,m,x,r,i=[],o=[],a=u[e+" "];if(!a){t||(t=Y(e)),n=t.length;while(n--)(a=te(t[n]))[S]?i.push(a):o.push(a);(a=u(e,(v=o,m=0<(y=i).length,x=0<v.length,r=function(e,t,n,r,i){var o,a,s,u=0,l="0",c=e&&[],f=[],p=w,d=e||x&&b.find.TAG("*",i),h=E+=null==p?1:Math.random()||.1,g=d.length;for(i&&(w=t==T||t||i);l!==g&&null!=(o=d[l]);l++){if(x&&o){a=0,t||o.ownerDocument==T||(V(o),n=!C);while(s=v[a++])if(s(o,t||T,n)){k.call(r,o);break}i&&(E=h)}m&&((o=!s&&o)&&u--,e&&c.push(o))}if(u+=l,m&&l!==u){a=0;while(s=y[a++])s(c,f,t,n);if(e){if(0<u)while(l--)c[l]||f[l]||(f[l]=pe.call(r));f=Z(f)}k.apply(r,f),i&&!e&&0<f.length&&1<u+y.length&&ce.uniqueSort(r)}return i&&(E=h,w=p),c},m?F(r):r))).selector=e}return a}function re(e,t,n,r){var i,o,a,s,u,l="function"==typeof e&&e,c=!r&&Y(e=l.selector||e);if(n=n||[],1===c.length){if(2<(o=c[0]=c[0].slice(0)).length&&"ID"===(a=o[0]).type&&9===t.nodeType&&C&&b.relative[o[1].type]){if(!(t=(b.find.ID(a.matches[0].replace(O,P),t)||[])[0]))return n;l&&(t=t.parentNode),e=e.slice(o.shift().value.length)}i=D.needsContext.test(e)?0:o.length;while(i--){if(a=o[i],b.relative[s=a.type])break;if((u=b.find[s])&&(r=u(a.matches[0].replace(O,P),H.test(o[0].type)&&U(t.parentNode)||t))){if(o.splice(i,1),!(e=r.length&&Q(o)))return k.apply(n,r),n;break}}}return(l||ne(e,c))(r,t,!C,n,!t||H.test(e)&&U(t.parentNode)||t),n}G.prototype=b.filters=b.pseudos,b.setFilters=new G,le.sortStable=S.split("").sort(l).join("")===S,V(),le.sortDetached=$(function(e){return 1&e.compareDocumentPosition(T.createElement("fieldset"))}),ce.find=I,ce.expr[":"]=ce.expr.pseudos,ce.unique=ce.uniqueSort,I.compile=ne,I.select=re,I.setDocument=V,I.tokenize=Y,I.escape=ce.escapeSelector,I.getText=ce.text,I.isXML=ce.isXMLDoc,I.selectors=ce.expr,I.support=ce.support,I.uniqueSort=ce.uniqueSort}();var d=function(e,t,n){var r=[],i=void 0!==n;while((e=e[t])&&9!==e.nodeType)if(1===e.nodeType){if(i&&ce(e).is(n))break;r.push(e)}return r},h=function(e,t){for(var n=[];e;e=e.nextSibling)1===e.nodeType&&e!==t&&n.push(e);return n},b=ce.expr.match.needsContext,w=/^<([a-z][^\/\0>:\x20\t\r\n\f]*)[\x20\t\r\n\f]*\/?>(?:<\/\1>|)$/i;function T(e,n,r){return v(n)?ce.grep(e,function(e,t){return!!n.call(e,t,e)!==r}):n.nodeType?ce.grep(e,function(e){return e===n!==r}):"string"!=typeof n?ce.grep(e,function(e){return-1<se.call(n,e)!==r}):ce.filter(n,e,r)}ce.filter=function(e,t,n){var r=t[0];return n&&(e=":not("+e+")"),1===t.length&&1===r.nodeType?ce.find.matchesSelector(r,e)?[r]:[]:ce.find.matches(e,ce.grep(t,function(e){return 1===e.nodeType}))},ce.fn.extend({find:function(e){var t,n,r=this.length,i=this;if("string"!=typeof e)return this.pushStack(ce(e).filter(function(){for(t=0;t<r;t++)if(ce.contains(i[t],this))return!0}));for(n=this.pushStack([]),t=0;t<r;t++)ce.find(e,i[t],n);return 1<r?ce.uniqueSort(n):n},filter:function(e){return this.pushStack(T(this,e||[],!1))},not:function(e){return this.pushStack(T(this,e||[],!0))},is:function(e){return!!T(this,"string"==typeof e&&b.test(e)?ce(e):e||[],!1).length}});var k,S=/^(?:\s*(<[\w\W]+>)[^>]*|#([\w-]+))$/;(ce.fn.init=function(e,t,n){var r,i;if(!e)return this;if(n=n||k,"string"==typeof e){if(!(r="<"===e[0]&&">"===e[e.length-1]&&3<=e.length?[null,e,null]:S.exec(e))||!r[1]&&t)return!t||t.jquery?(t||n).find(e):this.constructor(t).find(e);if(r[1]){if(t=t instanceof ce?t[0]:t,ce.merge(this,ce.parseHTML(r[1],t&&t.nodeType?t.ownerDocument||t:C,!0)),w.test(r[1])&&ce.isPlainObject(t))for(r in t)v(this[r])?this[r](t[r]):this.attr(r,t[r]);return this}return(i=C.getElementById(r[2]))&&(this[0]=i,this.length=1),this}return e.nodeType?(this[0]=e,this.length=1,this):v(e)?void 0!==n.ready?n.ready(e):e(ce):ce.makeArray(e,this)}).prototype=ce.fn,k=ce(C);var E=/^(?:parents|prev(?:Until|All))/,j={children:!0,contents:!0,next:!0,prev:!0};function A(e,t){while((e=e[t])&&1!==e.nodeType);return e}ce.fn.extend({has:function(e){var t=ce(e,this),n=t.length;return this.filter(function(){for(var e=0;e<n;e++)if(ce.contains(this,t[e]))return!0})},closest:function(e,t){var n,r=0,i=this.length,o=[],a="string"!=typeof e&&ce(e);if(!b.test(e))for(;r<i;r++)for(n=this[r];n&&n!==t;n=n.parentNode)if(n.nodeType<11&&(a?-1<a.index(n):1===n.nodeType&&ce.find.matchesSelector(n,e))){o.push(n);break}return this.pushStack(1<o.length?ce.uniqueSort(o):o)},index:function(e){return e?"string"==typeof e?se.call(ce(e),this[0]):se.call(this,e.jquery?e[0]:e):this[0]&&this[0].parentNode?this.first().prevAll().length:-1},add:function(e,t){return this.pushStack(ce.uniqueSort(ce.merge(this.get(),ce(e,t))))},addBack:function(e){return this.add(null==e?this.prevObject:this.prevObject.filter(e))}}),ce.each({parent:function(e){var t=e.parentNode;return t&&11!==t.nodeType?t:null},parents:function(e){return d(e,"parentNode")},parentsUntil:function(e,t,n){return d(e,"parentNode",n)},next:function(e){return A(e,"nextSibling")},prev:function(e){return A(e,"previousSibling")},nextAll:function(e){return d(e,"nextSibling")},prevAll:function(e){return d(e,"previousSibling")},nextUntil:function(e,t,n){return d(e,"nextSibling",n)},prevUntil:function(e,t,n){return d(e,"previousSibling",n)},siblings:function(e){return h((e.parentNode||{}).firstChild,e)},children:function(e){return h(e.firstChild)},contents:function(e){return null!=e.contentDocument&&r(e.contentDocument)?e.contentDocument:(fe(e,"template")&&(e=e.content||e),ce.merge([],e.childNodes))}},function(r,i){ce.fn[r]=function(e,t){var n=ce.map(this,i,e);return"Until"!==r.slice(-5)&&(t=e),t&&"string"==typeof t&&(n=ce.filter(t,n)),1<this.length&&(j[r]||ce.uniqueSort(n),E.test(r)&&n.reverse()),this.pushStack(n)}});var D=/[^\x20\t\r\n\f]+/g;function N(e){return e}function q(e){throw e}function L(e,t,n,r){var i;try{e&&v(i=e.promise)?i.call(e).done(t).fail(n):e&&v(i=e.then)?i.call(e,t,n):t.apply(void 0,[e].slice(r))}catch(e){n.apply(void 0,[e])}}ce.Callbacks=function(r){var e,n;r="string"==typeof r?(e=r,n={},ce.each(e.match(D)||[],function(e,t){n[t]=!0}),n):ce.extend({},r);var i,t,o,a,s=[],u=[],l=-1,c=function(){for(a=a||r.once,o=i=!0;u.length;l=-1){t=u.shift();while(++l<s.length)!1===s[l].apply(t[0],t[1])&&r.stopOnFalse&&(l=s.length,t=!1)}r.memory||(t=!1),i=!1,a&&(s=t?[]:"")},f={add:function(){return s&&(t&&!i&&(l=s.length-1,u.push(t)),function n(e){ce.each(e,function(e,t){v(t)?r.unique&&f.has(t)||s.push(t):t&&t.length&&"string"!==x(t)&&n(t)})}(arguments),t&&!i&&c()),this},remove:function(){return ce.each(arguments,function(e,t){var n;while(-1<(n=ce.inArray(t,s,n)))s.splice(n,1),n<=l&&l--}),this},has:function(e){return e?-1<ce.inArray(e,s):0<s.length},empty:function(){return s&&(s=[]),this},disable:function(){return a=u=[],s=t="",this},disabled:function(){return!s},lock:function(){return a=u=[],t||i||(s=t=""),this},locked:function(){return!!a},fireWith:function(e,t){return a||(t=[e,(t=t||[]).slice?t.slice():t],u.push(t),i||c()),this},fire:function(){return f.fireWith(this,arguments),this},fired:function(){return!!o}};return f},ce.extend({Deferred:function(e){var o=[["notify","progress",ce.Callbacks("memory"),ce.Callbacks("memory"),2],["resolve","done",ce.Callbacks("once memory"),ce.Callbacks("once memory"),0,"resolved"],["reject","fail",ce.Callbacks("once memory"),ce.Callbacks("once memory"),1,"rejected"]],i="pending",a={state:function(){return i},always:function(){return s.done(arguments).fail(arguments),this},"catch":function(e){return a.then(null,e)},pipe:function(){var i=arguments;return ce.Deferred(function(r){ce.each(o,function(e,t){var n=v(i[t[4]])&&i[t[4]];s[t[1]](function(){var e=n&&n.apply(this,arguments);e&&v(e.promise)?e.promise().progress(r.notify).done(r.resolve).fail(r.reject):r[t[0]+"With"](this,n?[e]:arguments)})}),i=null}).promise()},then:function(t,n,r){var u=0;function l(i,o,a,s){return function(){var n=this,r=arguments,e=function(){var e,t;if(!(i<u)){if((e=a.apply(n,r))===o.promise())throw new TypeError("Thenable self-resolution");t=e&&("object"==typeof e||"function"==typeof e)&&e.then,v(t)?s?t.call(e,l(u,o,N,s),l(u,o,q,s)):(u++,t.call(e,l(u,o,N,s),l(u,o,q,s),l(u,o,N,o.notifyWith))):(a!==N&&(n=void 0,r=[e]),(s||o.resolveWith)(n,r))}},t=s?e:function(){try{e()}catch(e){ce.Deferred.exceptionHook&&ce.Deferred.exceptionHook(e,t.error),u<=i+1&&(a!==q&&(n=void 0,r=[e]),o.rejectWith(n,r))}};i?t():(ce.Deferred.getErrorHook?t.error=ce.Deferred.getErrorHook():ce.Deferred.getStackHook&&(t.error=ce.Deferred.getStackHook()),ie.setTimeout(t))}}return ce.Deferred(function(e){o[0][3].add(l(0,e,v(r)?r:N,e.notifyWith)),o[1][3].add(l(0,e,v(t)?t:N)),o[2][3].add(l(0,e,v(n)?n:q))}).promise()},promise:function(e){return null!=e?ce.extend(e,a):a}},s={};return ce.each(o,function(e,t){var n=t[2],r=t[5];a[t[1]]=n.add,r&&n.add(function(){i=r},o[3-e][2].disable,o[3-e][3].disable,o[0][2].lock,o[0][3].lock),n.add(t[3].fire),s[t[0]]=function(){return s[t[0]+"With"](this===s?void 0:this,arguments),this},s[t[0]+"With"]=n.fireWith}),a.promise(s),e&&e.call(s,s),s},when:function(e){var n=arguments.length,t=n,r=Array(t),i=ae.call(arguments),o=ce.Deferred(),a=function(t){return function(e){r[t]=this,i[t]=1<arguments.length?ae.call(arguments):e,--n||o.resolveWith(r,i)}};if(n<=1&&(L(e,o.done(a(t)).resolve,o.reject,!n),"pending"===o.state()||v(i[t]&&i[t].then)))return o.then();while(t--)L(i[t],a(t),o.reject);return o.promise()}});var H=/^(Eval|Internal|Range|Reference|Syntax|Type|URI)Error$/;ce.Deferred.exceptionHook=function(e,t){ie.console&&ie.console.warn&&e&&H.test(e.name)&&ie.console.warn("jQuery.Deferred exception: "+e.message,e.stack,t)},ce.readyException=function(e){ie.setTimeout(function(){throw e})};var O=ce.Deferred();function P(){C.removeEventListener("DOMContentLoaded",P),ie.removeEventListener("load",P),ce.ready()}ce.fn.ready=function(e){return O.then(e)["catch"](function(e){ce.readyException(e)}),this},ce.extend({isReady:!1,readyWait:1,ready:function(e){(!0===e?--ce.readyWait:ce.isReady)||(ce.isReady=!0)!==e&&0<--ce.readyWait||O.resolveWith(C,[ce])}}),ce.ready.then=O.then,"complete"===C.readyState||"loading"!==C.readyState&&!C.documentElement.doScroll?ie.setTimeout(ce.ready):(C.addEventListener("DOMContentLoaded",P),ie.addEventListener("load",P));var M=function(e,t,n,r,i,o,a){var s=0,u=e.length,l=null==n;if("object"===x(n))for(s in i=!0,n)M(e,t,s,n[s],!0,o,a);else if(void 0!==r&&(i=!0,v(r)||(a=!0),l&&(a?(t.call(e,r),t=null):(l=t,t=function(e,t,n){return l.call(ce(e),n)})),t))for(;s<u;s++)t(e[s],n,a?r:r.call(e[s],s,t(e[s],n)));return i?e:l?t.call(e):u?t(e[0],n):o},R=/^-ms-/,I=/-([a-z])/g;function W(e,t){return t.toUpperCase()}function F(e){return e.replace(R,"ms-").replace(I,W)}var $=function(e){return 1===e.nodeType||9===e.nodeType||!+e.nodeType};function B(){this.expando=ce.expando+B.uid++}B.uid=1,B.prototype={cache:function(e){var t=e[this.expando];return t||(t={},$(e)&&(e.nodeType?e[this.expando]=t:Object.defineProperty(e,this.expando,{value:t,configurable:!0}))),t},set:function(e,t,n){var r,i=this.cache(e);if("string"==typeof t)i[F(t)]=n;else for(r in t)i[F(r)]=t[r];return i},get:function(e,t){return void 0===t?this.cache(e):e[this.expando]&&e[this.expando][F(t)]},access:function(e,t,n){return void 0===t||t&&"string"==typeof t&&void 0===n?this.get(e,t):(this.set(e,t,n),void 0!==n?n:t)},remove:function(e,t){var n,r=e[this.expando];if(void 0!==r){if(void 0!==t){n=(t=Array.isArray(t)?t.map(F):(t=F(t))in r?[t]:t.match(D)||[]).length;while(n--)delete r[t[n]]}(void 0===t||ce.isEmptyObject(r))&&(e.nodeType?e[this.expando]=void 0:delete e[this.expando])}},hasData:function(e){var t=e[this.expando];return void 0!==t&&!ce.isEmptyObject(t)}};var _=new B,z=new B,X=/^(?:\{[\w\W]*\}|\[[\w\W]*\])$/,U=/[A-Z]/g;function V(e,t,n){var r,i;if(void 0===n&&1===e.nodeType)if(r="data-"+t.replace(U,"-$&").toLowerCase(),"string"==typeof(n=e.getAttribute(r))){try{n="true"===(i=n)||"false"!==i&&("null"===i?null:i===+i+""?+i:X.test(i)?JSON.parse(i):i)}catch(e){}z.set(e,t,n)}else n=void 0;return n}ce.extend({hasData:function(e){return z.hasData(e)||_.hasData(e)},data:function(e,t,n){return z.access(e,t,n)},removeData:function(e,t){z.remove(e,t)},_data:function(e,t,n){return _.access(e,t,n)},_removeData:function(e,t){_.remove(e,t)}}),ce.fn.extend({data:function(n,e){var t,r,i,o=this[0],a=o&&o.attributes;if(void 0===n){if(this.length&&(i=z.get(o),1===o.nodeType&&!_.get(o,"hasDataAttrs"))){t=a.length;while(t--)a[t]&&0===(r=a[t].name).indexOf("data-")&&(r=F(r.slice(5)),V(o,r,i[r]));_.set(o,"hasDataAttrs",!0)}return i}return"object"==typeof n?this.each(function(){z.set(this,n)}):M(this,function(e){var t;if(o&&void 0===e)return void 0!==(t=z.get(o,n))?t:void 0!==(t=V(o,n))?t:void 0;this.each(function(){z.set(this,n,e)})},null,e,1<arguments.length,null,!0)},removeData:function(e){return this.each(function(){z.remove(this,e)})}}),ce.extend({queue:function(e,t,n){var r;if(e)return t=(t||"fx")+"queue",r=_.get(e,t),n&&(!r||Array.isArray(n)?r=_.access(e,t,ce.makeArray(n)):r.push(n)),r||[]},dequeue:function(e,t){t=t||"fx";var n=ce.queue(e,t),r=n.length,i=n.shift(),o=ce._queueHooks(e,t);"inprogress"===i&&(i=n.shift(),r--),i&&("fx"===t&&n.unshift("inprogress"),delete o.stop,i.call(e,function(){ce.dequeue(e,t)},o)),!r&&o&&o.empty.fire()},_queueHooks:function(e,t){var n=t+"queueHooks";return _.get(e,n)||_.access(e,n,{empty:ce.Callbacks("once memory").add(function(){_.remove(e,[t+"queue",n])})})}}),ce.fn.extend({queue:function(t,n){var e=2;return"string"!=typeof t&&(n=t,t="fx",e--),arguments.length<e?ce.queue(this[0],t):void 0===n?this:this.each(function(){var e=ce.queue(this,t,n);ce._queueHooks(this,t),"fx"===t&&"inprogress"!==e[0]&&ce.dequeue(this,t)})},dequeue:function(e){return this.each(function(){ce.dequeue(this,e)})},clearQueue:function(e){return this.queue(e||"fx",[])},promise:function(e,t){var n,r=1,i=ce.Deferred(),o=this,a=this.length,s=function(){--r||i.resolveWith(o,[o])};"string"!=typeof e&&(t=e,e=void 0),e=e||"fx";while(a--)(n=_.get(o[a],e+"queueHooks"))&&n.empty&&(r++,n.empty.add(s));return s(),i.promise(t)}});var G=/[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,Y=new RegExp("^(?:([+-])=|)("+G+")([a-z%]*)$","i"),Q=["Top","Right","Bottom","Left"],J=C.documentElement,K=function(e){return ce.contains(e.ownerDocument,e)},Z={composed:!0};J.getRootNode&&(K=function(e){return ce.contains(e.ownerDocument,e)||e.getRootNode(Z)===e.ownerDocument});var ee=function(e,t){return"none"===(e=t||e).style.display||""===e.style.display&&K(e)&&"none"===ce.css(e,"display")};function te(e,t,n,r){var i,o,a=20,s=r?function(){return r.cur()}:function(){return ce.css(e,t,"")},u=s(),l=n&&n[3]||(ce.cssNumber[t]?"":"px"),c=e.nodeType&&(ce.cssNumber[t]||"px"!==l&&+u)&&Y.exec(ce.css(e,t));if(c&&c[3]!==l){u/=2,l=l||c[3],c=+u||1;while(a--)ce.style(e,t,c+l),(1-o)*(1-(o=s()/u||.5))<=0&&(a=0),c/=o;c*=2,ce.style(e,t,c+l),n=n||[]}return n&&(c=+c||+u||0,i=n[1]?c+(n[1]+1)*n[2]:+n[2],r&&(r.unit=l,r.start=c,r.end=i)),i}var ne={};function re(e,t){for(var n,r,i,o,a,s,u,l=[],c=0,f=e.length;c<f;c++)(r=e[c]).style&&(n=r.style.display,t?("none"===n&&(l[c]=_.get(r,"display")||null,l[c]||(r.style.display="")),""===r.style.display&&ee(r)&&(l[c]=(u=a=o=void 0,a=(i=r).ownerDocument,s=i.nodeName,(u=ne[s])||(o=a.body.appendChild(a.createElement(s)),u=ce.css(o,"display"),o.parentNode.removeChild(o),"none"===u&&(u="block"),ne[s]=u)))):"none"!==n&&(l[c]="none",_.set(r,"display",n)));for(c=0;c<f;c++)null!=l[c]&&(e[c].style.display=l[c]);return e}ce.fn.extend({show:function(){return re(this,!0)},hide:function(){return re(this)},toggle:function(e){return"boolean"==typeof e?e?this.show():this.hide():this.each(function(){ee(this)?ce(this).show():ce(this).hide()})}});var xe,be,we=/^(?:checkbox|radio)$/i,Te=/<([a-z][^\/\0>\x20\t\r\n\f]*)/i,Ce=/^$|^module$|\/(?:java|ecma)script/i;xe=C.createDocumentFragment().appendChild(C.createElement("div")),(be=C.createElement("input")).setAttribute("type","radio"),be.setAttribute("checked","checked"),be.setAttribute("name","t"),xe.appendChild(be),le.checkClone=xe.cloneNode(!0).cloneNode(!0).lastChild.checked,xe.innerHTML="<textarea>x</textarea>",le.noCloneChecked=!!xe.cloneNode(!0).lastChild.defaultValue,xe.innerHTML="<option></option>",le.option=!!xe.lastChild;var ke={thead:[1,"<table>","</table>"],col:[2,"<table><colgroup>","</colgroup></table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],_default:[0,"",""]};function Se(e,t){var n;return n="undefined"!=typeof e.getElementsByTagName?e.getElementsByTagName(t||"*"):"undefined"!=typeof e.querySelectorAll?e.querySelectorAll(t||"*"):[],void 0===t||t&&fe(e,t)?ce.merge([e],n):n}function Ee(e,t){for(var n=0,r=e.length;n<r;n++)_.set(e[n],"globalEval",!t||_.get(t[n],"globalEval"))}ke.tbody=ke.tfoot=ke.colgroup=ke.caption=ke.thead,ke.th=ke.td,le.option||(ke.optgroup=ke.option=[1,"<select multiple='multiple'>","</select>"]);var je=/<|&#?\w+;/;function Ae(e,t,n,r,i){for(var o,a,s,u,l,c,f=t.createDocumentFragment(),p=[],d=0,h=e.length;d<h;d++)if((o=e[d])||0===o)if("object"===x(o))ce.merge(p,o.nodeType?[o]:o);else if(je.test(o)){a=a||f.appendChild(t.createElement("div")),s=(Te.exec(o)||["",""])[1].toLowerCase(),u=ke[s]||ke._default,a.innerHTML=u[1]+ce.htmlPrefilter(o)+u[2],c=u[0];while(c--)a=a.lastChild;ce.merge(p,a.childNodes),(a=f.firstChild).textContent=""}else p.push(t.createTextNode(o));f.textContent="",d=0;while(o=p[d++])if(r&&-1<ce.inArray(o,r))i&&i.push(o);else if(l=K(o),a=Se(f.appendChild(o),"script"),l&&Ee(a),n){c=0;while(o=a[c++])Ce.test(o.type||"")&&n.push(o)}return f}var De=/^([^.]*)(?:\.(.+)|)/;function Ne(){return!0}function qe(){return!1}function Le(e,t,n,r,i,o){var a,s;if("object"==typeof t){for(s in"string"!=typeof n&&(r=r||n,n=void 0),t)Le(e,s,n,r,t[s],o);return e}if(null==r&&null==i?(i=n,r=n=void 0):null==i&&("string"==typeof n?(i=r,r=void 0):(i=r,r=n,n=void 0)),!1===i)i=qe;else if(!i)return e;return 1===o&&(a=i,(i=function(e){return ce().off(e),a.apply(this,arguments)}).guid=a.guid||(a.guid=ce.guid++)),e.each(function(){ce.event.add(this,t,i,r,n)})}function He(e,r,t){t?(_.set(e,r,!1),ce.event.add(e,r,{namespace:!1,handler:function(e){var t,n=_.get(this,r);if(1&e.isTrigger&&this[r]){if(n)(ce.event.special[r]||{}).delegateType&&e.stopPropagation();else if(n=ae.call(arguments),_.set(this,r,n),this[r](),t=_.get(this,r),_.set(this,r,!1),n!==t)return e.stopImmediatePropagation(),e.preventDefault(),t}else n&&(_.set(this,r,ce.event.trigger(n[0],n.slice(1),this)),e.stopPropagation(),e.isImmediatePropagationStopped=Ne)}})):void 0===_.get(e,r)&&ce.event.add(e,r,Ne)}ce.event={global:{},add:function(t,e,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,v=_.get(t);if($(t)){n.handler&&(n=(o=n).handler,i=o.selector),i&&ce.find.matchesSelector(J,i),n.guid||(n.guid=ce.guid++),(u=v.events)||(u=v.events=Object.create(null)),(a=v.handle)||(a=v.handle=function(e){return"undefined"!=typeof ce&&ce.event.triggered!==e.type?ce.event.dispatch.apply(t,arguments):void 0}),l=(e=(e||"").match(D)||[""]).length;while(l--)d=g=(s=De.exec(e[l])||[])[1],h=(s[2]||"").split(".").sort(),d&&(f=ce.event.special[d]||{},d=(i?f.delegateType:f.bindType)||d,f=ce.event.special[d]||{},c=ce.extend({type:d,origType:g,data:r,handler:n,guid:n.guid,selector:i,needsContext:i&&ce.expr.match.needsContext.test(i),namespace:h.join(".")},o),(p=u[d])||((p=u[d]=[]).delegateCount=0,f.setup&&!1!==f.setup.call(t,r,h,a)||t.addEventListener&&t.addEventListener(d,a)),f.add&&(f.add.call(t,c),c.handler.guid||(c.handler.guid=n.guid)),i?p.splice(p.delegateCount++,0,c):p.push(c),ce.event.global[d]=!0)}},remove:function(e,t,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,v=_.hasData(e)&&_.get(e);if(v&&(u=v.events)){l=(t=(t||"").match(D)||[""]).length;while(l--)if(d=g=(s=De.exec(t[l])||[])[1],h=(s[2]||"").split(".").sort(),d){f=ce.event.special[d]||{},p=u[d=(r?f.delegateType:f.bindType)||d]||[],s=s[2]&&new RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"),a=o=p.length;while(o--)c=p[o],!i&&g!==c.origType||n&&n.guid!==c.guid||s&&!s.test(c.namespace)||r&&r!==c.selector&&("**"!==r||!c.selector)||(p.splice(o,1),c.selector&&p.delegateCount--,f.remove&&f.remove.call(e,c));a&&!p.length&&(f.teardown&&!1!==f.teardown.call(e,h,v.handle)||ce.removeEvent(e,d,v.handle),delete u[d])}else for(d in u)ce.event.remove(e,d+t[l],n,r,!0);ce.isEmptyObject(u)&&_.remove(e,"handle events")}},dispatch:function(e){var t,n,r,i,o,a,s=new Array(arguments.length),u=ce.event.fix(e),l=(_.get(this,"events")||Object.create(null))[u.type]||[],c=ce.event.special[u.type]||{};for(s[0]=u,t=1;t<arguments.length;t++)s[t]=arguments[t];if(u.delegateTarget=this,!c.preDispatch||!1!==c.preDispatch.call(this,u)){a=ce.event.handlers.call(this,u,l),t=0;while((i=a[t++])&&!u.isPropagationStopped()){u.currentTarget=i.elem,n=0;while((o=i.handlers[n++])&&!u.isImmediatePropagationStopped())u.rnamespace&&!1!==o.namespace&&!u.rnamespace.test(o.namespace)||(u.handleObj=o,u.data=o.data,void 0!==(r=((ce.event.special[o.origType]||{}).handle||o.handler).apply(i.elem,s))&&!1===(u.result=r)&&(u.preventDefault(),u.stopPropagation()))}return c.postDispatch&&c.postDispatch.call(this,u),u.result}},handlers:function(e,t){var n,r,i,o,a,s=[],u=t.delegateCount,l=e.target;if(u&&l.nodeType&&!("click"===e.type&&1<=e.button))for(;l!==this;l=l.parentNode||this)if(1===l.nodeType&&("click"!==e.type||!0!==l.disabled)){for(o=[],a={},n=0;n<u;n++)void 0===a[i=(r=t[n]).selector+" "]&&(a[i]=r.needsContext?-1<ce(i,this).index(l):ce.find(i,this,null,[l]).length),a[i]&&o.push(r);o.length&&s.push({elem:l,handlers:o})}return l=this,u<t.length&&s.push({elem:l,handlers:t.slice(u)}),s},addProp:function(t,e){Object.defineProperty(ce.Event.prototype,t,{enumerable:!0,configurable:!0,get:v(e)?function(){if(this.originalEvent)return e(this.originalEvent)}:function(){if(this.originalEvent)return this.originalEvent[t]},set:function(e){Object.defineProperty(this,t,{enumerable:!0,configurable:!0,writable:!0,value:e})}})},fix:function(e){return e[ce.expando]?e:new ce.Event(e)},special:{load:{noBubble:!0},click:{setup:function(e){var t=this||e;return we.test(t.type)&&t.click&&fe(t,"input")&&He(t,"click",!0),!1},trigger:function(e){var t=this||e;return we.test(t.type)&&t.click&&fe(t,"input")&&He(t,"click"),!0},_default:function(e){var t=e.target;return we.test(t.type)&&t.click&&fe(t,"input")&&_.get(t,"click")||fe(t,"a")}},beforeunload:{postDispatch:function(e){void 0!==e.result&&e.originalEvent&&(e.originalEvent.returnValue=e.result)}}}},ce.removeEvent=function(e,t,n){e.removeEventListener&&e.removeEventListener(t,n)},ce.Event=function(e,t){if(!(this instanceof ce.Event))return new ce.Event(e,t);e&&e.type?(this.originalEvent=e,this.type=e.type,this.isDefaultPrevented=e.defaultPrevented||void 0===e.defaultPrevented&&!1===e.returnValue?Ne:qe,this.target=e.target&&3===e.target.nodeType?e.target.parentNode:e.target,this.currentTarget=e.currentTarget,this.relatedTarget=e.relatedTarget):this.type=e,t&&ce.extend(this,t),this.timeStamp=e&&e.timeStamp||Date.now(),this[ce.expando]=!0},ce.Event.prototype={constructor:ce.Event,isDefaultPrevented:qe,isPropagationStopped:qe,isImmediatePropagationStopped:qe,isSimulated:!1,preventDefault:function(){var e=this.originalEvent;this.isDefaultPrevented=Ne,e&&!this.isSimulated&&e.preventDefault()},stopPropagation:function(){var e=this.originalEvent;this.isPropagationStopped=Ne,e&&!this.isSimulated&&e.stopPropagation()},stopImmediatePropagation:function(){var e=this.originalEvent;this.isImmediatePropagationStopped=Ne,e&&!this.isSimulated&&e.stopImmediatePropagation(),this.stopPropagation()}},ce.each({altKey:!0,bubbles:!0,cancelable:!0,changedTouches:!0,ctrlKey:!0,detail:!0,eventPhase:!0,metaKey:!0,pageX:!0,pageY:!0,shiftKey:!0,view:!0,"char":!0,code:!0,charCode:!0,key:!0,keyCode:!0,button:!0,buttons:!0,clientX:!0,clientY:!0,offsetX:!0,offsetY:!0,pointerId:!0,pointerType:!0,screenX:!0,screenY:!0,targetTouches:!0,toElement:!0,touches:!0,which:!0},ce.event.addProp),ce.each({focus:"focusin",blur:"focusout"},function(r,i){function o(e){if(C.documentMode){var t=_.get(this,"handle"),n=ce.event.fix(e);n.type="focusin"===e.type?"focus":"blur",n.isSimulated=!0,t(e),n.target===n.currentTarget&&t(n)}else ce.event.simulate(i,e.target,ce.event.fix(e))}ce.event.special[r]={setup:function(){var e;if(He(this,r,!0),!C.documentMode)return!1;(e=_.get(this,i))||this.addEventListener(i,o),_.set(this,i,(e||0)+1)},trigger:function(){return He(this,r),!0},teardown:function(){var e;if(!C.documentMode)return!1;(e=_.get(this,i)-1)?_.set(this,i,e):(this.removeEventListener(i,o),_.remove(this,i))},_default:function(e){return _.get(e.target,r)},delegateType:i},ce.event.special[i]={setup:function(){var e=this.ownerDocument||this.document||this,t=C.documentMode?this:e,n=_.get(t,i);n||(C.documentMode?this.addEventListener(i,o):e.addEventListener(r,o,!0)),_.set(t,i,(n||0)+1)},teardown:function(){var e=this.ownerDocument||this.document||this,t=C.documentMode?this:e,n=_.get(t,i)-1;n?_.set(t,i,n):(C.documentMode?this.removeEventListener(i,o):e.removeEventListener(r,o,!0),_.remove(t,i))}}}),ce.each({mouseenter:"mouseover",mouseleave:"mouseout",pointerenter:"pointerover",pointerleave:"pointerout"},function(e,i){ce.event.special[e]={delegateType:i,bindType:i,handle:function(e){var t,n=e.relatedTarget,r=e.handleObj;return n&&(n===this||ce.contains(this,n))||(e.type=r.origType,t=r.handler.apply(this,arguments),e.type=i),t}}}),ce.fn.extend({on:function(e,t,n,r){return Le(this,e,t,n,r)},one:function(e,t,n,r){return Le(this,e,t,n,r,1)},off:function(e,t,n){var r,i;if(e&&e.preventDefault&&e.handleObj)return r=e.handleObj,ce(e.delegateTarget).off(r.namespace?r.origType+"."+r.namespace:r.origType,r.selector,r.handler),this;if("object"==typeof e){for(i in e)this.off(i,t,e[i]);return this}return!1!==t&&"function"!=typeof t||(n=t,t=void 0),!1===n&&(n=qe),this.each(function(){ce.event.remove(this,e,n,t)})}});var Oe=/<script|<style|<link/i,Pe=/checked\s*(?:[^=]|=\s*.checked.)/i,Me=/^\s*<!\[CDATA\[|\]\]>\s*$/g;function Re(e,t){return fe(e,"table")&&fe(11!==t.nodeType?t:t.firstChild,"tr")&&ce(e).children("tbody")[0]||e}function Ie(e){return e.type=(null!==e.getAttribute("type"))+"/"+e.type,e}function We(e){return"true/"===(e.type||"").slice(0,5)?e.type=e.type.slice(5):e.removeAttribute("type"),e}function Fe(e,t){var n,r,i,o,a,s;if(1===t.nodeType){if(_.hasData(e)&&(s=_.get(e).events))for(i in _.remove(t,"handle events"),s)for(n=0,r=s[i].length;n<r;n++)ce.event.add(t,i,s[i][n]);z.hasData(e)&&(o=z.access(e),a=ce.extend({},o),z.set(t,a))}}function $e(n,r,i,o){r=g(r);var e,t,a,s,u,l,c=0,f=n.length,p=f-1,d=r[0],h=v(d);if(h||1<f&&"string"==typeof d&&!le.checkClone&&Pe.test(d))return n.each(function(e){var t=n.eq(e);h&&(r[0]=d.call(this,e,t.html())),$e(t,r,i,o)});if(f&&(t=(e=Ae(r,n[0].ownerDocument,!1,n,o)).firstChild,1===e.childNodes.length&&(e=t),t||o)){for(s=(a=ce.map(Se(e,"script"),Ie)).length;c<f;c++)u=e,c!==p&&(u=ce.clone(u,!0,!0),s&&ce.merge(a,Se(u,"script"))),i.call(n[c],u,c);if(s)for(l=a[a.length-1].ownerDocument,ce.map(a,We),c=0;c<s;c++)u=a[c],Ce.test(u.type||"")&&!_.access(u,"globalEval")&&ce.contains(l,u)&&(u.src&&"module"!==(u.type||"").toLowerCase()?ce._evalUrl&&!u.noModule&&ce._evalUrl(u.src,{nonce:u.nonce||u.getAttribute("nonce")},l):m(u.textContent.replace(Me,""),u,l))}return n}function Be(e,t,n){for(var r,i=t?ce.filter(t,e):e,o=0;null!=(r=i[o]);o++)n||1!==r.nodeType||ce.cleanData(Se(r)),r.parentNode&&(n&&K(r)&&Ee(Se(r,"script")),r.parentNode.removeChild(r));return e}ce.extend({htmlPrefilter:function(e){return e},clone:function(e,t,n){var r,i,o,a,s,u,l,c=e.cloneNode(!0),f=K(e);if(!(le.noCloneChecked||1!==e.nodeType&&11!==e.nodeType||ce.isXMLDoc(e)))for(a=Se(c),r=0,i=(o=Se(e)).length;r<i;r++)s=o[r],u=a[r],void 0,"input"===(l=u.nodeName.toLowerCase())&&we.test(s.type)?u.checked=s.checked:"input"!==l&&"textarea"!==l||(u.defaultValue=s.defaultValue);if(t)if(n)for(o=o||Se(e),a=a||Se(c),r=0,i=o.length;r<i;r++)Fe(o[r],a[r]);else Fe(e,c);return 0<(a=Se(c,"script")).length&&Ee(a,!f&&Se(e,"script")),c},cleanData:function(e){for(var t,n,r,i=ce.event.special,o=0;void 0!==(n=e[o]);o++)if($(n)){if(t=n[_.expando]){if(t.events)for(r in t.events)i[r]?ce.event.remove(n,r):ce.removeEvent(n,r,t.handle);n[_.expando]=void 0}n[z.expando]&&(n[z.expando]=void 0)}}}),ce.fn.extend({detach:function(e){return Be(this,e,!0)},remove:function(e){return Be(this,e)},text:function(e){return M(this,function(e){return void 0===e?ce.text(this):this.empty().each(function(){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||(this.textContent=e)})},null,e,arguments.length)},append:function(){return $e(this,arguments,function(e){1!==this.nodeType&&11!==this.nodeType&&9!==this.nodeType||Re(this,e).appendChild(e)})},prepend:function(){return $e(this,arguments,function(e){if(1===this.nodeType||11===this.nodeType||9===this.nodeType){var t=Re(this,e);t.insertBefore(e,t.firstChild)}})},before:function(){return $e(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this)})},after:function(){return $e(this,arguments,function(e){this.parentNode&&this.parentNode.insertBefore(e,this.nextSibling)})},empty:function(){for(var e,t=0;null!=(e=this[t]);t++)1===e.nodeType&&(ce.cleanData(Se(e,!1)),e.textContent="");return this},clone:function(e,t){return e=null!=e&&e,t=null==t?e:t,this.map(function(){return ce.clone(this,e,t)})},html:function(e){return M(this,function(e){var t=this[0]||{},n=0,r=this.length;if(void 0===e&&1===t.nodeType)return t.innerHTML;if("string"==typeof e&&!Oe.test(e)&&!ke[(Te.exec(e)||["",""])[1].toLowerCase()]){e=ce.htmlPrefilter(e);try{for(;n<r;n++)1===(t=this[n]||{}).nodeType&&(ce.cleanData(Se(t,!1)),t.innerHTML=e);t=0}catch(e){}}t&&this.empty().append(e)},null,e,arguments.length)},replaceWith:function(){var n=[];return $e(this,arguments,function(e){var t=this.parentNode;ce.inArray(this,n)<0&&(ce.cleanData(Se(this)),t&&t.replaceChild(e,this))},n)}}),ce.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(e,a){ce.fn[e]=function(e){for(var t,n=[],r=ce(e),i=r.length-1,o=0;o<=i;o++)t=o===i?this:this.clone(!0),ce(r[o])[a](t),s.apply(n,t.get());return this.pushStack(n)}});var _e=new RegExp("^("+G+")(?!px)[a-z%]+$","i"),ze=/^--/,Xe=function(e){var t=e.ownerDocument.defaultView;return t&&t.opener||(t=ie),t.getComputedStyle(e)},Ue=function(e,t,n){var r,i,o={};for(i in t)o[i]=e.style[i],e.style[i]=t[i];for(i in r=n.call(e),t)e.style[i]=o[i];return r},Ve=new RegExp(Q.join("|"),"i");function Ge(e,t,n){var r,i,o,a,s=ze.test(t),u=e.style;return(n=n||Xe(e))&&(a=n.getPropertyValue(t)||n[t],s&&a&&(a=a.replace(ve,"$1")||void 0),""!==a||K(e)||(a=ce.style(e,t)),!le.pixelBoxStyles()&&_e.test(a)&&Ve.test(t)&&(r=u.width,i=u.minWidth,o=u.maxWidth,u.minWidth=u.maxWidth=u.width=a,a=n.width,u.width=r,u.minWidth=i,u.maxWidth=o)),void 0!==a?a+"":a}function Ye(e,t){return{get:function(){if(!e())return(this.get=t).apply(this,arguments);delete this.get}}}!function(){function e(){if(l){u.style.cssText="position:absolute;left:-11111px;width:60px;margin-top:1px;padding:0;border:0",l.style.cssText="position:relative;display:block;box-sizing:border-box;overflow:scroll;margin:auto;border:1px;padding:1px;width:60%;top:1%",J.appendChild(u).appendChild(l);var e=ie.getComputedStyle(l);n="1%"!==e.top,s=12===t(e.marginLeft),l.style.right="60%",o=36===t(e.right),r=36===t(e.width),l.style.position="absolute",i=12===t(l.offsetWidth/3),J.removeChild(u),l=null}}function t(e){return Math.round(parseFloat(e))}var n,r,i,o,a,s,u=C.createElement("div"),l=C.createElement("div");l.style&&(l.style.backgroundClip="content-box",l.cloneNode(!0).style.backgroundClip="",le.clearCloneStyle="content-box"===l.style.backgroundClip,ce.extend(le,{boxSizingReliable:function(){return e(),r},pixelBoxStyles:function(){return e(),o},pixelPosition:function(){return e(),n},reliableMarginLeft:function(){return e(),s},scrollboxSize:function(){return e(),i},reliableTrDimensions:function(){var e,t,n,r;return null==a&&(e=C.createElement("table"),t=C.createElement("tr"),n=C.createElement("div"),e.style.cssText="position:absolute;left:-11111px;border-collapse:separate",t.style.cssText="box-sizing:content-box;border:1px solid",t.style.height="1px",n.style.height="9px",n.style.display="block",J.appendChild(e).appendChild(t).appendChild(n),r=ie.getComputedStyle(t),a=parseInt(r.height,10)+parseInt(r.borderTopWidth,10)+parseInt(r.borderBottomWidth,10)===t.offsetHeight,J.removeChild(e)),a}}))}();var Qe=["Webkit","Moz","ms"],Je=C.createElement("div").style,Ke={};function Ze(e){var t=ce.cssProps[e]||Ke[e];return t||(e in Je?e:Ke[e]=function(e){var t=e[0].toUpperCase()+e.slice(1),n=Qe.length;while(n--)if((e=Qe[n]+t)in Je)return e}(e)||e)}var et=/^(none|table(?!-c[ea]).+)/,tt={position:"absolute",visibility:"hidden",display:"block"},nt={letterSpacing:"0",fontWeight:"400"};function rt(e,t,n){var r=Y.exec(t);return r?Math.max(0,r[2]-(n||0))+(r[3]||"px"):t}function it(e,t,n,r,i,o){var a="width"===t?1:0,s=0,u=0,l=0;if(n===(r?"border":"content"))return 0;for(;a<4;a+=2)"margin"===n&&(l+=ce.css(e,n+Q[a],!0,i)),r?("content"===n&&(u-=ce.css(e,"padding"+Q[a],!0,i)),"margin"!==n&&(u-=ce.css(e,"border"+Q[a]+"Width",!0,i))):(u+=ce.css(e,"padding"+Q[a],!0,i),"padding"!==n?u+=ce.css(e,"border"+Q[a]+"Width",!0,i):s+=ce.css(e,"border"+Q[a]+"Width",!0,i));return!r&&0<=o&&(u+=Math.max(0,Math.ceil(e["offset"+t[0].toUpperCase()+t.slice(1)]-o-u-s-.5))||0),u+l}function ot(e,t,n){var r=Xe(e),i=(!le.boxSizingReliable()||n)&&"border-box"===ce.css(e,"boxSizing",!1,r),o=i,a=Ge(e,t,r),s="offset"+t[0].toUpperCase()+t.slice(1);if(_e.test(a)){if(!n)return a;a="auto"}return(!le.boxSizingReliable()&&i||!le.reliableTrDimensions()&&fe(e,"tr")||"auto"===a||!parseFloat(a)&&"inline"===ce.css(e,"display",!1,r))&&e.getClientRects().length&&(i="border-box"===ce.css(e,"boxSizing",!1,r),(o=s in e)&&(a=e[s])),(a=parseFloat(a)||0)+it(e,t,n||(i?"border":"content"),o,r,a)+"px"}function at(e,t,n,r,i){return new at.prototype.init(e,t,n,r,i)}ce.extend({cssHooks:{opacity:{get:function(e,t){if(t){var n=Ge(e,"opacity");return""===n?"1":n}}}},cssNumber:{animationIterationCount:!0,aspectRatio:!0,borderImageSlice:!0,columnCount:!0,flexGrow:!0,flexShrink:!0,fontWeight:!0,gridArea:!0,gridColumn:!0,gridColumnEnd:!0,gridColumnStart:!0,gridRow:!0,gridRowEnd:!0,gridRowStart:!0,lineHeight:!0,opacity:!0,order:!0,orphans:!0,scale:!0,widows:!0,zIndex:!0,zoom:!0,fillOpacity:!0,floodOpacity:!0,stopOpacity:!0,strokeMiterlimit:!0,strokeOpacity:!0},cssProps:{},style:function(e,t,n,r){if(e&&3!==e.nodeType&&8!==e.nodeType&&e.style){var i,o,a,s=F(t),u=ze.test(t),l=e.style;if(u||(t=Ze(s)),a=ce.cssHooks[t]||ce.cssHooks[s],void 0===n)return a&&"get"in a&&void 0!==(i=a.get(e,!1,r))?i:l[t];"string"===(o=typeof n)&&(i=Y.exec(n))&&i[1]&&(n=te(e,t,i),o="number"),null!=n&&n==n&&("number"!==o||u||(n+=i&&i[3]||(ce.cssNumber[s]?"":"px")),le.clearCloneStyle||""!==n||0!==t.indexOf("background")||(l[t]="inherit"),a&&"set"in a&&void 0===(n=a.set(e,n,r))||(u?l.setProperty(t,n):l[t]=n))}},css:function(e,t,n,r){var i,o,a,s=F(t);return ze.test(t)||(t=Ze(s)),(a=ce.cssHooks[t]||ce.cssHooks[s])&&"get"in a&&(i=a.get(e,!0,n)),void 0===i&&(i=Ge(e,t,r)),"normal"===i&&t in nt&&(i=nt[t]),""===n||n?(o=parseFloat(i),!0===n||isFinite(o)?o||0:i):i}}),ce.each(["height","width"],function(e,u){ce.cssHooks[u]={get:function(e,t,n){if(t)return!et.test(ce.css(e,"display"))||e.getClientRects().length&&e.getBoundingClientRect().width?ot(e,u,n):Ue(e,tt,function(){return ot(e,u,n)})},set:function(e,t,n){var r,i=Xe(e),o=!le.scrollboxSize()&&"absolute"===i.position,a=(o||n)&&"border-box"===ce.css(e,"boxSizing",!1,i),s=n?it(e,u,n,a,i):0;return a&&o&&(s-=Math.ceil(e["offset"+u[0].toUpperCase()+u.slice(1)]-parseFloat(i[u])-it(e,u,"border",!1,i)-.5)),s&&(r=Y.exec(t))&&"px"!==(r[3]||"px")&&(e.style[u]=t,t=ce.css(e,u)),rt(0,t,s)}}}),ce.cssHooks.marginLeft=Ye(le.reliableMarginLeft,function(e,t){if(t)return(parseFloat(Ge(e,"marginLeft"))||e.getBoundingClientRect().left-Ue(e,{marginLeft:0},function(){return e.getBoundingClientRect().left}))+"px"}),ce.each({margin:"",padding:"",border:"Width"},function(i,o){ce.cssHooks[i+o]={expand:function(e){for(var t=0,n={},r="string"==typeof e?e.split(" "):[e];t<4;t++)n[i+Q[t]+o]=r[t]||r[t-2]||r[0];return n}},"margin"!==i&&(ce.cssHooks[i+o].set=rt)}),ce.fn.extend({css:function(e,t){return M(this,function(e,t,n){var r,i,o={},a=0;if(Array.isArray(t)){for(r=Xe(e),i=t.length;a<i;a++)o[t[a]]=ce.css(e,t[a],!1,r);return o}return void 0!==n?ce.style(e,t,n):ce.css(e,t)},e,t,1<arguments.length)}}),((ce.Tween=at).prototype={constructor:at,init:function(e,t,n,r,i,o){this.elem=e,this.prop=n,this.easing=i||ce.easing._default,this.options=t,this.start=this.now=this.cur(),this.end=r,this.unit=o||(ce.cssNumber[n]?"":"px")},cur:function(){var e=at.propHooks[this.prop];return e&&e.get?e.get(this):at.propHooks._default.get(this)},run:function(e){var t,n=at.propHooks[this.prop];return this.options.duration?this.pos=t=ce.easing[this.easing](e,this.options.duration*e,0,1,this.options.duration):this.pos=t=e,this.now=(this.end-this.start)*t+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),n&&n.set?n.set(this):at.propHooks._default.set(this),this}}).init.prototype=at.prototype,(at.propHooks={_default:{get:function(e){var t;return 1!==e.elem.nodeType||null!=e.elem[e.prop]&&null==e.elem.style[e.prop]?e.elem[e.prop]:(t=ce.css(e.elem,e.prop,""))&&"auto"!==t?t:0},set:function(e){ce.fx.step[e.prop]?ce.fx.step[e.prop](e):1!==e.elem.nodeType||!ce.cssHooks[e.prop]&&null==e.elem.style[Ze(e.prop)]?e.elem[e.prop]=e.now:ce.style(e.elem,e.prop,e.now+e.unit)}}}).scrollTop=at.propHooks.scrollLeft={set:function(e){e.elem.nodeType&&e.elem.parentNode&&(e.elem[e.prop]=e.now)}},ce.easing={linear:function(e){return e},swing:function(e){return.5-Math.cos(e*Math.PI)/2},_default:"swing"},ce.fx=at.prototype.init,ce.fx.step={};var st,ut,lt,ct,ft=/^(?:toggle|show|hide)$/,pt=/queueHooks$/;function dt(){ut&&(!1===C.hidden&&ie.requestAnimationFrame?ie.requestAnimationFrame(dt):ie.setTimeout(dt,ce.fx.interval),ce.fx.tick())}function ht(){return ie.setTimeout(function(){st=void 0}),st=Date.now()}function gt(e,t){var n,r=0,i={height:e};for(t=t?1:0;r<4;r+=2-t)i["margin"+(n=Q[r])]=i["padding"+n]=e;return t&&(i.opacity=i.width=e),i}function vt(e,t,n){for(var r,i=(yt.tweeners[t]||[]).concat(yt.tweeners["*"]),o=0,a=i.length;o<a;o++)if(r=i[o].call(n,t,e))return r}function yt(o,e,t){var n,a,r=0,i=yt.prefilters.length,s=ce.Deferred().always(function(){delete u.elem}),u=function(){if(a)return!1;for(var e=st||ht(),t=Math.max(0,l.startTime+l.duration-e),n=1-(t/l.duration||0),r=0,i=l.tweens.length;r<i;r++)l.tweens[r].run(n);return s.notifyWith(o,[l,n,t]),n<1&&i?t:(i||s.notifyWith(o,[l,1,0]),s.resolveWith(o,[l]),!1)},l=s.promise({elem:o,props:ce.extend({},e),opts:ce.extend(!0,{specialEasing:{},easing:ce.easing._default},t),originalProperties:e,originalOptions:t,startTime:st||ht(),duration:t.duration,tweens:[],createTween:function(e,t){var n=ce.Tween(o,l.opts,e,t,l.opts.specialEasing[e]||l.opts.easing);return l.tweens.push(n),n},stop:function(e){var t=0,n=e?l.tweens.length:0;if(a)return this;for(a=!0;t<n;t++)l.tweens[t].run(1);return e?(s.notifyWith(o,[l,1,0]),s.resolveWith(o,[l,e])):s.rejectWith(o,[l,e]),this}}),c=l.props;for(!function(e,t){var n,r,i,o,a;for(n in e)if(i=t[r=F(n)],o=e[n],Array.isArray(o)&&(i=o[1],o=e[n]=o[0]),n!==r&&(e[r]=o,delete e[n]),(a=ce.cssHooks[r])&&"expand"in a)for(n in o=a.expand(o),delete e[r],o)n in e||(e[n]=o[n],t[n]=i);else t[r]=i}(c,l.opts.specialEasing);r<i;r++)if(n=yt.prefilters[r].call(l,o,c,l.opts))return v(n.stop)&&(ce._queueHooks(l.elem,l.opts.queue).stop=n.stop.bind(n)),n;return ce.map(c,vt,l),v(l.opts.start)&&l.opts.start.call(o,l),l.progress(l.opts.progress).done(l.opts.done,l.opts.complete).fail(l.opts.fail).always(l.opts.always),ce.fx.timer(ce.extend(u,{elem:o,anim:l,queue:l.opts.queue})),l}ce.Animation=ce.extend(yt,{tweeners:{"*":[function(e,t){var n=this.createTween(e,t);return te(n.elem,e,Y.exec(t),n),n}]},tweener:function(e,t){v(e)?(t=e,e=["*"]):e=e.match(D);for(var n,r=0,i=e.length;r<i;r++)n=e[r],yt.tweeners[n]=yt.tweeners[n]||[],yt.tweeners[n].unshift(t)},prefilters:[function(e,t,n){var r,i,o,a,s,u,l,c,f="width"in t||"height"in t,p=this,d={},h=e.style,g=e.nodeType&&ee(e),v=_.get(e,"fxshow");for(r in n.queue||(null==(a=ce._queueHooks(e,"fx")).unqueued&&(a.unqueued=0,s=a.empty.fire,a.empty.fire=function(){a.unqueued||s()}),a.unqueued++,p.always(function(){p.always(function(){a.unqueued--,ce.queue(e,"fx").length||a.empty.fire()})})),t)if(i=t[r],ft.test(i)){if(delete t[r],o=o||"toggle"===i,i===(g?"hide":"show")){if("show"!==i||!v||void 0===v[r])continue;g=!0}d[r]=v&&v[r]||ce.style(e,r)}if((u=!ce.isEmptyObject(t))||!ce.isEmptyObject(d))for(r in f&&1===e.nodeType&&(n.overflow=[h.overflow,h.overflowX,h.overflowY],null==(l=v&&v.display)&&(l=_.get(e,"display")),"none"===(c=ce.css(e,"display"))&&(l?c=l:(re([e],!0),l=e.style.display||l,c=ce.css(e,"display"),re([e]))),("inline"===c||"inline-block"===c&&null!=l)&&"none"===ce.css(e,"float")&&(u||(p.done(function(){h.display=l}),null==l&&(c=h.display,l="none"===c?"":c)),h.display="inline-block")),n.overflow&&(h.overflow="hidden",p.always(function(){h.overflow=n.overflow[0],h.overflowX=n.overflow[1],h.overflowY=n.overflow[2]})),u=!1,d)u||(v?"hidden"in v&&(g=v.hidden):v=_.access(e,"fxshow",{display:l}),o&&(v.hidden=!g),g&&re([e],!0),p.done(function(){for(r in g||re([e]),_.remove(e,"fxshow"),d)ce.style(e,r,d[r])})),u=vt(g?v[r]:0,r,p),r in v||(v[r]=u.start,g&&(u.end=u.start,u.start=0))}],prefilter:function(e,t){t?yt.prefilters.unshift(e):yt.prefilters.push(e)}}),ce.speed=function(e,t,n){var r=e&&"object"==typeof e?ce.extend({},e):{complete:n||!n&&t||v(e)&&e,duration:e,easing:n&&t||t&&!v(t)&&t};return ce.fx.off?r.duration=0:"number"!=typeof r.duration&&(r.duration in ce.fx.speeds?r.duration=ce.fx.speeds[r.duration]:r.duration=ce.fx.speeds._default),null!=r.queue&&!0!==r.queue||(r.queue="fx"),r.old=r.complete,r.complete=function(){v(r.old)&&r.old.call(this),r.queue&&ce.dequeue(this,r.queue)},r},ce.fn.extend({fadeTo:function(e,t,n,r){return this.filter(ee).css("opacity",0).show().end().animate({opacity:t},e,n,r)},animate:function(t,e,n,r){var i=ce.isEmptyObject(t),o=ce.speed(e,n,r),a=function(){var e=yt(this,ce.extend({},t),o);(i||_.get(this,"finish"))&&e.stop(!0)};return a.finish=a,i||!1===o.queue?this.each(a):this.queue(o.queue,a)},stop:function(i,e,o){var a=function(e){var t=e.stop;delete e.stop,t(o)};return"string"!=typeof i&&(o=e,e=i,i=void 0),e&&this.queue(i||"fx",[]),this.each(function(){var e=!0,t=null!=i&&i+"queueHooks",n=ce.timers,r=_.get(this);if(t)r[t]&&r[t].stop&&a(r[t]);else for(t in r)r[t]&&r[t].stop&&pt.test(t)&&a(r[t]);for(t=n.length;t--;)n[t].elem!==this||null!=i&&n[t].queue!==i||(n[t].anim.stop(o),e=!1,n.splice(t,1));!e&&o||ce.dequeue(this,i)})},finish:function(a){return!1!==a&&(a=a||"fx"),this.each(function(){var e,t=_.get(this),n=t[a+"queue"],r=t[a+"queueHooks"],i=ce.timers,o=n?n.length:0;for(t.finish=!0,ce.queue(this,a,[]),r&&r.stop&&r.stop.call(this,!0),e=i.length;e--;)i[e].elem===this&&i[e].queue===a&&(i[e].anim.stop(!0),i.splice(e,1));for(e=0;e<o;e++)n[e]&&n[e].finish&&n[e].finish.call(this);delete t.finish})}}),ce.each(["toggle","show","hide"],function(e,r){var i=ce.fn[r];ce.fn[r]=function(e,t,n){return null==e||"boolean"==typeof e?i.apply(this,arguments):this.animate(gt(r,!0),e,t,n)}}),ce.each({slideDown:gt("show"),slideUp:gt("hide"),slideToggle:gt("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(e,r){ce.fn[e]=function(e,t,n){return this.animate(r,e,t,n)}}),ce.timers=[],ce.fx.tick=function(){var e,t=0,n=ce.timers;for(st=Date.now();t<n.length;t++)(e=n[t])()||n[t]!==e||n.splice(t--,1);n.length||ce.fx.stop(),st=void 0},ce.fx.timer=function(e){ce.timers.push(e),ce.fx.start()},ce.fx.interval=13,ce.fx.start=function(){ut||(ut=!0,dt())},ce.fx.stop=function(){ut=null},ce.fx.speeds={slow:600,fast:200,_default:400},ce.fn.delay=function(r,e){return r=ce.fx&&ce.fx.speeds[r]||r,e=e||"fx",this.queue(e,function(e,t){var n=ie.setTimeout(e,r);t.stop=function(){ie.clearTimeout(n)}})},lt=C.createElement("input"),ct=C.createElement("select").appendChild(C.createElement("option")),lt.type="checkbox",le.checkOn=""!==lt.value,le.optSelected=ct.selected,(lt=C.createElement("input")).value="t",lt.type="radio",le.radioValue="t"===lt.value;var mt,xt=ce.expr.attrHandle;ce.fn.extend({attr:function(e,t){return M(this,ce.attr,e,t,1<arguments.length)},removeAttr:function(e){return this.each(function(){ce.removeAttr(this,e)})}}),ce.extend({attr:function(e,t,n){var r,i,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return"undefined"==typeof e.getAttribute?ce.prop(e,t,n):(1===o&&ce.isXMLDoc(e)||(i=ce.attrHooks[t.toLowerCase()]||(ce.expr.match.bool.test(t)?mt:void 0)),void 0!==n?null===n?void ce.removeAttr(e,t):i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:(e.setAttribute(t,n+""),n):i&&"get"in i&&null!==(r=i.get(e,t))?r:null==(r=ce.find.attr(e,t))?void 0:r)},attrHooks:{type:{set:function(e,t){if(!le.radioValue&&"radio"===t&&fe(e,"input")){var n=e.value;return e.setAttribute("type",t),n&&(e.value=n),t}}}},removeAttr:function(e,t){var n,r=0,i=t&&t.match(D);if(i&&1===e.nodeType)while(n=i[r++])e.removeAttribute(n)}}),mt={set:function(e,t,n){return!1===t?ce.removeAttr(e,n):e.setAttribute(n,n),n}},ce.each(ce.expr.match.bool.source.match(/\w+/g),function(e,t){var a=xt[t]||ce.find.attr;xt[t]=function(e,t,n){var r,i,o=t.toLowerCase();return n||(i=xt[o],xt[o]=r,r=null!=a(e,t,n)?o:null,xt[o]=i),r}});var bt=/^(?:input|select|textarea|button)$/i,wt=/^(?:a|area)$/i;function Tt(e){return(e.match(D)||[]).join(" ")}function Ct(e){return e.getAttribute&&e.getAttribute("class")||""}function kt(e){return Array.isArray(e)?e:"string"==typeof e&&e.match(D)||[]}ce.fn.extend({prop:function(e,t){return M(this,ce.prop,e,t,1<arguments.length)},removeProp:function(e){return this.each(function(){delete this[ce.propFix[e]||e]})}}),ce.extend({prop:function(e,t,n){var r,i,o=e.nodeType;if(3!==o&&8!==o&&2!==o)return 1===o&&ce.isXMLDoc(e)||(t=ce.propFix[t]||t,i=ce.propHooks[t]),void 0!==n?i&&"set"in i&&void 0!==(r=i.set(e,n,t))?r:e[t]=n:i&&"get"in i&&null!==(r=i.get(e,t))?r:e[t]},propHooks:{tabIndex:{get:function(e){var t=ce.find.attr(e,"tabindex");return t?parseInt(t,10):bt.test(e.nodeName)||wt.test(e.nodeName)&&e.href?0:-1}}},propFix:{"for":"htmlFor","class":"className"}}),le.optSelected||(ce.propHooks.selected={get:function(e){var t=e.parentNode;return t&&t.parentNode&&t.parentNode.selectedIndex,null},set:function(e){var t=e.parentNode;t&&(t.selectedIndex,t.parentNode&&t.parentNode.selectedIndex)}}),ce.each(["tabIndex","readOnly","maxLength","cellSpacing","cellPadding","rowSpan","colSpan","useMap","frameBorder","contentEditable"],function(){ce.propFix[this.toLowerCase()]=this}),ce.fn.extend({addClass:function(t){var e,n,r,i,o,a;return v(t)?this.each(function(e){ce(this).addClass(t.call(this,e,Ct(this)))}):(e=kt(t)).length?this.each(function(){if(r=Ct(this),n=1===this.nodeType&&" "+Tt(r)+" "){for(o=0;o<e.length;o++)i=e[o],n.indexOf(" "+i+" ")<0&&(n+=i+" ");a=Tt(n),r!==a&&this.setAttribute("class",a)}}):this},removeClass:function(t){var e,n,r,i,o,a;return v(t)?this.each(function(e){ce(this).removeClass(t.call(this,e,Ct(this)))}):arguments.length?(e=kt(t)).length?this.each(function(){if(r=Ct(this),n=1===this.nodeType&&" "+Tt(r)+" "){for(o=0;o<e.length;o++){i=e[o];while(-1<n.indexOf(" "+i+" "))n=n.replace(" "+i+" "," ")}a=Tt(n),r!==a&&this.setAttribute("class",a)}}):this:this.attr("class","")},toggleClass:function(t,n){var e,r,i,o,a=typeof t,s="string"===a||Array.isArray(t);return v(t)?this.each(function(e){ce(this).toggleClass(t.call(this,e,Ct(this),n),n)}):"boolean"==typeof n&&s?n?this.addClass(t):this.removeClass(t):(e=kt(t),this.each(function(){if(s)for(o=ce(this),i=0;i<e.length;i++)r=e[i],o.hasClass(r)?o.removeClass(r):o.addClass(r);else void 0!==t&&"boolean"!==a||((r=Ct(this))&&_.set(this,"__className__",r),this.setAttribute&&this.setAttribute("class",r||!1===t?"":_.get(this,"__className__")||""))}))},hasClass:function(e){var t,n,r=0;t=" "+e+" ";while(n=this[r++])if(1===n.nodeType&&-1<(" "+Tt(Ct(n))+" ").indexOf(t))return!0;return!1}});var St=/\r/g;ce.fn.extend({val:function(n){var r,e,i,t=this[0];return arguments.length?(i=v(n),this.each(function(e){var t;1===this.nodeType&&(null==(t=i?n.call(this,e,ce(this).val()):n)?t="":"number"==typeof t?t+="":Array.isArray(t)&&(t=ce.map(t,function(e){return null==e?"":e+""})),(r=ce.valHooks[this.type]||ce.valHooks[this.nodeName.toLowerCase()])&&"set"in r&&void 0!==r.set(this,t,"value")||(this.value=t))})):t?(r=ce.valHooks[t.type]||ce.valHooks[t.nodeName.toLowerCase()])&&"get"in r&&void 0!==(e=r.get(t,"value"))?e:"string"==typeof(e=t.value)?e.replace(St,""):null==e?"":e:void 0}}),ce.extend({valHooks:{option:{get:function(e){var t=ce.find.attr(e,"value");return null!=t?t:Tt(ce.text(e))}},select:{get:function(e){var t,n,r,i=e.options,o=e.selectedIndex,a="select-one"===e.type,s=a?null:[],u=a?o+1:i.length;for(r=o<0?u:a?o:0;r<u;r++)if(((n=i[r]).selected||r===o)&&!n.disabled&&(!n.parentNode.disabled||!fe(n.parentNode,"optgroup"))){if(t=ce(n).val(),a)return t;s.push(t)}return s},set:function(e,t){var n,r,i=e.options,o=ce.makeArray(t),a=i.length;while(a--)((r=i[a]).selected=-1<ce.inArray(ce.valHooks.option.get(r),o))&&(n=!0);return n||(e.selectedIndex=-1),o}}}}),ce.each(["radio","checkbox"],function(){ce.valHooks[this]={set:function(e,t){if(Array.isArray(t))return e.checked=-1<ce.inArray(ce(e).val(),t)}},le.checkOn||(ce.valHooks[this].get=function(e){return null===e.getAttribute("value")?"on":e.value})});var Et=ie.location,jt={guid:Date.now()},At=/\?/;ce.parseXML=function(e){var t,n;if(!e||"string"!=typeof e)return null;try{t=(new ie.DOMParser).parseFromString(e,"text/xml")}catch(e){}return n=t&&t.getElementsByTagName("parsererror")[0],t&&!n||ce.error("Invalid XML: "+(n?ce.map(n.childNodes,function(e){return e.textContent}).join("\n"):e)),t};var Dt=/^(?:focusinfocus|focusoutblur)$/,Nt=function(e){e.stopPropagation()};ce.extend(ce.event,{trigger:function(e,t,n,r){var i,o,a,s,u,l,c,f,p=[n||C],d=ue.call(e,"type")?e.type:e,h=ue.call(e,"namespace")?e.namespace.split("."):[];if(o=f=a=n=n||C,3!==n.nodeType&&8!==n.nodeType&&!Dt.test(d+ce.event.triggered)&&(-1<d.indexOf(".")&&(d=(h=d.split(".")).shift(),h.sort()),u=d.indexOf(":")<0&&"on"+d,(e=e[ce.expando]?e:new ce.Event(d,"object"==typeof e&&e)).isTrigger=r?2:3,e.namespace=h.join("."),e.rnamespace=e.namespace?new RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,e.result=void 0,e.target||(e.target=n),t=null==t?[e]:ce.makeArray(t,[e]),c=ce.event.special[d]||{},r||!c.trigger||!1!==c.trigger.apply(n,t))){if(!r&&!c.noBubble&&!y(n)){for(s=c.delegateType||d,Dt.test(s+d)||(o=o.parentNode);o;o=o.parentNode)p.push(o),a=o;a===(n.ownerDocument||C)&&p.push(a.defaultView||a.parentWindow||ie)}i=0;while((o=p[i++])&&!e.isPropagationStopped())f=o,e.type=1<i?s:c.bindType||d,(l=(_.get(o,"events")||Object.create(null))[e.type]&&_.get(o,"handle"))&&l.apply(o,t),(l=u&&o[u])&&l.apply&&$(o)&&(e.result=l.apply(o,t),!1===e.result&&e.preventDefault());return e.type=d,r||e.isDefaultPrevented()||c._default&&!1!==c._default.apply(p.pop(),t)||!$(n)||u&&v(n[d])&&!y(n)&&((a=n[u])&&(n[u]=null),ce.event.triggered=d,e.isPropagationStopped()&&f.addEventListener(d,Nt),n[d](),e.isPropagationStopped()&&f.removeEventListener(d,Nt),ce.event.triggered=void 0,a&&(n[u]=a)),e.result}},simulate:function(e,t,n){var r=ce.extend(new ce.Event,n,{type:e,isSimulated:!0});ce.event.trigger(r,null,t)}}),ce.fn.extend({trigger:function(e,t){return this.each(function(){ce.event.trigger(e,t,this)})},triggerHandler:function(e,t){var n=this[0];if(n)return ce.event.trigger(e,t,n,!0)}});var qt=/\[\]$/,Lt=/\r?\n/g,Ht=/^(?:submit|button|image|reset|file)$/i,Ot=/^(?:input|select|textarea|keygen)/i;function Pt(n,e,r,i){var t;if(Array.isArray(e))ce.each(e,function(e,t){r||qt.test(n)?i(n,t):Pt(n+"["+("object"==typeof t&&null!=t?e:"")+"]",t,r,i)});else if(r||"object"!==x(e))i(n,e);else for(t in e)Pt(n+"["+t+"]",e[t],r,i)}ce.param=function(e,t){var n,r=[],i=function(e,t){var n=v(t)?t():t;r[r.length]=encodeURIComponent(e)+"="+encodeURIComponent(null==n?"":n)};if(null==e)return"";if(Array.isArray(e)||e.jquery&&!ce.isPlainObject(e))ce.each(e,function(){i(this.name,this.value)});else for(n in e)Pt(n,e[n],t,i);return r.join("&")},ce.fn.extend({serialize:function(){return ce.param(this.serializeArray())},serializeArray:function(){return this.map(function(){var e=ce.prop(this,"elements");return e?ce.makeArray(e):this}).filter(function(){var e=this.type;return this.name&&!ce(this).is(":disabled")&&Ot.test(this.nodeName)&&!Ht.test(e)&&(this.checked||!we.test(e))}).map(function(e,t){var n=ce(this).val();return null==n?null:Array.isArray(n)?ce.map(n,function(e){return{name:t.name,value:e.replace(Lt,"\r\n")}}):{name:t.name,value:n.replace(Lt,"\r\n")}}).get()}});var Mt=/%20/g,Rt=/#.*$/,It=/([?&])_=[^&]*/,Wt=/^(.*?):[ \t]*([^\r\n]*)$/gm,Ft=/^(?:GET|HEAD)$/,$t=/^\/\//,Bt={},_t={},zt="*/".concat("*"),Xt=C.createElement("a");function Ut(o){return function(e,t){"string"!=typeof e&&(t=e,e="*");var n,r=0,i=e.toLowerCase().match(D)||[];if(v(t))while(n=i[r++])"+"===n[0]?(n=n.slice(1)||"*",(o[n]=o[n]||[]).unshift(t)):(o[n]=o[n]||[]).push(t)}}function Vt(t,i,o,a){var s={},u=t===_t;function l(e){var r;return s[e]=!0,ce.each(t[e]||[],function(e,t){var n=t(i,o,a);return"string"!=typeof n||u||s[n]?u?!(r=n):void 0:(i.dataTypes.unshift(n),l(n),!1)}),r}return l(i.dataTypes[0])||!s["*"]&&l("*")}function Gt(e,t){var n,r,i=ce.ajaxSettings.flatOptions||{};for(n in t)void 0!==t[n]&&((i[n]?e:r||(r={}))[n]=t[n]);return r&&ce.extend(!0,e,r),e}Xt.href=Et.href,ce.extend({active:0,lastModified:{},etag:{},ajaxSettings:{url:Et.href,type:"GET",isLocal:/^(?:about|app|app-storage|.+-extension|file|res|widget):$/.test(Et.protocol),global:!0,processData:!0,async:!0,contentType:"application/x-www-form-urlencoded; charset=UTF-8",accepts:{"*":zt,text:"text/plain",html:"text/html",xml:"application/xml, text/xml",json:"application/json, text/javascript"},contents:{xml:/\bxml\b/,html:/\bhtml/,json:/\bjson\b/},responseFields:{xml:"responseXML",text:"responseText",json:"responseJSON"},converters:{"* text":String,"text html":!0,"text json":JSON.parse,"text xml":ce.parseXML},flatOptions:{url:!0,context:!0}},ajaxSetup:function(e,t){return t?Gt(Gt(e,ce.ajaxSettings),t):Gt(ce.ajaxSettings,e)},ajaxPrefilter:Ut(Bt),ajaxTransport:Ut(_t),ajax:function(e,t){"object"==typeof e&&(t=e,e=void 0),t=t||{};var c,f,p,n,d,r,h,g,i,o,v=ce.ajaxSetup({},t),y=v.context||v,m=v.context&&(y.nodeType||y.jquery)?ce(y):ce.event,x=ce.Deferred(),b=ce.Callbacks("once memory"),w=v.statusCode||{},a={},s={},u="canceled",T={readyState:0,getResponseHeader:function(e){var t;if(h){if(!n){n={};while(t=Wt.exec(p))n[t[1].toLowerCase()+" "]=(n[t[1].toLowerCase()+" "]||[]).concat(t[2])}t=n[e.toLowerCase()+" "]}return null==t?null:t.join(", ")},getAllResponseHeaders:function(){return h?p:null},setRequestHeader:function(e,t){return null==h&&(e=s[e.toLowerCase()]=s[e.toLowerCase()]||e,a[e]=t),this},overrideMimeType:function(e){return null==h&&(v.mimeType=e),this},statusCode:function(e){var t;if(e)if(h)T.always(e[T.status]);else for(t in e)w[t]=[w[t],e[t]];return this},abort:function(e){var t=e||u;return c&&c.abort(t),l(0,t),this}};if(x.promise(T),v.url=((e||v.url||Et.href)+"").replace($t,Et.protocol+"//"),v.type=t.method||t.type||v.method||v.type,v.dataTypes=(v.dataType||"*").toLowerCase().match(D)||[""],null==v.crossDomain){r=C.createElement("a");try{r.href=v.url,r.href=r.href,v.crossDomain=Xt.protocol+"//"+Xt.host!=r.protocol+"//"+r.host}catch(e){v.crossDomain=!0}}if(v.data&&v.processData&&"string"!=typeof v.data&&(v.data=ce.param(v.data,v.traditional)),Vt(Bt,v,t,T),h)return T;for(i in(g=ce.event&&v.global)&&0==ce.active++&&ce.event.trigger("ajaxStart"),v.type=v.type.toUpperCase(),v.hasContent=!Ft.test(v.type),f=v.url.replace(Rt,""),v.hasContent?v.data&&v.processData&&0===(v.contentType||"").indexOf("application/x-www-form-urlencoded")&&(v.data=v.data.replace(Mt,"+")):(o=v.url.slice(f.length),v.data&&(v.processData||"string"==typeof v.data)&&(f+=(At.test(f)?"&":"?")+v.data,delete v.data),!1===v.cache&&(f=f.replace(It,"$1"),o=(At.test(f)?"&":"?")+"_="+jt.guid+++o),v.url=f+o),v.ifModified&&(ce.lastModified[f]&&T.setRequestHeader("If-Modified-Since",ce.lastModified[f]),ce.etag[f]&&T.setRequestHeader("If-None-Match",ce.etag[f])),(v.data&&v.hasContent&&!1!==v.contentType||t.contentType)&&T.setRequestHeader("Content-Type",v.contentType),T.setRequestHeader("Accept",v.dataTypes[0]&&v.accepts[v.dataTypes[0]]?v.accepts[v.dataTypes[0]]+("*"!==v.dataTypes[0]?", "+zt+"; q=0.01":""):v.accepts["*"]),v.headers)T.setRequestHeader(i,v.headers[i]);if(v.beforeSend&&(!1===v.beforeSend.call(y,T,v)||h))return T.abort();if(u="abort",b.add(v.complete),T.done(v.success),T.fail(v.error),c=Vt(_t,v,t,T)){if(T.readyState=1,g&&m.trigger("ajaxSend",[T,v]),h)return T;v.async&&0<v.timeout&&(d=ie.setTimeout(function(){T.abort("timeout")},v.timeout));try{h=!1,c.send(a,l)}catch(e){if(h)throw e;l(-1,e)}}else l(-1,"No Transport");function l(e,t,n,r){var i,o,a,s,u,l=t;h||(h=!0,d&&ie.clearTimeout(d),c=void 0,p=r||"",T.readyState=0<e?4:0,i=200<=e&&e<300||304===e,n&&(s=function(e,t,n){var r,i,o,a,s=e.contents,u=e.dataTypes;while("*"===u[0])u.shift(),void 0===r&&(r=e.mimeType||t.getResponseHeader("Content-Type"));if(r)for(i in s)if(s[i]&&s[i].test(r)){u.unshift(i);break}if(u[0]in n)o=u[0];else{for(i in n){if(!u[0]||e.converters[i+" "+u[0]]){o=i;break}a||(a=i)}o=o||a}if(o)return o!==u[0]&&u.unshift(o),n[o]}(v,T,n)),!i&&-1<ce.inArray("script",v.dataTypes)&&ce.inArray("json",v.dataTypes)<0&&(v.converters["text script"]=function(){}),s=function(e,t,n,r){var i,o,a,s,u,l={},c=e.dataTypes.slice();if(c[1])for(a in e.converters)l[a.toLowerCase()]=e.converters[a];o=c.shift();while(o)if(e.responseFields[o]&&(n[e.responseFields[o]]=t),!u&&r&&e.dataFilter&&(t=e.dataFilter(t,e.dataType)),u=o,o=c.shift())if("*"===o)o=u;else if("*"!==u&&u!==o){if(!(a=l[u+" "+o]||l["* "+o]))for(i in l)if((s=i.split(" "))[1]===o&&(a=l[u+" "+s[0]]||l["* "+s[0]])){!0===a?a=l[i]:!0!==l[i]&&(o=s[0],c.unshift(s[1]));break}if(!0!==a)if(a&&e["throws"])t=a(t);else try{t=a(t)}catch(e){return{state:"parsererror",error:a?e:"No conversion from "+u+" to "+o}}}return{state:"success",data:t}}(v,s,T,i),i?(v.ifModified&&((u=T.getResponseHeader("Last-Modified"))&&(ce.lastModified[f]=u),(u=T.getResponseHeader("etag"))&&(ce.etag[f]=u)),204===e||"HEAD"===v.type?l="nocontent":304===e?l="notmodified":(l=s.state,o=s.data,i=!(a=s.error))):(a=l,!e&&l||(l="error",e<0&&(e=0))),T.status=e,T.statusText=(t||l)+"",i?x.resolveWith(y,[o,l,T]):x.rejectWith(y,[T,l,a]),T.statusCode(w),w=void 0,g&&m.trigger(i?"ajaxSuccess":"ajaxError",[T,v,i?o:a]),b.fireWith(y,[T,l]),g&&(m.trigger("ajaxComplete",[T,v]),--ce.active||ce.event.trigger("ajaxStop")))}return T},getJSON:function(e,t,n){return ce.get(e,t,n,"json")},getScript:function(e,t){return ce.get(e,void 0,t,"script")}}),ce.each(["get","post"],function(e,i){ce[i]=function(e,t,n,r){return v(t)&&(r=r||n,n=t,t=void 0),ce.ajax(ce.extend({url:e,type:i,dataType:r,data:t,success:n},ce.isPlainObject(e)&&e))}}),ce.ajaxPrefilter(function(e){var t;for(t in e.headers)"content-type"===t.toLowerCase()&&(e.contentType=e.headers[t]||"")}),ce._evalUrl=function(e,t,n){return ce.ajax({url:e,type:"GET",dataType:"script",cache:!0,async:!1,global:!1,converters:{"text script":function(){}},dataFilter:function(e){ce.globalEval(e,t,n)}})},ce.fn.extend({wrapAll:function(e){var t;return this[0]&&(v(e)&&(e=e.call(this[0])),t=ce(e,this[0].ownerDocument).eq(0).clone(!0),this[0].parentNode&&t.insertBefore(this[0]),t.map(function(){var e=this;while(e.firstElementChild)e=e.firstElementChild;return e}).append(this)),this},wrapInner:function(n){return v(n)?this.each(function(e){ce(this).wrapInner(n.call(this,e))}):this.each(function(){var e=ce(this),t=e.contents();t.length?t.wrapAll(n):e.append(n)})},wrap:function(t){var n=v(t);return this.each(function(e){ce(this).wrapAll(n?t.call(this,e):t)})},unwrap:function(e){return this.parent(e).not("body").each(function(){ce(this).replaceWith(this.childNodes)}),this}}),ce.expr.pseudos.hidden=function(e){return!ce.expr.pseudos.visible(e)},ce.expr.pseudos.visible=function(e){return!!(e.offsetWidth||e.offsetHeight||e.getClientRects().length)},ce.ajaxSettings.xhr=function(){try{return new ie.XMLHttpRequest}catch(e){}};var Yt={0:200,1223:204},Qt=ce.ajaxSettings.xhr();le.cors=!!Qt&&"withCredentials"in Qt,le.ajax=Qt=!!Qt,ce.ajaxTransport(function(i){var o,a;if(le.cors||Qt&&!i.crossDomain)return{send:function(e,t){var n,r=i.xhr();if(r.open(i.type,i.url,i.async,i.username,i.password),i.xhrFields)for(n in i.xhrFields)r[n]=i.xhrFields[n];for(n in i.mimeType&&r.overrideMimeType&&r.overrideMimeType(i.mimeType),i.crossDomain||e["X-Requested-With"]||(e["X-Requested-With"]="XMLHttpRequest"),e)r.setRequestHeader(n,e[n]);o=function(e){return function(){o&&(o=a=r.onload=r.onerror=r.onabort=r.ontimeout=r.onreadystatechange=null,"abort"===e?r.abort():"error"===e?"number"!=typeof r.status?t(0,"error"):t(r.status,r.statusText):t(Yt[r.status]||r.status,r.statusText,"text"!==(r.responseType||"text")||"string"!=typeof r.responseText?{binary:r.response}:{text:r.responseText},r.getAllResponseHeaders()))}},r.onload=o(),a=r.onerror=r.ontimeout=o("error"),void 0!==r.onabort?r.onabort=a:r.onreadystatechange=function(){4===r.readyState&&ie.setTimeout(function(){o&&a()})},o=o("abort");try{r.send(i.hasContent&&i.data||null)}catch(e){if(o)throw e}},abort:function(){o&&o()}}}),ce.ajaxPrefilter(function(e){e.crossDomain&&(e.contents.script=!1)}),ce.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/\b(?:java|ecma)script\b/},converters:{"text script":function(e){return ce.globalEval(e),e}}}),ce.ajaxPrefilter("script",function(e){void 0===e.cache&&(e.cache=!1),e.crossDomain&&(e.type="GET")}),ce.ajaxTransport("script",function(n){var r,i;if(n.crossDomain||n.scriptAttrs)return{send:function(e,t){r=ce("<script>").attr(n.scriptAttrs||{}).prop({charset:n.scriptCharset,src:n.url}).on("load error",i=function(e){r.remove(),i=null,e&&t("error"===e.type?404:200,e.type)}),C.head.appendChild(r[0])},abort:function(){i&&i()}}});var Jt,Kt=[],Zt=/(=)\?(?=&|$)|\?\?/;ce.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var e=Kt.pop()||ce.expando+"_"+jt.guid++;return this[e]=!0,e}}),ce.ajaxPrefilter("json jsonp",function(e,t,n){var r,i,o,a=!1!==e.jsonp&&(Zt.test(e.url)?"url":"string"==typeof e.data&&0===(e.contentType||"").indexOf("application/x-www-form-urlencoded")&&Zt.test(e.data)&&"data");if(a||"jsonp"===e.dataTypes[0])return r=e.jsonpCallback=v(e.jsonpCallback)?e.jsonpCallback():e.jsonpCallback,a?e[a]=e[a].replace(Zt,"$1"+r):!1!==e.jsonp&&(e.url+=(At.test(e.url)?"&":"?")+e.jsonp+"="+r),e.converters["script json"]=function(){return o||ce.error(r+" was not called"),o[0]},e.dataTypes[0]="json",i=ie[r],ie[r]=function(){o=arguments},n.always(function(){void 0===i?ce(ie).removeProp(r):ie[r]=i,e[r]&&(e.jsonpCallback=t.jsonpCallback,Kt.push(r)),o&&v(i)&&i(o[0]),o=i=void 0}),"script"}),le.createHTMLDocument=((Jt=C.implementation.createHTMLDocument("").body).innerHTML="<form></form><form></form>",2===Jt.childNodes.length),ce.parseHTML=function(e,t,n){return"string"!=typeof e?[]:("boolean"==typeof t&&(n=t,t=!1),t||(le.createHTMLDocument?((r=(t=C.implementation.createHTMLDocument("")).createElement("base")).href=C.location.href,t.head.appendChild(r)):t=C),o=!n&&[],(i=w.exec(e))?[t.createElement(i[1])]:(i=Ae([e],t,o),o&&o.length&&ce(o).remove(),ce.merge([],i.childNodes)));var r,i,o},ce.fn.load=function(e,t,n){var r,i,o,a=this,s=e.indexOf(" ");return-1<s&&(r=Tt(e.slice(s)),e=e.slice(0,s)),v(t)?(n=t,t=void 0):t&&"object"==typeof t&&(i="POST"),0<a.length&&ce.ajax({url:e,type:i||"GET",dataType:"html",data:t}).done(function(e){o=arguments,a.html(r?ce("<div>").append(ce.parseHTML(e)).find(r):e)}).always(n&&function(e,t){a.each(function(){n.apply(this,o||[e.responseText,t,e])})}),this},ce.expr.pseudos.animated=function(t){return ce.grep(ce.timers,function(e){return t===e.elem}).length},ce.offset={setOffset:function(e,t,n){var r,i,o,a,s,u,l=ce.css(e,"position"),c=ce(e),f={};"static"===l&&(e.style.position="relative"),s=c.offset(),o=ce.css(e,"top"),u=ce.css(e,"left"),("absolute"===l||"fixed"===l)&&-1<(o+u).indexOf("auto")?(a=(r=c.position()).top,i=r.left):(a=parseFloat(o)||0,i=parseFloat(u)||0),v(t)&&(t=t.call(e,n,ce.extend({},s))),null!=t.top&&(f.top=t.top-s.top+a),null!=t.left&&(f.left=t.left-s.left+i),"using"in t?t.using.call(e,f):c.css(f)}},ce.fn.extend({offset:function(t){if(arguments.length)return void 0===t?this:this.each(function(e){ce.offset.setOffset(this,t,e)});var e,n,r=this[0];return r?r.getClientRects().length?(e=r.getBoundingClientRect(),n=r.ownerDocument.defaultView,{top:e.top+n.pageYOffset,left:e.left+n.pageXOffset}):{top:0,left:0}:void 0},position:function(){if(this[0]){var e,t,n,r=this[0],i={top:0,left:0};if("fixed"===ce.css(r,"position"))t=r.getBoundingClientRect();else{t=this.offset(),n=r.ownerDocument,e=r.offsetParent||n.documentElement;while(e&&(e===n.body||e===n.documentElement)&&"static"===ce.css(e,"position"))e=e.parentNode;e&&e!==r&&1===e.nodeType&&((i=ce(e).offset()).top+=ce.css(e,"borderTopWidth",!0),i.left+=ce.css(e,"borderLeftWidth",!0))}return{top:t.top-i.top-ce.css(r,"marginTop",!0),left:t.left-i.left-ce.css(r,"marginLeft",!0)}}},offsetParent:function(){return this.map(function(){var e=this.offsetParent;while(e&&"static"===ce.css(e,"position"))e=e.offsetParent;return e||J})}}),ce.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(t,i){var o="pageYOffset"===i;ce.fn[t]=function(e){return M(this,function(e,t,n){var r;if(y(e)?r=e:9===e.nodeType&&(r=e.defaultView),void 0===n)return r?r[i]:e[t];r?r.scrollTo(o?r.pageXOffset:n,o?n:r.pageYOffset):e[t]=n},t,e,arguments.length)}}),ce.each(["top","left"],function(e,n){ce.cssHooks[n]=Ye(le.pixelPosition,function(e,t){if(t)return t=Ge(e,n),_e.test(t)?ce(e).position()[n]+"px":t})}),ce.each({Height:"height",Width:"width"},function(a,s){ce.each({padding:"inner"+a,content:s,"":"outer"+a},function(r,o){ce.fn[o]=function(e,t){var n=arguments.length&&(r||"boolean"!=typeof e),i=r||(!0===e||!0===t?"margin":"border");return M(this,function(e,t,n){var r;return y(e)?0===o.indexOf("outer")?e["inner"+a]:e.document.documentElement["client"+a]:9===e.nodeType?(r=e.documentElement,Math.max(e.body["scroll"+a],r["scroll"+a],e.body["offset"+a],r["offset"+a],r["client"+a])):void 0===n?ce.css(e,t,i):ce.style(e,t,n,i)},s,n?e:void 0,n)}})}),ce.each(["ajaxStart","ajaxStop","ajaxComplete","ajaxError","ajaxSuccess","ajaxSend"],function(e,t){ce.fn[t]=function(e){return this.on(t,e)}}),ce.fn.extend({bind:function(e,t,n){return this.on(e,null,t,n)},unbind:function(e,t){return this.off(e,null,t)},delegate:function(e,t,n,r){return this.on(t,e,n,r)},undelegate:function(e,t,n){return 1===arguments.length?this.off(e,"**"):this.off(t,e||"**",n)},hover:function(e,t){return this.on("mouseenter",e).on("mouseleave",t||e)}}),ce.each("blur focus focusin focusout resize scroll click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup contextmenu".split(" "),function(e,n){ce.fn[n]=function(e,t){return 0<arguments.length?this.on(n,null,e,t):this.trigger(n)}});var en=/^[\s\uFEFF\xA0]+|([^\s\uFEFF\xA0])[\s\uFEFF\xA0]+$/g;ce.proxy=function(e,t){var n,r,i;if("string"==typeof t&&(n=e[t],t=e,e=n),v(e))return r=ae.call(arguments,2),(i=function(){return e.apply(t||this,r.concat(ae.call(arguments)))}).guid=e.guid=e.guid||ce.guid++,i},ce.holdReady=function(e){e?ce.readyWait++:ce.ready(!0)},ce.isArray=Array.isArray,ce.parseJSON=JSON.parse,ce.nodeName=fe,ce.isFunction=v,ce.isWindow=y,ce.camelCase=F,ce.type=x,ce.now=Date.now,ce.isNumeric=function(e){var t=ce.type(e);return("number"===t||"string"===t)&&!isNaN(e-parseFloat(e))},ce.trim=function(e){return null==e?"":(e+"").replace(en,"$1")},"function"==typeof define&&define.amd&&define("jquery",[],function(){return ce});var tn=ie.jQuery,nn=ie.$;return ce.noConflict=function(e){return ie.$===ce&&(ie.$=nn),e&&ie.jQuery===ce&&(ie.jQuery=tn),ce},"undefined"==typeof e&&(ie.jQuery=ie.$=ce),ce});
  

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
        var hd_size = newsFocus_hd.length - 1;
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

	$.fn.size = function() {
		return this.length;
	  }

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
