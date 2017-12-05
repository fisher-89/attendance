<template>
	<Row>
		<i-col span="6">
			<router-link :to="'/f/application/askforleave?ver='+ver">
				<square-button text="请假" color="orange" icon="coffee"></square-button>
			</router-link>
		</i-col>
		<i-col span="6">
			<router-link :to="'/f/application/transrecord?ver='+ver">
				<square-button text="调动记录" color="#2d8cf0" icon="arrow-right-a"></square-button>
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
        components: {
            'square-button': SquareButtonComponent
        }
    }
</script>
