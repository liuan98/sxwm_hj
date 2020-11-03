;my.defineComponent || (my.defineComponent = Component);(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["components/page-component/app-special-topic/app-special-topic-normal"],{"26a4":function(t,n,e){"use strict";e.r(n);var c=e("28c4"),i=e("a70e");for(var u in i)"default"!==u&&function(t){e.d(n,t,function(){return i[t]})}(u);e("798d");var o=e("2877"),a=Object(o["a"])(i["default"],c["a"],c["b"],!1,null,"aeb5f78c",null);n["default"]=a.exports},"28c4":function(t,n,e){"use strict";var c=function(){var t=this,n=t.$createElement;t._self._c},i=[];e.d(n,"a",function(){return c}),e.d(n,"b",function(){return i})},"3eec":function(t,n,e){},"59ca":function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var c={name:"app-special-topic",props:{topic_list:{type:Array,default:function(){return[]}},count:{type:Number,default:function(){return 2}},icon:String,logo_1:String,logo_2:String},computed:{newDataList:function(){if(2===this.count){for(var t=[],n=0;n<Math.ceil(this.topic_list.length/this.count);n++)t.push(this.topic_list.slice(n*this.count,(n+1)*this.count));return t}}}};n.default=c},"798d":function(t,n,e){"use strict";var c=e("3eec"),i=e.n(c);i.a},a70e:function(t,n,e){"use strict";e.r(n);var c=e("59ca"),i=e.n(c);for(var u in c)"default"!==u&&function(t){e.d(n,t,function(){return c[t]})}(u);n["default"]=i.a}}]);
;(my["webpackJsonp"] = my["webpackJsonp"] || []).push([
    'components/page-component/app-special-topic/app-special-topic-normal-create-component',
    {
        'components/page-component/app-special-topic/app-special-topic-normal-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('c11b')['createComponent'](__webpack_require__("26a4"))
        })
    },
    [['components/page-component/app-special-topic/app-special-topic-normal-create-component']]
]);                
