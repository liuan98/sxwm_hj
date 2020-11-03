<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

Yii::$app->loadViewComponent('app-comment-edit');
?>
<div id="app" v-cloak>
    <app-comment-edit sign="booking" navigate_url='plugin/booking/mall/comment'></app-comment-edit>
</div>
<script>
const app = new Vue({
    el: '#app',
    mounted() {
        if (getQuery('id')) {

        }
    }
});
</script>