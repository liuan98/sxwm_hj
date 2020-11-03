;my.defineComponent || (my.defineComponent = Component);(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["pages/cats/style-six"],{"6f43":function(t,n,e){},"777b":function(t,n,e){"use strict";var o=function(){var t=this,n=t.$createElement;t._self._c},i=[];e.d(n,"a",function(){return o}),e.d(n,"b",function(){return i})},"81c8":function(t,n,e){"use strict";(function(t){Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var o=function(){return e.e("components/page-component/app-category-list/app-category-list").then(e.bind(null,"5d3f"))},i=function(){return e.e("components/page-component/app-no-goods/app-no-goods").then(e.bind(null,"8112"))},a={name:"style-six",components:{"app-category-list":o,"app-no-goods":i},props:["list","activeIndex","setHeight"],methods:{active:function(t){this.$emit("active",t)},route_go:function(n){n&&t.navigateTo({url:n})},getChild:function(n,e){t.navigateTo({url:this.list[this.activeIndex].child[n].child[e].page_url})},route_advert:function(t){this.$emit("route_advert",t)}}};n.default=a}).call(this,e("c11b")["default"])},"952f":function(t,n,e){"use strict";e.r(n);var o=e("81c8"),i=e.n(o);for(var a in o)"default"!==a&&function(t){e.d(n,t,function(){return o[t]})}(a);n["default"]=i.a},d582:function(t,n,e){"use strict";e.r(n);var o=e("777b"),i=e("952f");for(var a in i)"default"!==a&&function(t){e.d(n,t,function(){return i[t]})}(a);e("dbf8");var u=e("2877"),c=Object(u["a"])(i["default"],o["a"],o["b"],!1,null,"d0318810",null);n["default"]=c.exports},dbf8:function(t,n,e){"use strict";var o=e("6f43"),i=e.n(o);i.a}}]);
;(my["webpackJsonp"] = my["webpackJsonp"] || []).push([
    'pages/cats/style-six-create-component',
    {
        'pages/cats/style-six-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('c11b')['createComponent'](__webpack_require__("d582"))
        })
    },
    [['pages/cats/style-six-create-component']]
]);                
