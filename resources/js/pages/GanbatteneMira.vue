<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const starsCanvas = ref<HTMLCanvasElement | null>(null)
const endingCanvas = ref<HTMLCanvasElement | null>(null)
const heroTitle = ref<HTMLElement | null>(null)
const heroSubtitle = ref<HTMLElement | null>(null)
const heroStatus = ref<HTMLElement | null>(null)
const heroSeal = ref<HTMLElement | null>(null)
const heroRef = ref<HTMLElement | null>(null)

const quotes = [
    "You've come farther than you realize.",
    'The finish line is proof of how strong you\'ve become.',
    'The future is waiting for your next mission.',
    'Every challenge you conquered became part of your story.',
]
const currentQuote = ref(0)
const quoteText = ref(quotes[0])
let quoteInterval: ReturnType<typeof setInterval> | null = null

const journeyItems = [
    { label: 'CLASSIFIED', title: 'Late Nights', desc: 'Hours spent mastering the mission when others rested.', icon: '🌙' },
    { label: 'CONFIDENTIAL', title: 'Difficult Exams', desc: 'Each test was an obstacle course designed to forge resilience.', icon: '📋' },
    { label: 'RESTRICTED', title: 'Moments of Doubt', desc: 'Even the best agents question themselves. You chose to keep going.', icon: '🔐' },
    { label: 'INTEL REPORT', title: 'Continuous Learning', desc: 'Acquiring skills and knowledge like a true field expert.', icon: '📖' },
    { label: 'FIELD REPORT', title: 'Remarkable Growth', desc: 'Documented evidence of transformation across the entire mission.', icon: '📈' },
    { label: 'VERIFIED', title: 'Unbreakable Determination', desc: 'No setback could stop the mission. That is the mark of a true agent.', icon: '💪' },
]

const burstParticles = Array.from({ length: 20 }, (_, i) => ({
    angle: (i / 20) * 360,
    dist: 80 + Math.random() * 60,
    size: Math.round(4 + Math.random() * 6),
}))

let starsAnimFrame = 0
let endingAnimFrame = 0
const cleanups: (() => void)[] = []

