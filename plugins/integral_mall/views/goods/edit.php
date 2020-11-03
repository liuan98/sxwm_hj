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
<div id="app" v-cloak>
    <app-goods
            ref="appGoods"
            :is_member="0"
            :is_attr="1"
            :is_show="0"
            :is_info="0"
            :is_detail="0"
            :form="form"
            url="plugin/integral_mall/mall/goods/edit"
            :preview-info="previewInfo"
            @handle-preview="handlePreview"
            get_goods_url="plugin/integral_mall/mall/goods/edit"
            referrer="plugin/integral_mall/mall/goods/index">
        <template slot="before_attr">
            <el-card shadow="never" class="mt-24">
                <div slot="header">
                    <span>显示设置</span>
                </div>
                <el-row>
                    <el-col :xl="12" :lg="16">
                        <el-form-item label="放置首页" prop="is_home">
                            <el-tooltip class="item" effect="dark" content="开启后,商品会在积分商城首页展示"
                                        placement="top">
                                <el-switch
                                        :active-value="1"
                                        :inactive-value="0"
                                        v-model="form.is_home">
                                </el-switch>
                            </el-tooltip>
                        </el-form-item>
                    </el-col>
                </el-row>
            </el-card>
        </template>

        <template slot="preview">
            <div v-if="previewData" flex="dir:top">
                <div class="goods">
                    <div class="goods-name">{{previewData.name}}</div>
                    <div flex="dir:left" style="font-size:14px">
                        <div flex="dir:left" style="font-size: 18px;height:22px;color:#ff4544;margin-top:15px">
                            <div flex="dir:top">
                                <div style="font-size:15px;margin-bottom:3px">
                                    {{previewData.integral_num}}积分+{{previewData.price}}元
                                </div>
                                <div style="font-size: 8px;text-decoration: line-through;color:#888">
                                    ￥{{previewData.original_price}}
                                </div>
                            </div>
                        </div>
                        <div class="share" flex="dir:top main:center cross:center">
                            <el-image src="statics/img/mall/goods/icon-share.png"></el-image>
                            <div>分享</div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </app-goods>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                previewData: null,
                previewInfo: {
                    is_head: false,
                    is_cart: false,
                },

                form: {
                    extra: {
                        integral_num: '积分'
                    },
                    is_home: 1,
                },
            };
        },
        created() {
            let id = getQuery('id');
            if (id) {
                this.form.id = getQuery('id');
            }
        },
        methods: {
            handlePreview(e) {
                this.previewData = e;
            },
            // 监听子组件事件
            childrenGoods(e) {
                let self = this;
                if (getQuery('id')) {
                    self.form.extra = {
                        integral_num: '积分',
                    };
                    self.form.is_home = e.integralMallGoods.is_home
                }
            },
        }
    });
</script>
