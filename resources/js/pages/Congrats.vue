<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

const starsCanvas = ref<HTMLCanvasElement | null>(null)
const heroTitle = ref<HTMLElement | null>(null)
const heroRef = ref<HTMLElement | null>(null)

const testimonials = [
    { agent: 'AGENT: Y.F.', role: 'Field Operative', msg: 'Watching you grow has been the greatest mission. You\'ve earned this.' },
    { agent: 'AGENT: L.F.', role: 'Strategic Leader', msg: 'Your determination is unmatched. Now go change the world.' },
    { agent: 'AGENT: A.F.', role: 'Intelligence Specialist', msg: 'Heh. You knew you could do it all along. 😏' },
]

const futureMessages = [
    'You have the degree. You have the heart.',
    'Now begins your real mission: helping others find their way.',
    'Every person you help is another life secured.',
    'This is not the end. This is where it gets interesting.',
    'The world needed a social worker like you. Welcome, Agent.',
]

let starsAnimFrame = 0
const cleanups: (() => void)[] = []

function initStars(canvas: HTMLCanvasElement, lightColor = true): () => void {
    const ctx = canvas.getContext('2d')!
    let stars: { x: number; y: number; r: number; opacity: number; delta: number }[] = []
    let raf = 0

    function resize() {
        canvas.width = canvas.offsetWidth
        canvas.height = canvas.offsetHeight
        stars = Array.from({ length: 250 }, () => ({
            x: Math.random() * canvas.width,
            y: Math.random() * canvas.height,
            r: Math.random() * 1.6 + 0.4,
            opacity: Math.random() * 0.8 + 0.15,
            delta: (Math.random() - 0.5) * 0.018,
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
            ctx.fillStyle = lightColor
                ? `rgba(220, 240, 255, ${s.opacity * 0.8})`
                : `rgba(200, 220, 255, ${s.opacity})`
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
    if (starsCanvas.value) cleanups.push(initStars(starsCanvas.value, true))

    // ── HERO ──────────────────────────────────────────────────────────────
    if (heroTitle.value) {
        const chars = splitChars(heroTitle.value)
        gsap.set(chars, { opacity: 0, y: 80, rotateX: -100 })
        gsap.to(chars, {
            opacity: 1, y: 0, rotateX: 0,
            duration: 0.95, stagger: 0.05, ease: 'back.out(1.8)', delay: 0.3,
        })
    }

    if (heroRef.value) {
        gsap.to('.hero-bg-layer', {
            y: '40%', ease: 'none',
            scrollTrigger: { trigger: heroRef.value, start: 'top top', end: 'bottom top', scrub: true },
        })
    }

    // ── DEGREE CARD ───────────────────────────────────────────────────────
    const degreeCard = document.querySelector<HTMLElement>('.degree-card')
    if (degreeCard) {
        gsap.fromTo(degreeCard,
            { opacity: 0, scale: 0.8, rotateY: -25 },
            {
                opacity: 1, scale: 1, rotateY: 0, duration: 1.3, delay: 3.2, ease: 'back.out(1.5)',
                transformOrigin: 'center',
            },
        )
    }

    // ── STAMP ─────────────────────────────────────────────────────────────
    const stamp = document.querySelector<HTMLElement>('.mission-stamp')
    if (stamp) {
        gsap.fromTo(stamp,
            { opacity: 0, scale: 0, rotation: -45 },
            { opacity: 1, scale: 1, rotation: -12, duration: 0.8, delay: 4.1, ease: 'elastic.out(1.2, 0.5)' },
        )
    }

    // ── MIRA IMAGE ─────────────────────────────────────────────────────────
    gsap.utils.toArray<HTMLElement>('.celebration-image').forEach((img, i) => {
        gsap.fromTo(img,
            { opacity: 0, y: 50, scale: 0.9 },
            {
                opacity: 1, y: 0, scale: 1, duration: 1, stagger: 0.2, ease: 'power3.out',
                scrollTrigger: { trigger: img, start: 'top 80%', toggleActions: 'play none none reverse' },
            },
        )
    })

    // ── TESTIMONIALS ───────────────────────────────────────────────────────
    gsap.utils.toArray<HTMLElement>('.testimonial-card').forEach((card, i) => {
        gsap.fromTo(card,
            { opacity: 0, x: i % 2 === 0 ? -70 : 70, rotateY: i % 2 === 0 ? 8 : -8 },
            {
                opacity: 1, x: 0, rotateY: 0, duration: 1, ease: 'power3.out',
                scrollTrigger: { trigger: card, start: 'top 85%', toggleActions: 'play none none reverse' },
            },
        )
    })

    // ── FUTURE MISSION ─────────────────────────────────────────────────────
    const futureTitle = document.querySelector<HTMLElement>('.future-title')
    if (futureTitle) {
        const chars = splitChars(futureTitle)
        gsap.fromTo(chars,
            { opacity: 0, y: 32 },
            {
                opacity: 1, y: 0, stagger: 0.04, duration: 0.6, ease: 'power2.out',
                scrollTrigger: { trigger: futureTitle, start: 'top 80%' },
            },
        )
    }

    gsap.utils.toArray<HTMLElement>('.future-msg').forEach((msg, i) => {
        gsap.fromTo(msg,
            { opacity: 0, y: 28 },
            {
                opacity: 1, y: 0, duration: 0.8, ease: 'power3.out', delay: i * 0.12,
                scrollTrigger: { trigger: msg, start: 'top 88%', toggleActions: 'play none none reverse' },
            },
        )
    })

    // ── CLOSING ANIMATION ──────────────────────────────────────────────────
    const closingTl = gsap.timeline({
        scrollTrigger: { trigger: '.closing-section', start: 'top 70%', once: true },
    })
    closingTl
        .fromTo('.closing-badge',
            { opacity: 0, scale: 0.3, rotation: -180 },
            { opacity: 1, scale: 1, rotation: 0, duration: 1.2, ease: 'elastic.out(1, 0.45)' },
        )
        .fromTo('.closing-text',
            { opacity: 0, y: 40 },
            { opacity: 1, y: 0, duration: 0.9, ease: 'power3.out' },
            '-=0.6',
        )
        .to('.closing-particle', {
            opacity: 1, scale: 1, stagger: 0.08, duration: 0.5,
        }, '-=0.4')
})

onUnmounted(() => {
    ScrollTrigger.getAll().forEach((t) => t.kill())
    cleanups.forEach((fn) => fn())
    cancelAnimationFrame(starsAnimFrame)
})
</script>

<template>
    <Head title="Mission Complete: BSSW Graduation | Amira Reyshi Cantado" />

    <div class="relative overflow-x-hidden bg-[#040812] text-white" style="font-family: 'Courier New', Courier, monospace;">

        <!-- ══════════════════════════════════════════════════════════
             SECTION 1 — MISSION COMPLETE BRIEFING
        ══════════════════════════════════════════════════════════ -->
        <section ref="heroRef" class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden">
            <canvas ref="starsCanvas" class="hero-bg-layer absolute inset-0 w-full h-full"></canvas>

            <!-- Grid overlay -->
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none"
                 style="background-image: linear-gradient(rgba(34,197,94,.6) 1px, transparent 1px), linear-gradient(90deg, rgba(34,197,94,.6) 1px, transparent 1px); background-size: 60px 60px;">
            </div>

            <!-- Vignette -->
            <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(ellipse at center, transparent 38%, #040812 100%);"></div>

            <div class="relative z-10 flex flex-col items-center text-center px-4 max-w-5xl mx-auto">
                <div class="mb-6 px-4 py-1.5 border border-green-400/50 bg-green-400/10 text-green-300 text-[9px] md:text-[11px] tracking-[0.38em] uppercase origin-center">
                    ✓ CLASSIFIED: TOP SECRET ✓
                </div>

                <h1 ref="heroTitle"
                    class="text-4xl sm:text-6xl md:text-8xl font-black uppercase leading-none mb-2"
                    style="text-shadow: 0 0 50px rgba(34,197,94,.6), 0 0 100px rgba(34,197,94,.25);">
                    <span class="text-green-400">MISSION</span>
                    <br>
                    <span class="text-white">COMPLETE</span>
                </h1>

                <p class="mt-8 text-base md:text-xl tracking-[0.32em] text-green-300/75 uppercase">
                    Agent: Amira Reyshi Cantado
                </p>

                <div class="mt-12 text-amber-300 text-2xl md:text-4xl font-black tracking-wider" style="text-shadow: 0 0 30px rgba(251,191,36,.5);">
                    Bachelor of Science in Social Work
                </div>

                <p class="mt-6 text-green-200/50 text-sm md:text-base tracking-[0.24em]">
                    MISSION COMPLETION: ACCOMPLISHED
                </p>

                <div class="mt-16 text-green-400/40 animate-pulse text-[8px] tracking-[0.4em]">
                    — CLASSIFIED DOSSIER FOLLOWS —
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 2 — DEGREE DOSSIER
        ══════════════════════════════════════════════════════════ -->
        <section class="relative py-28 md:py-40 px-4 overflow-hidden">
            <div class="absolute inset-0" style="background: linear-gradient(180deg, #040812 0%, #0d0620 50%, #040812 100%);"></div>
            <div class="absolute inset-0 opacity-[0.015] pointer-events-none"
                 style="background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(255,255,255,.1) 2px, rgba(255,255,255,.1) 4px);">
            </div>

            <div class="relative z-10 max-w-3xl mx-auto">
                <div class="degree-card border-2 border-green-400/40 p-10 md:p-16 relative"
                     style="background: linear-gradient(135deg, rgba(34,197,94,.08), rgba(34,197,94,.02)), url('data:image/svg+xml;utf8,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><text x="10" y="90" font-size="12" opacity="0.05" fill="rgba(34,197,94,.3)">DEGREE</text></svg>');
                             backdrop-filter: blur(4px);">
                    <!-- Corner stamps -->
                    <div class="absolute top-4 left-4 w-6 h-6 border-t-2 border-l-2 border-green-400/50"></div>
                    <div class="absolute top-4 right-4 w-6 h-6 border-t-2 border-r-2 border-green-400/50"></div>
                    <div class="absolute bottom-4 left-4 w-6 h-6 border-b-2 border-l-2 border-green-400/50"></div>
                    <div class="absolute bottom-4 right-4 w-6 h-6 border-b-2 border-r-2 border-green-400/50"></div>

                    <div class="text-center mb-8 pb-6 border-b border-green-400/20">
                        <div class="text-[10px] tracking-[0.48em] text-green-400/55 uppercase mb-3">CLASSIFIED DOCUMENT</div>
                        <h2 class="text-2xl md:text-4xl font-black text-green-300 tracking-wider uppercase">Mission Dossier</h2>
                    </div>

                    <div class="space-y-4 text-sm md:text-base">
                        <div class="flex justify-between border-b border-green-400/15 pb-3">
                            <span class="text-green-400/60 tracking-[0.2em]">AGENT NAME</span>
                            <span class="text-green-300 font-bold">Amira Reyshi Cantado</span>
                        </div>
                        <div class="flex justify-between border-b border-green-400/15 pb-3">
                            <span class="text-green-400/60 tracking-[0.2em]">DEGREE AWARDED</span>
                            <span class="text-green-300 font-bold">Bachelor of Science in Social Work</span>
                        </div>
                        <div class="flex justify-between border-b border-green-400/15 pb-3">
                            <span class="text-green-400/60 tracking-[0.2em]">CLEARANCE LEVEL</span>
                            <span class="text-amber-300 font-bold">MAXIMUM</span>
                        </div>
                        <div class="flex justify-between border-b border-green-400/15 pb-3">
                            <span class="text-green-400/60 tracking-[0.2em]">STATUS</span>
                            <span class="text-green-400 font-bold animate-pulse">ACTIVE FIELD AGENT</span>
                        </div>
                        <div class="flex justify-between pt-2">
                            <span class="text-green-400/60 tracking-[0.2em]">MISSION CLASSIFICATION</span>
                            <span class="text-amber-400 font-black">TOP SECRET</span>
                        </div>
                    </div>
                </div>

                <!-- Stamp -->
                <div class="mission-stamp absolute top-1/3 right-12 w-32 h-32 opacity-0 pointer-events-none"
                     style="font-family: 'Impact', sans-serif;">
                    <div class="w-full h-full rounded-full border-4 border-green-500/60 flex items-center justify-center relative"
                         style="background: radial-gradient(circle, rgba(34,197,94,.15) 0%, transparent 70%); transform: rotate(-25deg);">
                        <div class="text-center">
                            <div class="text-[13px] font-black text-green-500 tracking-widest" style="text-shadow: 0 0 10px rgba(34,197,94,.6);">APPROVED</div>
                            <div class="text-[9px] text-green-400/70 mt-0.5 tracking-widest">CONFIRMED</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 3 — MIRA'S GRADUATION MOMENT
        ══════════════════════════════════════════════════════════ -->
        <section class="relative py-24 md:py-36 px-4 overflow-hidden">
            <div class="absolute inset-0" style="background: linear-gradient(180deg, #0d0620 0%, #080410 50%, #0d0620 100%);"></div>

            <div class="relative z-10 max-w-4xl mx-auto">
                <h2 class="text-center text-2xl md:text-4xl font-black text-amber-300 mb-12 tracking-wider"
                    style="text-shadow: 0 0 30px rgba(251,191,36,.4);">
                    FIELD DOCUMENTATION
                </h2>

                <div class="celebration-image rounded-lg overflow-hidden border-2 border-amber-400/30"
                     style="backdrop-filter: blur(4px); box-shadow: 0 0 40px rgba(251,191,36,.2), inset 0 0 30px rgba(251,191,36,.05);">
                    <img src="/storage/Gemini_Generated_Image_lr9ynrlr9ynrlr9y.png" alt="Amira's Graduation"
                         class="w-full h-auto object-cover">
                </div>

                <div class="mt-8 text-center text-amber-200/55 text-xs md:text-sm tracking-[0.3em]">
                    AGENT AMIRA AT MISSION COMPLETION — RAINBOW SKY, CLEAR FUTURE AHEAD
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 4 — MISSION SUPPORT TEAM
        ══════════════════════════════════════════════════════════ -->
        <section class="relative py-24 md:py-36 px-4 overflow-hidden">
            <div class="absolute inset-0" style="background: linear-gradient(180deg, #080410 0%, #0a0520 50%, #080410 100%);"></div>

            <div class="relative z-10 max-w-4xl mx-auto">
                <h2 class="text-center text-2xl md:text-4xl font-black text-purple-300 mb-4 tracking-wider">
                    Mission Support Team
                </h2>
                <p class="text-center text-purple-300/50 text-[9px] tracking-[0.3em] mb-12 uppercase">
                    Messages from your family — The Forgers
                </p>

                <!-- Family Image -->
                <div class="celebration-image rounded-lg overflow-hidden border-2 border-purple-400/30 mb-12"
                     style="backdrop-filter: blur(4px); box-shadow: 0 0 40px rgba(192,132,252,.2);">
                    <img src="/storage/5826936767401675501.jpg" alt="Spy x Family Support"
                         class="w-full h-auto object-cover">
                </div>

                <!-- Testimonials -->
                <div class="grid md:grid-cols-1 lg:grid-cols-3 gap-6">
                    <div v-for="(testimonial, i) in testimonials" :key="i"
                         class="testimonial-card border border-purple-400/25 p-6 md:p-8 relative"
                         style="background: linear-gradient(135deg, rgba(192,132,252,.06), rgba(192,132,252,.02)); backdrop-filter: blur(4px);">
                        <div class="absolute top-3 left-3 w-4 h-4 border-t border-l border-purple-400/40"></div>
                        <div class="absolute bottom-3 right-3 w-4 h-4 border-b border-r border-purple-400/40"></div>

                        <div class="text-[8px] tracking-[0.3em] text-purple-400/60 uppercase mb-2">{{ testimonial.agent }}</div>
                        <div class="text-[9px] tracking-[0.2em] text-purple-300/50 uppercase mb-4">{{ testimonial.role }}</div>
                        <p class="text-sm md:text-base text-purple-100/70 leading-relaxed font-medium">
                            "{{ testimonial.msg }}"
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 5 — NEXT MISSION BRIEFING
        ══════════════════════════════════════════════════════════ -->
        <section class="relative py-28 md:py-40 px-4 overflow-hidden">
            <div class="absolute inset-0" style="background: linear-gradient(180deg, #0a0520 0%, #040812 50%, #0a0520 100%);"></div>
            <div class="absolute inset-0 pointer-events-none"
                 style="background: radial-gradient(ellipse at center, rgba(251,191,36,.08) 0%, transparent 60%); opacity: .25;">
            </div>

            <div class="relative z-10 max-w-3xl mx-auto text-center">
                <div class="text-[9px] tracking-[0.48em] text-amber-400/55 mb-6 uppercase">// NEXT MISSION BRIEFING //</div>
                <h2 class="future-title text-2xl md:text-4xl font-black text-amber-300 mb-14 tracking-wider uppercase"
                    style="text-shadow: 0 0 30px rgba(251,191,36,.4);">
                    The Real Mission Begins
                </h2>

                <div class="space-y-8">
                    <div v-for="(msg, i) in futureMessages" :key="i"
                         class="future-msg">
                        <p class="text-lg md:text-2xl font-bold text-white leading-relaxed"
                           style="text-shadow: 0 0 20px rgba(251,191,36,.35);">
                            {{ msg }}
                        </p>
                    </div>
                </div>

                <div class="mt-16 pt-8 border-t border-amber-400/20">
                    <p class="text-sm md:text-base text-amber-200/60 tracking-[0.25em] uppercase">
                        This degree is not your destination. It is your toolkit.
                    </p>
                    <p class="text-sm md:text-base text-amber-200/60 tracking-[0.25em] uppercase mt-3">
                        Every person you help. Every life you touch. Every struggle you ease.
                    </p>
                    <p class="text-base md:text-lg text-amber-300 font-bold tracking-[0.2em] uppercase mt-6"
                       style="text-shadow: 0 0 15px rgba(251,191,36,.5);">
                        That is your mission.
                    </p>
                </div>
            </div>
        </section>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 6 — CLOSING
        ══════════════════════════════════════════════════════════ -->
        <section class="closing-section relative min-h-screen flex flex-col items-center justify-center overflow-hidden px-4 py-20">
            <div class="absolute inset-0" style="background: linear-gradient(135deg, #040812 0%, #0d0810 50%, #040812 100%);"></div>

            <!-- Closing particles -->
            <div v-for="i in 8" :key="i"
                 class="closing-particle absolute w-2 h-2 rounded-full opacity-0 scale-0"
                 :style="`background: linear-gradient(135deg, #fbbf24, #34d399); bottom: ${15 + i * 8}%; left: ${12 + i * 10}%;`">
            </div>

            <div class="relative z-10 text-center max-w-2xl mx-auto">
                <div class="closing-badge mb-8 opacity-0">
                    <div class="w-40 h-40 md:w-56 md:h-56 mx-auto rounded-full border-4 border-amber-400/60 flex items-center justify-center relative"
                         style="background: radial-gradient(circle, rgba(251,191,36,.12) 0%, rgba(5,10,26,.9) 60%); box-shadow: 0 0 60px rgba(251,191,36,.35), inset 0 0 40px rgba(251,191,36,.1);">
                        <div class="text-center">
                            <div class="text-5xl mb-2">🎓</div>
                            <div class="text-[9px] md:text-[10px] tracking-[0.3em] text-amber-300 font-black mb-1">SOCIAL WORK</div>
                            <div class="text-[10px] md:text-[12px] tracking-[0.2em] text-amber-300 font-black">BACHELOR DEGREE</div>
                            <div class="text-[8px] md:text-[9px] tracking-[0.35em] text-green-400 font-bold mt-3">MISSION APPROVED</div>
                        </div>
                    </div>
                </div>

                <div class="closing-text opacity-0">
                    <h2 class="text-3xl md:text-5xl font-black text-white mb-4 tracking-wider"
                        style="text-shadow: 0 0 30px rgba(255,255,255,.3);">
                        Congratulations
                    </h2>
                    <p class="text-xl md:text-3xl font-bold text-amber-300 mb-2"
                       style="text-shadow: 0 0 20px rgba(251,191,36,.6);">
                        Amira Reyshi Cantado
                    </p>
                    <p class="text-base md:text-lg text-white/70 tracking-[0.2em] mt-8 mb-12">
                        Your journey as a social worker begins now.
                    </p>
                    <p class="text-lg md:text-2xl font-black text-green-400 tracking-wider"
                       style="text-shadow: 0 0 15px rgba(34,197,94,.7);">
                        Go change the world,<br>Agent.
                    </p>
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
