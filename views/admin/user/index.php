<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */
Yii::$app->loadViewComponent('admin/app-batch-permission');
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .action-box .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        display: inline-block;
        width: 250px;
        margin-right: 10px;
        margin-bottom: 20px;
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

    .el-tooltip__popper {
        max-width: 200px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 0;">
        <div class="table-body">
            <div>
                <div class="input-item">
                    <el-input @keyup.enter.native="search" size="small" placeholder="请输入用户名或手机号" clearable v-model="keyword" clearable @clear='getList'>
                        <el-button slot="append" @click="search" icon="el-icon-search"></el-button>
                    </el-input>
                </div>
                <app-batch-permission :choose-list="multipleSelection"></app-batch-permission>
            </div>
            <el-table
                    v-loading="listLoading"
                    :data="list"
                    border
                    @selection-change="handleSelectionChange"
                    style="width: 100%">
                <el-table-column
                        type="selection"
                        width="55">
                </el-table-column>
                <el-table-column
                        prop="username"
                        label="账户">
                    <template slot-scope="scope">
                        <div flex="dir:top">
                            <span>{{scope.row.username}}</span>
                            <span style="color: #999999;">{{scope.row.mobile}}</span>
                            <el-tooltip v-if="scope.row.adminInfo.remark.length > 16" class="item" effect="dark" :content="scope.row.adminInfo.remark" placement="bottom">
                                <span style="color: #999999;">{{scope.row.remark}}</span>
                            </el-tooltip>
                            <span v-else style="color: #999999;">{{scope.row.adminInfo.remark}}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        prop=""
                        label="可创建小程序数量">
                    <template slot-scope="scope">
                        <span v-if="scope.row.adminInfo.app_max_count == -1">无限制</span>
                        <span v-else>{{scope.row.adminInfo.app_max_count}}</span>
                    </template>
                </el-table-column>
                <el-table-column
                        label="已创建小程序数量">
                    <template slot-scope="scope">
                        <a href="#" style="text-decoration:none; color: #409EFF;" @click="toMallList(scope.row)">{{scope.row.create_app_count}}</a>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="adminInfo.expired_at"
                        :formatter="formatExpired"
                        width="180"
                        label="有效期">
                </el-table-column>
                <el-table-column
                        fixed="right"
                        label="操作"
                        width="220">
                    <template slot-scope="scope">
                        <div class="action-box">
                            <el-button @click="edit(scope.row.id)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                    <img src="statics/img/mall/edit.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button @click="editPassword(scope.row.id)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="修改密码" placement="top">
                                    <img src="statics/img/mall/change.png" alt="">
                                </el-tooltip>
                            </el-button>
                            <el-button @click="destroy(scope.row.id)" type="text" circle size="mini">
                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                    <img src="statics/img/mall/del.png" alt="">
                                </el-tooltip>
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>

            <div style="text-align: right;margin: 20px 0;">
                <el-pagination
                        @current-change="pagination"
                        background
                        layout="prev, pager, next"
                        :current-page="page"
                        :page-count="pageCount">
                </el-pagination>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                list: [],
                listLoading: false,
                page: 1,
                pageCount: 0,
                keyword: '',

                multipleSelection: [],
            };
        },
        methods: {
            search() {
                this.page = 1;
                this.getList();
            },

            formatExpired(row, column) {
                if (row.adminInfo.expired_at !== '0000-00-00 00:00:00') {
                    return row.adminInfo.expired_at;
                }
                return '永久'
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
                        r: 'admin/user/index',
                        page: self.page,
                        keyword: self.keyword,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    if (e.data.code === 0) {
                        self.list = e.data.data.list;
                        self.pageCount = e.data.data.pagination.page_count;
                        self.list.forEach(function(item){
                            if(item.adminInfo.remark.length > 16) {
                                item.remark = item.adminInfo.remark.slice(0,16) + '...'
                            }
                            
                        })
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            edit(id) {
                navigateTo({
                    r: 'admin/user/edit',
                    id: id,
                });
            },
            destroy(id) {
                let self = this;
                self.$confirm('删除该用户, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    request({
                        params: {
                            r: 'admin/user/destroy',
                        },
                        method: 'post',
                        data: {
                            id: id,
                        }
                    }).then(e => {
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
            editPassword(id) {
                let self = this;
                self.$prompt('请输入新密码', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    inputPattern: /\S+/,
                    inputErrorMessage: '请输入新密码',
                    inputType: 'password',
                }).then(({value}) => {
                    request({
                        params: {
                            r: 'admin/user/edit-password',
                        },
                        method: 'post',
                        data: {
                            id: id,
                            password: value
                        }
                    }).then(e => {
                        if (e.data.code === 0) {
                            self.$message.success(e.data.msg);
                        } else {
                            self.$message.error(e.data.msg);
                        }
                    }).catch(e => {
                        console.log(e);
                    });
                }).catch(() => {

                });
            },
            toMallList(row) {
                navigateTo({
                    r: 'admin/mall/index',
                    _layout: 'admin',
                    user_id: row.id,
                });
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            }
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
