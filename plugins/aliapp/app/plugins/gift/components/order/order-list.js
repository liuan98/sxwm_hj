;my.defineComponent || (my.defineComponent = Component);(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["plugins/gift/components/order/order-list"],{"172e":function(t,e,r){},6761:function(t,e,r){"use strict";r.r(e);var n=r("9e86"),i=r.n(n);for(var o in n)"default"!==o&&function(t){r.d(e,t,function(){return n[t]})}(o);e["default"]=i.a},"7d4f":function(t,e,r){"use strict";r.r(e);var n=r("93e9"),i=r("6761");for(var o in i)"default"!==o&&function(t){r.d(e,t,function(){return i[t]})}(o);r("9a38");var u=r("2877"),a=Object(u["a"])(i["default"],n["a"],n["b"],!1,null,"370515d9",null);e["default"]=a.exports},"93e9":function(t,e,r){"use strict";var n=function(){var t=this,e=t.$createElement,r=(t._self._c,t.__map(t.order_list,function(e,r){var n=t.getPicUrl(e.sendOrder[0].detail[0].goods_info),i=JSON.parse(e.sendOrder[0].detail[0].goods_info);return{$orig:t.__get_orig(e),m0:n,g0:i}}));t.$mp.data=Object.assign({},{$root:{l0:r}})},i=[];r.d(e,"a",function(){return n}),r.d(e,"b",function(){return i})},"9a38":function(t,e,r){"use strict";var n=r("172e"),i=r.n(n);i.a},"9e86":function(t,e,r){"use strict";(function(t){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var r={name:"order-list",props:{theme:String,order_list:Array,tab_status:Number,big_gift_pic:String},methods:{redirectTo:function(){t.redirectTo({url:"/plugins/gift/index/index"})},navigateTo:function(e){t.navigateTo({url:e})},getPicUrl:function(t){var e=JSON.parse(t).goods_attr;return e.pic_url?e.pic_url:e.cover_pic}}};e.default=r}).call(this,r("c11b")["default"])}}]);
;(my["webpackJsonp"] = my["webpackJsonp"] || []).push([
    'plugins/gift/components/order/order-list-create-component',
    {
        'plugins/gift/components/order/order-list-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('c11b')['createComponent'](__webpack_require__("7d4f"))
        })
    },
    [['plugins/gift/components/order/order-list-create-component']]
]);                
