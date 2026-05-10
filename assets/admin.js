document.addEventListener('DOMContentLoaded', function () {

  function ajax(action, data, cb) {
    var fd = new FormData();
    fd.append('action', action);
    fd.append('nonce', RR.nonce);
    for (var k in data) fd.append(k, data[k]);
    fetch(RR.ajax_url, { method: 'POST', body: fd })
      .then(function(r) { return r.json(); })
      .then(cb)
      .catch(function() { cb({ success: false, data: 'Erreur réseau.' }); });
  }
  function showErr(id, msg) { var el = document.getElementById(id); if (el) { el.textContent = msg; el.style.display = 'block'; } }
  function hideErr(id) { var el = document.getElementById(id); if (el) el.style.display = 'none'; }
  function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

  /* ── LOGIN ── */
  var loginBtn = document.getElementById('rr-login-btn');
  if (loginBtn) {
    var toggle = document.getElementById('rr-pass-toggle');
    if (toggle) toggle.addEventListener('click', function() {
      var inp = document.getElementById('rr-password');
      var s = document.getElementById('rr-eye-show'), h = document.getElementById('rr-eye-hide');
      var isPass = inp.type === 'password';
      inp.type = isPass ? 'text' : 'password';
      s.style.display = isPass ? 'none' : '';
      h.style.display = isPass ? '' : 'none';
    });
    ['rr-username','rr-password'].forEach(function(id) {
      var el = document.getElementById(id);
      if (el) el.addEventListener('keydown', function(e) { if (e.key === 'Enter') loginBtn.click(); });
    });
    loginBtn.addEventListener('click', function() {
      var u = document.getElementById('rr-username').value.trim();
      var p = document.getElementById('rr-password').value;
      hideErr('rr-login-error');
      if (!u || !p) { showErr('rr-login-error', 'Veuillez renseigner vos identifiants.'); return; }
      loginBtn.disabled = true;
      document.getElementById('rr-login-text').style.display = 'none';
      document.getElementById('rr-login-loader').style.display = 'inline';
      ajax('rr_login', { username: u, password: p }, function(res) {
        if (res.success) window.location.reload();
        else {
          showErr('rr-login-error', res.data || 'Identifiants incorrects.');
          loginBtn.disabled = false;
          document.getElementById('rr-login-text').style.display = 'inline';
          document.getElementById('rr-login-loader').style.display = 'none';
        }
      });
    });
    return;
  }

  /* ── DASHBOARD ── */
  if (!document.querySelector('.rr-dash')) return;

  var allResa = [], allUsers = [], curFilter = 'all', curSearch = '';

  document.querySelectorAll('.rr-nav-item').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.rr-nav-item').forEach(function(b) { b.classList.remove('active'); });
      document.querySelectorAll('.rr-panel').forEach(function(p) { p.classList.remove('active'); });
      btn.classList.add('active');
      document.getElementById('panel-' + btn.dataset.panel).classList.add('active');
      if (btn.dataset.panel === 'team') loadUsers();
    });
  });

  document.getElementById('rr-logout').addEventListener('click', function() {
    ajax('rr_logout', {}, function() { window.location.reload(); });
  });

  /* RESERVATIONS */
  function loadResa() {
    ajax('rr_get_reservations', {}, function(res) {
      if (res.success) { allResa = res.data || []; updateStats(); renderResa(); }
    });
  }
  function updateStats() {
    var today = new Date().toISOString().split('T')[0];
    var pending = allResa.filter(function(r) { return r.status === 'pending'; }).length;
    var confirmed = allResa.filter(function(r) { return r.status === 'confirmed'; }).length;
    var todayCnt = allResa.filter(function(r) { return r.date === today; }).length;
    document.getElementById('s-total').textContent = allResa.length;
    document.getElementById('s-pending').textContent = pending;
    document.getElementById('s-confirmed').textContent = confirmed;
    document.getElementById('s-today').textContent = todayCnt;
    var badge = document.getElementById('rr-pending-count');
    badge.textContent = pending;
    badge.classList.toggle('visible', pending > 0);
  }
  function renderResa() {
    var list = document.getElementById('rr-resa-list');
    var q = curSearch.toLowerCase();
    var filtered = allResa.filter(function(r) {
      var f = curFilter === 'all' || r.status === curFilter;
      var s = !q || r.name.toLowerCase().includes(q) || r.email.toLowerCase().includes(q) || r.phone.includes(q);
      return f && s;
    });
    filtered.sort(function(a, b) { return (a.date + a.time).localeCompare(b.date + b.time); });
    if (!filtered.length) { list.innerHTML = '<div class="rr-empty">Aucune réservation.</div>'; return; }

    var labels = { pending: 'En attente', confirmed: 'Confirmée', cancelled: 'Annulée' };
    list.innerHTML = filtered.map(function(r) {
      var d = new Date(r.date + 'T12:00:00');
      var dateStr = d.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'long', year: 'numeric' });
      var btns = '';
      if (r.status === 'pending') btns = '<button class="rr-action-btn confirm" data-id="' + r.id + '" data-status="confirmed">✓ Confirmer</button><button class="rr-action-btn cancel" data-id="' + r.id + '" data-status="cancelled">✗ Refuser</button>';
      else if (r.status === 'confirmed') btns = '<button class="rr-action-btn cancel" data-id="' + r.id + '" data-status="cancelled">Annuler</button>';
      else if (r.status === 'cancelled') btns = '<button class="rr-action-btn confirm" data-id="' + r.id + '" data-status="pending">Remettre en attente</button>';

      return '<div class="rr-card ' + r.status + '">' +
        '<div>' +
          '<div class="rr-card-name">' + esc(r.name) + '</div>' +
          '<div class="rr-card-meta">' +
            '<span>📅 ' + dateStr + '</span>' +
            '<span>🕐 ' + esc(r.time) + '</span>' +
            '<span>👥 ' + esc(r.guests) + ' couvert' + (parseInt(r.guests) > 1 ? 's' : '') + '</span>' +
            '<span>📞 ' + esc(r.phone) + '</span>' +
            '<span>✉️ ' + esc(r.email) + '</span>' +
          '</div>' +
          (r.notes ? '<div class="rr-card-notes">' + esc(r.notes) + '</div>' : '') +
        '</div>' +
        '<div class="rr-card-right">' +
          '<span class="rr-pill ' + r.status + '">' + labels[r.status] + '</span>' +
          '<div class="rr-card-btns">' + btns + '</div>' +
        '</div>' +
      '</div>';
    }).join('');

    list.querySelectorAll('.rr-action-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        btn.disabled = true;
        ajax('rr_update_status', { id: btn.dataset.id, status: btn.dataset.status }, function(res) {
          if (res.success) loadResa();
          else { alert('Erreur : ' + res.data); btn.disabled = false; }
        });
      });
    });
  }
  document.querySelectorAll('.rr-filter').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.rr-filter').forEach(function(b) { b.classList.remove('active'); });
      btn.classList.add('active');
      curFilter = btn.dataset.filter;
      renderResa();
    });
  });
  var search = document.getElementById('rr-search');
  if (search) search.addEventListener('input', function() { curSearch = this.value; renderResa(); });

  /* TEAM */
  function loadUsers() {
    ajax('rr_get_users', {}, function(res) { if (res.success) { allUsers = res.data || []; renderUsers(); } });
  }
  function renderUsers() {
    var list = document.getElementById('rr-users-list');
    if (!list) return;
    if (!allUsers.length) { list.innerHTML = '<div class="rr-empty">Aucun compte.</div>'; return; }
    list.innerHTML = allUsers.map(function(u) {
      var initials = (u.full_name||'??').split(' ').map(function(w) { return w[0]; }).join('').substring(0,2).toUpperCase();
      return '<div class="rr-user-card">' +
        '<div class="rr-user-card-left">' +
          '<div class="rr-user-avatar ' + u.role + '">' + initials + '</div>' +
          '<div>' +
            '<div class="rr-user-card-name">' + esc(u.full_name) + '</div>' +
            '<div class="rr-user-card-meta">@' + esc(u.username) + ' · <span class="rr-role-badge ' + u.role + '">' + (u.role === 'manager' ? 'Manager' : 'Employé') + '</span></div>' +
          '</div>' +
        '</div>' +
        '<button class="rr-delete-btn" data-id="' + u.id + '">Supprimer</button>' +
      '</div>';
    }).join('');
    list.querySelectorAll('.rr-delete-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        if (!confirm('Supprimer ce compte ?')) return;
        ajax('rr_delete_user', { id: btn.dataset.id }, function(res) {
          if (res.success) loadUsers();
          else alert('Erreur : ' + res.data);
        });
      });
    });
  }
  var addBtn = document.getElementById('rr-add-user-btn');
  var addForm = document.getElementById('rr-add-user-form');
  if (addBtn) addBtn.addEventListener('click', function() { addForm.style.display = addForm.style.display === 'none' ? 'block' : 'none'; hideErr('rr-user-error'); });
  var cancelBtn = document.getElementById('rr-cancel-user');
  if (cancelBtn) cancelBtn.addEventListener('click', function() { addForm.style.display = 'none'; });
  var saveUser = document.getElementById('rr-save-user');
  if (saveUser) {
    saveUser.addEventListener('click', function() {
      hideErr('rr-user-error');
      var fn = document.getElementById('nu-fullname').value.trim();
      var un = document.getElementById('nu-username').value.trim();
      var pw = document.getElementById('nu-password').value;
      var rl = document.getElementById('nu-role').value;
      if (!fn || !un || !pw) { showErr('rr-user-error', 'Tous les champs sont requis.'); return; }
      saveUser.disabled = true;
      ajax('rr_add_user', { full_name: fn, username: un, password: pw, role: rl }, function(res) {
        saveUser.disabled = false;
        if (res.success) {
          addForm.style.display = 'none';
          ['nu-fullname','nu-username','nu-password'].forEach(function(id) { document.getElementById(id).value = ''; });
          loadUsers();
        } else showErr('rr-user-error', res.data || 'Erreur.');
      });
    });
  }

  /* ACCOUNT */
  var savePw = document.getElementById('rr-save-pw');
  if (savePw) {
    savePw.addEventListener('click', function() {
      hideErr('rr-pw-error');
      document.getElementById('rr-pw-success').style.display = 'none';
      var o = document.getElementById('pw-old').value;
      var n = document.getElementById('pw-new').value;
      var c = document.getElementById('pw-confirm').value;
      if (!o || !n) { showErr('rr-pw-error', 'Tous les champs sont requis.'); return; }
      if (n !== c)  { showErr('rr-pw-error', 'Les mots de passe ne correspondent pas.'); return; }
      savePw.disabled = true;
      ajax('rr_change_password', { old_password: o, new_password: n }, function(res) {
        savePw.disabled = false;
        if (res.success) {
          ['pw-old','pw-new','pw-confirm'].forEach(function(id) { document.getElementById(id).value = ''; });
          document.getElementById('rr-pw-success').style.display = 'block';
        } else showErr('rr-pw-error', res.data || 'Erreur.');
      });
    });
  }

  loadResa();
});
