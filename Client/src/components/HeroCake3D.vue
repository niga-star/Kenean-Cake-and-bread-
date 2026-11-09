<template>
  <div
    ref="containerRef"
    class="relative w-full h-[380px] sm:h-[440px] md:h-[480px] lg:h-[540px] xl:h-[580px] flex items-center justify-center select-none touch-none group"
    @pointerdown="onPointerDown"
    @pointermove="onPointerMove"
    @pointerup="onPointerUp"
    @pointerleave="onPointerLeave"
  >
    <!-- Three.js Canvas -->
    <canvas ref="canvasRef" class="w-full h-full block cursor-grab active:cursor-grabbing outline-none"></canvas>

    <!-- Interactive 3D Pill Hint (Luxury Badge) -->
    <div
      class="absolute bottom-3 sm:bottom-4 left-1/2 -translate-x-1/2 flex items-center space-x-2 px-3.5 py-1.5 rounded-full bg-white/90 dark:bg-[#1E120B]/90 backdrop-blur-md border border-rose-200/80 dark:border-[#58263A] text-[#DE1B54] dark:text-rose-300 text-xs font-bold shadow-md pointer-events-none transition-all duration-300 group-hover:scale-105"
    >
      <span class="inline-block animate-spin" style="animation-duration: 4s;">✨</span>
      <span>{{ isAmharic ? '3D በይነተገናኝ ኬክ • ለማሽከርከር ይጎትቱ' : '3D Interactive Cake • Drag to Rotate' }}</span>
    </div>

    <!-- Quick Spin & Reset Controls in Top Right -->
    <div class="absolute top-3 right-3 flex items-center space-x-1.5 opacity-80 hover:opacity-100 transition-opacity">
      <button
        type="button"
        @click="triggerSpinBoost"
        class="w-8 h-8 rounded-full bg-white/90 dark:bg-[#1E120B]/90 backdrop-blur-md border border-rose-200/80 dark:border-[#4A2518] text-gray-700 dark:text-gray-200 hover:text-[#DE1B54] hover:border-[#DE1B54] flex items-center justify-center text-xs shadow-xs transition-all cursor-pointer"
        :title="isAmharic ? 'ኬኩን አሽከርክር' : 'Spin Cake'"
      >
        <span>🔄</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import * as THREE from 'three';
import { useLocaleStore } from '../stores/locale';

const localeStore = useLocaleStore();
const isAmharic = computed(() => localeStore.isAmharic);

const containerRef = ref(null);
const canvasRef = ref(null);

let renderer = null;
let scene = null;
let camera = null;
let cakeGroup = null;
let floatingElements = [];
let animId = null;
let isIntersecting = true;
let resizeObserver = null;

// Interaction states
let isDragging = false;
let previousPointerX = 0;
let previousPointerY = 0;
let targetRotY = 0.35;
let targetRotX = 0.12;
let currentRotY = 0.35;
let currentRotX = 0.12;
let autoSpinSpeed = 0.0035;
let spinBoost = 0;

// Mouse parallax tracking
let mouseParallaxX = 0;
let mouseParallaxY = 0;

function init3D() {
  if (!canvasRef.value || !containerRef.value) return;

  const width = containerRef.value.clientWidth || 400;
  const height = containerRef.value.clientHeight || 400;

  // 1. Scene
  scene = new THREE.Scene();

  // 2. Camera
  camera = new THREE.PerspectiveCamera(38, width / height, 0.1, 100);
  camera.position.set(0, 1.1, 5.6);
  camera.lookAt(0, 0.25, 0);

  // 3. Renderer
  renderer = new THREE.WebGLRenderer({
    canvas: canvasRef.value,
    antialias: true,
    alpha: true,
    powerPreference: 'high-performance',
  });
  renderer.setSize(width, height);
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.18;

  // 4. Lights
  const ambientLight = new THREE.AmbientLight(0xfff8f2, 1.5);
  scene.add(ambientLight);

  const mainLight = new THREE.DirectionalLight(0xffffff, 2.0);
  mainLight.position.set(3.5, 5.5, 4.2);
  scene.add(mainLight);

  const rimLight = new THREE.DirectionalLight(0xffb8c6, 1.4);
  rimLight.position.set(-3.5, 3.2, -2.5);
  scene.add(rimLight);

  const bottomFillLight = new THREE.DirectionalLight(0xffeedd, 0.8);
  bottomFillLight.position.set(0, -3, 2);
  scene.add(bottomFillLight);

  const topperPointLight = new THREE.PointLight(0xffd166, 1.8, 6);
  topperPointLight.position.set(0, 2.1, 0.5);
  scene.add(topperPointLight);

  // 5. Build Masterpiece Cake
  buildCakeScene();

  // 6. Intersection Observer for Performance
  const obs = new IntersectionObserver(([entry]) => {
    isIntersecting = entry.isIntersecting;
  }, { threshold: 0.05 });
  obs.observe(containerRef.value);

  // 7. Resize Observer
  resizeObserver = new ResizeObserver(onResize);
  resizeObserver.observe(containerRef.value);

  // 8. Start Animation Loop
  animate();
}

