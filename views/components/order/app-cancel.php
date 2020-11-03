<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */
?>

<style>

</style>

<template id="app-cancel">
    <div class="app-cancel">
        <!-- 备注 -->
        <el-dialog :title="title" :visible.sync="dialogVisible" width="30%" @close="closeDialog">
            <el-form>
                <el-form-item :label="content">
                    <el-input type="textarea" v-model="remark" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item style="text-align: right">
                    <el-button size="small" @click="dialogVisible = false">取消</el-button>
                    <el-button size="small" type="primary" :loading="submitLoading" @click="toSumbit">确定
                    </el-button>
                </el-form-item>
            </el-form>
        </el-dialog>
    </div>
</template>

<script>
    Vue.component('app-cancel', {
        template: '#app-cancel',
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
            cancelType: {
                type: Number,
                default: -1,
            }
        },
        watch: {
            isShow: function (newVal) {
                if (newVal) {
                    this.openDialog()
                }
            }
        },
        data() {
            return {
                dialogVisible: false,
                status: '',
                content: '',
                title: '',
                remark: '',
                submitLoading: false,
            }
        },
        methods: {
            openDialog() {
                // 申请取消的判断
                this.dialogVisible = true;
                if (this.cancelType == 1) {
                    this.status = 1;
                    this.title = '同意取消';
                    this.content = '填写同意理由：';
                } else if (this.cancelType == 0) {
                    this.status = 2;
                    this.title = '拒绝取消';
                    this.content = '填写拒绝理由：';
                } else if (this.cancelType == 2) {
                    this.status = 1;
                    this.title = '订单取消';
                    this.content = '填写取消理由：';
                }
            },
            closeDialog() {
                this.$emit('close')
            },
            toSumbit() {
                this.submitLoading = true;
                request({
                    params: {
                        r: 'mall/order/cancel'
                    },
                    data: {
                        order_id: this.order.id,
                        remark: this.remark,
                        status: this.status,
                    },
                    method: 'post'
                }).then(e => {
                    this.submitLoading = false;
                    if (e.data.code === 0) {
                        this.dialogVisible = false;
                        this.$message.success(e.data.msg);
                        this.$emit('submit')
                    } else {
                        this.$message.error(e.data.msg);
                    }

                }).catch(e => {
                });
            }
        }
    })
</script>