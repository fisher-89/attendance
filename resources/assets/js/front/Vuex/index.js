import Vue from 'vue';
import Vuex from 'vuex';
import userInfo from './userinfo.js';
import 'mint-ui/lib/style.css';
Vue.use(Vuex);

const store = new Vuex.Store({
	modules:{
		userinfo:userInfo
	}
})

export default store;
