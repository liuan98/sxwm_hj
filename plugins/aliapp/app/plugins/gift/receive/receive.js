(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["plugins/gift/receive/receive"],{"61d5":function(t,i,e){"use strict";var n=function(){var t=this,i=t.$createElement;t._self._c},s=[];e.d(i,"a",function(){return n}),e.d(i,"b",function(){return s})},"6c6b":function(t,i,e){"use strict";(function(t){Object.defineProperty(i,"__esModule",{value:!0}),i.default=void 0;var n=a(e("a34a")),s=e("2f62");function a(t){return t&&t.__esModule?t:{default:t}}function r(t,i){var e=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);i&&(n=n.filter(function(i){return Object.getOwnPropertyDescriptor(t,i).enumerable})),e.push.apply(e,n)}return e}function o(t){for(var i=1;i<arguments.length;i++){var e=null!=arguments[i]?arguments[i]:{};i%2?r(e,!0).forEach(function(i){u(t,i,e[i])}):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(e)):r(e).forEach(function(i){Object.defineProperty(t,i,Object.getOwnPropertyDescriptor(e,i))})}return t}function u(t,i,e){return i in t?Object.defineProperty(t,i,{value:e,enumerable:!0,configurable:!0,writable:!0}):t[i]=e,t}function c(t,i,e,n,s,a,r){try{var o=t[a](r),u=o.value}catch(c){return void e(c)}o.done?i(u):Promise.resolve(u).then(n,s)}function f(t){return function(){var i=this,e=arguments;return new Promise(function(n,s){var a=t.apply(i,e);function r(t){c(a,n,s,r,o,"next",t)}function o(t){c(a,n,s,r,o,"throw",t)}r(void 0)})}}var g=function(){return e.e("plugins/gift/components/announcement/share-gift-text").then(e.bind(null,"9827"))},l=function(){return e.e("plugins/gift/components/receive/receive-content").then(e.bind(null,"cc41"))},h={name:"receive",data:function(){return{loading:!1,big:"",small:"",status:"",gift_status:-1,gift_id:-1,gift_detail:{list:{type:"direct_open"}},is_play:!1}},onLoad:function(i){this.gift_id=i.gift_id,this.gift_status=i.status,this.$store.dispatch("gift/getConfig",this.$api.gift.config),this.request(),t.hideShareMenu();var e=this;this.innerAudioContext=t.createInnerAudioContext(),this.innerAudioContext.autoplay=!0,this.innerAudioContext.onEnded(function(t){e.is_play=!1})},onHide:function(){this.is_play=!1,this.innerAudioContext.stop()},onShareAppMessage:function(){return this.$shareAppMessage({path:"/plugins/gift/index/index",title:this.gift_detail.list.bless_word,params:{gift_id:this.gift_id},bgImgUrl:0===this.gift_detail.is_big_gift?this.gift_detail.list.sendOrder[0].detail[0].goods.goodsWarehouse.cover_pic:this.big_gift_pic,imageUrl:0===this.gift_detail.is_big_gift?this.gift_detail.list.sendOrder[0].detail[0].goods.goodsWarehouse.cover_pic:this.big_gift_pic})},methods:{request:function(){var t=f(n.default.mark(function t(){var i,e,s,a,r;return n.default.wrap(function(t){while(1)switch(t.prev=t.next){case 0:return this.$utils.showLoading(),t.next=3,this.$request({url:this.$api.gift.gift,methods:"get",data:{gift_id:this.gift_id}});case 3:i=t.sent,this.$utils.hideLoading(),this.gift_detail=i.data,0!=this.gift_status?"num_open"===this.gift_detail.list.type?0===this.gift_detail.open_status?(this.big="参与成功，等待开奖",this.small="满".concat(this.gift_detail.list.open_num,"人开奖")):0===this.gift_detail.status?(this.big="很遗憾，你未中奖",this.status="未中奖"):(this.big="恭喜你，中奖了",this.status="中奖了"):"time_open"===this.gift_detail.list.type?0===this.gift_detail.open_status?(this.big="参与成功，等待开奖",e=new Date(this.gift_detail.list.open_time.replace(/-/g,"/")),s=e.getMonth()+1,a=e.getDate(),this.small="".concat(s>10?s:"0"+s,"月").concat(a>10?a:"0"+a,"日 ").concat(e.getHours(),":").concat(e.getMinutes()>10?e.getMinutes():"0"+e.getMinutes(),":00 开奖")):0===this.gift_detail.status?(this.big="很遗憾，你未中奖",this.status="未中奖"):(this.big="恭喜你，中奖了",this.status="中奖了"):"direct_open"===this.gift_detail.list.type&&(1===this.gift_detail.status?(this.big="礼物领取成功",this.status="已领取"):(this.big="礼物已被抢光",this.status="已抢光")):(this.big=this.gift_detail.win_goods_name,"time_open"===this.gift_detail.list.type&&(r=new Date(this.gift_detail.list.open_time.replace(/-/g,"/")),this.small="".concat(r.getMonth()+1>10?r.getMonth()+1:"0"+r.getMonth()+1,"月").concat(r.getDate()>10?r.getDate():"0"+r.getDate(),"日 ").concat(r.getHours(),":").concat(r.getMinutes()>10?r.getMinutes():"0"+r.getMinutes(),":00 开奖"))),this.loading=!0;case 8:case"end":return t.stop()}},t,this)}));function i(){return t.apply(this,arguments)}return i}(),play:function(t,i){this.is_play=t,t?(this.innerAudioContext.src=i,this.innerAudioContext.play()):this.innerAudioContext.stop()}},computed:o({},(0,s.mapState)("gift",{theme:function(t){return t.theme},big_gift_pic:function(t){return t.big_gift_pic}})),components:{"share-gift-text":g,"receive-content":l}};i.default=h}).call(this,e("c11b")["default"])},"97b6":function(t,i,e){"use strict";e.r(i);var n=e("6c6b"),s=e.n(n);for(var a in n)"default"!==a&&function(t){e.d(i,t,function(){return n[t]})}(a);i["default"]=s.a},aaa2:function(t,i,e){"use strict";var n=e("ee9f"),s=e.n(n);s.a},e7e8a:function(t,i,e){"use strict";e.r(i);var n=e("61d5"),s=e("97b6");for(var a in s)"default"!==a&&function(t){e.d(i,t,function(){return s[t]})}(a);e("aaa2");var r=e("2877"),o=Object(r["a"])(s["default"],n["a"],n["b"],!1,null,"b5952830",null);i["default"]=o.exports},ee9f:function(t,i,e){}},[["940e","common/runtime","common/vendor"]]]);