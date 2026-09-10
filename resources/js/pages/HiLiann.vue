<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import gsap from 'gsap'

const letters = 'Hi, Liann! 💫'.split('')
const letterRefs = ref<HTMLElement[]>([])
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
    // starburst background particles
    const canvas = document.getElementById('bg-canvas') as HTMLCanvasElement
    const ctx = canvas.getContext('2d')!
    canvas.width  = window.innerWidth
    canvas.height = window.innerHeight

    const STARS = Array.from({ length: 120 }, () => ({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        r: Math.random() * 1.8 + 0.3,
        o: Math.random(),
        speed: Math.random() * 0.004 + 0.002,
        phase: Math.random() * Math.PI * 2,
    }))

    let frame = 0
    function drawStars() {
        ctx.clearRect(0, 0, canvas.width, canvas.height)
        frame++
        STARS.forEach(s => {
            s.o = 0.3 + 0.7 * Math.abs(Math.sin(frame * s.speed + s.phase))
            ctx.beginPath()
            ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2)
            ctx.fillStyle = `rgba(200,180,255,${s.o})`
            ctx.fill()
        })
        requestAnimationFrame(drawStars)
    }
    drawStars()

    window.addEventListener('resize', () => {
        canvas.width  = window.innerWidth
        canvas.height = window.innerHeight
    })

    // ── letter entrance ──
    gsap.set('.hl-letter', { opacity: 0, y: 60, rotateZ: -15 })
    gsap.to('.hl-letter', {
        opacity: 1, y: 0, rotateZ: 0,
        duration: 0.6,
        ease: 'back.out(2)',
        stagger: 0.07,
        delay: 0.3,
    })

    // ── subtitle ──
    gsap.from('.hl-sub', { opacity: 0, y: 30, duration: 0.8, delay: 1.4, ease: 'power3.out' })

    // ── floating emoji ──
    gsap.from('.hl-emoji', {
        opacity: 0, scale: 0, duration: 0.5,
        stagger: 0.15, delay: 1.8, ease: 'back.out(2)',
    })

    // ── continuous float loop ──
    gsap.to('.hl-emoji', {
        y: -18,
        duration: 2,
        ease: 'sine.inOut',
        yoyo: true,
        repeat: -1,
        stagger: { each: 0.3, from: 'random' },
    })

    // ── pulse ring ──
    gsap.to('.hl-ring', {
        scale: 1.18,
        opacity: 0,
        duration: 1.8,
        ease: 'power2.out',
        repeat: -1,
        stagger: 0.6,
    })

    // ── card shimmer ──
    gsap.fromTo('.hl-shimmer',
        { x: '-100%' },
        { x: '200%', duration: 2.4, ease: 'power1.inOut', repeat: -1, repeatDelay: 2 }
    )
})
</script>

