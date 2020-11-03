<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */
?>

<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
    }

    .form-button {
        margin: 0!important;
    }

    .form-button .el-form-item__content {
        margin-left: 0!important;
    }

    .button-item {
        padding: 9px 25px;
    } 
</style>
<div id="app" v-cloak>
    <el-card class="box-card" shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;" v-loading="cardLoading">
        <div slot="header">
            <div>
                <span></span>
            </div>
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'mall/card/index'})">卡券</span></el-breadcrumb-item>
                <el-breadcrumb-item>卡券编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="form-body">
            <el-form :model="ruleForm" :rules="rules" size="small" ref="ruleForm" label-width="120px">
                <el-form-item label="卡券名称" prop="name">
                    <el-input v-model="ruleForm.name" placeholder="请输入卡券名称"></el-input>
                </el-form-item>
                <el-form-item label="卡券图标" prop="pic_url">
                    <app-attachment :multiple="false" :max="1" @selected="picUrl">
                        <el-tooltip class="item" effect="dark" content="建议尺寸88*88" placement="top">
                            <el-button size="mini">选择文件</el-button>
                        </el-tooltip>
                    </app-attachment>
                    <app-image width="80px" height="80px" mode="aspectFill" :src="ruleForm.pic_url"></app-image>
                </el-form-item>
                <el-form-item label="卡券有效期" prop="expire_type">
                    <el-radio v-model="ruleForm.expire_type" :label="1">领取后N天内有效</el-radio>
                    <el-radio v-model="ruleForm.expire_type" :label="2">时间段</el-radio>
                </el-form-item>
                <el-form-item label="有效天数" v-if="ruleForm.expire_type == 1" prop="expire_day">
                    <el-input size="small" onkeyup="value=value.replace(/^(0+)|[^\d]+/g,'')" type="number" v-model="ruleForm.expire_day" autocomplete="off"></el-input>
                </el-form-item>
                <el-form-item label="有效期范围" v-if="ruleForm.expire_type == 2" prop="time">
                    <el-date-picker
                            size='small'
                            v-model="ruleForm.time"
                            value-format="yyyy-MM-dd"
                            type="daterange"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期"
                    >
                    </el-date-picker>
                </el-form-item>
                <el-form-item label="可发放数量" prop="total_count">
                    <label slot="label" style="padding-right: 12px">可发放数量
                        <el-tooltip class="item" effect="dark"
                                    content="卡券可发放数量，-1表示不限制发放数量"
                                    placement="top">
                            <i class="el-icon-info"></i>
                        </el-tooltip>
                    </label>
                    <el-input placeholder="请输入卡券可发放数量" type="number" :min="-1"
                              :disabled="ruleForm.total_count == -1"
                              v-model.number="ruleForm.total_count"></el-input>
                    <el-checkbox v-model="ruleForm.total_count" :true-label="-1" :false-label="0">无限制</el-checkbox>
                </el-form-item>
                <el-form-item label="卡券描述" prop="description">
                    <el-input
                            type="textarea"
                            :rows="4"
                            placeholder="请输入卡券描述"
                            v-model="ruleForm.description">
                    </el-input>
                </el-form-item>
            </el-form>
        </div>
        <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('ruleForm')" size="small">保存</el-button>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            var validDate = (rule, value, callback) => {
                if (!value) {
                    return callback(new Error('有效天数不能为空'));
                }
                setTimeout(() => {
                    if (value < 0.01) {
                        callback(new Error('有效天数需大于零'));
                    } else {
                        callback();
                    }
                }, 0);
            };
            return {
                ruleForm: {
                    pic_url: '',
                    description: '',
                    expire_type: 1,
                    expire_day: '',
                    begin_time: '',
                    end_time: '',
                    time: [],
                    total_count: -1
                },
                rules: {
                    name: [
                        {required: true, message: '请输入卡券名称', trigger: 'change'},
                    ],
                    description: [
                        {required: true, message: '请输入卡券描述', trigger: 'change'},
                    ],
                    pic_url: [
                        {required: true, message: '请选择卡券图标', trigger: 'change'},
                    ],
                    expire_day: [
                        {validator: validDate, trigger: 'change'},
                    ],
                    time: [
                        {required: true, message: '请选择有效时间范围', trigger: 'change'},
                    ],
                },
                btnLoading: false,
                cardLoading: false,
            };
        },
        methods: {
            store(formName) {
                if (this.ruleForm.expire_type == 1) {
                    this.ruleForm.time = ['0000-00-00', '0000-00-00']
                } else {
                    this.ruleForm.expire_day = 0
                }
                this.$refs[formName].validate((valid) => {
                    let self = this;
                    if (valid) {
                        self.btnLoading = true;
                        request({
                            params: {
                                r: 'mall/card/edit'
                            },
                            method: 'post',
                            data: {
                                form: self.ruleForm,
                            }
                        }).then(e => {
                            self.btnLoading = false;
                            if (e.data.code == 0) {
                                self.$message.success(e.data.msg);
                                navigateTo({
                                    r: 'mall/card/index'
                                })
                            } else {
                                self.$message.error(e.data.msg);
                            }
                        }).catch(e => {
                            self.$message.error(e.data.msg);
                            self.btnLoading = false;
                        });
                    } else {
                        console.log('error submit!!');
                        return false;
                    }
                });
            },
            getDetail() {
                let self = this;
                self.cardLoading = true;
                request({
                    params: {
                        r: 'mall/card/edit',
                        id: getQuery('id')
                    },
                    method: 'get',
                }).then(e => {
                    self.cardLoading = false;
                    if (e.data.code == 0) {
                        self.ruleForm = e.data.data.detail;
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            picUrl(e) {
                if (e.length) {
                    this.ruleForm.pic_url = e[0].url;
                    this.$refs.ruleForm.validateField('pic_url');
                }
            },
        },
        mounted: function () {
            if (getQuery('id')) {
                this.getDetail();
            }
        }
    });
</script>
