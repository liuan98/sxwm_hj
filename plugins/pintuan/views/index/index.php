<?php defined('YII_ENV') or exit('Access Denied');
Yii::$app->loadViewComponent('app-poster');
Yii::$app->loadViewComponent('app-setting');
?>
<style>
    .info-title {
        margin-left: 20px;
        color: #ff4544;
    }

    .info-title span {
        color: #3399ff;
        cursor: pointer;
        font-size: 13px;
    }

    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 10px;
    }

    .form-body {
        padding: 10px 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .button-item {
        margin-top: 12px;
        padding: 9px 25px;
    }

    .red {
        padding: 0 25px;
        color: #ff4544;
    }
</style>
<section id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;"
             v-loading="loading">
        <div class="text item" style="width:100%">
            <el-form :model="form" label-width="150px" :rules="rule" ref="form">
                <el-tabs v-model="activeName">
                    <el-tab-pane label="基本设置" class="form-body" name="first">
                        <app-setting v-model="form"></app-setting>
                        <el-form-item label="拼团规则(多条)">
                            <el-table
                                    style="margin-bottom: 15px;"
                                    v-if="form.rules.length > 0"
                                    :data="form.rules"
                                    border
                                    style="width: 100%">
                                <el-table-column
                                        label="标题"
                                        width="180">
                                    <template slot-scope="scope">
                                        <el-input v-model="scope.row.title" placeholder="请输入标题"></el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        label="内容">
                                    <template slot-scope="scope">
                                        <el-input type="textarea"
                                                  v-model="scope.row.content"
                                                  placeholder="请输入内容">
                                        </el-input>
                                    </template>
                                </el-table-column>
                                <el-table-column
                                        label="操作">
                                    <template slot-scope="scope">
                                        <el-button size="small" @click="destroyRules(scope.$index)" type="text" circle>
                                            <el-tooltip class="item" effect="dark" content="删除" placement="top">
                                                <img src="statics/img/mall/del.png" alt="">
                                            </el-tooltip>
                                        </el-button>
                                    </template>
                                </el-table-column>
                            </el-table>
                            <el-button type="text" @click="addRules">
                                <i class="el-icon-plus" style="font-weight: bolder;margin-left: 5px;"></i>
                                <span style="color: #353535;font-size: 14px">新增拼团规则</span>
                            </el-button>
                        </el-form-item>

                    </el-tab-pane>
                    <el-tab-pane label="自定义海报" class="form-body" style="background:none;padding:0" name="second">
                        <app-poster :rule_form="form.goods_poster"
                                    :goods_component="goodsComponent"
                        ></app-poster>
                    </el-tab-pane>
                </el-tabs>
                <el-button class="button-item" :loading="btnLoading" type="primary" @click="store('form')" size="small">
                    保存
                </el-button>
            </el-form>
        </div>
    </el-card>
</section>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                form: {
                    is_share: 0,
                    is_sms: 0,
                    is_mail: 0,
                    is_print: 0,
                    is_territorial_limitation: 0,
                    rules: [],
                    send_type: ['express', 'offline'],
                    payment_type: ['online_pay'],
                },
                rule: {},
                is_show: false,
                activeName: 'first',
                goodsComponent: [
                    {
                        key: 'head',
                        icon_url: 'statics/img/mall/poster/icon_head.png',
                        title: '头像',
                        is_active: true
                    },
                    {
                        key: 'nickname',
                        icon_url: 'statics/img/mall/poster/icon_nickname.png',
                        title: '昵称',
                        is_active: true
                    },
                    {
                        key: 'pic',
                        icon_url: 'statics/img/mall/poster/icon_pic.png',
                        title: '商品图片',
                        is_active: true
                    },
                    {
                        key: 'name',
                        icon_url: 'statics/img/mall/poster/icon_name.png',
                        title: '商品名称',
                        is_active: true
                    },
                    {
                        key: 'price',
                        icon_url: 'statics/img/mall/poster/icon_price.png',
                        title: '商品价格',
                        is_active: true
                    },
                    {
                        key: 'desc',
                        icon_url: 'statics/img/mall/poster/icon_desc.png',
                        title: '海报描述',
                        is_active: true
                    },
                    {
                        key: 'qr_code',
                        icon_url: 'statics/img/mall/poster/icon_qr_code.png',
                        title: '二维码',
                        is_active: true
                    },
                    {
                        key: 'poster_bg',
                        icon_url: 'statics/img/mall/poster/icon-mark.png',
                        title: '标识',
                        is_active: true
                    }
                ],
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/pintuan/mall/index/'
                            },
                            method: 'post',
                            data: this.form
                        }).then(e => {
                            this.btnLoading = false;
                            if (e.data.code == 0) {
                                this.$message.success(e.data.msg);
                            } else {
                                this.$message.error(e.data.msg);
                            }
                        });
                    } else {
                        this.btnLoading = false;
                        console.log('error submit!!');
                        return false;
                    }
                })
            },
            loadData() {
                this.loading = true;
                request({
                    params: {
                        r: 'plugin/pintuan/mall/index'
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.form = e.data.data.detail;
                        this.is_show = true;
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            // 添加权益
            addRules() {
                this.form.rules = this.form.rules ? this.form.rules : [];
                this.form.rules.push({
                    title: '',
                    content: '',
                })
            },
            // 删除权益
            destroyRules(index) {
                this.form.rules.splice(index, 1);
            }
        },

        created() {
            this.loadData();
        },
    })
</script>