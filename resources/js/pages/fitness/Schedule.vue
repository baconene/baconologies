<script setup lang="ts">
import { ref, computed, onMounted, reactive, watch } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import gsap from 'gsap'

// ── props ──────────────────────────────────────────────────────────────
const props = defineProps<{
    programType: 'PPL' | 'upper_lower'
    goal: string
    customSchedule: DaySchedule[] | null
}>()

// ── types ──────────────────────────────────────────────────────────────
interface Exercise {
    name: string
    sets: number
    reps: string
    muscle: string
}
interface DaySchedule {
    label: string
    tag: string
    color: string
    rest: boolean
    exercises: Exercise[]
}

// ── DEFAULT PROGRAMS ───────────────────────────────────────────────────
const DEFAULT_PPL: DaySchedule[] = [
    {
        label: 'Day 1', tag: 'Push A', color: '#f97316', rest: false,
        exercises: [
            { name: 'Barbell Bench Press',    sets: 4, reps: '6–8',   muscle: 'Chest' },
            { name: 'Overhead Press (OHP)',   sets: 3, reps: '8–10',  muscle: 'Shoulders' },
            { name: 'Incline DB Press',       sets: 3, reps: '10–12', muscle: 'Upper Chest' },
            { name: 'Cable Lateral Raise',    sets: 4, reps: '15–20', muscle: 'Shoulders' },
            { name: 'Tricep Rope Pushdown',   sets: 3, reps: '12–15', muscle: 'Triceps' },
            { name: 'Skull Crushers',         sets: 3, reps: '10–12', muscle: 'Triceps' },
            { name: 'Cable Chest Fly',        sets: 3, reps: '12–15', muscle: 'Chest' },
        ],
    },
    {
        label: 'Day 2', tag: 'Pull A', color: '#3b82f6', rest: false,
        exercises: [
            { name: 'Barbell Bent-Over Row',  sets: 4, reps: '6–8',   muscle: 'Back' },
            { name: 'Pull-ups',               sets: 4, reps: 'AMRAP', muscle: 'Lats' },
            { name: 'Seated Cable Row',       sets: 3, reps: '10–12', muscle: 'Back' },
            { name: 'Face Pulls',             sets: 4, reps: '15–20', muscle: 'Rear Delts' },
            { name: 'Barbell Curl',           sets: 3, reps: '10–12', muscle: 'Biceps' },
            { name: 'Hammer Curls',           sets: 3, reps: '12–15', muscle: 'Biceps' },
            { name: 'Lat Pulldown',           sets: 3, reps: '10–12', muscle: 'Lats' },
        ],
    },
    {
        label: 'Day 3', tag: 'Legs A', color: '#8b5cf6', rest: false,
        exercises: [
            { name: 'Back Squat',             sets: 4, reps: '5–8',   muscle: 'Quads / Glutes' },
            { name: 'Romanian Deadlift',      sets: 3, reps: '8–10',  muscle: 'Hamstrings' },
            { name: 'Leg Press',              sets: 3, reps: '12–15', muscle: 'Quads' },
            { name: 'Seated Leg Curl',        sets: 3, reps: '12–15', muscle: 'Hamstrings' },
            { name: 'Bulgarian Split Squat',  sets: 3, reps: '10–12', muscle: 'Quads / Glutes' },
            { name: 'Hip Thrust',             sets: 3, reps: '12–15', muscle: 'Glutes' },
            { name: 'Standing Calf Raise',    sets: 4, reps: '15–20', muscle: 'Calves' },
        ],
    },
    {
        label: 'Day 4', tag: 'Push B', color: '#f97316', rest: false,
        exercises: [
            { name: 'Incline Barbell Press',  sets: 4, reps: '6–8',   muscle: 'Upper Chest' },
            { name: 'DB Shoulder Press',      sets: 3, reps: '10–12', muscle: 'Shoulders' },
            { name: 'Cable Chest Fly',        sets: 4, reps: '12–15', muscle: 'Chest' },
            { name: 'Lateral Raise',          sets: 4, reps: '15–20', muscle: 'Shoulders' },
            { name: 'Close-Grip Bench Press', sets: 3, reps: '8–10',  muscle: 'Triceps' },
            { name: 'Overhead Tricep Ext.',   sets: 3, reps: '12–15', muscle: 'Triceps' },
            { name: 'Machine Chest Press',    sets: 3, reps: '12–15', muscle: 'Chest' },
        ],
    },
    {
        label: 'Day 5', tag: 'Pull B', color: '#3b82f6', rest: false,
        exercises: [
            { name: 'Deadlift',               sets: 4, reps: '4–6',   muscle: 'Full Back' },
            { name: 'Lat Pulldown',           sets: 4, reps: '10–12', muscle: 'Lats' },
            { name: 'T-Bar Row',              sets: 3, reps: '8–10',  muscle: 'Back' },
            { name: 'Rear Delt Fly',          sets: 4, reps: '15–20', muscle: 'Rear Delts' },
            { name: 'Preacher Curl',          sets: 3, reps: '10–12', muscle: 'Biceps' },
            { name: 'Cable Curl',             sets: 3, reps: '12–15', muscle: 'Biceps' },
            { name: 'Single-Arm DB Row',      sets: 3, reps: '10–12', muscle: 'Back' },
        ],
    },
    {
        label: 'Day 6', tag: 'Legs B', color: '#8b5cf6', rest: false,
        exercises: [
            { name: 'Front Squat',            sets: 4, reps: '5–8',   muscle: 'Quads' },
            { name: 'Sumo Deadlift',          sets: 3, reps: '6–8',   muscle: 'Glutes / Hamstrings' },
            { name: 'Hack Squat',             sets: 3, reps: '10–12', muscle: 'Quads' },
            { name: 'Leg Extension',          sets: 3, reps: '15–20', muscle: 'Quads' },
            { name: 'Walking Lunges',         sets: 3, reps: '12/leg', muscle: 'Quads / Glutes' },
            { name: 'Good Mornings',          sets: 3, reps: '10–12', muscle: 'Hamstrings' },
            { name: 'Seated Calf Raise',      sets: 4, reps: '15–20', muscle: 'Calves' },
        ],
    },
    { label: 'Day 7', tag: 'Rest', color: '#334155', rest: true, exercises: [] },
]

