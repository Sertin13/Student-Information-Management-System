import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { useAuth } from '../context/authContext.js'
import academicRecordService from '../services/academicRecordService.js'
import academicTermService from '../services/academicTermService.js'
import courseOfferingService from '../services/courseOfferingService.js'
import courseService from '../services/courseService.js'
import enrollmentService from '../services/enrollmentService.js'
import instructorService from '../services/instructorService.js'
import programService from '../services/programService.js'
import studentService from '../services/studentService.js'

const total = (page) => Number.isFinite(page?.total) ? page.total : null

const adminLinks = [
  ['/students', 'Students'], ['/programs', 'Programs'], ['/courses', 'Courses'],
  ['/academic-terms', 'Academic Terms'], ['/course-offerings', 'Course Offerings'],
  ['/enrollments', 'Enrollments'], ['/grades', 'Grades'], ['/academic-record', 'Academic Record'], ['/profile', 'Profile'],
]

function MetricCards({ items }) {
  return <div className="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    {items.map(({ label, value, to }) => <Link key={label} to={to} className="rounded-lg border bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow">
      <p className="text-sm font-medium text-slate-600">{label}</p>
      <p className="mt-2 text-3xl font-semibold text-slate-900">{value === null ? '—' : value.toLocaleString()}</p>
      <span className="mt-3 inline-block text-sm text-blue-700">View {label.toLowerCase()} →</span>
    </Link>)}
  </div>
}

function errorMessage(error) {
  if (error?.status === 0) return 'Unable to reach the server. Check that the backend is running and try again.'
  if (error?.status === 401) return 'Your session has expired. Sign in again to continue.'
  if (error?.status === 403) return 'Your account is not authorized to view this dashboard information.'
  if (error?.status === 404) return 'The information linked to this account could not be found.'
  return 'Dashboard information could not be loaded. Please try again.'
}

export function DashboardPage() {
  const { user } = useAuth()
  const role = user?.role?.name
  const [dashboard, setDashboard] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [attempt, setAttempt] = useState(0)

  useEffect(() => {
    let active = true
    const load = async () => {
      try {
        let result
        if (role === 'Admin' || role === 'Staff') {
          const [students, programs, courses, terms, offerings, enrollments] = await Promise.all([
            studentService.list({ page: 1, per_page: 1 }),
            programService.list(1, { per_page: 1 }),
            courseService.list(1, { per_page: 1 }),
            academicTermService.list(1, { per_page: 1 }),
            courseOfferingService.list(1, { per_page: 1 }),
            enrollmentService.list(1, { per_page: 1 }),
          ])
          result = {
            kind: 'manager',
            items: [
              { label: 'Students', value: total(students), to: '/students' },
              { label: 'Programs', value: total(programs), to: '/programs' },
              { label: 'Courses', value: total(courses), to: '/courses' },
              { label: 'Academic Terms', value: total(terms), to: '/academic-terms' },
              { label: 'Course Offerings', value: total(offerings), to: '/course-offerings' },
              { label: 'Enrollments', value: total(enrollments), to: '/enrollments' },
            ],
          }
        } else if (role === 'Instructor') {
          const [offerings, roster] = await Promise.all([
            academicRecordService.assignedOfferings(),
            instructorService.teachingEnrollments(),
          ])
          result = {
            kind: 'instructor',
            items: [
              { label: 'Assigned Course Offerings', value: total(offerings), to: '/grades' },
              { label: 'Teaching Enrollments', value: total(roster), to: '/grades' },
            ],
          }
        } else if (role === 'Student') {
          const [student, enrollments, grades] = await Promise.all([
            academicRecordService.ownStudent(),
            academicRecordService.ownEnrollments(),
            academicRecordService.ownGrades(),
          ])
          result = {
            kind: 'student',
            student,
            items: [
              { label: 'My Enrollments', value: total(enrollments), to: '/academic-record' },
              { label: 'My Grades', value: total(grades), to: '/academic-record' },
            ],
          }
        }
        if (active) setDashboard(result ?? null)
      } catch (apiError) {
        if (active) setError(apiError)
      } finally {
        if (active) setLoading(false)
      }
    }
    load()
    return () => { active = false }
  }, [attempt, role])

  const retry = () => { setDashboard(null); setError(null); setLoading(true); setAttempt((current) => current + 1) }
  const links = role === 'Admin' || role === 'Staff' ? adminLinks : role === 'Instructor' ? [['/grades', 'Grade Roster'], ['/profile', 'Profile']] : role === 'Student' ? [['/academic-record', 'Academic Record'], ['/profile', 'Profile']] : []

  return <section>
    <div>
      <h2 className="text-2xl font-semibold text-slate-900">Dashboard</h2>
      <p className="mt-1 text-slate-600">Welcome, {user?.name}. Your overview is based on your account role.</p>
    </div>

    {loading ? <p className="mt-6 text-slate-600" aria-live="polite">Loading dashboard…</p> : error ? <div className="mt-6 rounded border border-red-200 bg-red-50 p-4" role="alert">
      <p className="text-red-800">{errorMessage(error)}</p>
      <button type="button" onClick={retry} className="mt-3 rounded border border-red-300 px-3 py-2 text-sm text-red-800 hover:bg-red-100">Retry</button>
    </div> : !dashboard ? <p className="mt-6 rounded border border-dashed p-5 text-slate-600" role="status">This dashboard is not available for your account role.</p> : <>
      {dashboard.kind === 'student' && <div className="mt-5 rounded-lg border bg-white p-5">
        <h3 className="font-semibold text-slate-900">Student Profile</h3>
        <p className="mt-1 text-slate-600">{dashboard.student.first_name} {dashboard.student.last_name} · {dashboard.student.student_number}</p>
      </div>}
      <MetricCards items={dashboard.items} />
      {dashboard.items.every((item) => item.value === 0) && <p className="mt-4 rounded border border-dashed p-4 text-slate-600">No records are available for this overview yet.</p>}
    </>}

    <nav aria-label="Dashboard shortcuts" className="mt-8">
      <h3 className="text-lg font-semibold text-slate-900">Quick links</h3>
      <div className="mt-3 flex flex-wrap gap-2">{links.map(([to, label]) => <Link key={to} to={to} className="rounded border bg-white px-3 py-2 text-sm text-blue-700 hover:bg-blue-50">{label}</Link>)}</div>
    </nav>
  </section>
}
