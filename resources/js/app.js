import Vue from 'vue';
// import Vuelidate from 'vuelidate';
import vSelect from 'vue-select'

Vue.component('v-select', vSelect);

// Vue.use(Vuelidate);

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('file-uploader', require('./components/FilesUploader.vue').default);

Vue.component('preset-form', require('./components/PresetForm.vue').default);

Vue.component('process-job', require('./components/ProcessJob.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
$(() => {
    if ($('#app').length) {
        const app = new Vue({
            el: '#app',
        });
    }
});
