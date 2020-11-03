;my.defineComponent || (my.defineComponent = Component);(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["plugins/book/components/app-write-off-code"],{"110f5":function(t,e,n){"use strict";n.r(e);var i=n("d6b7"),a=n.n(i);for(var u in i)"default"!==u&&function(t){n.d(e,t,function(){return i[t]})}(u);e["default"]=a.a},"969b":function(t,e,n){},c7ca:function(t,e,n){"use strict";var i=n("969b"),a=n.n(i);a.a},d6b7:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var i={name:"app-write-off-code",props:{hidden:{type:Boolean,default:function(){return!1}},itemId:{type:String,default:function(){return"-1"}}},data:function(){return{file_path:""}},watch:{hidden:{handler:function(t){var e=this;!0===t&&this.$request({url:this.$api.book.clerk_code,data:{id:this.itemId}}).then(function(t){0===t.code&&(e.file_path=t.data.file_path)})}}},methods:{hiddenHandler:function(){this.$emit("hiden",!1),this.file_path=""}}};e.default=i},e604:function(t,e,n){"use strict";n.r(e);var i=n("e9b4"),a=n("110f5");for(var u in a)"default"!==u&&function(t){n.d(e,t,function(){return a[t]})}(u);n("c7ca");var r=n("2877"),f=Object(r["a"])(a["default"],i["a"],i["b"],!1,null,"6f85493c",null);e["default"]=f.exports},e9b4:function(t,e,n){"use strict";var i=function(){var t=this,e=t.$createElement;t._self._c},a=[];n.d(e,"a",function(){return i}),n.d(e,"b",function(){return a})}}]);
;(my["webpackJsonp"] = my["webpackJsonp"] || []).push([
    'plugins/book/components/app-write-off-code-create-component',
    {
        'plugins/book/components/app-write-off-code-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('c11b')['createComponent'](__webpack_require__("e604"))
        })
    },
    [['plugins/book/components/app-write-off-code-create-component']]
]);                
