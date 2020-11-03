<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */
?>

<style>
    .app-cat-list .input-item {
        display: inline-block;
        width: 250px;
    }

    .app-cat-list .input-item .el-input__inner {
        border-right: 0;
    }

    .app-cat-list .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-cat-list .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-cat-list .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .app-cat-list .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .app-cat-list .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .app-cat-list .table-body .cat-item {
        display: flex;
        justify-content: space-between;
        height: 65px;
        align-items: center;
        padding-left: 5px;
        border-top: 1px solid #F5F5F5;
        color: #000000;
        cursor: pointer;
        width: 100%;
        /*min-width: 350px*/
    }

    .app-cat-list .active {
        background-color: #F5F5F5;
        /*color: #3399FF;*/
    }

    .app-cat-list .table-body .cat-item:first-of-type {
        border-top: 0;
    }

    .app-cat-list .table-body .cat-item .cat-name {
        font-size: 16px;
        display: flex;
        align-items: center;
    }

    .app-cat-list .table-body .cat-item .el-form-item {
        margin-bottom: 0;
    }

    .app-cat-list .table-body .cat-item .el-form-item .el-button {
        padding: 0;
        margin: 0 5px;
    }

    .app-cat-list .table-body .cat-item .el-input {
        width: 100px;
    }

    /*.app-cat-list .cat-item:hover .edit-sort {*/
    /*display: inline-block;*/
    /*}*/

    .app-cat-list .change {
        width: 80px;
    }

    .app-cat-list .change .el-input__inner {
        height: 22px !important;
        line-height: 22px !important;
        padding: 0;
    }

    /*.app-cat-list .edit-sort {*/
    /*display: none;*/
    /*}*/

    .app-cat-list .cat-name-info {
        width: 100px;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .app-cat-list .cat-list {
        white-space: nowrap;
    }

    .app-cat-list .cat-list .el-card {
        /*margin-left: -5px;*/
        /*width: 546px;*/
        display: inline-block;
        /*vertical-align: top;*/
    }

    .app-cat-list .cat-list .el-card:first-of-type {
        margin-left: 0
    }

    .app-cat-list .cat-list .card-item-box {
        margin-right: 5px;
        height: 552px;
    }

    .app-cat-list .cat-id {
        width: 55px;
        color: #999;
        font-size: 14px;
        margin-left: 5px;
    }

    .app-cat-list .el-form--inline .el-form-item {
        margin-right: 0px;
    }

    .app-cat-list .cat-icon {
        margin-right: 10px;
    }

    .app-cat-list .cat-item .el-form-item {
        margin-bottom: 0;
    }

    .app-cat-list .edit-sort-box .el-button.is-circle {
        padding: 3px;
    }
</style>

<template id="app-cat-list">
    <div class="app-cat-list">
        <el-form size="small" :inline="true" :model="search" @submit.native.prevent>
            <template v-if="!isEditSort">
                <el-form-item>
                    <div class="input-item">
                        <el-input @keyup.enter.native="searchCat" clearable @clear="searchCat" size="small"
                                  placeholder="请输入搜索内容"
                                  v-model="search.keyword">
                            <el-button slot="append" icon="el-icon-search" @click="searchCat"></el-button>
                        </el-input>
                    </div>
                </el-form-item>
            </template>
            <el-form-item v-if="!isEditSort">
                <el-button @click="isEditSort=true" style="margin-left: 10px" type="primary">编辑排序</el-button>
            </el-form-item>
            <el-form-item v-if="isEditSort">
                <el-button :loading="submitLoading" @click="storeSort" style="margin-left: 10px" type="primary">保存排序
                </el-button>
                <el-button @click="isEditSort=false" style="margin-left: 10px">取消编辑
                </el-button>
                <span style="margin-left: 10px;">拖动分类名称排序</span>
            </el-form-item>
        </el-form>
        <div class="cat-list" flex="dir:left box:mean">
            <el-card v-loading="listLoading" shadow="never" class="card-item-box"
                     body-style="padding:0;height: 500px;overflow:auto">
                <div slot="header">
                    一级分类
                </div>
                <div v-if="first_cat_list.length > 0" style="overflow:auto" @scroll="firstScroll">
                    <draggable v-model="first_cat_list" :options="{disabled:!isEditSort}">
                        <div :style="{'cursor': isEditSort ? 'move' : 'pointer'}"
                             @click="select(item)"
                             v-for="(item,index) in first_cat_list"
                             class="cat-item"
                             :class="first_cat.id == item.id ? 'active':''">
                            <el-row flex="cross:center" style="height: 50px">
                                <el-col :span="4">
                                    <el-tooltip class="item" effect="dark" content="ID" placement="top">
                                        <div class="cat-id">{{item.id}}</div>
                                    </el-tooltip>
                                </el-col>
                                <el-col :span="13" flex="cross:center">
                                    <app-image class="cat-icon" :src="item.pic_url" width="30px"
                                               height="30px"></app-image>
                                    <div class="cat-name-info">
                                        <el-tooltip class="item" effect="dark" :content="item.name" placement="top">
                                            <span>{{item.name}}</span>
                                        </el-tooltip>
                                    </div>
                                </el-col>
                                <el-col :span="7">
                                    <el-form v-if="!isEditSort" flex="cross:center" :inline="true"
                                             @submit.native.prevent>
                                        <el-form-item>
                                            <el-button style="display: block;" type="text"
                                                       class="set-el-button"
                                                       size="mini" circle @click="edit(item.id)">
                                                <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                                    <img src="statics/img/mall/edit.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </el-form-item>
                                        <el-form-item>
                                            <el-button style="display: block;" type="text"
                                                       class="set-el-button"
                                                       size="mini" circle @click="destroy(item)">
                                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                    <img src="statics/img/mall/del.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </el-form-item>
                                    </el-form>
                                </el-col>
                            </el-row>
                        </div>
                    </draggable>
                </div>

            </el-card>
            <el-card v-loading="listLoading_2" shadow="never" class="card-item-box"
                     body-style="padding:0;height: 500px;overflow:auto">
                <div v-if="first_cat.child.length > 0" slot="header">
                    二级分类
                </div>
                <div v-if="first_cat.child.length > 0" style="overflow:auto" @scroll="scrollAgain">
                    <draggable v-model="first_cat.child" :options="{disabled:!isEditSort}">
                        <div @click="selectAgain(item)"
                             :class="sec_cat.id == item.id ? 'active':''"
                             v-for="(item,index) in first_cat.child"
                             :style="{'cursor': isEditSort ? 'move' : 'pointer'}"
                             class="cat-item">
                            <el-row flex="cross:center" style="height: 50px;">
                                <el-col :span="4">
                                    <el-tooltip class="item" effect="dark" content="ID" placement="top">
                                        <div class="cat-id">{{item.id}}</div>
                                    </el-tooltip>
                                </el-col>
                                <el-col :span="13" flex="cross:center">
                                    <app-image class="cat-icon" :src="item.pic_url" width="30px"
                                               height="30px"></app-image>
                                    <div class="cat-name-info">
                                        <el-tooltip class="item" effect="dark" :content="item.name" placement="top">
                                            <span>{{item.name}}</span>
                                        </el-tooltip>
                                    </div>
                                </el-col>
                                <el-col :span="7">
                                    <el-form v-if="!isEditSort" flex="cross:center" :inline="true"
                                             @submit.native.prevent>
                                        <el-form-item>
                                            <el-button style="display: block;" type="text"
                                                       class="set-el-button"
                                                       size="mini" circle @click="edit(item.id)">
                                                <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                                    <img src="statics/img/mall/edit.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </el-form-item>
                                        <el-form-item>
                                            <el-button style="display: block;" type="text"
                                                       class="set-el-button"
                                                       size="mini" circle @click="destroy(item)">
                                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                    <img src="statics/img/mall/del.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </el-form-item>
                                    </el-form>
                                </el-col>
                            </el-row>
                        </div>
                    </draggable>
                </div>
            </el-card>
            <el-card v-loading="listLoading_3" shadow="never" class="card-item-box"
                     body-style="padding:0;height: 500px;overflow:auto">
                <div v-if="sec_cat.child.length > 0" slot="header">三级分类</div>
                <div v-if="sec_cat.child.length > 0">
                    <draggable v-model="sec_cat.child" :options="{disabled:!isEditSort}">
                        <div @click="selectThird(item)"
                             v-for="(item,index) in sec_cat.child"
                             class="cat-item"
                             :style="{'cursor': isEditSort ? 'move' : 'pointer'}"
                             :class="third_cat_id == item.id ? 'active':''">
                            <el-row flex="cross:center" style="height:50px;">
                                <el-col :span="4">
                                    <el-tooltip class="item" effect="dark" content="ID" placement="top">
                                        <div class="cat-id">{{item.id}}</div>
                                    </el-tooltip>
                                </el-col>
                                <el-col :span="13" flex="cross:center">
                                    <app-image class="cat-icon" :src="item.pic_url" width="30px"
                                               height="30px"></app-image>
                                    <div class="cat-name-info">
                                        <el-tooltip class="item" effect="dark" :content="item.name" placement="top">
                                            <span>{{item.name}}</span>
                                        </el-tooltip>
                                    </div>
                                </el-col>
                                <el-col :span="7">
                                    <el-form v-if="!isEditSort" flex="cross:center" :inline="true"
                                             @submit.native.prevent>
                                        <el-form-item>
                                            <el-button style="display: block;" type="text"
                                                       class="set-el-button"
                                                       size="mini" circle @click="edit(item.id)">
                                                <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                                    <img src="statics/img/mall/edit.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </el-form-item>
                                        <el-form-item>
                                            <el-button style="display: block;" type="text"
                                                       class="set-el-button"
                                                       size="mini" circle @click="destroy(item)">
                                                <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                    <img src="statics/img/mall/del.png" alt="">
                                                </el-tooltip>
                                            </el-button>
                                        </el-form-item>
                                    </el-form>
                                </el-col>
                            </el-row>
                        </div>
                    </draggable>
                </div>
            </el-card>
        </div>
        <!-- 分类搜索弹框-->
        <el-dialog :visible.sync="searchFinish">
            <el-table border :data="searchList" @row-click="rowClick">
                <el-table-column align="center" property="status_text" label="分类等级" width="200">
                    <template slot-scope="scope">
                        <span v-if="!scope.row.status_text">一级分类</span>
                        <span v-else>{{scope.row.status_text}}</span>
                    </template>
                </el-table-column>
                <el-table-column align="center" property="name" label="分类名称" width="300">
                    <template slot-scope="scope">
                        <div style="display: flex;align-items: center;justify-content: center">
                            <app-image style="margin-right: 10px" :src="scope.row.pic_url" width="30px"
                                       height="30px"></app-image>
                            <span>{{scope.row.name}}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column align="center" label="操作">
                    <template slot-scope="scope">
                        <el-button type="text" class="set-el-button" size="mini" circle @click="edit(scope.row.id)">
                            <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                <img src="statics/img/mall/edit.png" alt="">
                            </el-tooltip>
                        </el-button>
                        <el-button type="text" class="set-el-button" size="mini" circle @click="destroy(scope.row)">
                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                <img src="statics/img/mall/del.png" alt="">
                            </el-tooltip>
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-cat-list', {
        template: '#app-cat-list',
        data() {
            return {
                list: [],
                first_cat: {
                    child: []
                },
                sec_cat: {
                    child: []
                },
                third_cat_id: null,
                listLoading: false,
                listLoading_2: false,
                listLoading_3: false,
                page: 1,
                pageCount: 0,
                first_cat_list: [],
                page_2: 1,
                pageCount_2: 0,
                page_3: 1,
                pageCount_3: 0,
                searchFinish: false,
                searchList: [],
                search: {
                    keyword: ''
                },
                editSortVisible: false,
                editSortForm: {
                    sort: 100,
                },
                submitLoading: false,
                isEditSort: false,
            }
        },
        methods: {
            changeSort(e) {
                this.editSortVisible = true;
                this.editSortForm = e;
            },
            // 修改排序
            changeSortSubmit(formName) {
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        this.submitLoading = true;
                        request({
                            params: {
                                r: 'mall/cat/sort',
                                id: this.editSortForm.id,
                                sort: this.editSortForm.sort
                            },
                            method: 'get',
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code === 0) {
                                this.editSortVisible = false
                                this.$message({
                                    message: '修改成功',
                                    type: 'success'
                                });
                                if (this.editSortForm.parent_id == '0') {
                                    this.getList();
                                } else if (this.editSortForm == this.first_cat.id) {
                                    this.children_2();
                                } else if (this.editSortForm == this.sec_cat.id) {
                                    this.children_3();
                                }
                            } else {
                                this.$message({
                                    message: e.data.msg,
                                    type: 'warning'
                                });
                            }
                        }).catch(e => {
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            // 一级分类滚动加载更多
            firstScroll(e) {
                if (e.srcElement.scrollTop + e.srcElement.offsetHeight == e.srcElement.scrollHeight && this.list.length == 20) {
                    this.page += 1;
                    this.getList();
                }
            },
            // 二级分类滚动加载更多
            scrollAgain(e) {
                if (e.srcElement.scrollTop + e.srcElement.offsetHeight == e.srcElement.scrollHeight && this.first_cat.child.length == 20) {
                    this.page_2 += 1;
                    this.children_2();
                }
            },
            // 三级分类滚动加载更多
            thirdScroll(e) {
                if (e.srcElement.scrollTop + e.srcElement.offsetHeight == e.srcElement.scrollHeight && this.sec_cat.child.length == 20) {
                    this.page_3 += 1;
                    this.children_3();
                }
            },
            // 选中一级分类
            select(row) {
                if (this.isEditSort) {
                    return;
                }
                this.first_cat = row;
                this.sec_cat = {
                    child: []
                }
            },
            // 选中二级分类
            selectAgain(row) {
                if (this.isEditSort) {
                    return;
                }
                this.sec_cat = row;
            },
            selectThird(row) {
                if (this.isEditSort) {
                    return;
                }
                this.third_cat_id = row.id;
            },
            // 获取数据
            getList() {
                let self = this;
                self.list = [];
                self.sec_cat.child = [];
                self.first_cat.child = [];
                self.listLoading = true;
                self.listLoading_2 = true;
                self.listLoading_3 = true;
                request({
                    params: {
                        r: 'mall/cat/index',
                        page: self.page,
                        keyword: self.search.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.listLoading_2 = false;
                    self.listLoading_3 = false;
                    self.list = e.data.data.list;
                    self.first_cat_list = e.data.data.list;
                    if (e.data.data.list.length > 0) {
                        self.first_cat = self.first_cat_list[0]
                        if (self.first_cat.child.length > 0) {
                            self.sec_cat = self.first_cat.child[0]
                        }
                    }
                }).catch(e => {
                    self.listLoading = false;
                    self.listLoading_2 = false;
                    self.listLoading_3 = false;
                    console.log(e);
                });
            },
            // 搜索
            searchCat() {
                let self = this;
                self.searchList = [];
                if (self.search.keyword == '') {
                    this.getList();
                    return false
                }
                request({
                    params: {
                        r: 'mall/cat/index',
                        page: 1,
                        keyword: self.search.keyword
                    },
                    method: 'get',
                }).then(e => {
                    self.searchFinish = true;
                    self.searchList = e.data.data.list;
                }).catch(e => {
                    console.log(e);
                });
            },
            // 编辑
            edit(id) {
                navigateTo({
                    r: 'mall/cat/edit',
                    id: id,
                });
            },
            // 删除
            destroy(row) {
                let self = this;
                self.$confirm('删除该条数据, 是否继续?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    self.listLoading = true;
                    request({
                        params: {
                            r: 'mall/cat/destroy',
                        },
                        method: 'post',
                        data: {
                            id: row.id,
                        }
                    }).then(e => {
                        self.listLoading = false;
                        if (e.data.code === 0) {
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
            // 二级分类列表
            children_2() {
                let self = this;
                self.listLoading_2 = true;
                request({
                    params: {
                        r: 'mall/cat/children-list',
                        id: self.first_cat.id,
                        page: self.page_2
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading_2 = false;
                    if (self.page_2 == 1) {
                        self.first_cat.child = e.data.data.list;
                    } else {
                        self.first_cat.child.concat(e.data.data.list);
                    }
                    self.pageCount_2 = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            // 三级分类列表
            children_3() {
                let self = this;
                self.listLoading_3 = true;
                request({
                    params: {
                        r: 'mall/cat/children-list',
                        id: self.sec_cat.id,
                        page: self.page_3
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading_3 = false;
                    if (self.page_3 == 1) {
                        self.sec_cat.child = e.data.data.list;
                    } else {
                        self.sec_cat.child.concat(e.data.data.list);
                    }
                    self.pageCount_3 = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            switchStatus(id) {
                let self = this;
                request({
                    params: {
                        r: 'mall/cat/switch-status',
                        id: id,
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code == 0) {
                        self.$message.success(e.data.msg);
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            rowClick(row) {
                let self = this;
                self.list.forEach(function (item1) {
                    if (item1.id == row.id) {
                        self.first_cat = item1;
                        self.sec_cat = {
                            child: [],
                        }
                    }
                    if (item1.child) {
                        item1.child.forEach(function (item2) {
                            if (item2.id == row.id) {
                                self.first_cat = item1;
                                self.sec_cat = item2;
                                self.third_cat_id = null;
                            }

                            if (item2.child) {
                                item2.child.forEach(function (item3) {
                                    if (item3.id == row.id) {
                                        self.first_cat = item1;
                                        self.sec_cat = item2;
                                        self.third_cat_id = item3.id;
                                    }
                                })
                            }
                        })
                    }
                })
                self.searchFinish = false;
            },
            storeSort() {
                let self = this;
                self.submitLoading = true;

                let firstList = [];
                let secondList = [];
                let thirdList = [];

                self.first_cat_list.forEach(function (item) {
                    firstList.push({
                        id: item.id,
                        name: item.name
                    })
                });
                self.first_cat.child.forEach(function (item) {
                    secondList.push({
                        id: item.id,
                        name: item.name
                    })
                })
                self.sec_cat.child.forEach(function (item) {
                    thirdList.push({
                        id: item.id,
                        name: item.name
                    })
                })

                request({
                    params: {
                        r: 'mall/cat/store-sort'
                    },
                    method: 'post',
                    data: {
                        first_list: JSON.stringify(firstList),
                        second_list: JSON.stringify(secondList),
                        third_list: JSON.stringify(thirdList),
                    }
                }).then(e => {
                    self.submitLoading = false;
                    if (e.data.code === 0) {
                        self.isEditSort = false;
                        self.$message.success(e.data.msg);
                        self.getList();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    self.submitLoading = false;
                });
            }
        },
        mounted() {
            this.getList();
        }
    })
</script>