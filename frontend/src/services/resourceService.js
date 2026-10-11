import api from '../api/client.js'

export function createResourceService(resource) {
  return {
    async list(page = 1, params = {}) { const response = await api.get(`/${resource}`, { params: { ...params, page, per_page: params.per_page ?? 20 } }); return response.data.data },
    async create(payload) { const response = await api.post(`/${resource}`, payload); return response.data.data },
    async update(id, payload) { const response = await api.put(`/${resource}/${id}`, payload); return response.data.data },
    async remove(id) { await api.delete(`/${resource}/${id}`) },
  }
}
