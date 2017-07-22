export default[
	{ 
		path:'/f', //首页
		component:require('./front/index.vue')
	},
	{ 
		path:'/f/sign',  //打卡 
		component:require('./front/people/people.vue')
	},
	{
		path:'/f/trip',  //调动
		component:require('./front/transfer/index.vue')
	},
	{
		path:'/f/transrecord', //调动记录
		component:require('./front/transfer/record.vue')
	},
	{
		path:'/f/confirm',   //店长确定 
		component:require('./front/confirm/index.vue')
	},
	{
		path:'/f/record',   //考勤记录
		component:require('./front/record/index.vue')
	},
	{
		path:'/f/attend',
		component:require('./front/attendance/index.vue')
	},
	{
		path:'/f/attendrecord',
		component:require('./front/attendancerecord/index.vue')
	},
	{
		path:'/f/attendedit',
		component:require('./front/attendancerecord/edit.vue')
	},
	{
		path:'/f/exl',
		component:require('./front/demo/exl.vue')
	},
	{
		path:'/f/component',
		component:require('./front/demo/component.vue')
	},
	{
		path:'/f/input',
		component:require('./front/demo/input.vue')
	},
	{
		path:'/f/redis',
		component:require('./front/demo/redis.vue')
	}
	// {
	// 	path:'/f/edit',
	// 	component:require('./front/demo/edit.vue')
	// },
	// {
	// 	path:'/f/table', 
	// 	component:require('./front/demo/table-com.vue')
	// },
	// {
		// path:'/f/editor',
		// component:require('./front/demo/editor.vue') 
		// component:require('./front/demo/review.vue'),
		// children:[
		// 	{
		// 		path:'/',
		// 		component:require('./front/demo/list.vue')
		// 	},
		// 	{
		// 		path:'add',
		// 		component:require('./front/demo/table-com.vue')
		// 	}
		// ]
	// }
] 