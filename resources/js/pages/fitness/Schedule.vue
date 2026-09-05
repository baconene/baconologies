<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import gsap from 'gsap'

const props = defineProps<{
    programType: 'PPL' | 'upper_lower'
    goal: string
}>()

interface Exercise {
    name: string
    sets: number
    reps: string
    muscle: string
}
interface Day {
    label: string
    tag: string
    color: string
    rest: boolean
    exercises: Exercise[]
}

// ── PPL Program ───────────────────────────────────────────────────────
const PPL: Day[] = [
    {
        label: 'Day 1', tag: 'Push A', color: '#f97316', rest: false,
        exercises: [
            { name: 'Barbell Bench Press',       sets: 4, reps: '6–8',   muscle: 'Chest' },
            { name: 'Overhead Press (OHP)',       sets: 3, reps: '8–10',  muscle: 'Shoulders' },
            { name: 'Incline DB Press',           sets: 3, reps: '10–12', muscle: 'Upper Chest' },
            { name: 'Cable Lateral Raise',        sets: 4, reps: '12–15', muscle: 'Shoulders' },
            { name: 'Tricep Rope Pushdown',       sets: 3, reps: '12–15', muscle: 'Triceps' },
            { name: 'Skull Crushers',             sets: 3, reps: '10–12', muscle: 'Triceps' },
        ],
    },
    {
        label: 'Day 2', tag: 'Pull A', color: '#3b82f6', rest: false,
        exercises: [
            { name: 'Barbell Bent-Over Row',      sets: 4, reps: '6–8',   muscle: 'Back' },
            { name: 'Pull-ups',                   sets: 4, reps: 'AMRAP', muscle: 'Lats' },
            { name: 'Seated Cable Row',           sets: 3, reps: '10–12', muscle: 'Back' },
            { name: 'Face Pulls',                 sets: 4, reps: '15–20', muscle: 'Rear Delts' },
            { name: 'Barbell Curl',               sets: 3, reps: '10–12', muscle: 'Biceps' },
            { name: 'Hammer Curls',               sets: 3, reps: '12–15', muscle: 'Biceps' },
        ],
    },
    {
        label: 'Day 3', tag: 'Legs A', color: '#8b5cf6', rest: false,
        exercises: [
            { name: 'Back Squat',                 sets: 4, reps: '5–8',   muscle: 'Quads / Glutes' },
            { name: 'Romanian Deadlift',          sets: 3, reps: '8–10',  muscle: 'Hamstrings' },
            { name: 'Leg Press',                  sets: 3, reps: '12–15', muscle: 'Quads' },
            { name: 'Seated Leg Curl',            sets: 3, reps: '12–15', muscle: 'Hamstrings' },
            { name: 'Standing Calf Raise',        sets: 4, reps: '15–20', muscle: 'Calves' },
        ],
    },
    {
        label: 'Day 4', tag: 'Push B', color: '#f97316', rest: false,
        exercises: [
            { name: 'Incline Barbell Press',      sets: 4, reps: '6–8',   muscle: 'Upper Chest' },
            { name: 'Cable Chest Fly',            sets: 4, reps: '12–15', muscle: 'Chest' },
            { name: 'DB Shoulder Press',          sets: 3, reps: '10–12', muscle: 'Shoulders' },
            { name: 'Lateral Raise',              sets: 3, reps: '15–20', muscle: 'Shoulders' },
            { name: 'Close-Grip Bench Press',     sets: 3, reps: '8–10',  muscle: 'Triceps' },
            { name: 'Tricep Dips',                sets: 3, reps: 'AMRAP', muscle: 'Triceps' },
        ],
    },
    {
        label: 'Day 5', tag: 'Pull B', color: '#3b82f6', rest: false,
        exercises: [
            { name: 'Deadlift',                   sets: 4, reps: '4–6',   muscle: 'Full Back' },
            { name: 'Lat Pulldown',               sets: 4, reps: '10–12', muscle: 'Lats' },
            { name: 'T-Bar Row',                  sets: 3, reps: '8–10',  muscle: 'Back' },
            { name: 'Rear Delt Fly',              sets: 4, reps: '15–20', muscle: 'Rear Delts' },
            { name: 'Preacher Curl',              sets: 3, reps: '10–12', muscle: 'Biceps' },
            { name: 'Cable Curl',                 sets: 3, reps: '12–15', muscle: 'Biceps' },
        ],
    },
    {
        label: 'Day 6', tag: 'Legs B', color: '#8b5cf6', rest: false,
        exercises: [
            { name: 'Front Squat',                sets: 4, reps: '5–8',   muscle: 'Quads' },
            { name: 'Sumo Deadlift',              sets: 3, reps: '6–8',   muscle: 'Glutes / Hamstrings' },
            { name: 'Hack Squat',                 sets: 3, reps: '10–12', muscle: 'Quads' },
            { name: 'Leg Extension',              sets: 3, reps: '15–20', muscle: 'Quads' },
            { name: 'Seated Calf Raise',          sets: 4, reps: '15–20', muscle: 'Calves' },
        ],
    },
    { label: 'Day 7', tag: 'Rest', color: '#334155', rest: true, exercises: [] },
]

