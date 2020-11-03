<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */
?>

<style>
    .table-body {
        padding: 20px;
        background-color: #fff;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <div>
                <span>直播管理</span>
            </div>
        </div>
        <div class="table-body">
            <el-button :loading="buttonLoading" style="margin-bottom: 15px;" type="primary" size="small" @click="search">获取最新直播间列表</el-button>
            <el-table
                v-loading="listLoading"
                :data="list"
                border
                style="width: 100%">
                <el-table-column
                        width="80"
                        prop="roomid"
                        label="房间ID"
                        width="120">
                </el-table-column>
                <el-table-column
                    label="房间名"
                    width="220">
                    <template slot-scope="scope">
                        <app-ellipsis :line="1">{{scope.row.name}}</app-ellipsis>
                    </template>
                </el-table-column>
                <el-table-column
                    label="主播信息">
                    <template slot-scope="scope">
                        <div flex="cross:center">
                            <img style="width: 45px;height: 45px " :src="scope.row.anchor_img">
                            <span style="margin-left: 10px;">{{scope.row.anchor_name}}</span>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                        width="120"
                    label="封面图片">
                    <template slot-scope="scope">
                        <img :src="scope.row.cover_img" style="width: 45px;height: 45px">
                    </template>
                </el-table-column>
                <el-table-column
                        width="180"
                    label="计划直播时间">
                    <template slot-scope="scope">
                        <div>{{scope.row.start_time}}</div>
                        <div>{{scope.row.end_time}}</div>
                    </template>
                </el-table-column>
                <el-table-column
                    label="状态">
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.live_status === 101" type="success">{{scope.row.status_text}}</el-tag>
                        <el-tag v-else-if="scope.row.live_status === 102" type="primary">{{scope.row.status_text}}</el-tag>
                        <el-tag v-else-if="scope.row.live_status === 103" type="warning">{{scope.row.status_text}}</el-tag>
                        <el-tag v-else-if="scope.row.live_status === 104" type="error">{{scope.row.status_text}}</el-tag>
                        <el-tag v-else-if="scope.row.live_status === 105">{{scope.row.status_text}}</el-tag>
                        <el-tag v-else-if="scope.row.live_status === 106" type="error">{{scope.row.status_text}}</el-tag>
                        <el-tag v-else type="error">{{scope.row.status_text}}</el-tag>
                    </template>
                </el-table-column>
            </el-table>

            <div style="text-align: right;margin: 20px 0;">
                <el-pagination
                    @current-change="pagination"
                    background
                    layout="prev, pager, next"
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
                buttonLoading: false,
                page: 1,
                pageCount: 0,
                is_refresh: 0,
            };
        },
        methods: {
            search() {
                this.page = 1;
                this.is_refresh = 1;
                this.buttonLoading = true;
                this.getList();
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
                        r: 'mall/live/index',
                        page: self.page,
                        is_refresh: self.is_refresh,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.buttonLoading = false;
                    self.is_refresh = 0;
                    if (e.data.code == 0) {
                        self.list = e.data.data.list;
                        self.pageCount = e.data.data.pageCount;
                    } else {
                        self.$message.error(e.data.msg)
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
        },
        mounted: function () {
            this.getList();
        }
    });
</script>
