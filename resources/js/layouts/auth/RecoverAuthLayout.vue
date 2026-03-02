<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { home } from '@/routes'

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>

    <Head :title="title" :description="description" />

    <div class="auth-page">

        <!-- Animated SVG backdrop (same as AuthUserLayout) -->
        <svg class="auth-backdrop" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
            <defs>
                <radialGradient id="bgGradR" cx="50%" cy="50%" r="80%">
                    <stop offset="0%" stop-color="#0e0e2e" />
                    <stop offset="100%" stop-color="#050510" />
                </radialGradient>
                <radialGradient id="orb1R" cx="50%" cy="50%" r="50%">
                    <stop offset="0%" stop-color="#7c3aed" stop-opacity="0.45" />
                    <stop offset="100%" stop-color="#7c3aed" stop-opacity="0" />
                </radialGradient>
                <radialGradient id="orb2R" cx="50%" cy="50%" r="50%">
                    <stop offset="0%" stop-color="#2563eb" stop-opacity="0.35" />
                    <stop offset="100%" stop-color="#2563eb" stop-opacity="0" />
                </radialGradient>
                <radialGradient id="orb3R" cx="50%" cy="50%" r="50%">
                    <stop offset="0%" stop-color="#db2777" stop-opacity="0.28" />
                    <stop offset="100%" stop-color="#db2777" stop-opacity="0" />
                </radialGradient>
                <radialGradient id="orb4R" cx="50%" cy="50%" r="50%">
                    <stop offset="0%" stop-color="#0891b2" stop-opacity="0.30" />
                    <stop offset="100%" stop-color="#0891b2" stop-opacity="0" />
                </radialGradient>
                <pattern id="dotsR" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                    <circle cx="1.5" cy="1.5" r="1.5" fill="#ffffff" fill-opacity="0.06" />
                </pattern>
                <pattern id="gridR" x="0" y="0" width="80" height="80" patternUnits="userSpaceOnUse">
                    <path d="M 80 0 L 0 0 0 80" fill="none" stroke="#ffffff" stroke-width="0.4" stroke-opacity="0.05" />
                </pattern>
                <filter id="blur-orbR">
                    <feGaussianBlur stdDeviation="60" />
                </filter>
            </defs>

            <rect width="100%" height="100%" fill="url(#bgGradR)" />
            <rect width="100%" height="100%" fill="url(#dotsR)" />
            <rect width="100%" height="100%" fill="url(#gridR)" />

            <ellipse class="orb orb-1" cx="15%" cy="20%" rx="380" ry="380" fill="url(#orb1R)" filter="url(#blur-orbR)" />
            <ellipse class="orb orb-2" cx="85%" cy="75%" rx="420" ry="420" fill="url(#orb2R)" filter="url(#blur-orbR)" />
            <ellipse class="orb orb-3" cx="70%" cy="12%" rx="300" ry="300" fill="url(#orb3R)" filter="url(#blur-orbR)" />
            <ellipse class="orb orb-4" cx="25%" cy="88%" rx="340" ry="340" fill="url(#orb4R)" filter="url(#blur-orbR)" />

            <g class="lines" stroke="#a78bfa" stroke-opacity="0.12" stroke-width="1" fill="none">
                <line x1="0" y1="30%" x2="100%" y2="15%" />
                <line x1="0" y1="60%" x2="100%" y2="45%" />
                <line x1="0" y1="90%" x2="100%" y2="75%" />
                <line x1="20%" y1="0" x2="5%" y2="100%" />
                <line x1="70%" y1="0" x2="55%" y2="100%" />
            </g>

            <g fill="none" stroke="#7c3aed" stroke-opacity="0.15" stroke-width="1">
                <polygon class="hex hex-1" points="60,0 120,34 120,104 60,138 0,104 0,34" transform="translate(80,60)" />
                <polygon class="hex hex-2" points="40,0 80,23 80,69 40,92 0,69 0,23" transform="translate(calc(100vw - 160px), calc(100vh - 200px))" />
                <polygon class="hex hex-3" points="30,0 60,17 60,51 30,68 0,51 0,17" transform="translate(calc(100vw - 100px), 80px)" />
            </g>

            <circle class="float-circle fc-1" cx="88%" cy="18%" r="6" fill="#a78bfa" fill-opacity="0.35" />
            <circle class="float-circle fc-2" cx="10%" cy="55%" r="4" fill="#60a5fa" fill-opacity="0.40" />
            <circle class="float-circle fc-3" cx="92%" cy="55%" r="9" fill="none" stroke="#a78bfa" stroke-opacity="0.25" stroke-width="1.5" />
            <circle class="float-circle fc-4" cx="50%" cy="95%" r="5" fill="#f472b6" fill-opacity="0.30" />
            <circle class="float-circle fc-5" cx="5%"  cy="10%" r="3" fill="#34d399" fill-opacity="0.35" />
            <circle class="float-circle fc-6" cx="75%" cy="90%" r="7" fill="none" stroke="#38bdf8" stroke-opacity="0.30" stroke-width="1" />

            <rect class="diamond d-1" x="0" y="0" width="18" height="18" fill="none" stroke="#818cf8" stroke-opacity="0.25" stroke-width="1.5" transform="translate(calc(100vw * 0.38), calc(100vh * 0.06)) rotate(45 9 9)" />
            <rect class="diamond d-2" x="0" y="0" width="12" height="12" fill="none" stroke="#f472b6" stroke-opacity="0.20" stroke-width="1.2" transform="translate(calc(100vw * 0.12), calc(100vh * 0.72)) rotate(45 6 6)" />
            <rect class="diamond d-3" x="0" y="0" width="22" height="22" fill="none" stroke="#34d399" stroke-opacity="0.18" stroke-width="1" transform="translate(calc(100vw * 0.78), calc(100vh * 0.35)) rotate(45 11 11)" />

            <path d="M 0,180 Q 90,90 180,0" stroke="#7c3aed" stroke-opacity="0.12" stroke-width="1" fill="none" />
            <path d="M 0,260 Q 130,130 260,0" stroke="#7c3aed" stroke-opacity="0.07" stroke-width="1" fill="none" />
        </svg>

        <!-- Form card -->
        <div class="app-container">
            <img src="/images/logo.png" alt="Logo" class="logo-icon" @click="router.visit(home())">

            <h1 class="recover-title">{{ title }}</h1>

            <p class="instructions" id="instructionsText">{{ description }}</p>

            <div class="form-container">
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
/* ---- Page wrapper ---- */
.auth-page {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background-color: #050510;
}

