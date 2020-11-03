<?php
/**
 * link: http://www.67930603.top/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: Alpha
 */

?>
<div id="app" v-cloak>
    <el-button @click="getUser">AAA</el-button>
</div>
<script>
const app = new Vue({
    el: '#app',
    data() {
        return {
            user: {}
        };
    },
    methods: {
        getUser() {
            this.$alert('aaa');
        },
    },
});
</script>