// ── Upper/Lower Program ───────────────────────────────────────────────
const UPPER_LOWER: Day[] = [
    {
        label: 'Day 1', tag: 'Upper A', color: '#f97316', rest: false,
        exercises: [
            { name: 'Barbell Bench Press',        sets: 4, reps: '5–6',   muscle: 'Chest' },
            { name: 'Barbell Bent-Over Row',      sets: 4, reps: '5–6',   muscle: 'Back' },
            { name: 'Overhead Press',             sets: 3, reps: '8–10',  muscle: 'Shoulders' },
            { name: 'Pull-ups',                   sets: 3, reps: 'AMRAP', muscle: 'Lats' },
            { name: 'Incline DB Press',           sets: 3, reps: '10–12', muscle: 'Chest' },
            { name: 'Barbell Curl',               sets: 3, reps: '10–12', muscle: 'Biceps' },
            { name: 'Tricep Pushdown',            sets: 3, reps: '12–15', muscle: 'Triceps' },
        ],
    },
    {
        label: 'Day 2', tag: 'Lower A', color: '#8b5cf6', rest: false,
        exercises: [
            { name: 'Back Squat',                 sets: 4, reps: '5–6',   muscle: 'Quads / Glutes' },
            { name: 'Romanian Deadlift',          sets: 4, reps: '6–8',   muscle: 'Hamstrings' },
            { name: 'Leg Press',                  sets: 3, reps: '12–15', muscle: 'Quads' },
            { name: 'Seated Leg Curl',            sets: 3, reps: '12–15', muscle: 'Hamstrings' },
            { name: 'Calf Raises',                sets: 4, reps: '15–20', muscle: 'Calves' },
        ],
    },
    { label: 'Day 3', tag: 'Rest', color: '#334155', rest: true, exercises: [] },
    {
        label: 'Day 4', tag: 'Upper B', color: '#f97316', rest: false,
        exercises: [
            { name: 'Incline Bench Press',        sets: 4, reps: '8–10',  muscle: 'Upper Chest' },
            { name: 'T-Bar Row',                  sets: 4, reps: '8–10',  muscle: 'Back' },
            { name: 'DB Shoulder Press',          sets: 3, reps: '10–12', muscle: 'Shoulders' },
            { name: 'Lat Pulldown',               sets: 3, reps: '10–12', muscle: 'Lats' },
            { name: 'Cable Fly',                  sets: 3, reps: '12–15', muscle: 'Chest' },
            { name: 'Face Pulls',                 sets: 3, reps: '15–20', muscle: 'Rear Delts' },
            { name: 'Close-Grip Bench',           sets: 3, reps: '8–10',  muscle: 'Triceps' },
        ],
    },
    {
        label: 'Day 5', tag: 'Lower B', color: '#8b5cf6', rest: false,
        exercises: [
            { name: 'Deadlift',                   sets: 4, reps: '4–5',   muscle: 'Full Back / Glutes' },
            { name: 'Front Squat',                sets: 3, reps: '6–8',   muscle: 'Quads' },
            { name: 'Hack Squat',                 sets: 3, reps: '10–12', muscle: 'Quads' },
            { name: 'Leg Extension',              sets: 3, reps: '15–20', muscle: 'Quads' },
            { name: 'Seated Calf Raise',          sets: 4, reps: '15–20', muscle: 'Calves' },
        ],
    },
    { label: 'Day 6', tag: 'Rest', color: '#334155', rest: true, exercises: [] },
    { label: 'Day 7', tag: 'Rest', color: '#334155', rest: true, exercises: [] },
]

