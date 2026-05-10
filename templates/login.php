<div class="rr-login-wrap">
  <div class="rr-login-card">
    <div class="rr-login-logo">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M12 2C6.5 2 4 6 4 10c0 3 2 5 2 5h12s2-2 2-5c0-4-2.5-8-8-8z"/>
        <path d="M8 22h8M12 15v7"/>
      </svg>
    </div>
    <h1 class="rr-login-title">Espace équipe</h1>
    <p class="rr-login-sub">Connectez-vous pour gérer les réservations</p>

    <div class="rr-alert rr-err" id="rr-login-error" style="display:none;"></div>

    <div class="rr-field">
      <label class="rr-lab" for="rr-username">Identifiant</label>
      <input class="rr-inp" type="text" id="rr-username" placeholder="manager" autocomplete="username" />
    </div>
    <div class="rr-field">
      <label class="rr-lab" for="rr-password">Mot de passe</label>
      <div class="rr-pass-wrap">
        <input class="rr-inp" type="password" id="rr-password" placeholder="••••••••" autocomplete="current-password" />
        <button type="button" class="rr-pass-toggle" id="rr-pass-toggle" aria-label="Afficher/masquer">
          <svg id="rr-eye-show" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          <svg id="rr-eye-hide" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
        </button>
      </div>
    </div>

    <button class="rr-submit" id="rr-login-btn" type="button">
      <span id="rr-login-text">Se connecter</span>
      <span id="rr-login-loader" style="display:none;">Connexion…</span>
    </button>

    <p class="rr-login-hint">Compte par défaut : <code>manager</code> / <code>Admin1234!</code></p>
  </div>
</div>
