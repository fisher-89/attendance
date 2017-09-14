/* Packages Start */
import Vue from 'vue';
import VueRouter from 'vue-router';
import iView from 'iview';
Vue.use(VueRouter);
Vue.use(iView);
/* Packages End */
/* StyleSheets Start */
import '../css/mint-ui.min.css';
import '../css/iview.css';
/* StyleSheets End */
/* Global Packages Start */
import axios from 'axios';
import {
	Indicator,
	Toast,
	InfiniteScroll,
	Tabbar,
	TabItem
} from 'mint-ui';
window.axios = axios;
window.Indicator = Indicator;
window.Toast = Toast;
Vue.use(InfiniteScroll);
Vue.component(Tabbar.name, Tabbar);
Vue.component(TabItem.name, TabItem);
window.oaurl = 'http://192.168.1.20:8001/Api/';
/* Global Packages End */
/* Router Start */
import routes from './router';
const router = new VueRouter({
	mode: 'history',
	routes: routes,
});
router.beforeEach((to, from, next) => {
	iView.LoadingBar.start();
	/*登录 start*/
	let staff = sessionStorage.getItem('staff');
	if (staff == null) {
		Indicator.open('登录中...');
		axios.get('/api/getuser').then(function(response) {
			sessionStorage.setItem('staff', JSON.stringify(response.data));
			Indicator.close();
			next();
		});
	} else {
		next();
	}
	/*登录 end*/
});
router.afterEach((to, from, next) => {
	iView.LoadingBar.finish();
	window.scrollTo(0, 0);
});
/* Router End */
import View from '../front/view.vue';
const app = new Vue({
	el: '#app',
	router: router,
	render: h => h(View)
});