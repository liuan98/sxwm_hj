;my.defineComponent || (my.defineComponent = Component);(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["components/page-component/app-group-goods/app-group-goods"],{"0510":function(t,e,n){"use strict";n.r(e);var r=n("f63f"),o=n.n(r);for(var u in r)"default"!==u&&function(t){n.d(e,t,function(){return r[t]})}(u);e["default"]=o.a},1401:function(t,e,n){},"4d51":function(t,e,n){"use strict";var r=n("1401"),o=n.n(r);o.a},"68fb":function(t,e,n){"use strict";var r=function(){var t=this,e=t.$createElement;t._self._c},o=[];n.d(e,"a",function(){return r}),n.d(e,"b",function(){return o})},"93cd":function(t,e,n){"use strict";n.r(e);var r=n("68fb"),o=n("0510");for(var u in o)"default"!==u&&function(t){n.d(e,t,function(){return o[t]})}(u);n("4d51");var a=n("2877"),i=Object(a["a"])(o["default"],r["a"],r["b"],!1,null,"60ea65cf",null);e["default"]=i.exports},f63f:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var r=n("2f62");function o(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter(function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable})),n.push.apply(n,r)}return n}function u(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?o(n,!0).forEach(function(e){a(t,e,n[e])}):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):o(n).forEach(function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))})}return t}function a(t,e,n){return e in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}var i=function(){return n.e("components/basic-component/app-load-text/app-load-text").then(n.bind(null,"cae6"))},f={name:"app-group-goods",components:{"app-load-text":i},computed:u({},(0,r.mapState)({height:function(t){return t.gConfig.systemInfo.windowHeight}})),props:{buttonColor:{type:String,default:function(){return""}},buyBtnStyle:{type:Number,default:function(){return 3}},buyBtnText:{type:String,default:function(){return""}},customizeGoodsTag:{type:Boolean,default:function(){return!1}},fill:{type:Number,default:function(){return 1}},goodsCoverProportion:{type:String,default:function(){return""}},goodsStyle:{type:Number,default:function(){return 2}},goodsTagPicUrl:{type:String,default:function(){return""}},list:{type:Array,default:function(){return[]}},listStyle:{type:Number,default:function(){return 1}},showBuyBtn:{type:Boolean,default:function(){return!0}},showGoodsName:{type:Boolean,default:function(){return!0}},showGoodsTag:{type:Boolean,default:function(){return!0}},scrollTop:{type:Number,default:function(){return 0}},value:{type:Boolean,default:function(){return!0}}},data:function(){return{request:this.value}},methods:{autoEnd:function(){var t=this;this.$lazyLoadingData("app-group-goods").then(function(e){e[0].height+e[0].top<t.height&&(t.request=!1,t.$emit("input",t.request))})}},watch:{scrollTop:{handler:function(t,e){t>e&&this.request&&this.autoEnd()}}}};e.default=f}}]);
;(my["webpackJsonp"] = my["webpackJsonp"] || []).push([
    'components/page-component/app-group-goods/app-group-goods-create-component',
    {
        'components/page-component/app-group-goods/app-group-goods-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('c11b')['createComponent'](__webpack_require__("93cd"))
        })
    },
    [['components/page-component/app-group-goods/app-group-goods-create-component']]
]);                
