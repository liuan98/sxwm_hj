<?php
/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2019/5/5
 * Time: 17:19
 */
?>
<template id="diy-ad">
    <div>
        <div class="diy-component-preview">
            <div style="padding: 50px 0;text-align: center;background: #fff;">这是一个流量主广告位</div>
        </div>
        <div class="diy-component-edit">
            <el-form label-width="100px" @submit.native.prevent>
                <el-form-item>
                    <el-alert style="line-height: normal;" :closable="false"
                              type="warning" title="流量主广告需要申请开通流量主功能。"></el-alert>
                </el-form-item>
                <el-form-item label="广告位ID">
                    <el-input size="small" v-model="data.id"></el-input>
                </el-form-item>
            </el-form>
        </div>
    </div>
</template>
<script>
    Vue.component('diy-ad', {
        template: '#diy-ad',
        props: {
            value: Object,
        },
        data() {
            return {
                data: {
                    id: '',
                }
            };
        },
        created() {
            if (!this.value) {
                this.$emit('input', JSON.parse(JSON.stringify(this.data)))
            } else {
                this.data = JSON.parse(JSON.stringify(this.value));
            }
        },
        computed: {},
        watch: {
            data: {
                deep: true,
                handler(newVal, oldVal) {
                    this.$emit('input', newVal, oldVal)
                },
            }
        },
        methods: {}
    });
</script>
