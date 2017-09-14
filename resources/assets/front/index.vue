<template>
    <div>
      <br>
        <Row v-if="isManager">
            <Col span="22" push="1">
                <router-link to="/f/attend">
                    <Button size="large" long>店铺考勤录入</Button>
                </router-link>
            </Col>
        </Row>
        <br>
        <Row v-if="isManager">
            <Col span="22" push="1">
                <router-link  to="/f/attendrecord">
                    <Button size="large" long>店铺考勤记录</Button>
                </router-link>
            </Col>
        </Row>
        <br>
        <Row>
            <Col span="22" push="1">
                <router-link  to="/f/sign">
                    <Button size="large" long>个人考勤</Button>
                </router-link>
            </Col>
        </Row>
        <br>
        <Row>
            <Col span="22" push="1">
                <router-link  to="/f/askforleave">
                    <Button size="large" long>请假审批</Button>
                </router-link>
            </Col>
        </Row>
        <br>
        <Row>
            <Col span="22" push="1">
                <router-link  to="/f/transrecord">
                    <Button size="large" long>调动记录</Button>
                </router-link>
            </Col>
        </Row>
    </div>
</template>

<script>
    export default{
        data(){
            return {}
        },
        props:['currentUser'],
        computed:{
            inShop:function(){
                if(this.currentUser){
                    return this.currentUser.shop_sn.length > 0;
                }else{
                    return false;
                }
            },
            isManager:function(){
                if(this.currentUser && this.inShop){
                    let managerSn = this.currentUser.shop.manager_sn;
                    let staffSn = this.currentUser.staff_sn;
                    return staffSn == managerSn;
                }else{
                    return false;
                }
            }
        }
    }

</script>