const DEFAULT_UPPER_LOWER: DaySchedule[] = [
    {
        label: 'Day 1', tag: 'Upper A', color: '#f97316', rest: false,
        exercises: [
            { name: 'Barbell Bench Press',    sets: 4, reps: '5–6',   muscle: 'Chest' },
            { name: 'Barbell Bent-Over Row',  sets: 4, reps: '5–6',   muscle: 'Back' },
            { name: 'Overhead Press',         sets: 3, reps: '8–10',  muscle: 'Shoulders' },
            { name: 'Pull-ups',               sets: 3, reps: 'AMRAP', muscle: 'Lats' },
            { name: 'Incline DB Press',       sets: 3, reps: '10–12', muscle: 'Upper Chest' },
            { name: 'Face Pulls',             sets: 3, reps: '15–20', muscle: 'Rear Delts' },
            { name: 'Barbell Curl',           sets: 3, reps: '10–12', muscle: 'Biceps' },
            { name: 'Tricep Pushdown',        sets: 3, reps: '12–15', muscle: 'Triceps' },
        ],
    },
    {
        label: 'Day 2', tag: 'Lower A', color: '#8b5cf6', rest: false,
        exercises: [
            { name: 'Back Squat',             sets: 4, reps: '5–6',   muscle: 'Quads / Glutes' },
            { name: 'Romanian Deadlift',      sets: 4, reps: '6–8',   muscle: 'Hamstrings' },
            { name: 'Leg Press',              sets: 3, reps: '12–15', muscle: 'Quads' },
            { name: 'Seated Leg Curl',        sets: 3, reps: '12–15', muscle: 'Hamstrings' },
            { name: 'Bulgarian Split Squat',  sets: 3, reps: '10/leg', muscle: 'Quads / Glutes' },
            { name: 'Hip Thrust',             sets: 3, reps: '12–15', muscle: 'Glutes' },
            { name: 'Calf Raises',            sets: 4, reps: '15–20', muscle: 'Calves' },
        ],
    },
    { label: 'Day 3', tag: 'Rest', color: '#334155', rest: true, exercises: [] },
    {
        label: 'Day 4', tag: 'Upper B', color: '#f97316', rest: false,
        exercises: [
            { name: 'Incline Bench Press',    sets: 4, reps: '8–10',  muscle: 'Upper Chest' },
            { name: 'T-Bar Row',              sets: 4, reps: '8–10',  muscle: 'Back' },
            { name: 'DB Shoulder Press',      sets: 3, reps: '10–12', muscle: 'Shoulders' },
            { name: 'Lat Pulldown',           sets: 3, reps: '10–12', muscle: 'Lats' },
            { name: 'Cable Fly',              sets: 3, reps: '12–15', muscle: 'Chest' },
            { name: 'Rear Delt Fly',          sets: 3, reps: '15–20', muscle: 'Rear Delts' },
            { name: 'Hammer Curls',           sets: 3, reps: '12–15', muscle: 'Biceps' },
            { name: 'Close-Grip Bench',       sets: 3, reps: '8–10',  muscle: 'Triceps' },
        ],
    },
    {
        label: 'Day 5', tag: 'Lower B', color: '#8b5cf6', rest: false,
        exercises: [
            { name: 'Deadlift',               sets: 4, reps: '4–5',   muscle: 'Full Back / Glutes' },
            { name: 'Front Squat',            sets: 3, reps: '6–8',   muscle: 'Quads' },
            { name: 'Hack Squat',             sets: 3, reps: '10–12', muscle: 'Quads' },
            { name: 'Leg Extension',          sets: 3, reps: '15–20', muscle: 'Quads' },
            { name: 'Walking Lunges',         sets: 3, reps: '12/leg', muscle: 'Quads / Glutes' },
            { name: 'Good Mornings',          sets: 3, reps: '10–12', muscle: 'Hamstrings' },
            { name: 'Seated Calf Raise',      sets: 4, reps: '15–20', muscle: 'Calves' },
        ],
    },
    { label: 'Day 6', tag: 'Rest', color: '#334155', rest: true, exercises: [] },
    { label: 'Day 7', tag: 'Rest', color: '#334155', rest: true, exercises: [] },
]

