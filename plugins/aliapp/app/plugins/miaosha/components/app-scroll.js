;my.defineComponent || (my.defineComponent = Component);(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["plugins/miaosha/components/app-scroll"],{"0c18":function(t,n,e){"use strict";var a=function(){var t=this,n=t.$createElement;t._self._c},c=[];e.d(n,"a",function(){return a}),e.d(n,"b",function(){return c})},9419:function(t,n,e){},ae5f:function(t,n,e){"use strict";var a=e("9419"),c=e.n(a);c.a},de01:function(t,n,e){"use strict";e.r(n);var a=e("0c18"),c=e("feb7");for(var i in c)"default"!==i&&function(t){e.d(n,t,function(){return c[t]})}(i);e("ae5f");var r=e("2877"),u=Object(r["a"])(c["default"],a["a"],a["b"],!1,null,"3740072b",null);n["default"]=u.exports},f691:function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var a={name:"app-scroll",props:{timeList:Array},data:function(){return{scrollId:"",activeIndex:0}},methods:{active:function(t,n){this.activeIndex=n;var e=n-2;this.scrollId="ms_".concat(e),this.$emit("click",n,t)}},watch:{timeList:{handler:function(t){var n=this;t.map(function(t,e){1===t.status&&(console.log(e),n.scrollId="ms_".concat(e-2),n.activeIndex=e)})},immediate:!0}}};n.default=a},feb7:function(t,n,e){"use strict";e.r(n);var a=e("f691"),c=e.n(a);for(var i in a)"default"!==i&&function(t){e.d(n,t,function(){return a[t]})}(i);n["default"]=c.a}}]);
;(my["webpackJsonp"] = my["webpackJsonp"] || []).push([
    'plugins/miaosha/components/app-scroll-create-component',
    {
        'plugins/miaosha/components/app-scroll-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('c11b')['createComponent'](__webpack_require__("de01"))
        })
    },
    [['plugins/miaosha/components/app-scroll-create-component']]
]);                
