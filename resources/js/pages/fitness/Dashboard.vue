<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { Chart, DoughnutController, ArcElement, LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip, Legend } from 'chart.js'
import gsap from 'gsap'

Chart.register(DoughnutController, ArcElement, LineController, LineElement, PointElement, LinearScale, CategoryScale, Filler, Tooltip, Legend)

interface Log {
    log_date: string
    calories_consumed: number | null
    weight_kg: number | null
    workout_completed: boolean
    notes: string | null
}
interface Profile {
    bmi: number
    calorie_target: number
    goal: string
    program_type: string
    activity_level: string
    gender: string
    weight_kg: number
    height_cm: number
    age: number
}

const props = defineProps<{
    profile: Profile
    logs: Log[]
    todayLog: Log | null
    userName: string
}>()

// log form
const logForm = useForm({
    calories_consumed: props.todayLog?.calories_consumed ?? '',
    weight_kg:         props.todayLog?.weight_kg ?? '',
    workout_completed: props.todayLog?.workout_completed ?? false,
    notes:             props.todayLog?.notes ?? '',
})
function submitLog() { logForm.post('/fitness/log') }

// BMI helpers
const bmiCategory = computed(() => {
    const b = props.profile.bmi
    if (b < 18.5) return { label: 'Underweight', color: '#60a5fa', pct: Math.round((b / 40) * 100) }
    if (b < 25)   return { label: 'Normal',      color: '#34d399', pct: Math.round((b / 40) * 100) }
    if (b < 30)   return { label: 'Overweight',  color: '#fbbf24', pct: Math.round((b / 40) * 100) }
    return               { label: 'Obese',        color: '#f87171', pct: Math.round(Math.min(b / 40, 1) * 100) }
})

const goalLabel: Record<string, string> = {
    lose_weight: '🔥 Lose Weight', maintain: '⚖️ Maintain', gain_muscle: '💪 Gain Muscle',
}
const programLabel: Record<string, string> = {
    PPL: 'Push / Pull / Legs', upper_lower: 'Upper / Lower',
}

// calorie doughnut
const calorieCanvas = ref<HTMLCanvasElement | null>(null)
const consumed = computed(() => props.todayLog?.calories_consumed ?? 0)
const remaining = computed(() => Math.max(0, props.profile.calorie_target - consumed.value))
const caloriePct = computed(() => Math.min(100, Math.round((consumed.value / props.profile.calorie_target) * 100)))

// weight trend
const weightCanvas = ref<HTMLCanvasElement | null>(null)

// recent logs (reverse for chart: oldest first)
const chartLogs = computed(() => [...props.logs].reverse().slice(-10))

onMounted(() => {
    // calorie ring
    if (calorieCanvas.value) {
        new Chart(calorieCanvas.value, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [consumed.value, remaining.value],
                    backgroundColor: ['#34d399', '#1a2d4a'],
                    borderWidth: 0,
                    circumference: 270,
                    rotation: 225,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '78%',
                animation: { duration: 1000 },
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
            },
        })
    }

    // weight trend
    const wLogs = chartLogs.value.filter(l => l.weight_kg !== null)
    if (weightCanvas.value && wLogs.length > 1) {
        new Chart(weightCanvas.value, {
            type: 'line',
            data: {
                labels: wLogs.map(l => new Date(l.log_date).toLocaleDateString([], { month: 'short', day: 'numeric' })),
                datasets: [{
                    data: wLogs.map(l => l.weight_kg),
                    borderColor: '#34d399',
                    backgroundColor: 'rgba(52,211,153,0.08)',
                    borderWidth: 2, pointRadius: 4,
                    pointBackgroundColor: '#34d399', fill: true, tension: 0.4,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { color: '#1a2d4a' }, ticks: { color: '#475569', font: { size: 11 } } },
                    y: { grid: { color: '#1a2d4a' }, ticks: { color: '#475569', font: { size: 11 } } },
                },
            },
        })
    }

    // GSAP
    gsap.from('.fit-db-stat', { opacity: 0, y: 24, duration: 0.5, stagger: 0.1, ease: 'power3.out' })
    gsap.from('.fit-db-card', { opacity: 0, y: 28, duration: 0.5, stagger: 0.12, delay: 0.3, ease: 'power3.out' })
})
</script>