// ── state ──────────────────────────────────────────────────────────────
const DEFAULT = props.programType === 'PPL' ? DEFAULT_PPL : DEFAULT_UPPER_LOWER

// Deep clone for editing
function cloneSchedule(s: DaySchedule[]): DaySchedule[] {
    return JSON.parse(JSON.stringify(s))
}

const activeView  = ref<'calendar' | 'program'>('calendar')
const activeDay   = ref(0)
const editMode    = ref(false)
const hasChanges  = ref(false)

// Working copy of the schedule (custom or default)
const schedule = ref<DaySchedule[]>(cloneSchedule(props.customSchedule ?? DEFAULT))
const editCopy  = ref<DaySchedule[]>(cloneSchedule(schedule.value))

const isCustomized = computed(() => !!props.customSchedule)

// ── SAVE / RESET ───────────────────────────────────────────────────────
const saveForm  = useForm({ schedule_data: [] as DaySchedule[] })
const resetForm = useForm({})

function enterEdit() {
    editCopy.value = cloneSchedule(schedule.value)
    editMode.value = true
    hasChanges.value = false
}

function cancelEdit() {
    editCopy.value = cloneSchedule(schedule.value)
    editMode.value = false
    hasChanges.value = false
}

function saveSchedule() {
    saveForm.schedule_data = editCopy.value
    saveForm.post('/fitness/schedule/save', {
        onSuccess: () => {
            schedule.value = cloneSchedule(editCopy.value)
            editMode.value = false
            hasChanges.value = false
        },
    })
}

function resetSchedule() {
    if (!confirm('Reset to default program? Your customizations will be lost.')) return
    resetForm.post('/fitness/schedule/reset', {
        onSuccess: () => {
            schedule.value = cloneSchedule(DEFAULT)
            editCopy.value = cloneSchedule(DEFAULT)
            editMode.value = false
        },
    })
}

// ── EXERCISE EDITING ───────────────────────────────────────────────────
function addExercise(dayIdx: number) {
    editCopy.value[dayIdx].exercises.push({ name: '', sets: 3, reps: '8–12', muscle: '' })
    hasChanges.value = true
}

function removeExercise(dayIdx: number, exIdx: number) {
    editCopy.value[dayIdx].exercises.splice(exIdx, 1)
    hasChanges.value = true
}

function markChanged() { hasChanges.value = true }

// ── CALENDAR ───────────────────────────────────────────────────────────
const today     = new Date()
const calMonth  = ref(today.getMonth())
const calYear   = ref(today.getFullYear())

const calendarDays = computed(() => {
    const firstDay = new Date(calYear.value, calMonth.value, 1)
    const lastDay  = new Date(calYear.value, calMonth.value + 1, 0)
    // Start week on Monday: JS getDay() 0=Sun → shift to Mon=0
    const startDow = (firstDay.getDay() + 6) % 7

    const days: { date: number; month: number; dayIdx: number | null }[] = []

    // Padding from prev month
    for (let i = 0; i < startDow; i++) {
        const d = new Date(calYear.value, calMonth.value, -startDow + i + 1)
        days.push({ date: d.getDate(), month: d.getMonth(), dayIdx: null })
    }

    // Current month days — map to schedule cycle
    const programLen = schedule.value.length // 7
    // Anchor: find what day-of-week Monday this week is, set as day 0 of the cycle
    const todayDow = (today.getDay() + 6) % 7 // Mon=0
    const todayAbsolute = Math.floor(today.getTime() / 86400000)
    const mondayAbsolute = todayAbsolute - todayDow

    for (let d = 1; d <= lastDay.getDate(); d++) {
        const date = new Date(calYear.value, calMonth.value, d)
        const dow  = (date.getDay() + 6) % 7 // Mon=0
        const dateAbsolute = Math.floor(date.getTime() / 86400000)
        const weekOffset = Math.floor((dateAbsolute - mondayAbsolute) / 7)
        const cycleDay   = ((weekOffset * 7 + dow) % programLen + programLen) % programLen
        days.push({ date: d, month: calMonth.value, dayIdx: cycleDay })
    }

    // Pad to complete last row
    const remaining = (7 - (days.length % 7)) % 7
    for (let i = 1; i <= remaining; i++) {
        days.push({ date: i, month: calMonth.value + 1, dayIdx: null })
    }

    return days
})

function prevMonth() {
    if (calMonth.value === 0) { calMonth.value = 11; calYear.value-- }
    else calMonth.value--
}
function nextMonth() {
    if (calMonth.value === 11) { calMonth.value = 0; calYear.value++ }
    else calMonth.value++
}

const monthName = computed(() =>
    new Date(calYear.value, calMonth.value, 1).toLocaleString('default', { month: 'long', year: 'numeric' })
)

function isToday(date: number, month: number) {
    return date === today.getDate() && month === today.getMonth() && calYear.value === today.getFullYear()
}