<template>
    <Head title="Hi Liann! 💫" />

    <div class="hl-page" @click="spawnHeart">

        <canvas id="bg-canvas" class="hl-canvas"></canvas>

        <!-- floating hearts on click -->
        <div
            v-for="h in hearts" :key="h.id"
            class="hl-click-heart"
            :style="{ left: h.x + 'px', top: h.y + 'px', fontSize: (h.scale * 24) + 'px' }"
        >💜</div>

        <!-- floating bg emojis -->
        <div class="hl-bg-emojis">
            <span class="hl-emoji" style="top:8%;left:6%;font-size:2rem">✨</span>
            <span class="hl-emoji" style="top:15%;right:10%;font-size:1.6rem">🌸</span>
            <span class="hl-emoji" style="top:70%;left:8%;font-size:1.4rem">🦋</span>
            <span class="hl-emoji" style="top:75%;right:7%;font-size:1.8rem">⭐</span>
            <span class="hl-emoji" style="top:45%;left:3%;font-size:1.2rem">💫</span>
            <span class="hl-emoji" style="top:40%;right:4%;font-size:1.5rem">🌙</span>
            <span class="hl-emoji" style="top:88%;left:40%;font-size:1.3rem">🌷</span>
            <span class="hl-emoji" style="top:5%;left:50%;font-size:1.4rem">💜</span>
        </div>

        <!-- card -->
        <div class="hl-card">
            <!-- pulse rings -->
            <div class="hl-ring"></div>
            <div class="hl-ring" style="animation-delay:0.6s"></div>

            <!-- shimmer bar -->
            <div class="hl-shimmer-wrap">
                <div class="hl-shimmer"></div>
            </div>

            <!-- letters -->
            <h1 class="hl-title" aria-label="Hi, Liann!">
                <span
                    v-for="(ch, i) in letters"
                    :key="i"
                    class="hl-letter"
                    :ref="el => { if (el) letterRefs[i] = el as HTMLElement }"
                    :style="ch === ' ' ? { display: 'inline-block', width: '0.35em' } : {}"
                >{{ ch }}</span>
            </h1>

            <p class="hl-sub">You just got a little spark of sunshine 🌟</p>
            <p class="hl-hint">( click anywhere ✨ )</p>
        </div>
    </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.hl-page {
    min-height: 100vh;
    background: radial-gradient(ellipse at 40% 30%, #1a0a3a 0%, #0a0518 60%, #04020e 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    cursor: default;
    font-family: 'Segoe UI', system-ui, sans-serif;
    user-select: none;
}

.hl-canvas {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}

.hl-bg-emojis {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 1;
}
.hl-emoji {
    position: absolute;
    filter: drop-shadow(0 0 8px rgba(200,160,255,0.6));
}

/* click hearts */
.hl-click-heart {
    position: fixed;
    transform: translate(-50%, -50%);
    pointer-events: none;
    z-index: 100;
    animation: hl-heart-up 1.2s ease-out forwards;
    filter: drop-shadow(0 0 6px rgba(180,100,255,0.8));
}
@keyframes hl-heart-up {
    0%   { opacity: 1; transform: translate(-50%, -50%) scale(1); }
    100% { opacity: 0; transform: translate(-50%, -140%) scale(1.6); }
}

/* card */
.hl-card {
    position: relative;
    z-index: 10;
    text-align: center;
    padding: 56px 64px 48px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(200,160,255,0.2);
    border-radius: 32px;
    backdrop-filter: blur(24px);
    box-shadow:
        0 0 60px rgba(160,100,255,0.12),
        inset 0 1px 0 rgba(255,255,255,0.06);
    overflow: hidden;
    max-width: 90vw;
}

/* pulse rings */
.hl-ring {
    position: absolute;
    inset: -20px;
    border-radius: 50%;
    border: 2px solid rgba(180,120,255,0.35);
    transform-origin: center;
    pointer-events: none;
}

/* shimmer */
.hl-shimmer-wrap {
    position: absolute;
    inset: 0;
    overflow: hidden;
    border-radius: 32px;
    pointer-events: none;
}
.hl-shimmer {
    position: absolute;
    top: 0; bottom: 0;
    width: 40%;
    background: linear-gradient(105deg, transparent 30%, rgba(255,255,255,0.06) 50%, transparent 70%);
    transform: skewX(-15deg);
}

/* title */
.hl-title {
    font-size: clamp(2.6rem, 8vw, 5rem);
    font-weight: 900;
    letter-spacing: -1px;
    line-height: 1.1;
    margin-bottom: 20px;
    background: linear-gradient(135deg, #e8d5ff 0%, #c084fc 40%, #a855f7 70%, #7c3aed 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    filter: drop-shadow(0 0 20px rgba(168,85,247,0.5));
}
.hl-letter {
    display: inline-block;
    will-change: transform;
}

.hl-sub {
    font-size: clamp(1rem, 2.5vw, 1.2rem);
    color: rgba(220,190,255,0.75);
    margin-bottom: 10px;
    letter-spacing: 0.2px;
}
.hl-hint {
    font-size: 0.78rem;
    color: rgba(180,140,255,0.4);
    letter-spacing: 0.5px;
}
</style>