function buildCakeScene() {
  cakeGroup = new THREE.Group();
  floatingElements = [];

  // --- LUXURY STAND & PEDESTAL ---
  const standMat = new THREE.MeshStandardMaterial({
    color: 0xfffcf7,
    roughness: 0.18,
    metalness: 0.08,
  });

  const goldMat = new THREE.MeshStandardMaterial({
    color: 0xf3c952,
    roughness: 0.22,
    metalness: 0.88,
  });

  const rubyMat = new THREE.MeshStandardMaterial({
    color: 0xde1b54,
    roughness: 0.18,
    metalness: 0.15,
  });

  const blushCreamMat = new THREE.MeshStandardMaterial({
    color: 0xfff6f3,
    roughness: 0.38,
    metalness: 0.03,
  });

  const pureWhiteFrostingMat = new THREE.MeshStandardMaterial({
    color: 0xffffff,
    roughness: 0.32,
    metalness: 0.02,
  });

  const chocolateMat = new THREE.MeshStandardMaterial({
    color: 0x3d251e,
    roughness: 0.25,
    metalness: 0.1,
  });

  const strawberrySkinMat = new THREE.MeshStandardMaterial({
    color: 0xd91642,
    roughness: 0.2,
    metalness: 0.1,
  });

  const greenLeafMat = new THREE.MeshStandardMaterial({
    color: 0x2e7d32,
    roughness: 0.4,
  });

  // Soft Ground Shadow
  const shadowGeo = new THREE.CylinderGeometry(2.3, 2.4, 0.01, 32);
  const shadowMat = new THREE.MeshBasicMaterial({
    color: 0x1f110a,
    transparent: true,
    opacity: 0.22,
  });
  const shadowMesh = new THREE.Mesh(shadowGeo, shadowMat);
  shadowMesh.position.y = -1.25;
  cakeGroup.add(shadowMesh);

  // Stand Base Plate
  const standBaseGeo = new THREE.CylinderGeometry(1.55, 1.7, 0.12, 48);
  const standBaseMesh = new THREE.Mesh(standBaseGeo, standMat);
  standBaseMesh.position.y = -1.18;
  cakeGroup.add(standBaseMesh);

  // Stand Gold Rim
  const standGoldRimGeo = new THREE.TorusGeometry(1.68, 0.03, 16, 48);
  const standGoldRim = new THREE.Mesh(standGoldRimGeo, goldMat);
  standGoldRim.rotation.x = Math.PI / 2;
  standGoldRim.position.y = -1.22;
  cakeGroup.add(standGoldRim);

  // Stand Fluted Column
  const standColGeo = new THREE.CylinderGeometry(0.38, 0.58, 0.65, 32);
  const standColMesh = new THREE.Mesh(standColGeo, standMat);
  standColMesh.position.y = -0.8;
  cakeGroup.add(standColMesh);

  // Stand Column Gold Ring Accent
  const standRingGeo = new THREE.TorusGeometry(0.44, 0.045, 16, 32);
  const standRing = new THREE.Mesh(standRingGeo, goldMat);
  standRing.rotation.x = Math.PI / 2;
  standRing.position.y = -0.8;
  cakeGroup.add(standRing);

  // Stand Top Platter
  const platterGeo = new THREE.CylinderGeometry(2.1, 2.15, 0.12, 48);
  const platterMesh = new THREE.Mesh(platterGeo, standMat);
  platterMesh.position.y = -0.42;
  cakeGroup.add(platterMesh);

  // Platter Gold Rim
  const platterRimGeo = new THREE.TorusGeometry(2.12, 0.035, 16, 48);
  const platterRim = new THREE.Mesh(platterRimGeo, goldMat);
  platterRim.rotation.x = Math.PI / 2;
  platterRim.position.y = -0.42;
  cakeGroup.add(platterRim);

  // --- TIER 1: BOTTOM TIER (Signature SweetBakes Cake) ---
  const tier1Height = 0.95;
  const tier1Radius = 1.62;
  const tier1Geo = new THREE.CylinderGeometry(tier1Radius, tier1Radius, tier1Height, 64);
  const tier1Mesh = new THREE.Mesh(tier1Geo, blushCreamMat);
  tier1Mesh.position.y = 0.12;
  cakeGroup.add(tier1Mesh);

  // Tier 1 Base Scalloped Pearls Border
  const pearlGeo = new THREE.SphereGeometry(0.065, 12, 12);
  const pearlMat = new THREE.MeshStandardMaterial({
    color: 0xffffff,
    roughness: 0.15,
    metalness: 0.12,
  });
  const pearlCount = 36;
  for (let i = 0; i < pearlCount; i++) {
    const angle = (i / pearlCount) * Math.PI * 2;
    const pearl = new THREE.Mesh(pearlGeo, pearlMat);
    pearl.position.set(
      Math.cos(angle) * (tier1Radius + 0.02),
      -0.34,
      Math.sin(angle) * (tier1Radius + 0.02)
    );
    cakeGroup.add(pearl);
  }

  // Tier 1 Middle Red Velvet Velvet Ribbon
  const ribbonGeo = new THREE.CylinderGeometry(tier1Radius + 0.015, tier1Radius + 0.015, 0.12, 64);
  const ribbonMesh = new THREE.Mesh(ribbonGeo, rubyMat);
  ribbonMesh.position.y = 0.05;
  cakeGroup.add(ribbonMesh);

  // Tier 1 Drip Frosting Glaze Crown
  const drip1Geo = new THREE.CylinderGeometry(tier1Radius + 0.025, tier1Radius + 0.025, 0.15, 64);
  const drip1Mesh = new THREE.Mesh(drip1Geo, pureWhiteFrostingMat);
  drip1Mesh.position.y = 0.55;
  cakeGroup.add(drip1Mesh);

  // Organic cascading drips down tier 1
  for (let i = 0; i < 14; i++) {
    const angle = (i / 14) * Math.PI * 2 + (i % 2 === 0 ? 0.08 : 0);
    const dropLen = 0.18 + (i % 4) * 0.08;
    const dropGeo = new THREE.CylinderGeometry(0.045, 0.02, dropLen, 12);
    const dropMesh = new THREE.Mesh(dropGeo, pureWhiteFrostingMat);
    dropMesh.position.set(
      Math.cos(angle) * (tier1Radius + 0.025),
      0.55 - dropLen / 2,
      Math.sin(angle) * (tier1Radius + 0.025)
    );
    cakeGroup.add(dropMesh);

    // Drip bulb droplet at end
    const bulbGeo = new THREE.SphereGeometry(0.042, 10, 10);
    const bulbMesh = new THREE.Mesh(bulbGeo, pureWhiteFrostingMat);
    bulbMesh.position.set(
      Math.cos(angle) * (tier1Radius + 0.025),
      0.55 - dropLen,
      Math.sin(angle) * (tier1Radius + 0.025)
    );
    cakeGroup.add(bulbMesh);
  }

  // --- TIER 2: TOP TIER ---
  const tier2Height = 0.85;
  const tier2Radius = 1.1;
  const tier2Geo = new THREE.CylinderGeometry(tier2Radius, tier2Radius, tier2Height, 64);
  const tier2Mesh = new THREE.Mesh(tier2Geo, pureWhiteFrostingMat);
  tier2Mesh.position.y = 1.02;
  cakeGroup.add(tier2Mesh);

  // Tier 2 Join Gold Border
  const tier2GoldRingGeo = new THREE.TorusGeometry(tier2Radius + 0.02, 0.035, 16, 48);
  const tier2GoldRing = new THREE.Mesh(tier2GoldRingGeo, goldMat);
  tier2GoldRing.rotation.x = Math.PI / 2;
  tier2GoldRing.position.y = 0.6;
  cakeGroup.add(tier2GoldRing);

  // Tier 2 Glaze Top Crown (Rich Strawberry Glaze)
  const glazeGeo = new THREE.CylinderGeometry(tier2Radius + 0.02, tier2Radius + 0.02, 0.14, 64);
  const glazeMesh = new THREE.Mesh(glazeGeo, rubyMat);
  glazeMesh.position.y = 1.41;
  cakeGroup.add(glazeMesh);

  // Glaze drips cascading down top tier
  for (let i = 0; i < 10; i++) {
    const angle = (i / 10) * Math.PI * 2 + 0.15;
    const dropLen = 0.16 + (i % 3) * 0.07;
    const dropGeo = new THREE.CylinderGeometry(0.04, 0.015, dropLen, 12);
    const dropMesh = new THREE.Mesh(dropGeo, rubyMat);
    dropMesh.position.set(
      Math.cos(angle) * (tier2Radius + 0.02),
      1.41 - dropLen / 2,
      Math.sin(angle) * (tier2Radius + 0.02)
    );
    cakeGroup.add(dropMesh);

    const bulbGeo = new THREE.SphereGeometry(0.038, 10, 10);
    const bulbMesh = new THREE.Mesh(bulbGeo, rubyMat);
    bulbMesh.position.set(
      Math.cos(angle) * (tier2Radius + 0.02),
      1.41 - dropLen,
      Math.sin(angle) * (tier2Radius + 0.02)
    );
    cakeGroup.add(bulbMesh);
  }

  // --- LUXURY TOPPINGS & CONFECTIONERY ART ---
  // 1. Rosettes of whipped cream on top
  const rosetteCount = 8;
  const rosetteGeo = new THREE.ConeGeometry(0.12, 0.18, 16);
  for (let i = 0; i < rosetteCount; i++) {
    const angle = (i / rosetteCount) * Math.PI * 2;
    const rosette = new THREE.Mesh(rosetteGeo, pureWhiteFrostingMat);
    rosette.position.set(
      Math.cos(angle) * 0.85,
      1.54,
      Math.sin(angle) * 0.85
    );
    rosette.rotation.y = angle;
    cakeGroup.add(rosette);
  }

  // 2. Realistic Strawberries atop Cake
  function createStrawberry(scale = 1.0) {
    const sGroup = new THREE.Group();
    const coneGeo = new THREE.ConeGeometry(0.14 * scale, 0.28 * scale, 16);
    const berryMesh = new THREE.Mesh(coneGeo, strawberrySkinMat);
    berryMesh.rotation.x = Math.PI;
    sGroup.add(berryMesh);

    // Green leafy crown
    const leafGeo = new THREE.ConeGeometry(0.08 * scale, 0.08 * scale, 5);
    const leafMesh = new THREE.Mesh(leafGeo, greenLeafMat);
    leafMesh.position.y = 0.14 * scale;
    sGroup.add(leafMesh);

    return sGroup;
  }

  const s1 = createStrawberry(1.2);
  s1.position.set(0.15, 1.62, 0.15);
  s1.rotation.set(0.2, 0.4, -0.15);
  cakeGroup.add(s1);

  const s2 = createStrawberry(0.95);
  s2.position.set(-0.35, 1.58, 0.25);
  s2.rotation.set(-0.25, 1.2, 0.3);
  cakeGroup.add(s2);

  const s3 = createStrawberry(1.0);
  s3.position.set(0.32, 1.58, -0.28);
  s3.rotation.set(0.3, -0.8, -0.2);
  cakeGroup.add(s3);

  // 3. French Macarons atop Tier & Ledge
  function createMacaron(colorHex, scale = 1.0) {
    const mGroup = new THREE.Group();
    const shellMat = new THREE.MeshStandardMaterial({
      color: colorHex,
      roughness: 0.35,
      metalness: 0.05,
    });
    const fillMat = new THREE.MeshStandardMaterial({
      color: 0xffffff,
      roughness: 0.25,
    });

    const shellGeo = new THREE.CylinderGeometry(0.2 * scale, 0.2 * scale, 0.07 * scale, 24);
    const topShell = new THREE.Mesh(shellGeo, shellMat);
    topShell.position.y = 0.05 * scale;
    mGroup.add(topShell);

    const botShell = new THREE.Mesh(shellGeo, shellMat);
    botShell.position.y = -0.05 * scale;
    mGroup.add(botShell);

    const creamFill = new THREE.CylinderGeometry(0.18 * scale, 0.18 * scale, 0.04 * scale, 24);
    const fillMesh = new THREE.Mesh(creamFill, fillMat);
    mGroup.add(fillMesh);

    return mGroup;
  }

  // Pink Macaron on Top
  const macaron1 = createMacaron(0xf472b6, 1.1);
  macaron1.position.set(-0.15, 1.6, -0.32);
  macaron1.rotation.set(0.4, 0.2, 0.8);
  cakeGroup.add(macaron1);

  // Pistachio Macaron on Tier 1 Ledge
  const macaron2 = createMacaron(0xa7f3d0, 1.0);
  macaron2.position.set(1.35, 0.68, 0.3);
  macaron2.rotation.set(0.2, -0.5, 0.4);
  cakeGroup.add(macaron2);

  // Vanilla Gold Macaron on Tier 1 Ledge
  const macaron3 = createMacaron(0xfef08a, 1.0);
  macaron3.position.set(-1.3, 0.68, -0.4);
  macaron3.rotation.set(-0.3, 0.7, -0.5);
  cakeGroup.add(macaron3);

  // 4. 24K Gold Flakes & Spheres
  const goldBallGeo = new THREE.SphereGeometry(0.065, 14, 14);
  for (let i = 0; i < 7; i++) {
    const gb = new THREE.Mesh(goldBallGeo, goldMat);
    const angle = (i / 7) * Math.PI * 2;
    gb.position.set(
      Math.cos(angle) * 0.55,
      1.52,
      Math.sin(angle) * 0.55
    );
    cakeGroup.add(gb);
  }

  // 5. Celebration Center Golden Sparkle / Heart Topper
  const topperStemGeo = new THREE.CylinderGeometry(0.02, 0.02, 0.6, 12);
  const topperStem = new THREE.Mesh(topperStemGeo, goldMat);
  topperStem.position.set(0, 1.82, 0);
  cakeGroup.add(topperStem);

  const heartShape = new THREE.Shape();
  heartShape.moveTo(0, 0.15);
  heartShape.bezierCurveTo(0, 0.3, -0.25, 0.45, -0.35, 0.2);
  heartShape.bezierCurveTo(-0.45, -0.05, 0, -0.3, 0, -0.4);
  heartShape.bezierCurveTo(0, -0.3, 0.45, -0.05, 0.35, 0.2);
  heartShape.bezierCurveTo(0.25, 0.45, 0, 0.3, 0, 0.15);

  const extrudeSettings = { depth: 0.06, bevelEnabled: true, bevelSegments: 3, steps: 1, bevelSize: 0.02, bevelThickness: 0.02 };
  const heartGeo = new THREE.ExtrudeGeometry(heartShape, extrudeSettings);
  const heartMesh = new THREE.Mesh(heartGeo, goldMat);
  heartMesh.position.set(0, 2.3, 0);
  heartMesh.scale.set(0.65, 0.65, 0.65);
  heartMesh.rotation.z = Math.PI;
  cakeGroup.add(heartMesh);

  // --- FLOATING CONFECTIONERY PARTICLES IN ORBIT ---
  // Create 6 floating luxury elements that bob in 3D around the cake
  const orbitItems = [
    { type: 'macaron', color: 0xf472b6, radius: 2.7, y: 0.8, speed: 0.7, offset: 0.5 },
    { type: 'berry', color: 0xd91642, radius: 2.6, y: -0.2, speed: 0.9, offset: 1.8 },
    { type: 'gold', color: 0xf3c952, radius: 2.8, y: 1.6, speed: 0.6, offset: 3.2 },
    { type: 'macaron', color: 0xa7f3d0, radius: 2.5, y: -0.6, speed: 0.8, offset: 4.5 },
    { type: 'gold', color: 0xf3c952, radius: 2.4, y: 0.3, speed: 1.1, offset: 2.4 },
    { type: 'berry', color: 0xd91642, radius: 2.9, y: 1.2, speed: 0.75, offset: 5.1 },
  ];

  orbitItems.forEach((item, idx) => {
    let mesh;
    if (item.type === 'macaron') {
      mesh = createMacaron(item.color, 0.75);
    } else if (item.type === 'berry') {
      mesh = createStrawberry(0.75);
    } else {
      const gSphere = new THREE.SphereGeometry(0.12, 16, 16);
      mesh = new THREE.Mesh(gSphere, goldMat);
    }

    mesh.position.set(
      Math.cos(item.offset) * item.radius,
      item.y,
      Math.sin(item.offset) * item.radius
    );
    scene.add(mesh);

    floatingElements.push({
      mesh,
      ...item,
      baseY: item.y,
      currentAngle: item.offset,
    });
  });

  scene.add(cakeGroup);
}

