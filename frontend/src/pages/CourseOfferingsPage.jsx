import { useEffect, useState } from 'react'
import { ReferenceDataPage } from '../components/ReferenceDataPage.jsx'
import offeringService from '../services/courseOfferingService.js'
import courseService from '../services/courseService.js'
import termService from '../services/academicTermService.js'
import api from '../api/client.js'

export function CourseOfferingsPage() {
  const [courses, setCourses] = useState([]); const [terms, setTerms] = useState([]); const [instructors, setInstructors] = useState([])
  useEffect(() => { Promise.all([courseService.list(1), courseService.list(2), termService.list(1), termService.list(2), api.get('/instructors')]).then(([c1, c2, t1, t2, users]) => { setCourses([...(c1.data ?? []), ...(c2.data ?? [])]); setTerms([...(t1.data ?? []), ...(t2.data ?? [])]); setInstructors(users.data.data ?? []) }) }, [])
  const fields = [{ name: 'course_id', label: 'Course', type: 'select', options: courses.map((course) => ({ value: String(course.id), label: `${course.course_code} — ${course.course_title}` })), required: true }, { name: 'academic_term_id', label: 'Academic term', type: 'select', options: terms.map((term) => ({ value: String(term.id), label: `${term.academic_year} — ${term.semester}` })), required: true }, { name: 'instructor_id', label: 'Instructor', type: 'select', options: [{ value: '', label: 'Unassigned' }, ...instructors.map((instructor) => ({ value: String(instructor.id), label: instructor.name }))] }, { name: 'section', label: 'Section', required: true }, { name: 'schedule', label: 'Schedule' }, { name: 'room', label: 'Room' }, { name: 'capacity', label: 'Capacity', type: 'number', required: true }, { name: 'status', label: 'Status', type: 'select', options: ['Active', 'Inactive'], default: 'Active', required: true }]
  return <ReferenceDataPage title="Course Offerings" service={offeringService} fields={fields} columns={[{ name: 'course_id', label: 'Course ID' }, { name: 'academic_term_id', label: 'Academic Term ID' }, { name: 'instructor_id', label: 'Instructor ID' }, { name: 'section', label: 'Section' }, { name: 'schedule', label: 'Schedule' }, { name: 'room', label: 'Room' }, { name: 'capacity', label: 'Capacity' }, { name: 'status', label: 'Status' }]} queryConfig={{ sortOptions: [{ value: 'section', label: 'Section' }, { value: 'capacity', label: 'Capacity' }, { value: 'status', label: 'Status' }] }} />
}
