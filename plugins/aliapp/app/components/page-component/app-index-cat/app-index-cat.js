;my.defineComponent || (my.defineComponent = Component);(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["components/page-component/app-index-cat/app-index-cat"],{"1fa6":function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var a=o(e("7ab7"));function o(t){return t&&t.__esModule?t:{default:t}}var u=function(){return Promise.all([e.e("common/vendor"),e.e("components/page-component/app-goods-list/app-goods-list")]).then(e.bind(null,"3f73"))},r={name:"app-index-cat",components:{"app-goods-list":u},props:{catPicUrl:String,name:String,listStyle:{type:String,default:function(){return 1}},list:Array,catId:Number},methods:{jump:function(){(0,a.default)({open_type:"navigate",page_url:"/pages/goods/list",params:[{key:"cat_id",value:this.catId}]})}}};n.default=r},"258a":function(t,n,e){"use strict";e.r(n);var a=e("2795"),o=e("ed8c");for(var u in o)"default"!==u&&function(t){e.d(n,t,function(){return o[t]})}(u);e("bf14");var r=e("2877"),c=Object(r["a"])(o["default"],a["a"],a["b"],!1,null,"d809559e",null);n["default"]=c.exports},2795:function(t,n,e){"use strict";var a=function(){var t=this,n=t.$createElement;t._self._c},o=[];e.d(n,"a",function(){return a}),e.d(n,"b",function(){return o})},"9ac7":function(t,n,e){},bf14:function(t,n,e){"use strict";var a=e("9ac7"),o=e.n(a);o.a},ed8c:function(t,n,e){"use strict";e.r(n);var a=e("1fa6"),o=e.n(a);for(var u in a)"default"!==u&&function(t){e.d(n,t,function(){return a[t]})}(u);n["default"]=o.a}}]);
;(my["webpackJsonp"] = my["webpackJsonp"] || []).push([
    'components/page-component/app-index-cat/app-index-cat-create-component',
    {
        'components/page-component/app-index-cat/app-index-cat-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('c11b')['createComponent'](__webpack_require__("258a"))
        })
    },
    [['components/page-component/app-index-cat/app-index-cat-create-component']]
]);                
