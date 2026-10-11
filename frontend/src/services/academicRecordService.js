import api from '../api/client.js'
export default {
  async ownStudent() { const r = await api.get('/my/student'); return r.data.data },
  async ownEnrollments() { const r = await api.get('/my/enrollments'); return r.data.data },
  async ownGrades() { const r = await api.get('/my/grades'); return r.data.data },
  async record(studentId) { const r = await api.get(`/students/${studentId}/academic-record`); return r.data.data },
  async assignedOfferings() { const r = await api.get('/my/course-offerings'); return r.data.data },
}