function calClickDay(dayIdx: number | null) {
    if (dayIdx === null) return
    activeDay.value = dayIdx
    activeView.value = 'program'
    gsap.to('.fit-sc-detail', { opacity: 0, y: 10, duration: 0.15, onComplete: () => {
        gsap.to('.fit-sc-detail', { opacity: 1, y: 0, duration: 0.3 })
    }})
}

// ── muscles ────────────────────────────────────────────────────────────
const MUSCLE_COLORS: Record<string, string> = {
    Chest: '#f97316', 'Upper Chest': '#fb923c', Shoulders: '#facc15',
    Triceps: '#f87171', Back: '#3b82f6', Lats: '#60a5fa',
    'Rear Delts': '#93c5fd', Biceps: '#34d399',
    'Quads / Glutes': '#8b5cf6', Quads: '#a78bfa',
    Hamstrings: '#c084fc', Glutes: '#d8b4fe',
    'Glutes / Hamstrings': '#c084fc', 'Full Back': '#3b82f6',
    'Full Back / Glutes': '#818cf8', Calves: '#6ee7b7',
}
function mColor(m: string) { return MUSCLE_COLORS[m] ?? '#94a3b8' }

const GOAL_LABEL: Record<string, string> = {
    lose_weight: '🔥 Lose Weight', maintain: '⚖️ Maintain', gain_muscle: '💪 Gain Muscle',
}

const page = usePage()
const flash = computed(() => (page.props as any).flash ?? {})

// ── animation ──────────────────────────────────────────────────────────
onMounted(() => {
    gsap.from('.fit-sc-header', { opacity: 0, y: -20, duration: 0.6 })
    gsap.from('.fit-sc-tabs',   { opacity: 0, y: 10,  duration: 0.5, delay: 0.2 })
    gsap.from('.fit-sc-body',   { opacity: 0, y: 20,  duration: 0.5, delay: 0.3 })
})

function switchView(v: 'calendar' | 'program') {
    gsap.to('.fit-sc-body', { opacity: 0, y: 10, duration: 0.2, onComplete: () => {
        activeView.value = v
        gsap.to('.fit-sc-body', { opacity: 1, y: 0, duration: 0.3 })
    }})
}

function selectDay(i: number) {
    gsap.to('.fit-sc-detail', { opacity: 0, x: -15, duration: 0.18, onComplete: () => {
        activeDay.value = i
        gsap.fromTo('.fit-sc-detail', { opacity: 0, x: 15 }, { opacity: 1, x: 0, duration: 0.28 })
    }})
}
</script>

