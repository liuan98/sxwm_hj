(my["webpackJsonp"]=my["webpackJsonp"]||[]).push([["plugins/mch/mch/order/order"],{"08e6":function(t,e,i){"use strict";(function(t){Object.defineProperty(e,"__esModule",{value:!0}),e.default=void 0;var n=i("2f62");function s(t,e){var i=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter(function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable})),i.push.apply(i,n)}return i}function a(t){for(var e=1;e<arguments.length;e++){var i=null!=arguments[e]?arguments[e]:{};e%2?s(i,!0).forEach(function(e){o(t,e,i[e])}):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(i)):s(i).forEach(function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(i,e))})}return t}function o(t,e,i){return e in t?Object.defineProperty(t,e,{value:i,enumerable:!0,configurable:!0,writable:!0}):t[e]=i,t}for(var d=function(){return Promise.all([i.e("common/vendor"),i.e("components/basic-component/app-layout/app-layout")]).then(i.bind(null,"0b17"))},r=new Date,c=[],h=[],u=[],l=2015;l<=r.getFullYear();l++)c.push(l);for(var f=1;f<=12;f++)h.push(f);for(var m=1;m<=31;m++)u.push(m);var p={data:function(){return{mch_id:0,cancelRefund:!1,time_start:[],noAddress:!1,years:c,months:h,days:u,status:"1",_num:"0",more_list:!1,addressId:"0",refund_price:0,page:1,notRefund:!1,openAddress:!1,isReason:!1,menu:[{name:"待收货",value:"3"},{name:"待处理",value:"7"},{name:"已取消",value:"6"},{name:"已完成",value:"8"}],active:null,show:!1,start:[],end:[],search:!1,keyword:"",list:[],candidate:[],date_start:"",date_end:"",time:0,inSearch:!1,address:[],today:"",yesterday:"",weekday:"",chooseTime:!1,isRefund:!1,noRefund:!1,cancelOrder:!1,confirmOrder:!1,detail:{},changePrice:!1,callPhone:!1,custom:!1,mobile:"",isSend:!1,sendType:0,price:0,express:0,msg:!1,first:!1,searchResult:!1,total:0,about:"",actions:[{name:"取消",color:"#666666"},{name:"去设置",color:"#ff4544",loading:!1}]}},components:{"app-layout":d},computed:a({},(0,n.mapState)({theme:function(t){return t.mallConfig.theme},userInfo:function(t){return t.user.info},adminImg:function(t){return t.mallConfig.__wxapp_img.mch}})),methods:{toDetail:function(e,i){var n=e.order_id;2==i&&(n=e.id),t.navigateTo({url:"/plugins/mch/mch/order-detail/order-detail?id="+n+"&status="+i+"&mch_id="+this.mch_id})},toRedirect:function(e){t.redirectTo({url:e})},cancel:function(){this.about="",this.date_start="",this.date_end="",this.isRefund=!1,this.chooseTime=!1,this.noRefund=!1,this.changePrice=!1,this.cancelOrder=!1,this.confirmOrder=!1,this.callPhone=!1,this.isSend=!1,this.notRefund=!1,this.openAddress=!1,this.isReason=!1,this.addressId=0,this.noAddress=!1},tabStatus:function(t){var e=this;e.status=t,e.active=null,e.list=[],e.date_start="",e.date_end="",e.keyword="",e.time=0,e.show=!1,e._num="0",2==t&&(e._num=0),e.page=1,this.getList()},toCall:function(t){1==this.status?this.mobile=t.mobile:this.mobile=t.order.mobile,this.callPhone=!this.callPhone},beConfirm:function(){var e=this;e.$request({url:e.$api.app_admin.shou_huo,data:{refund_order_id:e.detail.id},method:"post"}).then(function(i){e.$hideLoading(),0==i.code?(t.showToast({title:i.msg,duration:1e3,type:"success",mask:!1}),e.cancel(),setTimeout(function(i){e.list=[],e.page=1,t.showLoading({title:"加载中..."}),e.getList()},1e3)):t.showToast({title:i.msg,icon:"none",duration:1e3})}).catch(function(t){e.$hideLoading()})},call:function(){t.makePhoneCall({phoneNumber:this.mobile}),this.callPhone=!this.callPhone},toSearch:function(){this.search=!0,this.list=[],this.searchResult=!1,this.candidate=t.getStorageSync("mch_keyword"),this.inSearch=!1},keywordSearch:function(t){this.keyword=t,this.searchResult=!0,this.getList()},clear:function(){var e=this;t.removeStorage({key:"mch_keyword",success:function(i){e.candidate=[],t.showToast({title:"清理成功",duration:1e3,type:"success",mask:!1})}})},searchMethod:function(){var e=t.getStorageSync("mch_keyword");0!=this.keyword.length&&(e.length>0?e.unshift(this.keyword):e=[this.keyword],this.page=1,this.getList(),e.forEach(function(t,i){e[0]==e[i]&&i>0&&e.splice(i,1)}),t.setStorage({key:"mch_keyword",data:e}),this.inSearch=!0,this.searchResult=!0)},cancelSeacrch:function(){this.search=!1,this.keyword="",this.list=[],this.page=1,this.getList()},change:function(t){var e=this;e.time=t,e.custom=!1,4==e.time&&(e.custom=!0,e.date_end=e.today,e.date_start=e.today)},toChoose:function(){var e=this;if(4!=e.time){switch(e.date_start="",e.date_end=e.today,e.time.toString()){case"0":e.date_start="",e.date_end="";break;case"1":e.date_start=e.today;break;case"2":e.date_start=e.yesterday,e.date_end=e.yesterday;break;case"3":e.date_start=e.weekday;break}setTimeout(function(){e.list=[],e.page=1,e.getList(),e.chooseTime=!1},300)}else{var i=e.date_end.substring(0,10),n=e.date_start.substring(0,10),s=i.split("-"),a=n.split("-");+s[0]<+a[0]?t.showToast({title:"结束时间不应早于开始时间",icon:"none",duration:1e3}):+s[0]==+a[0]?+s[1]<+a[1]?t.showToast({title:"结束时间不应早于开始时间",icon:"none",duration:1e3}):+s[1]==+a[1]&&+s[2]<+a[2]?t.showToast({title:"结束时间不应早于开始时间",icon:"none",duration:1e3}):(e.list=[],e.page=1,e.getList(),e.chooseTime=!1):(e.list=[],e.page=1,e.getList(),e.chooseTime=!1)}},startChange:function(t){var e=t.detail.value,i=(this.years,this.years[e[0]]),n=this.months[e[1]],s=this.days[e[2]];n>=1&&n<=9&&(n="0"+n),s>=1&&s<=9&&(s="0"+s),this.date_start=i+"-"+n+"-"+s},endChange:function(t){var e=t.detail.value,i=this.years[e[0]],n=this.months[e[1]],s=this.days[e[2]];n>=1&&n<=9&&(n="0"+n),s>=1&&s<=9&&(s="0"+s),this.date_end=i+"-"+n+"-"+s},toTime:function(){var t,e,i=this;i.start=[],i.end=[],t=i.date_start?i.date_start:i.today,e=i.date_end?i.date_end:i.today,i.years.forEach(function(e,n){t.substring(0,4)==i.years[n]&&(i.start[0]=+n)}),i.months.forEach(function(e,n){t.substring(5,7)==i.months[n]&&(i.start[1]=+n)}),i.days.forEach(function(e,n){t.substring(8,10)==i.days[n]&&(i.start[2]=+n)}),i.date_end&&(e=i.date_end),i.years.forEach(function(t,n){e.substring(0,4)==i.years[n]&&(i.end[0]=+n)}),i.months.forEach(function(t,n){e.substring(5,7)==i.months[n]&&(i.end[1]=+n)}),i.days.forEach(function(t,n){e.substring(8,10)==i.days[n]&&(i.end[2]=+n)}),i.chooseTime=!i.chooseTime,i.show=!1},chooseItem:function(t){var e=this;e._num=t,e.menu.forEach(function(t,i){t.value==e._num&&(e.active=t)}),e.show=!e.show,e.page=1,e.list=[],e.getList()},tab:function(t){this._num=t,this.show=!1,this.active=null,this.list=[],this.date_start="",this.date_end="",this.keyword="",this.page=1,this.getList()},getList:function(){var e=this;e.about="";var i=0;console.log(e.status),2==e.status&&(i=e._num),t.showLoading({title:"加载中..."}),e.$request({url:e.$api.mch.order_list,data:{status:2==e.status?"0":e._num,mch_id:e.mch_id,end_date:e.date_end,start_date:e.date_start,order_type:2==e.status?"refund_order":"order",refund_status:i,page:e.page,keyword:e.keyword}}).then(function(i){if(e.$hideLoading(),t.hideLoading(),e.first=!0,0==i.code){var n=i.data.list;if(2==e.status){var s=i.data.address;s.forEach(function(t,e){t.address=t.address.replace(/"/g,""),t.address=t.address.replace(/,/g,""),t.address=t.address.replace("[",""),t.address=t.address.replace("]","")}),e.address=s}else n.forEach(function(t){t.order_id=t.id,t.detail.forEach(function(e){e.refund_status>0&&(t.have_refund=1)})});e.more_list=!1,n.length==i.data.pagination.pageSize&&(e.more_list=!0),e.page++,e.list=e.list.concat(n),e.$forceUpdate()}else t.showToast({title:i.msg,icon:"none",duration:1e3})}).catch(function(i){e.$hideLoading(),t.hideLoading()})},toCancelorder:function(t){this.detail=t,this.cancelOrder=!this.cancelOrder},toConfirm:function(t){this.detail=t,this.confirmOrder=!this.confirmOrder},cancelSubmit:function(){var e=this;t.showLoading({title:"加载中..."}),e.$request({url:e.$api.mch.cancel,data:{status:1,mch_id:e.mch_id,remark:"",order_id:e.detail.id},method:"post"}).then(function(i){t.hideLoading(),0==i.code?(t.showToast({title:"取消成功",duration:2e3,type:"success",mask:!1}),e.list=[],e.isRefund=!1,e.cancelOrder=!1,e.page=1,e.getList()):t.showToast({title:i.msg,icon:"none",duration:1e3})}).catch(function(e){t.hideLoading(),t.showToast({title:e,icon:"none",duration:1e3})})},toChange:function(t){this.detail=t,this.changePrice=!this.changePrice,this.price=t.total_goods_price,this.express=t.express_price,this.total="￥"+t.total_pay_price},priceInput:function(t){t.detail.value>-.01?this.total="￥"+(+t.detail.value+ +this.express).toFixed(2):this.total="数据有误"},expressInput:function(t){t.detail.value>-.01?this.total="￥"+(+t.detail.value+ +this.price).toFixed(2):this.total="数据有误"},submitChange:function(){var e=this;t.showLoading({title:"加载中..."}),e.price>-.01&&e.express>-.01?e.$request({url:e.$api.mch.update_total_price,data:{order_id:e.detail.id,mch_id:e.mch_id,total_price:e.price,express_price:e.express},method:"post"}).then(function(i){t.hideLoading(),0==i.code?(t.showToast({title:i.msg,duration:2e3,type:"success",mask:!1}),e.page=1,e.list=[],e.changePrice=!1,e.getList()):t.showToast({title:i.msg,icon:"none",duration:1e3})}).catch(function(e){t.hideLoading(),t.showToast({title:e,icon:"none",duration:1e3})}):e.price&&"number"==typeof e.price?e.express&&"number"==typeof e.express||t.showToast({title:"运费必须大于等于0",icon:"none",duration:1e3}):t.showToast({title:"商品总价必须大于等于0",icon:"none",duration:1e3})},toRefundOrder:function(t){return!1},toSend:function(t){this.detail=t,this.isSend=!0},toExpress:function(e,i){var n=e.id,s=e.refund;n>0?t.navigateTo({url:"/plugins/mch/mch/send/send?id="+n+"&is_send="+i+"&mch_id="+this.mch_id}):s&&t.navigateTo({url:"/plugins/mch/mch/send/send?order_refund_id="+s+"&is_send="+i+"&mch_id="+this.mch_id})},toSendType:function(){var e=this;1==e.sendType?e.detail.status_text?(t.showLoading({title:"加载中..."}),e.$request({url:e.$api.mch.refund_handle,data:{is_express:0,merchant_remark:"",mch_id:e.mch_id,type:e.detail.type,is_agree:1,order_refund_id:e.detail.id},method:"post"}).then(function(i){t.hideLoading(),0==i.code?(t.showToast({title:i.msg,type:"success",mask:!1,duration:2e3}),e.list=[],e.page=1,e.isSend=!1,e.sendType=0,e.getList()):t.showToast({title:i.msg,icon:"none",duration:1e3})}).catch(function(e){t.hideLoading(),t.showToast({title:e,icon:"none",duration:1e3})})):(t.showLoading({title:"加载中..."}),e.$request({url:e.$api.mch.order_send,data:{is_express:0,mch_id:e.mch_id,words:"",order_id:e.detail.id},method:"post"}).then(function(i){t.hideLoading(),0==i.code?(t.showToast({title:i.msg,type:"success",mask:!1,duration:2e3}),e.list=[],e.page=1,e.isSend=!1,e.sendType=0,e.getList()):t.showToast({title:i.msg,icon:"none",duration:1e3})}).catch(function(e){t.hideLoading(),t.showToast({title:e,icon:"none",duration:1e3})})):(e.isSend=!1,e.detail.status_text?t.navigateTo({url:"/plugins/mch/mch/send/send?order_refund_id="+e.detail.id+"&mch_id="+e.mch_id}):t.navigateTo({url:"/plugins/mch/mch/send/send?id="+e.detail.id+"&mch_id="+e.mch_id}))},look:function(e){t.previewImage({current:e,urls:[e]})},lookAbout:function(t){this.detail=t,this.isReason=!0},toRefund:function(t){this.detail=t,this.refund_price=t.refund_price,this.isRefund=!this.isRefund},agree:function(){var e=this;t.showLoading({title:"处理中..."}),e.detail.refund_price>0?e.$request({url:e.$api.mch.refund_handle,data:{order_refund_id:e.detail.id,type:e.detail.type,is_agree:1,mch_id:e.mch_id,refund_price:e.refund_price,merchant_remark:e.about},method:"post"}).then(function(i){t.hideLoading(),0==i.code?t.showModal({title:"提示",content:i.msg,showCancel:!1,success:function(t){t.confirm&&(e.list=[],e.notRefund=!1,e.openAddress=!1,e.isRefund=!1,e.addressId=0,e.page=1,e.getList())}}):t.showToast({title:i.msg,icon:"none",duration:1e3})}).catch(function(e){t.hideLoading(),t.showToast({title:e,icon:"none",duration:1e3})}):t.showToast({title:"退款金额需大于零",icon:"none",duration:1e3})},refundHandle:function(t,e,i){1==i&&(this.cancelRefund=!0),1==t?0==this.address.length?this.noAddress=!0:(this.detail=e,this.openAddress=!0):2==t&&(this.detail=e,this.notRefund=!0),this.$forceUpdate()},toAgreeCancel:function(t){this.detail=t,this.isRefund=!this.isRefund},decline:function(e){var i=this;if(1==e&&i.addressId<1)return t.showToast({title:"请选择地址",icon:"none",duration:1e3}),!1;i.cancelRefund&&(e=2),t.showLoading({title:"处理中..."}),i.$request({url:i.$api.mch.refund_handle,data:{order_refund_id:i.detail.id,type:i.detail.type,is_agree:e,mch_id:i.mch_id,address_id:i.addressId,refund_price:i.detail.refund_price,merchant_remark:i.about},method:"post"}).then(function(e){t.hideLoading(),0==e.code?t.showModal({title:"提示",content:e.msg,showCancel:!1,success:function(t){t.confirm&&(i.page=1,i.list=[],i.notRefund=!1,i.cancelOrder=!1,i.openAddress=!1,i.addressId=0,i.getList())}}):t.showToast({title:e.msg,icon:"none",duration:1e3})}).catch(function(e){t.hideLoading(),t.showToast({title:e,icon:"none",duration:1e3})})},chooseAddress:function(t){this.addressId==t?this.addressId="":this.addressId=t},noCancel:function(){var e=this;t.showLoading({title:"处理中..."}),e.$request({url:e.$api.mch.cancel,data:{status:2,remark:e.about,mch_id:e.mch_id,order_id:e.detail.id},method:"post"}).then(function(i){t.hideLoading(),0==i.code?(t.showToast({title:i.msg,type:"success",mask:!1,duration:2e3}),e.list=[],e.page=1,e.noRefund=!1,e.getList()):t.showToast({title:i.msg,icon:"none",duration:1e3})}).catch(function(e){t.hideLoading(),t.showToast({title:e,icon:"none",duration:1e3})})},beNotRefund:function(t){this.detail=t,this.noRefund=!this.noRefund}},onReachBottom:function(){this.more_list&&this.getList()},onShow:function(){!this.search&&this.first&&(this.list=[],this.page=1,this.getList())},onLoad:function(t){var e=this;e.$showLoading({type:"global",text:"加载中..."}),e.status="1",e._num="0",e.mch_id=t.mch_id;var i=new Date,n=i.getFullYear(),s=i.getMonth()+1;s>=1&&s<=9&&(s="0"+s);var a=i.getDate();e.today=n+"-"+s+"-"+a;var o=Date.parse(new Date),d=1e3*(o/1e3-86400),r=new Date(d),c=r.getFullYear(),h=r.getMonth()+1;h>=1&&h<=9&&(h="0"+h);var u=r.getDate();e.yesterday=c+"-"+h+"-"+u;var l=1e3*(o/1e3-604800),f=new Date(l),m=f.getFullYear(),p=f.getMonth()+1;p>=1&&p<=9&&(p="0"+p);var g=f.getDate();e.weekday=m+"-"+p+"-"+g,e.getList()}};e.default=p}).call(this,i("c11b")["default"])},"0bb8":function(t,e,i){"use strict";i.r(e);var n=i("1944"),s=i("e60c");for(var a in s)"default"!==a&&function(t){i.d(e,t,function(){return s[t]})}(a);i("32c2");var o=i("2877"),d=Object(o["a"])(s["default"],n["a"],n["b"],!1,null,"3e5cbbfb",null);e["default"]=d.exports},1944:function(t,e,i){"use strict";var n=function(){var t=this,e=t.$createElement;t._self._c;t._isMounted||(t.e0=function(e){t.show=!t.show},t.e1=function(e){t.show=!t.show},t.e2=function(e){t.sendType=0},t.e3=function(e){t.sendType=1})},s=[];i.d(e,"a",function(){return n}),i.d(e,"b",function(){return s})},"32c2":function(t,e,i){"use strict";var n=i("b244"),s=i.n(n);s.a},b244:function(t,e,i){},e60c:function(t,e,i){"use strict";i.r(e);var n=i("08e6"),s=i.n(n);for(var a in n)"default"!==a&&function(t){i.d(e,t,function(){return n[t]})}(a);e["default"]=s.a}},[["c8bb","common/runtime","common/vendor"]]]);