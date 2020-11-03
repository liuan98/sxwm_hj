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
    .el-textarea__inner {
        padding: 5px 15px;
    }
    .sortable-chosen {
        border:0px solid #3399ff !important;
    }
</style>
<div id="app" v-cloak>
    <el-card shadow="never" style="border:0" body-style="background-color: #f3f3f3;padding: 10px 0 0;">
        <div slot="header">
            <el-breadcrumb separator="/">
                <el-breadcrumb-item><span style="color: #409EFF;cursor: pointer" @click="$navigate({r:'plugin/booking/mall/goods/index'})">商品管理</span></el-breadcrumb-item>
                <el-breadcrumb-item v-if="form.goods_id > 0">详情</el-breadcrumb-item>
                <el-breadcrumb-item v-else>添加商品</el-breadcrumb-item>
            </el-breadcrumb>
        </div>
        <app-goods ref="appGoods"
                   sign="booking"
                   :is_attr="1"
                   :is_show="0"
                   :is_form="0"
                   :form="form"
                   :rule="rule"
                   :preview-info="previewInfo"
                   @handle-preview="handlePreview"
                   url="plugin/booking/mall/goods/edit"
                   get_goods_url="plugin/booking/mall/goods/edit"
                   referrer="plugin/booking/mall/goods/index">
            <template slot="before_marketing">
                <el-card shadow="never" style="margin-top: 24px">
                    <div slot="header">门店选择</div>
                    <el-form-item label="门店选择" prop="store">
                        <el-select v-model="chooseStore" @change="showStore" multiple filterable placeholder="请选择">
                            <el-option v-for="item in store" :key="item.id" :label="item.name" :value="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-card>
            </template>

            <template slot="before_marketing">
                <el-card shadow="never" style="margin-top: 24px">
                    <div slot="header">自定义表单</div>
                    <el-form-item label="表单设置">
                        <app-form v-if="formSwitch" :is_date_range="true" :is_time_range="true" :value.sync="form.form_data"></app-form>
                    </el-form-item>
                </el-card>
            </template>
            <template slot="preview_end">
                <div v-if="previewData" flex="dir:top">
                    <el-image style="margin-top:12px;height:161px"
                              src="<?= \app\helpers\PluginHelper::getPluginBaseAssetsUrl() ?>/img/store.png"></el-image>
                </div>
            </template>
        </app-goods>
    </el-card>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                tags: [],
                rule: {
                    store: [
                        {required: true, message: '请选择门店列表', trigger: 'change'},
                    ],
                },
                selectedOptions: [],
                formSwitch: false,
                is_add: 1,
                form: {
                    form_data: [],
                    store: [],
                },
                cats: [],
                attrGroups: [],
                store: [],
                chooseStore: [],
                previewData: null,
                previewInfo: {},
            };
        },
        mounted() {
            let id = getQuery('id');
            if (id) {
                this.is_add = 0;
                this.getDetail(id);
            } else {
                this.formSwitch = true;
            }
            this.clerkUser();
        },
        methods: {
            handlePreview(e) {
                this.previewData = e;
            },
            clerkUser() {
                request({
                    params: {
                        r: 'plugin/booking/mall/goods/store-search',
                        keyword: this.keyword,
                    },
                }).then(e => {
                    if (e.data.code === 0) {
                        this.store = e.data.data;
                    } else {
                        this.$message.error(e.data.msg);
                    }
                }).catch(e => {});
            },
            showStore(row) {
                this.chooseStore = row;
                this.form.store = [];
                row.forEach(idx=>{
                    for(let index in this.store) {
                        if(this.store[index].id == idx) {
                            this.form.store.push(this.store[index])
                        }
                    }
                })
            },

            /////////
            getDetail(id) {
                this.cardLoading = true;
                request({
                    params: {
                        r: 'plugin/booking/mall/goods/edit',
                        id: id
                    },
                    method: 'get'
                }).then(e => {
                    this.cardLoading = false;
                    if (e.data.code == 0) {
                        let plugin = e.data.data.detail.plugin;
                        this.form.id = getQuery('id');
                        this.form.form_data = plugin.form_data? plugin.form_data : [];
                        this.form.store = plugin.store ? plugin.store : [];
                        if(this.form.store) {
                            for(let i = 0;i < this.form.store.length;i++) {
                                this.chooseStore.push(this.form.store[i].id)
                            }
                        }
                    } else {
                        this.$message.error(e.data.msg);
                    }
                    this.formSwitch = true;
                }).catch(e => {
                    this.cardLoading = false;
                });
            }
        }
    });
</script>
