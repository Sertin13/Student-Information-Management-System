import { NavLink } from 'react-router-dom'
import { useAuth } from '../context/authContext.js'

const management = [['/students', 'Students'], ['/programs', 'Programs'], ['/courses', 'Courses'], ['/academic-terms', 'Academic Terms'], ['/course-offerings', 'Course Offerings'], ['/enrollments', 'Enrollments']]

export function Sidebar() {
  const { hasRole } = useAuth()
  const links = [
    ['/dashboard', 'Dashboard'],
    ...(hasRole('Admin', 'Staff') ? management : []),
    ...(hasRole('Admin', 'Staff', 'Instructor') ? [['/grades', 'Grades']] : []),
    ...(hasRole('Admin', 'Staff', 'Student') ? [['/academic-record', 'Academic Record']] : []),
    ['/profile', 'Profile'],
  ]

  return <aside className="w-full shrink-0 bg-slate-900 p-4 text-slate-100 md:min-h-screen md:w-64">
    <h1 className="mb-6 text-lg font-bold">Student Information System</h1>
    <nav aria-label="Primary navigation" className="flex gap-1 overflow-x-auto md:flex-col">
      {links.map(([to, label]) => <NavLink key={to} to={to} className={({ isActive }) => `whitespace-nowrap rounded px-3 py-2 text-sm outline-offset-2 ${isActive ? 'bg-blue-600 text-white' : 'hover:bg-slate-800'}`}>{label}</NavLink>)}
    </nav>
  </aside>
}
