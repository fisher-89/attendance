/* Packages Start */
import Vue from 'vue';
import iView from 'iview';

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
} from 'mint-ui';

window.axios = axios;
window.Indicator = Indicator;
window.Toast = Toast;

import View from '../front/check/index.vue';

const app = new Vue({
    el: '#app',
    beforeCreate() {
        iView.LoadingBar.start();
    },
    mounted() {
        iView.LoadingBar.finish();
    },
    render: h => h(View)
});