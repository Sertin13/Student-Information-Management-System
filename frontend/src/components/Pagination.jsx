export function Pagination({ page, lastPage, onChange }) {
  return <nav aria-label="Pagination" className="mt-4 flex items-center justify-between text-sm">
    <button type="button" disabled={page <= 1} onClick={() => onChange(page - 1)} aria-label="Go to previous page" className="rounded border px-3 py-2 disabled:cursor-not-allowed disabled:opacity-50">Previous</button>
    <span aria-live="polite">Page {page} of {lastPage}</span>
    <button type="button" disabled={page >= lastPage} onClick={() => onChange(page + 1)} aria-label="Go to next page" className="rounded border px-3 py-2 disabled:cursor-not-allowed disabled:opacity-50">Next</button>
  </nav>
}
