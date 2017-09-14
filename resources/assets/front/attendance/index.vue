<template>
    <div>
        {{attendanceSheet}}
    </div>
</template>

<script>

    export default {
        data() {
            return {
                shopInfo: {
                    // achievement:18181,
                    name: '',
                    shop_sn: '',
                    lister: '',
                    oimg: '',
                    achievement: 0,
                    cooperate_money: 0,
                    goods_money: 0,
                },
                attendanceSheet: [],
                searchStaffStatus: false,
            }
        },
        mounted() {
            this.getAttendanceRecord();
        },
        methods: {
            getAttendanceRecord() {
                Indicator.open('加载中...');
                axios.post('/api/attendance').then((response) => {
                    this.attendanceSheet = response.data;
                    Indicator.close();
                });
            },

        },
        computed: {

        }
    }
</script>