<template>
    <Head title="FitTrack — Dashboard" />

    <div class="fit-db-page">

        <!-- TOP NAV -->
        <nav class="fit-db-nav">
            <Link href="/fitness" class="fit-logo">⚡ FitTrack</Link>
            <div class="fit-db-nav-links">
                <Link href="/fitness/schedule" class="fit-db-nav-link">Schedule</Link>
                <Link href="/fitness/onboarding" class="fit-db-nav-link">Edit Profile</Link>
                <Link href="/logout" method="post" as="button" class="fit-db-nav-btn">Log out</Link>
            </div>
        </nav>

        <main class="fit-db-main">

            <!-- greeting -->
            <div class="fit-db-greeting">
                <h1>Hey, {{ userName.split(' ')[0] }} 👋</h1>
                <p>{{ new Date().toLocaleDateString([], { weekday: 'long', month: 'long', day: 'numeric' }) }}</p>
            </div>

            <!-- STAT STRIP -->
            <div class="fit-db-stats">
                <div class="fit-db-stat">
                    <div class="fit-db-stat-label">BMI</div>
                    <div class="fit-db-stat-value" :style="{ color: bmiCategory.color }">{{ profile.bmi }}</div>
                    <div class="fit-db-stat-sub" :style="{ color: bmiCategory.color }">{{ bmiCategory.label }}</div>
                </div>
                <div class="fit-db-stat">
                    <div class="fit-db-stat-label">Calorie Target</div>
                    <div class="fit-db-stat-value" style="color:#34d399">{{ profile.calorie_target.toLocaleString() }}</div>
                    <div class="fit-db-stat-sub">kcal / day</div>
                </div>
                <div class="fit-db-stat">
                    <div class="fit-db-stat-label">Program</div>
                    <div class="fit-db-stat-value" style="font-size:1rem">{{ programLabel[profile.program_type] }}</div>
                    <div class="fit-db-stat-sub">{{ goalLabel[profile.goal] }}</div>
                </div>
                <div class="fit-db-stat">
                    <div class="fit-db-stat-label">Streak</div>
                    <div class="fit-db-stat-value" style="color:#f59e0b">{{ logs.filter(l => l.workout_completed).length }}</div>
                    <div class="fit-db-stat-sub">workouts logged</div>
                </div>
            </div>

            <!-- MAIN GRID -->
            <div class="fit-db-grid">

                <!-- Calorie ring -->
                <div class="fit-db-card fit-db-calorie-card">
                    <h3>Today's Calories</h3>
                    <div class="fit-db-ring-wrap">
                        <canvas ref="calorieCanvas" height="200"></canvas>
                        <div class="fit-db-ring-center">
                            <span class="fit-db-ring-pct">{{ caloriePct }}%</span>
                            <span class="fit-db-ring-label">of target</span>
                        </div>
                    </div>
                    <div class="fit-db-calorie-row">
                        <div><div class="fit-dot green"></div>Consumed: <strong>{{ consumed.toLocaleString() }} kcal</strong></div>
                        <div><div class="fit-dot gray"></div>Remaining: <strong>{{ remaining.toLocaleString() }} kcal</strong></div>
                    </div>
                </div>

                <!-- Log form -->
                <div class="fit-db-card">
                    <h3>📝 Log Today</h3>
                    <form @submit.prevent="submitLog" class="fit-db-log-form">
                        <div class="fit-db-log-field">
                            <label>Calories consumed (kcal)</label>
                            <input v-model="logForm.calories_consumed" type="number" min="0" max="9999" placeholder="e.g. 1850" />
                        </div>
                        <div class="fit-db-log-field">
                            <label>Weight check-in (kg)</label>
                            <input v-model="logForm.weight_kg" type="number" step="0.1" min="30" max="300" placeholder="optional" />
                        </div>
                        <label class="fit-db-checkbox">
                            <input v-model="logForm.workout_completed" type="checkbox" />
                            <span>✅ Workout completed today</span>
                        </label>
                        <div class="fit-db-log-field">
                            <label>Notes</label>
                            <input v-model="logForm.notes" type="text" maxlength="500" placeholder="How did it go?" />
                        </div>
                        <button type="submit" class="fit-btn-solid" :disabled="logForm.processing">
                            {{ logForm.processing ? 'Saving…' : (todayLog ? 'Update log' : 'Save log') }}
                        </button>
                    </form>
                </div>

                <!-- Weight trend -->
                <div class="fit-db-card fit-db-wide">
                    <h3>📈 Weight Trend (last 10 days)</h3>
                    <div v-if="logs.filter(l => l.weight_kg !== null).length > 1" class="fit-db-chart-wrap">
                        <canvas ref="weightCanvas"></canvas>
                    </div>
                    <p v-else class="fit-db-empty">Log your weight daily to see the trend here.</p>
                </div>

                <!-- Recent log table -->
                <div class="fit-db-card fit-db-wide">
                    <h3>🗓 Recent Activity</h3>
                    <div v-if="logs.length" class="fit-db-table-wrap">
                        <table class="fit-db-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Calories</th>
                                    <th>Weight</th>
                                    <th>Workout</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in logs" :key="log.log_date">
                                    <td>{{ new Date(log.log_date).toLocaleDateString([], { month: 'short', day: 'numeric' }) }}</td>
                                    <td>{{ log.calories_consumed ? log.calories_consumed + ' kcal' : '—' }}</td>
                                    <td>{{ log.weight_kg ? log.weight_kg + ' kg' : '—' }}</td>
                                    <td>{{ log.workout_completed ? '✅' : '—' }}</td>
                                    <td class="fit-td-notes">{{ log.notes ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p v-else class="fit-db-empty">No logs yet. Start logging today!</p>
                </div>

            </div>
        </main>
    </div>
</template>

<style scoped>
.fit-db-page {
    min-height: 100vh;
    background: #080f1e;
    color: #e2e8f0;
    font-family: 'Segoe UI', system-ui, sans-serif;
}

/* nav */
.fit-db-nav {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 32px; border-bottom: 1px solid #1a2d4a; position: sticky; top: 0;
    background: rgba(8,15,30,0.95); backdrop-filter: blur(8px); z-index: 10;
}
.fit-logo { font-size: 1.1rem; font-weight: 700; color: #34d399; text-decoration: none; }
.fit-db-nav-links { display: flex; gap: 12px; align-items: center; }
.fit-db-nav-link  { color: #94a3b8; font-size: 0.9rem; text-decoration: none; transition: color 0.2s; }
.fit-db-nav-link:hover { color: #34d399; }
.fit-db-nav-btn {
    padding: 6px 16px; background: transparent; border: 1px solid #2d4a6e;
    border-radius: 8px; color: #64748b; font-size: 0.85rem; cursor: pointer;
    transition: border-color 0.2s; font-family: inherit;
}
.fit-db-nav-btn:hover { border-color: #f87171; color: #f87171; }

/* main */
.fit-db-main { max-width: 1100px; margin: 0 auto; padding: 32px 24px 60px; }
.fit-db-greeting { margin-bottom: 28px; }
.fit-db-greeting h1 { font-size: 1.8rem; font-weight: 700; margin-bottom: 4px; }
.fit-db-greeting p  { color: #475569; font-size: 0.9rem; }

/* stats strip */
.fit-db-stats {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 28px;
}
.fit-db-stat {
    background: #0d1a2e; border: 1px solid #1a2d4a; border-radius: 14px;
    padding: 18px 16px; text-align: center;
}
.fit-db-stat-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: #475569; margin-bottom: 8px; }
.fit-db-stat-value { font-size: 1.7rem; font-weight: 700; line-height: 1; }
.fit-db-stat-sub   { font-size: 0.8rem; color: #475569; margin-top: 4px; }

/* grid */
.fit-db-grid {
    display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;
}
.fit-db-wide { grid-column: 1 / -1; }

/* card */
.fit-db-card {
    background: #0d1a2e; border: 1px solid #1a2d4a; border-radius: 16px; padding: 24px;
}
.fit-db-card h3 { font-size: 0.95rem; font-weight: 600; color: #94a3b8; margin-bottom: 20px; }

/* calorie ring */
.fit-db-ring-wrap { position: relative; height: 200px; margin-bottom: 16px; }
.fit-db-ring-center {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
    text-align: center; pointer-events: none;
}
.fit-db-ring-pct   { display: block; font-size: 2rem; font-weight: 700; color: #34d399; }
.fit-db-ring-label { display: block; font-size: 0.75rem; color: #475569; }
.fit-db-calorie-row { display: flex; flex-direction: column; gap: 6px; font-size: 0.85rem; color: #64748b; }
.fit-db-calorie-row div { display: flex; align-items: center; gap: 8px; }
.fit-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.fit-dot.green { background: #34d399; }
.fit-dot.gray  { background: #1a2d4a; border: 1px solid #2d4a6e; }

/* log form */
.fit-db-log-form { display: flex; flex-direction: column; gap: 14px; }
.fit-db-log-field { display: flex; flex-direction: column; gap: 6px; }
.fit-db-log-field label { font-size: 0.78rem; color: #64748b; }
.fit-db-log-field input {
    background: #0a1525; border: 1px solid #1e3a5f; border-radius: 8px;
    color: #e2e8f0; padding: 10px 12px; font-size: 0.9rem; outline: none; transition: border-color 0.2s;
}
.fit-db-log-field input:focus { border-color: #34d399; }
.fit-db-checkbox { display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.9rem; color: #94a3b8; }
.fit-db-checkbox input { accent-color: #34d399; width: 16px; height: 16px; }
.fit-btn-solid {
    padding: 12px; background: #34d399; border: none; border-radius: 10px;
    color: #080f1e; font-weight: 700; font-size: 0.95rem; cursor: pointer;
    transition: background 0.2s; font-family: inherit;
}
.fit-btn-solid:hover    { background: #10b981; }
.fit-btn-solid:disabled { opacity: 0.4; cursor: not-allowed; }

/* chart */
.fit-db-chart-wrap { height: 180px; position: relative; }
.fit-db-empty { color: #334155; font-size: 0.9rem; text-align: center; padding: 40px 0; }

/* table */
.fit-db-table-wrap { overflow-x: auto; }
.fit-db-table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
.fit-db-table th {
    text-align: left; padding: 10px 12px; font-size: 0.72rem;
    text-transform: uppercase; letter-spacing: 0.8px; color: #475569;
    border-bottom: 1px solid #1a2d4a;
}
.fit-db-table td { padding: 10px 12px; border-bottom: 1px solid #0f2038; color: #94a3b8; }
.fit-db-table tr:last-child td { border-bottom: none; }
.fit-td-notes { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

@media (max-width: 700px) {
    .fit-db-grid { grid-template-columns: 1fr; }
    .fit-db-nav { padding: 12px 16px; }
}
</style>
