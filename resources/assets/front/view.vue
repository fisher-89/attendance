<template>
	<div>
		<div>
			<router-view :currentUser="currentUser"></router-view>
			<div style="width:100%;height:60px;"></div>
		</div>
		<mt-tabbar v-model="tabbar" :fixed="true" style="z-index: 10;">
			<mt-tab-item id="/f/sign">
				<router-link to="/f/sign">
					<Row>
						<Icon type="clock" size="30"/>
					</Row>
					<Row>
						考勤
					</Row>
				</router-link>
			</mt-tab-item>
			<mt-tab-item id="/f/transrecord">
				<router-link to="/f/transrecord">
					<Row>
						<Icon type="arrow-right-a" size="30"/>
					</Row>
					<Row>
						调动
					</Row>
				</router-link>
			</mt-tab-item>
			<mt-tab-item id="/f/askforleave">
				<router-link to="/f/askforleave">
					<Row>
						<Icon type="coffee" size="30"/>
					</Row>
					<Row>
						请假
					</Row>
				</router-link>
			</mt-tab-item>
			<mt-tab-item v-if="currentUser.is_manager" id="/f/attend">
				<router-link to="/f/attend">
					<Row>
						<Icon type="person" size="30"/>
					</Row>
					<Row>
						店长
					</Row>
				</router-link>
			</mt-tab-item>
		</mt-tabbar>
	</div>
</template>

<style>
	.mint-tab-item {
		color: #999;
	}

	.mint-tab-item a {
		color: inherit;
	}
</style>

<script>
    export default {
        data() {
            let currentUser = this.getCurrentUser();
            return {
                tabbar: window.location.pathname,
                currentUser: currentUser
            };
        },
        computed: {
            inShop: function () {
                if (this.currentUser) {
                    return this.currentUser.shop_sn.length > 0;
                } else {
                    return false;
                }
            },
        },
        methods: {
            getCurrentUser: function () {
                let staff = sessionStorage.getItem('staff');
                let currentUser = JSON.parse(staff);
                return currentUser;
            }
        }
    }
</script>
