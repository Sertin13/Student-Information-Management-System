import { Link } from 'react-router-dom'

export function ForbiddenPage() { return <main className="grid min-h-screen place-items-center p-6 text-center"><section><h1 className="text-2xl font-semibold">403 — Forbidden</h1><p className="mt-2 text-slate-600">You are authenticated, but you do not have permission to perform this action.</p><Link className="mt-5 inline-block rounded bg-blue-600 px-4 py-2 text-white" to="/dashboard">Back to dashboard</Link></section></main> }
export function NotFoundPage() { return <main className="grid min-h-screen place-items-center"><h1 className="text-2xl font-semibold">404 — Not Found</h1></main> }
