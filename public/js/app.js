/**
 * HoanKiem LAB - Main JavaScript
 */

// ============================================
// Notifications
// ============================================

function initNotifications() {
  const btn = document.getElementById('notif-btn');
  const dropdown = document.getElementById('notif-dropdown');
  if (!btn || !dropdown) return;

  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.closest('.dropdown').classList.toggle('open');
  });

  document.addEventListener('click', () => {
    dropdown?.closest('.dropdown')?.classList.remove('open');
  });

  // Auto-refresh count every 30s
  setInterval(refreshNotifCount, 30000);
}

function refreshNotifCount() {
  fetch(baseUrl + '/api/notifications/count')
    .then(r => r.json())
    .then(data => {
      const badge = document.getElementById('notif-count');
      const dot = document.getElementById('notif-dot');
      if (badge) badge.textContent = data.count || '';
      if (dot) dot.style.display = data.count > 0 ? 'block' : 'none';
    })
    .catch(() => {});
}

// ============================================
// Flash Messages (auto-dismiss)
// ============================================

function initFlashMessages() {
  const alerts = document.querySelectorAll('.alert[data-auto-dismiss]');
  alerts.forEach(alert => {
    const delay = parseInt(alert.dataset.autoDismiss) || 4000;
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-10px)';
      alert.style.transition = 'all 0.3s ease';
      setTimeout(() => alert.remove(), 300);
    }, delay);
  });

  // Close button
  document.querySelectorAll('.alert .btn-close').forEach(btn => {
    btn.addEventListener('click', () => {
      const alert = btn.closest('.alert');
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 200);
    });
  });
}

// ============================================
// Modal
// ============================================

function openModal(modalId) {
  const overlay = document.getElementById(modalId);
  if (overlay) {
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(modalId) {
  const overlay = document.getElementById(modalId);
  if (overlay) {
    overlay.classList.remove('active');
    document.body.style.overflow = '';
  }
}

// Close modal on overlay click
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('active');
    document.body.style.overflow = '';
  }
});

// Close on Escape
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.active').forEach(m => {
      m.classList.remove('active');
      document.body.style.overflow = '';
    });
  }
});

// ============================================
// Sidebar (Mobile toggle)
// ============================================

function initSidebar() {
  const toggleBtn = document.getElementById('sidebar-toggle');
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.getElementById('sidebar-overlay');

  if (!toggleBtn || !sidebar) return;

  toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('open');
    overlay?.classList.toggle('active');
  });

  overlay?.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('active');
  });
}

// ============================================
// Confirm Delete
// ============================================

function confirmDelete(message, formId) {
  if (confirm(message || 'Bạn có chắc chắn muốn xóa?')) {
    document.getElementById(formId)?.submit();
  }
}

// ============================================
// Image Preview
// ============================================

function previewImage(input, previewId) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = (e) => {
      const preview = document.getElementById(previewId);
      if (preview) {
        preview.src = e.target.result;
        preview.style.display = 'block';
      }
    };
    reader.readAsDataURL(input.files[0]);
  }
}

// ============================================
// Production Step Timer
// ============================================

function startStepTimer(startTime) {
  const timerEl = document.getElementById('step-timer');
  if (!timerEl || !startTime) return;

  function updateTimer() {
    const start = new Date(startTime);
    const now = new Date();
    const diff = Math.floor((now - start) / 1000);

    const h = Math.floor(diff / 3600).toString().padStart(2, '0');
    const m = Math.floor((diff % 3600) / 60).toString().padStart(2, '0');
    const s = (diff % 60).toString().padStart(2, '0');

    timerEl.textContent = `${h}:${m}:${s}`;
  }

  updateTimer();
  setInterval(updateTimer, 1000);
}

// ============================================
// Form Validation (Client-side)
// ============================================

function validateForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return true;

  let valid = true;
  const required = form.querySelectorAll('[required]');

  required.forEach(field => {
    const error = field.parentNode.querySelector('.field-error');
    if (!field.value.trim()) {
      field.classList.add('is-invalid');
      if (error) {
        error.textContent = 'Trường này là bắt buộc';
        error.style.display = 'block';
      }
      valid = false;
    } else {
      field.classList.remove('is-invalid');
      if (error) error.textContent = '';
    }
  });

  return valid;
}

// ============================================
// Tabs
// ============================================

function initTabs() {
  document.querySelectorAll('[data-tab-target]').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.tabTarget;
      const container = btn.closest('[data-tabs]') || document;

      // Update buttons
      container.querySelectorAll('[data-tab-target]').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      // Update panels
      document.querySelectorAll('[data-tab-panel]').forEach(panel => {
        panel.style.display = panel.dataset.tabPanel === target ? '' : 'none';
      });
    });
  });
}

// ============================================
// Search (filter table)
// ============================================

function initTableSearch(inputId, tableId) {
  const input = document.getElementById(inputId);
  const tbody = document.querySelector(`#${tableId} tbody`);
  if (!input || !tbody) return;

  input.addEventListener('input', () => {
    const q = input.value.toLowerCase();
    tbody.querySelectorAll('tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}

// ============================================
// Copy to clipboard
// ============================================

function copyText(text, feedbackEl) {
  navigator.clipboard.writeText(text).then(() => {
    const el = typeof feedbackEl === 'string'
      ? document.getElementById(feedbackEl)
      : feedbackEl;
    if (el) {
      const original = el.textContent;
      el.textContent = 'Đã copy!';
      setTimeout(() => el.textContent = original, 1500);
    }
  });
}

// ============================================
// Format currency (client-side)
// ============================================

function formatMoney(amount) {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND'
  }).format(amount);
}

// ============================================
// Pull-to-refresh (Customer mobile)
// ============================================

function initPullToRefresh() {
  let startY = 0;
  let pulling = false;

  document.addEventListener('touchstart', (e) => {
    if (window.scrollY === 0) {
      startY = e.touches[0].pageY;
      pulling = true;
    }
  });

  document.addEventListener('touchend', (e) => {
    if (pulling) {
      const endY = e.changedTouches[0].pageY;
      if (endY - startY > 80) {
        window.location.reload();
      }
    }
    pulling = false;
  });
}

// ============================================
// Initialize
// ============================================

document.addEventListener('DOMContentLoaded', () => {
  initFlashMessages();
  initNotifications();
  initSidebar();
  initTabs();
});
