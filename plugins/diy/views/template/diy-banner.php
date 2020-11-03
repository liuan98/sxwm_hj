<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/26
 * Time: 16:31
 */
?>
<style>
    .diy-banner .banner-container {
        background: #fff;
    }

    .diy-banner .banner-container .banner-img {
        height: 100%;
        width: 100%;
        background-repeat: no-repeat;
        background-position: center;
    }

    .diy-banner .banner-container .banner-img-cover {
        background-size: cover;
    }

    .diy-banner .banner-container .banner-img-contain {
        background-size: contain;
    }

    .diy-banner .banner-edit-item {
        border: 1px solid #dcdfe6;
        padding: 5px;
        margin-bottom: 5px;
    }

    .diy-banner .pic-upload {
        display: block;
        width: 65px;
        height: 65px;
        line-height: 65px;
        border: 1px dashed #8bc4ff;
        color: #8bc4ff;
        background: #f9f9f9;
        cursor: pointer;
        background-size: 100% 100%;
        font-size: 28px;
        text-align: center;
        vertical-align: middle;
    }

    .diy-banner .banner-edit-options {
        position: relative;
    }

    .diy-banner .banner-edit-options .el-button {
        height: 25px;
        line-height: 25px;
        width: 25px;
        padding: 0;
        text-align: center;
        border: none;
        border-radius: 0;
        position: absolute;
        margin-left: 0;
    }

    .diy-banner .banner-style-item {
        width: 100px;
        border: 1px solid #ebeef5;
        cursor: pointer;
        padding: 5px;
        line-height: normal;
        text-align: center;
        color: #606266;
    }

    .diy-banner .banner-style-item + .banner-style-item {
        margin-left: 5px;
    }

    .diy-banner .banner-style-item.active {
        border-color: #00a0e9;
        color: #409EFF;
    }

    .diy-banner .banner-style-1,
    .diy-banner .banner-style-2 {
        display: block;
        height: 50px;
        margin: 0 auto 5px;
        position: relative;
    }

    .diy-banner .banner-style-1 {
        background: #e6f4ff;
    }

    .diy-banner .banner-style-2 > div {
        background: #e6f4ff;
        position: absolute;
        left: 0;
        top: 10%;
        height: 50px;
        width: 100%;
        z-index: 0;
        zoom: .75;
    }

    .diy-banner .banner-style-2 > div:last-child {
        left: 15%;
        zoom: 1;
        box-shadow: 0 0 5px rgba(0, 0, 0, .2);
        z-index: 1;
        width: 70%;
        top: 0;
    }

    .chooseLink .el-input-group__append {
        background-color: #fff;
    }
