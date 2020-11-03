<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

Yii::$app->loadViewComponent('app-comment-reply');
?>
<div id="app" v-cloak>
    <app-comment-reply navigate_url='plugin/booking/mall/comment'></app-comment-reply>
</div>
<script>
const app = new Vue({
    el: '#app',
    mounted() {
        if (getQuery('id')) {}
    }
});
</script>