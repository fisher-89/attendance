let response = [
    /*打卡*/
    {path: '/f/sign', component: require('../front/clock/index.vue')},
    /*调动记录*/
    {path: '/f/transrecord', component: require('../front/transfer/record.vue')},
    /*请假*/
    {path: '/f/askforleave', component: require('../front/leave/index.vue')},
    /*店铺考勤*/
    {path: '/f/attend', component: require('../front/attendance/index.vue')},
];
export default response;
