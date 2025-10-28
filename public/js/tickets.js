// tickets.js
(() => {
  const TICKETS_KEY = 'ticketapp_tickets';
  const sessionKey = 'ticketapp_session';

  // helper to fetch tickets
  function loadTickets() {
    return JSON.parse(localStorage.getItem(TICKETS_KEY) || '[]');
  }
  function saveTickets(t) {
    localStorage.setItem(TICKETS_KEY, JSON.stringify(t));
  }

  // status color mapping
  function statusClass(status) {
    switch(status) {
      case 'open': return 'bg-green-100 text-green-800';
      case 'in_progress': return 'bg-amber-100 text-amber-800';
      case 'closed': return 'bg-gray-100 text-gray-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  }

  // render tickets
  function render() {
    const listEl = document.getElementById('tickets-list');
    if (!listEl) return;
    const tickets = loadTickets();
    listEl.innerHTML = tickets.map(t => `
      <article class="bg-white p-4 rounded shadow">
        <div class="flex justify-between items-start">
          <div>
            <h3 class="font-semibold">${escapeHtml(t.title)}</h3>
            <p class="text-sm text-gray-500">${escapeHtml(t.description || '')}</p>
          </div>
          <div class="text-right">
            <span class="inline-block px-2 py-1 rounded text-xs ${statusClass(t.status)}">${t.status}</span>
            <div class="mt-2 flex gap-2">
              <button data-id="${t.id}" class="edit-btn text-sm">Edit</button>
              <button data-id="${t.id}" class="delete-btn text-sm text-red-600">Delete</button>
            </div>
          </div>
        </div>
      </article>
    `).join('');
    updateDashboardCounts();
    attachListHandlers();
  }

  // safe escape
  function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
  }

  function updateDashboardCounts() {
    const tickets = loadTickets();
    const total = tickets.length;
    const open = tickets.filter(t => t.status === 'open').length;
    const closed = tickets.filter(t => t.status === 'closed').length;
    const totalEl = document.getElementById('total-tickets');
    if (totalEl) totalEl.textContent = total;
    const openEl = document.getElementById('open-tickets');
    if (openEl) openEl.textContent = open;
    const closedEl = document.getElementById('closed-tickets');
    if (closedEl) closedEl.textContent = closed;
  }

  function attachListHandlers(){
    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.onclick = (e) => {
        const id = e.target.dataset.id;
        openModalForEdit(id);
      }
    });
    document.querySelectorAll('.delete-btn').forEach(btn => {
      btn.onclick = (e) => {
        const id = e.target.dataset.id;
        if (confirm('Delete ticket? This cannot be undone.')) {
          const tickets = loadTickets().filter(t => t.id !== id);
          saveTickets(tickets);
          showToast('Ticket deleted', 'success');
          render();
        }
      }
    });
  }

  // modal controls
  const modal = document.getElementById('ticket-modal');
  const form = document.getElementById('ticket-form');
  let editingId = null;

  document.getElementById('create-ticket-btn')?.addEventListener('click', () => {
    editingId = null;
    form.reset();
    document.getElementById('modal-title').textContent = 'Create Ticket';
    showModal();
  });

  document.getElementById('cancel-ticket')?.addEventListener('click', hideModal);

  function showModal() { modal.classList.remove('hidden'); }
  function hideModal() { modal.classList.add('hidden'); }

  function openModalForEdit(id) {
    const tickets = loadTickets();
    const t = tickets.find(x => x.id === id);
    if (!t) return showToast('Ticket not found', 'error');
    editingId = id;
    document.getElementById('ticket-title').value = t.title;
    document.getElementById('ticket-status').value = t.status;
    document.getElementById('ticket-desc').value = t.description || '';
    document.getElementById('modal-title').textContent = 'Edit Ticket';
    showModal();
  }

  // handle form submit (create/update)
  form?.addEventListener('submit', (e) => {
    e.preventDefault();
    const title = document.getElementById('ticket-title').value.trim();
    const status = document.getElementById('ticket-status').value;
    const desc = document.getElementById('ticket-desc').value.trim();

    // validation
    let hasError = false;
    const titleErr = document.getElementById('title-error');
    const statusErr = document.getElementById('status-error');
    titleErr.classList.add('hidden'); statusErr.classList.add('hidden');

    if (!title) { titleErr.textContent = 'Title is required'; titleErr.classList.remove('hidden'); hasError = true; }
    if (!['open','in_progress','closed'].includes(status)) { statusErr.textContent = 'Status must be open, in_progress or closed'; statusErr.classList.remove('hidden'); hasError = true; }
    if (hasError) return;

    const tickets = loadTickets();

    if (editingId) {
      // update
      const idx = tickets.findIndex(t => t.id === editingId);
      if (idx === -1) return showToast('Failed to find ticket', 'error');
      tickets[idx] = {...tickets[idx], title, status, description: desc};
      saveTickets(tickets);
      showToast('Ticket updated', 'success');
    } else {
      // create
      const id = 't_' + Date.now();
      tickets.unshift({ id, title, status, description: desc, createdAt: Date.now()});
      saveTickets(tickets);
      showToast('Ticket created', 'success');
    }
    hideModal();
    render();
  });

  // utility toast wrapper (depends on toasts.js)
  function showToast(msg, type='info') {
    if (window.showToast) return window.showToast(msg, type);
    alert(msg);
  }

  // ensure guard checks run (the guard script may redirect)
  // initial render
  document.addEventListener('DOMContentLoaded', render);
})();
