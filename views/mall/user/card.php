<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
?>
<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }

    .table-info .el-button {
        padding: 0 !important;
        border: 0;
        margin: 0 5px;
    }

    .input-item {
        display: inline-block;
        width: 300px;
        margin-right: 10px;
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
        padding: 15px;
    }

    .select {
        float: left;
        width: 100px;
        margin-right: 10px;
    }

    .el-input-group__prepend {
        background-color: #fff;
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
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>用户卡券</span>
            </div>
        </div>
        <div class="table-body">
            <div>
                <el-select size="small" v-model="status" @change="handleCommand" class="select">
                    <el-option key="0" label="全部" :value="0"></el-option>
                    <el-option key="1" label="未使用" :value="1"></el-option>
                    <el-option key="2" label="已使用" :value="2"></el-option>
                </el-select>
                <div class="input-item">
                    <el-input size="small" @keyup.enter.native="search" clearable @clear="search" placeholder="请输入搜索内容"
                              v-model="key_name">
                        <el-select style="width: 80px" slot="prepend" v-model="key_code">
                            <el-option label="门店" value="0"></el-option>
                            <el-option label="卡券" value="1"></el-option>
                            <el-option label="昵称" value="2"></el-option>
                        </el-select>
                        <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                    </el-input>
                </div>
                <el-button type="primary" v-if="ids.length > 0" @click="batchDestroy" size="small">批量删除</el-button>
            </div>
            <div style="display: flex;margin: 10px 0;">
                <div style="margin:5px 10px 5px 0;">
                    <el-date-picker
                            @change="search"
                            size="small"
                            v-model="send_date"
                            type="datetimerange"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至"
                            start-placeholder="发放开始日期"
                            end-placeholder="发放结束日期"
                    ></el-date-picker>
                    <br>
                </div>
                <div style="margin:5px 0;">
                    <el-date-picker
                            @change="search"
                            size="small"
                            v-model="clerk_date"
                            type="datetimerange"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            range-separator="至"
                            start-placeholder="核销开始日期"
                            end-placeholder="核销结束日期"
                    ></el-date-picker>
                </div>
            </div>
            <el-table border :data="form" style="width: 100%" @selection-change="selsChange" v-loading="listLoading">
                <el-table-column align='center' type="selection" width="55"></el-table-column>
                <el-table-column prop="id" label="ID" width="100"></el-table-column>
                <el-table-column prop="nickname" label="昵称" width="150"></el-table-column>
                <el-table-column prop="name" label="卡券名称" width="150"></el-table-column>
                <el-table-column label="卡券信息" width="340">
                    <template slot-scope="scope">
                        <div class="info p-2" style="border: 1px solid #ddd;">
                            <div flex="dir:left box:first">
                                <app-image style="border-radius:50%;margin:auto 5px" mode="aspectFill"
                                           :src="scope.row.pic_url"></app-image>
                                <div flex="dir:left cross:center">{{scope.row.content}}</div>
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="created_at" label="发放时间" width="180"></el-table-column>
                <el-table-column prop="is_use" :formatter="UseFormatter" width="80" label="状态"></el-table-column>
                <el-table-column prop="store_name" label="门店"></el-table-column>
                <el-table-column prop="clerked_at" label="核销时间" width="180"
                                 :formatter="ClerkFormatter"></el-table-column>
                <el-table-column label="操作" width="80">
                    <template slot-scope="scope">
                        <el-button type="text" circle size="mini" @click="destroy(scope.row)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div style="text-align: right;margin: 20px 0;">
                <el-pagination @current-change="pagination" background layout="prev, pager, next"
                               :page-count="pageCount"></el-pagination>
            </div>
        </div>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                form: [],
                listLoading: false,
                pageCount: 0,
                ids: [],
                send_date: '',
                clerk_date: '',
                status: 0,
                store_id: 0,
                card_name: '',
                store_name: '',
                clerk_id: '',
                user_name: '',
                key_code: '0',
                key_name: ''
            };
        },
        methods: {
            selsChange(row) {
                this.ids = row;
            },

            ClerkFormatter(row) {
                if (row.clerked_at == '0000-00-00 00:00:00') {
                    return '';
                } else {
                    return row.clerked_at;
                }
            },

            UseFormatter(row) {
                return row.is_use == 1 ? '已使用' : '未使用';
            },

            handleCommand(row) {
                this.status = row;
                this.page = 1;
                this.getList();
            },

            search() {
                this.saveUserId(0)
                if (this.key_code == 0) {
                    this.store_name = this.key_name
                    this.user_name = ''
                    this.card_name = ''
                } else if (this.key_code == 1) {
                    this.card_name = this.key_name
                    this.user_name = ''
                    this.store_name = ''
                } else if (this.key_code == 2) {
                    this.user_name = this.key_name
                    this.store_name = ''
                    this.card_name = ''
                }
                this.page = 1;
                this.getList();
            },

            pagination(currentPage) {
                this.page = currentPage;
                this.getList();
            },

            batchDestroy: function () {
                if (this.ids.length == 0) {
                    this.$message.error('请先勾选商品');
                    return;
                }
                let ids = [];
                this.ids.forEach(v => {
                    ids.push(v.id);
                });
                this.$confirm('确认删除选中记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'mall/user/card-batch-destroy'
                        },
                        data: {ids: ids},
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            location.reload();
                        }
                    }).catch(e => {
                        this.listLoading = false;
                    });
                });
            },
            //删除
            destroy: function (column) {
                this.$confirm('确认删除该记录吗?', '提示', {
                    type: 'warning'
                }).then(() => {
                    this.listLoading = true;
                    request({
                        params: {
                            r: 'mall/user/card-destroy'
                        },
                        data: {id: column.id},
                        method: 'post'
                    }).then(e => {
                        if (e.data.code === 0) {
                            this.getList();
                        }
                    }).catch(e => {
                        this.listLoading = false;
                    });
                });
            },

            getList() {
                this.listLoading = true;
                let userId = 0;
                let uId = getCookieValue('cardUserId')
                if (uId) {
                    userId = uId;
                }
                request({
                    params: {
                        r: 'mall/user/card',
                        page: this.page,
                        user_id: userId,
                        store_id: getQuery('store_id'),
                        clerk_id: getQuery('clerk_id'),
                        card_name: this.card_name,
                        store_name: this.store_name,
                        user_name: this.user_name,
                        status: this.status,
                        send_date: this.send_date,
                        clerk_date: this.clerk_date,
                    },
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code == 0) {
                        this.form = e.data.data.list;
                        this.pageCount = e.data.data.pagination.page_count;
                        if (e.data.data.by_username) {
                            this.key_name = e.data.data.by_username
                            this.key_code = '2';
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                });
            },
            saveUserId(userId) {
                let Days = 1;
                let exp = new Date();
                exp.setTime(exp.getTime() + Days*24*60*60*1000);
                document.cookie = "cardUserId=" + userId + ";expires=" + exp.toGMTString();
            },
        },

        mounted() {
            if (getQuery('user_id')) {
                this.saveUserId(getQuery('user_id'))
                navigateTo({
                    r: 'mall/user/card',
                });
            }
            this.getList();
        }
    })
</script>