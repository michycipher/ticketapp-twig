
(() => {
  const sessionKey = 'ticketapp_session';
  function isProtected() {
    // list of protected routes:
    const protectedPaths = ['/dashboard', '/tickets'];
    return protectedPaths.includes(location.pathname);
  }

  if (isProtected()) {
    const session = localStorage.getItem(sessionKey);
    if (!session) {
      // friendly message
      localStorage.removeItem(sessionKey);
      alert('You must log in to access that page. Redirecting to login.');
      window.location.href = '/auth/login';
    } else {
      // optionally check expiry: e.g. 24 hours
      const s = JSON.parse(session);
      const age = Date.now() - s.issuedAt;
      const maxAge = 24 * 60 * 60 * 1000;
      if (age > maxAge) {
        localStorage.removeItem(sessionKey);
        alert('Your session has expired — please log in again.');
        window.location.href = '/auth/login';
      }
    }
  }
})();
