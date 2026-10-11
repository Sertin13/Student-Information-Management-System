import api from '../api/client.js'
export default {
  async teachingEnrollments(page = 1) { const r = await api.get('/my/teaching-enrollments', { params: { page } }); return r.data.data },
  async createGrade(payload) { const r = await api.post('/my/grades', payload); return r.data.data },
  async updateGrade(id, payload) { const r = await api.put(`/my/grades/${id}`, payload); return r.data.data },
}
