(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["pages/order/clerk/clerk"],{2444:function(e,t,n){"use strict";n.r(t);var i=n("9af6"),o=n("ef5d");for(var r in o)"default"!==r&&function(e){n.d(t,e,function(){return o[e]})}(r);n("d442");var a=n("2877"),c=Object(a["a"])(o["default"],i["a"],i["b"],!1,null,"6680ec74",null);t["default"]=c.exports},"4fe9":function(e,t,n){},"9af6":function(e,t,n){"use strict";var i=function(){var e=this,t=e.$createElement;e._self._c},o=[];n.d(t,"a",function(){return i}),n.d(t,"b",function(){return o})},d442:function(e,t,n){"use strict";var i=n("4fe9"),o=n.n(i);o.a},df65:function(e,t,n){"use strict";(function(e){Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var i=function(){return n.e("components/page-component/app-order-goods-info/app-order-goods-info").then(n.bind(null,"1602"))},o={components:{"app-order-goods-info":i},data:function(){return{id:null,orderDetail:{},clerk_remark:"",is_show:!1}},methods:{getOrderDetail:function(){var e=this;this.$showLoading(),this.$request({url:this.$api.order.detail,data:{id:this.id,action_type:1}}).then(function(t){e.$hideLoading(),e.is_show=!0,0===t.code&&(e.orderDetail=t.data.detail)}).catch(function(){e.$hideLoading()})},clerkAffirmPay:function(){var t=this;e.showModal({title:"提示",content:"确认已进行线下收款?",success:function(n){var i=this;n.confirm&&(e.showLoading({title:"加载中"}),t.$request({url:t.$api.order.clerk_affirm_pay,data:{id:t.id,action_type:1}}).then(function(n){e.hideLoading(),0===n.code?t.getOrderDetail():e.showToast({title:n.msg,icon:"none"}),i.msg=n.data.msg}).catch(function(){e.hideLoading()}))}})},orderClerk:function(){var t=this;e.showModal({content:"是否核销订单？",success:function(n){n.confirm&&(t.$showLoading(),t.$request({url:t.$api.order.order_clerk,data:{id:t.id,action_type:1,clerk_remark:t.clerk_remark}}).then(function(n){t.$hideLoading(),e.showToast({title:n.msg,icon:"none",duration:2e3,success:function(){0===n.code&&setTimeout(function(){e.redirectTo({url:"/plugins/clerk/order/order?status=1&type=1"})},2e3)}})}).catch(function(){t.$hideLoading()}))}})},closeDialog:function(){if("核销成功"==this.msg)if(this.msg="",this.is_clerk){var t,n=getCurrentPages();n.forEach(function(e,i){"plugins/clerk/order/order"===n[i].route&&(t=i)}),t>-1?(n[t]._num=1,e.navigateBack({delta:n.length-1-t})):e.redirectTo({url:"/plugins/clerk/order/order?status=1&type=1"})}else e.redirectTo({url:"/pages/index/index"});else this.msg=""}},onLoad:function(e){this.id=e.id,this.getOrderDetail()}};t.default=o}).call(this,n("c11b")["default"])},ef5d:function(e,t,n){"use strict";n.r(t);var i=n("df65"),o=n.n(i);for(var r in i)"default"!==r&&function(e){n.d(t,e,function(){return i[e]})}(r);t["default"]=o.a}},[["b461","common/runtime","common/vendor"]]]);