const schedule = computed(() => props.programType === 'PPL' ? PPL : UPPER_LOWER)

// highlight today's day (0=Monday offset)
const todayIndex = computed(() => {
    const d = new Date().getDay() // 0=Sun
    return d === 0 ? 6 : d - 1   // Mon=0 … Sun=6
})

const activeDay = ref(todayIndex.value < schedule.value.length ? todayIndex.value : 0)

const muscleColor: Record<string, string> = {
    Chest: '#f97316', 'Upper Chest': '#fb923c', Shoulders: '#facc15',
    Triceps: '#f87171', Back: '#3b82f6', Lats: '#60a5fa',
    'Rear Delts': '#93c5fd', Biceps: '#34d399', 'Quads / Glutes': '#8b5cf6',
    Quads: '#a78bfa', Hamstrings: '#c084fc', 'Glutes / Hamstrings': '#c084fc',
    'Full Back': '#3b82f6', 'Full Back / Glutes': '#818cf8', Calves: '#6ee7b7',
}

const goalLabel: Record<string, string> = {
    lose_weight: '🔥 Lose Weight', maintain: '⚖️ Maintain', gain_muscle: '💪 Gain Muscle',
}

onMounted(() => {
    gsap.from('.fit-sc-header', { opacity: 0, y: -20, duration: 0.6, ease: 'power3.out' })
    gsap.from('.fit-sc-day-tab', { opacity: 0, y: 10, duration: 0.4, stagger: 0.05, delay: 0.2 })
    gsap.from('.fit-sc-detail',  { opacity: 0, x: 20, duration: 0.5, delay: 0.4 })
})

function selectDay(i: number) {
    gsap.to('.fit-sc-detail', { opacity: 0, x: -20, duration: 0.2, onComplete: () => {
        activeDay.value = i
        gsap.fromTo('.fit-sc-detail', { opacity: 0, x: 20 }, { opacity: 1, x: 0, duration: 0.3 })
    }})
}
</script>

