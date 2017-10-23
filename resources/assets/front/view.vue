<template>
	<div>
		<div>
			<router-view :currentUser.sync="currentUser"></router-view>
			<div style="width:100%;height:60px;"></div>
		</div>
		<mt-tabbar v-model="tabbar" :fixed="true" style="z-index: 10;">
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
			<mt-tab-item id="/f/transrecord">
				<router-link :to="'/f/transrecord?ver='+ver">
					<Row>
						<Icon type="arrow-right-a" size="30"/>
					</Row>
					<Row>
						调动
					</Row>
				</router-link>
			</mt-tab-item>
			<mt-tab-item id="/f/askforleave">
				<router-link :to="'/f/askforleave?ver='+ver">
					<Row>
						<Icon type="coffee" size="30"/>
					</Row>
					<Row>
						请假
					</Row>
				</router-link>
			</mt-tab-item>
			<mt-tab-item v-if="currentUser.is_manager" id="/f/attend">
				<router-link :to="'/f/attend?ver='+ver">
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
            let ver = location.search.replace(/^.*\?ver=(\d{8}).*$/, '$1');
            let currentUser = this.getCurrentUser();
            return {
                tabbar: window.location.pathname,
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
            },
        },
        beforeMount() {
//            this.dingtalkInit();
        },
        methods: {
            getCurrentUser: function () {
                let staff = sessionStorage.getItem('staff');
                let currentUser = JSON.parse(staff.replace(/[\t|\r|\n]/g, ''));
                return currentUser;
            },
            dingtalkInit() {
                let url = '/js_config';
                axios.post(url, {'current_url': location.href}).then((response) => {
                    let jsConfig = response.data;
                    jsConfig['jsApiList'] = ['biz.util.uploadImageFromCamera', 'device.geolocation.get'];
                    dd.config(jsConfig);
                    dd.error(function (error) {
                        let message = error.message;
                        let html = '';
                        if (message.match(/52013/)) {
                            html += '<p>签名校验失败</p>';
                            html += '<h2 onClick="location.reload()" style="text-align:center;margin-top:20px;color:#333;">点此刷新</h2>';
                        } else {
                            html += message;
                            html += '<h2 onClick="location.reload()" style="text-align:center;margin-top:20px;color:#333;">点此刷新</h2>';
                        }
                        document.write(html);
                    });
                });
            },
        }
    }
</script>
