let response = [
    /*打卡*/
    {path: '/f/sign', component: require('../front/clock/index.vue')},

    /*请求*/
    {path: '/f/application', component: require('../front/application/index.vue')},
    /*请假*/
    {path: '/f/application/askforleave', component: require('../front/application/leave.vue')},
    /*请假*/
    {path: '/f/application/askforothersleave', component: require('../front/application/others_leave.vue')},

    /*统计*/
    {path: '/f/statistics', component: require('../front/statistics/index.vue')},
    /*个人统计*/
    {path: '/f/statistics/personal', component: require('../front/statistics/personal.vue')},
    /*店铺统计*/
    {path: '/f/statistics/shop', component: require('../front/statistics/shop.vue')},
    /*调动记录*/
    {path: '/f/statistics/transrecord', component: require('../front/statistics/transfer_record.vue')},

    /*店铺考勤*/
    {path: '/f/attend', component: require('../front/attendance/index.vue')},
];
export default response;
