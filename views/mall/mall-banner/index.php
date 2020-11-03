<?php defined('YII_ENV') or exit('Access Denied');
/**
 * @copyright (c)天幕网络
 * @author xay
 * @link http://www.67930603.top/
 */
?>
<div id="app" v-cloak>
    <app-banner url="mall/mall-banner/index" submit_url="mall/mall-banner/edit"></app-banner>
</div>
<script>
const app = new Vue({
    el: '#app'
})
</script>