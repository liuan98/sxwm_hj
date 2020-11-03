<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/4/26
 * Time: 10:26
 */
Yii::$app->loadViewComponent('diy/diy-bg');
?>
<style>
    .diy-nav .nav-container {
        min-height: 100px;
        width: 100%;
        overflow-x: auto;
    }

    .diy-nav .nav-item {
        text-align: center;
        font-size: 24px;
        padding: 20px 0;
    }

    .diy-nav .nav-item > div {
        height: 25px;
        line-height: 25px;
    }

    .diy-nav .nav-item img {
        display: block;
        width: 88px;
        height: 88px;
        margin: 0 auto 5px auto;
    }

    .diy-nav .edit-nav-item {
        border: 1px solid #e2e2e2;
        line-height: normal;
        padding: 5px;
        margin-bottom: 5px;
    }

    .diy-nav .nav-icon-upload {
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

    .diy-nav .nav-edit-options {
        position: relative;
    }

    .diy-nav .nav-edit-options .el-button {
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

    .diy-nav .about-text {
        color: #909399;
        font-size: 12px;
        margin-top: -10px;
    }
</style>
<template id="diy-nav">
    <div class="diy-nav">
        <div class="diy-component-preview">
            <div class="nav-container" :style="cContainerStyle">
                <div :style="cStyle" flex="dir:left">
                    <div v-for="(navGroup,groupIndex) in cNavGroups" flex="dir:left"
                         style="width: 750px;flex-wrap:wrap;">
                        <div v-for="(nav,navIndex) in navGroup" :style="cNavStyle" class="nav-item">
                            <img :src="nav.icon">
                            <div :style="'color:'+data.color+';'">{{nav.name}}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px">
                <el-form-item label="每页行数">
                    <el-input size="small" v-model.number="data.rows" type="number" min="1" max="100"></el-input>
                </el-form-item>
                <el-form-item label="每行个数">
                    <app-radio v-model="data.columns" :label="3">3</app-radio>
                    <app-radio v-model="data.columns" :label="4">4</app-radio>
                    <app-radio v-model="data.columns" :label="5">5</app-radio>
                </el-form-item>
                <el-form-item label="左右滑动">
                    <el-switch v-model="data.scroll"></el-switch>
                </el-form-item>
                <el-form-item label="拉取商城导航">
                    <el-button size="small" @click="selectNav">选择</el-button>
                </el-form-item>
                <el-form-item label="导航图标">
                    <div v-for="(nav,index) in data.navs" class="edit-nav-item">
                        <div class="nav-edit-options">
                            <el-button @click="navItemDelete(index)"
                                       type="primary"
                                       icon="el-icon-delete"
                                       style="top: -6px;right: -31px;"></el-button>
                        </div>
                        <div flex="dir:left box:first cross:center">
                            <div>
                                <app-image-upload style="margin-right: 5px;" v-model="nav.icon" width="88"
                                                  height="88"></app-image-upload>
                            </div>
                            <div>
                                <el-input v-model="nav.name" placeholder="名称" size="small"
                                          style="margin-bottom: 5px"></el-input>
                                <div @click="pickLinkClick(index)">
                                    <el-input v-model="nav.url" placeholder="点击选择链接" readonly
                                                  size="small">
                                        <app-pick-link slot="append" @selected="linkSelected">
                                            <el-button size="small">选择链接</el-button>
                                        </app-pick-link>
                                    </el-input>
                                </div>
                            </div>
                        </div>
                    </div>
                    <el-button size="small" @click="addNav">添加图标</el-button>
                </el-form-item>
                <diy-bg :data="data" :background="showBackImg" :hr="!showBackImg" @update="updateData" @toggle="toggleData" @change="changeData">
                    <template slot="about">
                        <div class="about-text">当前组件高度为{{ Math.ceil(+data.navs.length / data.columns) > data.rows ?+data.rows*156 : Math.ceil(+data.navs.length / data.columns)*156}}px,宽750px</div>
                    </template>
                </diy-bg>
                <el-form-item label="文字颜色">
                    <el-color-picker size="small" v-model="data.color"></el-color-picker>
                    <el-input size="small" style="width: 80px;margin-right: 25px;" v-model="data.color"></el-input>
                </el-form-item>
            </el-form>
            <el-dialog title="导航链接" :visible.sync="dialogTableVisible">
                <el-table v-loading="listLoading" :data="navList" ref="multipleTable" @selection-change="handleSelectionChange">
                    <el-table-column type="selection" width="55"></el-table-column>
                    <el-table-column property="name" label="导航名称"></el-table-column>
                    <el-table-column label="导航链接">
                        <template slot-scope="scope">
                            <app-ellipsis :line="1">{{scope.row.url}}</app-ellipsis>
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
                    <el-button type="primary" @click="updateNav">确 定</el-button>
                </div>
            </el-dialog>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-nav', {
        template: '#diy-nav',
        props: {
            value: Object
        },
        data() {
            return {
                currentEditNavIndex: null,
                data: {
                    // background: '#ffffff',
                    color: '#353535',
                    rows: 1,
                    columns: 3,
                    scroll: true,
                    navs: [],
                    showImg: false,
                    backgroundColor: '#ffffff',
                    backgroundPicUrl: '',
                    position: 5,
                    mode: 1,
                    backgroundHeight: 100,
                    backgroundWidth: 100,
                },
                position: 'center center',
                repeat: 'no-repeat',
                dialogTableVisible: false,
                page: 1,
                pageCount: 0,
                navList: [],
                listLoading: false,
                showBackImg: true,
                multipleSelection: [],
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', this.data)
            } else {
                this.data = this.value;
            }
        },
        computed: {
            cContainerStyle() {
                return `background-color:${this.data.backgroundColor};overflow-x:${this.data.scroll ? 'auto' : 'hidden'};background-image:url(${this.data.backgroundPicUrl});background-size:${this.data.backgroundWidth}% ${this.data.backgroundHeight}%;background-repeat:${this.repeat};background-position:${this.position}`;
            },
            cStyle() {
                let width = (this.cNavGroups.length ? this.cNavGroups.length : 1) * 750;
                return `width:${width}px;`;
            },
            cNavGroups() {
                const navGroups = [];
                const groupNavCount = this.data.rows * this.data.columns;
                for (let i in this.data.navs) {
                    const groupIndex = parseInt(i / groupNavCount);
                    if (!navGroups[groupIndex]) {
                        navGroups[groupIndex] = [];
                    }
                    navGroups[groupIndex].push(this.data.navs[i]);
                }
                return navGroups;
            },
            cNavStyle() {
                return `width:${100 / this.data.columns}%;`;
            },
        },
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal);
                },
            }
        },
        methods: {
            updateData(e) {
                this.data = e;
            },
            toggleData(e) {
                this.position = e;
            },
            changeData(e) {
                this.repeat = e;
            },
            addNav() {
                this.data.navs.push({
                    icon: '',
                    name: '',
                    url: '',
                    openType: '',
                });
            },
            navItemDelete(index) {
                this.data.navs.splice(index, 1);
            },
            linkSelected(list, params) {
                if (!list.length) {
                    return;
                }
                const link = list[0];
                if (this.currentEditNavIndex !== null) {
                    this.data.navs[this.currentEditNavIndex].openType = link.open_type;
                    this.data.navs[this.currentEditNavIndex].url = link.new_link_url;
                    this.data.navs[this.currentEditNavIndex].params = link.params;
                    this.currentEditNavIndex = null;
                }
            },
            pickLinkClick(index) {
                this.currentEditNavIndex = index;
            },
            selectNav() {
                this.dialogTableVisible = true;
                this.getNavList();

            },
            getNavList() {
                let self = this;
                self.listLoading = true;
                request({
                    params: {
                        r: 'mall/home-nav/index',
                        page: self.page,
                        limit: 10
                    },
                    method: 'get',
                }).then(e => {
                    self.listLoading = false;
                    self.navList = e.data.data.list;
                    self.pageCount = e.data.data.pagination.page_count;
                }).catch(e => {
                    console.log(e);
                });
            },
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getNavList();
            },
            handleSelectionChange(val) {
                let arr = [];
                val.forEach(function (item, index) {
                    arr.push({
                        icon: item.icon_url,
                        name: item.name,
                        openType: item.open_type,
                        url: item.url,
                    })
                });
                this.multipleSelection = arr;
            },
            updateNav() {
                console.log(this.multipleSelection)
                let self = this;
                self.multipleSelection.forEach(function (item, index) {
                    self.data.navs.push(item)
                });
                self.dialogTableVisible = false;
            }
        }
    });
</script>