(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["plugins/bargain/goods/goods"],{"1aec":function(n,e,t){"use strict";t.r(e);var i=t("6891"),a=t("bf15");for(var o in a)"default"!==o&&function(n){t.d(e,n,function(){return a[n]})}(o);t("392a");var r=t("2877"),s=Object(r["a"])(a["default"],i["a"],i["b"],!1,null,"d6d4a0f2",null);e["default"]=s.exports},"392a":function(n,e,t){"use strict";var i=t("f9f0"),a=t.n(i);a.a},6891:function(n,e,t){"use strict";var i=function(){var n=this,e=n.$createElement,t=(n._self._c,Number(120));n.$mp.data=Object.assign({},{$root:{m0:t}})},a=[];t.d(e,"a",function(){return i}),t.d(e,"b",function(){return a})},ad61:function(n,t,i){"use strict";(function(n){Object.defineProperty(t,"__esModule",{value:!0}),t.default=void 0;var a=i("2f62");function o(n,e){var t=Object.keys(n);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(n);e&&(i=i.filter(function(e){return Object.getOwnPropertyDescriptor(n,e).enumerable})),t.push.apply(t,i)}return t}function r(n){for(var e=1;e<arguments.length;e++){var t=null!=arguments[e]?arguments[e]:{};e%2?o(t,!0).forEach(function(e){s(n,e,t[e])}):Object.getOwnPropertyDescriptors?Object.defineProperties(n,Object.getOwnPropertyDescriptors(t)):o(t).forEach(function(e){Object.defineProperty(n,e,Object.getOwnPropertyDescriptor(t,e))})}return n}function s(n,e,t){return e in n?Object.defineProperty(n,e,{value:t,enumerable:!0,configurable:!0,writable:!0}):n[e]=t,n}var u=function(){return Promise.all([i.e("common/vendor"),i.e("components/page-component/app-quick-navigation/app-quick-navigation")]).then(i.bind(null,"4d92"))},c=function(){return Promise.all([i.e("common/vendor"),i.e("components/basic-component/app-rich/parse")]).then(i.bind(null,"cb0e"))},g=function(){return i.e("components/page-component/goods/app-goods-banner").then(i.bind(null,"12b6"))},p=function(){return i.e("components/page-component/goods/app-goods-service").then(i.bind(null,"311c"))},d=function(){return i.e("components/page-component/app-iphonex-bottom/app-iphonex-bottom").then(i.bind(null,"bc3b"))},l=function(){return i.e("components/page-component/app-share-qr-code-poster/app-share-qr-code-poster").then(i.bind(null,"409e"))},b=function(){return i.e("components/basic-component/app-iphone-x/app-iphone-x").then(i.bind(null,"7598"))},f=function(){return i.e("components/basic-component/app-empty-bottom/app-empty-bottom").then(i.bind(null,"0c06"))},m=function(){return i.e("components/page-component/goods/app-goods-marketing").then(i.bind(null,"1143"))},h={name:"goods",components:{appQuickNavigation:u,appRichText:c,appGoodsBanner:g,appService:p,appShareQrCode:l,appIphonexBottom:d,appEmptyBottom:f,appIphoneX:b,appGoodsMarketing:m},computed:r({},(0,a.mapState)({appImg:function(n){return n.mallConfig.plugin.bargain.app_image},userInfo:function(n){return n.user.info},isUnderlinePrice:function(n){return n.mallConfig.mall.setting.is_underline_price}}),{},(0,a.mapState)("gConfig",{iphone:function(n){return n.iphone},iphoneHeight:function(n){return n.iphoneHeight}})),data:function(){return{timeIntegral:null,goods_id:"",bargain:null,finish_list:null,begin_list:null,end_list:null,shareShow:!1,title:"砍价",page:1,circuit:[{name:"点击砍价",url:"./../image/bargain-click.png"},{name:"",url:"./../image/bargain-jiantou.png"},{name:"找人砍价",url:"./../image/bargain-help.png"},{name:"",url:"./../image/bargain-jiantou.png"},{name:"价格合适",url:"./../image/bargain-price.png"},{name:"",url:"./../image/bargain-jiantou.png"},{name:"优惠购买",url:"./../image/bargain-buy.png"}],poster:this.$api.bargain.poster}},onLoad:function(e){var t=this;t.$store.dispatch("user/info"),t.goods_id=e.goods_id,t.$showLoading(),t.$request({url:t.$api.bargain.goods_detail,data:{goods_id:t.goods_id}}).then(function(e){if(t.$hideLoading(),0===e.code){if(t.bargain=e.data.bargain,t.bargain.bargain_info){var i=function(){if(t.bargain.bargain_info){var n=[t.setTimeStart(t.bargain.bargain_info.finish_at),t.setTimeStart(t.bargain.begin_time),t.setTimeStart(t.bargain.end_time)];t.finish_list=n[0],t.begin_list=n[1],t.end_list=n[2]}else clearInterval(t.timeIntegral)};i(),t.timeIntegral=setInterval(function(){i()},1e3)}}else n.showToast({icon:"none",title:e.msg})}).catch(function(n){t.$hideLoading()})},onUnload:function(){clearInterval(this.timeIntegral)},onShareAppMessage:function(){return this.$shareAppMessage({title:this.bargain.goods.app_share_title?this.bargain.goods.app_share_title:this.bargain.name,path:"/plugins/bargain/goods/goods",imageUrl:this.bargain.goods.app_share_pic?this.bargain.goods.app_share_pic:this.bargain.cover_pic,params:{goods_id:this.bargain.goods_id}})},methods:{bargainFriend:function(){n.navigateTo({url:"/plugins/bargain/activity/activity?id="+this.bargain.bargain_info.bargain_order_id})},userList:function(){var n=this,t=this;t.$request({url:t.$api.bargain.goods_detail,data:{goods_id:t.goods_id}}).then(function(i){if(0===i.code&&i.data.bargain.bargain_info){var a=e.data.bargain.bargain_info.list;t.setData({user_list:a}),n.finishTime&&setTimeout(function(){n.userList()},5e3)}})},setTimeStart:function(n){var e=n.replace(/-/g,"/"),t=parseInt((new Date(e).getTime()-(new Date).getTime())/1e3),i=0,a=0,o=0,r=0;return t>0?(i=Math.floor(t/86400),a=Math.floor(t/3600)-24*i,o=Math.floor(t/60)-24*i*60-60*a,r=Math.floor(t)-24*i*60*60-60*a*60-60*o,{d:i,h:a<10?"0"+a:a,m:o<10?"0"+o:o,s:r<10?"0"+r:r}):null},subscribe:function(){var e=this;this.$subscribe(this.bargain.template_message).then(function(t){var i=e.bargain.template_message[0];"accept"==t[i]?n.showModal({title:"提示",content:"订阅成功",showCancel:!1,success:function(n){e.save()}}):n.showModal({title:"提示",content:"取消订阅",showCancel:!1,success:function(n){e.save()}})}).catch(function(n){e.save()})},save:function(){var e=this;e.$showLoading(),e.$request({url:e.$api.bargain.bargain_submit,data:{goods_id:e.bargain.goods_id}}).then(function(t){e.$hideLoading(),0==t.code?e.bargainResult(t):n.showToast({icon:"none",title:t.msg})}).catch(function(n){e.$hideLoading()})},bargainResult:function(e){var t=this;t.$showLoading(),t.$request({url:t.$api.bargain.bargain_result,data:{queueId:e.data.queueId,token:e.data.token}}).then(function(i){if(0===i.code){if(i.data.retry)return void setTimeout(function(){t.bargainResult(e)},1e3);t.$hideLoading(),n.redirectTo({url:"/plugins/bargain/activity/activity?order_id="+i.data.bargain_order_id})}else t.$hideLoading(),n.showToast({icon:"none",title:i.msg})}).catch(function(n){t.$hideLoading()})},submit:function(){var e=this.bargain,t=[{mch_id:0,bargain_order_id:e.bargain_info.bargain_order_id,goods_list:[{id:e.goods_id,attr:[],num:1,cart_id:0,goods_attr_id:e.goods_attr_id}]}];n.navigateTo({url:"/pages/order-submit/order-submit?mch_list="+JSON.stringify(t)+"&preview_url="+encodeURIComponent(this.$api.bargain.order_preview)+"&submit_url="+encodeURIComponent(this.$api.bargain.order_submit)})},shareClick:function(){this.shareShow=!0}}};t.default=h}).call(this,i("c11b")["default"])},bf15:function(n,e,t){"use strict";t.r(e);var i=t("ad61"),a=t.n(i);for(var o in i)"default"!==o&&function(n){t.d(e,n,function(){return i[n]})}(o);e["default"]=a.a},f9f0:function(n,e,t){}},[["4f83","common/runtime","common/vendor"]]]);