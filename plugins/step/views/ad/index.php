<?php defined('YII_ENV') or exit('Access Denied'); ?>
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

    .table-body .el-table .el-button {
        padding: 0!important;
        border: 0;
        margin: 0 5px;
    }

    .el-form-item {
        margin-bottom: 0;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <span>流量主列表</span>
            <el-button style="float: right; margin: -5px 0" type="primary" size="small"
                       @click="add">添加流量主
            </el-button>
        </div>
        <div class="table-body">
            <div class="input-item">
                <el-input @keyup.enter.native="search" size="small" placeholder="请输入搜索内容" v-model="keyword" clearable @clear='search'>
                    <el-button slot="append" icon="el-icon-search" @click="search"></el-button>
                </el-input>
            </div>
            <el-table v-loading="loading" border :data="list" style="width: 100%;margin-bottom: 15px">
                <el-table-column align="center" width='200' prop="id" label="ID"></el-table-column>
                <el-table-column prop="name" width='400' label="位置">
                    <template slot-scope="scope">
                        <div v-for="item in select" v-if="scope.row.site == item.value">{{item.name}}</div>
                    </template>
                </el-table-column>
                <el-table-column align="center" prop="unit_id" label="广告单元ID"></el-table-column>
                <el-table-column align="center" width='400' prop="status" label="状态">
                    <template slot-scope="scope">
                        <el-switch v-model="scope.row.status" active-value="1" @change="handleStatus(scope.row)" inactive-value="0"></el-switch>
                    </template>
                </el-table-column>
                <el-table-column width='300' label="操作">
                    <template slot-scope="scope">
                        <el-button circle size="mini" type="text" @click="edit(scope.row)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button circle size="mini" type="text" @click="del(scope.row)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div flex="box:last cross:center">
                <div style="visibility: hidden">
                    <el-button plain type="primary" size="small">批量操作1</el-button>
                    <el-button plain type="primary" size="small">批量操作2</el-button>
                </div>
                <div>
                    <el-pagination v-if="pagination"
                            :page-size="pagination.pageSize" style="display: inline-block;float: right;" background @current-change="pageChange" layout="prev, pager, next" :total="pagination.total_count">
                    </el-pagination>
                </div>
            </div>
        </div>
    </el-card>
    <el-dialog title="添加流量主" :visible.sync="ad_list" width="450px">
        <el-form v-loading="form_loading" :model="form" label-width="100px">
            <el-form-item label="广告单元ID">
                <el-input style="width: 250px" size="small" v-model="form.unit_id" autocomplete="off"></el-input>
            </el-form-item>
            <el-form-item label="活动区域">
                <el-select style="width: 250px" size="small" v-model="form.site" placeholder="请选择活动区域">
                    <el-option v-for="item in select" :label="item.name" :value="item.value"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="状态">
                <el-switch v-model="form.status" active-value="1" inactive-value="0"></el-switch>
            </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button size="small" @click="ad_list = false">取 消</el-button>
            <el-button size="small" type="primary" @click="submit">确 定</el-button>
        </div>
    </el-dialog>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            loading: false,
            ad_list: false,
            form_loading: false,
            list: [],
            select: [],
            pagination: null,
            keyword: '',
            form: {
                id:'',
                type: '1',
                status: '0'
            }
        };
    },

    methods: {
        //分页
        pageChange(page) {
            this.getList(page)
        },

        search() {
            this.getList(1);
        },

        add() {
            this.ad_list = true;
            this.form = {
                id:'',
                site: '1',
                status: '0'
            }
        },

        edit(row) {
            this.ad_list = true;
            this.form_loading = true;
            let id = row.id;
            let that = this;
            request({
                params: {
                    r: 'plugin/step/mall/ad/edit',
                    id: id
                },
                method: 'get'
            }).then(e => {
                if (e.data.code === 0) {
                    that.form_loading = false;
                    that.form = e.data.data.list;
                } else {
                    that.$message.error(e.data.msg);
                }
            }).catch(e => {
            });
        },

        handleStatus(row) {
            let that = this;
            let para = {
                id: row.id,
                status: row.status
            };
            that.loading = true;
            request({
                params: {
                    r: 'plugin/step/mall/ad/edit-status',
                },
                data: para,
                method: 'post',
            }).then(e => {
                if (e.data.code === 0) {
                    that.loading = false;
                    that.$message({
                        message: e.data.msg,
                        type: 'success'
                    });
                    that.getList();
                } else {
                    that.$message.error(e.data.msg);
                }
            }).catch(e => {
                that.loading = false;
            });
        },

        getList(page) {
            let self = this;
            self.loading = true;
            request({
                params: {
                    r: 'plugin/step/mall/ad',
                    page:page,
                    id: self.keyword,
                },
                method: 'get',
            }).then(e => {
                self.loading = false;
                if (e.data.code === 0) {
                    this.list = e.data.data.list;
                    this.select = e.data.data.select;
                    this.pagination = e.data.data.pagination;
                } else {
                    this.$message.error(e.data.msg);
                }
            }).catch(e => {
                self.loading = false;
            });
        },

        del: function(row) {
            this.$confirm('确认删除该记录吗?', '提示', {
                type: 'warning'
            }).then(() => {
                let para = { id: row.id};
                request({
                    params: {
                        r: 'plugin/step/mall/ad/destroy'
                    },
                    data: para,
                    method: 'post'
                }).then(e => {
                    if (e.data.code === 0) {
                    const h = this.$createElement;
                    this.$message({
                        message: '删除成功',
                        type: 'success'
                    });
                    this.getList();
                }else{
                    this.$alert(e.data.msg, '提示', {
                      confirmButtonText: '确定'
                    })
                }
                }).catch(e => {
                    this.$alert(e.data.msg, '提示', {
                      confirmButtonText: '确定'
                    })
                });
            })
        },


        submit() {
            let self = this;
            let para = this.form;
            request({
                params: {
                    r: 'plugin/step/mall/ad/edit',
                },
                data: para,
                method: 'post',
            }).then(e => {
                self.loading = false;
                if (e.data.code === 0) {
                        this.$message({
                            message: e.data.msg,
                            type: 'success'
                        });
                        setTimeout(function(){
                            self.ad_list = false;
                            self.getList();
                        },500); 
                    } else {
                        this.$message.error(e.data.msg);
                    }
            }).catch(e => {
                self.loading = false;
            });
        },
    },
    created() {
        this.getList(1);
    }
})
</script>