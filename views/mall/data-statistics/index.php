<?php defined('YII_ENV') or exit('Access Denied');
$urlManager = Yii::$app->urlManager;
Yii::$app->loadViewComponent('statistics/app-search');
?>
<style>
    .el-tabs__nav-wrap::after {
        height: 1px;
    }

    .table-body {
        background-color: #fff;
        position: relative;
        padding-bottom: 50px;
        margin-bottom: 10px;
        border: 1px solid #EBEEF5;
    }

    .table-body .el-tabs {
        margin-left: 10px;
    }

    .table-body .el-tabs__nav-scroll {
        width: 120px;
        margin-left: 30px;
    }

    .table-body .el-tabs__item {
        height: 32px;
        line-height: 32px;
    }

    .table-body .clean {
        color: #92959B;
        margin-left: 20px;
        cursor: pointer;
        font-size: 15px;
    }

    .num-info {
        display: flex;
        width: 100%;
        height: 60px;
        font-size: 28px;
        color: #303133;
    }

    .num-info .num-info-item {
        text-align: center;
        width: 20%;
        border-left: 1px dashed #EFF1F7;
    }

    .num-info .num-info-item:first-of-type {
        border-left: 0;
    }

    .info-item-name {
        font-size: 16px;
        color: #92959B;
    }

    .tab-pay {
        position: absolute;
        bottom: 0;
        right: 50px;
    }

    .tab-pay .el-tabs__item {
        height: 56px;
        line-height: 56px;
    }

    .pay-info {
        padding: 40px 0;
        display: flex;
        justify-content: space-between;
    }

    .pay-info .pay-info-item {
        padding-top: 45px;
        width: 22%;
        margin: 0 1.5%;
        text-align: center;
        font-size: 26px;
        border: 1px solid #EBEEF5;
        border-radius: 10px;
        height: 150px;
        position: relative;
        cursor: pointer;
    }

    .pay-info .pay-info-item.active {
        border: 1px solid #3399FF;
    }

    .pay-info .pay-info-item img {
        display: none;
    }

    .pay-info .pay-info-item.active img {
        position: absolute;
        top: 0;
        left: 0;
        display: block;
    }

    .echarts-title {
        color: #92959B;
        display: flex;
        font-size: 16px;
        margin-left: 45px;
    }

    .echarts-title-item {
        margin-right: 45px;
        display: flex;
        align-items: center;
    }

    .echarts-title-item .echarts-title-icon {
        height: 16px;
        width: 16px;
        margin-right: 10px;
        background-color: #3399ff;
    }

    .table-area {
        margin-top: 20px;
        display: flex;
        justify-content: space-between;
    }

    .table-area .el-card {
        width: 49.5%;
        color: #303133;
    }

    .el-tabs__header {
        margin-bottom: 0 !important;
    }

    .el-card__header {
        position: relative;
    }

    .sort-active {
        color: #3399ff;
    }

    .select-item {
        border: 1px solid #3399ff;
        margin-top: -1px !important;
    }

    .el-popper .popper__arrow, .el-popper .popper__arrow::after {
        display: none;
    }

    .el-select-dropdown__item.hover, .el-select-dropdown__item:hover {
        background-color: #3399ff;
        color: #fff;
    }

    .table-area .el-card__header {
        padding: 14px 20px;
    }

    .text-omit {
        width: 380px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>数据概况</span>
        </div>
        <div class="table-body">
            <app-search
                    @to-search="toSearch"
                    @search="searchList"
                    :new-search="search"
                    :is-show-keyword="false"
                    :day-data="{'today':today, 'weekDay': weekDay, 'monthDay': monthDay}">
                <template slot="select">
                    <div>
                        <el-select size="small" popper-class="select-item" @change="tabMch" style="width: 160px" filterable
                                   v-model="search.mch" placeholder="请输入搜索内容">
                            <el-option label="全部" value="0"></el-option>
                            <el-option v-for="item in mch_list" :key="item.id" :label="item.name"
                                       :value="item.id"></el-option>
                        </el-select>
                    </div>
                </template>
            </app-search>
            <!-- 数据总览 -->
            <div class="num-info">
                <div class="num-info-item">
                    <div>{{all_data.user_count}}</div>
                    <div class="info-item-name">
                        <span>用户数</span>
                        <el-tooltip class="item" effect="dark" content="统计全平台用户数，不随店铺的更改而更改" placement="bottom">
                            <i class="el-icon-question"></i>
                        </el-tooltip>
                    </div>
                </div>
                <div class="num-info-item">
                    <div>{{all_data.goods_num}}</div>
                    <div class="info-item-name">商品数
                        <el-tooltip class="item" effect="dark" content="统计某时间段内添加的商品总数" placement="bottom">
                            <i class="el-icon-question"></i>
                        </el-tooltip>
                    </div>
                </div>
                <div class="num-info-item">
                    <div>{{all_data.order_num}}</div>
                    <div class="info-item-name">订单数</div>
                </div>
                <div class="num-info-item">
                    <div>{{all_data.wait_send_num}}</div>
                    <div class="info-item-name">待发货订单数</div>
                </div>
                <div class="num-info-item">
                    <div>{{all_data.pro_order}}</div>
                    <div class="info-item-name">维权订单数</div>
                </div>
            </div>
        </div>
        <!-- 销售情况 -->
        <el-card shadow="never">
            <div slot="header">
                <span>销售情况</span>
                <div class="tab-pay">
                    <el-tabs v-model="activeDay" @tab-click="tab_pay">
                        <el-tab-pane label="昨日" name="one"></el-tab-pane>
                        <el-tab-pane label="7日" name="seven"></el-tab-pane>
                    </el-tabs>
                </div>
            </div>
            <!-- 选择框 -->
            <div class="pay-info">
                <div class="pay-info-item" :class="{active:order}" @click="chooseInfo('order')">
                    <img src="statics/img/mall/statistic/active.png" alt="">
                    <div>{{table_data.order_num}}</div>
                    <div class="info-item-name">支付订单数</div>
                </div>
                <div class="pay-info-item" :class="{active:price}" @click="chooseInfo('price')">
                    <img src="statics/img/mall/statistic/active.png" alt="">
                    <div>{{table_data.total_pay_price}}</div>
                    <div class="info-item-name">支付金额</div>
                </div>
                <div class="pay-info-item" :class="{active:people}" @click="chooseInfo('people')">
                    <img src="statics/img/mall/statistic/active.png" alt="">
                    <div>{{table_data.user_num}}</div>
                    <div class="info-item-name">支付人数</div>
                </div>
                <div class="pay-info-item" :class="{active:num}" @click="chooseInfo('num')">
                    <img src="statics/img/mall/statistic/active.png" alt="">
                    <div>{{table_data.goods_num}}</div>
                    <div class="info-item-name">支付件数</div>
                </div>
            </div>
            <div class="echarts-title">
                <div class="echarts-title-item" v-if="order">
                    <div class="echarts-title-icon"></div>
                    <div>支付订单数</div>
                </div>
                <div class="echarts-title-item" v-if="price">
                    <div style="background-color: #FFA360;" class="echarts-title-icon"></div>
                    <div>支付金额</div>
                </div>
                <div class="echarts-title-item" v-if="people">
                    <div style="background-color: #4BC282;" class="echarts-title-icon"></div>
                    <div>支付人数</div>
                </div>
                <div class="echarts-title-item" v-if="num">
                    <div style="background-color: #FF8585;" class="echarts-title-icon"></div>
                    <div>支付件数</div>
                </div>
            </div>
            <div id="echarts" style="height:18rem;"></div>
        </el-card>
        <div class="table-area">
            <el-card shadow="never">
                <div slot="header">
                    <form target="_blank" :action="goods_url" method="post">
                        <span style="float: left;height: 32px;line-height: 32px">商品购买力TOP排行</span>
                        <div>
                            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                            <input name="flag" type="hidden" value="EXPORT">
                            <input name="date_start" type="hidden" :value="search.date_start">
                            <input name="date_end" type="hidden" :value="search.date_end">
                            <input name="mch_id" type="hidden" :value="search.mch_id">
                            <input name="goods_order" type="hidden" :value="sort_order">
                        </div>
                        <div flex="dir:right" style="">
                            <button type="submit" class="el-button el-button--primary el-button--small">导出TOP100
                            </button>
                        </div>
                    </form>
                </div>
                <el-table v-loading="goods_loading" :row-style="{height:'63px'}" @sort-change="changeGoods"
                          :header-cell-style="{background:'#F3F5F6','color':'#303133'}" :data="goods_top_list">
                    <el-table-column align="center" label="排名">
                        <template slot-scope="scope">
                            <img style="margin-top: 3px" v-if="scope.$index == 0"
                                 src="statics/img/mall/statistic/first.png" alt="">
                            <img style="margin-top: 3px" v-else-if="scope.$index == 1"
                                 src="statics/img/mall/statistic/sec.png" alt="">
                            <img style="margin-top: 3px" v-else-if="scope.$index == 2"
                                 src="statics/img/mall/statistic/third.png" alt="">
                            <span v-else-if="scope.$index < 9">0{{scope.$index+1}}</span>
                            <span v-else>{{scope.$index+1}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="name" width="400" label="商品">
                        <template slot-scope="scope">
                            <div class="text-omit">{{scope.row.name}}</div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="total_price" label="销售额" width="150" sortable='custom'
                                     :label-class-name="goodsProp == 'total_price' ? 'sort-active': ''">
                    </el-table-column>
                    <el-table-column prop="num" label="销量" width="150" sortable='custom'
                                     :label-class-name="goodsProp == 'num' ? 'sort-active': ''">
                    </el-table-column>
                </el-table>
            </el-card>
            <el-card shadow="never">
                <div slot="header">
                    <form target="_blank" :action="user_url" method="post">
                        <span style="float: left;height: 32px;line-height: 32px">用户购买力TOP排行</span>
                        <div>
                            <input name="_csrf" type="hidden" id="_csrf" value="<?= Yii::$app->request->csrfToken ?>">
                            <input name="flag" type="hidden" value="EXPORT">
                            <input name="date_start" type="hidden" :value="search.date_start">
                            <input name="date_end" type="hidden" :value="search.date_end">
                            <input name="mch_id" type="hidden" :value="search.mch_id">
                            <input name="user_order" type="hidden" :value="sort_order">
                        </div>
                        <div flex="dir:right" style="">
                            <button type="submit" class="el-button el-button--primary el-button--small">导出TOP100
                            </button>
                        </div>
                    </form>
                </div>
                <el-table v-loading="user_loading" :row-style="{height:'63px'}" @sort-change="changeUser"
                          :header-cell-style="{background:'#F3F5F6','color':'#303133'}" :data="user_top_list">
                    <el-table-column align="center" label="排名">
                        <template slot-scope="scope">
                            <img style="margin-top: 3px" v-if="scope.$index == 0"
                                 src="statics/img/mall/statistic/first.png" alt="">
                            <img style="margin-top: 3px" v-else-if="scope.$index == 1"
                                 src="statics/img/mall/statistic/sec.png" alt="">
                            <img style="margin-top: 3px" v-else-if="scope.$index == 2"
                                 src="statics/img/mall/statistic/third.png" alt="">
                            <span v-else-if="scope.$index < 9">0{{scope.$index+1}}</span>
                            <span v-else>{{scope.$index+1}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="name" label="用户" width="400">
                        <template slot-scope="scope">
                            <app-image style="margin-right: 10px;float: left;" :src="scope.row.avatar" width="32px"
                                       height="32px">
                            </app-image>
                            <span class="text-omit"
                                  style="height: 32px;line-height: 32px;display: inline-block;width: 280px">{{scope.row.nickname}}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="total_price" label="支付金额" width="150"
                                     :label-class-name="userProp == 'total_price' ? 'sort-active': ''"
                                     sortable='custom'>
                    </el-table-column>
                    <el-table-column prop="num" label="支付件数" width="150"
                                     :label-class-name="userProp == 'num' ? 'sort-active': ''" sortable='custom'>
                    </el-table-column>
                </el-table>
            </el-card>
        </div>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                // 今天
                today: '',
                // 七天前
                weekDay: '',
                // 30天前
                monthDay: '',
                // 加载动画
                loading: false,
                goods_loading: false,
                user_loading: false,
                // 销售情况选中情况
                order: true,
                price: true,
                people: true,
                num: true,
                // 日期选中状态
                activeName: '',
                // 搜索内容
                search: {
                    mch: null,
                    time: null,
                    date_start: null,
                    date_end: null,
                    platform: '',
                },
                // 总体数据
                all_data: {
                    goods_num: "0",
                    order_num: "0",
                    pro_order: "0",
                    user_count: "0",
                    wait_send_num: "0"
                },
                // 店铺列表
                mch_list: [],
                // 销售总计
                table_data: {
                    order_num: 0,
                    total_pay_price: 0,
                    user_num: 0,
                    goods_num: 0,
                },
                // 销售情况
                table_list: {
                    order_num: '0',
                    total_pay_price: '0',
                    user_num: '0',
                    goods_num: '0',
                },
                // 商品排行
                goods_top_list: [],
                // 用户排行
                user_top_list: [],
                // 时段
                pay_day: [],
                // 图表X轴
                echarts_item: ['支付订单数', '支付金额', '支付人数', '支付件数'],
                // 图表参数
                series: [
                    {
                        name: '支付订单数',
                        type: 'line',
                        data: []
                    },
                    {
                        name: '支付金额',
                        type: 'line',
                        data: []
                    },
                    {
                        name: '支付人数',
                        type: 'line',
                        data: []
                    },
                    {
                        name: '支付件数',
                        type: 'line',
                        data: []
                    },
                ],
                activeDay: 'one',
                sort_order: null,
                userProp: '',
                goodsProp: '',
                user_url: '<?= $urlManager->createUrl('mall/data-statistics/users_top')?>',
                goods_url: '<?= $urlManager->createUrl('mall/data-statistics/goods_top')?>',
                is_mch_role: true
            };
        },
        methods: {
            // 修改用户购买力排行榜排序
            changeUser(column) {
                this.user_loading = true;
                this.goodsProp = null;
                if (column && column.order == "descending") {
                    this.sort_order = column.prop + ' DESC'
                } else if (column && column.order == "ascending") {
                    this.sort_order = column.prop + ' ASC'
                }
                this.userProp = column ? column.prop : '';
                let params = {
                    r: 'mall/data-statistics/users_top',
                }
                let para = {
                    mch_id: this.search.mch,
                    date_start: this.search.date_start,
                    date_end: this.search.date_end,
                    user_order: this.sort_order,
                    platform: this.search.platform,
                }
                request({
                    params: params,
                    data: para,
                    method: 'post',
                }).then(res => {
                    this.user_loading = false;
                    if (res.data.code == 0) {
                        this.user_top_list = res.data.data.user_top_list;
                    }
                }).catch(res => {
                    this.user_loading = false;
                })
            },
            // 修改商品排序
            changeGoods(column) {
                this.userProp = null;
                this.goods_loading = true;
                if (column.order == "descending") {
                    this.sort_order = column.prop + ' DESC'
                } else if (column.order == "ascending") {
                    this.sort_order = column.prop + ' ASC'
                }
                this.goodsProp = column.prop;
                let params = {
                    r: 'mall/data-statistics/goods_top',
                }
                let para = {
                    mch_id: this.search.mch,
                    date_start: this.search.date_start,
                    date_end: this.search.date_end,
                    goods_order: this.sort_order,
                }
                request({
                    params: params,
                    data: para,
                    method: 'post',
                }).then(res => {
                    this.goods_loading = false;
                    if (res.data.code == 0) {
                        this.goods_top_list = res.data.data.goods_top_list;
                    }
                }).catch(res => {
                    this.goods_loading = false;
                })
            },
            // 获取数据
            getList() {
                this.loading = true;
                this.series[0].data = [];
                this.series[1].data = [];
                this.series[2].data = [];
                this.series[3].data = [];
                this.order_num = 0;
                this.total_pay_price = 0;
                this.user_num = 0;
                this.goods_num = 0;
                this.pay_day = [];
                const _csrf = '<?=Yii::$app->request->csrfToken?>';
                request({
                    params: {
                        r: 'mall/data-statistics/index',
                    },
                    data: {
                        _csrf,
                        date_start: this.search.date_start,
                        date_end: this.search.date_end,
                        mch_id: this.search.mch,
                        platform: this.search.platform,
                    },
                    method: 'post',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.all_data = e.data.data.all_data;
                        this.mch_list = e.data.data.mch_list;
                        this.goods_top_list = e.data.data.goods_top_list;
                        this.user_top_list = e.data.data.user_top_list;
                        this.table_list = e.data.data.table_list;
                        this.is_mch_role = e.data.data.is_mch_role;
                        // this.table_data = e.data.data.table_data;
                        // for(let i = 0;i< this.table_list.length;i++) {
                        //     this.pay_day.push(this.table_list[i].created_at)
                        //     this.series[0].data.push(this.table_list[i].order_num);
                        //     this.series[1].data.push(this.table_list[i].total_pay_price);
                        //     this.series[2].data.push(this.table_list[i].user_num);
                        //     this.series[3].data.push(this.table_list[i].goods_num);
                        // }
                        // this.form();
                        // var myChart = echarts.init(document.getElementById('echarts'));
                        // myChart.hideLoading();
                        this.tab_pay();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            // 选择图表显示内容
            chooseInfo(row) {
                let that = this;
                var myChart = echarts.init(document.getElementById('echarts'));
                switch (row) {
                    case 'order':
                        that.order = !that.order;
                        myChart.setOption({
                            legend: {
                                show: false,
                                data: that.echarts_item,
                                selected: {
                                    '支付订单数': that.order,
                                    '支付金额': that.price,
                                    '支付人数': that.people,
                                    '支付件数': that.num
                                }
                            }
                        })
                        break;
                    case 'price':
                        that.price = !that.price;
                        myChart.setOption({
                            legend: {
                                show: false,
                                data: that.echarts_item,
                                selected: {
                                    '支付订单数': that.order,
                                    '支付金额': that.price,
                                    '支付人数': that.people,
                                    '支付件数': that.num
                                }
                            }
                        })
                        break;
                    case 'people':
                        that.people = !that.people;
                        myChart.setOption({
                            legend: {
                                show: false,
                                data: that.echarts_item,
                                selected: {
                                    '支付订单数': that.order,
                                    '支付金额': that.price,
                                    '支付人数': that.people,
                                    '支付件数': that.num
                                }
                            }
                        })
                        break;
                    case 'num':
                        that.num = !that.num;
                        myChart.setOption({
                            legend: {
                                show: false,
                                data: that.echarts_item,
                                selected: {
                                    '支付订单数': that.order,
                                    '支付金额': that.price,
                                    '支付人数': that.people,
                                    '支付件数': that.num
                                }
                            }
                        })
                        break;
                }
            },
            // 生成图表
            form() {
                let that = this;
                var myChart = echarts.init(document.getElementById('echarts'));
                myChart.setOption({
                    tooltip: {
                        trigger: 'axis',
                        backgroundColor: '#fff',
                        textStyle: {color: '#303133'},
                        padding: 20,
                        extraCssText: 'box-shadow: 0 0 4px rgba(0, 0, 0.1);'
                    },
                    legend: {
                        show: false,
                        data: that.echarts_item
                    },
                    color: ['#3399FF', '#FFA360', '#4BC282', '#FF8585'],
                    grid: {
                        left: '0',
                        right: '4%',
                        bottom: '3%',
                        containLabel: true
                    },
                    xAxis: {
                        type: 'category',
                        boundaryGap: false,
                        data: that.pay_day
                    },
                    yAxis: {
                        splitLine: {
                            show: true,
                            lineStyle: {
                                type: 'dashed'
                            }
                        },
                        axisLine: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        axisLabel: {
                            show: false
                        },
                        type: 'value'
                    },
                    series: that.series
                });
                myChart.showLoading({text: '正在加载数据'});
            },
            // 切换图表区间
            tab_pay() {
                this.order = true;
                this.price = true;
                this.people = true;
                this.num = true;
                this.series[0].data = [];
                this.series[1].data = [];
                this.series[2].data = [];
                this.series[3].data = [];
                this.order_num = 0;
                this.total_pay_price = 0;
                this.user_num = 0;
                this.goods_num = 0;
                this.pay_day = [];
                this.form();
                let date_start;
                let date_end;
                if (this.activeDay == 'seven') {
                    date_start = this.weekDay;
                    date_end = this.today;
                }
                request({
                    params: {
                        r: 'mall/data-statistics/table',
                    },
                    data: {
                        mch_id: this.search.mch,
                        date_start: date_start,
                        date_end: date_end,
                        platform: this.search.platform,
                    },
                    method: 'post',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.table_list = e.data.data.list;
                        this.table_data = e.data.data.table_data;
                        for (let i = 0; i < this.table_list.length; i++) {
                            this.pay_day.push(this.table_list[i].created_at)
                            this.series[0].data.push(this.table_list[i].order_num);
                            this.series[1].data.push(this.table_list[i].total_pay_price);
                            this.series[2].data.push(this.table_list[i].user_num);
                            this.series[3].data.push(this.table_list[i].goods_num);
                        }
                        this.form();
                        var myChart = echarts.init(document.getElementById('echarts'));
                        myChart.hideLoading();
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            searchList(searchData) {
                this.search = searchData;
                this.page = 1;
                this.getList();
            },
            toSearch(searchData) {
                this.search = searchData;
                this.getList();
                this.tab_pay();
                this.changeUser();
            },
            tabMch() {
                this.getList();
            },
        },
        created() {
            this.getList();
            let date = new Date();
            let timestamp = date.getTime();
            let seperator1 = "-";
            let year = date.getFullYear();
            let nowMonth = date.getMonth() + 1;
            let strDate = date.getDate();
            if (nowMonth >= 1 && nowMonth <= 9) {
                nowMonth = "0" + nowMonth;
            }
            if (strDate >= 0 && strDate <= 9) {
                strDate = "0" + strDate;
            }
            this.today = year + seperator1 + nowMonth + seperator1 + strDate;
            let week = new Date(timestamp - 7 * 24 * 3600 * 1000)
            let weekYear = week.getFullYear();
            let weekMonth = week.getMonth() + 1;
            let weekStrDate = week.getDate();
            if (weekMonth >= 1 && weekMonth <= 9) {
                weekMonth = "0" + weekMonth;
            }
            if (weekStrDate >= 0 && weekStrDate <= 9) {
                weekStrDate = "0" + weekStrDate;
            }
            this.weekDay = weekYear + seperator1 + weekMonth + seperator1 + weekStrDate;
            let month = new Date(timestamp - 30 * 24 * 3600 * 1000);
            let monthYear = month.getFullYear();
            let monthMonth = month.getMonth() + 1;
            let monthStrDate = month.getDate();
            if (monthMonth >= 1 && monthMonth <= 9) {
                monthMonth = "0" + monthMonth;
            }
            if (monthStrDate >= 0 && monthStrDate <= 9) {
                monthStrDate = "0" + monthStrDate;
            }
            this.monthDay = monthYear + seperator1 + monthMonth + seperator1 + monthStrDate;
        },
    })
</script>