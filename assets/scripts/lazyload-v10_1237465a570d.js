!function(t){function e(r){if(n[r])return n[r].exports;var o=n[r]={i:r,l:!1,exports:{}};return t[r].call(o.exports,o,o.exports,e),o.l=!0,o.exports}var n={};e.m=t,e.c=n,e.d=function(t,n,r){e.o(t,n)||Object.defineProperty(t,n,{configurable:!1,enumerable:!0,get:r})},e.n=function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,"a",n),n},e.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},e.p="/app/themes/twist/assets/",e(e.s=8)}({8:function(t,e,n){t.exports=n(9)},9:function(t,e,n){var r,o,i=Object.assign||function(t){for(var e=arguments,n=1;n<arguments.length;n++){var r=e[n];for(var o in r)Object.prototype.hasOwnProperty.call(r,o)&&(t[o]=r[o])}return t},a="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t};!function(i,s){"object"===a(e)&&void 0!==t?t.exports=s():(r=s,void 0!==(o="function"==typeof r?r.call(e,n,e,t):r)&&(t.exports=o))}(0,function(){"use strict";function t(t,e,n){var r=e._settings;!n&&s(t)||(A(r.callback_enter,t),P.indexOf(t.tagName)>-1&&(M(t,e),C(t,r.class_loading)),L(t,e),a(t),A(r.callback_set,t))}var e={elements_selector:"img",container:document,threshold:300,thresholds:null,data_src:"src",data_srcset:"srcset",data_sizes:"sizes",data_bg:"bg",class_loading:"loading",class_loaded:"loaded",class_error:"error",load_delay:0,callback_load:null,callback_error:null,callback_set:null,callback_enter:null,callback_finish:null,to_webp:!1},n=function(t){return i({},e,t)},r=function(t,e){return t.getAttribute("data-"+e)},o=function(t,e,n){var r="data-"+e;if(null===n)return void t.removeAttribute(r);t.setAttribute(r,n)},a=function(t){return o(t,"was-processed","true")},s=function(t){return"true"===r(t,"was-processed")},c=function(t,e){return o(t,"ll-timeout",e)},l=function(t){return r(t,"ll-timeout")},u=function(t){return t.filter(function(t){return!s(t)})},d=function(t,e){return t.filter(function(t){return t!==e})},f=function(t,e){var n,r=new t(e);try{n=new CustomEvent("LazyLoad::Initialized",{detail:{instance:r}})}catch(t){n=document.createEvent("CustomEvent"),n.initCustomEvent("LazyLoad::Initialized",!1,!1,{instance:r})}window.dispatchEvent(n)},_=function(t,e){return e?t.replace(/\.(jpe?g|png)/gi,".webp"):t},v="undefined"!=typeof window,g=v&&!("onscroll"in window)||/(gle|ing|ro)bot|crawl|spider/i.test(navigator.userAgent),p=v&&"IntersectionObserver"in window,b=v&&"classList"in document.createElement("p"),h=v&&function(){var t=document.createElement("canvas");return!(!t.getContext||!t.getContext("2d"))&&0===t.toDataURL("image/webp").indexOf("data:image/webp")}(),m=function(t,e,n,o){for(var i,a=0;i=t.children[a];a+=1)if("SOURCE"===i.tagName){var s=r(i,n);y(i,e,s,o)}},y=function(t,e,n,r){n&&t.setAttribute(e,_(n,r))},w=function(t,e){var n=h&&e.to_webp,o=e.data_srcset,i=t.parentNode;i&&"PICTURE"===i.tagName&&m(i,"srcset",o,n);var a=r(t,e.data_sizes);y(t,"sizes",a);var s=r(t,o);y(t,"srcset",s,n);var c=r(t,e.data_src);y(t,"src",c,n)},E=function(t,e){var n=r(t,e.data_src);y(t,"src",n)},I=function(t,e){var n=e.data_src,o=r(t,n);m(t,"src",n),y(t,"src",o),t.load()},O=function(t,e){var n=h&&e.to_webp,o=r(t,e.data_src),i=r(t,e.data_bg);if(o){var a=_(o,n);t.style.backgroundImage='url("'+a+'")'}if(i){var s=_(i,n);t.style.backgroundImage=s}},x={IMG:w,IFRAME:E,VIDEO:I},L=function(t,e){var n=e._settings,r=t.tagName,o=x[r];if(o)return o(t,n),e._updateLoadingCount(1),void(e._elements=d(e._elements,t));O(t,n)},C=function(t,e){if(b)return void t.classList.add(e);t.className+=(t.className?" ":"")+e},k=function(t,e){if(b)return void t.classList.remove(e);t.className=t.className.replace(new RegExp("(^|\\s+)"+e+"(\\s+|$)")," ").replace(/^\s+/,"").replace(/\s+$/,"")},A=function(t,e){t&&t(e)},z=function(t,e,n){t.addEventListener(e,n)},N=function(t,e,n){t.removeEventListener(e,n)},R=function(t,e,n){z(t,"load",e),z(t,"loadeddata",e),z(t,"error",n)},S=function(t,e,n){N(t,"load",e),N(t,"loadeddata",e),N(t,"error",n)},j=function(t,e,n){var r=n._settings,o=e?r.class_loaded:r.class_error,i=e?r.callback_load:r.callback_error,a=t.target;k(a,r.class_loading),C(a,o),A(i,a),n._updateLoadingCount(-1)},M=function(t,e){var n=function n(o){j(o,!0,e),S(t,n,r)},r=function r(o){j(o,!1,e),S(t,n,r)};R(t,n,r)},P=["IMG","IFRAME","VIDEO"],D=function(e,n,r){t(e,r),n.unobserve(e)},T=function(t){var e=l(t);e&&(clearTimeout(e),c(t,null))},U=function(t,e,n){var r=n._settings.load_delay,o=l(t);o||(o=setTimeout(function(){D(t,e,n),T(t)},r),c(t,o))},F=function(t){return t.isIntersecting||t.intersectionRatio>0},G=function(t){return{root:t.container===document?null:t.container,rootMargin:t.thresholds||t.threshold+"px"}},V=function(t,e){this._settings=n(t),this._setObserver(),this._loadingCount=0,this.update(e)};return V.prototype={_manageIntersection:function(t){var e=this._observer,n=this._settings.load_delay,r=t.target;if(!n)return void(F(t)&&D(r,e,this));F(t)?U(r,e,this):T(r)},_onIntersection:function(t){t.forEach(this._manageIntersection.bind(this))},_setObserver:function(){p&&(this._observer=new IntersectionObserver(this._onIntersection.bind(this),G(this._settings)))},_updateLoadingCount:function(t){this._loadingCount+=t,0===this._elements.length&&0===this._loadingCount&&A(this._settings.callback_finish)},update:function(t){var e=this,n=this._settings,r=t||n.container.querySelectorAll(n.elements_selector);if(this._elements=u(Array.prototype.slice.call(r)),g||!this._observer)return void this.loadAll();this._elements.forEach(function(t){e._observer.observe(t)})},destroy:function(){var t=this;this._observer&&(this._elements.forEach(function(e){t._observer.unobserve(e)}),this._observer=null),this._elements=null,this._settings=null},load:function(e,n){t(e,this,n)},loadAll:function(){var t=this;this._elements.forEach(function(e){t.load(e)})}},v&&function(t,e){if(e)if(e.length)for(var n,r=0;n=e[r];r+=1)f(t,n);else f(t,e)}(V,window.lazyLoadOptions),V})}});