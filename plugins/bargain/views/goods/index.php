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
            ref="goodsList"
            :is-show-svip="false"
            goods_url="plugin/bargain/mall/goods/index"
            edit_goods_url='plugin/bargain/mall/goods/edit'>
    </app-goods-list>
</div>
<script>
    const app = new Vue({
        el: '#app',
        data() {
            return {

            };
        },
        methods: {
        }
    });
</script>
