import './bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import { createPinia } from 'pinia';
// import ExampleComponent from './components/ExampleComponent.vue';
import router from './router';
import App from './App.vue';

const pinia = createPinia();
const app = createApp(App);
app.use(pinia);
app.use(router);
// app.component('example-component', ExampleComponent);
app.mount('#app');
