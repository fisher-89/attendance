<template>
	<div v-if="currentUser != false" style="padding:16px;">
		<Row>
			<i-col span="4">
				<Avatar icon="person" size="large"/>
			</i-col>
			<i-col span="9">
				<h3>{{currentUser.realname}}</h3>
				<small>{{currentUser.staff_sn}}</small>
			</i-col>
		</Row>
		<Clock :current-user.sync="currentUser" :refresh.sync="clockRefresh"
		       :date.sync="date"></Clock>
	</div>
</template>


<script>
    import timeLineComponent from './timeLine.vue';

    var components = {};
    components['Clock'] = timeLineComponent;

    export default {
        data() {
            return {
                date: '',
                currentUser: false,
                clockRefresh: false
            };
        },
        props: ['staffSn'],
        computed: {},
        components: components,
        watch: {
            staffSn(newValue) {
                axios.post('/clock/get_shop_staff', {staff_sn: newValue}).then((response) => {
                    this.currentUser = response.data;
                    this.clockRefresh = true;
                });
            }
        },
        filters: {},
        beforeMount() {
        },
        mounted() {
        },
        methods: {}
    }
</script>
