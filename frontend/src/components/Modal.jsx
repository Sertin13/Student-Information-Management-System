import { useEffect, useId, useRef } from 'react'

export function Modal({ title, children, onClose }) {
  const titleId = useId()
  const closeButtonRef = useRef(null)

  useEffect(() => {
    closeButtonRef.current?.focus()
    const closeOnEscape = (event) => {
      if (event.key === 'Escape') onClose()
    }
    window.addEventListener('keydown', closeOnEscape)
    return () => window.removeEventListener('keydown', closeOnEscape)
  }, [onClose])

  return <div className="fixed inset-0 z-20 grid place-items-center bg-slate-950/40 p-4" role="presentation">
    <section role="dialog" aria-modal="true" aria-labelledby={titleId} tabIndex="-1" className="max-h-[90vh] w-full max-w-lg overflow-auto rounded-xl bg-white p-6 shadow-xl">
      <div className="mb-5 flex items-center justify-between gap-4">
        <h2 id={titleId} className="text-lg font-semibold">{title}</h2>
        <button ref={closeButtonRef} type="button" onClick={onClose} className="rounded px-2 py-1 text-slate-600 hover:bg-slate-100">Close</button>
      </div>
      {children}
    </section>
  </div>
}
