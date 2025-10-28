// public/assets/js/app.js
document.addEventListener('DOMContentLoaded', () => {
  // When landing on dashboard after server sets cookie 'ticketapp_token', copy it into localStorage key required
  const tokenCookie = (name => {
    const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    if (match) return match[2];
    return null;
  })('ticketapp_token');

  if (tokenCookie) {
    try {
      localStorage.setItem('ticketapp_session', tokenCookie);
    } catch (e) {
      console.warn('Failed to set localStorage session', e);
    }
  }

  // Protect pages client-side too: if page requires auth, add data-protect attribute to body or templates include this script check.
  // We'll check for presence of a data-auth attribute on the main container
  const protectedPaths = ['/dashboard', '/tickets', '/tickets/create', '/tickets/edit'];
  if (protectedPaths.some(p => location.pathname.startsWith(p))) {
    if (!localStorage.getItem('ticketapp_session')) {
      // show toast and redirect
      alert('Your session has expired — please log in again.');
      window.location.href = '/auth/login';
    }
  }
});