<template>
    <Head title="FitTrack — Schedule" />

    <div class="fit-sc-page">

        <!-- NAV -->
        <nav class="fit-nav-bar">
            <Link href="/fitness" class="fit-logo">⚡ FitTrack</Link>
            <div class="fit-nav-right">
                <Link href="/fitness/dashboard" class="fit-nav-link">Dashboard</Link>
                <Link href="/logout" method="post" as="button" class="fit-nav-out">Log out</Link>
            </div>
        </nav>

        <main class="fit-sc-main">

            <!-- HEADER -->
            <div class="fit-sc-header">
                <div>
                    <h1>{{ programType === 'PPL' ? 'Push / Pull / Legs' : 'Upper / Lower' }}</h1>
                    <p>{{ GOAL_LABEL[goal] }} · {{ programType === 'PPL' ? '6' : '4' }} training days / week
                       <span v-if="isCustomized" class="fit-custom-badge">✏️ Customized</span>
                    </p>
                </div>
                <div class="fit-sc-header-actions">
                    <button v-if="!editMode" class="fit-btn-edit" @click="enterEdit">✏️ Customize</button>
                    <button v-if="isCustomized && !editMode" class="fit-btn-reset" @click="resetSchedule" :disabled="resetForm.processing">
                        {{ resetForm.processing ? 'Resetting…' : '↩ Reset to default' }}
                    </button>
                </div>
            </div>

            <!-- FLASH -->
            <div v-if="flash.success" class="fit-flash">✅ {{ flash.success }}</div>

            <!-- VIEW TABS -->
            <div class="fit-view-tabs fit-sc-tabs">
                <button :class="['fit-view-tab', { active: activeView === 'calendar' }]" @click="switchView('calendar')">
                    📅 Calendar
                </button>
                <button :class="['fit-view-tab', { active: activeView === 'program' }]" @click="switchView('program')">
                    💪 Program
                </button>
            </div>

            <!-- EDIT MODE BANNER -->
            <div v-if="editMode" class="fit-edit-banner">
                <span>✏️ Edit mode — click any field to change it</span>
                <div class="fit-edit-actions">
                    <button class="fit-btn-cancel" @click="cancelEdit">Cancel</button>
                    <button class="fit-btn-save" @click="saveSchedule" :disabled="!hasChanges || saveForm.processing">
                        {{ saveForm.processing ? 'Saving…' : '💾 Save changes' }}
                    </button>
                </div>
            </div>

            <div class="fit-sc-body">

                <!-- ══ CALENDAR VIEW ══ -->
                <div v-if="activeView === 'calendar'" class="fit-cal-wrap">

                    <div class="fit-cal-nav">
                        <button class="fit-cal-nav-btn" @click="prevMonth">‹</button>
                        <span class="fit-cal-month">{{ monthName }}</span>
                        <button class="fit-cal-nav-btn" @click="nextMonth">›</button>
                    </div>

                    <!-- day-of-week headers -->
                    <div class="fit-cal-grid">
                        <div class="fit-cal-dow" v-for="d in ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']" :key="d">{{ d }}</div>

                        <div
                            v-for="(cell, i) in calendarDays"
                            :key="i"
                            :class="[
                                'fit-cal-cell',
                                { 'fit-cal-other': cell.month !== calMonth },
                                { 'fit-cal-today': isToday(cell.date, cell.month) },
                                { 'fit-cal-rest':  cell.dayIdx !== null && schedule[cell.dayIdx].rest },
                                { 'fit-cal-clickable': cell.dayIdx !== null && !schedule[cell.dayIdx].rest },
                            ]"
                            @click="calClickDay(cell.dayIdx)"
                        >
                            <span class="fit-cal-date">{{ cell.date }}</span>
                            <span
                                v-if="cell.dayIdx !== null && !schedule[cell.dayIdx].rest"
                                class="fit-cal-tag"
                                :style="{ background: schedule[cell.dayIdx].color + '25', color: schedule[cell.dayIdx].color }"
                            >
                                {{ schedule[cell.dayIdx].tag }}
                            </span>
                            <span v-if="cell.dayIdx !== null && schedule[cell.dayIdx].rest" class="fit-cal-rest-label">Rest</span>
                        </div>
                    </div>

                    <!-- legend -->
                    <div class="fit-cal-legend">
                        <div v-for="day in schedule.filter(d => !d.rest)" :key="day.tag" class="fit-cal-legend-item">
                            <span class="fit-legend-dot" :style="{ background: day.color }"></span>
                            {{ day.tag }}
                        </div>
                        <div class="fit-cal-legend-item">
                            <span class="fit-legend-dot" style="background:#334155"></span> Rest
                        </div>
                    </div>
                </div>

                <!-- ══ PROGRAM VIEW ══ -->
                <div v-else class="fit-program-wrap">

                    <!-- day selector tabs -->
                    <div class="fit-day-tabs">
                        <button
                            v-for="(day, i) in (editMode ? editCopy : schedule)"
                            :key="i"
                            :class="['fit-day-tab', { active: i === activeDay, rest: day.rest }]"
                            :style="i === activeDay && !day.rest ? { borderColor: day.color, color: day.color } : {}"
                            @click="selectDay(i)"
                        >
                            <span class="fit-tab-lbl">{{ day.label }}</span>
                            <span class="fit-tab-tag">{{ day.tag }}</span>
                        </button>
                    </div>

                    <!-- detail panel -->
                    <div class="fit-sc-detail">

                        <!-- VIEW mode -->
                        <template v-if="!editMode">
                            <div class="fit-day-hdr">
                                <div>
                                    <span class="fit-day-badge"
                                        :style="{ background: schedule[activeDay].color+'20', color: schedule[activeDay].color, borderColor: schedule[activeDay].color+'50' }">
                                        {{ schedule[activeDay].tag }}
                                    </span>
                                    <h2>{{ schedule[activeDay].label }}</h2>
                                </div>
                                <span v-if="!schedule[activeDay].rest" class="fit-ex-count">
                                    {{ schedule[activeDay].exercises.length }} exercises
                                </span>
                            </div>

                            <!-- rest -->
                            <div v-if="schedule[activeDay].rest" class="fit-rest-panel">
                                <div class="fit-rest-icon">😴</div>
                                <h3>Rest & Recovery</h3>
                                <p>Muscles grow during rest. Prioritise sleep, hydration, and protein.</p>
                                <ul class="fit-rest-tips">
                                    <li>🥩 0.8–1g protein per lb of bodyweight</li>
                                    <li>💧 2–3L of water today</li>
                                    <li>🧘 Light walk or stretching is fine</li>
                                    <li>😴 Aim for 7–9 hours of sleep</li>
                                </ul>
                            </div>

                            <!-- exercises -->
                            <div v-else class="fit-ex-list">
                                <div v-for="(ex, i) in schedule[activeDay].exercises" :key="i" class="fit-ex-row">
                                    <div class="fit-ex-num">{{ i + 1 }}</div>
                                    <div class="fit-ex-info">
                                        <div class="fit-ex-name">{{ ex.name }}</div>
                                        <div class="fit-ex-muscle" :style="{ color: mColor(ex.muscle) }">{{ ex.muscle }}</div>
                                    </div>
                                    <div class="fit-ex-right">
                                        <div class="fit-ex-sets">{{ ex.sets }} sets</div>
                                        <div class="fit-ex-reps">{{ ex.reps }} reps</div>
                                    </div>
                                </div>

                                <!-- goal tip -->
                                <div class="fit-goal-tip">
                                    <strong>💡</strong>
                                    <span v-if="goal === 'lose_weight'"> Rest 60–90 s between sets. Supersets welcome. Focus on form over weight.</span>
                                    <span v-else-if="goal === 'gain_muscle'"> Rest 2–3 min on heavy sets. Add weight or reps each week (progressive overload).</span>
                                    <span v-else> Add weight when you hit the top of the rep range across all sets.</span>
                                </div>
                            </div>
                        </template>

                        <!-- EDIT mode -->
                        <template v-else>
                            <div class="fit-day-hdr">
                                <div>
                                    <span class="fit-day-badge"
                                        :style="{ background: editCopy[activeDay].color+'20', color: editCopy[activeDay].color, borderColor: editCopy[activeDay].color+'50' }">
                                        {{ editCopy[activeDay].tag }}
                                    </span>
                                    <input
                                        v-model="editCopy[activeDay].tag"
                                        class="fit-edit-tag-input"
                                        placeholder="Session name"
                                        @input="markChanged"
                                    />
                                </div>
                                <span class="fit-ex-count edit">{{ editCopy[activeDay].exercises.length }} exercises</span>
                            </div>

                            <div v-if="editCopy[activeDay].rest" class="fit-rest-panel">
                                <div class="fit-rest-icon">😴</div>
                                <p style="color:#475569">Rest day — no exercises to edit.</p>
                            </div>

                            <div v-else class="fit-ex-list">
                                <!-- exercise rows (editable) -->
                                <div v-for="(ex, ei) in editCopy[activeDay].exercises" :key="ei" class="fit-ex-edit-row">
                                    <div class="fit-ex-num">{{ ei + 1 }}</div>
                                    <div class="fit-edit-fields">
                                        <input v-model="ex.name"   class="fit-edit-input fit-edit-name"   placeholder="Exercise name" @input="markChanged" />
                                        <input v-model="ex.muscle" class="fit-edit-input fit-edit-muscle" placeholder="Muscle group"  @input="markChanged" />
                                        <div class="fit-edit-setrow">
                                            <label>Sets</label>
                                            <input v-model.number="ex.sets" type="number" min="1" max="10" class="fit-edit-input fit-edit-small" @input="markChanged" />
                                            <label>Reps</label>
                                            <input v-model="ex.reps" class="fit-edit-input fit-edit-small" placeholder="8–12" @input="markChanged" />
                                        </div>
                                    </div>
                                    <button class="fit-ex-delete" @click="removeExercise(activeDay, ei)" title="Remove">✕</button>
                                </div>

                                <!-- add exercise -->
                                <button class="fit-add-ex-btn" @click="addExercise(activeDay)">
                                    + Add exercise
                                </button>

                                <!-- min 6 warning -->
                                <div v-if="editCopy[activeDay].exercises.length < 6" class="fit-edit-warn">
                                    ⚠ Add at least {{ 6 - editCopy[activeDay].exercises.length }} more exercise(s) — minimum 6 per session.
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div><!-- /body -->
        </main>
    </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; }

