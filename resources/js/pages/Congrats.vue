<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const starsCanvas = ref<HTMLCanvasElement | null>(null)
const heroTitle = ref<HTMLElement | null>(null)

const stats = [
    { number: '4', label: 'Years of Study', suffix: '+' },
    { number: '100', label: 'Assignments Completed', suffix: '%' },
    { number: '2', label: 'Degree Earned', suffix: '✓' },
    { number: '∞', label: 'Lives to Impact', suffix: '' },
]

const showCongratulationsModal = ref(true)
let congratulationsTimeout: ReturnType<typeof setTimeout> | null = null

const timeline = [
    { year: '2022', title: 'Enrollment', color: 'orange' },
    { year: '2024', title: 'Fieldwork', color: 'blue' },
    { year: '2026', title: 'Graduation', color: 'green' },
]

let starsAnimFrame = 0
const cleanups: (() => void)[] = []

function initStars(canvas: HTMLCanvasElement): () => void {
    const ctx = canvas.getContext('2d')!
    let stars: { x: number; y: number; r: number; opacity: number; delta: number }[] = []
    let raf = 0

    function resize() {
        canvas.width = canvas.offsetWidth
        canvas.height = canvas.offsetHeight
        stars = Array.from({ length: 200 }, () => ({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            r: Math.random() * 1.4 + 0.3,
            opacity: Math.random() * 0.6 + 0.2,
            delta: (Math.random() - 0.5) * 0.015,
        }))
    }

    resize()
    window.addEventListener('resize', resize)

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height)
        for (const s of stars) {
            s.opacity += s.delta
            if (s.opacity > 1 || s.opacity < 0.1) s.delta *= -1
            ctx.beginPath()
            ctx.arc(s.x, s.y, s.r, 0, Math.PI * 2)
            ctx.fillStyle = `rgba(200, 220, 255, ${s.opacity * 0.5})`
            ctx.fill()
        }
        raf = requestAnimationFrame(draw)
        starsAnimFrame = raf
    }
    draw()

    return () => {
        cancelAnimationFrame(raf)
        window.removeEventListener('resize', resize)
    }
}

function splitChars(el: HTMLElement): HTMLSpanElement[] {
    const text = el.textContent ?? ''
    el.innerHTML = ''
    return [...text].map((ch) => {
        const s = document.createElement('span')
        s.textContent = ch === ' ' ? ' ' : ch
        s.style.display = 'inline-block'
        el.appendChild(s)
        return s
    })
}