<template>
    <Head title="FitTrack — Schedule" />

    <div class="fit-sc-page">

        <!-- NAV -->
        <nav class="fit-db-nav">
            <Link href="/fitness" class="fit-logo">⚡ FitTrack</Link>
            <div style="display:flex;gap:12px;align-items:center">
                <Link href="/fitness/dashboard" class="fit-db-nav-link">Dashboard</Link>
                <Link href="/logout" method="post" as="button" class="fit-db-nav-btn">Log out</Link>
            </div>
        </nav>

        <main class="fit-sc-main">

            <!-- header -->
            <div class="fit-sc-header">
                <div>
                    <h1>{{ programType === 'PPL' ? 'Push / Pull / Legs' : 'Upper / Lower' }} Program</h1>
                    <p>{{ goalLabel[goal] }} · {{ programType === 'PPL' ? '6' : '4' }} training days / week</p>
                </div>
                <Link href="/fitness/dashboard" class="fit-btn-back">← Dashboard</Link>
            </div>

            <!-- day tabs -->
            <div class="fit-sc-tabs">
                <button
                    v-for="(day, i) in schedule" :key="i"
                    :class="['fit-sc-day-tab', { active: i === activeDay, today: i === todayIndex, rest: day.rest }]"
                    :style="i === activeDay ? { borderColor: day.color, color: day.color } : {}"
                    @click="selectDay(i)"
                >
                    <span class="fit-sc-tab-label">{{ day.label }}</span>
                    <span class="fit-sc-tab-tag">{{ day.tag }}</span>
                    <span v-if="i === todayIndex" class="fit-sc-today-pip"></span>
                </button>
            </div>

            <!-- day detail -->
            <div class="fit-sc-detail">
                <div class="fit-sc-day-header">
                    <div>
                        <span class="fit-sc-day-badge" :style="{ background: schedule[activeDay].color + '20', color: schedule[activeDay].color, borderColor: schedule[activeDay].color + '40' }">
                            {{ schedule[activeDay].tag }}
                        </span>
                        <h2>{{ schedule[activeDay].label }}</h2>
                    </div>
                    <div v-if="activeDay === todayIndex" class="fit-sc-today-label">📍 Today</div>
                </div>

                <!-- rest day -->
                <div v-if="schedule[activeDay].rest" class="fit-sc-rest">
                    <div class="fit-sc-rest-icon">😴</div>
                    <h3>Rest & Recovery</h3>
                    <p>Your muscles grow during rest. Stay hydrated, eat your protein, and sleep well.</p>
                    <ul class="fit-sc-rest-tips">
                        <li>🥩 Hit your protein target (0.8–1g per lb of bodyweight)</li>
                        <li>💧 Drink at least 2–3L of water</li>
                        <li>🧘 Light stretching or walking is fine</li>
                        <li>😴 Aim for 7–9 hours of sleep</li>
                    </ul>
                </div>

                <!-- exercise list -->
                <div v-else class="fit-sc-exercises">
                    <div
                        v-for="(ex, i) in schedule[activeDay].exercises"
                        :key="ex.name"
                        class="fit-sc-ex-row"
                    >
                        <div class="fit-sc-ex-num">{{ i + 1 }}</div>
                        <div class="fit-sc-ex-info">
                            <div class="fit-sc-ex-name">{{ ex.name }}</div>
                            <div class="fit-sc-ex-muscle" :style="{ color: muscleColor[ex.muscle] ?? '#94a3b8' }">
                                {{ ex.muscle }}
                            </div>
                        </div>
                        <div class="fit-sc-ex-detail">
                            <div class="fit-sc-ex-sets">{{ ex.sets }} sets</div>
                            <div class="fit-sc-ex-reps">{{ ex.reps }} reps</div>
                        </div>
                    </div>

                    <!-- tips by goal -->
                    <div class="fit-sc-tip">
                        <strong>💡 Tip:</strong>
                        <span v-if="goal === 'lose_weight'"> Keep rest between sets to 60–90 s and prioritise compound lifts to maximise calorie burn.</span>
                        <span v-else-if="goal === 'gain_muscle'"> Rest 2–3 min between heavy sets. Progressive overload — add weight or reps each week.</span>
                        <span v-else> Balance intensity and volume. Add weight when you hit the top of the rep range for all sets.</span>
                    </div>
                </div>
            </div>

        </main>
    </div>
</template>

<style scoped>
.fit-sc-page {
    min-height: 100vh;
    background: #080f1e;
    color: #e2e8f0;
    font-family: 'Segoe UI', system-ui, sans-serif;
}