.fit-sc-page {
    min-height: 100vh;
    background: #080f1e;
    color: #e2e8f0;
    font-family: 'Segoe UI', system-ui, sans-serif;
}

/* ── NAV ── */
.fit-nav-bar {
    display: flex; justify-content: space-between; align-items: center;
    padding: 16px 32px; border-bottom: 1px solid #1a2d4a;
    background: rgba(8,15,30,0.96); backdrop-filter: blur(8px);
    position: sticky; top: 0; z-index: 20;
}
.fit-logo      { font-size: 1.1rem; font-weight: 700; color: #34d399; text-decoration: none; }
.fit-nav-right { display: flex; gap: 12px; align-items: center; }
.fit-nav-link  { color: #94a3b8; font-size: 0.9rem; text-decoration: none; }
.fit-nav-link:hover { color: #34d399; }
.fit-nav-out {
    padding: 6px 16px; background: transparent; border: 1px solid #2d4a6e;
    border-radius: 8px; color: #64748b; font-size: 0.85rem; cursor: pointer; font-family: inherit;
}
.fit-nav-out:hover { border-color: #f87171; color: #f87171; }

/* ── MAIN ── */
.fit-sc-main { max-width: 1000px; margin: 0 auto; padding: 32px 20px 80px; }

/* ── HEADER ── */
.fit-sc-header {
    display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px;
}
.fit-sc-header h1 { font-size: 1.7rem; font-weight: 800; margin-bottom: 4px; }
.fit-sc-header p  { color: #475569; font-size: 0.88rem; }
.fit-custom-badge {
    display: inline-block; margin-left: 8px;
    background: rgba(251,191,36,0.12); color: #fbbf24;
    border: 1px solid rgba(251,191,36,0.3); border-radius: 20px;
    padding: 2px 10px; font-size: 0.72rem;
}
.fit-sc-header-actions { display: flex; gap: 10px; flex-shrink: 0; }
.fit-btn-edit {
    padding: 9px 18px; background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.35);
    border-radius: 10px; color: #34d399; font-size: 0.88rem; cursor: pointer; font-family: inherit;
    transition: background 0.2s;
}
.fit-btn-edit:hover { background: rgba(52,211,153,0.2); }
.fit-btn-reset {
    padding: 9px 18px; background: transparent; border: 1px solid #334155;
    border-radius: 10px; color: #64748b; font-size: 0.85rem; cursor: pointer; font-family: inherit;
}
.fit-btn-reset:hover { border-color: #f87171; color: #f87171; }

/* ── FLASH ── */
.fit-flash {
    margin-bottom: 16px; padding: 12px 16px; background: rgba(52,211,153,0.1);
    border: 1px solid rgba(52,211,153,0.3); border-radius: 10px; font-size: 0.88rem; color: #34d399;
}

/* ── VIEW TABS ── */
.fit-view-tabs { display: flex; gap: 8px; margin-bottom: 16px; }
.fit-view-tab {
    padding: 10px 24px; background: #0d1a2e; border: 1px solid #1a2d4a;
    border-radius: 10px; color: #475569; font-size: 0.9rem; cursor: pointer;
    font-family: inherit; transition: all 0.2s;
}
.fit-view-tab.active { border-color: #34d399; color: #34d399; background: rgba(52,211,153,0.07); }

/* ── EDIT BANNER ── */
.fit-edit-banner {
    display: flex; justify-content: space-between; align-items: center;
    background: rgba(251,191,36,0.07); border: 1px solid rgba(251,191,36,0.25);
    border-radius: 12px; padding: 12px 18px; margin-bottom: 16px;
    font-size: 0.88rem; color: #fbbf24;
}
.fit-edit-actions { display: flex; gap: 10px; }
.fit-btn-cancel {
    padding: 8px 18px; background: transparent; border: 1px solid #334155;
    border-radius: 8px; color: #64748b; font-size: 0.85rem; cursor: pointer; font-family: inherit;
}
.fit-btn-save {
    padding: 8px 20px; background: #34d399; border: none; border-radius: 8px;
    color: #080f1e; font-weight: 700; font-size: 0.88rem; cursor: pointer; font-family: inherit;
    transition: background 0.2s;
}
.fit-btn-save:disabled { opacity: 0.4; cursor: not-allowed; }
.fit-btn-save:not(:disabled):hover { background: #10b981; }

/* ── BODY ── */
.fit-sc-body { min-height: 400px; }

/* ══ CALENDAR ══ */
.fit-cal-wrap { background: #0d1a2e; border: 1px solid #1a2d4a; border-radius: 18px; padding: 24px; }

.fit-cal-nav {
    display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;
}
.fit-cal-month  { font-size: 1.1rem; font-weight: 700; }
.fit-cal-nav-btn {
    width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;
    background: #0a1525; border: 1px solid #1e3a5f; border-radius: 8px;
    color: #94a3b8; font-size: 1.2rem; cursor: pointer; transition: border-color 0.2s;
}
.fit-cal-nav-btn:hover { border-color: #34d399; color: #34d399; }

.fit-cal-grid {
    display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px;
}
.fit-cal-dow {
    text-align: center; font-size: 0.72rem; text-transform: uppercase;
    letter-spacing: 0.8px; color: #334155; padding: 6px 0 10px;
}
.fit-cal-cell {
    min-height: 76px; padding: 8px 6px; border-radius: 10px;
    background: #0a1525; border: 1px solid #0f2038;
    display: flex; flex-direction: column; gap: 4px;
    transition: border-color 0.18s, background 0.18s;
}
.fit-cal-cell.fit-cal-other   { opacity: 0.3; }
.fit-cal-cell.fit-cal-today   { border-color: #34d399 !important; background: rgba(52,211,153,0.05); }
.fit-cal-cell.fit-cal-rest    { background: #090d17; }
.fit-cal-cell.fit-cal-clickable { cursor: pointer; }
.fit-cal-cell.fit-cal-clickable:hover { border-color: #2d4a6e; background: #111f38; }

.fit-cal-date {
    font-size: 0.82rem; font-weight: 600; color: #64748b; line-height: 1;
}
.fit-cal-today .fit-cal-date { color: #34d399; }
.fit-cal-tag {
    display: inline-block; padding: 2px 7px; border-radius: 6px;
    font-size: 0.68rem; font-weight: 600; white-space: nowrap;
}
.fit-cal-rest-label { font-size: 0.68rem; color: #334155; }

.fit-cal-legend {
    display: flex; flex-wrap: wrap; gap: 14px; margin-top: 20px;
    padding-top: 16px; border-top: 1px solid #0f2038;
}
.fit-cal-legend-item { display: flex; align-items: center; gap: 6px; font-size: 0.8rem; color: #64748b; }
.fit-legend-dot { width: 10px; height: 10px; border-radius: 50%; }

/* ══ PROGRAM ══ */
.fit-program-wrap { display: flex; flex-direction: column; gap: 16px; }

/* day tabs */
.fit-day-tabs {
    display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px;
}
.fit-day-tab {
    display: flex; flex-direction: column; align-items: center; gap: 3px;
    padding: 10px 4px; background: #0d1a2e; border: 1px solid #1a2d4a;
    border-radius: 10px; color: #475569; font-family: inherit; cursor: pointer;
    font-size: 0.73rem; transition: all 0.18s;
}
.fit-day-tab:hover { border-color: #2d4a6e; color: #94a3b8; }
.fit-day-tab.active { font-weight: 600; }
.fit-day-tab.rest  { opacity: 0.45; }
.fit-tab-lbl { font-size: 0.62rem; text-transform: uppercase; letter-spacing: 0.5px; }
.fit-tab-tag { font-weight: 700; font-size: 0.78rem; }

/* detail card */
.fit-sc-detail {
    background: #0d1a2e; border: 1px solid #1a2d4a; border-radius: 16px; padding: 24px 22px;
}
.fit-day-hdr {
    display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 22px;
}
.fit-day-badge {
    display: inline-block; padding: 3px 12px; border-radius: 20px; border: 1px solid;
    font-size: 0.75rem; font-weight: 600; margin-bottom: 6px;
}
.fit-day-hdr h2 { font-size: 1.2rem; font-weight: 700; }
.fit-ex-count { font-size: 0.8rem; color: #334155; }
.fit-ex-count.edit { color: #fbbf24; }

/* rest panel */
.fit-rest-panel { text-align: center; padding: 28px 0; }
.fit-rest-icon  { font-size: 2.5rem; margin-bottom: 12px; }
.fit-rest-panel h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; }
.fit-rest-panel p  { color: #64748b; font-size: 0.9rem; margin-bottom: 20px; }
.fit-rest-tips { list-style: none; display: inline-flex; flex-direction: column; gap: 10px; text-align: left; }
.fit-rest-tips li { font-size: 0.88rem; color: #94a3b8; }

/* exercise list (view) */
.fit-ex-list { display: flex; flex-direction: column; }
.fit-ex-row {
    display: flex; align-items: center; gap: 14px;
    padding: 12px 0; border-bottom: 1px solid #0f2038;
}
.fit-ex-row:last-of-type { border-bottom: none; }
.fit-ex-num {
    width: 28px; height: 28px; border-radius: 50%; background: #0a1525;
    border: 1px solid #1e3a5f; display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem; color: #475569; flex-shrink: 0;
}
.fit-ex-info  { flex: 1; }
.fit-ex-name  { font-size: 0.95rem; font-weight: 600; margin-bottom: 3px; }
.fit-ex-muscle{ font-size: 0.75rem; font-weight: 500; }
.fit-ex-right { text-align: right; flex-shrink: 0; }
.fit-ex-sets  { font-size: 0.95rem; font-weight: 700; }
.fit-ex-reps  { font-size: 0.75rem; color: #475569; margin-top: 2px; }

.fit-goal-tip {
    margin-top: 18px; padding: 12px 16px;
    background: rgba(52,211,153,0.05); border: 1px solid rgba(52,211,153,0.15);
    border-radius: 10px; font-size: 0.85rem; color: #64748b; line-height: 1.5;
}
.fit-goal-tip strong { color: #34d399; }

/* exercise list (edit) */
.fit-ex-edit-row {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 12px 0; border-bottom: 1px solid #0f2038;
}
.fit-edit-fields { flex: 1; display: flex; flex-direction: column; gap: 7px; }
.fit-edit-input {
    background: #0a1525; border: 1px solid #1e3a5f; border-radius: 8px;
    color: #e2e8f0; padding: 8px 12px; font-size: 0.88rem; outline: none;
    transition: border-color 0.18s; font-family: inherit; width: 100%;
}
.fit-edit-input:focus { border-color: #34d399; }
.fit-edit-name   { font-weight: 600; }
.fit-edit-muscle { font-size: 0.82rem; }
.fit-edit-setrow {
    display: flex; align-items: center; gap: 8px;
}
.fit-edit-setrow label { font-size: 0.75rem; color: #475569; white-space: nowrap; }
.fit-edit-small  { width: 70px !important; text-align: center; }
.fit-edit-tag-input {
    background: transparent; border: none; border-bottom: 1px dashed #2d4a6e;
    color: #e2e8f0; font-size: 1.1rem; font-weight: 700; outline: none;
    width: 180px; padding: 2px 4px;
}
.fit-ex-delete {
    flex-shrink: 0; width: 28px; height: 28px; border-radius: 6px;
    background: rgba(248,113,113,0.1); border: 1px solid rgba(248,113,113,0.25);
    color: #f87171; font-size: 0.75rem; cursor: pointer; font-family: inherit;
    display: flex; align-items: center; justify-content: center; margin-top: 8px;
    transition: background 0.18s;
}
.fit-ex-delete:hover { background: rgba(248,113,113,0.25); }

.fit-add-ex-btn {
    margin-top: 14px; padding: 11px; width: 100%;
    background: rgba(52,211,153,0.06); border: 1px dashed rgba(52,211,153,0.3);
    border-radius: 10px; color: #34d399; font-size: 0.9rem; cursor: pointer;
    font-family: inherit; transition: background 0.18s;
}
.fit-add-ex-btn:hover { background: rgba(52,211,153,0.12); }

.fit-edit-warn {
    margin-top: 12px; padding: 10px 14px;
    background: rgba(251,191,36,0.07); border: 1px solid rgba(251,191,36,0.25);
    border-radius: 8px; font-size: 0.83rem; color: #fbbf24;
}

/* ── RESPONSIVE ── */
@media (max-width: 680px) {
    .fit-day-tabs  { grid-template-columns: repeat(4, 1fr); }
    .fit-cal-grid  { gap: 2px; }
    .fit-cal-cell  { min-height: 58px; }
    .fit-sc-header { flex-direction: column; gap: 14px; }
    .fit-nav-bar   { padding: 12px 16px; }
}
</style>
