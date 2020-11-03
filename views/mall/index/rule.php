<?php
/**
 * @copyright ©2019 浙江禾匠信息科技
 * Created by PhpStorm.
 * User: Andy - Wangjie
 * Date: 2019/10/22
 * Time: 13:55
 */
Yii::$app->loadViewComponent('app-kdniao');
Yii::$app->loadViewComponent('app-postage-rule');
Yii::$app->loadViewComponent('app-free-delivery-rules');
Yii::$app->loadViewComponent('app-offer-price');
?>

<style>
    .el-tabs__header {
        padding: 0 20px;
        height: 56px;
        line-height: 56px;
        background-color: #fff;
        margin-bottom: 0;
    }

    .title {
        margin-top: 10px;
        padding: 18px 20px;
        border-top: 1px solid #F3F3F3;
        border-bottom: 1px solid #F3F3F3;
        background-color: #fff;
    }

    .form-body {
        background-color: #fff;
        padding: 20px 50% 20px 0;
    }
</style>
<div id="app" v-cloak>
    <el-card style="border:0" shadow="never" body-style="background-color: #f3f3f3;padding: 0 0;" v-loading="loading">
        <el-tabs v-model="activeName" @tab-click="handleClick">
            <el-tab-pane label="快递鸟设置" name="first" v-if="isShow">
                <el-row>
                    <el-col :span="24">
                        <app-kdniao></app-kdniao>
                    </el-col>
                </el-row>
            </el-tab-pane>
            <el-tab-pane label="运费规则" name="second">
                <el-row>
                    <el-col :span="24">
                        <app-postage-rule></app-postage-rule>
                    </el-col>
                </el-row>
            </el-tab-pane>
            <el-tab-pane label="包邮规则" name="third">
                <el-row>
                    <el-col :span="24">
                        <app-free-delivery-rules></app-free-delivery-rules>
                    </el-col>
                </el-row>
            </el-tab-pane>
            <el-tab-pane label="起送规则" name="fourth">
                <el-row>
                    <el-col :span="24">
                        <app-offer-price></app-offer-price>
                    </el-col>
                </el-row>
            </el-tab-pane>
        </el-tabs>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                activeName: getQuery('tab') ? getQuery('tab') :'first',
                isShow: true,
                loading: false,
            };
        },
        methods: {
            handleClick(tab, event) {
                console.log(tab, event);
            },

            getRole() {
                let self = this;
                self.loading = true;
                request({
                    params: {
                        r: 'mall/index/role'
                    },
                    method: 'get',
                }).then(e => {
                    this.loading = false;
                    if (e.data.code == 0 && e.data.data == 'mch') {
                        self.isShow = false;
                        self.activeName = 'second';
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
        },
        mounted: function () {
            this.getRole();
        }
    });
</script>