/* ---- SVG backdrop ---- */
.auth-backdrop {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
}

.orb-1 { animation: orbDrift1 18s ease-in-out infinite alternate; }
.orb-2 { animation: orbDrift2 22s ease-in-out infinite alternate; }
.orb-3 { animation: orbDrift3 15s ease-in-out infinite alternate; }
.orb-4 { animation: orbDrift4 25s ease-in-out infinite alternate; }

@keyframes orbDrift1 {
    from { transform: translate(0, 0) scale(1);   opacity: 0.8; }
    to   { transform: translate(60px, 40px) scale(1.15); opacity: 1; }
}
@keyframes orbDrift2 {
    from { transform: translate(0, 0) scale(1);   opacity: 0.7; }
    to   { transform: translate(-50px, -60px) scale(1.2); opacity: 1; }
}
@keyframes orbDrift3 {
    from { transform: translate(0, 0) scale(1);   opacity: 0.6; }
    to   { transform: translate(-40px, 55px) scale(1.1); opacity: 0.9; }
}
@keyframes orbDrift4 {
    from { transform: translate(0, 0) scale(1);   opacity: 0.65; }
    to   { transform: translate(45px, -50px) scale(1.18); opacity: 1; }
}

.fc-1 { animation: floatY 8s  ease-in-out infinite alternate; }
.fc-2 { animation: floatY 11s ease-in-out infinite alternate-reverse; }
.fc-3 { animation: floatY 9s  ease-in-out 1s infinite alternate; }
.fc-4 { animation: floatY 13s ease-in-out 2s infinite alternate-reverse; }
.fc-5 { animation: floatY 7s  ease-in-out 0.5s infinite alternate; }
.fc-6 { animation: floatY 10s ease-in-out 1.5s infinite alternate-reverse; }

.hex-1 { animation: floatY 14s ease-in-out infinite alternate; }
.hex-2 { animation: floatY 18s ease-in-out 2s infinite alternate-reverse; }
.hex-3 { animation: floatY 12s ease-in-out 1s infinite alternate; }

.d-1 { animation: spinSlow 30s linear infinite; transform-origin: calc(100vw * 0.38 + 9px) calc(100vh * 0.06 + 9px); }
.d-2 { animation: spinSlow 40s linear infinite reverse; transform-origin: calc(100vw * 0.12 + 6px) calc(100vh * 0.72 + 6px); }
.d-3 { animation: spinSlow 50s linear infinite; transform-origin: calc(100vw * 0.78 + 11px) calc(100vh * 0.35 + 11px); }

@keyframes floatY {
    from { transform: translateY(0px); }
    to   { transform: translateY(-18px); }
}
@keyframes spinSlow {
    from { transform: rotate(45deg); }
    to   { transform: rotate(405deg); }
}

/* ---- Form card ---- */
.app-container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 450px;
    margin: 0 auto;
    padding: 2.5rem 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    background: rgba(10, 10, 35, 0.72);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(124, 58, 237, 0.18);
    border-radius: 20px;
    box-shadow:
        0 0 60px rgba(124, 58, 237, 0.08),
        0 24px 64px rgba(0, 0, 0, 0.6);
}

.logo-icon {
    width: 48px;
    height: 48px;
    margin-bottom: 2rem;
    cursor: pointer;
    transition: transform 0.2s;
}
.logo-icon:hover {
    transform: scale(1.08);
}

.recover-title {
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 1rem;
    letter-spacing: -0.5px;
    color: #fff;
}

.instructions {
    color: #ccc;
    margin-bottom: 3.5rem;
    font-size: 1rem;
    max-width: 350px;
    min-height: 40px;
}
</style>