document.addEventListener('DOMContentLoaded', function () {
  var dateInput = document.getElementById('rr-date');
  if (!dateInput) return;
  dateInput.min = new Date().toISOString().split('T')[0];

  document.querySelectorAll('.rr-slot').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.rr-slot').forEach(function(b) { b.classList.remove('selected'); });
      btn.classList.add('selected');
      document.getElementById('rr-time').value = btn.dataset.time;
    });
  });

  var submitBtn = document.getElementById('rr-submit');
  submitBtn.addEventListener('click', function() {
    var v = function(id) { return (document.getElementById(id).value || '').trim(); };
    var name = v('rr-name'), email = v('rr-email'), phone = v('rr-phone');
    var date = v('rr-date'), time = v('rr-time'), guests = v('rr-guests'), notes = v('rr-notes');
    var errEl = document.getElementById('rr-error');

    if (!name || !email || !phone || !date || !time || !guests) {
      errEl.textContent = 'Merci de remplir tous les champs obligatoires et de choisir un créneau.';
      errEl.style.display = 'block';
      errEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      return;
    }
    errEl.style.display = 'none';
    submitBtn.disabled = true;
    document.getElementById('rr-submit-text').style.display   = 'none';
    document.getElementById('rr-submit-loader').style.display = 'inline';

    var fd = new FormData();
    fd.append('action', 'rr_submit');
    fd.append('nonce',  RR.nonce);
    ['name','email','phone','date','time','guests','notes'].forEach(function(k) { fd.append(k, eval(k)); });

    fetch(RR.ajax_url, { method: 'POST', body: fd })
      .then(function(r) { return r.json(); })
      .then(function(res) {
        if (res.success) {
          document.getElementById('rr-form-section').style.display = 'none';
          document.getElementById('rr-success').style.display = 'block';
          window.scrollTo({ top: document.getElementById('rr-form').offsetTop - 30, behavior: 'smooth' });
        } else {
          errEl.textContent = res.data || 'Erreur. Veuillez réessayer.';
          errEl.style.display = 'block';
          submitBtn.disabled = false;
          document.getElementById('rr-submit-text').style.display = 'inline';
          document.getElementById('rr-submit-loader').style.display = 'none';
        }
      })
      .catch(function() {
        errEl.textContent = 'Erreur réseau. Veuillez réessayer.';
        errEl.style.display = 'block';
        submitBtn.disabled = false;
        document.getElementById('rr-submit-text').style.display = 'inline';
        document.getElementById('rr-submit-loader').style.display = 'none';
      });
  });

  document.getElementById('rr-back').addEventListener('click', function() {
    document.getElementById('rr-form-section').style.display = 'block';
    document.getElementById('rr-success').style.display = 'none';
    ['rr-name','rr-email','rr-phone','rr-date','rr-notes'].forEach(function(id) { document.getElementById(id).value = ''; });
    document.getElementById('rr-guests').value = '';
    document.getElementById('rr-time').value = '';
    document.querySelectorAll('.rr-slot').forEach(function(b) { b.classList.remove('selected'); });
    submitBtn.disabled = false;
    document.getElementById('rr-submit-text').style.display = 'inline';
    document.getElementById('rr-submit-loader').style.display = 'none';
  });
});