function initStars(canvas: HTMLCanvasElement, isEnding = false): () => void {
    const ctx = canvas.getContext('2d')!
    let stars: { x: number; y: number; r: number; opacity: number; delta: number }[] = []
    let raf = 0

    function resize() {
        canvas.width = canvas.offsetWidth
        canvas.height = canvas.offsetHeight
        stars = Array.from({ length: 200 }, () => ({
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
            ctx.fillStyle = isEnding
                ? `rgba(251, 220, 150, ${s.opacity})`
                : `rgba(200, 220, 255, ${s.opacity})`
            ctx.fill()
        }
        raf = requestAnimationFrame(draw)
        if (isEnding) endingAnimFrame = raf
        else starsAnimFrame = raf
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
        s.textContent = ch === ' ' ? ' ' : ch
        s.style.display = 'inline-block'
        el.appendChild(s)
        return s
    })
}

function splitWords(el: HTMLElement): HTMLSpanElement[] {
    const words = (el.textContent ?? '').split(' ')
    el.innerHTML = ''
    return words.map((w, i) => {
        const s = document.createElement('span')
        s.textContent = i < words.length - 1 ? w + ' ' : w
        s.style.display = 'inline-block'
        el.appendChild(s)
        return s
    })
}

function animateCounter(el: HTMLElement, target: number) {
    const obj = { val: 0 }
    gsap.to(obj, {
        val: target,
        duration: 2,
        ease: 'power2.out',
        onUpdate: () => { el.textContent = Math.round(obj.val) + '%' },
    })
}

async function typewriter(el: HTMLElement, text: string, speed = 38) {
    el.textContent = ''
    for (const ch of text) {
        el.textContent += ch
        await new Promise<void>((r) => setTimeout(r, speed))
    }
}

onMounted(() => {
    if (starsCanvas.value) cleanups.push(initStars(starsCanvas.value, false))
    if (endingCanvas.value) cleanups.push(initStars(endingCanvas.value, true))

    // ── HERO ──────────────────────────────────────────────────────────────
    if (heroTitle.value) {
        const chars = splitChars(heroTitle.value)
        gsap.set(chars, { opacity: 0, y: 70, rotateX: -90 })
        gsap.to(chars, {
            opacity: 1, y: 0, rotateX: 0,
            duration: 0.85, stagger: 0.04, ease: 'back.out(1.7)', delay: 0.4,
        })
    }

    if (heroSubtitle.value) {
        gsap.fromTo(heroSubtitle.value,
            { opacity: 0, y: 28, filter: 'blur(12px)' },
            { opacity: 1, y: 0, filter: 'blur(0px)', duration: 1.2, delay: 2, ease: 'power3.out' },
        )
    }

    if (heroStatus.value) {
        gsap.fromTo(heroStatus.value,
            { opacity: 0, scaleX: 0 },
            { opacity: 1, scaleX: 1, duration: 0.8, delay: 2.6, ease: 'power3.out', transformOrigin: 'center' },
        )
    }

    if (heroSeal.value) {
        gsap.fromTo(heroSeal.value,
            { opacity: 0, scale: 0, rotation: -200 },
            { opacity: 1, scale: 1, rotation: 0, duration: 1.3, delay: 3.0, ease: 'elastic.out(1, 0.45)' },
        )
        gsap.to(heroSeal.value, {
            rotation: '+=6', duration: 2.5, repeat: -1, yoyo: true, ease: 'sine.inOut', delay: 4.5,
        })
    }

    gsap.utils.toArray<HTMLElement>('.floating-doc').forEach((doc, i) => {
        gsap.to(doc, {
            y: `+=${25 + i * 12}`, x: `+=${8 - i * 4}`, rotation: `+=${4 - i * 2}`,
            duration: 3 + i * 0.6, repeat: -1, yoyo: true, ease: 'sine.inOut',
        })
    })

    if (heroRef.value) {
        gsap.to('.hero-bg-layer', {
            y: '35%', ease: 'none',
            scrollTrigger: { trigger: heroRef.value, start: 'top top', end: 'bottom top', scrub: true },
        })
    }

    // ── JOURNEY ───────────────────────────────────────────────────────────
    const journeyHeadline = document.querySelector<HTMLElement>('.journey-headline')
    if (journeyHeadline) {
        const words = splitWords(journeyHeadline)
        gsap.fromTo(words,
            { opacity: 0, y: 42 },
            {
                opacity: 1, y: 0, stagger: 0.07, duration: 0.75, ease: 'power3.out',
                scrollTrigger: { trigger: journeyHeadline, start: 'top 82%' },
            },
        )
    }

    gsap.utils.toArray<HTMLElement>('.journey-card').forEach((card, i) => {
        gsap.fromTo(card,
            { opacity: 0, x: i % 2 === 0 ? -80 : 80 },
            {
                opacity: 1, x: 0, duration: 0.9, ease: 'power3.out',
                scrollTrigger: { trigger: card, start: 'top 87%', toggleActions: 'play none none reverse' },
            },
        )
    })

    // ── INTELLIGENCE ─────────────────────────────────────────────────────
    const intelTitle = document.querySelector<HTMLElement>('.intel-title')
    if (intelTitle) {
        const chars = splitChars(intelTitle)
        gsap.fromTo(chars,
            { opacity: 0, y: 28 },
            {
                opacity: 1, y: 0, stagger: 0.03, duration: 0.5, ease: 'power2.out',
                scrollTrigger: { trigger: intelTitle, start: 'top 82%' },
            },
        )
    }

    gsap.utils.toArray<HTMLElement>('.stat-bar-fill').forEach((bar, i) => {
        const target = parseInt(bar.dataset.target ?? '100', 10)
        ScrollTrigger.create({
            trigger: bar, start: 'top 82%', once: true,
            onEnter: () => {
                gsap.fromTo(bar, { width: '0%' }, { width: target + '%', duration: 1.9, ease: 'power3.out', delay: i * 0.15 })
                const counter = bar.closest('.stat-item')?.querySelector<HTMLElement>('.stat-number')
                if (counter) animateCounter(counter, target)
            },
        })
    })

    // ── MESSAGES ──────────────────────────────────────────────────────────
    const cycleQuote = async () => {
        const el = document.querySelector<HTMLElement>('.quote-text')
        if (!el) return
        await gsap.to(el, { opacity: 0, y: -18, duration: 0.45, ease: 'power2.in' })
        currentQuote.value = (currentQuote.value + 1) % quotes.length
        quoteText.value = quotes[currentQuote.value]
        await new Promise<void>((r) => setTimeout(r, 50))
        await gsap.fromTo(el, { opacity: 0, y: 18 }, { opacity: 1, y: 0, duration: 0.6, ease: 'power2.out' })
    }

    ScrollTrigger.create({
        trigger: '.messages-section', start: 'top 65%',
        onEnter: () => {
            const el = document.querySelector<HTMLElement>('.quote-text')
            if (el) typewriter(el, quotes[0], 38)
            quoteInterval = setInterval(cycleQuote, 3800)
        },
        onLeave: () => { if (quoteInterval) { clearInterval(quoteInterval); quoteInterval = null } },
        onEnterBack: () => { quoteInterval = setInterval(cycleQuote, 3800) },
        onLeaveBack: () => { if (quoteInterval) { clearInterval(quoteInterval); quoteInterval = null } },
    })

    // ── COUNTDOWN ─────────────────────────────────────────────────────────
    ScrollTrigger.create({
        trigger: '.countdown-section', start: 'top 62%', once: true,
        onEnter: () => {
            const nums = gsap.utils.toArray<HTMLElement>('.countdown-num')
            const complete = document.querySelector<HTMLElement>('.mission-complete')
            const tl = gsap.timeline()
            nums.forEach((num) => {
                tl.fromTo(num,
                    { scale: 3.5, opacity: 0, filter: 'blur(24px)' },
                    { scale: 1, opacity: 1, filter: 'blur(0px)', duration: 0.55, ease: 'power3.out' },
                )
                tl.to(num, { scale: 0.4, opacity: 0, filter: 'blur(16px)', duration: 0.38, ease: 'power3.in' })
            })
            if (complete) {
                tl.fromTo(complete,
                    { scale: 0, opacity: 0, rotation: -12 },
                    { scale: 1, opacity: 1, rotation: 0, duration: 1.2, ease: 'elastic.out(1, 0.45)' },
                )
                tl.to('.burst-particle', {
                    opacity: 1, scale: 1,
                    stagger: { amount: 0.5, from: 'random' },
                    duration: 0.4,
                }, '-=0.9')
            }
        },
    })

    // ── LETTER ────────────────────────────────────────────────────────────
    gsap.utils.toArray<HTMLElement>('.letter-para').forEach((para) => {
        gsap.fromTo(para,
            { opacity: 0, y: 36 },
            {
                opacity: 1, y: 0, duration: 0.85, ease: 'power3.out',
                scrollTrigger: { trigger: para, start: 'top 88%', toggleActions: 'play none none reverse' },
            },
        )
    })

    // ── ENDING ────────────────────────────────────────────────────────────
    gsap.utils.toArray<HTMLElement>('.light-ray').forEach((ray, i) => {
        gsap.fromTo(ray,
            { opacity: 0 },
            { opacity: 0.12 + i * 0.04, duration: 2.5 + i * 0.4, repeat: -1, yoyo: true, ease: 'sine.inOut', delay: i * 0.45 },
        )
    })

    gsap.utils.toArray<HTMLElement>('.ending-particle').forEach((p, i) => {
        gsap.to(p, {
            y: -(80 + i * 22), x: (i % 2 === 0 ? 1 : -1) * (20 + i * 6),
            opacity: 0, duration: 4 + i * 0.6, repeat: -1, delay: i * 0.35, ease: 'power1.out',
        })
    })

    const endingTl = gsap.timeline({
        scrollTrigger: { trigger: '.ending-section', start: 'top 62%', once: true },
    })
    endingTl
        .fromTo('.ending-status-label',
            { opacity: 0, letterSpacing: '0.6em' },
            { opacity: 1, letterSpacing: '0.35em', duration: 1, ease: 'power3.out' },
        )
        .fromTo('.ending-success',
            { opacity: 0, scale: 0.25, filter: 'blur(30px)' },
            { opacity: 1, scale: 1, filter: 'blur(0px)', duration: 1.6, ease: 'power3.out' },
            '-=0.3',
        )
        .fromTo('.ending-name',
            { opacity: 0, y: 30 },
            { opacity: 1, y: 0, duration: 0.9, ease: 'power3.out' },
            '-=0.5',
        )
        .fromTo('.ending-tagline',
            { opacity: 0, scale: 1.6, filter: 'blur(12px)' },
            { opacity: 1, scale: 1, filter: 'blur(0px)', duration: 1.1, ease: 'elastic.out(1, 0.5)' },
            '+=0.25',
        )
})

onUnmounted(() => {
    ScrollTrigger.getAll().forEach((t) => t.kill())
    if (quoteInterval) clearInterval(quoteInterval)
    cleanups.forEach((fn) => fn())
    cancelAnimationFrame(starsAnimFrame)
    cancelAnimationFrame(endingAnimFrame)
})
</script>

<template>
    <Head title="Ganbatte ne, Mira! | Mission: Graduation" />

    <div class="relative overflow-x-hidden bg-[#050a1a] text-white" style="font-family: 'Courier New', Courier, monospace;">

        <!-- ══════════════════════════════════════════════════════════
             SECTION 1 — MISSION BRIEFING
        ══════════════════════════════════════════════════════════ -->
        <section ref="heroRef" class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden">

            <!-- Stars canvas -->
            <canvas ref="starsCanvas" class="hero-bg-layer absolute inset-0 w-full h-full"></canvas>

            <!-- Grid overlay -->
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none"
                 style="background-image: linear-gradient(rgba(251,191,36,.6) 1px, transparent 1px), linear-gradient(90deg, rgba(251,191,36,.6) 1px, transparent 1px); background-size: 60px 60px;">
            </div>

            <!-- Vignette -->
            <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(ellipse at center, transparent 38%, #050a1a 100%);"></div>

            <!-- Floating classified docs -->
            <div class="floating-doc absolute top-[8%] left-[4%] w-28 md:w-36 opacity-10 pointer-events-none" style="transform: rotate(-14deg);">
                <div class="border border-amber-400/40 bg-amber-400/5 p-3 text-[6px] md:text-[7px] text-amber-200/60 leading-relaxed">
                    <div class="text-amber-400 font-bold mb-1 tracking-widest">TOP SECRET</div>
                    <div>SUBJECT: AMIRA REYSHI</div>
                    <div>CLEARANCE: MAXIMUM</div>
                    <div>STATUS: ACTIVE</div>
                    <div>MISSION: GRADUATION</div>
                    <div class="mt-1 border-t border-amber-400/20 pt-1">████████████████</div>
                    <div>████████ REDACTED</div>
                </div>
            </div>
            <div class="floating-doc absolute top-[18%] right-[3%] w-24 md:w-32 opacity-10 pointer-events-none" style="transform: rotate(11deg);">
                <div class="border border-rose-400/40 bg-rose-400/5 p-3 text-[6px] text-rose-200/60 leading-relaxed">
                    <div class="text-rose-400 font-bold mb-1 tracking-widest">CLASSIFIED</div>
                    <div>AGENT PROFILE</div>
                    <div>CODE: A.R.C</div>
                    <div>████████</div>
                    <div>MISSION ACTIVE</div>
                </div>
            </div>
            <div class="floating-doc absolute bottom-[22%] left-[7%] w-20 opacity-10 pointer-events-none hidden md:block" style="transform: rotate(7deg);">
                <div class="border border-sky-400/30 bg-sky-400/5 p-3 text-[6px] text-sky-200/50 leading-relaxed">
                    <div class="text-sky-400 font-bold mb-1">INTEL FILE</div>
                    <div>REF: GRD-2025</div>
                    <div>████████</div>
                    <div>███████</div>
                </div>
            </div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col items-center text-center px-4 max-w-5xl mx-auto">

                <div ref="heroStatus"
                     class="mb-7 px-4 py-1.5 border border-amber-400/50 bg-amber-400/10 text-amber-300 text-[9px] md:text-[11px] tracking-[0.38em] uppercase origin-center opacity-0">
                    ⬡ MISSION STATUS: GRADUATION INCOMING ⬡
                </div>

                <h1 ref="heroTitle"
                    class="text-4xl sm:text-6xl md:text-8xl font-black uppercase leading-none mb-2"
                    style="text-shadow: 0 0 40px rgba(251,191,36,.5), 0 0 80px rgba(251,191,36,.2);">
                    <span class="text-amber-300">GANBATTE NE,</span>
                    <br>
                    <span class="text-white">MIRA</span>
                </h1>

                <p ref="heroSubtitle"
                   class="mt-5 text-sm md:text-base tracking-[0.32em] text-amber-200/55 uppercase opacity-0">
                    Agent Amira Reyshi Cantado
                </p>

                <!-- Seal -->
                <div ref="heroSeal" class="mt-12 md:mt-16 opacity-0">
                    <div class="w-40 h-40 md:w-52 md:h-52 rounded-full border-4 border-amber-400/65 flex flex-col items-center justify-center relative"
                         style="background: radial-gradient(circle, rgba(251,191,36,.12) 0%, rgba(5,10,26,.9) 70%); box-shadow: 0 0 50px rgba(251,191,36,.28), inset 0 0 30px rgba(251,191,36,.08);">
                        <svg class="absolute inset-0 w-full h-full" viewBox="0 0 200 200">
                            <path id="seal-path" d="M 100,100 m -80,0 a 80,80 0 1,1 160,0 a 80,80 0 1,1 -160,0" fill="none"/>
                            <text font-size="10.5" fill="rgba(251,191,36,.65)" letter-spacing="2.8" font-family="Courier New">
                                <textPath href="#seal-path">✦ MISSION: ALMOST COMPLETE ✦ AGENT: A.R.C ✦ </textPath>
                            </text>
                        </svg>
                        <div class="text-center z-10">
                            <div class="text-3xl mb-1">🎓</div>
                            <div class="text-[8px] md:text-[9px] tracking-[0.22em] text-amber-300/75 font-bold">MISSION</div>
                            <div class="text-[10px] md:text-[12px] tracking-[0.14em] text-amber-300 font-black">ALMOST</div>
                            <div class="text-[10px] md:text-[12px] tracking-[0.14em] text-amber-300 font-black">COMPLETE</div>
                        </div>
                    </div>
                </div>

                <!-- Scroll hint -->
                <div class="mt-14 flex flex-col items-center gap-1.5 text-amber-400/35 animate-bounce">
                    <span class="text-[8px] tracking-[0.35em]">SCROLL TO PROCEED</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 2 — THE JOURNEY
        ══════════════════════════════════════════════════════════ -->
        <section class="relative py-24 md:py-36 px-4">
            <div class="absolute inset-0" style="background: linear-gradient(180deg, #050a1a 0%, #070d20 50%, #050a1a 100%);"></div>
            <!-- Scanlines -->
            <div class="absolute inset-0 opacity-[0.015] pointer-events-none"
                 style="background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(255,255,255,.1) 2px, rgba(255,255,255,.1) 4px);">
            </div>

            <div class="relative z-10 max-w-5xl mx-auto">
                <h2 class="journey-headline text-center text-2xl md:text-4xl font-black tracking-wider text-amber-300 mb-3 uppercase">
                    Every great agent earns their victory.
                </h2>
                <p class="text-center text-amber-200/35 text-[10px] tracking-[0.32em] mb-16 uppercase">— Field Chronicle: Agent A.R.C —</p>

                <!-- Timeline -->
                <div class="relative">
                    <!-- Center line -->
                    <div class="absolute left-1/2 top-0 bottom-0 w-px hidden md:block"
                         style="background: linear-gradient(180deg, transparent, rgba(251,191,36,.28) 20%, rgba(251,191,36,.28) 80%, transparent);"></div>

                    <div class="space-y-8 md:space-y-12">
                        <div v-for="(item, i) in journeyItems" :key="i"
                             class="journey-card relative"
                             :class="i % 2 === 0 ? 'md:pr-[52%]' : 'md:pl-[52%]'">

                            <!-- Timeline dot -->
                            <div class="hidden md:block absolute left-1/2 top-6 w-3 h-3 rounded-full bg-amber-400 -translate-x-1/2"
                                 style="box-shadow: 0 0 12px rgba(251,191,36,.9);"></div>

                            <div class="border border-amber-400/20 bg-amber-400/5 p-5 md:p-6 relative overflow-hidden group hover:border-amber-400/40 transition-colors duration-300"
                                 style="backdrop-filter: blur(4px);">
                                <!-- Corner marks -->
                                <div class="absolute top-0 left-0 w-3 h-3 border-t border-l border-amber-400/55"></div>
                                <div class="absolute top-0 right-0 w-3 h-3 border-t border-r border-amber-400/55"></div>
                                <div class="absolute bottom-0 left-0 w-3 h-3 border-b border-l border-amber-400/55"></div>
                                <div class="absolute bottom-0 right-0 w-3 h-3 border-b border-r border-amber-400/55"></div>

                                <div class="flex items-start gap-3">
                                    <span class="text-2xl shrink-0">{{ item.icon }}</span>
                                    <div>
                                        <div class="text-[8px] tracking-[0.32em] text-amber-400/55 uppercase mb-1">{{ item.label }}</div>
                                        <h3 class="text-base md:text-lg font-bold text-amber-200 tracking-wide mb-1.5">{{ item.title }}</h3>
                                        <p class="text-xs md:text-sm text-amber-100/50 leading-relaxed">{{ item.desc }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 3 — SECRET INTELLIGENCE REPORT
        ══════════════════════════════════════════════════════════ -->
        <section class="relative py-24 md:py-36 px-4 overflow-hidden">
            <div class="absolute inset-0 bg-[#040812]"></div>
            <div class="absolute inset-0 opacity-[0.04] pointer-events-none"
                 style="background-image: linear-gradient(rgba(56,189,248,.8) 1px, transparent 1px), linear-gradient(90deg, rgba(56,189,248,.8) 1px, transparent 1px); background-size: 40px 40px;">
            </div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 rounded-full pointer-events-none"
                 style="background: radial-gradient(circle, rgba(56,189,248,.3) 0%, transparent 70%); opacity: .1;"></div>

            <div class="relative z-10 max-w-3xl mx-auto">
                <div class="text-center mb-12">
                    <div class="text-[9px] tracking-[0.48em] text-sky-400/55 mb-3 uppercase">// CLASSIFIED INTELLIGENCE REPORT //</div>
                    <h2 class="intel-title text-2xl md:text-4xl font-black tracking-wider text-sky-300 uppercase">Agent Assessment</h2>
                </div>

                <div class="border border-sky-400/22 p-6 md:p-10 relative" style="background: rgba(56,189,248,.04); backdrop-filter: blur(4px);">
                    <div class="absolute top-0 left-0 w-4 h-4 border-t-2 border-l-2 border-sky-400/55"></div>
                    <div class="absolute top-0 right-0 w-4 h-4 border-t-2 border-r-2 border-sky-400/55"></div>
                    <div class="absolute bottom-0 left-0 w-4 h-4 border-b-2 border-l-2 border-sky-400/55"></div>
                    <div class="absolute bottom-0 right-0 w-4 h-4 border-b-2 border-r-2 border-sky-400/55"></div>

                    <!-- Top bar -->
                    <div class="flex items-center gap-2 mb-8 pb-4 border-b border-sky-400/18">
                        <div class="w-2 h-2 rounded-full bg-sky-400 animate-pulse" style="box-shadow: 0 0 7px rgba(56,189,248,.9);"></div>
                        <div class="w-2 h-2 rounded-full bg-amber-400 animate-pulse" style="animation-delay:.3s; box-shadow: 0 0 7px rgba(251,191,36,.9);"></div>
                        <div class="w-2 h-2 rounded-full bg-rose-400 animate-pulse" style="animation-delay:.6s; box-shadow: 0 0 7px rgba(251,113,133,.9);"></div>
                        <span class="text-[8px] text-sky-400/45 tracking-widest ml-2 uppercase">SYSTEM ONLINE — REPORT ID: GRD-ARC-2025</span>
                    </div>

                    <div class="space-y-7">
                        <!-- Sky stat -->
                        <div class="stat-item">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs md:text-sm tracking-[0.2em] uppercase text-sky-300">Persistence</span>
                                <span class="stat-number text-xs md:text-sm font-bold text-sky-300">0%</span>
                            </div>
                            <div class="h-1.5 md:h-2 overflow-hidden" style="background: rgba(255,255,255,.06);">
                                <div class="stat-bar-fill h-full w-0 relative" data-target="100"
                                     style="background: linear-gradient(90deg, #38bdf8, rgba(186,230,253,.7)); box-shadow: 0 0 10px rgba(56,189,248,.7);">
                                </div>
                            </div>
                        </div>
                        <!-- Amber stat -->
                        <div class="stat-item">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs md:text-sm tracking-[0.2em] uppercase text-amber-300">Courage</span>
                                <span class="stat-number text-xs md:text-sm font-bold text-amber-300">0%</span>
                            </div>
                            <div class="h-1.5 md:h-2 overflow-hidden" style="background: rgba(255,255,255,.06);">
                                <div class="stat-bar-fill h-full w-0 relative" data-target="100"
                                     style="background: linear-gradient(90deg, #fbbf24, rgba(253,230,138,.7)); box-shadow: 0 0 10px rgba(251,191,36,.7);">
                                </div>
                            </div>
                        </div>
                        <!-- Rose stat -->
                        <div class="stat-item">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xs md:text-sm tracking-[0.2em] uppercase text-rose-300">Resilience</span>
                                <span class="stat-number text-xs md:text-sm font-bold text-rose-300">0%</span>
                            </div>
                            <div class="h-1.5 md:h-2 overflow-hidden" style="background: rgba(255,255,255,.06);">
                                <div class="stat-bar-fill h-full w-0 relative" data-target="100"
                                     style="background: linear-gradient(90deg, #fb7185, rgba(253,164,175,.7)); box-shadow: 0 0 10px rgba(251,113,133,.7);">
                                </div>
                            </div>
                        </div>
                        <!-- Potential -->
                        <div class="stat-item">
                            <div class="flex justify-between items-center">
                                <span class="text-xs md:text-sm tracking-[0.2em] uppercase text-amber-300">Potential</span>
                                <span class="text-xs md:text-sm font-bold text-amber-300 tracking-wider">UNLIMITED</span>
                            </div>
                            <div class="h-1.5 md:h-2 mt-2 overflow-hidden" style="background: rgba(255,255,255,.06);">
                                <div class="stat-bar-fill h-full w-0" data-target="100"
                                     style="background: linear-gradient(90deg, #f59e0b, #fde68a, #f59e0b); background-size: 200% 100%; animation: shimmer 2s linear infinite; box-shadow: 0 0 16px rgba(251,191,36,.65);">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 pt-4 border-t border-sky-400/18 flex items-center justify-between">
                        <span class="text-[8px] text-sky-400/38 tracking-wider">ASSESSMENT: EXEMPLARY</span>
                        <span class="text-[8px] text-sky-400/38 tracking-wider">CLEARANCE: TOP SECRET</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 4 — MESSAGES FROM HEADQUARTERS
        ══════════════════════════════════════════════════════════ -->
        <section class="messages-section relative py-28 md:py-40 px-4 overflow-hidden">
            <div class="absolute inset-0" style="background: linear-gradient(180deg, #040812 0%, #0a0520 50%, #040812 100%);"></div>
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="w-[600px] h-[600px] rounded-full"
                     style="background: radial-gradient(circle, rgba(168,85,247,.32) 0%, transparent 70%); opacity: .18;">
                </div>
            </div>

            <div class="relative z-10 max-w-3xl mx-auto text-center">
                <div class="text-[9px] tracking-[0.48em] text-purple-400/55 mb-10 uppercase">// HEADQUARTERS TRANSMISSION //</div>

                <div class="min-h-[120px] md:min-h-[100px] flex items-center justify-center mb-10">
                    <p class="quote-text text-xl md:text-3xl font-bold text-white leading-relaxed"
                       style="text-shadow: 0 0 28px rgba(168,85,247,.6), 0 0 56px rgba(168,85,247,.28);">
                        {{ quoteText }}
                    </p>
                </div>

                <div class="flex justify-center gap-2.5">
                    <div v-for="(_, i) in quotes" :key="i"
                         class="w-1.5 h-1.5 rounded-full transition-all duration-300"
                         :style="currentQuote === i
                             ? 'background: #c084fc; box-shadow: 0 0 7px rgba(192,132,252,.9); transform: scale(1.4);'
                             : 'background: rgba(192,132,252,.28);'">
                    </div>
                </div>

                <div class="mt-12 text-[8px] tracking-[0.4em] text-purple-400/35 uppercase">— Transmitted from HQ —</div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 5 — GRADUATION LAUNCH SEQUENCE
        ══════════════════════════════════════════════════════════ -->
        <section class="countdown-section relative py-28 md:py-40 px-4 overflow-hidden">
            <div class="absolute inset-0 bg-[#020610]"></div>
            <!-- Spotlight -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-96 h-full pointer-events-none opacity-[0.18]"
                 style="background: conic-gradient(from 0deg at 50% 0%, transparent 32%, rgba(251,191,36,.25) 50%, transparent 68%);">
            </div>

            <div class="relative z-10 max-w-2xl mx-auto text-center">
                <div class="text-[9px] tracking-[0.48em] text-amber-400/55 mb-14 uppercase">// INITIATING GRADUATION LAUNCH SEQUENCE //</div>

                <div class="relative h-52 flex items-center justify-center">
                    <div v-for="num in ['3', '2', '1']" :key="num"
                         class="countdown-num absolute text-[120px] md:text-[160px] font-black text-amber-400 opacity-0"
                         style="text-shadow: 0 0 40px rgba(251,191,36,.9), 0 0 80px rgba(251,191,36,.45);">
                        {{ num }}
                    </div>
                </div>

                <div class="mission-complete opacity-0 mt-6">
                    <div class="relative inline-block">
                        <!-- Burst particles -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div v-for="(p, i) in burstParticles" :key="i"
                                 class="burst-particle absolute rounded-full bg-amber-400 opacity-0 scale-0"
                                 :style="`width:${p.size}px; height:${p.size}px; transform: rotate(${p.angle}deg) translateX(${p.dist}px);`">
                            </div>
                        </div>

                        <div class="border-2 border-amber-400/78 px-8 md:px-16 py-7 md:py-10"
                             style="box-shadow: 0 0 60px rgba(251,191,36,.28), inset 0 0 40px rgba(251,191,36,.05);">
                            <div class="text-[9px] tracking-[0.5em] text-amber-400/65 mb-3">GRADUATION SEQUENCE</div>
                            <div class="text-3xl md:text-5xl font-black text-amber-300 tracking-widest"
                                 style="text-shadow: 0 0 30px rgba(251,191,36,.8);">MISSION</div>
                            <div class="text-3xl md:text-5xl font-black text-white tracking-widest"
                                 style="text-shadow: 0 0 28px rgba(255,255,255,.38);">COMPLETE</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 6 — FINAL LETTER
        ══════════════════════════════════════════════════════════ -->
        <section class="relative py-24 md:py-36 px-4">
            <div class="absolute inset-0" style="background: linear-gradient(180deg, #020610 0%, #0d0a1a 50%, #020610 100%);"></div>
            <div class="absolute inset-0 pointer-events-none"
                 style="background: radial-gradient(ellipse at center, rgba(251,191,36,.12) 0%, transparent 55%); opacity: .22;">
            </div>

            <div class="relative z-10 max-w-2xl mx-auto">
                <div class="border border-amber-400/22 p-8 md:p-14 relative" style="background: linear-gradient(135deg, rgba(251,191,36,.06), rgba(251,191,36,.02), transparent); backdrop-filter: blur(4px);">
                    <div class="absolute top-0 left-0 w-5 h-5 border-t-2 border-l-2 border-amber-400/45"></div>
                    <div class="absolute top-0 right-0 w-5 h-5 border-t-2 border-r-2 border-amber-400/45"></div>
                    <div class="absolute bottom-0 left-0 w-5 h-5 border-b-2 border-l-2 border-amber-400/45"></div>
                    <div class="absolute bottom-0 right-0 w-5 h-5 border-b-2 border-r-2 border-amber-400/45"></div>

                    <div class="letter-para text-[9px] tracking-[0.4em] text-amber-400/50 uppercase mb-8">— Personal Message — Clearance: Amira Only —</div>

                    <p class="letter-para text-xl md:text-2xl font-bold text-amber-200 mb-8">Dear Amira,</p>

                    <p class="letter-para text-sm md:text-base text-amber-100/65 leading-loose mb-5">
                        You started this journey with dreams, uncertainty, and courage.
                    </p>
                    <p class="letter-para text-sm md:text-base text-amber-100/65 leading-loose mb-5">
                        Every assignment, every sleepless night, every challenge was another step toward this moment.
                    </p>
                    <p class="letter-para text-sm md:text-base text-amber-100/65 leading-loose mb-5">
                        Now you're standing at the edge of a brand new adventure.
                    </p>
                    <p class="letter-para text-sm md:text-base text-amber-100/65 leading-loose mb-5">
                        Graduation is not the end of your mission.
                    </p>
                    <p class="letter-para text-sm md:text-base text-amber-200/88 leading-loose mb-5 font-semibold">
                        It is only the beginning of your next one.
                    </p>

                    <div class="letter-para my-8 border-l-2 border-amber-400/38 pl-5 space-y-2.5">
                        <p class="text-sm text-amber-100/58 leading-loose">Keep believing in yourself.</p>
                        <p class="text-sm text-amber-100/58 leading-loose">Keep growing.</p>
                        <p class="text-sm text-amber-100/58 leading-loose">Keep dreaming.</p>
                        <p class="text-sm text-amber-100/58 leading-loose">Keep moving forward.</p>
                    </div>

                    <p class="letter-para text-xl md:text-2xl font-black text-amber-300 mt-9 mb-3"
                       style="text-shadow: 0 0 20px rgba(251,191,36,.4);">
                        Ganbatte ne, Mira.
                    </p>
                    <p class="letter-para text-sm text-amber-100/55 leading-loose">
                        The world is waiting for you.
                    </p>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 7 — FINAL CINEMATIC ENDING
        ══════════════════════════════════════════════════════════ -->
        <section class="ending-section relative min-h-screen flex flex-col items-center justify-center overflow-hidden px-4 py-20">
            <canvas ref="endingCanvas" class="absolute inset-0 w-full h-full"></canvas>

            <!-- Light rays -->
            <div v-for="i in 6" :key="i"
                 class="light-ray absolute top-0 left-1/2 w-px h-full origin-top opacity-0 pointer-events-none"
                 :style="`background: linear-gradient(180deg, rgba(251,191,36,.55) 0%, transparent 100%); transform: translateX(-50%) rotate(${(i - 3) * 13}deg);`">
            </div>

            <!-- Radial glow -->
            <div class="absolute inset-0 pointer-events-none"
                 style="background: radial-gradient(ellipse at center 62%, rgba(251,191,36,.1) 0%, rgba(168,85,247,.055) 40%, transparent 70%);">
            </div>

            <!-- Floating particles -->
            <div v-for="i in 12" :key="i"
                 class="ending-particle absolute w-1 h-1 rounded-full"
                 :style="`background: rgba(251,191,36,.55); bottom: ${10 + i * 7}%; left: ${8 + i * 7.5}%;`">
            </div>

            <div class="relative z-10 text-center max-w-2xl mx-auto">
                <div class="ending-status-label text-[9px] uppercase mb-8 opacity-0 text-amber-400/55"
                     style="letter-spacing: 0.35em;">
                    ⬡ Mission Status Report ⬡
                </div>

                <div class="ending-success text-7xl md:text-9xl font-black mb-8 opacity-0"
                     style="background: linear-gradient(135deg, #fbbf24, #fde68a, #fbbf24); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; filter: drop-shadow(0 0 40px rgba(251,191,36,.6));">
                    SUCCESS
                </div>

                <div class="ending-name mb-12 opacity-0">
                    <p class="text-sm md:text-base text-amber-200/55 tracking-[0.32em] uppercase mb-2">Congratulations,</p>
                    <p class="text-2xl md:text-4xl font-black text-white tracking-wider"
                       style="text-shadow: 0 0 30px rgba(255,255,255,.28);">
                        Amira Reyshi Cantado
                    </p>
                </div>

                <div class="ending-tagline opacity-0">
                    <div class="inline-block px-8 py-4 border border-amber-400/38"
                         style="background: rgba(251,191,36,.08); backdrop-filter: blur(4px);">
                        <p class="text-xl md:text-3xl font-black tracking-widest">
                            <span class="text-amber-300" style="text-shadow: 0 0 20px rgba(251,191,36,.8), 0 0 40px rgba(251,191,36,.4);">Ganbatte Ne</span>
                            <span class="text-white"> ✨</span>
                        </p>
                    </div>
                </div>
            </div>
        </section>

    </div>
</template>

<style scoped>
@keyframes shimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

canvas {
    display: block;
}
</style>
