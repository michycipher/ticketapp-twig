// auth.js
(() => {
  const sessionKey = 'ticketapp_session';
  // small helper
  function toast(msg, type='info') {
    if (window.showToast) return window.showToast(msg, type);
    alert(msg);
  }

  // SIGNUP: (if on signup page)
  const signupForm = document.getElementById('signup-form');
  if (signupForm) {
    signupForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const email = signupForm.email.value.trim();
      const password = signupForm.password.value.trim();
      // simple validation
      if (!email || !password || password.length < 6) {
        toast('Provide valid email and password (min 6 chars)', 'error');
        return;
      }
      const users = JSON.parse(localStorage.getItem('ticketapp_users') || '[]');
      if (users.find(u => u.email === email)) {
        toast('User already exists. Login instead.', 'error');
        return;
      }
      users.push({ email, password });
      localStorage.setItem('ticketapp_users', JSON.stringify(users));
      // auto-login
      const session = { user: email, issuedAt: Date.now() };
      localStorage.setItem(sessionKey, JSON.stringify(session));
      toast('Account created — welcome!', 'success');
      window.location.href = '/dashboard';
    });
  }

  // LOGIN
  const loginForm = document.getElementById('login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const email = loginForm.email.value.trim();
      const password = loginForm.password.value.trim();

      if (!email || !password) {
        toast('Email and password are required', 'error');
        return;
      }
      const users = JSON.parse(localStorage.getItem('ticketapp_users') || '[]');
      const found = users.find(u => u.email === email && u.password === password);
      if (!found) {
        toast('Invalid credentials', 'error');
        return;
      }
      const session = { user: email, issuedAt: Date.now() };
      localStorage.setItem(sessionKey, JSON.stringify(session));
      toast('Login successful', 'success');
      window.location.href = '/dashboard';
    });
  }

  // LOGOUT button (if present)
  const logoutBtn = document.getElementById('logout-btn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', (e) => {
      localStorage.removeItem(sessionKey);
      window.location.href = '/';
    });
  }
})();
