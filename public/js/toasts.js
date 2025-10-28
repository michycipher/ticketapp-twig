
(function(){
  const container = document.createElement('div');
  container.id = 'toast-container';
  container.style.position = 'fixed';
  container.style.right = '1rem';
  container.style.bottom = '1rem';
  container.style.zIndex = 9999;
  document.body.appendChild(container);

  window.showToast = function(message, type='info') {
    const el = document.createElement('div');
    el.className = 'mb-2 px-4 py-2 rounded shadow';
    el.style.minWidth = '180px';
    el.style.background = (type === 'success') ? '#ecfdf5' : (type === 'error' ? '#fee2e2' : '#eef2ff');
    el.textContent = message;
    container.appendChild(el);
    setTimeout(()=> {
      el.style.transition = 'opacity 0.3s';
      el.style.opacity = '0';
      setTimeout(()=> el.remove(), 300);
    }, 3500);
  };
})();
