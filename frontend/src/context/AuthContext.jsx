import { useCallback, useEffect, useMemo, useState } from 'react'
import api from '../api/client.js'
import { authStorage } from '../utils/authStorage.js'
import { AuthContext } from './authContext.js'

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  const [token, setToken] = useState(authStorage.getToken())
  const [isInitializing, setIsInitializing] = useState(true)

  const clearAuth = useCallback(() => {
    authStorage.clearToken()
    setToken(null)
    setUser(null)
  }, [])

  const refreshUser = useCallback(async () => {
    const response = await api.get('/auth/me', { skipAuthRedirect: true })
    setUser(response.data?.data ?? null)
    return response.data?.data
  }, [])

  useEffect(() => {
    const initialize = async () => {
      if (!authStorage.getToken()) { setIsInitializing(false); return }
      try { await refreshUser() } catch { clearAuth() } finally { setIsInitializing(false) }
    }
    initialize()
  }, [clearAuth, refreshUser])

  useEffect(() => {
    const handleUnauthorized = () => { clearAuth(); window.location.assign('/login') }
    window.addEventListener('auth:unauthorized', handleUnauthorized)
    return () => window.removeEventListener('auth:unauthorized', handleUnauthorized)
  }, [clearAuth])

  const login = useCallback(async (credentials) => {
    const response = await api.post('/auth/login', credentials, { skipAuthRedirect: true })
    const nextToken = response.data?.data?.token
    if (!nextToken) throw { status: 500, message: 'The server did not return an authentication token.', validationErrors: {} }
    authStorage.setToken(nextToken)
    setToken(nextToken)
    try { return await refreshUser() } catch (error) { clearAuth(); throw error }
  }, [clearAuth, refreshUser])

  const logout = useCallback(async () => {
    try { await api.post('/auth/logout', {}, { skipAuthRedirect: true }) } catch { /* Local logout remains safe if token is invalid. */ } finally { clearAuth() }
  }, [clearAuth])

  const value = useMemo(() => ({ user, token, isAuthenticated: Boolean(user && token), isInitializing, login, logout, refreshUser, hasRole: (...roles) => roles.includes(user?.role?.name) }), [user, token, isInitializing, login, logout, refreshUser])
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}
