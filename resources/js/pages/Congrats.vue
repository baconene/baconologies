<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const starsCanvas = ref<HTMLCanvasElement | null>(null)
const heroTitle = ref<HTMLElement | null>(null)
const heroRef = ref<HTMLElement | null>(null)

const skills = [
    { icon: '❤️', label: 'Compassion', desc: 'Deep empathy for those in need' },
    { icon: '🛡️', label: 'Resilience', desc: 'Unwavering strength through challenges' },
    { icon: '🧠', label: 'Insight', desc: 'Understanding complex human situations' },
    { icon: '🤝', label: 'Advocacy', desc: 'Championing for the vulnerable' },
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
        stars = Array.from({ length: 300 }, () => ({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            r: Math.random() * 1.5 + 0.3,
            opacity: Math.random() * 0.7 + 0.2,
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
            ctx.fillStyle = `rgba(200, 220, 255, ${s.opacity * 0.6})`
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

    // ── HERO TITLE ─────────────────────────────────────────────────────
    if (heroTitle.value) {
        const chars = splitChars(heroTitle.value)
        gsap.set(chars, { opacity: 0, y: 60, rotateX: -80 })
        gsap.to(chars, {
            opacity: 1, y: 0, rotateX: 0,
            duration: 0.85, stagger: 0.04, ease: 'back.out(1.7)', delay: 0.3,
        })
    }

    // ── HERO IMAGE ENTER ───────────────────────────────────────────────
    gsap.fromTo('.hero-img',
        { opacity: 0, scale: 0.85, filter: 'blur(20px)' },
        { opacity: 1, scale: 1, filter: 'blur(0px)', duration: 1.2, delay: 1.8, ease: 'power3.out' },
    )

    // ── SUBTITLE FADE ──────────────────────────────────────────────────
    gsap.fromTo('.hero-subtitle',
        { opacity: 0, y: 25 },
        { opacity: 1, y: 0, duration: 0.9, delay: 2.4, ease: 'power2.out' },
    )

    // ── FAMILY IMAGE REVEAL ────────────────────────────────────────────
    gsap.fromTo('.family-img',
        { opacity: 0, y: 40, scale: 0.9 },
        {
            opacity: 1, y: 0, scale: 1, duration: 1, ease: 'power3.out',
            scrollTrigger: { trigger: '.family-img', start: 'top 75%' },
        },
    )

    // ── SKILLS GRID ────────────────────────────────────────────────────
    gsap.utils.toArray<HTMLElement>('.skill-card').forEach((card, i) => {
        gsap.fromTo(card,
            { opacity: 0, y: 35, scale: 0.9 },
            {
                opacity: 1, y: 0, scale: 1, duration: 0.7, ease: 'power3.out',
                scrollTrigger: { trigger: card, start: 'top 82%' },
                delay: i * 0.08,
            },
        )
    })

    // ── STAT COUNTERS ─────────────────────────────────────────────────
    const statBars = gsap.utils.toArray<HTMLElement>('.stat-bar')
    statBars.forEach((bar, i) => {
        const target = 100
        ScrollTrigger.create({
            trigger: bar, start: 'top 80%', once: true,
            onEnter: () => {
                gsap.fromTo(bar, { width: '0%' }, { width: target + '%', duration: 1.5, ease: 'power3.out', delay: i * 0.1 })
            },
        })
    })

    // ── CLOSING CTA ────────────────────────────────────────────────────
    gsap.fromTo('.closing-box',
        { opacity: 0, y: 50, scale: 0.9 },
        {
            opacity: 1, y: 0, scale: 1, duration: 1, ease: 'power3.out',
            scrollTrigger: { trigger: '.closing-box', start: 'top 75%' },
        },
    )

    // ── PARALLAX BG ────────────────────────────────────────────────────
    if (heroRef.value) {
        gsap.to('.hero-bg-stars', {
            y: '30%', ease: 'none',
            scrollTrigger: { trigger: heroRef.value, start: 'top top', end: 'bottom top', scrub: true },
        })
    }
})

onUnmounted(() => {
    ScrollTrigger.getAll().forEach((t) => t.kill())
    cleanups.forEach((fn) => fn())
    cancelAnimationFrame(starsAnimFrame)
})
</script>

<template>
    <Head title="Agent Amira — Mission Complete | BSSW Degree" />

    <div class="relative overflow-x-hidden bg-[#0a0815] text-white min-h-screen" style="font-family: 'Courier New', Courier, monospace;">

        <!-- ══════════════════════════════════════════════════════════
             HERO SECTION
        ══════════════════════════════════════════════════════════ -->
        <section ref="heroRef" class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden px-4 pt-20 pb-16">
            <canvas ref="starsCanvas" class="hero-bg-stars absolute inset-0 w-full h-full"></canvas>

            <!-- Grid overlay -->
            <div class="absolute inset-0 opacity-[0.02] pointer-events-none"
                 style="background-image: linear-gradient(rgba(34,197,94,.6) 1px, transparent 1px), linear-gradient(90deg, rgba(34,197,94,.6) 1px, transparent 1px); background-size: 80px 80px;">
            </div>

            <div class="relative z-10 max-w-5xl mx-auto w-full">
                <!-- Hero Grid Layout -->
                <div class="grid md:grid-cols-2 gap-8 md:gap-12 items-center">
                    <!-- Left: Content -->
                    <div class="flex flex-col justify-center order-2 md:order-1">
                        <div class="mb-4 inline-block w-fit">
                            <div class="px-3 py-1 border border-green-400/50 bg-green-400/10 text-green-300 text-[8px] tracking-[0.35em] uppercase">
                                ✓ MISSION COMPLETE
                            </div>
                        </div>

                        <h1 ref="heroTitle"
                            class="text-4xl md:text-5xl lg:text-6xl font-black uppercase leading-tight mb-4"
                            style="text-shadow: 0 0 40px rgba(34,197,94,.5);">
                            <span class="text-green-400">Agent</span>
                            <br>
                            <span class="text-white">Amira</span>
                        </h1>

                        <div class="hero-subtitle space-y-2">
                            <p class="text-base md:text-lg tracking-[0.2em] text-green-300/80">
                                Bachelor of Science in Social Work
                            </p>
                            <p class="text-sm md:text-base text-white/50 leading-relaxed max-w-sm">
                                Cleared for active field deployment in social work and community advocacy. Ready to make an impact.
                            </p>
                        </div>

                        <div class="mt-8 pt-6 border-t border-green-400/20">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <div class="text-2xl md:text-3xl font-black text-green-400">✓</div>
                                    <div class="text-[9px] text-white/40 mt-1 tracking-widest">APPROVED</div>
                                </div>
                                <div>
                                    <div class="text-2xl md:text-3xl font-black text-amber-300">2026</div>
                                    <div class="text-[9px] text-white/40 mt-1 tracking-widest">YEAR</div>
                                </div>
                                <div>
                                    <div class="text-2xl md:text-3xl font-black text-purple-400">∞</div>
                                    <div class="text-[9px] text-white/40 mt-1 tracking-widest">IMPACT</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Hero Image -->
                    <div class="order-1 md:order-2">
                        <div class="hero-img rounded-xl overflow-hidden border-2 border-green-400/30"
                             style="aspect-ratio: 9/11; box-shadow: 0 0 50px rgba(34,197,94,.25), inset 0 0 50px rgba(34,197,94,.05);">
                            <img src="/images/Gemini_Generated_Image_lr9ynrlr9ynrlr9y.png" alt="Agent Amira"
                                 class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             AGENT PROFILE SECTION
        ══════════════════════════════════════════════════════════ -->
        <section class="relative py-20 md:py-28 px-4 overflow-hidden">
            <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(34,197,94,.05) 0%, rgba(34,197,94,.02) 100%);"></div>

            <div class="relative z-10 max-w-5xl mx-auto">
                <div class="grid md:grid-cols-2 gap-12 md:gap-16 items-center">
                    <!-- Skills -->
                    <div>
                        <h2 class="text-2xl md:text-3xl font-black text-green-400 mb-8 tracking-wider">CORE COMPETENCIES</h2>
                        <div class="space-y-4">
                            <div v-for="(skill, i) in skills" :key="i"
                                 class="skill-card">
                                <div class="flex items-start gap-4">
                                    <div class="text-3xl shrink-0">{{ skill.icon }}</div>
                                    <div>
                                        <h3 class="font-bold text-white mb-1 tracking-wide">{{ skill.label }}</h3>
                                        <p class="text-sm text-white/50 leading-relaxed">{{ skill.desc }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div>
                        <h2 class="text-2xl md:text-3xl font-black text-green-400 mb-8 tracking-wider">MISSION STATS</h2>
                        <div class="space-y-7">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-bold text-white tracking-wide">Persistence</span>
                                    <span class="text-sm text-green-400 font-bold">100%</span>
                                </div>
                                <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                                    <div class="stat-bar h-full w-0 rounded-full" style="background: linear-gradient(90deg, #22c55e, #86efac); box-shadow: 0 0 15px rgba(34,197,94,.6);"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-bold text-white tracking-wide">Dedication</span>
                                    <span class="text-sm text-green-400 font-bold">100%</span>
                                </div>
                                <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                                    <div class="stat-bar h-full w-0 rounded-full" style="background: linear-gradient(90deg, #22c55e, #86efac); box-shadow: 0 0 15px rgba(34,197,94,.6);"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-bold text-white tracking-wide">Community Impact</span>
                                    <span class="text-sm text-green-400 font-bold">100%</span>
                                </div>
                                <div class="h-2 bg-white/10 rounded-full overflow-hidden">
                                    <div class="stat-bar h-full w-0 rounded-full" style="background: linear-gradient(90deg, #22c55e, #86efac); box-shadow: 0 0 15px rgba(34,197,94,.6);"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             MISSION SUPPORT SECTION
        ══════════════════════════════════════════════════════════ -->
        <section class="relative py-20 md:py-28 px-4 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-green-400/5 to-transparent"></div>

            <div class="relative z-10 max-w-4xl mx-auto">
                <h2 class="text-2xl md:text-3xl font-black text-center text-purple-300 mb-12 tracking-wider">
                    TEAM SUPPORT
                </h2>

                <div class="family-img rounded-lg overflow-hidden border border-purple-400/30"
                     style="box-shadow: 0 0 40px rgba(192,132,252,.15);">
                    <img src="/images/5826936767401675501.jpg" alt="Family Support Team"
                         class="w-full h-auto object-cover">
                </div>

                <p class="text-center text-purple-300/60 text-sm md:text-base mt-6 tracking-[0.2em]">
                    Every great agent has a support system. Yours believes in you.
                </p>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             CLOSING SECTION
        ══════════════════════════════════════════════════════════ -->
        <section class="relative py-24 md:py-32 px-4 overflow-hidden">
            <div class="absolute inset-0" style="background: linear-gradient(135deg, #0a0815 0%, #0f0920 100%);"></div>

            <div class="relative z-10 max-w-3xl mx-auto">
                <div class="closing-box text-center space-y-8">
                    <div class="space-y-3 mb-8">
                        <h2 class="text-3xl md:text-5xl font-black text-white tracking-wider">
                            Your Mission Awaits
                        </h2>
                        <p class="text-base md:text-lg text-white/70 leading-relaxed">
                            You've earned your degree. Now comes the real work — helping others find their way, restoring hope, and building stronger communities. This is where your impact begins.
                        </p>
                    </div>

                    <div class="border-t border-b border-green-400/20 py-8">
                        <p class="text-xl md:text-2xl font-black text-green-400 mb-3" style="text-shadow: 0 0 20px rgba(34,197,94,.4);">
                            Welcome, Agent Amira
                        </p>
                        <p class="text-sm md:text-base text-white/50 tracking-[0.25em]">
                            AUTHORIZED FOR FIELD OPERATIONS
                        </p>
                    </div>

                    <div class="pt-4">
                        <p class="text-white/60 text-sm md:text-base leading-relaxed max-w-2xl mx-auto">
                            Every person you help. Every life you change. Every moment you choose compassion over indifference — that's your real mission. The world doesn't need another degree holder. It needs someone who cares like you do.
                        </p>
                    </div>
                </div>
            </div>
        </section>

    </div>
</template>

<style scoped>
canvas {
    display: block;
}
</style>
