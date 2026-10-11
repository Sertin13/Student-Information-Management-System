import { useEffect, useState } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/authContext.js'

export function LoginPage() {
  const { login, isAuthenticated, isInitializing } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const [submitting, setSubmitting] = useState(false)
  const destination = location.state?.from?.pathname || '/dashboard'

  useEffect(() => {
    if (!isInitializing && isAuthenticated) navigate('/dashboard', { replace: true })
  }, [isAuthenticated, isInitializing, navigate])

  const submit = async (event) => {
    event.preventDefault()
    if (submitting) return
    setError('')
    setSubmitting(true)
    try {
      await login({ email, password })
      navigate(destination, { replace: true })
    } catch (apiError) {
      setError(apiError.message || 'Unable to sign in.')
    } finally {
      setSubmitting(false)
    }
  }

  return <main className="grid min-h-screen place-items-center bg-slate-100 p-6">
    <form onSubmit={submit} aria-busy={submitting} className="w-full max-w-sm rounded-xl bg-white p-7 shadow-sm">
      <h1 className="text-2xl font-semibold text-slate-900">Sign in</h1>
      <p className="mt-2 text-sm text-slate-600">Student Information System</p>
      {error && <p role="alert" aria-live="assertive" className="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">{error}</p>}
      <label htmlFor="login-email" className="mt-5 block text-sm font-medium text-slate-700">Email</label>
      <input id="login-email" type="email" autoComplete="email" required value={email} onChange={(event) => setEmail(event.target.value)} className="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
      <label htmlFor="login-password" className="mt-4 block text-sm font-medium text-slate-700">Password</label>
      <input id="login-password" type="password" autoComplete="current-password" required value={password} onChange={(event) => setPassword(event.target.value)} className="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
      <button type="submit" disabled={submitting} className="mt-6 w-full rounded bg-blue-600 px-4 py-2 font-medium text-white disabled:cursor-not-allowed disabled:opacity-60">{submitting ? 'Signing in…' : 'Sign in'}</button>
    </form>
  </main>
}