function onResize() {
  if (!containerRef.value || !renderer || !camera) return;
  const width = containerRef.value.clientWidth;
  const height = containerRef.value.clientHeight;
  if (width === 0 || height === 0) return;

  camera.aspect = width / height;
  camera.updateProjectionMatrix();
  renderer.setSize(width, height);
}

// Pointer / Mouse Parallax & Drag Orbit
function onPointerDown(e) {
  isDragging = true;
  previousPointerX = e.clientX;
  previousPointerY = e.clientY;
  spinBoost = 0;
}

function onPointerMove(e) {
  if (isDragging) {
    const deltaX = e.clientX - previousPointerX;
    const deltaY = e.clientY - previousPointerY;

    targetRotY += deltaX * 0.009;
    targetRotX += deltaY * 0.005;

    // Clamp vertical tilt
    targetRotX = Math.max(-0.25, Math.min(0.4, targetRotX));

    previousPointerX = e.clientX;
    previousPointerY = e.clientY;
  } else if (containerRef.value) {
    // Subtle Mouse Parallax
    const rect = containerRef.value.getBoundingClientRect();
    const nx = ((e.clientX - rect.left) / rect.width) * 2 - 1;
    const ny = -(((e.clientY - rect.top) / rect.height) * 2 - 1);

    mouseParallaxX = nx * 0.15;
    mouseParallaxY = ny * 0.1;
  }
}

