<template>
	<div>
		<div>
			<router-view :current-user.sync="currentUser"></router-view>
			<div style="width:100%;height:60px;"></div>
		</div>
		<mt-tabbar v-model="tabbar" :fixed="true" style="z-index: 10;" v-if="tabbar != '/f/check'">
			<mt-tab-item id="/f/sign">
				<router-link :to="'/f/sign?ver='+ver">
					<Row>
						<Icon type="clock" size="30"/>
					</Row>
					<Row>
						考勤
					</Row>
				</router-link>
			</mt-tab-item>
			<mt-tab-item id="/f/application">
				<router-link :to="'/f/application?ver='+ver">
					<Row>
						<Icon type="ios-paper" size="30"/>
					</Row>
					<Row>
						申请
					</Row>
				</router-link>
			</mt-tab-item>
			<mt-tab-item id="/f/statistics">
				<router-link :to="'/f/statistics?ver='+ver">
					<Row>
						<Icon type="pie-graph" size="30"/>
					</Row>
					<Row>
						统计
					</Row>
				</router-link>
			</mt-tab-item>
			<mt-tab-item v-if="currentUser.is_manager" id="/f/attend">
				<router-link :to="'/f/attend?ver='+ver">
					<Row>
						<Icon type="ios-home" size="30"/>
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
	.mint-tab-item, .mint-tab-item:active, .mint-tab-item:hover {
		color: #999;
	}

	.mint-tab-item a {
		color: inherit;
	}
</style>

<script>
    export default {
        data() {
            let ver = location.search.replace(/^.*\?ver=(\d{8}).*$/, '$1');
            let curPath = window.location.pathname.replace(/^(\/f\/\w+)\/.*$/, '$1');
            let currentUser = this.getCurrentUser();
            return {
                tabbar: curPath,
                currentUser: currentUser,
                ver: ver
            };
        },
        computed: {
            inShop: function () {
                if (this.currentUser) {
                    return this.currentUser.shop_sn.length > 0;
                } else {
                    return false;
                }
            }
        },
        beforeMount() {
            window.onpopstate = (event) => {
                let curPath = window.location.pathname.replace(/^(\/f\/\w+)\/.*$/, '$1');
                this.tabbar = curPath;
            }
        },
        methods: {
            getCurrentUser: function () {
                let staff = sessionStorage.getItem('staff');
                let currentUser = JSON.parse(staff.replace(/[\t|\r|\n]/g, ''));
                return currentUser;
            }
        }
    }
</script>
