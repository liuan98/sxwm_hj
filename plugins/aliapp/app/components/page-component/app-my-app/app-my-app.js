;my.defineComponent || (my.defineComponent = Component);(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["components/page-component/app-my-app/app-my-app"],{"36bc":function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var r=n("2f62");function o(t,e){var n=Object.keys(t);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(t);e&&(r=r.filter(function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable})),n.push.apply(n,r)}return n}function a(t){for(var e=1;e<arguments.length;e++){var n=null!=arguments[e]?arguments[e]:{};e%2?o(n,!0).forEach(function(e){c(t,e,n[e])}):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(n)):o(n).forEach(function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(n,e))})}return t}function c(t,e,n){return e in t?Object.defineProperty(t,e,{value:n,enumerable:!0,configurable:!0,writable:!0}):t[e]=n,t}var i={name:"app-my-app",data:function(){return{is_add_show:!1}},computed:a({},(0,r.mapState)("mallConfig",{setting:function(t){return t.mall.setting}})),methods:{close:function(){this.is_add_show=!1,this.$storage.setStorageSync("_IS_ADD_APP",!this.is_add_show)}},created:function(){this.is_add_show=!this.$storage.getStorageSync("_IS_ADD_APP")}};e.default=i},a524:function(t,e,n){"use strict";var r=function(){var t=this,e=t.$createElement;t._self._c},o=[];n.d(e,"a",function(){return r}),n.d(e,"b",function(){return o})},b088:function(t,e,n){"use strict";var r=n("c644"),o=n.n(r);o.a},c644:function(t,e,n){},d7d6:function(t,e,n){"use strict";n.r(e);var r=n("a524"),o=n("ff76");for(var a in o)"default"!==a&&function(t){n.d(e,t,function(){return o[t]})}(a);n("b088");var c=n("2877"),i=Object(c["a"])(o["default"],r["a"],r["b"],!1,null,"f65d5262",null);e["default"]=i.exports},ff76:function(t,e,n){"use strict";n.r(e);var r=n("36bc"),o=n.n(r);for(var a in r)"default"!==a&&function(t){n.d(e,t,function(){return r[t]})}(a);e["default"]=o.a}}]);
;(my["webpackJsonp"] = my["webpackJsonp"] || []).push([
    'components/page-component/app-my-app/app-my-app-create-component',
    {
        'components/page-component/app-my-app/app-my-app-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('c11b')['createComponent'](__webpack_require__("d7d6"))
        })
    },
    [['components/page-component/app-my-app/app-my-app-create-component']]
]);                
