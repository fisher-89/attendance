<template>
	<Row>
		<i-col span="6">
			<router-link :to="'/f/application/askforleave?ver='+ver">
				<square-button text="请假" color="orange" icon="coffee"></square-button>
			</router-link>
		</i-col>
		<i-col span="6" v-if="currentUser.is_manager">
			<router-link :to="'/f/application/askforothersleave?ver='+ver">
				<square-button text="请假(代)" color="orange" icon="coffee"></square-button>
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
