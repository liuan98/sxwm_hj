;my.defineComponent || (my.defineComponent = Component);(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["components/basic-component/app-switch/app-switch"],{"10a9":function(t,n,e){"use strict";e.r(n);var a=e("4ca9"),i=e.n(a);for(var u in a)"default"!==u&&function(t){e.d(n,t,function(){return a[t]})}(u);n["default"]=i.a},"4ca9":function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var a={name:"app-switch",data:function(){return{x:0,switch:this.value}},props:{theme:String,value:{default:!1}},methods:{switchChange:function(){this.switch=!this.switch,this.$emit("input",this.switch)}},computed:{themeColor:function(){return 88===this.x?"".concat(this.theme,"-background"):""}},watch:{value:{handler:function(t){!1===t?this.x=0:!0===t&&(this.x=88)},immediate:!0}}};n.default=a},6033:function(t,n,e){"use strict";var a=function(){var t=this,n=t.$createElement;t._self._c},i=[];e.d(n,"a",function(){return a}),e.d(n,"b",function(){return i})},"61ac":function(t,n,e){"use strict";e.r(n);var a=e("6033"),i=e("10a9");for(var u in i)"default"!==u&&function(t){e.d(n,t,function(){return i[t]})}(u);e("a3a5");var c=e("2877"),r=Object(c["a"])(i["default"],a["a"],a["b"],!1,null,null,null);n["default"]=r.exports},7539:function(t,n,e){},a3a5:function(t,n,e){"use strict";var a=e("7539"),i=e.n(a);i.a}}]);
;(my["webpackJsonp"] = my["webpackJsonp"] || []).push([
    'components/basic-component/app-switch/app-switch-create-component',
    {
        'components/basic-component/app-switch/app-switch-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('c11b')['createComponent'](__webpack_require__("61ac"))
        })
    },
    [['components/basic-component/app-switch/app-switch-create-component']]
]);                