function onPointerUp() {
  isDragging = false;
}

function onPointerLeave() {
  isDragging = false;
  mouseParallaxX = 0;
  mouseParallaxY = 0;
}

function triggerSpinBoost() {
  spinBoost = 0.06;
}

// Render Animation Loop
let clock = new THREE.Clock();

function animate() {
  animId = requestAnimationFrame(animate);

  if (!isIntersecting || !renderer || !scene || !camera || !cakeGroup) return;

  const elapsedTime = clock.getElapsedTime();

  // Natural continuous auto-spin
  if (!isDragging) {
    targetRotY += autoSpinSpeed + spinBoost;
    if (spinBoost > 0) {
      spinBoost *= 0.94;
      if (spinBoost < 0.0001) spinBoost = 0;
    }
  }

  // Smooth Interpolation (Lerp) for Rotation & Tilt
  currentRotY += (targetRotY + mouseParallaxX - currentRotY) * 0.08;
  currentRotX += (targetRotX + mouseParallaxY - currentRotX) * 0.08;

  cakeGroup.rotation.y = currentRotY;
  cakeGroup.rotation.x = currentRotX;

  // Gentle Floating Bobbing on Y axis
  cakeGroup.position.y = Math.sin(elapsedTime * 1.4) * 0.07;

  // Floating particles gentle orbit & bobbing
  floatingElements.forEach((elem) => {
    elem.currentAngle += autoSpinSpeed * elem.speed * 0.6;
    elem.mesh.position.x = Math.cos(elem.currentAngle) * elem.radius;
    elem.mesh.position.z = Math.sin(elem.currentAngle) * elem.radius;
    elem.mesh.position.y = elem.baseY + Math.sin(elapsedTime * elem.speed + elem.offset) * 0.12;
    elem.mesh.rotation.y += 0.01;
    elem.mesh.rotation.x += 0.005;
  });

  renderer.render(scene, camera);
}

function cleanup3D() {
  if (animId) cancelAnimationFrame(animId);
  if (resizeObserver) resizeObserver.disconnect();

  if (scene) {
    scene.traverse((obj) => {
      if (obj.isMesh) {
        if (obj.geometry) obj.geometry.dispose();
        if (obj.material) {
          if (Array.isArray(obj.material)) {
            obj.material.forEach((m) => m.dispose());
          } else {
            obj.material.dispose();
          }
        }
      }
    });
  }

  if (renderer) {
    renderer.dispose();
    renderer.forceContextLoss();
  }

  scene = null;
  camera = null;
  renderer = null;
  cakeGroup = null;
  floatingElements = [];
}

onMounted(() => {
  init3D();
});

onUnmounted(() => {
  cleanup3D();
});
</script>

