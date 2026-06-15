/* SSMF — 4-step appointment wizard (TRD FR-B). Data injected as window.SSMF_BOOKING. */
(function () {
  'use strict';
  const D = window.SSMF_BOOKING;
  if (!D) return;
  const i18n = D.i18n;

  const state = { step: 1, service: null, doctor: null, anyDoctor: false, date: null, time: null, slotDoctor: null };

  const panels = {
    1: document.getElementById('panel1'),
    2: document.getElementById('panel2'),
    3: document.getElementById('panel3'),
    4: document.getElementById('panel4'),
  };
  const stepsEl = Array.from(document.querySelectorAll('.wstep'));
  const btnBack = document.getElementById('wzBack');
  const btnNext = document.getElementById('wzNext');
  const errBox = document.getElementById('wzError');

  const fmtDate = (ymd) => {
    const d = new Date(ymd + 'T12:00:00');
    return d.toLocaleDateString(D.lang === 'fr' ? 'fr-FR' : 'en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
  };

  function showError(msg) {
    errBox.innerHTML = msg ? '<div class="alert alert-error">' + msg + '</div>' : '';
    if (msg) errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  /* ---------- rendering ---------- */

  function renderServices() {
    panels[1].innerHTML = '<div class="pick-grid">' + D.services.map((s) =>
      `<button type="button" class="pick-card${state.service === s.id ? ' selected' : ''}" data-svc="${s.id}">
         <span class="svc-chip">${s.iconHtml}</span>
         <span><strong>${esc(s.name)}</strong><small>${i18n.duration.replace('{min}', s.duration)}</small></span>
       </button>`).join('') + '</div>';
    panels[1].querySelectorAll('[data-svc]').forEach((b) =>
      b.addEventListener('click', () => {
        state.service = parseInt(b.dataset.svc, 10);
        state.doctor = null; state.anyDoctor = false; state.date = null; state.time = null;
        go(2);
      }));
  }

  function doctorsForService(svcId) {
    const ids = D.links.filter((l) => l[1] === svcId).map((l) => l[0]);
    return D.doctors.filter((d) => ids.includes(d.id));
  }

  function renderDoctors() {
    const docs = doctorsForService(state.service);
    let html = '<div class="pick-grid">';
    if (docs.length > 1) {
      html += `<button type="button" class="pick-card${state.anyDoctor ? ' selected' : ''}" data-doc="any">
        <span class="svc-chip"><svg class="svc-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><circle cx="9" cy="8" r="3.5"/><circle cx="16.5" cy="9.5" r="2.8"/><path d="M3 20c.8-3 3-4.5 6-4.5s5.2 1.5 6 4.5M14 15.7c2.4.2 4.3 1.5 5 4.3"/></svg></span>
        <span><strong>${i18n.anyDoctor}</strong><small>${i18n.anyDoctorSub}</small></span></button>`;
    }
    html += docs.map((d) =>
      `<button type="button" class="pick-card${state.doctor === d.id ? ' selected' : ''}" data-doc="${d.id}">
         <span class="doc-avatar"><span>${esc(d.initials)}</span></span>
         <span><strong>${esc(d.name)}</strong><small>${esc(d.specialty)}</small></span>
       </button>`).join('') + '</div>';
    panels[2].innerHTML = html;
    panels[2].querySelectorAll('[data-doc]').forEach((b) =>
      b.addEventListener('click', () => {
        if (b.dataset.doc === 'any') { state.anyDoctor = true; state.doctor = null; }
        else { state.doctor = parseInt(b.dataset.doc, 10); state.anyDoctor = false; }
        state.date = null; state.time = null;
        go(3);
      }));
  }

  function renderDates() {
    const days = [];
    const now = new Date();
    for (let i = 0; i < D.daysAhead; i++) {
      const d = new Date(now); d.setDate(now.getDate() + i);
      days.push(d);
    }
    const dayName = (d) => d.toLocaleDateString(D.lang === 'fr' ? 'fr-FR' : 'en-GB', { weekday: 'short' });
    const monName = (d) => d.toLocaleDateString(D.lang === 'fr' ? 'fr-FR' : 'en-GB', { month: 'short' });
    const ymd = (d) => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');

    panels[3].innerHTML =
      `<p style="font-weight:600;margin-bottom:10px">${i18n.pickDate}</p>
       <div class="date-strip" id="dateStrip">` +
      days.map((d) =>
        `<button type="button" class="date-pill${state.date === ymd(d) ? ' selected' : ''}" data-date="${ymd(d)}">
           <span class="dp-day">${dayName(d)}</span><span class="dp-num">${d.getDate()}</span><span class="dp-mon">${monName(d)}</span>
         </button>`).join('') +
      `</div>
       <div class="date-legend"><span class="dl dl-open"></span><span>${i18n.legendOpen}</span><span class="dl dl-full"></span><span>${i18n.legendFull}</span></div>
       <div id="slotZone"><p class="slot-empty">${i18n.pickDate}…</p></div>`;

    panels[3].querySelectorAll('[data-date]').forEach((b) =>
      b.addEventListener('click', () => {
        panels[3].querySelectorAll('.date-pill').forEach((x) => x.classList.remove('selected'));
        b.classList.add('selected');
        state.date = b.dataset.date; state.time = null; state.slotDoctor = null;
        updateSummary();
        loadSlots();
      }));
    loadCalendar();
    if (state.date) loadSlots();
  }

  async function loadCalendar() {
    const q = new URLSearchParams();
    if (state.anyDoctor) q.set('service_id', state.service); else q.set('doctor_id', state.doctor);
    try {
      const res = await fetch('api/calendar.php?' + q.toString());
      const map = await res.json();
      document.querySelectorAll('.date-pill').forEach((pill) => {
        pill.classList.remove('is-full', 'is-off');
        const st = map[pill.dataset.date];
        if (st === 'full') pill.classList.add('is-full');
        else if (st === 'off') pill.classList.add('is-off');
      });
    } catch (e) { /* leave pills neutral on error */ }
  }

  async function loadSlots() {
    const zone = document.getElementById('slotZone');
    zone.innerHTML = `<p class="slot-empty">${i18n.loading}</p>`;
    const q = new URLSearchParams({ date: state.date });
    if (state.anyDoctor) q.set('service_id', state.service); else q.set('doctor_id', state.doctor);
    try {
      const res = await fetch('api/slots.php?' + q.toString());
      const slots = await res.json();
      if (!Array.isArray(slots) || !slots.length) {
        zone.innerHTML = `<p class="slot-empty">${i18n.noSlots}</p>`;
        return;
      }
      const anyOpen = slots.some((s) => s.available);
      const heading = anyOpen ? i18n.pickSlot : i18n.dayFull;
      zone.innerHTML = `<p style="font-weight:600;margin:14px 0 0">${heading}</p><div class="slot-grid">` +
        slots.map((s) => s.available
          ? `<button type="button" class="slot-chip" data-time="${s.time}" data-docid="${s.doctor_id}" data-docname="${esc(s.doctor_name)}">
               ${s.time}${state.anyDoctor ? `<small>${esc(s.doctor_short)}</small>` : ''}
             </button>`
          : `<button type="button" class="slot-chip is-booked" disabled aria-disabled="true" title="${i18n.booked}">
               ${s.time}<small>${i18n.booked}</small>
             </button>`).join('') + '</div>';
      zone.querySelectorAll('.slot-chip:not(.is-booked)').forEach((b) =>
        b.addEventListener('click', () => {
          zone.querySelectorAll('.slot-chip').forEach((x) => x.classList.remove('selected'));
          b.classList.add('selected');
          state.time = b.dataset.time;
          state.slotDoctor = { id: parseInt(b.dataset.docid, 10), name: b.dataset.docname };
          updateSummary();
          // advance automatically, like steps 1 and 2 — picking a time IS the choice
          setTimeout(() => { if (state.step === 3 && state.time) go(4); }, 350);
        }));
    } catch (e) {
      zone.innerHTML = `<p class="slot-empty">${i18n.errGeneric}</p>`;
    }
  }

  /* ---------- summary ---------- */

  function updateSummary() {
    const svc = D.services.find((s) => s.id === state.service);
    const doc = state.slotDoctor ? state.slotDoctor.name
      : state.anyDoctor ? i18n.anyDoctor
      : state.doctor ? (D.doctors.find((d) => d.id === state.doctor) || {}).name : null;
    const rows = [];
    if (svc) rows.push(row('svc', svc.name, i18n.step1));
    if (doc) rows.push(row('doc', doc, i18n.step2));
    if (state.date) rows.push(row('cal', fmtDate(state.date) + (state.time ? ' · ' + state.time : ''), i18n.step3));
    document.getElementById('sumRows').innerHTML = rows.length
      ? rows.join('') : `<p class="sum-placeholder">${i18n.summaryEmpty}</p>`;

    function row(kind, main, label) {
      const icons = {
        svc: '<path d="M12 3v18M3 12h18"/>',
        doc: '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-3.5 4.5-5 8-5s6.5 1.5 8 5"/>',
        cal: '<rect x="4" y="6" width="16" height="15" rx="2"/><path d="M4 10h16M8 3v5M16 3v5"/>',
      };
      return `<div class="sum-row"><svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">${icons[kind]}</svg>
        <span><small>${label}</small><b>${main}</b></span></div>`;
    }
  }

  /* ---------- navigation ---------- */

  function go(step) {
    showError('');
    state.step = step;
    Object.entries(panels).forEach(([k, p]) => {
      const active = parseInt(k, 10) === step;
      p.hidden = !active;
      if (active) { p.classList.remove('entering'); void p.offsetWidth; p.classList.add('entering'); }
    });
    stepsEl.forEach((el, i) => {
      el.classList.toggle('active', i + 1 === step);
      el.classList.toggle('done', i + 1 < step);
    });
    btnBack.style.visibility = step === 1 ? 'hidden' : 'visible';
    btnNext.hidden = step === 1 || step === 2; // those advance by picking a card
    btnNext.textContent = step === 4 ? i18n.confirm : i18n.next;
    if (step === 1) renderServices();
    if (step === 2) renderDoctors();
    if (step === 3) renderDates();
    updateSummary();
    document.getElementById('wizardTop').scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  btnBack.addEventListener('click', () => go(Math.max(1, state.step - 1)));

  btnNext.addEventListener('click', () => {
    if (state.step === 3) {
      if (!state.date || !state.time) { showError(i18n.errPickSlot); return; }
      go(4);
    } else if (state.step === 4) {
      submit();
    }
  });

  /* ---------- submit ---------- */

  async function submit() {
    const f = document.getElementById('bookForm');
    const name = f.name.value.trim();
    const phone = f.phone.value.trim();
    if (!name || !phone) { showError(i18n.errRequired); return; }
    const forOther = f.querySelector('[name=booking_for]:checked').value === 'other';
    const otherName = f.other_name.value.trim();
    if (forOther && !otherName) { showError(i18n.errRequired); return; }

    btnNext.disabled = true;
    try {
      const res = await fetch('api/book.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          csrf: D.csrf,
          service_id: state.service,
          doctor_id: state.slotDoctor.id,
          date: state.date,
          time: state.time,
          name, phone,
          email: f.email.value.trim(),
          mrn: f.mrn.value.trim(),
          booking_for: forOther ? 'other' : 'self',
          other_name: otherName,
          notes: f.notes.value.trim(),
          website: f.website.value, // honeypot
        }),
      });
      const out = await res.json();
      if (!res.ok || !out.ok) {
        showError(out.error || i18n.errGeneric);
        if (out.code === 'slot_taken') { go(3); loadSlots(); }
        btnNext.disabled = false;
        return;
      }
      if (out.pay_url) { window.location.href = out.pay_url; return; } // online consultation fee
      renderSuccess(out, {
        patientName: forOther ? otherName : name,
        phone,
        alreadyRegistered: f.mrn.value.trim() !== '',
      });
    } catch (e) {
      showError(i18n.errGeneric);
      btnNext.disabled = false;
    }
  }

  function renderSuccess(out, patient) {
    const wrap = document.getElementById('wizardWrap');
    const waText = encodeURIComponent(i18n.waTemplate.replace('{ref}', out.reference).replace('{date}', fmtDate(state.date)).replace('{time}', state.time));
    // post-booking registration prompt (FR-R), prefilled — skipped when an MRN was given
    const regBlock = patient && !patient.alreadyRegistered
      ? `<div class="success-reg">
           <h3>${i18n.regTitle}</h3>
           <p>${i18n.regP}</p>
           <a class="btn btn-navy" href="register.php?name=${encodeURIComponent(patient.patientName)}&phone=${encodeURIComponent(patient.phone)}">${i18n.regBtn}</a>
         </div>`
      : '';
    wrap.innerHTML =
      `<div class="success-stage">
         <div class="success-check"><svg viewBox="0 0 24 24"><path d="M4 12.5 9.5 18 20 6.5"/></svg></div>
         <h2>${i18n.successTitle}</h2>
         <p style="color:var(--n-600);max-width:460px;margin:0 auto 6px">${i18n.successSub}</p>
         <div class="ref-code">${out.reference}</div>
         <p style="font-size:.9rem;color:var(--n-600)">${i18n.pendingNote}<br>${i18n.manageNote}</p>
         <div class="success-actions">
           <a class="btn btn-primary" target="_blank" rel="noopener" href="https://wa.me/${D.whatsapp}?text=${waText}">${i18n.waConfirm}</a>
           <a class="btn btn-outline" href="appointment.php">${i18n.bookNew}</a>
         </div>
         ${regBlock}
       </div>`;
    wrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function esc(s) { return String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])); }

  /* booking_for toggle shows "patient name" field */
  document.querySelectorAll('[name=booking_for]').forEach((r) =>
    r.addEventListener('change', () => {
      document.getElementById('otherNameField').hidden = document.querySelector('[name=booking_for]:checked').value !== 'other';
    }));

  /* ---------- init (with quick-book prefill) ---------- */
  const params = new URLSearchParams(location.search);
  const preSvc = params.get('service');
  if (preSvc) {
    const svc = D.services.find((s) => s.slug === preSvc || String(s.id) === preSvc);
    if (svc) state.service = svc.id;
  }
  const preDate = params.get('date');
  if (preDate && /^\d{4}-\d{2}-\d{2}$/.test(preDate)) state.date = preDate;

  go(state.service ? 2 : 1);
})();
