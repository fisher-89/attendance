let response = [
    {
        path: '/f/sign',
        component: require('../front/clock/index.vue')
    }, //打卡
    {
        path: '/f/transrecord',
        component: require('../front/transfer/record.vue')
    }, //调动记录
    {
        path: '/f/askforleave',
        component: require('../front/leave/index.vue')
    }, //请假
    {
        path: '/f/attend',
        component: require('../front/attendance/index.vue')
    }, //店铺考勤
    {
        path: '/f/attendrecord',
        component: require('../front/attendancerecord/index.vue')
    }, //店铺考勤记录
];
export default response;