/* shared nav (same as dashboard) */
.fit-db-nav {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 32px; border-bottom: 1px solid #1a2d4a;
    background: rgba(8,15,30,0.95); backdrop-filter: blur(8px);
    position: sticky; top: 0; z-index: 10;
}
.fit-logo         { font-size: 1.1rem; font-weight: 700; color: #34d399; text-decoration: none; }
.fit-db-nav-link  { color: #94a3b8; font-size: 0.9rem; text-decoration: none; }
.fit-db-nav-link:hover { color: #34d399; }
.fit-db-nav-btn {
    padding: 6px 16px; background: transparent; border: 1px solid #2d4a6e;
    border-radius: 8px; color: #64748b; font-size: 0.85rem; cursor: pointer;
    font-family: inherit;
}

.fit-sc-main { max-width: 900px; margin: 0 auto; padding: 32px 24px 80px; }

.fit-sc-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 28px;
}
.fit-sc-header h1 { font-size: 1.6rem; font-weight: 700; margin-bottom: 4px; }
.fit-sc-header p  { color: #475569; font-size: 0.9rem; }
.fit-btn-back {
    padding: 10px 20px; border: 1px solid #2d4a6e; border-radius: 10px;
    color: #94a3b8; font-size: 0.85rem; text-decoration: none; white-space: nowrap;
    transition: border-color 0.2s, color 0.2s;
}
.fit-btn-back:hover { border-color: #34d399; color: #34d399; }

/* tabs */
.fit-sc-tabs {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    margin-bottom: 24px;
}
.fit-sc-day-tab {
    position: relative;
    display: flex; flex-direction: column; align-items: center;
    gap: 4px; padding: 12px 6px;
    background: #0d1a2e; border: 1px solid #1a2d4a; border-radius: 12px;
    color: #475569; font-family: inherit; cursor: pointer; transition: all 0.2s;
    font-size: 0.75rem;
}
.fit-sc-day-tab:hover { border-color: #2d4a6e; color: #94a3b8; }
.fit-sc-day-tab.active { font-weight: 600; }
.fit-sc-day-tab.rest   { opacity: 0.5; }
.fit-sc-tab-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px; }
.fit-sc-tab-tag   { font-weight: 600; font-size: 0.78rem; }
.fit-sc-today-pip {
    position: absolute; top: 6px; right: 6px;
    width: 6px; height: 6px; border-radius: 50%; background: #34d399;
}

/* detail card */
.fit-sc-detail {
    background: #0d1a2e; border: 1px solid #1a2d4a; border-radius: 18px; padding: 28px 24px;
}
.fit-sc-day-header {
    display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;
}
.fit-sc-day-badge {
    display: inline-block; padding: 4px 12px; border-radius: 20px; border: 1px solid;
    font-size: 0.78rem; font-weight: 600; margin-bottom: 6px;
}
.fit-sc-day-header h2 { font-size: 1.2rem; font-weight: 700; }
.fit-sc-today-label { color: #34d399; font-size: 0.85rem; font-weight: 600; }

/* rest */
.fit-sc-rest { text-align: center; padding: 32px 0; }
.fit-sc-rest-icon { font-size: 3rem; margin-bottom: 14px; }
.fit-sc-rest h3 { font-size: 1.2rem; font-weight: 700; margin-bottom: 8px; }
.fit-sc-rest p  { color: #64748b; font-size: 0.9rem; margin-bottom: 24px; }
.fit-sc-rest-tips {
    list-style: none; display: inline-flex; flex-direction: column;
    gap: 10px; text-align: left;
}
.fit-sc-rest-tips li { font-size: 0.9rem; color: #94a3b8; }

/* exercises */
.fit-sc-exercises { display: flex; flex-direction: column; gap: 0; }
.fit-sc-ex-row {
    display: flex; align-items: center; gap: 16px;
    padding: 14px 0; border-bottom: 1px solid #0f2038;
}
.fit-sc-ex-row:last-of-type { border-bottom: none; }
.fit-sc-ex-num {
    width: 28px; height: 28px; border-radius: 50%;
    background: #0a1525; border: 1px solid #1e3a5f;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; color: #475569; flex-shrink: 0;
}
.fit-sc-ex-info { flex: 1; }
.fit-sc-ex-name   { font-size: 0.95rem; font-weight: 600; margin-bottom: 3px; }
.fit-sc-ex-muscle { font-size: 0.75rem; font-weight: 500; }
.fit-sc-ex-detail { text-align: right; flex-shrink: 0; }
.fit-sc-ex-sets { font-size: 0.95rem; font-weight: 700; color: #e2e8f0; }
.fit-sc-ex-reps { font-size: 0.78rem; color: #475569; margin-top: 2px; }

.fit-sc-tip {
    margin-top: 20px;
    background: rgba(52,211,153,0.05);
    border: 1px solid rgba(52,211,153,0.15);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 0.87rem;
    color: #64748b;
    line-height: 1.5;
}
.fit-sc-tip strong { color: #34d399; }

@media (max-width: 640px) {
    .fit-sc-tabs { grid-template-columns: repeat(4, 1fr); }
    .fit-sc-header { flex-direction: column; gap: 16px; }
}
</style>
