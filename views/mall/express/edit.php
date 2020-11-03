<?php defined('YII_ENV') or exit('Access Denied'); ?>
<style>
    .form-body {
        padding: 20px 0;
        background-color: #fff;
        margin-bottom: 20px;
        padding-right: 50%;
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
</style>
<section id="app" v-cloak>
    <el-card shadow="never" style="border:0" class="box-card" body-style="background-color: #f3f3f3;padding: 10px 0 0;"
             v-loading="listLoading">
        <div slot="header">
            <span></span>
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer"
                                          @click="$navigate({r:'mall/express/index'})">电子面单打印</span></el-breadcrumb-item>
                <el-breadcrumb-item>电子面单打印编辑</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <div class="text item">
            <el-form :model="form" label-width="150px" :rules="FormRules" ref="form">
                <div class="form-body">
                    <el-form-item label="选择快递公司" prop="express_id">
                        <el-select @change="selectExpress" size="small" v-model="form.express_id" filterable
                                   placeholder="请选择">
                            <el-option v-for="item in express" :key="item.name" :label="item.name"
                                       :value="item.id"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item prop="is_sms">
                        <template slot='label'>
                            <span>是否订阅短信</span>
                            <el-tooltip effect="dark" content="快递鸟付费用户专享功能"
                                    placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </template>
                        <el-switch v-model="form.is_sms" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>
                    <el-form-item prop="is_goods">
                        <template slot='label'>
                            <span>是否打印商品</span>
                            <el-tooltip effect="dark" content="打印商品信息"
                                        placement="top">
                                <i class="el-icon-info"></i>
                            </el-tooltip>
                        </template>
                        <el-switch v-model="form.is_goods" :active-value="1" :inactive-value="0"></el-switch>
                    </el-form-item>
                    <el-form-item label="电子面单客户账号" prop="customer_account">
                        <el-input size="small" v-model="form.customer_account" placeholder="与快递网点申请"
                                  autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="电子面单密码" prop="customer_pwd">
                        <el-input size="small" v-model="form.customer_pwd" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="月结编码" prop="month_code">
                        <el-input size="small" v-model="form.month_code" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="网点编码" prop="outlets_code">
                        <el-input size="small" v-model="form.outlets_code" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="网点名称" prop="outlets_name">
                        <el-input size="small" v-model="form.outlets_name" autocomplete="off"></el-input>
                    </el-form-item>

                    <el-form-item v-if="template_size_list[code]" label="电子面单模板规格" prop="template_size">
                        <el-select size="small" v-model="form.template_size" placeholder="请选择">
                            <el-option v-for="item in template_size_list[code]" :key="item.name" :label="item.name"
                                       :value="item.value"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="发件人公司" prop="company">
                        <el-input size="small" v-model="form.company" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="发件人名称" prop="name">
                        <el-input size="small" v-model="form.name" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="发件人电话" prop="tel">
                        <el-input size="small" type="number" v-model="form.tel" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="发件人手机" prop="mobile">
                        <el-input size="small" type="number" v-model="form.mobile" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="发件人邮编" prop="zip_code">
                        <el-input size="small" v-model="form.zip_code" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="发件人地区" prop="default">
                        <el-cascader size="small" @change="handleChange" v-model="form.default" placeholder="请选择地区"
                                     :options="district" filterable></el-cascader>
                    </el-form-item>
                    <el-form-item label="发件人详细地址" prop="address">
                        <el-input size="small" v-model="form.address" placeholder="请填写详细地址"
                                  autocomplete="off"></el-input>
                    </el-form-item>
                </div>
                <el-form-item class="form-button">
                    <el-button type="primary" class="button-item" :loading="btnLoading" @click="onSubmit">提交</el-button>
                    <el-button sizi="mini" class="button-item" @click="Cancel">取消</el-button>
                </el-form-item>
            </el-form>
        </div>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                code: '',
                form: {
                    is_sms: 0,
                    is_goods: 0
                },
                express: [],
                district: [],
                template_size_list: [],
                listLoading: false,
                btnLoading: false,
                FormRules: {
                    express_id: [
                        {required: true, message: '快递公司不能为空', trigger: 'blur'},
                    ],
                    name: [
                        {required: true, message: '发件人名称不能为空', trigger: 'blur'},
                    ],
                    address: [
                        {required: true, message: '发件人详细地址不能为空', trigger: 'blur'},
                    ],
                    default: [
                        {required: true, message: '发件人地区不能为空', trigger: 'blur'},
                    ],
                    mobile: [
                        {required: false, pattern: /^1\d{10}$/, message: '发件人格式不正确'},
                    ],
                    tel: [
                        {required: true, message: '发件人电话不能为空', trigger: 'change'},
                    ],
                },
            };
        },
        methods: {
            selectExpress(e) {
                this.form.template_size = '';
                this.getTemplateSize(e);
            },
            getTemplateSize(e) {
                const self = this;
                self.express.map(v => {
                    if (v.id === e) {
                        this.code = v.code;
                    }
                });
            },
            handleChange(row) {
                this.form.province = row[0];
                this.form.city = row[1];
                this.form.district = row[2];
            },

            onSubmit() {
                this.$refs.form.validate((valid) => {
                    if (valid) {
                        this.btnLoading = true;
                        let para = Object.assign({}, this.form);
                        request({
                            params: {
                                r: 'mall/express/edit',
                            },
                            data: para,
                            method: 'post'
                        }).then(e => {
                            if (e.data.code === 0) {
                                navigateTo({r: 'mall/express/index'});
                            } else {
                                this.$message.error(e.data.msg);
                            }
                            this.btnLoading = false;
                        }).catch(e => {
                            this.btnLoading = false;
                        });
                    }
                });
            },

            Cancel() {
                window.history.go(-1)
            },

            getList() {
                this.listLoading = true;
                request({
                    params: {
                        r: 'mall/express/edit',
                        id: getQuery('id'),
                    },
                }).then(e => {
                    if (e.data.code == 0) {
                        if (e.data.data) {
                            //默认值
                            if(e.data.data.list.template_size === undefined) {
                                e.data.data.list.template_size = '';
                            }
                            this.form = e.data.data.list;
                            this.express = e.data.data.express;
                            this.getTemplateSize(this.form.express_id);
                            this.district = e.data.data.district;
                            this.template_size_list = e.data.data.template_size_list;
                        }
                    }
                    this.listLoading = false;
                }).catch(e => {
                    this.listLoading = false;
                });
            },
        },

        mounted() {
            this.getList();
        }
    })
</script>
