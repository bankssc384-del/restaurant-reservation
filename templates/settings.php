<?php if ( ! defined('ABSPATH') ) exit; ?>

<style>
  .rr-settings-wrap { max-width: 980px; }
  .rr-settings-wrap h1 { display:flex; align-items:center; gap:10px; margin: 18px 0 22px; }
  .rr-tabs { display:flex; gap:4px; border-bottom:1px solid #ddd; margin-bottom:24px; }
  .rr-tab  { padding:10px 18px; cursor:pointer; border:none; background:transparent; font-size:14px; color:#666; border-bottom:2px solid transparent; margin-bottom:-1px; transition:all .15s; }
  .rr-tab.active { color:#0F766E; border-bottom-color:#0F766E; font-weight:600; }
  .rr-tab:hover:not(.active) { color:#222; }
  .rr-tab-panel { display:none; }
  .rr-tab-panel.active { display:block; }

  .rr-card-set { background:#fff; border:1px solid #ddd; border-radius:10px; padding:24px 28px; margin-bottom:18px; }
  .rr-card-set h2 { font-size:15px; margin:0 0 4px; color:#111; }
  .rr-card-set p.desc { font-size:13px; color:#777; margin:0 0 18px; }

  .rr-row { display:grid; grid-template-columns: 1fr 1fr; gap:18px; margin-bottom:14px; }
  .rr-row.full { grid-template-columns:1fr; }
  .rr-set-field label { display:block; font-size:13px; font-weight:600; color:#333; margin-bottom:6px; }
  .rr-set-field input[type=text], .rr-set-field input[type=number], .rr-set-field select, .rr-set-field textarea {
    width:100%; padding:8px 12px; border:1px solid #ccc; border-radius:6px; font-size:14px; background:#fff;
  }
  .rr-set-field textarea { min-height:120px; font-family:inherit; resize:vertical; }
  .rr-set-field .hint { font-size:11px; color:#888; margin-top:4px; }

  .rr-color-input { display:flex; align-items:center; gap:8px; }
  .rr-color-input input[type=color] { width:42px; height:36px; border:1px solid #ccc; border-radius:6px; padding:2px; cursor:pointer; }
  .rr-color-input input[type=text] { flex:1; }

  .rr-mode-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
  .rr-mode { border:2px solid #e5e7eb; border-radius:10px; padding:18px; cursor:pointer; transition:all .15s; }
  .rr-mode:hover { border-color:#cbd5e1; }
  .rr-mode.selected { border-color:#0F766E; background:#f0fdfa; }
  .rr-mode-title { font-weight:600; font-size:14px; margin-bottom:6px; color:#111; display:flex; align-items:center; gap:8px; }
  .rr-mode-desc  { font-size:12px; color:#666; line-height:1.5; }
  .rr-mode-radio { display:none; }

  .rr-actions { display:flex; gap:10px; justify-content:flex-end; padding:18px 0; }
  .rr-btn-save  { background:#0F766E; color:#fff; border:none; padding:10px 24px; border-radius:6px; cursor:pointer; font-weight:600; font-size:14px; }
  .rr-btn-save:hover { background:#0d5e57; }
  .rr-btn-reset { background:#fff; color:#991b1b; border:1px solid #fca5a5; padding:10px 18px; border-radius:6px; cursor:pointer; font-size:13px; }
  .rr-btn-reset:hover { background:#fef2f2; }

  .rr-vars-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px 14px; font-size:12px; color:#475569; line-height:1.7; margin-top:8px; }
  .rr-vars-box code { background:#fff; border:1px solid #e2e8f0; border-radius:4px; padding:1px 6px; font-size:11px; }

  .rr-shortcodes { background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:14px 18px; margin-bottom:18px; }
  .rr-shortcodes code { background:#fff; padding:2px 7px; border:1px solid #fcd34d; border-radius:4px; font-size:13px; }
</style>

<div class="rr-settings-wrap">
  <h1>🍽️ Réservation Restaurant — Réglages</h1>

  <div class="rr-shortcodes">
    <strong>Shortcodes :</strong>
    Formulaire client → <code>[reservation]</code>
    &nbsp;·&nbsp;
    Espace équipe → <code>[reservation_admin]</code>
  </div>

  <div class="rr-tabs">
    <button type="button" class="rr-tab active" data-tab="general">Général</button>
    <button type="button" class="rr-tab" data-tab="design">Design</button>
    <button type="button" class="rr-tab" data-tab="emails">E-mails</button>
    <button type="button" class="rr-tab" data-tab="slots">Créneaux</button>
  </div>

  <form method="post">
    <?php wp_nonce_field( 'rr_save_settings' ); ?>

    <!-- ── GENERAL ── -->
    <div class="rr-tab-panel active" id="tab-general">
      <div class="rr-card-set">
        <h2>Mode de validation</h2>
        <p class="desc">Comment souhaitez-vous traiter les réservations entrantes ?</p>

        <div class="rr-mode-grid">
          <label class="rr-mode <?= $s['mode'] === 'auto' ? 'selected' : '' ?>" data-val="auto">
            <input type="radio" name="mode" value="auto" class="rr-mode-radio" <?= checked( $s['mode'], 'auto', false ) ?> />
            <div class="rr-mode-title">⚡ Confirmation automatique</div>
            <div class="rr-mode-desc">Toute demande est confirmée immédiatement. Le client reçoit une confirmation aussitôt.</div>
          </label>
          <label class="rr-mode <?= $s['mode'] === 'manual' ? 'selected' : '' ?>" data-val="manual">
            <input type="radio" name="mode" value="manual" class="rr-mode-radio" <?= checked( $s['mode'], 'manual', false ) ?> />
            <div class="rr-mode-title">👤 Validation manuelle</div>
            <div class="rr-mode-desc">Chaque demande passe en statut "en attente". L'équipe confirme manuellement depuis le dashboard.</div>
          </label>
        </div>
      </div>

      <div class="rr-card-set">
        <h2>Canaux de notification</h2>
        <p class="desc">Comment notifier le client (demande reçue, confirmation, refus) ? Vous pouvez choisir un seul canal ou les deux.</p>
        <?php $channels = $s['notify_channels']; ?>
        <div style="display:flex; gap:14px; flex-wrap:wrap;">
          <label class="rr-mode <?= in_array('email',$channels) ? 'selected' : '' ?>" style="flex:1; min-width:200px;" data-toggle="email">
            <input type="checkbox" name="notify_channels[]" value="email" style="display:none;" <?= in_array('email',$channels) ? 'checked' : '' ?> />
            <div class="rr-mode-title">📧 E-mail</div>
            <div class="rr-mode-desc">Notifications envoyées par e-mail au client.</div>
          </label>
          <label class="rr-mode <?= in_array('sms',$channels) ? 'selected' : '' ?>" style="flex:1; min-width:200px;" data-toggle="sms">
            <input type="checkbox" name="notify_channels[]" value="sms" style="display:none;" <?= in_array('sms',$channels) ? 'checked' : '' ?> />
            <div class="rr-mode-title">📱 SMS</div>
            <div class="rr-mode-desc">Notifications envoyées par SMS. Configurez Brevo dans l'onglet "📱 SMS".</div>
          </label>
        </div>
        <p class="desc" style="margin-top:10px;">Au moins un canal doit être actif. Si aucune case n'est cochée, les e-mails seront utilisés par défaut.</p>
      </div>

      <div class="rr-card-set">
        <h2>Textes du formulaire</h2>
        <div class="rr-row">
          <div class="rr-set-field">
            <label>Titre</label>
            <input type="text" name="form_title" value="<?= esc_attr( $s['form_title'] ) ?>" />
          </div>
          <div class="rr-set-field">
            <label>Texte du bouton d'envoi</label>
            <input type="text" name="btn_text_label" value="<?= esc_attr( $s['btn_text_label'] ) ?>" />
          </div>
          <div class="rr-set-field">
            <label>Sous-titre</label>
            <input type="text" name="form_subtitle" value="<?= esc_attr( $s['form_subtitle'] ) ?>" />
          </div>
          <div class="rr-set-field">
            <label>Titre du message de succès</label>
            <input type="text" name="success_title" value="<?= esc_attr( $s['success_title'] ) ?>" />
          </div>
          <div class="rr-set-field" style="grid-column:1/-1;">
            <label>Texte du message de succès</label>
            <textarea name="success_text" rows="2"><?= esc_textarea( $s['success_text'] ) ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- ── DESIGN ── -->
    <div class="rr-tab-panel" id="tab-design">
      <div class="rr-card-set">
        <h2>Couleurs</h2>
        <div class="rr-row">
          <div class="rr-set-field">
            <label>Couleur principale (accent)</label>
            <div class="rr-color-input">
              <input type="color" value="<?= esc_attr( $s['color_primary'] ) ?>" data-target="color_primary" />
              <input type="text" name="color_primary" value="<?= esc_attr( $s['color_primary'] ) ?>" />
            </div>
          </div>
          <div class="rr-set-field">
            <label>Couleur du texte</label>
            <div class="rr-color-input">
              <input type="color" value="<?= esc_attr( $s['color_text'] ) ?>" data-target="color_text" />
              <input type="text" name="color_text" value="<?= esc_attr( $s['color_text'] ) ?>" />
            </div>
          </div>
          <div class="rr-set-field">
            <label>Couleur de fond</label>
            <div class="rr-color-input">
              <input type="color" value="<?= esc_attr( $s['color_background'] ) ?>" data-target="color_background" />
              <input type="text" name="color_background" value="<?= esc_attr( $s['color_background'] ) ?>" />
            </div>
          </div>
          <div class="rr-set-field">
            <label>Couleur des bordures</label>
            <div class="rr-color-input">
              <input type="color" value="<?= esc_attr( $s['color_border'] ) ?>" data-target="color_border" />
              <input type="text" name="color_border" value="<?= esc_attr( $s['color_border'] ) ?>" />
            </div>
          </div>
        </div>
      </div>

      <div class="rr-card-set">
        <h2>Champs du formulaire</h2>
        <div class="rr-row">
          <div class="rr-set-field">
            <label>Taille des champs</label>
            <select name="input_size">
              <option value="small"  <?= selected($s['input_size'],'small',false) ?>>Petite</option>
              <option value="medium" <?= selected($s['input_size'],'medium',false) ?>>Moyenne</option>
              <option value="large"  <?= selected($s['input_size'],'large',false) ?>>Grande</option>
            </select>
          </div>
          <div class="rr-set-field">
            <label>Arrondi des coins (px)</label>
            <input type="number" name="input_radius" value="<?= esc_attr( $s['input_radius'] ) ?>" min="0" max="30" />
          </div>
        </div>
      </div>

      <div class="rr-card-set">
        <h2>Bouton d'envoi</h2>
        <div class="rr-row">
          <div class="rr-set-field">
            <label>Couleur de fond</label>
            <div class="rr-color-input">
              <input type="color" value="<?= esc_attr( $s['btn_bg'] ) ?>" data-target="btn_bg" />
              <input type="text" name="btn_bg" value="<?= esc_attr( $s['btn_bg'] ) ?>" />
            </div>
          </div>
          <div class="rr-set-field">
            <label>Couleur du texte</label>
            <div class="rr-color-input">
              <input type="color" value="<?= esc_attr( $s['btn_text'] ) ?>" data-target="btn_text" />
              <input type="text" name="btn_text" value="<?= esc_attr( $s['btn_text'] ) ?>" />
            </div>
          </div>
          <div class="rr-set-field">
            <label>Taille du bouton</label>
            <select name="btn_size">
              <option value="small"  <?= selected($s['btn_size'],'small',false) ?>>Petit</option>
              <option value="medium" <?= selected($s['btn_size'],'medium',false) ?>>Moyen</option>
              <option value="large"  <?= selected($s['btn_size'],'large',false) ?>>Grand</option>
            </select>
          </div>
          <div class="rr-set-field">
            <label>Arrondi (px)</label>
            <input type="number" name="btn_radius" value="<?= esc_attr( $s['btn_radius'] ) ?>" min="0" max="30" />
          </div>
          <div class="rr-set-field">
            <label>Épaisseur de bordure (px)</label>
            <input type="number" name="btn_border_width" value="<?= esc_attr( $s['btn_border_width'] ) ?>" min="0" max="6" />
          </div>
          <div class="rr-set-field">
            <label>Couleur de bordure</label>
            <div class="rr-color-input">
              <input type="color" value="<?= esc_attr( $s['btn_border_color'] ) ?>" data-target="btn_border_color" />
              <input type="text" name="btn_border_color" value="<?= esc_attr( $s['btn_border_color'] ) ?>" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── EMAILS ── -->
    <div class="rr-tab-panel" id="tab-emails">
      <div class="rr-card-set">
        <h2>📨 Demande reçue (mode validation manuelle uniquement)</h2>
        <p class="desc">Envoyé immédiatement au client lorsqu'il fait une demande.</p>
        <div class="rr-row full">
          <div class="rr-set-field">
            <label>Sujet</label>
            <input type="text" name="email_received_subject" value="<?= esc_attr( $s['email_received_subject'] ) ?>" />
          </div>
          <div class="rr-set-field">
            <label>Corps du message</label>
            <textarea name="email_received_body"><?= esc_textarea( $s['email_received_body'] ) ?></textarea>
          </div>
        </div>
      </div>

      <div class="rr-card-set">
        <h2>✅ Confirmation</h2>
        <p class="desc">Envoyé en mode auto immédiatement, ou en manuel quand vous confirmez.</p>
        <div class="rr-row full">
          <div class="rr-set-field">
            <label>Sujet</label>
            <input type="text" name="email_confirmed_subject" value="<?= esc_attr( $s['email_confirmed_subject'] ) ?>" />
          </div>
          <div class="rr-set-field">
            <label>Corps du message</label>
            <textarea name="email_confirmed_body"><?= esc_textarea( $s['email_confirmed_body'] ) ?></textarea>
          </div>
        </div>
      </div>

      <div class="rr-card-set">
        <h2>❌ Refus</h2>
        <p class="desc">Envoyé quand vous refusez une demande depuis le dashboard.</p>
        <div class="rr-row full">
          <div class="rr-set-field">
            <label>Sujet</label>
            <input type="text" name="email_cancelled_subject" value="<?= esc_attr( $s['email_cancelled_subject'] ) ?>" />
          </div>
          <div class="rr-set-field">
            <label>Corps du message</label>
            <textarea name="email_cancelled_body"><?= esc_textarea( $s['email_cancelled_body'] ) ?></textarea>
          </div>
        </div>
      </div>

      <div class="rr-vars-box">
        <strong>Variables disponibles dans les e-mails :</strong><br>
        <code>{name}</code> nom du client &nbsp;
        <code>{date}</code> date &nbsp;
        <code>{time}</code> heure &nbsp;
        <code>{guests}</code> couverts &nbsp;
        <code>{notes}</code> notes &nbsp;
        <code>{site_name}</code> nom du restaurant
      </div>
    </div>

    <!-- ── SLOTS ── -->
    <div class="rr-tab-panel" id="tab-slots">
      <div class="rr-card-set">
        <h2>Créneaux horaires proposés</h2>
        <p class="desc">Listez les horaires séparés par une virgule, au format HH:MM.</p>
        <div class="rr-set-field">
          <textarea name="slots" rows="3"><?= esc_textarea( $s['slots'] ) ?></textarea>
          <p class="hint">Exemple : 11:00, 11:30, 12:00, 12:30, 19:00, 19:30, 20:00, 20:30</p>
        </div>
      </div>
    </div>

    <div class="rr-actions">
      <button type="submit" name="rr_reset" class="rr-btn-reset" onclick="return confirm('Réinitialiser tous les réglages ?');">Réinitialiser</button>
      <button type="submit" name="rr_save_settings" class="rr-btn-save">Enregistrer les réglages</button>
    </div>
  </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Tabs
  document.querySelectorAll('.rr-tab').forEach(function(t) {
    t.addEventListener('click', function() {
      document.querySelectorAll('.rr-tab').forEach(function(b) { b.classList.remove('active'); });
      document.querySelectorAll('.rr-tab-panel').forEach(function(p) { p.classList.remove('active'); });
      t.classList.add('active');
      document.getElementById('tab-' + t.dataset.tab).classList.add('active');
    });
  });

  // Color picker sync
  document.querySelectorAll('input[type=color][data-target]').forEach(function(picker) {
    var target = document.querySelector('input[name="' + picker.dataset.target + '"]');
    if (!target) return;
    picker.addEventListener('input', function() { target.value = picker.value; });
    target.addEventListener('input',  function() { if (/^#[0-9a-f]{6}$/i.test(target.value)) picker.value = target.value; });
  });

  // Mode cards (radio = mutually exclusive, checkbox = multi-select)
  document.querySelectorAll('.rr-mode').forEach(function(card) {
    card.addEventListener('click', function(e) {
      // ignore clicks coming from a real checked label-input
      if (e.target.tagName === 'INPUT') return;
      var radio = card.querySelector('input[type=radio]');
      var check = card.querySelector('input[type=checkbox]');
      if (radio) {
        // Reset siblings inside same group only
        var name = radio.name;
        document.querySelectorAll('.rr-mode').forEach(function(c) {
          var r = c.querySelector('input[type=radio]');
          if (r && r.name === name) c.classList.remove('selected');
        });
        card.classList.add('selected');
        radio.checked = true;
      } else if (check) {
        check.checked = !check.checked;
        card.classList.toggle('selected', check.checked);
      }
    });
  });
});
</script>
