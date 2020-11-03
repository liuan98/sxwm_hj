<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */
Yii::$app->loadViewComponent('app-poster');
Yii::$app->loadViewComponent('app-setting');
?>
<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
    }

    .form-body {
        padding: 10px 20px;
        background-color: #fff;
        margin-bottom: 20px;
    }

    .form-button {
        margin: 0 !important;
    }

    .form-button .el-form-item__content {
        margin-left: 0 !important;
    }

    .button-item {
        padding: 9px 25px;
    }

    .before {
        height: 100px;
        line-height: 100px;
        width: 100px;
        background-color: #f7f7f7;
        color: #bbbbbb;
        text-align: center;
    }

    .red {
        display:inline-block;
        padding:0 25px;
        color: #ff4544;
    }
</style>
<div id="app" v-cloak>
    <el-card v-loading="loading" style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;">
        <el-form :model="form" label-width="10rem" ref="form" :rules="rule">
            <el-tabs v-model="activeName">
                <el-tab-pane label="基础配置" name="first">
                    <div class="form-body">
                        <el-form-item prop="over_time">
                            <template slot='label'>
                                <span>未支付订单取消时间</span>
                                <el-tooltip effect="dark" content="注意：时间设置为0则表示不开启自动删除未支付订单功能"
                                            placement="top">
                                    <i class="el-icon-info"></i>
                                </el-tooltip>
                            </template>
                            <el-input style="width: 420px;" v-model="form.over_time" autocomplete="off">
                                <template slot="append">分</template>
                            </el-input>
                        </el-form-item>
                        <app-setting v-model="form" :is_territorial_limitation="false" :is_payment="false"
                                     :is_send_type="false"></app-setting>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="开放时间" name="second">
                    <div class="form-body">
                        <el-form-item label="开放时间">
                            <el-checkbox :indeterminate="isIndeterminate" v-model="checkAll"
                                         @change="handleCheckAllChange">
                                全选
                            </el-checkbox>
                            <div style="margin: 15px 0;"></div>
                            <el-checkbox-group v-model="form.open_time" @change="handleCheckedCitiesChange">
                                <div style="width: 120px; display: inline-block" v-for="option in options">
                                    <el-checkbox :label="option.value" :key="option.value">
                                        {{option.label}}
                                    </el-checkbox>
                                </div>
                            </el-checkbox-group>
                        </el-form-item>
                    </div>
                </el-tab-pane>
                <el-tab-pane label="自定义海报" name="three">
                    <div class="form-body" style="background:none;padding:0">
                        <app-poster :rule_form="form.goods_poster"
                                    :goods_component="goodsComponent"
                        ></app-poster>
                    </div>
                </el-tab-pane>
                <el-button :loading="btnLoading" class="button-item" type="primary" @click="store('form')" size="small"
                >保存
                </el-button>
            </el-tabs>
        </el-form>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                loading: false,
                btnLoading: false,
                form: {
                    open_time: [],
                },
                activeName: 'first',
                rule: {},
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
                    },
                    {
                        key: 'time_str',
                        icon_url: 'statics/img/mall/poster/icon_time.png',
                        title: '时间',
                        is_active: true
                    },
                ],
                checkAll: false,
                checkedCities: [],
                isIndeterminate: false,
            };
        },
        methods: {
            store(formName) {
                this.$refs[formName].validate(valid => {
                    if (valid) {
                        this.btnLoading = true;
                        request({
                            params: {
                                r: 'plugin/miaosha/mall/index/'
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
                        r: 'plugin/miaosha/mall/index'
                    },
                    method: 'get'
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0) {
                        this.form = e.data.data.detail;
                        if (this.form.open_time.length == this.options.length) {
                            this.checkAll = true;
                        } else if (this.form.open_time.length > 0 && this.form.open_time.length < this.options.length) {
                            this.isIndeterminate = true;
                        } else {
                            this.isIndeterminate = false;
                        }
                    }
                }).catch(e => {
                    this.loading = false;
                });
            },
            handleCheckAllChange(val) {
                let arr = [];
                this.options.forEach(function (item) {
                    arr.push(item.value)
                });
                this.form.open_time = val ? arr : [];
                this.isIndeterminate = false;
            },
            handleCheckedCitiesChange(value) {
                let checkedCount = value.length;
                this.checkAll = checkedCount === this.options.length;
                this.isIndeterminate = checkedCount > 0 && checkedCount < this.options.length;
            },
        },
        created() {
            this.loadData();
        },
        computed: {
            options() {
                let result = [];
                for (let i = 0; i < 24; i++) {
                    let h = i < 10 ? '0' + i : i;
                    result.push({
                        label: h + ':00~' + h + ':59',
                        value: '' + i
                    });
                }
                return result;
            }
        }
    });
</script>
