import api from '../api/client.js'
export default {
  async list(params = {}) {
    const filteredParams = Object.fromEntries(Object.entries(params).filter(([, value]) => value !== '' && value !== null && value !== undefined))
    const r = await api.get('/students', { params: filteredParams })
    return r.data.data
  },
  async get(id) { const r = await api.get(`/students/${id}`); return r.data.data },
  async create(data) { const r = await api.post('/students', data); return r.data.data },
  async update(id, data) { const r = await api.put(`/students/${id}`, data); return r.data.data },
  async remove(id) { await api.delete(`/students/${id}`) },
}
