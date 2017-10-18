<template>
	<div>
		<Card :shadow="true" v-if="currentUser">
			<Row>
				<i-col span="4">
					<Avatar icon="person" size="large"/>
				</i-col>
				<i-col span="9">
					<h3>{{currentUser.realname}}</h3><small>{{currentUser.staff_sn}}</small>
				</i-col>
			</Row>
			<Clock v-if="currentUser" :current-user="currentUser" date.sync=""></Clock>
		</Card>
	</div>
</template>


<script>
    import timeLineComponent from './timeLine.vue';

    var components = {};
    components['Clock'] = timeLineComponent;

    export default {
        data() {
            return {
                currentUser: false
            };
        },
        props: ['staffSn'],
        components: components,
        watch: {
            staffSn(newValue) {
                axios.post('/clock/get_shop_staff', {staff_sn: newValue}).then((response) => {
                    this.currentUser = response.data;
                });
            }
        },
        filters: {},
        beforeMount() {
        },
        mounted() {
        },
        methods: {
            getStaffInfo() {

            }
        }
    }
</script>
