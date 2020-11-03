<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */
?>

<style>
    .app-select-card .input-item {
        display: inline-block;
        width: 250px;
    }

    .app-select-card .input-item .el-input__inner {
        border-right: 0;
    }

    .app-select-card .input-item .el-input__inner:hover {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-select-card .input-item .el-input__inner:focus {
        border: 1px solid #dcdfe6;
        border-right: 0;
        outline: 0;
    }

    .app-select-card .input-item .el-input-group__append {
        background-color: #fff;
        border-left: 0;
        width: 10%;
        padding: 0;
    }

    .app-select-card .input-item .el-input-group__append .el-button {
        padding: 0;
    }

    .app-select-card .input-item .el-input-group__append .el-button {
        margin: 0;
    }

    .app-select-card .el-dialog__body {
        padding: 10px 20px;
    }

    .app-select-card .card-list {
        margin-top: 20px;
    }

    .app-select-card .card-list .card-list-item {
        margin: 5px 0;
        width: 100%;
    }

    .app-select-card .pagination-box {
        margin-top: 20px;
    }
</style>

<template id="app-select-card">
    <div class="app-select-card">
        <el-dialog
                @close="dialogClose"
                title="选择卡券"
                :visible.sync="card.dialog"
                width="30%">
            <div class="input-item">
                <el-input @keyup.enter.native="searchCards" size="small" placeholder="请输入卡券名称"
                          v-model="keyword" clearable
                          @clear="searchCards">
                    <el-button slot="append" icon="el-icon-search" @click="searchCards"></el-button>
                </el-input>
            </div>
            <div class="card-list" v-loading="card.loading" flex="dir:top">
                <div class="card-list-item"
                     flex="dir:left box:last cross:center"
                     v-for="(item, index) in card.list">
                    <app-ellipsis :line="1">
                        <el-checkbox style="width: 360px;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;vertical-align: top" v-model="item.checked" :key="item.id">
                            {{item.name}}
                        </el-checkbox>
                    </app-ellipsis>
                    <div>
                        <el-input-number type="number" size="small" v-model="item.num" :min="1" :max="100"
                                         label="输入数量"></el-input-number>
                    </div>
                </div>
            </div>
            <div class="pagination-box" flex="dir:right">
                <el-pagination
                        @current-change="pagination"
                        background
                        :current-page="currentPage"
                        layout="prev, pager, next"
                        :page-count="pageCount">
                </el-pagination>
            </div>
            <div slot="footer" class="dialog-footer">
                <el-button size="small" @click="card.dialog = false">取 消</el-button>
                <el-button size="small" type="primary" @click="cardConfirm">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-select-card', {
        template: '#app-select-card',
        props: {
            isShow: {
                type: Boolean,
                default: false
            },
            ruleForm: {
                type: Object,
                default: function () {
                    return {};
                }
            }
        },
        watch: {
            isShow: function (newVal) {
                if (newVal) {
                    this.openCardDialog();
                }
            }
        },
        data() {
            return {
                keyword: '',
                card: {
                    dialog: false,
                    list: [],
                    selected: [],
                    loading: false
                },
                pageCount: 0,
                currentPage: 1,
                page: 1,
            }
        },
        methods: {
            pagination(currentPage) {
                let self = this;
                self.page = currentPage;
                self.getCards();
            },
            searchCards() {
                this.page = 1;
                this.getCards();
            },
            // 获取卡券列表
            getCards() {
                let self = this;
                this.card.loading = true;
                request({
                    params: {
                        r: 'mall/card/index',
                        keyword: this.keyword,
                        page: self.page,
                    },
                    method: 'get',
                }).then(e => {
                    this.card.loading = false;
                    if (e.data.code === 0) {
                        let cards = e.data.data.list;
                        self.pageCount = e.data.data.pagination.page_count;
                        self.currentPage = e.data.data.pagination.current_page;
                        for (let i in cards) {
                            cards[i].checked = false;
                            cards[i].num = 1;
                        }
                        self.card.list = cards;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            openCardDialog() {
                this.getCards();
                this.card.dialog = this.isShow;
            },
            cardConfirm() {
                let self = this;
                self.card.selected = self.ruleForm.cards;
                self.card.list.forEach(function (item, index) {
                    let newNum = parseInt(item.num)
                    if (item.checked && newNum >= 1) {
                        if (self.card.selected.length > 0) {
                            let sign = true;
                            self.card.selected.forEach(function (item2, index2) {
                                if (item.id === item2.id) {
                                    self.card.selected[index2].num = item2.num + newNum
                                    sign = false;
                                }
                            })
                            if (sign) {
                                self.card.selected.push({
                                    id: item.id,
                                    name: item.name,
                                    num: newNum
                                })
                            }
                        } else {
                            self.card.selected.push({
                                id: item.id,
                                name: item.name,
                                num: newNum
                            })
                        }
                    }
                });
                console.log(this.card.selected)
                this.card.dialog = false;
                this.$emit('select', this.card.selected);
            },
            dialogClose() {
                this.$parent.$parent.cardDialogVisible = false;
            },
        },
        created() {

        }
    })
</script>