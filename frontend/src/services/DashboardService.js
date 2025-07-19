import axios from "axios"

export default {
    getCampaignData() {
        return axios.get('/campaigns-data')
    },
    getCampaignLast() {
        return axios.get('/campaigns-last')
    },
    getContactsData() {
        return axios.get('/contacts-data')
    },
    getCountConversations() {
        return axios.get('/cantidad-conversaciones')
    },
    getPromConversations() {
        return axios.get('/duracion-conversaciones')
    },
    getConsultorData(id) {
        return axios.get(`/getConsultorData/${id}`)
    },
    
}