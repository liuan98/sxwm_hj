;my.defineComponent || (my.defineComponent = Component);(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["plugins/gift/components/receive/participant"],{2218:function(t,e,r){"use strict";r.r(e);var n=r("f875"),u=r.n(n);for(var i in n)"default"!==i&&function(t){r.d(e,t,function(){return n[t]})}(i);e["default"]=u.a},5411:function(t,e,r){},"8b99":function(t,e,r){"use strict";var n=function(){var t=this,e=t.$createElement,r=(t._self._c,t.__map(t.newUserOrder,function(e,r){var n=t.filter(r);return{$orig:t.__get_orig(e),m0:n}}));t.$mp.data=Object.assign({},{$root:{l0:r}})},u=[];r.d(e,"a",function(){return n}),r.d(e,"b",function(){return u})},c5b6:function(t,e,r){"use strict";var n=r("5411"),u=r.n(n);u.a},e134:function(t,e,r){"use strict";r.r(e);var n=r("8b99"),u=r("2218");for(var i in u)"default"!==i&&function(t){r.d(e,t,function(){return u[t]})}(i);r("c5b6");var a=r("2877"),c=Object(a["a"])(u["default"],n["a"],n["b"],!1,null,"2debdf5a",null);e["default"]=c.exports},f875:function(t,e,r){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n={name:"participant",props:["userOrder"],methods:{filter:function(t){return/[9]/.test(t)}},computed:{newUserOrder:function(){return this.userOrder.length>30?this.userOrder.slice(0,30):this.userOrder}}};e.default=n}}]);
;(my["webpackJsonp"] = my["webpackJsonp"] || []).push([
    'plugins/gift/components/receive/participant-create-component',
    {
        'plugins/gift/components/receive/participant-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('c11b')['createComponent'](__webpack_require__("e134"))
        })
    },
    [['plugins/gift/components/receive/participant-create-component']]
]);                
