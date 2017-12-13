<template>
	<Row>
		<i-col span="6">
			<router-link :to="'/f/statistics/personal?ver='+ver">
				<square-button text="个人统计" icon="person"></square-button>
			</router-link>
		</i-col>
		<!--<i-col span="6" v-if="currentUser.is_manager">-->
		<!--<router-link :to="'/f/statistics/shop?ver='+ver">-->
		<!--<square-button text="店铺统计" icon="ios-home"></square-button>-->
		<!--</router-link>-->
		<!--</i-col>-->
		<i-col span="6">
			<router-link :to="'/f/statistics/transrecord?ver='+ver">
				<square-button text="调动记录" color="#2d8cf0" icon="arrow-right-a"></square-button>
			</router-link>
		</i-col>
		<i-col span="6">
			<router-link :to="'/f/statistics/leaverecord?ver='+ver">
				<square-button text="请假记录" color="orange" icon="coffee"></square-button>
			</router-link>
		</i-col>
	</Row>
</template>
<script>
    import SquareButtonComponent from '../tools/square_button.vue';

    export default {
        data() {
            let ver = location.search.replace(/^.*\?ver=(\d{8}).*$/, '$1');
            return {
                ver: ver
            };
        },
        props: ['currentUser'],
        components: {
            'square-button': SquareButtonComponent
        }
    }
</script>
