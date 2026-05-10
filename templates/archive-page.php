<?php if ( ! defined('ABSPATH') ) exit; ?>

<style>
  .rr-arch-wrap { max-width: 1100px; }
  .rr-arch-wrap h1 { display:flex; align-items:center; gap:10px; margin: 18px 0 22px; }

  .rr-arch-tabs { display:flex; gap:4px; border-bottom:1px solid #ddd; margin-bottom:22px; }
  .rr-arch-tab  { padding:10px 18px; cursor:pointer; border:none; background:transparent; font-size:14px; color:#666; border-bottom:2px solid transparent; margin-bottom:-1px; transition:all .15s; }
  .rr-arch-tab.active { color:#0F766E; border-bottom-color:#0F766E; font-weight:600; }
  .rr-arch-tab:hover:not(.active) { color:#222; }
  .rr-arch-panel { display:none; }
  .rr-arch-panel.active { display:block; }

  .rr-arch-card { background:#fff; border:1px solid #ddd; border-radius:10px; padding:24px 28px; margin-bottom:18px; }
  .rr-arch-card h2 { font-size:15px; margin:0 0 4px; color:#111; }
  .rr-arch-card p.desc { font-size:13px; color:#777; margin:0 0 18px; }

  .rr-arch-row { display:grid; grid-template-columns: 1fr 1fr; gap:18px; margin-bottom:14px; }
  .rr-arch-row.full { grid-template-columns:1fr; }

  .rr-arch-field label { display:block; font-size:13px; font-weight:600; color:#333; margin-bottom:6px; }
  .rr-arch-field input[type=text], .rr-arch-field input[type=email], .rr-arch-field input[type=date], .rr-arch-field input[type=number], .rr-arch-field select {
    width:100%; max-width:300px; padding:8px 12px; border:1px solid #ccc; border-radius:6px; font-size:14px;
  }

  .rr-arch-toggle { display:flex; align-items:center; gap:10px; padding:12px 14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:10px; cursor:pointer; }
  .rr-arch-toggle input { width:18px; height:18px; cursor:pointer; }
  .rr-arch-toggle:hover { background:#f3f4f6; }
  .rr-arch-toggle .label-main { font-weight:600; font-size:13px; color:#111; }
  .rr-arch-toggle .label-desc { font-size:12px; color:#777; margin-top:2px; }

  .rr-cron-info { background:#f0fdfa; border:1px solid #99f6e4; border-radius:8px; padding:12px 16px; font-size:13px; color:#0f766e; margin-bottom:16px; }
  .rr-cron-info code { background:#fff; padding:2px 6px; border-radius:4px; font-size:12px; }

  .rr-actions-row { display:flex; gap:10px; justify-content:flex-end; padding:16px 0; }
  .rr-btn-save { background:#0F766E; color:#fff; border:none; padding:10px 24px; border-radius:6px; cursor:pointer; font-weight:600; font-size:14px; }
  .rr-btn-save:hover { background:#0d5e57; }

  table.rr-table { width:100%; border-collapse:collapse; background:#fff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; }
  table.rr-table th, table.rr-table td { padding:10px 14px; text-align:left; font-size:13px; border-bottom:1px solid #f1f5f9; }
  table.rr-table th { background:#f9fafb; font-weight:600; color:#374151; font-size:12px; text-transform:uppercase; letter-spacing:.3px; }
  table.rr-table tr:last-child td { border-bottom:none; }
  table.rr-table .badge-auto    { background:#e0e7ff; color:#3730a3; font-size:11px; padding:2px 8px; border-radius:10px; font-weight:600; }
  table.rr-table .badge-manual  { background:#fef3c7; color:#92400e; font-size:11px; padding:2px 8px; border-radius:10px; font-weight:600; }
  table.rr-table .badge-deleted { background:#fee2e2; color:#991b1b; font-size:11px; padding:2px 6px; border-radius:10px; font-weight:600; }
  table.rr-table .badge-emailed { background:#d1fae5; color:#065f46; font-size:11px; padding:2px 6px; border-radius:10px; font-weight:600; }
  table.rr-table a.dl { color:#0F766E; text-decoration:none; font-weight:600; }
  table.rr-table a.dl:hover { text-decoration:underline; }
  table.rr-table button.del { background:transparent; border:none; color:#991b1b; cursor:pointer; font-size:12px; }
  table.rr-table button.del:hover { text-decoration:underline; }

  .rr-empty-row { text-align:center; padding:28px; color:#94a3b8; font-style:italic; }

  .rr-rgpd-box { background:#fef9c3; border:1px solid #fde047; border-radius:8px; padding:14px 18px; font-size:13px; color:#713f12; margin-bottom:18px; line-height:1.6; }
  .rr-rgpd-box strong { color:#422006; }

  .rr-export-form { display:grid; grid-template-columns: 1fr 1fr auto; gap:12px; align-items:end; }

  #rr-export-result { padding:12px 16px; border-radius:8px; font-size:13px; margin-top:14px; display:none; }
  #rr-export-result.success { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
  #rr-export-result.error   { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }

  .rr-log-action { font-family: ui-monospace, monospace; font-size:11px; padding:1px 6px; background:#f1f5f9; border-radius:4px; color:#475569; }
</style>

<div class="wrap rr-arch-wrap">
  <h1>🗄️ Archivage & données</h1>

  <div class="rr-rgpd-box">
    <strong>🛡️ Conformité RGPD</strong> — Les données personnelles ne doivent pas être conservées plus longtemps que nécessaire.
    Ce module exporte automatiquement les anciennes réservations en CSV puis (en option) les supprime de la base.
    <strong>Les réservations futures et en attente ne sont jamais touchées.</strong>
  </div>

  <div class="rr-arch-tabs">
    <button type="button" class="rr-arch-tab active" data-tab="settings">⚙️ Réglages</button>
    <button type="button" class="rr-arch-tab" data-tab="manual">📤 Export manuel</button>
    <button type="button" class="rr-arch-tab" data-tab="history">📁 Historique</button>
    <button type="button" class="rr-arch-tab" data-tab="logs">📋 Logs</button>
  </div>

  <!-- ── RÉGLAGES ── -->
  <div class="rr-arch-panel active" id="atab-settings">
    <form method="post">
      <?php wp_nonce_field( 'rr_save_archive' ); ?>

      <div class="rr-arch-card">
        <h2>Archivage automatique</h2>
        <p class="desc">L'archivage tourne automatiquement le 1er de chaque mois (via WP-Cron) et exporte les réservations passées dépassant la durée de conservation choisie.</p>

        <?php if ( $next_cron ) : ?>
          <div class="rr-cron-info">
            ⏰ Prochaine exécution automatique : <strong><?= esc_html( wp_date( 'd F Y à H:i', $next_cron ) ) ?></strong>
          </div>
        <?php endif; ?>

        <label class="rr-arch-toggle">
          <input type="checkbox" name="archive_enabled" value="1" <?= checked($s['archive_enabled'],1,false) ?> />
          <div>
            <div class="label-main">Activer l'archivage automatique</div>
            <div class="label-desc">Si désactivé, l'export ne se fait qu'avec le bouton manuel.</div>
          </div>
        </label>

        <div class="rr-arch-field" style="margin-top:18px;">
          <label>Durée de conservation des réservations</label>
          <select name="archive_retention_days">
            <option value="30"  <?= selected($s['archive_retention_days'],30,false) ?>>30 jours</option>
            <option value="60"  <?= selected($s['archive_retention_days'],60,false) ?>>60 jours</option>
            <option value="90"  <?= selected($s['archive_retention_days'],90,false) ?>>90 jours (recommandé)</option>
            <option value="180" <?= selected($s['archive_retention_days'],180,false) ?>>180 jours</option>
            <option value="365" <?= selected($s['archive_retention_days'],365,false) ?>>365 jours (1 an)</option>
          </select>
          <p class="desc" style="margin-top:8px;">Les réservations passées plus anciennes que cette durée seront archivées.</p>
        </div>
      </div>

      <div class="rr-arch-card">
        <h2>Options d'archivage</h2>

        <label class="rr-arch-toggle">
          <input type="checkbox" name="archive_delete_after" value="1" <?= checked($s['archive_delete_after'],1,false) ?> />
          <div>
            <div class="label-main">Supprimer automatiquement après export</div>
            <div class="label-desc">Une fois l'archive CSV créée et vérifiée, les réservations sont supprimées de la base. Recommandé pour le RGPD.</div>
          </div>
        </label>

        <label class="rr-arch-toggle">
          <input type="checkbox" name="archive_email_to_resto" value="1" <?= checked($s['archive_email_to_resto'],1,false) ?> />
          <div>
            <div class="label-main">Envoyer l'archive par e-mail au restaurant</div>
            <div class="label-desc">Le fichier CSV est envoyé en pièce jointe lors de chaque archivage.</div>
          </div>
        </label>

        <div class="rr-arch-field" style="margin-top:18px;">
          <label>Adresse e-mail de destination (optionnel)</label>
          <input type="email" name="archive_email_address" value="<?= esc_attr( $s['archive_email_address'] ) ?>" placeholder="<?= esc_attr( get_option('admin_email') ) ?>" />
          <p class="desc" style="margin-top:8px;">Si vide, l'e-mail administrateur du site sera utilisé.</p>
        </div>
      </div>

      <div class="rr-actions-row">
        <button type="submit" name="rr_save_archive" class="rr-btn-save">Enregistrer les réglages</button>
      </div>
    </form>
  </div>

  <!-- ── EXPORT MANUEL ── -->
  <div class="rr-arch-panel" id="atab-manual">
    <div class="rr-arch-card">
      <h2>📤 Export manuel d'une période</h2>
      <p class="desc">Choisissez une période pour exporter les réservations passées en CSV. Les réservations futures et en attente ne seront jamais incluses.</p>

      <div class="rr-export-form">
        <div class="rr-arch-field">
          <label>Date de début</label>
          <input type="date" id="exp-start" />
        </div>
        <div class="rr-arch-field">
          <label>Date de fin</label>
          <input type="date" id="exp-end" max="<?= esc_attr( date('Y-m-d', strtotime('-1 day')) ) ?>" />
        </div>
        <div>
          <button type="button" class="rr-btn-save" id="rr-export-run">Lancer l'export</button>
        </div>
      </div>

      <div style="margin-top:14px; display:flex; gap:18px; flex-wrap:wrap;">
        <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
          <input type="checkbox" id="exp-delete" /> Supprimer après export
        </label>
        <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
          <input type="checkbox" id="exp-email" /> Envoyer par e-mail
        </label>
      </div>

      <div id="rr-export-result"></div>
    </div>
  </div>

  <!-- ── HISTORIQUE ── -->
  <div class="rr-arch-panel" id="atab-history">
    <div class="rr-arch-card">
      <h2>📁 Historique des exports</h2>
      <p class="desc">Tous les exports archivés. Les fichiers sont stockés en sécurité dans <code>wp-content/uploads/rr-archives/</code> (accès direct bloqué).</p>

      <table class="rr-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Période</th>
            <th>Réservations</th>
            <th>Statut</th>
            <th>Fichier</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if ( empty( $exports ) ) : ?>
            <tr><td colspan="7" class="rr-empty-row">Aucun export pour le moment.</td></tr>
          <?php else : foreach ( $exports as $e ) : ?>
            <tr>
              <td><?= esc_html( wp_date( 'd/m/Y H:i', strtotime( $e->created_at ) ) ) ?></td>
              <td><span class="badge-<?= esc_attr( $e->type ) ?>"><?= $e->type === 'auto' ? 'Auto' : 'Manuel' ?></span></td>
              <td><?= esc_html( $e->period_start ) ?> → <?= esc_html( $e->period_end ) ?></td>
              <td><strong><?= esc_html( $e->count ) ?></strong></td>
              <td>
                <?php if ( $e->deleted_count > 0 ) : ?>
                  <span class="badge-deleted">🗑 <?= esc_html( $e->deleted_count ) ?> supprimées</span>
                <?php endif; ?>
                <?php if ( $e->emailed ) : ?>
                  <span class="badge-emailed">✉️ Envoyé</span>
                <?php endif; ?>
              </td>
              <td><a class="dl" href="<?= esc_url( admin_url( '?rr_download=' . urlencode( $e->filename ) ) ) ?>">Télécharger</a> <span style="color:#999; font-size:11px;">(<?= esc_html( size_format( $e->filesize ) ) ?>)</span></td>
              <td><button type="button" class="del" data-id="<?= esc_attr( $e->id ) ?>">Supprimer</button></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ── LOGS ── -->
  <div class="rr-arch-panel" id="atab-logs">
    <div class="rr-arch-card">
      <h2>📋 Journal d'activité (30 dernières actions)</h2>
      <p class="desc">Toutes les actions importantes (exports, suppressions, modifications de réglages) sont tracées pour conformité RGPD.</p>

      <table class="rr-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Utilisateur</th>
            <th>Action</th>
            <th>Détails</th>
          </tr>
        </thead>
        <tbody>
          <?php if ( empty( $logs ) ) : ?>
            <tr><td colspan="4" class="rr-empty-row">Aucun log pour le moment.</td></tr>
          <?php else : foreach ( $logs as $log ) : ?>
            <tr>
              <td><?= esc_html( wp_date( 'd/m/Y H:i:s', strtotime( $log->created_at ) ) ) ?></td>
              <td><?= esc_html( $log->username ?: 'system' ) ?></td>
              <td><span class="rr-log-action"><?= esc_html( $log->action ) ?></span></td>
              <td><?= esc_html( $log->details ) ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var nonce = '<?= wp_create_nonce("rr_archive_nonce") ?>';

  // Tabs
  document.querySelectorAll('.rr-arch-tab').forEach(function(t) {
    t.addEventListener('click', function() {
      document.querySelectorAll('.rr-arch-tab').forEach(function(b) { b.classList.remove('active'); });
      document.querySelectorAll('.rr-arch-panel').forEach(function(p) { p.classList.remove('active'); });
      t.classList.add('active');
      document.getElementById('atab-' + t.dataset.tab).classList.add('active');
    });
  });

  // Export manuel
  var btn = document.getElementById('rr-export-run');
  if (btn) btn.addEventListener('click', function() {
    var start  = document.getElementById('exp-start').value;
    var end    = document.getElementById('exp-end').value;
    var del    = document.getElementById('exp-delete').checked ? 1 : 0;
    var email  = document.getElementById('exp-email').checked ? 1 : 0;
    var res    = document.getElementById('rr-export-result');

    if (!start || !end) {
      res.className = 'error'; res.style.display = 'block';
      res.textContent = 'Veuillez choisir une date de début et une date de fin.';
      return;
    }

    btn.disabled = true; btn.textContent = 'Export en cours…';

    var fd = new FormData();
    fd.append('action', 'rr_manual_export');
    fd.append('nonce', nonce);
    fd.append('start', start);
    fd.append('end', end);
    fd.append('delete', del);
    fd.append('email', email);

    fetch(ajaxurl, { method: 'POST', body: fd })
      .then(function(r) { return r.json(); })
      .then(function(j) {
        btn.disabled = false; btn.textContent = "Lancer l'export";
        res.style.display = 'block';
        if (j.success) {
          res.className = 'success';
          res.textContent = '✓ Export réussi ! ' + j.data.count + ' réservation(s) exportée(s)' +
            (j.data.deleted ? ', ' + j.data.deleted + ' supprimée(s)' : '') +
            (j.data.emailed ? ', e-mail envoyé' : '') + '.';
          setTimeout(function() { window.location.reload(); }, 2000);
        } else {
          res.className = 'error';
          res.textContent = '✗ ' + (j.data || 'Erreur inconnue.');
        }
      })
      .catch(function() {
        btn.disabled = false; btn.textContent = "Lancer l'export";
        res.className = 'error'; res.style.display = 'block';
        res.textContent = 'Erreur réseau.';
      });
  });

  // Suppression d'un export
  document.querySelectorAll('button.del').forEach(function(b) {
    b.addEventListener('click', function() {
      if (!confirm('Supprimer définitivement cet export et son fichier CSV ?')) return;
      var fd = new FormData();
      fd.append('action', 'rr_delete_export');
      fd.append('nonce', nonce);
      fd.append('id', b.dataset.id);
      fetch(ajaxurl, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(j) { if (j.success) window.location.reload(); else alert(j.data || 'Erreur'); });
    });
  });
});
</script>