onMounted(() => {
    if (starsCanvas.value) cleanups.push(initStars(starsCanvas.value))

    // ── NAV LINKS ──────────────────────────────────────────────────────
    gsap.fromTo('.nav-link',
        { opacity: 0, y: -10 },
        { opacity: 1, y: 0, duration: 0.6, stagger: 0.1, delay: 0.2, ease: 'power2.out' },
    )

    // ── HERO CONTENT ───────────────────────────────────────────────────
    if (heroTitle.value) {
        const chars = splitChars(heroTitle.value)
        gsap.set(chars, { opacity: 0, y: 50 })
        gsap.to(chars, {
            opacity: 1, y: 0, duration: 0.75, stagger: 0.03, ease: 'back.out(1.5)', delay: 0.4,
        })
    }

    gsap.fromTo('.hero-subtitle',
        { opacity: 0, y: 20 },
        { opacity: 1, y: 0, duration: 0.8, delay: 2, ease: 'power3.out' },
    )

    gsap.fromTo('.hero-desc',
        { opacity: 0, y: 20 },
        { opacity: 1, y: 0, duration: 0.8, delay: 2.3, ease: 'power3.out' },
    )

    gsap.fromTo('.hero-cta',
        { opacity: 0, scale: 0.9 },
        { opacity: 1, scale: 1, duration: 0.6, delay: 2.6, ease: 'back.out(1.4)' },
    )

    gsap.fromTo('.hero-code',
        { opacity: 0, y: 15 },
        { opacity: 1, y: 0, duration: 0.7, delay: 2.8, ease: 'power2.out' },
    )

    // ── HERO IMAGE QUADRANTS ───────────────────────────────────────────
    const quadrants = gsap.utils.toArray<HTMLElement>('.img-quadrant')
    const quadrantAnimations = [
        { x: -60, y: -60, delay: 1.8 },
        { x: 60, y: -60, delay: 2.0 },
        { x: -60, y: 60, delay: 2.2 },
        { x: 60, y: 60, delay: 2.4 },
    ]

    quadrants.forEach((quad, i) => {
        gsap.fromTo(quad,
            { opacity: 0, x: quadrantAnimations[i].x, y: quadrantAnimations[i].y, scale: 0.85, filter: 'blur(12px)' },
            {
                opacity: 1, x: 0, y: 0, scale: 1, filter: 'blur(0px)',
                duration: 0.85, ease: 'cubic.inOut', delay: quadrantAnimations[i].delay,
            },
        )
    })

    // ── STATS ──────────────────────────────────────────────────────────
    gsap.utils.toArray<HTMLElement>('.stat-item').forEach((stat, i) => {
        gsap.fromTo(stat,
            { opacity: 0, y: 30 },
            {
                opacity: 1, y: 0, duration: 0.7, ease: 'power3.out',
                scrollTrigger: { trigger: stat, start: 'top 85%' },
                delay: i * 0.1,
            },
        )
    })

    // ── TIMELINE ───────────────────────────────────────────────────────
    gsap.fromTo('.timeline-label',
        { opacity: 0, y: -20 },
        { opacity: 1, y: 0, duration: 0.8, delay: 0.3, ease: 'power3.out', scrollTrigger: { trigger: '.timeline-section', start: 'top 70%' } },
    )

    gsap.utils.toArray<HTMLElement>('.timeline-point').forEach((point, i) => {
        gsap.fromTo(point,
            { opacity: 0, scale: 0 },
            {
                opacity: 1, scale: 1, duration: 0.6, ease: 'back.out(1.3)',
                scrollTrigger: { trigger: point, start: 'top 80%' },
                delay: i * 0.15,
            },
        )
    })

    // ── BOTTOM SECTION ─────────────────────────────────────────────────
    gsap.fromTo('.closing-content',
        { opacity: 0, y: 40 },
        { opacity: 1, y: 0, duration: 0.9, ease: 'power3.out', scrollTrigger: { trigger: '.closing-section', start: 'top 75%' } },
    )

    // ── CONGRATULATIONS MODAL ───────────────────────────────────────────
    if (showCongratulationsModal.value) {
        gsap.fromTo('.congratulations-backdrop',
            { opacity: 0 },
            { opacity: 1, duration: 0.5, ease: 'power2.out' },
        )

        gsap.fromTo('.congratulations-modal',
            { opacity: 0, scale: 0.3, rotation: -180, xPercent: -50, yPercent: -50 },
            { opacity: 1, scale: 1, rotation: 0, duration: 0.8, ease: 'back.out(1.5)' },
        )

        const confettiElements = gsap.utils.toArray<HTMLElement>('.confetti')
        confettiElements.forEach((conf, i) => {
            const angle = (i / confettiElements.length) * Math.PI * 2
            const velocity = 300 + Math.random() * 200
            const vx = Math.cos(angle) * velocity
            const vy = Math.sin(angle) * velocity - 200

            gsap.to(conf, {
                x: vx, y: vy, opacity: 0, rotation: 360,
                duration: 2 + Math.random() * 1, ease: 'power1.out', delay: 0.3,
            })
        })

        congratulationsTimeout = setTimeout(() => {
            closeCongratulationsModal()
        }, 10000)
    }
})

function closeCongratulationsModal() {
    gsap.to('.congratulations-backdrop', {
        opacity: 0, duration: 0.4, ease: 'power2.in', onComplete: () => {
            showCongratulationsModal.value = false
        },
    })
    gsap.to('.congratulations-modal', {
        opacity: 0, scale: 0.8, duration: 0.4, ease: 'power2.in',
    })
    if (congratulationsTimeout) clearTimeout(congratulationsTimeout)
}

