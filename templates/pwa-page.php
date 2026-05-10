<?php if ( ! defined('ABSPATH') ) exit; ?>

<style>
  .pwa-wrap { max-width: 1000px; }
  .pwa-wrap h1 { display: flex; align-items: center; gap: 10px; margin: 18px 0 22px; }
  .pwa-card { background: #fff; border: 1px solid #ddd; border-radius: 10px; padding: 24px 28px; margin-bottom: 18px; }
  .pwa-card h2 { font-size: 15px; margin: 0 0 4px; color: #111; }
  .pwa-card p.desc { font-size: 13px; color: #777; margin: 0 0 18px; line-height: 1.6; }
  .pwa-card a { color: #0F766E; }

  .url-box { background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 16px 20px; display: flex; align-items: center; gap: 14px; margin-bottom: 16px; flex-wrap: wrap; }
  .url-box code { flex: 1; min-width: 200px; background: #fff; border: 1px solid #99f6e4; padding: 8px 12px; border-radius: 6px; font-family: ui-monospace, monospace; font-size: 14px; color: #0F766E; word-break: break-all; }
  .copy-btn { background: #0F766E; color: #fff; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; }
  .copy-btn:hover { background: #0d5e57; }
  .open-btn { background: #fff; color: #0F766E; border: 1px solid #0F766E; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px; text-decoration: none; display: inline-block; }
  .open-btn:hover { background: #f0fdfa; color: #0F766E; }

  .step { display: flex; gap: 14px; padding: 14px 0; border-bottom: 1px solid #f1f5f9; }
  .step:last-child { border-bottom: none; }
  .step-num { background: #0F766E; color: #fff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; }
  .step-content h3 { font-size: 14px; margin: 4px 0 6px; color: #111; }
  .step-content p  { font-size: 13px; color: #555; line-height: 1.6; margin: 0; }

  table.tk-table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; }
  table.tk-table th, table.tk-table td { padding: 10px 14px; text-align: left; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
  table.tk-table th { background: #f9fafb; font-weight: 600; color: #374151; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; }
  table.tk-table tr:last-child td { border-bottom: none; }
  table.tk-table .role { font-size: 11px; padding: 2px 8px; border-radius: 10px; font-weight: 600; text-transform: uppercase; }
  table.tk-table .role.manager { background: #0f172a; color: #fff; }
  table.tk-table .role.employee { background: #e2e8f0; color: #475569; }

  .empty-row { text-align: center; padding: 28px; color: #94a3b8; font-style: italic; }

  .ios-tip, .android-tip { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; font-size: 13px; color: #78350f; margin-top: 10px; line-height: 1.6; }
</style>

<div class="wrap pwa-wrap">
  <h1>📱 Application mobile (PWA)</h1>

  <?php
    $app_url = rr_pwa_url();

    // Vérifier que les permaliens sont OK
    $rules = get_option( 'rewrite_rules' );
    $current_slug_check = rr_pwa_slug();
    $rules_ok = is_array( $rules ) && ! empty( array_filter( array_keys( $rules ), function( $r ) use ( $current_slug_check ) { return strpos( $r, $current_slug_check ) === 0; } ) );

    // Vérifier HTTPS
    $is_https = is_ssl();
  ?>

  <?php if ( ! $is_https ) : ?>
    <div class="notice notice-error inline"><p><strong>⚠️ HTTPS requis :</strong> Pour que la PWA fonctionne (notifications, installation), votre site doit être en HTTPS. Activez SSL dans votre hébergement.</p></div>
  <?php endif; ?>

  <?php if ( ! $rules_ok ) : ?>
    <div class="notice notice-warning inline">
      <p><strong>ℹ️ Permaliens à rafraîchir :</strong> Allez dans <a href="<?= esc_url( admin_url('options-permalink.php') ) ?>">Réglages → Permaliens</a> et cliquez sur "Enregistrer" (sans rien changer). Cela activera l'URL de l'app.</p>
    </div>
  <?php endif; ?>

  <div class="pwa-card">
    <h2>🔗 URL de l'application</h2>
    <p class="desc">Voici l'adresse à ouvrir sur la tablette ou le téléphone pour accéder à l'app.</p>
    <div class="url-box">
      <code id="app-url"><?= esc_html( $app_url ) ?></code>
      <button type="button" class="copy-btn" onclick="navigator.clipboard.writeText('<?= esc_js( $app_url ) ?>'); this.textContent='✓ Copié'; setTimeout(()=>this.textContent='📋 Copier', 1500);">📋 Copier</button>
      <a class="open-btn" href="<?= esc_url( $app_url ) ?>" target="_blank">↗ Ouvrir</a>
    </div>
  </div>

  <div class="pwa-card">
    <h2>🔒 Personnaliser l'URL (sécurité)</h2>
    <p class="desc">
      Par défaut, l'app est accessible à <code>tonsite.fr/app</code>. Vous pouvez modifier ce slug pour rendre l'URL <strong>plus difficile à deviner</strong> par des bots ou des pirates.
      <br><br>
      <strong>⚠️ Important :</strong> ce n'est pas une vraie protection contre les attaques (l'app est déjà sécurisée par mot de passe + token), mais cela limite les bots automatiques. Choisissez un slug que vous pouvez retenir.
    </p>

    <form method="post">
      <?php wp_nonce_field( 'rr_save_slug' ); ?>
      <div style="display:flex; gap:8px; align-items:center; margin-bottom:14px; flex-wrap:wrap;">
        <span style="font-family: ui-monospace, monospace; color:#64748b; font-size:14px; white-space:nowrap;"><?= esc_html( home_url( '/' ) ) ?></span>
        <input
          type="text"
          name="rr_pwa_slug"
          value="<?= esc_attr( $current_slug ) ?>"
          pattern="[a-z0-9-]+"
          minlength="3"
          maxlength="40"
          required
          style="flex:1; min-width:200px; padding:9px 14px; border:1.5px solid #cbd5e1; border-radius:7px; font-family: ui-monospace, monospace; font-size:14px;" />
      </div>
      <p style="font-size:12px; color:#64748b; margin:0 0 12px;">
        Lettres minuscules, chiffres et tirets uniquement. Min 3 caractères. Exemples : <code>resto-pro</code>, <code>resa-2026</code>, <code>monequipe-7x9</code>.
      </p>

      <label style="display:flex; align-items:center; gap:8px; padding:10px 14px; background:#fffbeb; border:1px solid #fde68a; border-radius:7px; margin-bottom:14px; cursor:pointer;">
        <input type="checkbox" name="revoke_all" value="1" />
        <span style="font-size:13px; color:#78350f;"><strong>Forcer la reconnexion</strong> de tous les appareils après changement (recommandé pour sécurité maximale).</span>
      </label>

      <button type="submit" name="rr_save_slug" class="button button-primary" style="background:#0F766E; border-color:#0F766E;">Enregistrer le nouveau slug</button>
    </form>
  </div>

  <div class="pwa-card">
    <h2>📲 Comment installer sur la tablette / le téléphone</h2>

    <div class="step">
      <div class="step-num">1</div>
      <div class="step-content">
        <h3>Ouvrir l'URL dans le navigateur</h3>
        <p>Sur l'appareil cible, ouvrez Chrome (Android) ou Safari (iPhone/iPad) et tapez l'URL ci-dessus.</p>
      </div>
    </div>

    <div class="step">
      <div class="step-num">2</div>
      <div class="step-content">
        <h3>Se connecter avec un compte d'équipe</h3>
        <p>Utilisez un identifiant créé dans <a href="<?= esc_url( admin_url('admin.php?page=rr-settings') ) ?>">Réservations → Réglages</a> (ou créé via le dashboard admin du site).</p>
      </div>
    </div>

    <div class="step">
      <div class="step-num">3</div>
      <div class="step-content">
        <h3>Installer l'app sur l'écran d'accueil</h3>
        <p>Une fois connecté, l'app vous propose automatiquement d'être installée. Sinon :</p>
        <div class="android-tip"><strong>📱 Android (Chrome) :</strong> Menu (⋮) en haut à droite → "Installer l'application" ou "Ajouter à l'écran d'accueil".</div>
        <div class="ios-tip"><strong>🍎 iPhone / iPad (Safari) :</strong> Bouton Partager (carré avec flèche) en bas → "Sur l'écran d'accueil".</div>
      </div>
    </div>

    <div class="step">
      <div class="step-num">4</div>
      <div class="step-content">
        <h3>Autoriser les notifications (optionnel)</h3>
        <p>L'app demandera la permission pour les notifications. Acceptez pour recevoir un son et une alerte quand une nouvelle réservation arrive — même quand l'app est en arrière-plan.</p>
      </div>
    </div>
  </div>

  <div class="pwa-card">
    <h2>🔐 Appareils connectés</h2>
    <p class="desc">Liste des appareils actuellement connectés à l'app. Vous pouvez révoquer l'accès à un appareil perdu ou volé.</p>

    <table class="tk-table">
      <thead>
        <tr>
          <th>Utilisateur</th>
          <th>Appareil</th>
          <th>Dernière activité</th>
          <th>Connecté depuis</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if ( empty( $tokens ) ) : ?>
          <tr><td colspan="5" class="empty-row">Aucun appareil connecté pour le moment.</td></tr>
        <?php else : foreach ( $tokens as $t ) : ?>
          <tr>
            <td>
              <strong><?= esc_html( $t->full_name ?: '—' ) ?></strong>
              &nbsp;<span class="role <?= esc_attr( $t->role ) ?>"><?= $t->role === 'manager' ? 'Manager' : 'Employé' ?></span>
            </td>
            <td><?= esc_html( $t->device_name ?: 'Inconnu' ) ?></td>
            <td><?= $t->last_used ? esc_html( human_time_diff( strtotime( $t->last_used ), current_time('timestamp') ) ) . ' avant' : '<span style="color:#aaa;">Jamais</span>' ?></td>
            <td><?= esc_html( wp_date( 'd/m/Y', strtotime( $t->created_at ) ) ) ?></td>
            <td>
              <form method="post" style="margin:0;">
                <?php wp_nonce_field( 'rr_revoke_token' ); ?>
                <input type="hidden" name="token_id" value="<?= esc_attr( $t->id ) ?>" />
                <button type="submit" name="rr_revoke_token" class="button button-link-delete" onclick="return confirm('Révoquer l\'accès à cet appareil ?');">Révoquer</button>
              </form>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="pwa-card">
    <h2>✨ Fonctionnalités</h2>
    <ul style="margin:0; padding-left:20px; line-height:1.9; font-size:13px; color:#444;">
      <li><strong>🔔 Sons et vibrations</strong> à l'arrivée d'une nouvelle réservation</li>
      <li><strong>📬 Notifications push</strong> même quand l'app est fermée (avec autorisation)</li>
      <li><strong>👥 Liens directs</strong> appel téléphonique et e-mail en un tap</li>
      <li><strong>🚦 Filtres rapides</strong> : à traiter, confirmées, aujourd'hui, à venir</li>
      <li><strong>⚡ Synchronisation auto</strong> toutes les 15 secondes</li>
      <li><strong>📡 Indicateur de connexion</strong> en temps réel (point vert/rouge)</li>
      <li><strong>🔐 Auth par token</strong> sécurisée, valable 30 jours</li>
      <li><strong>📱 Mode plein écran</strong> une fois installée sur l'écran d'accueil</li>
    </ul>
  </div>
</div>
