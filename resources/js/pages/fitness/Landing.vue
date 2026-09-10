<script setup lang="ts">
import { onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import gsap from 'gsap'

onMounted(() => {
    gsap.from('#fit-hero h1', { opacity: 0, y: -40, duration: 0.8, ease: 'power3.out' })
    gsap.from('#fit-hero p',  { opacity: 0, y: -20, duration: 0.7, delay: 0.2, ease: 'power3.out' })
    gsap.from('#fit-cta',     { opacity: 0, y: 20,  duration: 0.7, delay: 0.4, ease: 'power3.out' })
    gsap.from('.fit-feature-card', {
        opacity: 0, y: 40, duration: 0.6, stagger: 0.15, delay: 0.6, ease: 'back.out(1.3)',
    })
})
</script>

<template>
    <Head title="Fitness Tracker" />

    <div class="fit-landing">

        <!-- NAV -->
        <nav class="fit-nav">
            <span class="fit-logo">⚡ FitTrack</span>
            <div class="fit-nav-links">
                <Link href="/login" class="fit-btn-ghost">Log in</Link>
                <Link href="/register" class="fit-btn-solid">Get started</Link>
            </div>
        </nav>

        <!-- HERO -->
        <section id="fit-hero" class="fit-hero">
            <div class="fit-badge">Your personal fitness companion</div>
            <h1>Train smarter.<br><span class="fit-accent">Live stronger.</span></h1>
            <p>
                Track your BMI, hit your calorie targets, and follow a structured
                training program — all in one place.
            </p>
            <div id="fit-cta" class="fit-cta">
                <Link href="/register" class="fit-btn-solid fit-btn-lg">Start for free →</Link>
                <Link href="/login" class="fit-btn-ghost fit-btn-lg">I already have an account</Link>
            </div>
        </section>

        <!-- FEATURES -->
        <section class="fit-features">
            <div class="fit-feature-card">
                <div class="fit-feature-icon">📊</div>
                <h3>BMI & Calorie Targets</h3>
                <p>Answer a few questions and we calculate your personalised BMI and daily calorie goal using the Mifflin-St Jeor formula.</p>
            </div>
            <div class="fit-feature-card">
                <div class="fit-feature-icon">📅</div>
                <h3>Structured Programs</h3>
                <p>Choose Push/Pull/Legs (6 days) or Upper/Lower (4 days). We generate a full weekly schedule with exercises, sets, and reps.</p>
            </div>
            <div class="fit-feature-card">
                <div class="fit-feature-icon">📈</div>
                <h3>Daily Progress Log</h3>
                <p>Log calories consumed, today's weight, and whether you completed your workout. Track trends over 14 days on your dashboard.</p>
            </div>
        </section>

        <footer class="fit-footer">
            © {{ new Date().getFullYear() }} FitTrack · baconologies.com
        </footer>
    </div>
</template>

<style scoped>
.fit-landing {
    min-height: 100vh;
    background: #080f1e;
    color: #e2e8f0;
    font-family: 'Segoe UI', system-ui, sans-serif;
    display: flex;
    flex-direction: column;
}

/* nav */
.fit-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 40px;
    border-bottom: 1px solid #1a2d4a;
}
.fit-logo { font-size: 1.2rem; font-weight: 700; color: #34d399; letter-spacing: -0.3px; }
.fit-nav-links { display: flex; gap: 12px; align-items: center; }

/* buttons */
.fit-btn-ghost {
    padding: 8px 18px;
    border: 1px solid #2d4a6e;
    border-radius: 8px;
    color: #94a3b8;
    font-size: 0.9rem;
    text-decoration: none;
    transition: border-color 0.2s, color 0.2s;
}
.fit-btn-ghost:hover { border-color: #34d399; color: #34d399; }
.fit-btn-solid {
    padding: 8px 18px;
    background: #34d399;
    border-radius: 8px;
    color: #080f1e;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s;
}
.fit-btn-solid:hover { background: #10b981; }
.fit-btn-lg { padding: 14px 28px; font-size: 1rem; border-radius: 10px; }

/* hero */
.fit-hero {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 80px 24px 60px;
}
.fit-badge {
    display: inline-block;
    background: rgba(52,211,153,0.1);
    border: 1px solid rgba(52,211,153,0.3);
    color: #34d399;
    font-size: 0.8rem;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    padding: 6px 16px;
    border-radius: 100px;
    margin-bottom: 24px;
}
.fit-hero h1 {
    font-size: clamp(2.4rem, 6vw, 4rem);
    font-weight: 800;
    line-height: 1.1;
    letter-spacing: -1px;
    margin-bottom: 20px;
}
.fit-accent { color: #34d399; }
.fit-hero p {
    font-size: 1.1rem;
    color: #94a3b8;
    max-width: 480px;
    line-height: 1.6;
    margin-bottom: 36px;
}
.fit-cta { display: flex; gap: 14px; flex-wrap: wrap; justify-content: center; }

/* features */
.fit-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
    padding: 0 40px 80px;
    max-width: 1100px;
    margin: 0 auto;
    width: 100%;
}
.fit-feature-card {
    background: #0d1a2e;
    border: 1px solid #1a2d4a;
    border-radius: 16px;
    padding: 28px 24px;
    transition: border-color 0.2s, transform 0.2s;
}
.fit-feature-card:hover { border-color: #34d399; transform: translateY(-4px); }
.fit-feature-icon { font-size: 2rem; margin-bottom: 14px; }
.fit-feature-card h3 { font-size: 1.05rem; font-weight: 700; margin-bottom: 10px; }
.fit-feature-card p  { font-size: 0.88rem; color: #64748b; line-height: 1.6; }

.fit-footer { text-align: center; padding: 24px; color: #334155; font-size: 0.8rem; }
</style>
