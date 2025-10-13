import axios from 'axios'

const api = axios.create({
  baseURL: 'http://stock-monitor.test/', // Laravel backend URL
  withCredentials: true,            // quan trọng để gửi cookie
})

export default api
