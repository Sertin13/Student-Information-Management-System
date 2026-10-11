import { Outlet } from 'react-router-dom'
import { Sidebar } from '../components/Sidebar.jsx'
import { useAuth } from '../context/authContext.js'

export function AppLayout() {
  const { user, logout } = useAuth()
  return <div className="min-h-screen md:flex"><Sidebar /><div className="flex min-w-0 flex-1 flex-col"><header className="flex min-h-16 flex-wrap items-center justify-between gap-3 border-b bg-white px-4 py-3 sm:px-6"><span className="font-medium text-slate-700">Frontend Integration</span><div className="flex items-center gap-3 text-right text-sm"><div><p className="font-medium text-slate-800">{user?.name}</p><p className="text-slate-500">{user?.role?.name}</p></div><button type="button" onClick={logout} className="rounded bg-slate-100 px-3 py-2 text-slate-700 hover:bg-slate-200">Logout</button></div></header><main className="flex-1 p-4 sm:p-6"><Outlet /></main></div></div>
}
