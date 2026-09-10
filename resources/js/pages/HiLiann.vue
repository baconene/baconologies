<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import gsap from 'gsap'

const letters = 'Hi, Liann! 💫'.split('')
const hearts = ref<{ x: number; y: number; scale: number; id: number }[]>([])
let heartId = 0

function spawnHeart(e: MouseEvent) {
    const h = { x: e.clientX, y: e.clientY, scale: 0.6 + Math.random() * 0.8, id: heartId++ }
    hearts.value.push(h)
    setTimeout(() => {
        hearts.value = hearts.value.filter(hh => hh.id !== h.id)
    }, 1200)
}

onMounted(() => {
    // ── star canvas ──
    const canvas = document.getElementById('bg-canvas') as HTMLCanvasElement
    const ctx = canvas.getContext('2d')!
    function resize() {
        canvas.width  = window.innerWidth
        canvas.height = window.innerHeight
    }
    resize()
    window.addEventListener('resize', resize)

    const STARS = Array.from({ length: 130 }, () => ({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        r: Math.random() * 1.8 + 0.3,
        speed: Math.random() * 0.005 + 0.002,
        phase: Math.random() * Math.PI * 2,
    }))

    let frame = 0
    ;(function drawStars() {
        ctx.clearRect(0, 0, canvas.width, canvas.height)
        frame++
        STARS.forEach(s => {
            const o = 0.25 + 0.75 * Math.abs(Math.sin(frame * s.speed + s.phase))
            ctx.beginPath()
            ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2)
            ctx.fillStyle = `rgba(210,185,255,${o})`
            ctx.fill()
        })
        requestAnimationFrame(drawStars)
    })()

    // ── letter entrance ──
    // letters start invisible — set opacity on each span
    gsap.set('.hl-letter', { opacity: 0, y: 50, rotation: -12 })
    gsap.to('.hl-letter', {
        opacity: 1, y: 0, rotation: 0,
        duration: 0.55,
        ease: 'back.out(2.2)',
        stagger: 0.07,
        delay: 0.4,
    })

    // ── subtitle / hint ──
    gsap.from('.hl-sub',  { opacity: 0, y: 24, duration: 0.7, delay: 1.5, ease: 'power3.out' })
    gsap.from('.hl-hint', { opacity: 0, duration: 0.6, delay: 2.0 })

    // ── floating emojis ──
    gsap.from('.hl-emoji', {
        opacity: 0, scale: 0, duration: 0.5,
        stagger: 0.12, delay: 1.9, ease: 'back.out(2)',
    })
    gsap.to('.hl-emoji', {
        y: -20, duration: 2.2, ease: 'sine.inOut',
        yoyo: true, repeat: -1,
        stagger: { each: 0.35, from: 'random' },
    })

    // ── pulse rings (siblings of card, no overflow clip) ──
    gsap.set('.hl-ring', { scale: 1, opacity: 0.6, transformOrigin: 'center center' })
    gsap.to('.hl-ring', {
        scale: 1.6, opacity: 0, duration: 2.2, ease: 'power2.out',
        repeat: -1, stagger: { each: 0.8, repeat: -1 },
    })

    // ── card shimmer ──
    gsap.fromTo('.hl-shimmer',
        { xPercent: -120 },
        { xPercent: 220, duration: 2.6, ease: 'power1.inOut', repeat: -1, repeatDelay: 2.5 }
    )
})
</script>

<template>
    <Head title="Hi Liann! 💫" />

    <div class="hl-page" @click="spawnHeart">

        <!-- star canvas -->
        <canvas id="bg-canvas" class="hl-canvas"></canvas>

        <!-- floating hearts on click -->
        <div
            v-for="h in hearts" :key="h.id"
            class="hl-click-heart"
            :style="{ left: h.x + 'px', top: h.y + 'px', fontSize: (h.scale * 26) + 'px' }"
        >💜</div>

        <!-- ambient emojis -->
        <div class="hl-bg-emojis" aria-hidden="true">
            <span class="hl-emoji" style="top:7%;left:5%">✨</span>
            <span class="hl-emoji" style="top:14%;right:9%">🌸</span>
            <span class="hl-emoji" style="top:68%;left:7%">🦋</span>
            <span class="hl-emoji" style="top:74%;right:6%">⭐</span>
            <span class="hl-emoji" style="top:44%;left:3%">💫</span>
            <span class="hl-emoji" style="top:38%;right:4%">🌙</span>
            <span class="hl-emoji" style="top:87%;left:42%">🌷</span>
            <span class="hl-emoji" style="top:4%;left:52%">💜</span>
        </div>

        <!-- pulse rings — OUTSIDE the card so overflow:hidden doesn't clip them -->
        <div class="hl-rings" aria-hidden="true">
            <div class="hl-ring"></div>
            <div class="hl-ring"></div>
            <div class="hl-ring"></div>
        </div>

        <!-- card -->
        <div class="hl-card">
            <!-- shimmer bar inside its own overflow-hidden wrapper -->
            <div class="hl-shimmer-wrap" aria-hidden="true">
                <div class="hl-shimmer"></div>
            </div>

            <!-- title: gradient applied per-letter so GSAP opacity works correctly -->
            <h1 class="hl-title" aria-label="Hi, Liann! 💫">
                <span
                    v-for="(ch, i) in letters"
                    :key="i"
                    class="hl-letter"
                    :style="ch === ' ' ? { display: 'inline-block', width: '0.3em' } : {}"
                >{{ ch }}</span>
            </h1>

            <p class="hl-sub">You just got a little spark of sunshine 🌟</p>
            <p class="hl-hint">( click anywhere ✨ )</p>
        </div>
    </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ── PAGE ── */
