/* SSMF — homepage hero: Three.js "cells of life" scene.
   Decorative only: degrades to the CSS poster when WebGL/Three is unavailable
   or the user prefers reduced motion. */
(function () {
  'use strict';
  const mount = document.getElementById('hero3d');
  if (!mount || typeof THREE === 'undefined') return;
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  let renderer;
  try {
    renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true, powerPreference: 'low-power' });
  } catch (e) { return; } // no WebGL → keep CSS poster

  const DPR = Math.min(window.devicePixelRatio || 1, 2);
  renderer.setPixelRatio(DPR);
  renderer.setSize(mount.clientWidth, mount.clientHeight);
  mount.appendChild(renderer.domElement);
  // hide the CSS poster orbs once WebGL is live (keep the dashed ring feel in 3D instead)
  const poster = document.querySelector('.hero-poster');
  if (poster) poster.style.opacity = '0.25';

  const scene = new THREE.Scene();
  const camera = new THREE.PerspectiveCamera(45, mount.clientWidth / mount.clientHeight, 0.1, 100);
  camera.position.set(0, 0, 9);

  scene.add(new THREE.AmbientLight(0xffffff, 0.75));
  const key = new THREE.DirectionalLight(0xffffff, 0.9);
  key.position.set(4, 6, 6);
  scene.add(key);
  const greenGlow = new THREE.PointLight(0x0fa968, 1.4, 18);
  greenGlow.position.set(-4, -2, 4);
  scene.add(greenGlow);
  const blueGlow = new THREE.PointLight(0x1c6dd0, 1.2, 18);
  blueGlow.position.set(4, 3, 3);
  scene.add(blueGlow);

  const group = new THREE.Group();
  scene.add(group);

  const matBlue = new THREE.MeshPhysicalMaterial({
    color: 0x1c6dd0, roughness: 0.25, metalness: 0.05,
    clearcoat: 0.8, clearcoatRoughness: 0.25, transparent: true, opacity: 0.95,
  });
  const matGreen = new THREE.MeshPhysicalMaterial({
    color: 0x0fa968, roughness: 0.3, metalness: 0.05,
    clearcoat: 0.7, clearcoatRoughness: 0.3, transparent: true, opacity: 0.95,
  });
  const matGlass = new THREE.MeshPhysicalMaterial({
    color: 0xbcd9fb, roughness: 0.1, metalness: 0,
    transparent: true, opacity: 0.32, clearcoat: 1,
  });

  // mother cell + glass shell
  const core = new THREE.Mesh(new THREE.SphereGeometry(1.55, 48, 48), matBlue);
  group.add(core);
  const shell = new THREE.Mesh(new THREE.SphereGeometry(2.05, 48, 48), matGlass);
  group.add(shell);

  // dividing cells in orbit (the fertility metaphor)
  const orbiters = [];
  const orbitDefs = [
    { r: 3.1, size: 0.55, speed: 0.45, mat: matGreen, tilt: 0.4, phase: 0 },
    { r: 3.5, size: 0.34, speed: -0.3, mat: matBlue, tilt: -0.55, phase: 2.0 },
    { r: 2.8, size: 0.26, speed: 0.6, mat: matGreen, tilt: 0.9, phase: 4.1 },
    { r: 3.9, size: 0.42, speed: 0.25, mat: matGlass, tilt: -0.2, phase: 1.2 },
    { r: 3.3, size: 0.18, speed: -0.55, mat: matBlue, tilt: 0.7, phase: 5.3 },
  ];
  orbitDefs.forEach((d) => {
    const m = new THREE.Mesh(new THREE.SphereGeometry(d.size, 32, 32), d.mat);
    group.add(m);
    orbiters.push({ mesh: m, ...d });
  });

  // dashed-feel orbit rings
  [3.1, 3.9].forEach((r, i) => {
    const ring = new THREE.Mesh(
      new THREE.TorusGeometry(r, 0.015, 8, 120),
      new THREE.MeshBasicMaterial({ color: i ? 0x0fa968 : 0x1c6dd0, transparent: true, opacity: 0.35 })
    );
    ring.rotation.x = Math.PI / 2.4 + i * 0.5;
    ring.rotation.y = i * 0.6;
    group.add(ring);
  });

  // floating micro-particles
  const pGeo = new THREE.BufferGeometry();
  const N = 90, pos = new Float32Array(N * 3);
  for (let i = 0; i < N * 3; i++) pos[i] = (Math.random() - 0.5) * 11;
  pGeo.setAttribute('position', new THREE.BufferAttribute(pos, 3));
  const points = new THREE.Points(pGeo, new THREE.PointsMaterial({ color: 0x6ea8e8, size: 0.045, transparent: true, opacity: 0.7 }));
  scene.add(points);

  // gentle mouse parallax
  let tx = 0, ty = 0, mx = 0, my = 0;
  window.addEventListener('pointermove', (e) => {
    tx = (e.clientX / window.innerWidth - 0.5) * 0.5;
    ty = (e.clientY / window.innerHeight - 0.5) * 0.35;
  }, { passive: true });

  let running = true;
  document.addEventListener('visibilitychange', () => { running = !document.hidden; if (running) loop(performance.now()); });

  window.addEventListener('resize', () => {
    const w = mount.clientWidth, h = mount.clientHeight;
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    renderer.setSize(w, h);
  });

  const clock = new THREE.Clock();
  function loop() {
    if (!running) return;
    requestAnimationFrame(loop);
    const t = clock.getElapsedTime();

    group.rotation.y = t * 0.12;
    core.position.y = Math.sin(t * 0.8) * 0.12;
    shell.scale.setScalar(1 + Math.sin(t * 1.6) * 0.02); // breathing shell

    orbiters.forEach((o) => {
      const a = t * o.speed + o.phase;
      o.mesh.position.set(Math.cos(a) * o.r, Math.sin(a) * o.r * Math.sin(o.tilt), Math.sin(a) * o.r * Math.cos(o.tilt));
      o.mesh.rotation.y = t;
    });

    points.rotation.y = t * 0.03;

    mx += (tx - mx) * 0.04;
    my += (ty - my) * 0.04;
    camera.position.x = mx * 2.2;
    camera.position.y = -my * 1.6;
    camera.lookAt(0, 0, 0);

    renderer.render(scene, camera);
  }
  loop();
})();
