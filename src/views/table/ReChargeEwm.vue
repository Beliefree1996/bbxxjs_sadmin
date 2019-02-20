<template>
    <div class="app-container">
        <div class="filter-container">
            <div class="View">
                <img :src="getImgSrc" style="width: 450px;margin: 0 auto;display: block" alt="充值二维码" >
            </div>
        </div>
        <!--备注信息-->
        <div style="display: flex">
            <span style="white-space: nowrap">备注：</span><el-input
                :autosize="{ minRows: 2, maxRows: 5}" v-model="remarks" placeholder="默认均等分配至各坐席" type="textarea" style="display: inline-block"></el-input>
        </div>
        <el-row>
            <br>
        </el-row>
        <el-button type="primary" @click="submit">提交</el-button>
    </div>
</template>

<script>
    import { getToken, setToken, removeToken } from '@/utils/auth'
    import { userinfo, setremarks } from '@/api/Number'

    export default{
        name:'ReChargeEwm',
        props:{

        },
        data(){
            return {
                remarks: "",
                img_name:'0',
            }
        },
        created(){
            this.GetSid();
        },
        methods:{
            GetSid() {
                var params = {
                    token: getToken(),
                }
                userinfo(params).then(response => {
                    const res = response.data;
                    console.log(res);
                    if (res.code == '0000') {
                        this.img_name = res.data.id;
                        console.log(res.id);
                    } else {
                        this.$message.error(res.msg);
                    }
                }).catch(err => {
                    console.log(err);
                    this.$message.error("网络错误");
                });
            },
            submit() {
                var params = {
                    token: getToken(),
                    remarks: this.remarks,
                }
                setremarks(params).then(response => {
                    const res = response.data;
                    console.log(res);
                    if (res.code == '0000') {
                        this.$message({
                            type: 'success',
                            message: '备注提交成功!'
                        });
                    } else {
                        this.$message.error(res.msg);
                    }
                }).catch(err => {
                    console.log(err);
                    this.$message.error("网络错误");
                });
            },
        },
        computed:{
            getImgSrc(){
                return require('@/assets/ewmimg/'+this.img_name+'.jpg')
            }
        }
    }
</script>

<style scoped>
    .filter-container {
        margin: 30px auto;
        border: 1px solid #EEEEEE;
        border-radius: 5px;
        overflow: hidden;
        /*min-width: 500px;*/
    }
    .filter-container .View {
        width: 100%;
        margin-right: 10px;
        height: 100%;
        flex-shrink: 0;
    }
</style>