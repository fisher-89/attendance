/* Packages Start */
import Vue from 'vue';
import VueRouter from 'vue-router';
import iView from 'iview';

Vue.use(VueRouter);
Vue.use(iView);
/* Packages End */
/* StyleSheets Start */
import 'mint-ui/lib/style.min.css';
import 'iview/dist/styles/iview.css';
import 'flatpickr/dist/themes/airbnb.css';
import '../css/front.css';
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
    next();
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