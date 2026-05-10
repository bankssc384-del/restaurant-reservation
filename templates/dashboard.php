<?php
$user       = rr_current_user();
$is_manager = rr_is_manager();
$settings   = rr_settings();
?>
<div class="rr-dash">
  <aside class="rr-sidebar">
    <div class="rr-sb-brand">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M12 2C6.5 2 4 6 4 10c0 3 2 5 2 5h12s2-2 2-5c0-4-2.5-8-8-8z"/>
        <path d="M8 22h8M12 15v7"/>
      </svg>
      <span><?= esc_html( get_bloginfo('name') ) ?></span>
    </div>

    <nav class="rr-sb-nav">
      <button class="rr-nav-item active" data-panel="reservations">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span>Réservations</span>
        <span class="rr-badge" id="rr-pending-count">0</span>
      </button>
      <?php if ( $is_manager ) : ?>
      <button class="rr-nav-item" data-panel="team">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Équipe</span>
      </button>
      <?php endif; ?>
      <button class="rr-nav-item" data-panel="account">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span>Mon compte</span>
      </button>
    </nav>

    <div class="rr-sb-foot">
      <div class="rr-mode-tag <?= $settings['mode'] === 'auto' ? 'auto' : 'manual' ?>">
        <?= $settings['mode'] === 'auto' ? '⚡ Mode auto' : '👤 Mode manuel' ?>
      </div>
      <div class="rr-userbox">
        <div class="rr-avatar"><?= esc_html( strtoupper( substr( $user['full_name'], 0, 2 ) ) ) ?></div>
        <div>
          <div class="rr-uname"><?= esc_html( $user['full_name'] ) ?></div>
          <div class="rr-urole"><?= $is_manager ? 'Manager' : 'Employé' ?></div>
        </div>
      </div>
      <button class="rr-logout" id="rr-logout">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Déconnexion
      </button>
    </div>
  </aside>

  <main class="rr-main">

    <!-- RESERVATIONS -->
    <div class="rr-panel active" id="panel-reservations">
      <div class="rr-panel-h">
        <h2 class="rr-panel-title">Réservations</h2>
        <input class="rr-inp rr-search" type="text" id="rr-search" placeholder="Rechercher…" />
      </div>

      <div class="rr-stats">
        <div class="rr-stat"><span class="rr-stat-num" id="s-total">—</span><span class="rr-stat-l">Total</span></div>
        <div class="rr-stat rr-stat-pending"><span class="rr-stat-num" id="s-pending">—</span><span class="rr-stat-l">En attente</span></div>
        <div class="rr-stat rr-stat-confirmed"><span class="rr-stat-num" id="s-confirmed">—</span><span class="rr-stat-l">Confirmées</span></div>
        <div class="rr-stat"><span class="rr-stat-num" id="s-today">—</span><span class="rr-stat-l">Aujourd'hui</span></div>
      </div>

      <div class="rr-filters">
        <button class="rr-filter active" data-filter="all">Toutes</button>
        <button class="rr-filter" data-filter="pending">En attente</button>
        <button class="rr-filter" data-filter="confirmed">Confirmées</button>
        <button class="rr-filter" data-filter="cancelled">Annulées</button>
      </div>

      <div id="rr-resa-list" class="rr-list"><div class="rr-loading">Chargement…</div></div>
    </div>

    <!-- TEAM -->
    <?php if ( $is_manager ) : ?>
    <div class="rr-panel" id="panel-team">
      <div class="rr-panel-h">
        <h2 class="rr-panel-title">Gestion de l'équipe</h2>
        <button class="rr-submit rr-btn-sm" id="rr-add-user-btn">+ Ajouter un compte</button>
      </div>

      <div class="rr-form-card" id="rr-add-user-form" style="display:none;">
        <h3 class="rr-form-card-h">Nouveau compte</h3>
        <div class="rr-alert rr-err" id="rr-user-error" style="display:none;"></div>
        <div class="rr-form-card-grid">
          <div class="rr-field"><label class="rr-lab">Prénom et nom</label><input class="rr-inp" type="text" id="nu-fullname" placeholder="Jean Dupont" /></div>
          <div class="rr-field"><label class="rr-lab">Identifiant</label><input class="rr-inp" type="text" id="nu-username" placeholder="jean.dupont" /></div>
          <div class="rr-field"><label class="rr-lab">Mot de passe (8 car. min)</label><input class="rr-inp" type="password" id="nu-password" placeholder="••••••••" /></div>
          <div class="rr-field"><label class="rr-lab">Rôle</label>
            <select class="rr-inp" id="nu-role">
              <option value="employee">Employé</option>
              <option value="manager">Manager (accès complet)</option>
            </select>
          </div>
        </div>
        <div class="rr-form-card-actions">
          <button class="rr-submit rr-btn-sm" id="rr-save-user">Créer le compte</button>
          <button class="rr-back rr-btn-sm"   id="rr-cancel-user">Annuler</button>
        </div>
      </div>

      <div id="rr-users-list" class="rr-list"><div class="rr-loading">Chargement…</div></div>
    </div>
    <?php endif; ?>

    <!-- ACCOUNT -->
    <div class="rr-panel" id="panel-account">
      <div class="rr-panel-h"><h2 class="rr-panel-title">Mon compte</h2></div>
      <div class="rr-form-card" style="max-width:480px;">
        <h3 class="rr-form-card-h">Changer mon mot de passe</h3>
        <div class="rr-alert rr-err"     id="rr-pw-error"   style="display:none;"></div>
        <div class="rr-alert rr-success" id="rr-pw-success" style="display:none;">Mot de passe mis à jour ✓</div>
        <div class="rr-field"><label class="rr-lab">Mot de passe actuel</label><input class="rr-inp" type="password" id="pw-old" /></div>
        <div class="rr-field"><label class="rr-lab">Nouveau mot de passe</label><input class="rr-inp" type="password" id="pw-new" placeholder="8 caractères minimum" /></div>
        <div class="rr-field"><label class="rr-lab">Confirmer</label><input class="rr-inp" type="password" id="pw-confirm" /></div>
        <button class="rr-submit" id="rr-save-pw">Mettre à jour</button>
      </div>
    </div>

  </main>
</div>
