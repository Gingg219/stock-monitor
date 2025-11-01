import axios from 'axios'

export const api = axios.create({
  baseURL: 'http://localhost:8000',
  withXSRFToken: true,
  withCredentials: true,
  xsrfCookieName: 'XSRF-TOKEN',
  xsrfHeaderName: 'X-XSRF-TOKEN',
})

export default api