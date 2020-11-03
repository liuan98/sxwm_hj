<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/11
 * Time: 14:04
 */
?>
<style>
    .form-body {
        padding: 20px;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
        min-width: 1000px
    }

    .form-button {
        margin: 0;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .el-dialog {
        min-width: 600px;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="loading">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'mall/index/rule'})">包邮规则</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>{{edit ? '编辑规则':'添加规则'}}</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <el-form :model="ruleForm" ref="ruleForm" :rules="rules" label-width="150px">
            <div class="form-body">
                <el-form-item label="包邮规则名称" prop="name" required>
                    <el-input size='small' v-model="ruleForm.name"></el-input>
                </el-form-item>
                <el-form-item label="包邮金额" prop="price" required>
                    <label slot="label">包邮金额
                        <el-tooltip class="item" effect="dark"
                                    content="订单满XXX包邮"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </label>
                    <el-input size='small' type="number" v-model.number="ruleForm.price"></el-input>
                </el-form-item>
                <el-form-item label="包邮地区" prop="detail" required style="margin: 0">
                    <el-card shadow="never" style="margin-bottom: 12px;" v-if="ruleForm.detail.length>0">
                        <div flex="dir:left box:last">
                            <div>
                                <div flex="dir:left" style="flex-wrap: wrap">
                                    <div>区域：</div>
                                    <el-tag style="margin:5px;border:0" type="info"
                                            v-for="(item, index) in ruleForm.detail" :key="item.id">
                                        {{item.name}}
                                    </el-tag>
                                </div>
                            </div>
                            <div style="text-align: right">
                                <el-button type="text" @click="openDistrict" size="small" circle>
                                    <el-tooltip class="item" effect="dark" content="编辑" placement="top">
                                        <img src="statics/img/mall/edit.png" alt="">
                                    </el-tooltip>
                                </el-button>
                                <el-button type="text" @click="space" size="small" circle>
                                    <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                        <img src="statics/img/mall/del.png" alt="">
                                    </el-tooltip>
                                </el-button>
                            </div>
                        </div>
                    </el-card>
                    <el-button size="small" type="text" @click="openDistrict" v-if="ruleForm.detail.length<=0"><i
                                class="el-icon-plus">新增规则</i>
                    </el-button>
                </el-form-item>
            </div>
            <el-form-item class="form-button">
                <el-button sizi="mini" class="button-item" :loading="submitLoading" type="primary"
                           @click="onSubmit('ruleForm')">
                    保存
                </el-button>
                <el-button sizi="mini" class="button-item" @click="goBack">
                    取消
                </el-button>
            </el-form-item>
        </el-form>
    </el-card>
    <el-dialog title="包邮地区选择" :visible.sync="dialogVisible" width="50%">
        <div style="margin-bottom: 1rem;">
            <app-district :detail="detail" @selected="selectDistrict" :level="3"
                          :edit="detail"></app-district>
            <div style="text-align: right;margin-top: 1rem;">
                <el-button type="primary" @click="districtConfirm">
                    确定选择
                </el-button>
            </div>
        </div>
    </el-dialog>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                edit: false,
                submitLoading: false,
                loading: false,
                dialogVisible: false,
                detail: [],
                ruleForm: {
                    price: 0,
                    name: '',
                    detail: []
                },
                rules: {
                    price: [
                        {type: 'number', message: '请输入数字', trigger: 'change', required: true},
                        {type: 'number', message: '请输入数字', trigger: 'blur', required: true},
                    ],
                    detail: [
                        {message: '请选择包邮地区', trigger: 'blur', required: true}
                    ],
                    name: [
                        {message: '请填写包邮规则名称', trigger: 'blur', required: true},
                        {message: '请填写包邮规则名称', trigger: 'change', required: true}
                    ]
                }
            };
        },
        mounted() {
            if (getQuery('id')) {
                this.getDetail(getQuery('id'));
                this.edit = true;
            }
        },
        methods: {
            space() {
                this.ruleForm.detail = [];
            },

            goBack() {
                window.history.go(-1);
            },

            selectDistrict(e) {
                let list = [];
                for (let i in e) {
                    let obj = {
                        id: e[i].id,
                        name: e[i].name
                    };
                    list.push(obj);
                }
                this.detail = list;
            },
            openDistrict() {
                this.dialogVisible = true;
                this.detail = this.ruleForm.detail;
            },
            districtConfirm() {
                this.ruleForm.detail = JSON.parse(JSON.stringify(this.detail));
                this.dialogVisible = false;
                this.$refs['ruleForm'].validate((valid) => {
                    return false;
                })
            },
            onSubmit(formName) {
                this.submitLoading = true;
                this.$refs[formName].validate((valid) => {
                    if (valid) {
                        request({
                            params: {
                                r: 'mall/free-delivery-rules/edit',
                                id: getQuery('id')
                            },
                            method: 'post',
                            data: {
                                form: this.ruleForm
                            }
                        }).then(e => {
                            this.submitLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'mall/index/rule',
                                    tab: 'third'
                                });
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            this.submitLoading = false;
                            this.$message.error(e.data.msg);
                        });
                    } else {
                        console.log('error submit!!');
                        this.submitLoading = false;
                        return false;
                    }
                })
            },
            getDetail(id) {
                this.loading = true;
                request({
                    params: {
                        r: 'mall/free-delivery-rules/edit',
                        id: id
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.ruleForm = Object.assign(this.ruleForm, e.data.data.model);
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    this.loading = false;
                });
            }
        }
    });
</script>
