<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */
?>

<style>
    .app-send .title-box {
        margin: 15px 0;
    }

    .app-send .title-box .text {
        background-color: #FEFAEF;
        color: #E6A23C;
        padding: 6px;
    }

    .app-send .get-print {
        width: 100%;
        height: 100%;
    }

    .app-send .el-table__header-wrapper th {
        background-color: #f5f7fa;
    }

    .app-send .el-dialog__body {
        padding: 5px 20px 10px;
    }
</style>

<template id="app-send">
    <div class="app-send">
        <!-- 发货 -->
        <el-dialog title="发货" :visible.sync="dialogVisible" width="35%" @close="closeDialog">
            <div class="title-box">
                <span class="text">选择发货商品</span>
                <span>(默认全选)</span></div>
            <el-table
                    ref="multipleTable"
                    :data="orderDetail"
                    tooltip-effect="dark"
                    style="width: 100%"
                    max-height="250"
                    @selection-change="handleSelectionChange">
                <el-table-column
                        type="selection"
                        :selectable="selectInit"
                        width="55">
                </el-table-column>
                <el-table-column
                        label="图片"
                        width="60">
                    <template slot-scope="scope">
                        <app-image width="30" height="30" :src="scope.row.goods_info.goods_attr.cover_pic"></app-image>
                    </template>
                </el-table-column>
                <el-table-column
                        label="名称"
                        show-overflow-tooltip>
                    <template slot-scope="scope">
                        <el-tag v-if="scope.row.expressRelation" type="success" size="mini">已发货</el-tag>
                        <span>{{scope.row.goods_info.goods_attr.name}}</span>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="goods_info.goods_attr.number"
                        label="数量"
                        width="80"
                        show-overflow-tooltip>
                </el-table-column>
                <el-table-column
                        label="规格"
                        width="120"
                        show-overflow-tooltip>
                    <template slot-scope="scope">
                        <span v-for="attrItem in scope.row.goods_info.attr_list">
                            {{attrItem.attr_group_name}}:{{attrItem.attr_name}}
                        </span>
                    </template>
                </el-table-column>
            </el-table>
            <div class="title-box">
                <span class="text">物流信息</span>
            </div>
            <el-form label-width="130px"
                     class="sendForm"
                     :model="express"
                     :rules="rules"
                     ref="sendForm">
                <el-form-item label="物流选择">
                    <el-radio @change="resetForm('sendForm')" v-model="express.is_express" label="1">快递</el-radio>
                    <el-radio @change="resetForm('sendForm')" v-model="express.is_express" label="2">其它方式</el-radio>
                </el-form-item>
                <el-form-item label="快递公司" prop="express" v-if="express.is_express == 1">
                    <el-autocomplete
                            size="small"
                            v-model="express.express"
                            @select="getCustomer"
                            :fetch-suggestions="querySearch"
                            placeholder="请选择快递公司"
                    ></el-autocomplete>
                </el-form-item>
                <el-form-item label="收件人邮编" v-if="express.is_express == 1">
                    <el-input type="number" placeholder="请输入收件人邮编" size="small" v-model="express.code"
                              autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="商家编码" prop="customer_name"
                              v-if="express.is_express == 1 && (express.express === '京东物流' || express.express === '京东快运')">

                    <el-input placeholder="请输入商家编码" size="small" v-model="express.customer_name"
                              autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="快递单号" prop="express_no" class="express-no" v-if="express.is_express == 1">
                    <el-input placeholder="请输入快递单号" size="small" v-model.trim="express.express_no"
                              autocomplete="off">
                        <template v-if="isShowPrint" slot="append">
                            <div flex="main:center" style="width: 100px">
                                <el-button :loading="submitLoading" size="small" type="text" class="get-print"
                                           @click="getPrint(express)">获取面单
                                </el-button>
                            </div>
                        </template>
                    </el-input>
                </el-form-item>
                <!--                售后发货应该用插槽的方式-->
                <el-form-item v-if="express.is_express == 1 && isRefund" prop="merchant_remark" label="商家留言">
                    <el-input type="textarea" size="small" v-model="express.merchant_remark"
                              autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item v-if="express.is_express == 1 && !isRefund" label="商家留言（选填）">
                    <el-input type="textarea" size="small" v-model="express.merchant_remark"
                              autocomplete="off">
                    </el-input>
                </el-form-item>
                <el-form-item v-if="express.is_express == 2" prop="express_content" label="物流内容">
                    <el-input type="textarea" size="small" v-model="express.express_content"
                              autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item style="text-align: right">
                    <el-button size="small" @click="dialogVisible=false">取 消</el-button>
                    <!--                售后发货应该用插槽的方式-->
                    <el-button v-if="isRefund" size="small" type="primary" :loading="sendLoading"
                               @click="refundSend(express,'sendForm')">
                        确定
                    </el-button>
                    <el-button v-else size="small" type="primary" :loading="sendLoading"
                               @click="send_order(express,'sendForm')">
                        确定
                    </el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-send', {
        template: '#app-send',
        props: {
            isShow: {
                type: Boolean,
                default: false,
            },
            order: {
                type: Object,
                default: function () {
                    return {}
                }
            },
            sendType: {
                type: String,
                default: '',
            },
            isRefund: {
                type: Boolean,
                default: false,
            },
            isShowPrint: {
                type: Boolean,
                default: true,
            },
            expressId: {
                type: Number,
                default: 0,
            },
        },
        watch: {
            isShow: function (newVal) {
                if (newVal) {
                    this.openExpress()
                    this.getExpressData();
                    this.getExpressId();
                } else {
                    this.dialogVisible = false;
                }
            },
        },
        data() {
            return {
                dialogVisible: false,
                express: {},
                send_type: null,
                mhc_id: 0,
                sendLoading: false,
                submitLoading: false,
                rules: {
                    express: [
                        {required: true, message: '快递公司不能为空', trigger: 'change'},
                    ],
                    express_no: [
                        {required: true, message: '快递单号不能为空', trigger: 'change'},
                        {pattern: /^[0-9a-zA-Z]+$/, message: '仅支持数字与英文字母'}
                    ],
                    merchant_remark: [
                        {required: true, message: '商家留言不能为空', trigger: 'change'},
                    ],
                    customer_name: [
                        {required: true, message: '商家编码不能为空', trigger: 'change'},
                    ],
                    express_content: [
                        {required: true, message: '物流内容不能为空', trigger: 'change'},
                    ]
                },
                express_list: [],
                multipleSelection: [],
                orderDetail: [],
                expressSingle: {},
            }
        },
        methods: {
            // 打开发货框
            openExpress() {
                let self = this;
                self.getExpress();
                self.dialogVisible = true;
                self.send_type = self.sendType;
                if (self.send_type === 'change') {
                    self.order.detailExpress.forEach(function (item) {
                        if (item.id == self.expressId) {
                            self.express = {
                                is_express: item.send_type,
                                order_id: self.order.id,
                                express: item.express,
                                code: self.order.code,
                                express_no: item.express_no,
                                words: self.order.words,
                                customer_name: item.customer_name,
                                mch_id: self.order.mch_id,
                                merchant_remark: item.merchant_remark,
                                express_content: item.express_content,
                                express_id: self.expressId,
                            };
                        }
                    })
                } else {
                    self.express = {
                        is_express: '1',
                        order_id: self.order.id,
                        express_no: '',
                        mch_id: self.order.mch_id,
                        express_content: '',
                    };
                }
            },
            getExpressData() {
                let self = this;
                self.orderDetail = self.order.detail;
                // 默认全选
                self.orderDetail.forEach(row => {
                    if (!row.expressRelation) {
                        setTimeout(() => {
                            self.$refs.multipleTable.toggleRowSelection(row, true);
                        }, 1)
                    }
                });
            },
            getExpressId: function (newVal) {
                let self = this;
                if (self.expressId > 0) {
                    self.orderDetail = [];
                    self.order.detailExpress.forEach(function (item) {
                        if (item.id == self.expressId) {
                            item.expressRelation.forEach(function (item2) {
                                self.orderDetail.push(item2.orderDetail)
                            })
                        }
                    })

                    // 默认全选
                    self.orderDetail.forEach(row => {
                        if (row.expressRelation) {
                            setTimeout(() => {
                                self.$refs.multipleTable.toggleRowSelection(row);
                            }, 1)
                        }
                    });
                }
            },
            closeDialog() {
                this.$emit('close')
            },
            // 发货
            send_order(e, formName) {
                let self = this;
                let res = self.getOrderDetailId();
                if (res.length <= 0) {
                    this.$message.error('请选择发货商品');
                    this.closeDialog()
                    return false;
                }
                e.order_detail_id = res;
                self.$refs[formName].validate((valid) => {
                    if (valid) {
                        self.sendLoading = true;
                        if (self.express.is_express == 1) {
                            // 电子面单ID
                            e.express_single_id = self.expressSingle.id;
                            request({
                                params: {
                                    r: 'mall/order/send',
                                },
                                data: e,
                                method: 'post',
                            }).then(e => {
                                self.sendLoading = false;
                                if (e.data.code === 0) {
                                    self.dialogVisible = false;
                                    self.$emit('submit');
                                    if (self.send_type == "send") {
                                        self.$message({
                                            message: e.data.msg,
                                            type: 'success'
                                        });
                                    } else if (self.send_type == "change") {
                                        self.$message({
                                            message: '修改成功',
                                            type: 'success'
                                        });
                                    }
                                } else {
                                    self.$message.error(e.data.msg);
                                }
                            }).catch(e => {
                                self.sendLoading = false;
                            });
                        } else {
                            request({
                                params: {
                                    r: 'mall/order/send',
                                },
                                data: e,
                                method: 'post',
                            }).then(e => {
                                self.sendLoading = false;
                                if (e.data.code === 0) {
                                    self.dialogVisible = false;
                                    self.$emit('submit');
                                    if (self.send_type == "send") {
                                        self.$message({
                                            message: e.data.msg,
                                            type: 'success'
                                        });
                                    } else if (self.send_type == "change") {
                                        self.$message({
                                            message: '修改成功',
                                            type: 'success'
                                        });
                                    }
                                } else {
                                    self.$message.error(e.data.msg);
                                }
                            }).catch(e => {
                                self.sendLoading = false;
                            });
                        }
                    }
                });
            },
            getOrderDetailId() {
                // 选中的订单商品
                let orderDetailId = [];
                this.multipleSelection.forEach(function (item) {
                    orderDetailId.push(item.id)
                });
                return orderDetailId;
            },
            // 搜索建议
            querySearch(queryString, cb) {
                var express_list = this.express_list;
                var results = queryString ? express_list.filter(this.createFilter(queryString)) : express_list;
                cb(results);
            },
            createFilter(queryString) {
                return (express_list) => {
                    return (express_list.value.toLowerCase().indexOf(queryString.toLowerCase()) === 0);
                };
            },
            getCustomer() {
                let express = this.express.express;
                if (express !== '京东物流' && express !== '京东快运') {
                    this.express.customer_name = '';
                    return;
                }
                request({
                    params: {
                        _mall_id: <?php echo \Yii::$app->mall->id; ?>,
                        r: 'api/express/get-customer',
                        keyword: express
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        if (e.data.data.customer_account) {
                            let info = JSON.parse(JSON.stringify(this.express));
                            info.customer_name = e.data.data.customer_account;
                            this.express = info;
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            getExpress() {
                request({
                    params: {
                        r: 'mall/express/express-list'
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.express_list = e.data.data.list;
                        for (let i = 0; i < this.express_list.length; i++) {
                            this.express_list[i].value = this.express_list[i].name
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            // 获取面单
            getPrint(e) {
                this.submitLoading = true;
                request({
                    params: {
                        r: 'mall/order/print',
                        order_id: e.order_id,
                        express: e.express,
                        zip_code: e.code,
                        customer_name: e.customer_name,
                    },
                    method: 'get',
                }).then(e => {
                    e.visible = false;
                    this.submitLoading = false;
                    if (e.data.code == 0) {
                        this.$message({
                            message: '获取成功',
                            type: 'success'
                        });
                        this.express.express_no = e.data.data.Order.LogisticCode;
                        this.expressSingle = e.data.data.express_single;
                    } else {
                        this.$message({
                            message: e.data.msg,
                            type: 'warning'
                        });
                    }
                }).catch(e => {
                });
            },
            handleSelectionChange(val) {
                this.multipleSelection = val;
            },
            selectInit(row, index) {
                if (row.expressRelation) {
                    return false;
                } else {
                    return true;
                }
            },
            resetForm(formName) {
                this.$refs[formName].clearValidate();
            },
            // 售后发货方法 start
            refundSend(row, formName) {
                let res = this.getOrderDetailId();
                if (res.length <= 0) {
                    this.$message.error('请选择发货商品');
                    return false;
                }
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        let para = row;
                        para.order_refund_id = this.order.id;
                        para.type = 2;
                        para.is_agree = '1';
                        para.refund = '2';
                        this.para = para;
                        this.refundOver();
                    }
                });
            },
            refundOver() {
                if (this.para.type == 2) {
                    this.sendLoading = true;
                    let para = this.para;
                    request({
                        params: {
                            r: 'mall/order/refund-handle',
                        },
                        data: para,
                        method: 'post'
                    }).then(e => {
                        this.sendLoading = false;
                        if (e.data.code === 0) {
                            this.refundConfirmVisible = false;
                            this.$emit('submit');
                            this.$message({
                                message: e.data.msg,
                                type: 'success'
                            });
                        } else {
                            this.$message({
                                message: e.data.msg,
                                type: 'warning'
                            });
                        }
                    }).catch(e => {
                    });
                }
            },
            // 售后发货方法 end
        },
        created() {
        },
    })
</script>