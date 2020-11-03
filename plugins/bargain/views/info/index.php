<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

?>

<style>
    .goods-info {
        width: 100%;
        margin-top: 10px;
        position: relative;
    }

    .goods-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 250px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus{
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }
    
    .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .title {
        background-color: #F3F5F6;
        height: 40px;
        line-height: 40px;
        display: flex;
    }

    .title div {
        text-align: center;
    }

    .title+.el-card .el-card__body .el-card {
        border: 0;
    }

    .bargain-info {
        border-right: 1px #e2e2e2 solid;
        padding: 20px;
        display: flex;
    }

    .bargain-item-head {
        background-color: #F3F5F6;
        padding: 0;
    }

    .platform-img {
        margin-top: -2px;
        float: left;
        display: block;
        margin-right: 5px;
    }

    .price-info {
        position: absolute;
        top: 25px;
        left: 0;
        width: 50%;
        flex-wrap: wrap;
        display: flex;
    }

    .item-center {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100%;
    }

    .price-info img {
        margin-right: 5px;
    }

    .price-info div {
        display: flex;
        margin-right: 4%;
        margin-bottom: 5px;
        align-items: center;
    }

    .price-info div:last-of-type {
        margin-right: 0;
    }

    .detail-item {
        height: 60px;
        padding: 0 15px;
        margin-bottom: 20px;
        font-size: 16px;
        line-height: 60px;
    }

    .detail-item span {
        display: inline-block;
        width: 50%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .load-more {
        height: 60px;
        width: 100%;
        text-align: center;
        line-height: 60px;
        font-size: 16px;
        color: #3399ff;
        cursor: pointer;
    }

    .el-dialog__body {
        padding-bottom: 10px;
    }

    .el-dialog {
        min-width: 600px;
    }
</style>
<link rel="stylesheet" href="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/css/style.css">

<div id="app" v-cloak>
    <el-card v-loading="listLoading" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>砍价信息</span>
        </div>
        <div class="table-body">
            <el-form size="small" :inline="true" :model="search">
                <el-form-item style="display: none">
                    <el-input></el-input>
                </el-form-item>
                <el-form-item>
                    <div class="input-item">
                        <el-input size="small" placeholder="请输入商品名称" @keyup.enter.native="commonSearch"
                                  v-model="search.keyword" clearable @clear='commonSearch'>
                            <el-button slot="append" icon="el-icon-search" @click="commonSearch"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </el-form>
            <!-- 状态选择 -->
            <el-tabs v-model="search.status" @tab-click="handleClick">
                <el-tab-pane label="全部" name="-1"></el-tab-pane>
                <el-tab-pane label="砍价中" name="0"></el-tab-pane>
                <el-tab-pane label="砍价成功" name="1"></el-tab-pane>
                <el-tab-pane label="砍价失败" name="2"></el-tab-pane>
            </el-tabs>
            <div class="title">
                <div style="width: 50%;">砍价信息</div>
                <div style="width: 15%">参与人数</div>
                <div style="width: 10%">状态</div>
                <div style="width: 25%;min-width: 330px">参与详情</div>
            </div>
            <template v-for="(item, index) in list">
                <el-card shadow="never" style="margin-top: 1rem;background-color: #F3F5F6;" body-style="padding:0;background-color: #fff;">
                    <div slot="header" class="bargain-item-head">
                        <span style="margin-right: 1rem;float: left;">{{item.created_at}}</span>
                        <span style="margin-right: 1rem">
                            <img class="platform-img" v-if="item.platform == 'wxapp'" src="statics/img/mall/wx.png" alt="">
                            <img class="platform-img" v-if="item.platform == 'app'" src="statics/img/mall/ali.png" alt="">
                            <span>{{item.nickname}}({{item.user_id}})</span>
                        </span>
                    </div>
                    <div style="display: flex;">
                        <div style="width: 50%;">
                            <div class="bargain-info">
                                <app-image :src="item.goods.cover_pic" width="90px" height="90px" style="margin-right: 15px;"></app-image>
                                <div class="goods-info">
                                    <div class="goods-name">{{item.goods.name}}</div>
                                    <div class="price-info">
                                        <div>
                                            <el-tooltip class="item" effect="dark" content="售价" placement="top">
                                                <img src="statics/img/plugins/price.png" alt="">
                                            </el-tooltip>
                                            <span>￥{{item.price}}</span>
                                        </div>
                                        <div>
                                            <el-tooltip class="item" effect="dark" content="最低价" placement="top">
                                                <img src="statics/img/plugins/low.png" alt="">
                                            </el-tooltip>
                                            <span>￥{{item.min_price}}</span>
                                        </div>
                                        <div>
                                            <el-tooltip class="item" effect="dark" content="当前价" placement="top">
                                                <img src="statics/img/plugins/now.png" alt="">
                                            </el-tooltip>
                                            <span>￥{{item.now_price}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div style="width: 15%;">
                            <div class="item-center">{{item.total_people}}</div>
                        </div>
                        <div style="width: 10%;border-left: 1px #e2e2e2 solid;">
                            <div class="item-center">
                                <el-tooltip class="item" effect="dark" content="进行中" placement="top">    
                                    <img src="statics/img/mall/ing.png" v-if="item.status == 0" alt="">
                                </el-tooltip>
                                <el-tooltip class="item" effect="dark" content="成功" placement="top">    
                                    <img src="statics/img/mall/already.png" v-if="item.status == 1" alt="">
                                </el-tooltip>
                                <el-tooltip class="item" effect="dark" content="失败" placement="top">    
                                    <img src="statics/img/plugins/gameover.png" v-if="item.status == 2" alt="">
                                </el-tooltip>
                            </div>
                        </div>
                        <div style="width: 25%;border-left: 1px #e2e2e2 solid;padding: 0 2%;min-width: 330px">
                            <div class="item-center" style="justify-content: flex-start">
                                <app-image style="margin-right: 20px" :src="value.avatar" width="60px" height="60px" v-for="value in item.join_list"></app-image>
                                <el-tooltip class="item" effect="dark" content="参与详情" v-if="item.join_list.length != 0" placement="top">
                                    <img style="cursor: pointer;height: 32px;width: 32px;" @click="openList(item)" src="statics/img/mall/order/detail.png"></img>
                                </el-tooltip>
                            </div>
                        </div>
                    </div>
                </el-card>
            </template>
            <div flex="box:last cross:center" style="margin-top: 20px;">
                <div style="visibility: hidden">
                </div>
                <div>
                    <el-pagination
                            v-if="pagination"
                            style="display: inline-block;float: right;"
                            background
                            @current-change="pageChange"
                            layout="prev, pager, next"
                            :page-size="pagination.pageSize"
                            :current-page.sync="pagination.current_page"
                            :total="pagination.totalCount">
                    </el-pagination>
                </div>
            </div>
            <el-dialog title="参与详情" :visible.sync="dialogVisible" width="30%">
                <div v-for="value in detail_list" class="detail-item">
                    <app-image style="margin-right: 20px;float: left;" :src="value.avatar" width="60px" height="60px"></app-image>
                    <span>{{value.nickname}}</span>
                    <div style="float: right">砍了￥{{value.price}}</div>
                </div>
                <div @click="more" v-if="detail.length > 5 && clickMore" class="load-more">加载更多...</div>
            </el-dialog>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                clickMore: true,
                dialogVisible: false,
                search: {
                    r: 'plugin/bargain/mall/info/index',
                    keyword: '',
                    status: '-1',
                    page: 1
                },
                list: [],
                detail: [],
                activeName: '0',
                listLoading: false,
                pagination: {},
                detail_list: []
            };
        },
        created() {
            this.getList();
        },
        methods: {
            openList(row) {
                this.detail = row.user_list;
                this.detail_list = row.user_list.slice(0,5);
                this.dialogVisible = !this.dialogVisible;
            },

            more() {
                this.clickMore = !this.clickMore;
                this.detail_list = this.detail;
            },

            pageChange(currentPage) {
                let self = this;
                self.search.page = currentPage;
                self.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: self.search,
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        self.$message({
                            message: '请求成功',
                            type: 'success'
                        });
                        self.listLoading = false;
                        self.list = e.data.data.list;
                        for(let i = 0;i < self.list.length;i++) {
                            self.list[i].join_list = self.list[i].user_list.slice(0,3)
                        }
                        self.pagination = e.data.data.pagination;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            handleClick(e) {
                this.list = [];
                this.getList();
            },
            // 搜索
            commonSearch() {
                this.getList();
            }
        }
    });
</script>
