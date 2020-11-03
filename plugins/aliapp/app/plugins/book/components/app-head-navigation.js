;my.defineComponent || (my.defineComponent = Component);(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["plugins/book/components/app-head-navigation"],{"35ea":function(n,t,e){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a={name:"app-head-navigation",props:{list:{type:Array,default:function(){return[{name:"全部",id:0},{name:"待支付",id:1},{name:"待使用",id:2},{name:"待评价",id:4},{name:"售后",id:9}]}}},data:function(){return{activeIndex:0}},methods:{active:function(n){this.activeIndex=n,this.$emit("click",n)}}};t.default=a},"5c55":function(n,t,e){"use strict";e.r(t);var a=e("35ea"),i=e.n(a);for(var u in a)"default"!==u&&function(n){e.d(t,n,function(){return a[n]})}(u);t["default"]=i.a},"5fcf":function(n,t,e){"use strict";var a=e("7db0"),i=e.n(a);i.a},"7db0":function(n,t,e){},be43:function(n,t,e){"use strict";var a=function(){var n=this,t=n.$createElement;n._self._c},i=[];e.d(t,"a",function(){return a}),e.d(t,"b",function(){return i})},ffe8:function(n,t,e){"use strict";e.r(t);var a=e("be43"),i=e("5c55");for(var u in i)"default"!==u&&function(n){e.d(t,n,function(){return i[n]})}(u);e("5fcf");var c=e("2877"),r=Object(c["a"])(i["default"],a["a"],a["b"],!1,null,"4c7bf364",null);t["default"]=r.exports}}]);
;(my["webpackJsonp"] = my["webpackJsonp"] || []).push([
    'plugins/book/components/app-head-navigation-create-component',
    {
        'plugins/book/components/app-head-navigation-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('c11b')['createComponent'](__webpack_require__("ffe8"))
        })
    },
    [['plugins/book/components/app-head-navigation-create-component']]
]);                
