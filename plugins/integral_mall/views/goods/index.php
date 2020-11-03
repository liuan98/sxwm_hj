<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

Yii::$app->loadViewComponent('app-goods-list');
?>
<style>

</style>
<div id="app" v-cloak>

    <app-goods-list
            @get-all-checked="getAllChecked"
            ref="goodsList"
            goods_url="plugin/integral_mall/mall/goods/index"
            edit_goods_url='plugin/integral_mall/mall/goods/edit'
            :is-show-svip="false"
            :batch-list="batchList">

        <template slot="column-col">
            <el-table-column prop="integralMallGoods.integral_num" label="所需积分"></el-table-column>
            <el-table-column prop="confine_count" width="120" label="每人可兑换数">
                <template slot-scope="scope">
                    <div>{{scope.row.confine_count == -1 ? '不限':scope.row.confine_count}}</div>
                </template>
            </el-table-column>
            <el-table-column prop="is_sell_well" label="放置首页">
                <template slot-scope="scope">
                    <el-switch
                            :active-value="1"
                            :inactive-value="0"
                            @change="switchQuickShop(scope.row)"
                            v-model="scope.row.integralMallGoods.is_home">
                    </el-switch>
                </template>
            </el-table-column>
        </template>

        <template slot="batch" slot-scope="item">
            <div v-if="item.item === 'index'">
                <el-form-item label="是否放置首页">
                    <el-switch
                            @change="batch"
                            v-model="batchList[0].params.status"
                            :active-value="1"
                            :inactive-value="0">
                    </el-switch>
                </el-form-item>
            </div>
        </template>
    </app-goods-list>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {
                batchList: [
                    {
                        name: '放置首页',
                        key: 'index',
                        url: 'plugin/integral_mall/mall/goods/batch-update-index',
                        content: '批量移除放置首页,是否继续',
                        params: {
                            status: 0
                        }
                    },
                ],
                isAllChecked: false,
            };
        },
        methods: {
            // 放置首页
            switchQuickShop(row) {
                let self = this;
                request({
                    params: {
                        r: 'plugin/integral_mall/mall/goods/switch-sell-well',
                        id: row.id
                    },
                    method: 'get',
                }).then(e => {
                    if (e.data.code === 0) {
                        self.$message.success(e.data.msg);
                        self.$refs.goodsList.getList();
                    } else {
                        self.$message.error(e.data.msg);
                    }
                }).catch(e => {
                    console.log(e);
                });
            },
            batch() {
                let isAllChecked = this.isAllChecked;
                this.batchList[0].content = isAllChecked ? '警告: 批量设置所有商品' + (this.batchList[0].params.status ? '加入' : '移除') + '放置首页,是否继续' : '批量' + (this.batchList[0].params.status ? '加入' : '移除') + '放置首页,是否继续'
            },
            getAllChecked(isAllChecked) {
                this.batchList[0].content = isAllChecked ? '警告: 批量设置所有商品' + (this.batchList[0].params.status ? '加入' : '移除') + '放置首页,是否继续' : '批量' + (this.batchList[0].params.status ? '加入' : '移除') + '放置首页,是否继续';
                this.isAllChecked = isAllChecked;
            }
        }
    });
</script>
