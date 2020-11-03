<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */
?>

<style>
    .app-search .tabs {
        margin-top: 20px;
    }

    .app-search .label {
        margin-right: 10px;
    }

    .app-search .item-box {
        margin-bottom: 10px;
        margin-right: 15px;
    }

    .app-search .clear-where {
        color: #419EFB;
        cursor: pointer;
    }
</style>

<template id="app-search">
    <div class="app-search">
        <div flex="wrap:wrap cross:center">
            <div style="height: 32px;">{{dateLabel}}：</div>
            <el-date-picker
                    class="item-box"
                    size="small"
                    @change="changeTime"
                    v-model="search.time"
                    type="datetimerange"
                    value-format="yyyy-MM-dd HH:mm:ss"
                    range-separator="至"
                    start-placeholder="开始日期"
                    end-placeholder="结束日期">
            </el-date-picker>
            <div class="item-box" flex="dir:left cross:center" v-if="isShowPlatform">
                <div class="label">所属平台</div>
                <el-select style="width: 120px;" size="small" v-model="search.platform" @change='toSearch'>
                    <el-option key="all" label="全部平台" value=""></el-option>
                    <el-option key="wxapp" label="微信" value="wxapp"></el-option>
                    <el-option key="aliapp" label="支付宝" value="aliapp"></el-option>
                    <el-option key="ttapp" label="抖音/头条" value="ttapp"></el-option>
                    <el-option key="bdapp" label="百度" value="bdapp"></el-option>
                </el-select>
            </div>
            <div v-if="isShowOrderPlugin" class="item-box" flex="dir:left cross:center">
                <div class="label">订单类型</div>
                <el-select size="small" style="width: 120px" v-model="search.plugin" @change="toSearch"
                           placeholder="订单类型">
                    <el-option v-for="item in plugins" :key="item.sign" :label="item.name"
                               :value="item.sign">
                    </el-option>
                </el-select>
            </div>
            <div class="item-box" v-if="isShowOrderType" flex="dir:left cross:center">
                <div class="label">配送方式</div>
                <el-select size="small" style="width: 120px" v-model="search.send_type" @change="toSearch"
                           placeholder="配送方式">
                    <el-option label="全部订单" :value="-1"></el-option>
                    <el-option label="快递配送" :value="0"></el-option>
                    <el-option label="到店核销" :value="1"></el-option>
                    <el-option label="同城配送" :value="2"></el-option>
                </el-select>
            </div>
            <div class="item-box" class="label">
                <el-input style="width: 350px" size="small" v-model="search.keyword" placeholder="请输入搜索内容" clearable
                          @clear="toSearch"
                          @keyup.enter.native="toSearch">
                    <el-select style="width: 120px" slot="prepend" v-model="search.keyword_1">
                        <el-option v-for="item in selectList" :key="item.value"
                                   :label="item.name"
                                   :value="item.value">
                        </el-option>
                    </el-select>
                </el-input>
            </div>
            <div class="item-box" flex="cross:center">
                <div v-if="isShowClear" @click="clearWhere" class="div-box clear-where">清空筛选条件</div>
            </div>
            <div v-if="isShowPrintInvoice && isSendTemplate" class="item-box" flex="dir:left cross:center">
                <el-button type="primary" size="small" @click="printInvoice">打印发货单</el-button>
            </div>
        </div>
        <div class="tabs">
            <el-tabs v-model="newActiveName" @tab-click="handleClick">
                <el-tab-pane v-for="(item, index) in tabs" :key="index" :label="item.name"
                             :name="item.value"></el-tab-pane>
            </el-tabs>
        </div>
    </div>
</template>

<script>
    Vue.component('app-search', {
        template: '#app-search',
        props: {
            selectList: {
                type: Array,
                default: function () {
                    return [
                        {value: '1', name: '订单号'},
                        {value: '9', name: '商户单号'},
                        {value: '2', name: '用户名'},
                        {value: '4', name: '用户ID'},
                        {value: '5', name: '商品名称'},
                        {value: '3', name: '收件人'},
                        {value: '6', name: '收件人电话'},
                        {value: '7', name: '门店名称'}
                    ]
                }
            },
            tabs: {
                type: Array,
                default: function () {
                    return [
                        {value: '-1', name: '全部'},
                        {value: '0', name: '未付款'},
                        {value: '1', name: '待发货'},
                        {value: '2', name: '待收货'},
                        {value: '3', name: '已完成'},
                        {value: '4', name: '待处理'},
                        {value: '5', name: '已取消'},
                        {value: '7', name: '回收站'},
                    ]
                }
            },
            activeName: {
                type: String,
                default: '-1',
            },
            plugins: {
                type: Array,
                default: function () {
                    return [
                        {
                            name: '全部订单',
                            sign: 'all',
                        }
                    ];
                }
            },
            isShowOrderType: {
                type: Boolean,
                default: true
            },
            isShowOrderPlugin: {
                type: Boolean,
                default: false
            },
            newSearch: {
                type: Object,
                default: function () {
                    return {
                        time: null,
                        keyword: '',
                        keyword_1: '1',
                        date_start: '',
                        date_end: '',
                        platform: '',
                        status: '',
                        plugin: 'all',
                        send_type: -1,
                    }
                }
            },
            dateLabel: {
                type: String,
                default: '下单时间'
            },
            isShowPlatform: {
                type: Boolean,
                default: true
            },
            isShowPrintInvoice: {
                type: Boolean,
                default: false
            },
            isSendTemplate: {
                type: Boolean,
                default: false
            },
        },
        data() {
            return {
                search: {},
                newActiveName: null,
                isShowClear: false,
            }
        },
        methods: {
            printInvoice() {
                this.$emit('print');
            },
            // 日期搜索
            changeTime() {
                if (this.search.time) {
                    this.search.date_start = this.search.time[0];
                    this.search.date_end = this.search.time[1];
                } else {
                    this.search.date_start = null;
                    this.search.date_end = null;
                }
                this.toSearch();
            },
            toSearch() {
                this.search.page = 1;
                this.$emit('search', this.search);
                this.checkSearch();
            },
            handleClick(res) {
                this.search.status = this.newActiveName;
                this.toSearch();
            },
            clearWhere() {
                this.search.keyword = '';
                this.search.date_start = null;
                this.search.date_end = null;
                this.search.time = null;
                this.search.platform = '';
                this.search.send_type = -1;
                this.search.plugin = 'all';
                this.toSearch();
            },
            checkSearch() {
                if (this.search.keyword || (this.search.date_start && this.search.date_end)
                    || this.search.plugin != 'all' || this.search.send_type != -1
                    || this.search.platform) {
                    this.isShowClear = true;
                } else {
                    this.isShowClear = false;
                }
            }
        },
        created() {
            this.search = this.newSearch;
            this.newActiveName = this.activeName;
            this.checkSearch();
        }
    })
</script>