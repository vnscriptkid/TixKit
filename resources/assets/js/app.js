import TicketCheckout from './components/TicketCheckout.vue'
import Vue from 'vue'

require('./bootstrap')

const app = new Vue({
    components: {
        TicketCheckout,
    },
})

app.$mount('#app')