</style>
<template id="diy-banner">
    <div class="diy-banner">
        <div class="diy-component-preview">
            <div class="banner-container" :style="cContainerStyle">
                <el-carousel :height="data.height+'px'" :type="data.style==2?'card':''">
                    <el-carousel-item v-for="(banner,index) in data.banners" :key="index">
                        <div :class="'banner-img '+cBannerImgClass"
                             :style="'background-image: url('+banner.picUrl+');'"></div>
                    </el-carousel-item>
                </el-carousel>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form @submit.native.prevent label-width="100px">
                <el-form-item label="样式">
                    <div flex="dir:left">
                        <div @click="data.style=1" class="banner-style-item" :class="data.style==1?'active':''">
                            <div class="banner-style-1"></div>
                            <div>样式1</div>
                        </div>
                        <div @click="data.style=2" class="banner-style-item" :class="data.style==2?'active':''">
                            <div class="banner-style-2" flex>
                                <div></div>
                                <div></div>
                            </div>
                            <div>样式2</div>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item label="填充方式">
                    <app-radio v-model="data.fill" :label="0">留白</app-radio>
                    <app-radio v-model="data.fill" :label="1">填充</app-radio>
                </el-form-item>
                <el-form-item label="高度">
                    <el-input size="small" v-model.number="data.height" min="10" type="number">
                        <template slot="append">px</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="拉取轮播图">
                    <el-button size="small" @click="selectBanner">选择</el-button>
                </el-form-item>
                <el-form-item label="轮播图">
                    <div class="banner-edit-item" v-for="(banner,index) in data.banners">
                        <div class="banner-edit-options">
                            <el-button @click="bannerItemDelete(index)" type="primary" icon="el-icon-delete"
                                       style="top: -6px;right: -31px;"></el-button>
                        </div>
                        <div flex="box:first">
                            <div>
                                <app-image-upload width="750" :height="data.height" v-model="banner.picUrl"
                                                  style="margin-right: 5px;"></app-image-upload>
                            </div>
                            <div class="chooseLink">
                                <div @click="pickLinkClick(index)">
                                    <el-input v-model="banner.url" placeholder="点击选择链接" readonly
                                                  size="small">
                                        <app-pick-link slot="append" @selected="linkSelected">
                                            <el-button size="small">选择链接</el-button>
                                        </app-pick-link>
                                    </el-input>
                                </div>
                            </div>
                        </div>
                    </div>
                    <el-button size="small" @click="addBanner">添加轮播图</el-button>
                </el-form-item>
            </el-form>
        </div>
        <el-dialog title="轮播图" :visible.sync="dialogTableVisible">
            <el-table v-loading="listLoading" :data="bannerList" ref="multipleTable" @selection-change="handleSelectionChange">
                <el-table-column type="selection" width="55"></el-table-column>
                <el-table-column property="title" label="名称"></el-table-column>
                <el-table-column label="导航链接">
                    <template slot-scope="scope">
                        <app-ellipsis :line="1">{{scope.row.page_url}}</app-ellipsis>
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
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogTableVisible = false">取 消</el-button>
                <el-button type="primary" @click="updateBanner">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>
<script>
    Vue.component('diy-banner', {
        template: '#diy-banner',
        props: {
            value: Object,
        },
        data() {
            return {
                currentBannerIndex: null,
                data: {
                    style: 1,
                    fill: 1,
                    height: 450,
                    banners: [],
                },
                dialogTableVisible: false,
                page: 1,
                pageCount: 0,
                bannerList: [],
                listLoading: false,
                multipleSelection: [],
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)));
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {
            cContainerStyle() {
                return `height:${this.data.height}px;`;
            },
            cBannerImgClass() {
                if (this.data.fill == 0) {
                    return 'banner-img-contain';
                }
                if (this.data.fill == 1) {
                    return 'banner-img-cover';
                }
            },
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {
            addBanner() {
                this.data.banners.push({
                    picUrl: '',
                    url: '',
                    openType: '',
                });
            },
            bannerItemDelete(index) {
                this.data.banners.splice(index, 1);
            },
            pickLinkClick(index) {
                this.currentBannerIndex = index;
            },
            linkSelected(list) {
                if (!list.length) {
                    return;
                }
                const link = list[0];
                if (this.currentBannerIndex !== null) {
                    this.data.banners[this.currentBannerIndex].openType = link.open_type;
                    this.data.banners[this.currentBannerIndex].url = link.new_link_url;
                    this.data.banners[this.currentBannerIndex].params = link.params ? link.params : [];
                    this.currentBannerIndex = null;
                }
            },
            selectBanner() {
                this.dialogTableVisible = true;
                this.getBannerList();
            },
            getBannerList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'mall/mall-banner/index',
                        page: self.page,
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.bannerList = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getBannerList();
            },
            handleSelectionChange(val) {
                let arr = [];
                val.forEach(function (item, index) {
                    arr.push({
                        picUrl: item.pic_url,
                        openType: item.open_type,
                        url: item.page_url,
                        params: item.params
                    })
                });
                this.multipleSelection = arr;
            },
            updateBanner() {
                console.log(this.multipleSelection)
                let self = this;
                self.multipleSelection.forEach(function (item, index) {
                    self.data.banners.push(item)
                });
                self.dialogTableVisible = false;
            }
        }
    });
</script>
