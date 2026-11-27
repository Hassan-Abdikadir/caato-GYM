(function () {
  // 1. 2026 Animated Theme Switcher (Cyber Dark <-> Neo Light) with Live Chart.js Sync
  const tb = document.getElementById('themeBtn');
  const tLbl = document.getElementById('themeLabel');

  const syncThemeUI = () => {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    if (tLbl) tLbl.textContent = isDark ? 'DARK' : 'LIGHT';
    if (window.Chart) {
      Chart.defaults.color = isDark ? '#94a3b8' : '#475569';
      Chart.defaults.borderColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(15,23,42,0.07)';
      Object.values(Chart.instances || {}).forEach(c => {
        try {
          if (c.options && c.options.scales) {
            Object.values(c.options.scales).forEach(sc => {
              if (sc.ticks) sc.ticks.color = Chart.defaults.color;
              if (sc.grid) sc.grid.color = Chart.defaults.borderColor;
            });
          }
          c.update('none');
        } catch (e) {}
      });
    }
  };
  syncThemeUI();

  window.setCaatoTheme = function (mode) {
    document.documentElement.classList.add('theme-transition');
    document.documentElement.setAttribute('data-bs-theme', mode);
    localStorage.setItem('caato-theme', mode);
    syncThemeUI();
    setTimeout(() => document.documentElement.classList.remove('theme-transition'), 420);
  };

  tb && tb.addEventListener('click', () => {
    const next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
    window.setCaatoTheme(next);
  });

  // 2. Desktop Sidebar Collapse & Mobile Sidebar Toggle
  if (localStorage.getItem('caato-sidebar-collapsed') === '1') {
    document.body.classList.add('sidebar-collapsed');
  }
  const cb = document.getElementById('collapseBtn');
  cb && cb.addEventListener('click', () => {
    document.body.classList.toggle('sidebar-collapsed');
    localStorage.setItem('caato-sidebar-collapsed', document.body.classList.contains('sidebar-collapsed') ? '1' : '0');
  });

  const mb = document.getElementById('menuBtn');
  const sb = document.getElementById('sidebar');
  if (mb && sb) {
    mb.addEventListener('click', e => {
      e.stopPropagation();
      sb.classList.toggle('open');
    });
    document.addEventListener('click', e => {
      if (sb.classList.contains('open') && !sb.contains(e.target) && !mb.contains(e.target)) {
        sb.classList.remove('open');
      }
    });
  }

  // 3. Live Digital Clock in Topbar
  const clockEl = document.getElementById('liveClock');
  if (clockEl) {
    const tick = () => {
      const d = new Date();
      clockEl.textContent = d.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit', second: '2-digit'});
    };
    tick();
    setInterval(tick, 1000);
  }

  // 4. Web Audio API Synthesizer for Biometric Door & Scanner Feedback
  window.caatoAudio = {
    play(type) {
      try {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) return;
        const ctx = new Ctx();
        const now = ctx.currentTime;
        if (type === 'unlock' || type === 'success') {
          [587.33, 880].forEach((freq, i) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(freq, now + i * 0.11);
            gain.gain.setValueAtTime(0.14, now + i * 0.11);
            gain.gain.exponentialRampToValueAtTime(0.001, now + i * 0.11 + 0.25);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now + i * 0.11);
            osc.stop(now + i * 0.11 + 0.26);
          });
        } else if (type === 'denied' || type === 'error') {
          [220, 185].forEach((freq, i) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(freq, now + i * 0.14);
            gain.gain.setValueAtTime(0.12, now + i * 0.14);
            gain.gain.exponentialRampToValueAtTime(0.001, now + i * 0.14 + 0.18);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now + i * 0.14);
            osc.stop(now + i * 0.14 + 0.19);
          });
        }
      } catch (e) {}
    }
  };

  // 5. Global Ctrl+K Command Palette
  const cmdModalEl = document.getElementById('cmdPaletteModal');
  const cmdInput = document.getElementById('cmdSearchInput');
  const cmdResults = document.getElementById('cmdSearchResults');
  if (cmdModalEl && cmdInput && cmdResults) {
    const cmdModal = new bootstrap.Modal(cmdModalEl);
    let cmdTimer = null;
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));

    document.addEventListener('keydown', e => {
      if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        cmdModal.show();
      }
    });
    cmdModalEl.addEventListener('shown.bs.modal', () => cmdInput.focus());

    cmdInput.addEventListener('input', () => {
      clearTimeout(cmdTimer);
      const q = cmdInput.value.trim();
      if (q.length < 2) {
        cmdResults.innerHTML = '';
        return;
      }
      cmdTimer = setTimeout(async () => {
        try {
          const res = await fetch(CAATO.api + '&action=search&q=' + encodeURIComponent(q));
          const j = await res.json();
          const list = j.results || [];
          if (!list.length) {
            cmdResults.innerHTML = '<div class="text-center text-muted py-3 small">No matching members found.</div>';
            return;
          }
          cmdResults.innerHTML = list.map(r => `
            <a href="${esc(r.profile)}" class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 border-bottom">
              <div class="d-flex align-items-center gap-2">
                <img src="${esc(r.photo)}" class="avatar" style="width:36px;height:36px">
                <div>
                  <div class="fw-bold small">${esc(r.name)} <span class="text-muted font-monospace">(${esc(r.code)})</span></div>
                  <small class="text-muted" style="font-size:0.74rem">${esc(r.phone)} · ${esc(r.reg_branch)}</small>
                </div>
              </div>
              <span class="badge text-bg-${r.allowed ? 'success' : 'danger'}">${esc(r.status)}</span>
            </a>
          `).join('');
        } catch (e) {}
      }, 220);
    });
  }

  // 6. Toasts
  document.querySelectorAll('#toasts .toast').forEach(t => new bootstrap.Toast(t).show());

  // 7. Confirmation dialogs
  const modalEl = document.getElementById('confirmModal');
  if (modalEl) {
    const modal = new bootstrap.Modal(modalEl);
    let action = null;
    document.addEventListener('click', e => {
      const el = e.target.closest('[data-confirm]');
      if (!el || el.dataset.confirmed) return;
      e.preventDefault();
      document.getElementById('confirmText').textContent = el.dataset.confirm;
      action = () => {
        if (el.tagName === 'A') {
          location.href = el.href;
        } else if (el.form) {
          (el.form.requestSubmit ? el.form.requestSubmit(el) : el.form.submit());
        }
      };
      el.dataset.confirmed = '';
      modal.show();
    });
    document.getElementById('confirmOk').addEventListener('click', () => {
      modal.hide();
      if (!action) return;
      const a = action;
      action = null;
      document.querySelectorAll('[data-confirm]').forEach(x => x.dataset.confirmed = '1');
      a();
    });
  }

  // 8. Global Chart Defaults
  if (window.Chart) {
    Chart.defaults.font.family = "'Outfit', 'Barlow', system-ui, sans-serif";
    syncThemeUI();
  }

  // 9. Payment modal helper
  document.addEventListener('click', e => {
    const b = e.target.closest('[data-pay]');
    if (!b) return;
    const f = document.getElementById('payForm');
    if (!f) return;
    f.membership_id.value = b.dataset.pay;
    f.amount.value = b.dataset.balance;
    f.amount.max = b.dataset.balance;
    document.getElementById('payWho').textContent = b.dataset.who + ' – balance ' + CAATO.currency + b.dataset.balance;
    new bootstrap.Modal(document.getElementById('payModal')).show();
  });
  // 10. Self-Service Password Update
  window.changeMyPassword = async function (e) {
    e.preventDefault();
    const msg = document.getElementById('cpMsg');
    const body = new URLSearchParams({
      old_password: document.getElementById('cpOld').value,
      new_password: document.getElementById('cpNew').value
    });
    const res = await fetch(CAATO.api + '&action=change_password', {
      method: 'POST',
      headers: {'X-CSRF-Token': CAATO.csrf, 'Content-Type': 'application/x-www-form-urlencoded'},
      body
    });
    const j = await res.json();
    msg.className = 'small mt-2 fw-bold text-' + (j.ok ? 'success' : 'danger');
    msg.textContent = j.message;
    if (j.ok) {
      document.getElementById('cpOld').value = '';
      document.getElementById('cpNew').value = '';
      setTimeout(() => bootstrap.Modal.getInstance(document.getElementById('myPwModal')).hide(), 1100);
    }
  };
})();

window.chartColors = ['#ff5722', '#e11d48', '#06b6d4', '#10b981', '#f59e0b', '#8b5cf6', '#64748b'];
