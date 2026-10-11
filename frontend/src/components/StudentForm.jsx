import { useState } from 'react'

const initial = { student_number: '', first_name: '', middle_name: '', last_name: '', email: '', program_id: '', year_level: '', status: 'Regular', contact_number: '', address: '' }

export function StudentForm({ student, programs, onSave, onCancel, submitting, error }) {
  const [values, setValues] = useState(() => student ? { ...initial, ...student, program_id: String(student.program_id) } : initial)
  const set = (name, value) => setValues({ ...values, [name]: value })
  const validation = (name) => error?.validationErrors?.[name]?.[0]
  const fieldId = (name) => `student-${name}`
  const submit = (event) => {
    event.preventDefault()
    if (!submitting) onSave({ ...values, program_id: Number(values.program_id), year_level: Number(values.year_level) })
  }
  const fields = [['student_number', 'Student number', true], ['first_name', 'First name', true], ['middle_name', 'Middle name'], ['last_name', 'Last name', true], ['email', 'Email'], ['contact_number', 'Contact number']]

  return <form onSubmit={submit} aria-busy={submitting} className="space-y-3">
    {error?.message && <p role="alert" className="rounded bg-red-50 p-2 text-sm text-red-700">{error.message}</p>}
    <div className="grid gap-3 sm:grid-cols-2">
      {fields.map(([name, label, required]) => <div key={name}><label htmlFor={fieldId(name)} className="text-sm">{label}{required && ' *'}</label><input id={fieldId(name)} type={name === 'email' ? 'email' : 'text'} required={required} value={values[name] ?? ''} onChange={(event) => set(name, event.target.value)} aria-invalid={Boolean(validation(name))} aria-describedby={validation(name) ? `${fieldId(name)}-error` : undefined} className="mt-1 w-full rounded border p-2" />{validation(name) && <small id={`${fieldId(name)}-error`} className="text-red-700">{validation(name)}</small>}</div>)}
      <div><label htmlFor="student-program" className="text-sm">Program *</label><select id="student-program" required value={values.program_id} onChange={(event) => set('program_id', event.target.value)} aria-invalid={Boolean(validation('program_id'))} aria-describedby={validation('program_id') ? 'student-program-error' : undefined} className="mt-1 w-full rounded border p-2"><option value="">Select program</option>{programs.map((program) => <option key={program.id} value={program.id}>{program.code} — {program.name}</option>)}</select>{validation('program_id') && <small id="student-program-error" className="text-red-700">{validation('program_id')}</small>}</div>
      <div><label htmlFor="student-year-level" className="text-sm">Year level *</label><input id="student-year-level" type="number" min="1" max="6" required value={values.year_level} onChange={(event) => set('year_level', event.target.value)} aria-invalid={Boolean(validation('year_level'))} aria-describedby={validation('year_level') ? 'student-year-level-error' : undefined} className="mt-1 w-full rounded border p-2" />{validation('year_level') && <small id="student-year-level-error" className="text-red-700">{validation('year_level')}</small>}</div>
      <div><label htmlFor="student-status" className="text-sm">Status *</label><select id="student-status" value={values.status} onChange={(event) => set('status', event.target.value)} className="mt-1 w-full rounded border p-2">{['Regular', 'Irregular', 'Graduated', 'Inactive'].map((status) => <option key={status}>{status}</option>)}</select></div>
    </div>
    <div><label htmlFor="student-address" className="block text-sm">Address</label><textarea id="student-address" value={values.address ?? ''} onChange={(event) => set('address', event.target.value)} className="mt-1 w-full rounded border p-2" /></div>
    <div className="flex flex-wrap justify-end gap-3"><button type="button" onClick={onCancel} disabled={submitting} className="rounded px-4 py-2 disabled:opacity-50">Cancel</button><button type="submit" disabled={submitting} className="rounded bg-blue-600 px-4 py-2 text-white disabled:cursor-not-allowed disabled:opacity-50">{submitting ? 'Saving…' : 'Save'}</button></div>
  </form>
}
