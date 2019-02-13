<template>
    <div class="app-container">
        <div class="filter-container">

            <!--工具条-->
            <el-row :span="8" class="top_toolbar">
                <!--条件搜索-->
                <el-col :span="6">
                    <el-form :inline="true" :model="filters" style="display: inline-block;padding: 0;">
                        <el-form-item>
                            <el-input v-model="filters.name" placeholder="管理员名字" style="width: 120px"></el-input>
                        </el-form-item>
                        <el-form-item style="margin-left: 10px">
                            <el-button type="primary" v-on:click="getTable">查询</el-button>
                        </el-form-item>
                    </el-form>
                </el-col>
                <el-col :span="6" :offset="12">
                    <router-link :to="'/table/ReChargeEwm/1'">
                        <el-button type="primary" size="medium">充值</el-button>
                    </router-link>
                </el-col>
            </el-row>


            <!--列表-->
            <el-table :data="table_list" max-height="640" highlight-current-row  v-loading="tableLoading" border
                      style="width: 100%;" @selection-change="handleSelectionChange">
                <el-table-column align="center" type="index" label="序号" width="66"></el-table-column>
                <el-table-column align="center" prop="username" label="管理员名字"></el-table-column>
                <el-table-column align="center" prop="callmoney" label="余额"></el-table-column>
                <!--<el-table-column align="center" label="操作" width="120" fixed="right">-->
                    <!--<template slot-scope="scope">-->
                        <!--<el-button size="small" @click="recharge()">充值</el-button>-->
                    <!--</template>-->
                <!--</el-table-column>-->
            </el-table>
            <!--页码条-->
            <el-col :span="24" class="toolbar" style="margin-top: 10px">
                <!--<el-pagination layout="prev, pager, next" @current-change="handleCurrentChange" :page-size="pagesize"-->
                               <!--:total="total" style="float:right;"></el-pagination>-->
            </el-col>
        </div>
    </div>
</template>

<script>
    import { getToken, setToken, removeToken } from '@/utils/auth'
    import { ywuserlist,creckpc,mobilefp } from '@/api/Number'

    export default {
        name: "Recharge",
        data(){
            return{
                filters: {name: ''},
                table_list: [],
                tableLoading: false,
                // total: 0,
                // page: 1,
                // pagesize: 10,
                filters: {name: ''},
                multipleSelection:[],
                token:"",
            }
        },
        created(){
            this.table_id = this.$route.params && this.$route.params.id
            this.token = getToken()
            this.getTable();
        },
        methods:{
            // 点击页码
            // handleCurrentChange(val) {
            //     this.page = val;
            //     this.getTable();
            // },
            // 请求表格数据
            getTable() {
                var _this = this;
                var params = {
                    token:_this.token,
                    // s: _this.page,
                    // p: _this.pagesize,
                    name: _this.filters.name
                };
                this.tableLoading = true;
                console.log(params);

                ywuserlist(params).then(response => {
                    console.log(response);
                    const res = response.data;
                    console.log(res)
                    if (res.code == '0000') {
                        this.table_list = res.data.rows;
                        // this.total = res.data.total;
                    } else {
                        this.$message.error(res.msg);
                    }
                    this.tableLoading = false
                }).catch(err => {
                    console.log(err);
                    this.tableLoading = false;
                    this.$message.error("网络错误");
                });
            },
        },
    }
</script>

<style scoped>
    .edit-input {
        padding-right: 100px;
    }
    .cancel-btn {
        position: absolute;
        right: 15px;
        top: 10px;
    }
</style>