<?php
/**
 * Created by PhpStorm.
 * User: 风哀伤
 * Date: 2019/3/7
 * Time: 11:46
 * @copyright: (c)2019 天幕网络
 * @link: http://www.67930603.top
 */
Yii::$app->loadViewComponent('app-goods');
?>
<style>
    .card-body {
        padding: 20px;
        background-color: #fff;
    }

    .mt-24 {
        margin-bottom: 24px;
    }

    .el-form-item__label {
        padding: 0 20px 0 0;
        width: 180px;
    }

    .button > .el-button {
        margin-left: 150px;
    }

    .jieti .el-form-item__content {
        margin: 0 !important;
    }

    .el-scrollbar .el-scrollbar__wrap .el-scrollbar__view {
        white-space: nowrap;
        padding-top: 2px;
    }
</style>
<div id="app" v-cloak>
    <el-card body-style="background-color: #f3f3f3;padding: 10px 0 0;min-width: 900px;">
        <div slot="header" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item @click.native="returnBack">
                    <a>商品管理</a></el-breadcrumb-item>
                <el-breadcrumb-item>{{edit ? '修改商品' : '添加商品'}}</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <app-goods
            sign="gift"
            ref="appGoods"
            :is_member="1"
            :is_attr="1"
            :is_show="0"
            :is_share="1"
            :is_info="0"
            :is_detail="0"
            :form="form"
            url="plugin/gift/mall/goods/edit"
            get_goods_url="plugin/gift/mall/goods/edit"
            referrer="plugin/gift/mall/goods/index"
            @change="changeGoods"
            :preview-info="previewInfo"
            @handle-preview="handlePreview"
            @goods-success="childrenGoods">
        </app-goods>
    </el-card>
</div>

<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                previewData: null,
                previewInfo: {
                    is_head: true,
                    is_services: true,
                },

                form: {
                },
                edit: false,
            };
        },
        created() {
            let id = getQuery('id');
            if (id) {
                this.form.id = getQuery('id');
            }
            const index = window.location.search.indexOf('&');
            if (index !== -1) {
                let url = window.location.search.substring(index + 1);
                if (url.split('&')[0].split('=')[0] === 'id') {
                    this.edit = true;
                }
            }
        },
        mounted() {

        },
        methods: {
            handlePreview(e) {
            },
            changeGoods(data) {
            },
            // 监听子组件事件
            childrenGoods(e) {
                let self = this;
                // if (getQuery('id')) {
                //     self.form.open_date = [];
                //     self.form.open_date.push(e.advanceGoods.start_prepayment_at, e.advanceGoods.end_prepayment_at);
                //     self.form.pay_limit = e.advanceGoods.pay_limit;
                //     self.form.ladder_rules = e.ladder_rules;
                // }
            },

            returnBack() {
                this.$historyGo(-1);
            },
        },
        computed: {
            attr: function () {
            }
        }
    });
</script>
