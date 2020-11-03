;my.defineComponent || (my.defineComponent = Component);(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["components/basic-component/app-timer/app-timer"],{"04a0":function(t,e,n){"use strict";var r=function(){var t=this,e=t.$createElement;t._self._c},a=[];n.d(e,"a",function(){return r}),n.d(e,"b",function(){return a})},"4ef98":function(t,e,n){"use strict";n.r(e);var r=n("04a0"),a=n("5929");for(var i in a)"default"!==i&&function(t){n.d(e,t,function(){return a[t]})}(i);var u=n("2877"),c=Object(u["a"])(a["default"],r["a"],r["b"],!1,null,null,null);e["default"]=c.exports},5929:function(t,e,n){"use strict";n.r(e);var r=n("c6c6"),a=n.n(r);for(var i in r)"default"!==i&&function(t){n.d(e,t,function(){return r[t]})}(i);e["default"]=a.a},c6c6:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var r={name:"app-timer",data:function(){return{time:null,html:""}},props:{startTime:{type:String,default:function(){return"2019-8-30 10:00:00"}},color:{type:String,default:function(){return"white"}},fontSize:{type:String,default:function(){return"26"}}},beforeDestroy:function(){clearInterval(this.time)},watch:{startTime:{handler:function(t){var e=this,n=new Date(t.replace(/-/g,"/"));this.time=setInterval(function(){var t=new Date,r=n.getTime()-t.getTime(),a=parseInt(r/1e3/60/60/24%30),i=parseInt(r/1e3/60/60%24),u=parseInt(r/1e3/60%60),c=parseInt(r/1e3%60);e.html=a+"天"+i+":"+(u<10?"0"+u:u)+":"+(c<10?"0"+c:c)},1e3)},immediate:!0}}};e.default=r}}]);
;(my["webpackJsonp"] = my["webpackJsonp"] || []).push([
    'components/basic-component/app-timer/app-timer-create-component',
    {
        'components/basic-component/app-timer/app-timer-create-component':(function(module, exports, __webpack_require__){
            __webpack_require__('c11b')['createComponent'](__webpack_require__("4ef98"))
        })
    },
    [['components/basic-component/app-timer/app-timer-create-component']]
]);                
