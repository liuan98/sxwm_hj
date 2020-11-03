<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */
Yii::$app->loadViewComponent('goods/app-batch');
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .input-item {
        width: 250px;
        margin: 0 0 20px;
    }

    .input-item .el-input__inner {
        border-right: 0;
    }

    .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .input-item .el-input__inner:focus {
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

    .table-body .el-table .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .el-form-item {
        margin-bottom: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="listLoading" class="box-card" shadow="never" style="border:0"
             body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>秒杀商品</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small" @click="edit">添加商品</el-button>
        </div>
        <div class="table-body">
            <div style="background-color: #fce9e6;display:flex;width: 100%;border-color: #edd7d4;color: #e55640;border-radius: 2px;padding: 15px;margin-bottom: 20px;">
                <div style="width: 50px">注意！</div>
                <div>
                    <div>1、当前时间之前的整点不会添加秒杀场次！例如当前时间为9点~10点之间,那么当天8点、7点、6点...等场次不会添加</div>
                    <div>2、添加的秒杀场次会有一定延迟！延迟时间根据添加的场次数量决定,场次数量越多,延迟越长(1~2分钟),建议稍后查看</div>
                </div>
            </div>
            <el-form size="small" :inline="true" @submit.native.prevent :model="search">
                <el-form-item style="margin-bottom: 0">
                    <div class="input-item">
                        <el-input @keyup.enter.native="commonSearch" size="small" placeholder="请输入商品名称搜索"
                                  v-model="search.keyword" clearable @clear='commonSearch'>
                            <el-button slot="append" icon="el-icon-search" @click="commonSearch"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </el-form>
            <app-batch :choose-list="choose_list"
                       @to-search="getList"
                       batch-destroy-url="plugin/miaosha/mall/goods/batch-destroy"
                       :is-show-svip="false"
                       :is-show-batch-button="false"
                       :is-show-up-down="false"
                       :is-show-integral="false">
            </app-batch>
            <el-table :data="list" @selection-change="handleSelectionChange" border
                      style="width: 100%;margin-bottom: 15px">
                <el-table-column align="center" type="selection" width="60"></el-table-column>
                <el-table-column label="商品" width="500">
                    <template slot-scope="scope">
                        <div flex="box:first">
                            <div style="padding-right: 10px">
                                <app-image mode="aspectFill" :src="scope.row.goodsWarehouse.cover_pic"></app-image>
                            </div>
                            <div>
                                <app-ellipsis :line="1">{{scope.row.goodsWarehouse.name}}</app-ellipsis>
                                <el-tag size="mini" v-for="item in scope.row.cats"
                                        :key="item.id">{{item.name}}
                                </el-tag>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="miaosha_count" label="总场次"></el-table-column>
                <el-table-column
                        label="操作" width="180">
                    <template slot-scope="scope">
                        <el-button @click="miaosha(scope.row)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="查看场次" placement="top">
                                <img src="statics/img/plugins/session.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button @click="destroy(scope.row, scope.$index)" type="text" circle size="mini">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center" style="margin-top: 20px;">
                <div style="visibility: hidden;">
                    <el-button plain type="primary" size="small">批量操作1</el-button>
                    <el-button plain type="primary" size="small">批量操作2</el-button>
                </div>
                <div>
                    <el-pagination
                            v-if="pageCount > 0"
                            @current-change="pagination"
                            background
                            layout="prev, pager, next"
                            :page-count="pageCount">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                search: {
                    keyword: '',
                    status: '',
                },
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                choose_list: [],
            };
        },
        created() {
            this.getList();
        },
        methods: {
            handleSelectionChange(val) {
                let self = this;
                self.choose_list = [];
                val.forEach(function (item) {
                    self.choose_list.push(item.goods_warehouse_id);
                })
            },

            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getList();
            },
            getList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'plugin/miaosha/mall/goods/index',
                        page: self.page,
                        search: self.search,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.list = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            edit(id) {
                if (id) {
                    navigateTo({
                        r: 'plugin/miaosha/mall/goods/edit',
                        id: id,
                    });
                } else {
                    navigateTo({
                        r: 'plugin/miaosha/mall/goods/edit',
                    });
                }
            },
            miaosha(row) {
                navigateTo({
                    r: 'plugin/miaosha/mall/goods/miaosha-list',
                    id: row.goods_warehouse_id,
                });
            },
            destroy(row, index) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/miaosha/mall/goods/destroy',
                        },
                        method: 'post',
                        data: {
                            goods_warehouse_id: row.goods_warehouse_id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.list.splice(index, 1);
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },

            batchDestroy() {
                let self = this;
                self.$confirm('批量删除数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'plugin/miaosha/mall/goods/batch-destroy',
                        },
                        method: 'post',
                        data: {
                            choose_list: this.choose_list,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                            self.getList();
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {
                    self.$message.info('已取消删除')
                });
            },
            // 搜索
            commonSearch() {
                this.getList();
            },
        }
    });
</script>
