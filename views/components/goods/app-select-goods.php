<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */
?>
<style>
    .app-select-goods .cover-pic {
        height: 50px;
        width: 50px;
        margin-right: 10px
    }

    .app-select-goods .el-input__inner {
        border-color: #DCDFE6 !important;
    }
</style>
<template id="app-select-goods">
    <div class="app-select-goods">
        <el-dialog title="选择商品" :visible.sync="dialogSelectVisible" top="5vh" width="960px">
            <el-input placeholder="根据名称搜索" v-model="search.keyword" @keyup.enter.native="searchList" prop="rr">
                <el-button slot="append" @click="searchList">搜索</el-button>
            </el-input>
            <el-table v-loading="listLoading" :data="list" stripe height="544"
                      style="margin-top:27px"
                      @selection-change="handleSelectionChange" border>
                <el-table-column v-if="false" type="selection" width="55"></el-table-column>
                <el-table-column v-if="false" label="ID" width="100" prop="id"></el-table-column>
                <el-table-column width="100" label="ID" props="id">
                    <template slot-scope="scope">
                        <el-radio-group v-model="radioSelection" @change="handleSelectionChange(scope.row)">
                            <el-radio :label="scope.row.id"></el-radio>
                        </el-radio-group>
                    </template>
                </el-table-column>
                <el-table-column label="名称" width="810">
                    <template slot-scope="scope">
                        <div flex="dir:left cross:center">
                            <el-image class="cover-pic" :src="scope.row.goodsWarehouse.cover_pic"></el-image>
                            <app-ellipsis :line="2">{{scope.row.name}}</app-ellipsis>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <div slot="footer" class="dialog-footer">
                <div style="float: left">
                    <el-pagination
                            v-if="pagination"
                            background
                            :page-size="pagination.pageSize"
                            @current-change="getList"
                            layout="prev, pager, next"
                            :total="pagination.total_count">
                    </el-pagination>
                </div>
                <el-button v-if="false" @click="dialogSelectVisible = false">取 消</el-button>
                <el-button type="primary" @click="confirm">确 定</el-button>
            </div>
        </el-dialog>
        <div @click="showModel">
            <slot></slot>
        </div>
    </div>
</template>
<script>
    Vue.component('app-select-goods', {
        template: '#app-select-goods',
        props: {
            url: {
                type: String,
                default: 'mall/goods/index'
            },
        },
        data() {
            return {
                pagination: null,
                dialogSelectVisible: false,
                listLoading: false,
                radioSelection: 0,
                list: [],
                search: {
                    keyword: '',
                }
            }
        },
        methods: {
            searchList() {
                this.getList();
            },
            showModel() {
                this.dialogSelectVisible = true;
                this.getList();
            },
            getList(page = 1) {
                this.listLoading = true;
                request({
                    params: {
                        r: this.url,
                        search: this.search,
                        page,
                    },
                    method: 'get'
                }).then(e => {
                    this.listLoading = false;
                    if (e.data.code === 0) {
                        this.list = e.data.data.list;
                        this.pagination = e.data.data.pagination;
                        console.log(pagination)
                    }
                }).catch(e => {
                    this.listLoading = false;
                })
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            confirm() {
                this.$emit('selected', this.multipleSelection);
                this.dialogSelectVisible = false;
            }
        }
    })


</script>