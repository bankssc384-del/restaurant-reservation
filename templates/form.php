<?php
$s     = rr_settings();
$slots = array_map( 'trim', explode( ',', $s['slots'] ) );
?>
<div class="rr-form" id="rr-form">

  <div id="rr-form-section">
    <header class="rr-form-head">
      <h2 class="rr-form-h"><?= esc_html( $s['form_title'] ) ?></h2>
      <p class="rr-form-sub"><?= esc_html( $s['form_subtitle'] ) ?></p>
    </header>

    <div class="rr-alert rr-err" id="rr-error" style="display:none;"></div>

    <div class="rr-grid">
      <div class="rr-field">
        <label class="rr-lab" for="rr-name">Prénom et nom <span class="rr-req">*</span></label>
        <input class="rr-inp" type="text" id="rr-name" placeholder="Marie Dupont" />
      </div>
      <div class="rr-field">
        <label class="rr-lab" for="rr-phone">Téléphone <span class="rr-req">*</span></label>
        <input class="rr-inp" type="tel" id="rr-phone" placeholder="+33 6 12 34 56 78" />
      </div>
      <div class="rr-field rr-full">
        <label class="rr-lab" for="rr-email">Adresse e-mail <span class="rr-req">*</span></label>
        <input class="rr-inp" type="email" id="rr-email" placeholder="marie@exemple.fr" />
      </div>
      <div class="rr-field">
        <label class="rr-lab" for="rr-date">Date <span class="rr-req">*</span></label>
        <input class="rr-inp" type="date" id="rr-date" />
      </div>
      <div class="rr-field">
        <label class="rr-lab" for="rr-guests">Nombre de couverts <span class="rr-req">*</span></label>
        <select class="rr-inp" id="rr-guests">
          <option value="">Sélectionner</option>
          <option value="1">1 personne</option>
          <option value="2">2 personnes</option>
          <option value="3">3 personnes</option>
          <option value="4">4 personnes</option>
          <option value="5">5 personnes</option>
          <option value="6">6 personnes</option>
          <option value="7">7-8 personnes</option>
          <option value="9">9+ personnes</option>
        </select>
      </div>
      <div class="rr-field rr-full">
        <label class="rr-lab">Créneau horaire <span class="rr-req">*</span></label>
        <div class="rr-slots" id="rr-slots">
          <?php foreach ( $slots as $slot ) : if ( ! $slot ) continue; ?>
            <button type="button" class="rr-slot" data-time="<?= esc_attr( $slot ) ?>"><?= esc_html( str_replace(':', 'h', $slot) ) ?></button>
          <?php endforeach; ?>
        </div>
        <input type="hidden" id="rr-time" />
      </div>
      <div class="rr-field rr-full">
        <label class="rr-lab" for="rr-notes">Demandes particulières <span class="rr-opt">(optionnel)</span></label>
        <textarea class="rr-inp" id="rr-notes" rows="3" placeholder="Allergie, chaise bébé, occasion spéciale…"></textarea>
      </div>
    </div>

    <button class="rr-submit" id="rr-submit" type="button">
      <span id="rr-submit-text"><?= esc_html( $s['btn_text_label'] ) ?></span>
      <span id="rr-submit-loader" style="display:none;">Envoi en cours…</span>
    </button>
  </div>

  <div id="rr-success" style="display:none;">
    <div class="rr-success-icon">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
    </div>
    <h3 class="rr-success-h"><?= esc_html( $s['success_title'] ) ?></h3>
    <p class="rr-success-p"><?= esc_html( $s['success_text'] ) ?></p>
    <button class="rr-back" id="rr-back" type="button">Faire une nouvelle réservation</button>
  </div>

</div>
