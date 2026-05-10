<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$site_name = get_bloginfo( 'name' );
$base = rr_pwa_url();
$api  = home_url( '/wp-json/rr/v1' );
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, user-scalable=no">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Réservations">
<meta name="theme-color" content="#0F766E">
<link rel="manifest" href="<?= esc_url( $base ) ?>/manifest.json">
<link rel="apple-touch-icon" href="<?= esc_url( $base ) ?>/icon-192.png">
<title>Réservations — <?= esc_html( $site_name ) ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
html, body { height: 100%; overflow: hidden; }
body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
  background: #f1f5f9; color: #0f172a; user-select: none;
  padding: env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left);
}
#app { height: 100vh; display: flex; flex-direction: column; }

/* ── LOGIN ── */
.login-screen { flex: 1; display: flex; align-items: center; justify-content: center; padding: 24px; background: linear-gradient(135deg, #0F172A 0%, #134E4A 100%); }
.login-card { background: #fff; border-radius: 20px; padding: 40px 32px; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
.login-logo { width: 72px; height: 72px; background: #0F766E; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; color: #fff; }
.login-title { font-size: 24px; font-weight: 700; text-align: center; color: #0f172a; margin-bottom: 6px; letter-spacing: -.4px; }
.login-sub { font-size: 14px; color: #64748b; text-align: center; margin-bottom: 28px; }
.login-error { background: #fef2f2; color: #b91c1c; padding: 11px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; display: none; }

.field { margin-bottom: 16px; }
.field label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 7px; }
.field input { width: 100%; padding: 14px 16px; font-size: 16px; border: 1.5px solid #e2e8f0; border-radius: 12px; background: #fff; outline: none; font-family: inherit; transition: border-color .15s; -webkit-appearance: none; }
.field input:focus { border-color: #0F766E; }
.btn { width: 100%; padding: 15px; font-size: 16px; font-weight: 700; background: #0F766E; color: #fff; border: none; border-radius: 12px; cursor: pointer; font-family: inherit; transition: all .15s; }
.btn:active { transform: scale(.98); }
.btn:disabled { opacity: .6; }

/* ── HEADER ── */
.header { background: #0f172a; color: #fff; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.header-left { display: flex; align-items: center; gap: 10px; min-width: 0; }
.header-title { font-size: 15px; font-weight: 700; letter-spacing: -.2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.header-sub { font-size: 11px; color: rgba(255,255,255,.6); }
.connection-dot { width: 8px; height: 8px; border-radius: 50%; background: #22c55e; flex-shrink: 0; }
.connection-dot.offline { background: #ef4444; }
.icon-btn { width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,.08); color: #fff; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: background .15s; }
.icon-btn:active { background: rgba(255,255,255,.18); }
.icon-btn.alert-on { color: #fbbf24; }

/* ── STATS BAR ── */
.stats-bar { background: #fff; padding: 14px 20px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; border-bottom: 1px solid #e2e8f0; }
.stat { text-align: center; padding: 6px 4px; }
.stat-num { font-size: 22px; font-weight: 700; color: #0f172a; line-height: 1; letter-spacing: -.5px; }
.stat-label { font-size: 10px; color: #94a3b8; text-transform: uppercase; letter-spacing: .3px; margin-top: 4px; font-weight: 600; }
.stat.pending .stat-num   { color: #d97706; }
.stat.confirmed .stat-num { color: #059669; }
.stat.today .stat-num     { color: #0F766E; }

/* ── FILTERS ── */
.filters { background: #fff; padding: 0 16px 12px; display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none; }
.filters::-webkit-scrollbar { display: none; }
.filter { padding: 8px 16px; font-size: 13px; font-weight: 600; border: 1.5px solid #e2e8f0; border-radius: 999px; background: #fff; color: #64748b; white-space: nowrap; cursor: pointer; font-family: inherit; transition: all .15s; }
.filter.active { background: #0f172a; color: #fff; border-color: #0f172a; }
.filter .count { background: rgba(255,255,255,.2); padding: 1px 7px; border-radius: 10px; margin-left: 5px; font-size: 11px; }
.filter:not(.active) .count { background: #f1f5f9; color: #475569; }

/* ── LIST ── */
.list { flex: 1; overflow-y: auto; padding: 12px 16px 80px; -webkit-overflow-scrolling: touch; }
.day-header { font-size: 12px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .5px; margin: 16px 4px 10px; }
.day-header:first-child { margin-top: 0; }

.card { background: #fff; border: 1.5px solid #e2e8f0; border-left: 5px solid #cbd5e1; border-radius: 14px; padding: 16px; margin-bottom: 10px; transition: all .15s; }
.card.pending   { border-left-color: #f59e0b; background: #fffbeb; }
.card.confirmed { border-left-color: #10b981; }
.card.cancelled { border-left-color: #ef4444; opacity: .55; }
.card.new { animation: slideIn .4s ease-out; }
@keyframes slideIn { from { transform: translateX(20px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

.card-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; gap: 10px; }
.card-name { font-size: 17px; font-weight: 700; color: #0f172a; }
.card-time { font-size: 14px; font-weight: 700; color: #0F766E; padding: 4px 10px; background: #f0fdfa; border-radius: 8px; white-space: nowrap; flex-shrink: 0; }
.pending .card-time { background: #fef3c7; color: #92400e; }
.cancelled .card-time { background: #f1f5f9; color: #64748b; }

.card-meta { display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 13px; color: #475569; margin-bottom: 8px; }
.card-meta span { display: flex; align-items: center; gap: 4px; }
.card-notes { font-size: 13px; color: #64748b; font-style: italic; padding-top: 8px; border-top: 1px solid #e2e8f0; }
.card-notes::before { content: '💬 '; }

.card-actions { display: flex; gap: 8px; margin-top: 12px; }
.action-btn { flex: 1; padding: 11px; font-size: 14px; font-weight: 700; border-radius: 10px; cursor: pointer; border: none; font-family: inherit; transition: all .1s; min-height: 44px; }
.action-btn:active { transform: scale(.97); }
.confirm-btn { background: #10b981; color: #fff; }
.confirm-btn:active { background: #059669; }
.refuse-btn { background: #fff; color: #b91c1c; border: 1.5px solid #fca5a5; }
.refuse-btn:active { background: #fee2e2; }
.cancel-btn { background: #fff; color: #64748b; border: 1.5px solid #cbd5e1; }
.cancel-btn:active { background: #f1f5f9; }

.empty { text-align: center; padding: 60px 24px; color: #94a3b8; }
.empty-icon { font-size: 56px; margin-bottom: 12px; opacity: .4; }
.empty-text { font-size: 15px; }

.loading { text-align: center; padding: 40px; color: #94a3b8; font-size: 14px; }

/* ── BOTTOM BANNER ── */
.banner { position: fixed; bottom: 16px; left: 16px; right: 16px; background: #0f172a; color: #fff; padding: 12px 16px; border-radius: 12px; font-size: 13px; box-shadow: 0 10px 30px rgba(0,0,0,.2); display: flex; align-items: center; gap: 10px; transform: translateY(100px); transition: transform .3s; z-index: 100; }
.banner.visible { transform: translateY(0); }
.banner.error { background: #b91c1c; }
.banner.success { background: #059669; }

/* ── INSTALL HINT ── */
.install-hint { background: #fef3c7; color: #78350f; padding: 12px 16px; font-size: 13px; text-align: center; border-bottom: 1px solid #fde68a; cursor: pointer; }
.install-hint:active { background: #fde68a; }

/* ── MODAL ── */
.modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.7); display: none; align-items: center; justify-content: center; z-index: 200; padding: 20px; }
.modal-overlay.visible { display: flex; }
.modal { background: #fff; border-radius: 16px; padding: 24px; max-width: 400px; width: 100%; }
.modal-title { font-size: 17px; font-weight: 700; margin-bottom: 8px; }
.modal-text { font-size: 14px; color: #64748b; line-height: 1.5; margin-bottom: 18px; }
.modal-actions { display: flex; gap: 8px; }
.modal-btn { flex: 1; padding: 12px; border-radius: 10px; font-size: 14px; font-weight: 700; border: none; cursor: pointer; font-family: inherit; }
.modal-btn.primary { background: #0F766E; color: #fff; }
.modal-btn.danger  { background: #ef4444; color: #fff; }
.modal-btn.cancel  { background: #f1f5f9; color: #475569; }

@media (min-width: 900px) {
  .list { padding: 20px 24px 80px; max-width: 900px; margin: 0 auto; width: 100%; }
  .stats-bar { padding: 18px 24px; }
}
</style>
</head>
<body>

<div id="app">
  <!-- LOGIN SCREEN -->
  <div class="login-screen" id="login-screen">
    <div class="login-card">
      <div class="login-logo">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M12 2C6.5 2 4 6 4 10c0 3 2 5 2 5h12s2-2 2-5c0-4-2.5-8-8-8z"/>
          <path d="M8 22h8M12 15v7"/>
        </svg>
      </div>
      <h1 class="login-title">Espace équipe</h1>
      <p class="login-sub"><?= esc_html( $site_name ) ?></p>

      <div class="login-error" id="login-error"></div>

      <div class="field">
        <label for="login-username">Identifiant</label>
        <input type="text" id="login-username" autocomplete="username" autocapitalize="off" autocorrect="off">
      </div>
      <div class="field">
        <label for="login-password">Mot de passe</label>
        <input type="password" id="login-password" autocomplete="current-password">
      </div>
      <button class="btn" id="login-btn">Se connecter</button>
    </div>
  </div>

  <!-- DASHBOARD -->
  <div id="dashboard" style="display:none; height:100%; display:flex; flex-direction:column;">
    <header class="header">
      <div class="header-left">
        <span class="connection-dot" id="conn-dot"></span>
        <div>
          <div class="header-title" id="user-name">—</div>
          <div class="header-sub" id="user-role">—</div>
        </div>
      </div>
      <div style="display:flex; gap:6px;">
        <button class="icon-btn" id="alert-toggle" title="Activer/désactiver les sons">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5L6 9H2v6h4l5 4V5z"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>
        </button>
        <button class="icon-btn" id="refresh-btn" title="Actualiser">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
        </button>
        <button class="icon-btn" id="logout-btn" title="Déconnexion">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </button>
      </div>
    </header>

    <div class="install-hint" id="install-hint" style="display:none;">📲 Installer cette app sur l'écran d'accueil</div>

    <div class="stats-bar">
      <div class="stat"><div class="stat-num" id="s-total">0</div><div class="stat-label">Total</div></div>
      <div class="stat pending"><div class="stat-num" id="s-pending">0</div><div class="stat-label">En attente</div></div>
      <div class="stat confirmed"><div class="stat-num" id="s-confirmed">0</div><div class="stat-label">Confirmées</div></div>
      <div class="stat today"><div class="stat-num" id="s-today">0</div><div class="stat-label">Aujourd'hui</div></div>
    </div>

    <div class="filters">
      <button class="filter active" data-filter="all">Toutes <span class="count" id="c-all">0</span></button>
      <button class="filter" data-filter="pending">À traiter <span class="count" id="c-pending">0</span></button>
      <button class="filter" data-filter="confirmed">Confirmées <span class="count" id="c-confirmed">0</span></button>
      <button class="filter" data-filter="today">Aujourd'hui <span class="count" id="c-today">0</span></button>
      <button class="filter" data-filter="upcoming">À venir <span class="count" id="c-upcoming">0</span></button>
    </div>

    <div class="list" id="list">
      <div class="loading">Chargement…</div>
    </div>
  </div>

  <!-- MODAL CONFIRM -->
  <div class="modal-overlay" id="modal">
    <div class="modal">
      <div class="modal-title" id="modal-title">Confirmer ?</div>
      <div class="modal-text" id="modal-text">Voulez-vous continuer ?</div>
      <div class="modal-actions">
        <button class="modal-btn cancel" id="modal-cancel">Annuler</button>
        <button class="modal-btn primary" id="modal-ok">Confirmer</button>
      </div>
    </div>
  </div>

  <!-- BANNER -->
  <div class="banner" id="banner"><span id="banner-text"></span></div>
</div>

<script>
const API     = '<?= esc_js( $api ) ?>';
const APP_URL = '<?= esc_js( $base ) ?>';

// ─────────── STATE ───────────
const state = {
  token: localStorage.getItem('rr_token') || null,
  user: JSON.parse(localStorage.getItem('rr_user') || 'null'),
  reservations: [],
  filter: 'all',
  alerts: localStorage.getItem('rr_alerts') !== '0',
  lastLatest: 0,
  lastPending: 0,
  online: navigator.onLine,
};

const $ = id => document.getElementById(id);

// ─────────── HELPERS ───────────
function api(path, opts = {}) {
  const headers = { 'Content-Type': 'application/json', ...(opts.headers || {}) };
  if (state.token) headers['Authorization'] = 'Bearer ' + state.token;
  return fetch(API + path, { ...opts, headers })
    .then(async r => {
      const j = await r.json().catch(() => ({}));
      if (!r.ok) throw j;
      return j;
    });
}

function showBanner(text, type = '') {
  $('banner-text').textContent = text;
  $('banner').className = 'banner visible ' + type;
  setTimeout(() => $('banner').classList.remove('visible'), 3500);
}

function setConnDot() {
  $('conn-dot').classList.toggle('offline', !state.online);
}
window.addEventListener('online',  () => { state.online = true;  setConnDot(); loadReservations(); });
window.addEventListener('offline', () => { state.online = false; setConnDot(); });

// ─────────── SOUND ───────────
let audio = null;
function playBeep() {
  if (!state.alerts) return;
  try {
    // Beep généré (Web Audio) — pas besoin de fichier MP3
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const o = ctx.createOscillator();
    const g = ctx.createGain();
    o.connect(g); g.connect(ctx.destination);
    o.type = 'sine'; o.frequency.value = 880;
    g.gain.setValueAtTime(0, ctx.currentTime);
    g.gain.linearRampToValueAtTime(.3, ctx.currentTime + .02);
    g.gain.linearRampToValueAtTime(0, ctx.currentTime + .25);
    o.start(); o.stop(ctx.currentTime + .3);
    // double beep
    setTimeout(() => {
      const o2 = ctx.createOscillator();
      const g2 = ctx.createGain();
      o2.connect(g2); g2.connect(ctx.destination);
      o2.type = 'sine'; o2.frequency.value = 1100;
      g2.gain.setValueAtTime(0, ctx.currentTime);
      g2.gain.linearRampToValueAtTime(.3, ctx.currentTime + .02);
      g2.gain.linearRampToValueAtTime(0, ctx.currentTime + .25);
      o2.start(); o2.stop(ctx.currentTime + .3);
    }, 280);
  } catch (e) {}
  if ('vibrate' in navigator) navigator.vibrate([100, 50, 100]);
}

// ─────────── LOGIN ───────────
$('login-btn').addEventListener('click', doLogin);
['login-username','login-password'].forEach(id => {
  $(id).addEventListener('keydown', e => { if (e.key === 'Enter') doLogin(); });
});

function doLogin() {
  const username = $('login-username').value.trim();
  const password = $('login-password').value;
  $('login-error').style.display = 'none';
  if (!username || !password) {
    $('login-error').textContent = 'Veuillez remplir les deux champs.';
    $('login-error').style.display = 'block';
    return;
  }
  $('login-btn').disabled = true;
  $('login-btn').textContent = 'Connexion…';

  api('/login', {
    method: 'POST',
    body: JSON.stringify({
      username, password,
      device: (navigator.userAgentData?.platform || navigator.platform || 'Web') + ' · ' + new Date().toLocaleDateString()
    })
  })
  .then(r => {
    state.token = r.token;
    state.user  = r.user;
    localStorage.setItem('rr_token', r.token);
    localStorage.setItem('rr_user',  JSON.stringify(r.user));
    showDashboard();
  })
  .catch(err => {
    $('login-error').textContent = err.message || 'Identifiants incorrects.';
    $('login-error').style.display = 'block';
    $('login-btn').disabled = false;
    $('login-btn').textContent = 'Se connecter';
  });
}

// ─────────── DASHBOARD ───────────
function showDashboard() {
  $('login-screen').style.display = 'none';
  $('dashboard').style.display = 'flex';
  $('user-name').textContent = state.user.full_name;
  $('user-role').textContent = state.user.role === 'manager' ? 'Manager' : 'Employé';
  setConnDot();
  updateAlertBtn();
  loadReservations();
  startPolling();
  setupPushPermission();
}

$('logout-btn').addEventListener('click', () => {
  showModal('Déconnexion', 'Voulez-vous vraiment vous déconnecter ?', () => {
    api('/logout', { method: 'POST' }).catch(() => {});
    localStorage.removeItem('rr_token');
    localStorage.removeItem('rr_user');
    state.token = null; state.user = null;
    location.reload();
  });
});

$('refresh-btn').addEventListener('click', () => {
  $('refresh-btn').style.transform = 'rotate(360deg)';
  $('refresh-btn').style.transition = 'transform .5s';
  setTimeout(() => { $('refresh-btn').style.transition = ''; $('refresh-btn').style.transform = ''; }, 500);
  loadReservations();
});

$('alert-toggle').addEventListener('click', () => {
  state.alerts = !state.alerts;
  localStorage.setItem('rr_alerts', state.alerts ? '1' : '0');
  updateAlertBtn();
  if (state.alerts) playBeep();
});

function updateAlertBtn() {
  $('alert-toggle').classList.toggle('alert-on', state.alerts);
  $('alert-toggle').innerHTML = state.alerts
    ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5L6 9H2v6h4l5 4V5z"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"/></svg>'
    : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="23" y1="9" x2="17" y2="15"/><line x1="17" y1="9" x2="23" y2="15"/><path d="M11 5L6 9H2v6h4l5 4V5z"/></svg>';
}

// ─────────── DATA ───────────
function loadReservations() {
  api('/reservations')
    .then(r => {
      state.reservations = r.reservations || [];
      render();
    })
    .catch(err => {
      if (err.code === 'invalid_token' || err.code === 'no_token') {
        localStorage.removeItem('rr_token');
        location.reload();
      } else {
        showBanner('Erreur de connexion', 'error');
      }
    });
}

function render() {
  const today = new Date().toISOString().split('T')[0];
  const list = state.reservations;

  // Stats
  $('s-total').textContent     = list.length;
  $('s-pending').textContent   = list.filter(r => r.status === 'pending').length;
  $('s-confirmed').textContent = list.filter(r => r.status === 'confirmed').length;
  $('s-today').textContent     = list.filter(r => r.date === today && r.status !== 'cancelled').length;

  // Filter counts
  $('c-all').textContent       = list.length;
  $('c-pending').textContent   = list.filter(r => r.status === 'pending').length;
  $('c-confirmed').textContent = list.filter(r => r.status === 'confirmed').length;
  $('c-today').textContent     = list.filter(r => r.date === today).length;
  $('c-upcoming').textContent  = list.filter(r => r.date > today && r.status === 'confirmed').length;

  // Filter
  let filtered = list;
  if (state.filter === 'pending')   filtered = list.filter(r => r.status === 'pending');
  if (state.filter === 'confirmed') filtered = list.filter(r => r.status === 'confirmed');
  if (state.filter === 'today')     filtered = list.filter(r => r.date === today);
  if (state.filter === 'upcoming')  filtered = list.filter(r => r.date > today && r.status === 'confirmed');

  filtered.sort((a, b) => (a.date + a.time).localeCompare(b.date + b.time));

  if (!filtered.length) {
    $('list').innerHTML = '<div class="empty"><div class="empty-icon">📭</div><div class="empty-text">Aucune réservation</div></div>';
    return;
  }

  // Group by day
  let html = '';
  let prevDate = null;
  filtered.forEach(r => {
    if (r.date !== prevDate) {
      const d = new Date(r.date + 'T12:00:00');
      const isToday = r.date === today;
      const dStr = isToday ? "Aujourd'hui · " + d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long' })
                           : d.toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
      html += '<div class="day-header">' + dStr + '</div>';
      prevDate = r.date;
    }
    html += renderCard(r);
  });
  $('list').innerHTML = html;

  // Bind buttons
  document.querySelectorAll('[data-action]').forEach(btn => {
    btn.addEventListener('click', () => handleAction(btn));
  });
}

function renderCard(r) {
  const guests = parseInt(r.guests) || 0;
  const guestStr = guests + ' couvert' + (guests > 1 ? 's' : '');
  const initials = r.name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
  let actions = '';
  if (r.status === 'pending') {
    actions = '<div class="card-actions">' +
      '<button class="action-btn refuse-btn" data-action="cancelled" data-id="' + r.id + '" data-name="' + escAttr(r.name) + '">✗ Refuser</button>' +
      '<button class="action-btn confirm-btn" data-action="confirmed" data-id="' + r.id + '" data-name="' + escAttr(r.name) + '">✓ Confirmer</button>' +
    '</div>';
  } else if (r.status === 'confirmed') {
    actions = '<div class="card-actions">' +
      '<button class="action-btn cancel-btn" data-action="cancelled" data-id="' + r.id + '" data-name="' + escAttr(r.name) + '">Annuler la réservation</button>' +
    '</div>';
  } else if (r.status === 'cancelled') {
    actions = '<div class="card-actions">' +
      '<button class="action-btn cancel-btn" data-action="pending" data-id="' + r.id + '" data-name="' + escAttr(r.name) + '">↻ Remettre en attente</button>' +
    '</div>';
  }
  return '<div class="card ' + r.status + '" data-id="' + r.id + '">' +
    '<div class="card-top">' +
      '<div class="card-name">' + esc(r.name) + '</div>' +
      '<div class="card-time">' + esc(r.time) + '</div>' +
    '</div>' +
    '<div class="card-meta">' +
      '<span>👥 ' + guestStr + '</span>' +
      '<span>📞 <a href="tel:' + escAttr(r.phone) + '" style="color:inherit; text-decoration:none;">' + esc(r.phone) + '</a></span>' +
      '<span>✉️ <a href="mailto:' + escAttr(r.email) + '" style="color:inherit; text-decoration:none;">' + esc(r.email) + '</a></span>' +
    '</div>' +
    (r.notes ? '<div class="card-notes">' + esc(r.notes) + '</div>' : '') +
    actions +
  '</div>';
}

function handleAction(btn) {
  const id = btn.dataset.id;
  const status = btn.dataset.action;
  const name = btn.dataset.name;
  let title, text;
  if (status === 'confirmed')      { title = 'Confirmer ?'; text = `Confirmer la réservation de ${name} ? Un message sera envoyé au client.`; }
  else if (status === 'cancelled') { title = 'Refuser/Annuler ?'; text = `Annuler la réservation de ${name} ? Un message sera envoyé au client.`; }
  else { title = 'Remettre en attente ?'; text = ''; }

  showModal(title, text, () => {
    btn.disabled = true;
    btn.textContent = '...';
    api('/reservations/' + id + '/status', {
      method: 'POST',
      body: JSON.stringify({ status })
    })
    .then(() => {
      showBanner(status === 'confirmed' ? '✓ Réservation confirmée' : status === 'cancelled' ? '✗ Réservation annulée' : '↻ Remise en attente', 'success');
      loadReservations();
    })
    .catch(err => {
      showBanner('Erreur : ' + (err.message || 'inconnue'), 'error');
      btn.disabled = false;
    });
  });
}

// Filters
document.querySelectorAll('.filter').forEach(f => {
  f.addEventListener('click', () => {
    document.querySelectorAll('.filter').forEach(b => b.classList.remove('active'));
    f.classList.add('active');
    state.filter = f.dataset.filter;
    render();
  });
});

// ─────────── POLLING (auto-refresh) ───────────
function startPolling() {
  setInterval(() => {
    if (!state.online) return;
    api('/poll')
      .then(r => {
        // Nouvelle réservation détectée
        if (state.lastLatest && r.latest > state.lastLatest) {
          loadReservations();
          if (r.pending > state.lastPending) {
            playBeep();
            showBanner('🔔 Nouvelle réservation reçue', 'success');
            try {
              if (Notification.permission === 'granted' && document.hidden) {
                new Notification('Nouvelle réservation', { body: 'Une nouvelle demande vient d\'arriver.', icon: APP_URL + '/icon-192.png' });
              }
            } catch (e) {}
          }
        }
        state.lastLatest  = r.latest;
        state.lastPending = r.pending;
      })
      .catch(() => {});
  }, 15000); // toutes les 15 secondes
}

function setupPushPermission() {
  if ('Notification' in window && Notification.permission === 'default') {
    setTimeout(() => Notification.requestPermission(), 3000);
  }
}

// ─────────── MODAL ───────────
let modalOk = null;
function showModal(title, text, onOk) {
  $('modal-title').textContent = title;
  $('modal-text').textContent  = text;
  modalOk = onOk;
  $('modal').classList.add('visible');
}
$('modal-cancel').addEventListener('click', () => $('modal').classList.remove('visible'));
$('modal-ok').addEventListener('click', () => {
  $('modal').classList.remove('visible');
  if (modalOk) modalOk();
});

// ─────────── INSTALL HINT ───────────
let installEvent = null;
window.addEventListener('beforeinstallprompt', e => {
  e.preventDefault();
  installEvent = e;
  $('install-hint').style.display = 'block';
});
$('install-hint').addEventListener('click', async () => {
  if (installEvent) {
    installEvent.prompt();
    const r = await installEvent.userChoice;
    if (r.outcome === 'accepted') $('install-hint').style.display = 'none';
  }
});

// iOS hint
if (/iPhone|iPad|iPod/.test(navigator.userAgent) && !window.matchMedia('(display-mode: standalone)').matches && !window.navigator.standalone) {
  setTimeout(() => {
    if ($('install-hint').style.display === 'none') {
      $('install-hint').textContent = '📲 Pour installer : appuyez sur Partager → "Sur l\'écran d\'accueil"';
      $('install-hint').style.display = 'block';
    }
  }, 8000);
}

// ─────────── HELPERS ───────────
function esc(s) {
  return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(s) { return esc(s); }

// ─────────── INIT ───────────
if (state.token && state.user) {
  showDashboard();
} else {
  $('login-screen').style.display = 'flex';
  setTimeout(() => $('login-username').focus(), 100);
}

// Service Worker
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register(APP_URL + '/sw.js', { scope: '/app/' })
      .catch(e => console.log('SW failed', e));
  });
}
</script>

</body>
</html>
