(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["plugins/advance/search/search"],{"28af":function(t,e,n){"use strict";var r=n("bb6c"),a=n.n(r);a.a},"39ad":function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var r=a(n("a34a"));function a(t){return t&&t.__esModule?t:{default:t}}function s(t,e,n,r,a,s,i){try{var o=t[s](i),c=o.value}catch(u){return void n(u)}o.done?e(c):Promise.resolve(c).then(r,a)}function i(t){return function(){var e=this,n=arguments;return new Promise(function(r,a){var i=t.apply(e,n);function o(t){s(i,r,a,o,c,"next",t)}function c(t){s(i,r,a,o,c,"throw",t)}o(void 0)})}}function o(t){return h(t)||u(t)||c()}function c(){throw new TypeError("Invalid attempt to spread non-iterable instance")}function u(t){if(Symbol.iterator in Object(t)||"[object Arguments]"===Object.prototype.toString.call(t))return Array.from(t)}function h(t){if(Array.isArray(t)){for(var e=0,n=new Array(t.length);e<t.length;e++)n[e]=t[e];return n}}var l=function(){return Promise.all([n.e("common/vendor"),n.e("plugins/advance/components/index-product-list")]).then(n.bind(null,"51af"))},f="ADVANCE_SEARCH",d={name:"search",data:function(){return{search_text:"",search_list:[],strong:[],search:!1,page:1,over:!1,interval:0}},onLoad:function(){this.$storage.getStorageSync(f)?this.strong=this.$storage.getStorageSync(f):this.$storage.setStorageSync(f,[])},onHide:function(){clearInterval(this.interval)},onUnload:function(){clearInterval(this.interval)},onReachBottom:function(){var t=this;this.over||(this.page+=1,this.$request({url:this.$api.advance.goods,method:"get",data:{keyword:this.search_text,page:this.page}}).then(function(e){0===e.code&&(e.data.list.length>0?t.search_list=[].concat(o(t.search_list),o(e.data.list)):t.over=!0)}))},methods:{empyt_search:function(){this.search_text="",this.search_list=[],this.search=!1,clearInterval(this.interval)},request:function(){var t=i(r.default.mark(function t(){var e,n,a;return r.default.wrap(function(t){while(1)switch(t.prev=t.next){case 0:if(this.search=!0,this.page=1,e=this.$storage.getStorageSync(f),!this.search_text.match(/^[ ]*$/)){t.next=5;break}return t.abrupt("return");case 5:return n=[].concat(o(e),[this.search_text]),t.next=8,this.$request({url:this.$api.advance.goods,method:"get",data:{keyword:this.search_text,page:this.page}});case 8:a=t.sent,0===a.code&&(this.search_list=a.data.list,this.set_interval(),this.$storage.setStorageSync(f,n));case 10:case"end":return t.stop()}},t,this)}));function e(){return t.apply(this,arguments)}return e}(),empty_strong:function(){this.$storage.removeStorageSync(f),this.strong=[]},search_strong:function(t){this.search_text=t,this.request()},set_interval:function(){var t=this;clearInterval(this.interval),this.interval=setInterval(function(){var e=(new Date).getTime();0===t.search_list.length&&clearInterval(t.interval);for(var n=0;n<t.search_list.length;n++){var r=new Date(t.search_list[n].advanceGoods.end_prepayment_at.replace(/-/g,"/")).getTime(),a=r-e;if(a>0){var s=parseInt(a/1e3/60/60/24%30),i=parseInt(a/1e3/60/60%24),o=parseInt(a/1e3/60%60),c=parseInt(a/1e3%60);s>0?t.$set(t.search_list[n],"html",s+"天"+i+":"+(o<10?"0"+o:o)+":"+(c<10?"0"+c:c)):t.$set(t.search_list[n],"html",i+":"+(o<10?"0"+o:o)+":"+(c<10?"0"+c:c))}else t.$delete(t.search_list,n),t.search_list.length<10&&t.page_count>1&&t.$request({url:t.$api.advance.goods,method:"get"}).then(function(e){0===e.code&&(t.search_list=e.data.list,t.set_interval())})}},1e3)}},components:{"index-product-list":l}};e.default=d},"41a2":function(t,e,n){"use strict";var r=function(){var t=this,e=t.$createElement;t._self._c},a=[];n.d(e,"a",function(){return r}),n.d(e,"b",function(){return a})},"5f47":function(t,e,n){"use strict";n.r(e);var r=n("41a2"),a=n("919a");for(var s in a)"default"!==s&&function(t){n.d(e,t,function(){return a[t]})}(s);n("28af");var i=n("2877"),o=Object(i["a"])(a["default"],r["a"],r["b"],!1,null,"0c21122c",null);e["default"]=o.exports},"919a":function(t,e,n){"use strict";n.r(e);var r=n("39ad"),a=n.n(r);for(var s in r)"default"!==s&&function(t){n.d(e,t,function(){return r[t]})}(s);e["default"]=a.a},bb6c:function(t,e,n){}},[["fbf7","common/runtime","common/vendor"]]]);