import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// TODO; uncomment this when you merge
window.axios.defaults.baseURL = 'https://passport.et';