.hl-page {
    min-height: 100vh;
    background: radial-gradient(ellipse at 40% 30%, #1c0b40 0%, #0b0620 55%, #040110 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    cursor: default;
    font-family: 'Segoe UI', system-ui, sans-serif;
    user-select: none;
}

/* ── CANVAS ── */
.hl-canvas {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}

/* ── AMBIENT EMOJIS ── */
.hl-bg-emojis {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 1;
}
.hl-emoji {
    position: absolute;
    font-size: 1.5rem;
    filter: drop-shadow(0 0 10px rgba(210,160,255,0.55));
}

/* ── CLICK HEARTS ── */
.hl-click-heart {
    position: fixed;
    transform: translate(-50%, -50%);
    pointer-events: none;
    z-index: 100;
    animation: hl-rise 1.2s ease-out forwards;
    filter: drop-shadow(0 0 8px rgba(180,90,255,0.9));
}
@keyframes hl-rise {
    0%   { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    100% { opacity: 0; transform: translate(-50%, -160%) scale(1.7); }
}

/* ── PULSE RINGS ── */
.hl-rings {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    z-index: 2;
}
.hl-ring {
    position: absolute;
    width: 360px;
    height: 200px;
    border-radius: 50%;
    border: 1.5px solid rgba(190,130,255,0.45);
}

/* ── CARD ── */
.hl-card {
    position: relative;
    z-index: 10;
    text-align: center;
    padding: 60px 72px 52px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(200,150,255,0.22);
    border-radius: 32px;
    backdrop-filter: blur(28px);
    box-shadow:
        0 0 80px rgba(150,80,255,0.14),
        0 0 20px rgba(150,80,255,0.06),
        inset 0 1px 0 rgba(255,255,255,0.07);
    max-width: 90vw;
    overflow: hidden; /* for shimmer only */
}

/* ── SHIMMER ── */
.hl-shimmer-wrap {
    position: absolute;
    inset: 0;
    overflow: hidden;
    border-radius: 32px;
    pointer-events: none;
    z-index: 0;
}
.hl-shimmer {
    position: absolute;
    top: 0; bottom: 0;
    width: 45%;
    background: linear-gradient(
        105deg,
        transparent 25%,
        rgba(255,255,255,0.07) 50%,
        transparent 75%
    );
    transform: skewX(-12deg);
}

/* ── TITLE ── */
.hl-title {
    position: relative;
    z-index: 1;
    font-size: clamp(2.8rem, 8vw, 5.2rem);
    font-weight: 900;
    letter-spacing: -0.5px;
    line-height: 1.1;
    margin-bottom: 22px;
}

/* gradient applied per-letter — GSAP can safely animate opacity/transform */
.hl-letter {
    display: inline-block;
    will-change: transform, opacity;
    background: linear-gradient(135deg, #eddeff 0%, #c084fc 45%, #a855f7 75%, #7c3aed 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hl-sub {
    position: relative;
    z-index: 1;
    font-size: clamp(1rem, 2.4vw, 1.15rem);
    color: rgba(220,185,255,0.78);
    margin-bottom: 10px;
    letter-spacing: 0.2px;
}
.hl-hint {
    position: relative;
    z-index: 1;
    font-size: 0.8rem;
    color: rgba(180,140,255,0.38);
    letter-spacing: 0.5px;
}

@media (max-width: 480px) {
    .hl-card { padding: 44px 28px 36px; }
    .hl-ring  { width: 260px; height: 160px; }
}
</style>
