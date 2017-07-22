// require('./bootstrap');
// import VueRouter from 'vue-router';
import routes from './router.js';
import View from './front/view.vue';
// import store from './front/vuex';
import { InfiniteScroll } from 'mint-ui';

window.oaurl = 'http://192.168.1.110:8002/Api/';

import log from './logFn.js';

window.log = log;

Vue.use(InfiniteScroll);
// Vue.use(VueRouter);


let attendanceStatusMsg=['数据异常','通过'];
Vue.filter('setStatus',function(val){
	return attendanceStatusMsg[val];
})


const router = new VueRouter({
	mode: 'history',
	routes,
	scrollBehavior (to, from, savedPosition) {
		return { x: 0, y: 0 }
	}
});

const app = new Vue({
    el: '#app',
    router, 
    // store,
    render: h => h(View)
});
