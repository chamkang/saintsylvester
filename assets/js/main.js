/* SSMF v2 — slider, carousels, parallax, nav, reveals, counters, helix */
(function () {
  'use strict';
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- navbar: transparent at top, solid fixed after scrolling ---------- */
  const navbar = document.getElementById('navbar');
  if (navbar) {
    const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 90);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ---------- hero video: make sure it plays (some setups block autoplay) ---------- */
  const heroVideo = document.getElementById('heroVideo');
  if (heroVideo) {
    const tryPlay = () => heroVideo.play().catch(() => {});
    tryPlay();
    document.addEventListener('click', tryPlay, { once: true });
  }

  /* ---------- mobile menu (solid panel + scrim + scroll lock) ---------- */
  const toggle = document.getElementById('navToggle');
  const links = document.getElementById('navLinks');
  const scrim = document.getElementById('navScrim');
  function setMenu(open) {
    links.classList.toggle('open', open);
    toggle.classList.toggle('open', open);
    toggle.setAttribute('aria-expanded', open);
    document.body.classList.toggle('menu-open', open);
  }
  if (toggle && links) {
    toggle.addEventListener('click', () => setMenu(!links.classList.contains('open')));
    links.addEventListener('click', (e) => { if (e.target.closest('a')) setMenu(false); });
    if (scrim) scrim.addEventListener('click', () => setMenu(false));
    window.addEventListener('keydown', (e) => { if (e.key === 'Escape') setMenu(false); });
  }

  /* ---------- hero banner carousel ---------- */
  const slider = document.getElementById('heroSlider');
  if (slider) {
    const slides = Array.from(slider.querySelectorAll('.slide'));
    const dotsBox = document.getElementById('sliderDots');
    const counter = document.getElementById('slideNow');
    let cur = 0, timer = null;

    if (dotsBox) {
      slides.forEach((_, i) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.setAttribute('aria-label', 'Slide ' + (i + 1));
        if (i === 0) b.classList.add('on');
        b.addEventListener('click', () => { show(i); restart(); });
        dotsBox.appendChild(b);
      });
    }
    const dots = dotsBox ? Array.from(dotsBox.children) : [];

    function show(i) {
      cur = (i + slides.length) % slides.length;
      slides.forEach((s, k) => s.classList.toggle('active', k === cur));
      dots.forEach((d, k) => d.classList.toggle('on', k === cur));
      if (counter) counter.textContent = String(cur + 1).padStart(2, '0');
    }
    function restart() {
      clearInterval(timer);
      timer = setInterval(() => show(cur + 1), 6500); // banner texts always rotate (explicit requirement)
    }
    document.getElementById('slidePrev')?.addEventListener('click', () => { show(cur - 1); restart(); });
    document.getElementById('slideNext')?.addEventListener('click', () => { show(cur + 1); restart(); });

    // swipe
    let x0 = null;
    slider.addEventListener('touchstart', (e) => (x0 = e.touches[0].clientX), { passive: true });
    slider.addEventListener('touchend', (e) => {
      if (x0 === null) return;
      const dx = e.changedTouches[0].clientX - x0;
      if (Math.abs(dx) > 45) { show(cur + (dx < 0 ? 1 : -1)); restart(); }
      x0 = null;
    }, { passive: true });

    slider.addEventListener('mouseenter', () => clearInterval(timer));
    slider.addEventListener('mouseleave', restart);
    document.addEventListener('visibilitychange', () => (document.hidden ? clearInterval(timer) : restart()));
    restart();
  }

  /* ---------- testimonial carousel (auto) ---------- */
  const testi = document.getElementById('testiCarousel');
  if (testi) {
    const slides = Array.from(testi.querySelectorAll('.testi-slide'));
    const dotsBox = document.getElementById('testiDots');
    let cur = 0, timer = null;
    if (slides.length > 1) {
      slides.forEach((_, i) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.setAttribute('aria-label', 'Testimonial ' + (i + 1));
        if (i === 0) b.classList.add('on');
        b.addEventListener('click', () => { show(i); restart(); });
        dotsBox.appendChild(b);
      });
      const dots = Array.from(dotsBox.children);
      function show(i) {
        cur = (i + slides.length) % slides.length;
        slides.forEach((s, k) => s.classList.toggle('active', k === cur));
        dots.forEach((d, k) => d.classList.toggle('on', k === cur));
      }
      function restart() {
        clearInterval(timer);
        timer = setInterval(() => show(cur + 1), 7000);
      }
      restart();
    }
  }

  /* ---------- parallax backgrounds ---------- */
  const parallaxEls = Array.from(document.querySelectorAll('[data-parallax]'));
  if (parallaxEls.length && !reduced) {
    let ticking = false;
    function parallax() {
      parallaxEls.forEach((el) => {
        const speed = parseFloat(el.dataset.parallax) || 0.3;
        const r = el.parentElement.getBoundingClientRect();
        if (r.bottom < 0 || r.top > innerHeight) return;
        const progress = (r.top + r.height / 2 - innerHeight / 2) * speed;
        el.style.transform = 'translateY(' + progress.toFixed(1) + 'px)';
      });
      ticking = false;
    }
    window.addEventListener('scroll', () => {
      if (!ticking) { requestAnimationFrame(parallax); ticking = true; }
    }, { passive: true });
    parallax();
  }

  /* ---------- reveal on scroll ---------- */
  const revealables = document.querySelectorAll('.reveal, .reveal-stagger');
  if (revealables.length && 'IntersectionObserver' in window && !reduced) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    revealables.forEach((el) => io.observe(el));
  } else {
    revealables.forEach((el) => el.classList.add('in'));
  }

  /* ---------- count-up stats ---------- */
  const counters = document.querySelectorAll('[data-count]');
  if (counters.length) {
    const animate = (el) => {
      const target = parseInt(el.dataset.count, 10) || 0;
      const dur = 1700, t0 = performance.now();
      const fmt = (n) => n.toLocaleString(document.documentElement.lang === 'fr' ? 'fr-FR' : 'en-US');
      const tick = (t) => {
        const p = Math.min((t - t0) / dur, 1);
        el.textContent = fmt(Math.round(target * (1 - Math.pow(1 - p, 3))));
        if (p < 1) requestAnimationFrame(tick); else el.textContent = fmt(target);
      };
      reduced ? (el.textContent = fmt(target)) : requestAnimationFrame(tick);
    };
    const cio = new IntersectionObserver((entries) => {
      entries.forEach((en) => { if (en.isIntersecting) { animate(en.target); cio.unobserve(en.target); } });
    }, { threshold: 0.4 });
    counters.forEach((el) => cio.observe(el));
  }

  /* ---------- subtle 3D tilt (service & value cards only) ---------- */
  if (!reduced && window.matchMedia('(hover: hover)').matches) {
    document.querySelectorAll('.svc-card:not(.flagship), .value-card').forEach((card) => {
      let raf = null;
      card.addEventListener('pointermove', (e) => {
        if (raf) return;
        raf = requestAnimationFrame(() => {
          const r = card.getBoundingClientRect();
          const px = (e.clientX - r.left) / r.width - 0.5;
          const py = (e.clientY - r.top) / r.height - 0.5;
          card.style.transform = `perspective(900px) rotateY(${px * 5}deg) rotateX(${py * -5}deg) translateY(-6px)`;
          raf = null;
        });
      });
      card.addEventListener('pointerleave', () => {
        if (raf) { cancelAnimationFrame(raf); raf = null; }
        card.style.transition = 'transform .5s cubic-bezier(.22,.61,.36,1)';
        card.style.transform = '';
        setTimeout(() => (card.style.transition = ''), 500);
      });
    });
  }

  /* ---------- build the CSS-3D DNA helix ---------- */
  const helix = document.querySelector('.helix');
  if (helix) {
    const RUNGS = 15, H = helix.clientHeight || 400;
    for (let i = 0; i < RUNGS; i++) {
      const rung = document.createElement('div');
      rung.className = 'rung';
      rung.style.top = (i * (H - 16)) / (RUNGS - 1) + 'px';
      rung.style.animationDelay = -(i * 0.4) + 's';
      rung.innerHTML = '<i></i><i></i>';
      helix.appendChild(rung);
    }
  }

  /* ---------- quick-booking deep link ---------- */
  const qb = document.getElementById('quickbookForm');
  if (qb) {
    qb.addEventListener('submit', (e) => {
      e.preventDefault();
      const svc = qb.querySelector('[name=service]').value;
      const date = qb.querySelector('[name=date]').value;
      const q = new URLSearchParams();
      if (svc) q.set('service', svc);
      if (date) q.set('date', date);
      window.location.href = 'appointment.php' + (q.toString() ? '?' + q.toString() : '');
    });
  }
})();
