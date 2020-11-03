;my.defineComponent || (my.defineComponent = Component);(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["components/page-component/app-buy-prompt/app-buy-prompt"],{4448:function(t,n,u){"use strict";var e=u("8eba"),a=u.n(e);a.a},"49f6":function(t,n,u){"use strict";u.r(n);var e=u("7d75"),a=u("905a");for(var r in a)"default"!==r&&function(t){u.d(n,t,function(){return a[t]})}(r);u("4448");var c=u("2877"),o=Object(c["a"])(a["default"],e["a"],e["b"],!1,null,"51605034",null);n["default"]=o.exports},"7d75":function(t,n,u){"use strict";var e=function(){var t=this,n=t.$createElement;t._self._c},a=[];u.d(n,"a",function(){return e}),u.d(n,"b",function(){return a})},"8eba":function(t,n,u){},"905a":function(t,n,u){"use strict";u.r(n);var e=u("92fc"),a=u.n(e);for(var r in e)"default"!==r&&function(t){u.d(n,t,function(){return e[t]})}(r);n["default"]=a.a},"92fc":function(t,n,u){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var e={name:"app-buy-prompt",data:function(){return{buy_data:null}},created:function(){var t=this;t.$request({url:t.$api.index.buy_data}).then(function(n){0===n.code&&(t.buy_data=n.data)})},methods:{catchTouchMove:function(){return!1}}};n.default=e}}]);
;(my["webpackJsonp"] = my["webpackJsonp"] || []).push([
    'components/page-component/app-buy-prompt/app-buy-prompt-create-component',
    {
        'components/page-component/app-buy-prompt/app-buy-prompt-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('c11b')['createComponent'](__webpack_require__("49f6"))
        })
    },
    [['components/page-component/app-buy-prompt/app-buy-prompt-create-component']]
]);                
