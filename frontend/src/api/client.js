import axios from 'axios'
import { authStorage } from '../utils/authStorage.js'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
})

export function normalizeApiError(error) {
  if (!error.response) return { status: 0, message: 'Unable to reach the server. Please try again.', validationErrors: {} }

  const { status, data } = error.response
  const messages = { 401: 'Your session has expired. Please sign in again.', 403: 'You do not have permission to perform this action.', 404: 'The requested resource was not found.', 409: 'This request conflicts with an existing record.', 422: 'Please correct the highlighted fields.' }
  return { status, message: data?.message || messages[status] || 'A server error occurred. Please try again.', validationErrors: data?.errors || {} }
}

api.interceptors.request.use((config) => {
  const token = authStorage.getToken()
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    const isLoginRequest = error.config?.url?.endsWith('/auth/login')
    if (error.response?.status === 401 && !isLoginRequest && !error.config?.skipAuthRedirect) {
      authStorage.clearToken()
      window.dispatchEvent(new Event('auth:unauthorized'))
    }
    return Promise.reject(normalizeApiError(error))
  },
)

export default api