onUnmounted(() => {
    ScrollTrigger.getAll().forEach((t) => t.kill())
    cleanups.forEach((fn) => fn())
    cancelAnimationFrame(starsAnimFrame)
})
</script>

<template>
    <Head title="Agent Amira — BSSW Degree | Portfolio" />

    <div class="relative overflow-x-hidden bg-[#0a0815] text-white min-h-screen" style="font-family: 'Courier New', Courier, monospace;">

        <!-- ══════════════════════════════════════════════════════════
             CONGRATULATIONS MODAL
        ══════════════════════════════════════════════════════════ -->
        <div v-if="showCongratulationsModal"
             class="congratulations-backdrop fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">

            <!-- Modal Card -->
            <div class="congratulations-modal relative max-w-md w-full"
                 style="transform: translate(-50%, -50%); left: 50%; top: 50%;">

                <!-- Close Button -->
                <button @click="closeCongratulationsModal"
                        class="absolute -top-3 -right-3 z-20 w-10 h-10 rounded-full bg-green-500 hover:bg-green-600 flex items-center justify-center text-white font-bold transition-all duration-200"
                        style="box-shadow: 0 0 20px rgba(34,197,94,.5);">
                    ✕
                </button>

                <!-- Image -->
                <div class="rounded-2xl overflow-hidden border-2 border-green-400/40 shadow-2xl relative">
                    <img src="/images/5826936767401675501.jpg" alt="Congratulations"
                         class="w-full h-auto object-cover block">
                </div>

                <!-- Title -->
                <div class="text-center mt-6 mb-4">
                    <h2 class="text-3xl md:text-4xl font-black text-white mb-2"
                        style="text-shadow: 0 0 20px rgba(34,197,94,.4);">
                        Congratulations!
                    </h2>
                    <p class="text-sm md:text-base text-white/70 tracking-wide">
                        You've earned your degree. Your journey continues.
                    </p>
                </div>

                <!-- Confetti -->
                <div v-for="i in 40" :key="i"
                     class="confetti absolute rounded-full pointer-events-none"
                     :style="{
                         width: (Math.random() * 8 + 4) + 'px',
                         height: (Math.random() * 8 + 4) + 'px',
                         left: Math.random() * 100 + '%',
                         top: '-10px',
                         background: ['#22c55e', '#f59e0b', '#3b82f6', '#ec4899'][Math.floor(Math.random() * 4)],
                     }">
                </div>
            </div>
        </div>

        <!-- Canvas Background -->
        <canvas ref="starsCanvas" class="fixed inset-0 w-full h-full pointer-events-none"></canvas>

        <!-- Grid Overlay -->
        <div class="fixed inset-0 opacity-[0.02] pointer-events-none"
             style="background-image: linear-gradient(rgba(34,197,94,.4) 1px, transparent 1px), linear-gradient(90deg, rgba(34,197,94,.4) 1px, transparent 1px); background-size: 100px 100px;">
        </div>

        <div class="relative z-10">
            <!-- ══════════════════════════════════════════════════════════
                 NAVBAR
            ══════════════════════════════════════════════════════════ -->
            <nav class="sticky top-0 z-40 backdrop-blur-md bg-black/40 border-b border-green-400/10">
                <div class="max-w-7xl mx-auto px-4 md:px-8 h-16 flex items-center justify-between">
                    <div class="text-lg font-black text-white tracking-wider">Agent Amira</div>
                    <div class="flex items-center gap-8">
                        <a href="#skills" class="nav-link text-sm text-white/70 hover:text-green-400 transition-colors tracking-wide">Skills</a>
                        <a href="#journey" class="nav-link text-sm text-white/70 hover:text-green-400 transition-colors tracking-wide">Journey</a>
                        <a href="#impact" class="nav-link text-sm text-white/70 hover:text-green-400 transition-colors tracking-wide">Impact</a>
                    </div>
                </div>
            </nav>

            <!-- ══════════════════════════════════════════════════════════
                 HERO SECTION
            ══════════════════════════════════════════════════════════ -->
            <section class="relative min-h-screen flex items-center px-4 md:px-8 py-20">
                <div class="max-w-7xl mx-auto w-full grid md:grid-cols-2 gap-12 lg:gap-20 items-center">

                    <!-- Left: Content -->
                    <div class="order-2 md:order-1">
                        <div class="mb-4">
                            <span class="text-sm md:text-base text-green-400 font-bold tracking-widest">WELCOME</span>
                        </div>

                        <h1 ref="heroTitle"
                            class="text-4xl md:text-6xl lg:text-7xl font-black leading-tight mb-6"
                            style="text-shadow: 0 0 30px rgba(34,197,94,.3);">
                            <span class="text-white">Hey, I'm</span>
                            <br>
                            <span class="text-green-400">Amira Reyshi Cantado</span>
                        </h1>

                        <p class="hero-subtitle text-base md:text-lg text-white/70 mb-4 leading-relaxed">
                            Social Work Professional & Community Advocate
                        </p>

                        <p class="hero-desc text-sm md:text-base text-white/50 max-w-md leading-relaxed mb-8">
                            Dedicated to creating meaningful impact through social work. Armed with a degree in social work and a mission to help those in need.
                        </p>

                        <div class="hero-cta flex items-center gap-4 mb-8">
                            <button class="px-6 py-3 bg-green-500 hover:bg-green-600 text-black font-bold rounded-lg transition-all duration-300 text-sm md:text-base tracking-wide">
                                Start Mission
                            </button>
                            <button class="px-6 py-3 border-2 border-green-400/50 hover:border-green-400 text-white hover:text-green-400 font-bold rounded-lg transition-all duration-300 text-sm md:text-base tracking-wide">
                                Learn More
                            </button>
                        </div>

                        <div class="hero-code text-xs md:text-sm text-green-400/70 font-mono p-4 bg-black/40 rounded-lg border border-green-400/20 inline-block">
                            <div>// Agent Status: ACTIVE ✓</div>
                            <div class="text-white/50">// Clearance: MAXIMUM</div>
                        </div>
                    </div>

                    <!-- Right: Image -->
                    <div class="order-1 md:order-2">
                        <div class="relative rounded-2xl overflow-hidden"
                             style="aspect-ratio: 3/4;">
                            <img src="/images/Gemini_Generated_Image_lr9ynrlr9ynrlr9y.png" alt="Agent Amira"
                                 class="w-full h-full object-cover">

                            <!-- Quadrant Overlays -->
                            <div class="img-quadrant absolute top-0 left-0 w-1/2 h-1/2 bg-gradient-to-br from-green-400/15 to-transparent"
                                 style="border-right: 1px solid rgba(34,197,94,.15); border-bottom: 1px solid rgba(34,197,94,.15);">
                            </div>
                            <div class="img-quadrant absolute top-0 right-0 w-1/2 h-1/2 bg-gradient-to-bl from-green-400/15 to-transparent"
                                 style="border-left: 1px solid rgba(34,197,94,.15); border-bottom: 1px solid rgba(34,197,94,.15);">
                            </div>
                            <div class="img-quadrant absolute bottom-0 left-0 w-1/2 h-1/2 bg-gradient-to-tr from-green-400/15 to-transparent"
                                 style="border-right: 1px solid rgba(34,197,94,.15); border-top: 1px solid rgba(34,197,94,.15);">
                            </div>
                            <div class="img-quadrant absolute bottom-0 right-0 w-1/2 h-1/2 bg-gradient-to-tl from-green-400/15 to-transparent"
                                 style="border-left: 1px solid rgba(34,197,94,.15); border-top: 1px solid rgba(34,197,94,.15);">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════════════════════════
                 STATS SECTION
            ══════════════════════════════════════════════════════════ -->
            <section id="skills" class="relative py-20 md:py-28 px-4 md:px-8">
                <div class="max-w-7xl mx-auto">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                        <div v-for="(stat, i) in stats" :key="i"
                             class="stat-item p-6 md:p-8 bg-gradient-to-br from-green-400/10 to-transparent border border-green-400/20 rounded-xl hover:border-green-400/40 transition-colors">
                            <div class="text-3xl md:text-4xl lg:text-5xl font-black text-green-400 mb-2">
                                {{ stat.number }}<span class="text-2xl">{{ stat.suffix }}</span>
                            </div>
                            <div class="text-xs md:text-sm text-white/60 tracking-wide">{{ stat.label }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════════════════════════
                 TIMELINE SECTION
            ══════════════════════════════════════════════════════════ -->
            <section id="journey" class="timeline-section relative py-20 md:py-28 px-4 md:px-8">
                <div class="max-w-7xl mx-auto">
                    <h2 class="timeline-label text-2xl md:text-4xl font-black text-white mb-16 tracking-wider text-center">
                        My Journey
                    </h2>

                    <!-- Timeline Visual -->
                    <div class="relative flex flex-col md:flex-row items-center justify-center gap-8 md:gap-12">
                        <!-- Timeline Line (hidden on mobile) -->
                        <div class="hidden md:block absolute top-1/2 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-green-400/40 to-transparent -translate-y-1/2"></div>

                        <!-- Timeline Points -->
                        <div v-for="(point, i) in timeline" :key="i"
                             class="timeline-point relative z-10 flex flex-col items-center w-full md:w-auto">
                            <div class="w-12 h-12 rounded-full border-3 border-green-400 bg-black/60 flex items-center justify-center mb-4"
                                 style="box-shadow: 0 0 20px rgba(34,197,94,.4);">
                                <div class="w-5 h-5 rounded-full" :style="{ background: point.color === 'orange' ? '#f59e0b' : point.color === 'blue' ? '#3b82f6' : '#22c55e' }"></div>
                            </div>
                            <span class="text-sm md:text-base font-black text-green-400 mb-1">{{ point.year }}</span>
                            <span class="text-sm md:text-base text-white/70">{{ point.title }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ══════════════════════════════════════════════════════════
                 CLOSING SECTION
            ══════════════════════════════════════════════════════════ -->
            <section id="impact" class="closing-section relative py-20 md:py-28 px-4 md:px-8 border-t border-green-400/10">
                <div class="max-w-5xl mx-auto">
                    <div class="closing-content">
                        <h2 class="text-2xl md:text-4xl font-black text-white mb-6 tracking-wider">
                            What Drives Me
                        </h2>

                        <div class="grid md:grid-cols-2 gap-8 mb-12">
                            <div class="p-6 md:p-8 bg-gradient-to-br from-purple-400/10 to-transparent border border-purple-400/20 rounded-xl">
                                <div class="text-lg font-bold text-white mb-3">Compassion</div>
                                <p class="text-sm md:text-base text-white/60 leading-relaxed">
                                    Deep empathy for those struggling. Every person deserves dignity, respect, and support.
                                </p>
                            </div>

                            <div class="p-6 md:p-8 bg-gradient-to-br from-amber-400/10 to-transparent border border-amber-400/20 rounded-xl">
                                <div class="text-lg font-bold text-white mb-3">Action</div>
                                <p class="text-sm md:text-base text-white/60 leading-relaxed">
                                    Not just words. Dedication to real change through active engagement and continuous learning.
                                </p>
                            </div>
                        </div>

                        <div class="p-8 bg-gradient-to-r from-green-400/10 to-transparent border border-green-400/20 rounded-xl">
                            <p class="text-white/80 text-base md:text-lg leading-relaxed mb-4">
                                Your mission doesn't end with a degree. It begins here. Every person you help, every community you strengthen, every moment you choose compassion — that's your real legacy.
                            </p>
                            <p class="text-green-400 font-bold tracking-wider">
                                Ready to make an impact? Let's connect.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

        </div>

    </div>
</template>

<style scoped>
canvas {
    display: block;
}
</style>
