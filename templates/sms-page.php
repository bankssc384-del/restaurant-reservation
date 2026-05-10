<?php if ( ! defined('ABSPATH') ) exit; ?>

<style>
  .rr-sms-wrap { max-width: 980px; }
  .rr-sms-wrap h1 { display:flex; align-items:center; gap:10px; margin: 18px 0 22px; }
  .rr-sms-card { background:#fff; border:1px solid #ddd; border-radius:10px; padding:24px 28px; margin-bottom:18px; }
  .rr-sms-card h2 { font-size:15px; margin:0 0 4px; color:#111; }
  .rr-sms-card p.desc { font-size:13px; color:#777; margin:0 0 18px; line-height:1.6; }
  .rr-sms-card a { color:#0F766E; }

  .rr-sms-toggle { display:flex; align-items:center; gap:10px; padding:12px 14px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:18px; cursor:pointer; }
  .rr-sms-toggle input { width:18px; height:18px; cursor:pointer; }
  .rr-sms-toggle .lab-main { font-weight:600; font-size:13px; color:#111; }
  .rr-sms-toggle .lab-desc { font-size:12px; color:#777; margin-top:2px; }

  .rr-sms-row { display:grid; grid-template-columns: 1fr 1fr; gap:18px; margin-bottom:14px; }
  .rr-sms-row.full { grid-template-columns:1fr; }
  .rr-sms-field label { display:block; font-size:13px; font-weight:600; color:#333; margin-bottom:6px; }
  .rr-sms-field input[type=text], .rr-sms-field input[type=tel], .rr-sms-field textarea {
    width:100%; padding:8px 12px; border:1px solid #ccc; border-radius:6px; font-size:14px;
  }
  .rr-sms-field textarea { min-height:80px; font-family:inherit; resize:vertical; }
  .rr-sms-field .hint { font-size:11px; color:#888; margin-top:4px; }
  .rr-sms-field .char-counter { font-size:11px; color:#666; margin-top:4px; text-align:right; }
  .rr-sms-field .char-counter.warn { color:#b45309; }
  .rr-sms-field .char-counter.over { color:#b91c1c; font-weight:600; }

  .rr-test-box { background:#f0fdfa; border:1px solid #99f6e4; border-radius:8px; padding:14px 18px; }
  .rr-test-row { display:flex; gap:10px; align-items:end; }
  .rr-test-row .rr-sms-field { flex:1; margin-bottom:0; }

  .rr-vars-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px 14px; font-size:12px; color:#475569; line-height:1.7; }
  .rr-vars-box code { background:#fff; border:1px solid #e2e8f0; border-radius:4px; padding:1px 6px; font-size:11px; }

  .rr-actions-row { display:flex; gap:10px; justify-content:flex-end; padding:16px 0; }
  .rr-btn-save { background:#0F766E; color:#fff; border:none; padding:10px 24px; border-radius:6px; cursor:pointer; font-weight:600; font-size:14px; }
  .rr-btn-save:hover { background:#0d5e57; }
  .rr-btn-test { background:#fff; color:#0F766E; border:1px solid #0F766E; padding:8px 16px; border-radius:6px; cursor:pointer; font-weight:600; font-size:13px; }
  .rr-btn-test:hover { background:#f0fdfa; }

  #rr-test-result { margin-top:10px; padding:10px 14px; border-radius:6px; font-size:13px; display:none; }
  #rr-test-result.success { background:#f0fdf4; color:#15803d; border:1px solid #bbf7d0; }
  #rr-test-result.error   { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }

  .brevo-info { background:#fff7ed; border:1px solid #fdba74; border-radius:8px; padding:14px 18px; font-size:13px; color:#7c2d12; margin-bottom:18px; line-height:1.6; }
</style>

<div class="wrap rr-sms-wrap">
  <h1>📱 Notifications SMS</h1>

  <div class="brevo-info">
    <strong>ℹ️ Service Brevo (ex-Sendinblue)</strong> — Pour envoyer des SMS, vous avez besoin d'un compte Brevo et d'une clé API.
    <br>1. Créez un compte gratuit sur <a href="https://www.brevo.com" target="_blank">brevo.com</a>
    <br>2. Achetez des crédits SMS (à partir de quelques euros)
    <br>3. Récupérez votre clé API dans <em>Paramètres → Clés API</em>
  </div>

  <form method="post">
    <?php wp_nonce_field( 'rr_save_sms' ); ?>

    <div class="rr-sms-card">
      <h2>Configuration Brevo</h2>

      <label class="rr-sms-toggle">
        <input type="checkbox" name="sms_enabled" value="1" <?= checked( $s['sms_enabled'], 1, false ) ?> />
        <div>
          <div class="lab-main">Activer les notifications SMS</div>
          <div class="lab-desc">Une fois activé, vous pourrez choisir SMS comme canal de notification dans les Réglages.</div>
        </div>
      </label>

      <div class="rr-sms-row">
        <div class="rr-sms-field">
          <label>Clé API Brevo</label>
          <input type="text" name="sms_api_key" value="<?= esc_attr( $s['sms_api_key'] ) ?>" placeholder="xkeysib-..." />
          <p class="hint">Trouvable dans Brevo → SMTP & API → Clés API</p>
        </div>
        <div class="rr-sms-field">
          <label>Nom de l'expéditeur</label>
          <input type="text" name="sms_sender" value="<?= esc_attr( $s['sms_sender'] ) ?>" maxlength="11" placeholder="Resto" />
          <p class="hint">11 caractères max, alphanumérique. Sera affiché comme expéditeur du SMS.</p>
        </div>
      </div>
    </div>

    <div class="rr-sms-card">
      <h2>📨 Modèles de SMS</h2>
      <p class="desc">Personnalisez les messages envoyés. Utilisez les variables ci-dessous. Les SMS comptent en segments de 160 caractères : un message plus long sera facturé en plusieurs SMS.</p>

      <div class="rr-vars-box" style="margin-bottom:18px;">
        <strong>Variables :</strong>
        <code>{name}</code> nom &nbsp;
        <code>{date}</code> date &nbsp;
        <code>{time}</code> heure &nbsp;
        <code>{guests}</code> couverts
      </div>

      <div class="rr-sms-row full">
        <div class="rr-sms-field">
          <label>SMS — Demande reçue (mode validation manuelle)</label>
          <textarea name="sms_msg_received" oninput="updateCounter(this)"><?= esc_textarea( $s['sms_msg_received'] ) ?></textarea>
          <div class="char-counter"><span class="cnt">0</span> caractères · <span class="seg">1</span> SMS</div>
        </div>
        <div class="rr-sms-field">
          <label>SMS — Confirmation</label>
          <textarea name="sms_msg_confirmed" oninput="updateCounter(this)"><?= esc_textarea( $s['sms_msg_confirmed'] ) ?></textarea>
          <div class="char-counter"><span class="cnt">0</span> caractères · <span class="seg">1</span> SMS</div>
        </div>
        <div class="rr-sms-field">
          <label>SMS — Refus</label>
          <textarea name="sms_msg_cancelled" oninput="updateCounter(this)"><?= esc_textarea( $s['sms_msg_cancelled'] ) ?></textarea>
          <div class="char-counter"><span class="cnt">0</span> caractères · <span class="seg">1</span> SMS</div>
        </div>
      </div>
    </div>

    <div class="rr-sms-card">
      <h2>🧪 Tester l'envoi</h2>
      <p class="desc">Envoyez un SMS de test pour vérifier la configuration. Les réglages doivent être enregistrés au préalable.</p>

      <div class="rr-test-box">
        <div class="rr-test-row">
          <div class="rr-sms-field">
            <label>Numéro de test</label>
            <input type="tel" id="rr-test-phone" placeholder="+33 6 12 34 56 78" />
          </div>
          <button type="button" class="rr-btn-test" id="rr-test-btn">Envoyer un SMS de test</button>
        </div>
        <div id="rr-test-result"></div>
      </div>
    </div>

    <div class="rr-actions-row">
      <button type="submit" name="rr_save_sms" class="rr-btn-save">Enregistrer les réglages SMS</button>
    </div>
  </form>
</div>

<script>
function updateCounter(textarea) {
  var len = textarea.value.length;
  var seg = Math.max(1, Math.ceil(len / 160));
  var box = textarea.parentElement.querySelector('.char-counter');
  box.querySelector('.cnt').textContent = len;
  box.querySelector('.seg').textContent = seg;
  box.classList.toggle('warn', len > 160);
  box.classList.toggle('over', seg > 2);
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('textarea[name^="sms_msg_"]').forEach(updateCounter);

  var nonce = '<?= wp_create_nonce("rr_sms_nonce") ?>';
  var btn = document.getElementById('rr-test-btn');
  if (btn) btn.addEventListener('click', function() {
    var phone = document.getElementById('rr-test-phone').value.trim();
    var res   = document.getElementById('rr-test-result');
    if (!phone) {
      res.className = 'error'; res.style.display = 'block';
      res.textContent = 'Veuillez entrer un numéro.';
      return;
    }
    btn.disabled = true; btn.textContent = 'Envoi…';
    var fd = new FormData();
    fd.append('action', 'rr_test_sms');
    fd.append('nonce', nonce);
    fd.append('phone', phone);
    fetch(ajaxurl, { method:'POST', body:fd })
      .then(function(r){ return r.json(); })
      .then(function(j) {
        btn.disabled = false; btn.textContent = 'Envoyer un SMS de test';
        res.style.display = 'block';
        if (j.success) {
          res.className = 'success';
          res.textContent = '✓ SMS envoyé ! Vérifiez votre téléphone.';
        } else {
          res.className = 'error';
          res.textContent = '✗ ' + (j.data || 'Erreur inconnue');
        }
      });
  });
});
</script>
