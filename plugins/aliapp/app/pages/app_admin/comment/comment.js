(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["pages/app_admin/comment/comment"],{"1bb2":function(t,i,e){"use strict";(function(t){function e(t){return o(t)||n(t)||s()}function s(){throw new TypeError("Invalid attempt to spread non-iterable instance")}function n(t){if(Symbol.iterator in Object(t)||"[object Arguments]"===Object.prototype.toString.call(t))return Array.from(t)}function o(t){if(Array.isArray(t)){for(var i=0,e=new Array(t.length);i<t.length;i++)e[i]=t[i];return e}}Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var a={name:"comment",data:function(){return{list:[],over:!1,page:1,is_reply:2,score:3}},onLoad:function(){var t=this;this.$request({url:this.$api.app_admin.comments,data:{score:3,is_reply:2,page:1}}).then(function(i){0===i.code&&(t.list=i.data.list)})},onReachBottom:function(){this.over||(this.page++,this.request())},methods:{setActiveNav:function(i){var e=this;this.page=1,this.score=i,this.is_reply=2,this.over=!1,t.pageScrollTo({scrollTop:0}),this.$request({url:this.$api.app_admin.comments,data:{score:this.score,is_reply:this.is_reply,page:this.page}}).then(function(t){0===t.code&&(e.list=t.data.list)})},setActiveReply:function(t){var i=this;this.page=1,this.is_reply=t,this.over=!1,this.$request({url:this.$api.app_admin.comments,data:{score:this.score,is_reply:this.is_reply,page:this.page}}).then(function(t){0===t.code&&(i.list=t.data.list)})},request:function(){var t=this;this.$request({url:this.$api.app_admin.comments,data:{score:this.score,is_reply:this.is_reply,page:this.page}}).then(function(i){0===i.code&&(i.data.list.length>0?t.list=[].concat(e(t.list),e(i.data.list)):t.over=!0)})},isTop:function(i,e){var s=this,n=1==i.is_top?0:1;s.$request({url:s.$api.app_admin.comments_top,method:"POST",data:{status:n,id:i.id}}).then(function(o){0===o.code&&(i.is_top=n,s.list.splice(e,1,i),t.showToast({title:o.msg,icon:"none"}))})},isShow:function(t){var i=this;0===t.is_show?this.$request({url:this.$api.app_admin.comments_show,method:"POST",data:{is_show:1,id:t.id}}).then(function(e){if(0===e.code)for(var s=0;s<i.list.length;s++)t.id===i.list[s].id&&(i.list[s].is_show=1)}):this.$request({url:this.$api.app_admin.comments_show,method:"POST",data:{is_show:0,id:t.id}}).then(function(e){if(0===e.code)for(var s=0;s<i.list.length;s++)t.id===i.list[s].id&&(i.list[s].is_show=0)})}}};i.default=a}).call(this,e("c11b")["default"])},"1e58":function(t,i,e){},3959:function(t,i,e){"use strict";var s=e("1e58"),n=e.n(s);n.a},"430c":function(t,i,e){"use strict";e.r(i);var s=e("1bb2"),n=e.n(s);for(var o in s)"default"!==o&&function(t){e.d(i,t,function(){return s[t]})}(o);i["default"]=n.a},"8bcd":function(t,i,e){"use strict";var s=function(){var t=this,i=t.$createElement;t._self._c},n=[];e.d(i,"a",function(){return s}),e.d(i,"b",function(){return n})},b56c:function(t,i,e){"use strict";e.r(i);var s=e("8bcd"),n=e("430c");for(var o in n)"default"!==o&&function(t){e.d(i,t,function(){return n[t]})}(o);e("3959");var a=e("2877"),r=Object(a["a"])(n["default"],s["a"],s["b"],!1,null,"2375c9ba",null);i["default"]=r.exports}},[["6448","common/runtime","common/vendor"]]]);