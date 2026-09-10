<script setup lang="ts">
import { reactive, ref, computed, onMounted } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import gsap from 'gsap'

const step  = ref(1)
const TOTAL = 3

const form = useForm({
    age:            '',
    gender:         '',
    height_cm:      '',
    weight_kg:      '',
    activity_level: '',
    goal:           '',
    program_type:   '',
})

// preview calc (client-side, server recalculates)
const bmiPreview = computed(() => {
    const h = parseFloat(form.height_cm as string)
    const w = parseFloat(form.weight_kg as string)
    if (!h || !w) return null
    return (w / ((h / 100) ** 2)).toFixed(1)
})

const bmiCategory = computed(() => {
    const b = parseFloat(bmiPreview.value ?? '0')
    if (!b) return ''
    if (b < 18.5) return { label: 'Underweight', color: '#60a5fa' }
    if (b < 25)   return { label: 'Normal',      color: '#34d399' }
    if (b < 30)   return { label: 'Overweight',  color: '#fbbf24' }
    return               { label: 'Obese',        color: '#f87171' }
})

const caloriePreview = computed(() => {
    const w = parseFloat(form.weight_kg as string)
    const h = parseFloat(form.height_cm as string)
    const a = parseInt(form.age as string)
    if (!w || !h || !a || !form.gender || !form.activity_level || !form.goal) return null

    let bmr = 10 * w + 6.25 * h - 5 * a
    bmr += form.gender === 'male' ? 5 : -161

    const mult: Record<string, number> = {
        sedentary: 1.2, lightly_active: 1.375, moderately_active: 1.55,
        very_active: 1.725, extra_active: 1.9,
    }
    const adj: Record<string, number> = { lose_weight: -500, maintain: 0, gain_muscle: 300 }

    return Math.round(bmr * (mult[form.activity_level] ?? 1.55) + (adj[form.goal] ?? 0))
})

function nextStep() {
    if (step.value < TOTAL) {
        gsap.to('.fit-ob-card', { x: -40, opacity: 0, duration: 0.25, onComplete: () => {
            step.value++
            gsap.fromTo('.fit-ob-card', { x: 40, opacity: 0 }, { x: 0, opacity: 1, duration: 0.3 })
        }})
    }
}

function prevStep() {
    if (step.value > 1) {
        gsap.to('.fit-ob-card', { x: 40, opacity: 0, duration: 0.25, onComplete: () => {
            step.value--
            gsap.fromTo('.fit-ob-card', { x: -40, opacity: 0 }, { x: 0, opacity: 1, duration: 0.3 })
        }})
    }
}

function submit() {
    form.post('/fitness/onboarding')
}

onMounted(() => {
    gsap.from('.fit-ob-wrap', { opacity: 0, y: 30, duration: 0.6, ease: 'power3.out' })
})
</script>

<template>
    <Head title="FitTrack — Setup" />

    <div class="fit-ob-page">
        <div class="fit-ob-wrap">

            <!-- header -->
            <div class="fit-ob-header">
                <div class="fit-ob-logo">⚡ FitTrack</div>
                <div class="fit-ob-steps">
                    <span
                        v-for="n in TOTAL" :key="n"
                        :class="['fit-ob-dot', { active: n === step, done: n < step }]"
                    ></span>
                </div>
                <p class="fit-ob-step-label">Step {{ step }} of {{ TOTAL }}</p>
            </div>

            <!-- card -->
            <div class="fit-ob-card">

                <!-- STEP 1: Basic Info -->
                <template v-if="step === 1">
                    <h2>Tell us about yourself</h2>
                    <p class="fit-ob-sub">We use this to calculate your BMI and calorie needs.</p>

                    <div class="fit-ob-grid2">
                        <div class="fit-ob-field">
                            <label>Age</label>
                            <input v-model="form.age" type="number" min="13" max="100" placeholder="e.g. 28" />
                        </div>
                        <div class="fit-ob-field">
                            <label>Gender</label>
                            <div class="fit-ob-radio-row">
                                <label :class="['fit-ob-radio', { sel: form.gender === 'male' }]">
                                    <input v-model="form.gender" type="radio" value="male" hidden /> Male
                                </label>
                                <label :class="['fit-ob-radio', { sel: form.gender === 'female' }]">
                                    <input v-model="form.gender" type="radio" value="female" hidden /> Female
                                </label>
                            </div>
                        </div>
                        <div class="fit-ob-field">
                            <label>Height (cm)</label>
                            <input v-model="form.height_cm" type="number" min="100" max="250" placeholder="e.g. 175" />
                        </div>
                        <div class="fit-ob-field">
                            <label>Weight (kg)</label>
                            <input v-model="form.weight_kg" type="number" min="30" max="300" placeholder="e.g. 75" />
                        </div>
                    </div>

                    <!-- BMI preview -->
                    <div v-if="bmiPreview" class="fit-ob-preview">
                        <span>BMI Preview:</span>
                        <strong :style="{ color: bmiCategory?.color }">
                            {{ bmiPreview }} — {{ bmiCategory?.label }}
                        </strong>
                    </div>
                </template>

                <!-- STEP 2: Activity & Goal -->
                <template v-if="step === 2">
                    <h2>Activity & Goal</h2>
                    <p class="fit-ob-sub">Be honest — this shapes your calorie target.</p>

                    <div class="fit-ob-field">
                        <label>Activity Level</label>
                        <select v-model="form.activity_level">
                            <option value="">Select…</option>
                            <option value="sedentary">Sedentary (desk job, little exercise)</option>
                            <option value="lightly_active">Lightly Active (1–3 days/week)</option>
                            <option value="moderately_active">Moderately Active (3–5 days/week)</option>
                            <option value="very_active">Very Active (6–7 days/week)</option>
                            <option value="extra_active">Extra Active (athlete / physical job)</option>
                        </select>
                    </div>

                    <div class="fit-ob-field" style="margin-top:20px">
                        <label>Primary Goal</label>
                        <div class="fit-ob-goal-grid">
                            <label :class="['fit-ob-goal-card', { sel: form.goal === 'lose_weight' }]">
                                <input v-model="form.goal" type="radio" value="lose_weight" hidden />
                                🔥 Lose Weight
                                <span>–500 kcal/day</span>
                            </label>
                            <label :class="['fit-ob-goal-card', { sel: form.goal === 'maintain' }]">
                                <input v-model="form.goal" type="radio" value="maintain" hidden />
                                ⚖️ Maintain
                                <span>At TDEE</span>
                            </label>
                            <label :class="['fit-ob-goal-card', { sel: form.goal === 'gain_muscle' }]">
                                <input v-model="form.goal" type="radio" value="gain_muscle" hidden />
                                💪 Gain Muscle
                                <span>+300 kcal/day</span>
                            </label>
                        </div>
                    </div>

                    <div v-if="caloriePreview" class="fit-ob-preview">
                        <span>Daily Calorie Target:</span>
                        <strong style="color:#34d399">{{ caloriePreview.toLocaleString() }} kcal</strong>
                    </div>
                </template>

                <!-- STEP 3: Program -->
                <template v-if="step === 3">
                    <h2>Choose your program</h2>
                    <p class="fit-ob-sub">Pick the training split that fits your schedule.</p>

                    <div class="fit-ob-program-grid">
                        <label :class="['fit-ob-program-card', { sel: form.program_type === 'PPL' }]">
                            <input v-model="form.program_type" type="radio" value="PPL" hidden />
                            <div class="fit-ob-prog-icon">💥</div>
                            <strong>Push / Pull / Legs</strong>
                            <span class="fit-ob-prog-days">6 days / week</span>
                            <p>Dedicated push, pull, and leg sessions — ideal for intermediate lifters who want maximum volume.</p>
                        </label>
                        <label :class="['fit-ob-program-card', { sel: form.program_type === 'upper_lower' }]">
                            <input v-model="form.program_type" type="radio" value="upper_lower" hidden />
                            <div class="fit-ob-prog-icon">🏋️</div>
                            <strong>Upper / Lower</strong>
                            <span class="fit-ob-prog-days">4 days / week</span>
                            <p>Balanced upper and lower body sessions — perfect for beginners and those with busy schedules.</p>
                        </label>
                    </div>

                    <div v-if="form.errors.program_type" class="fit-ob-error">{{ form.errors.program_type }}</div>
                </template>

            </div>

            <!-- footer nav -->
            <div class="fit-ob-nav">
                <button v-if="step > 1" class="fit-btn-ghost" @click="prevStep">← Back</button>
                <span v-else></span>
                <button
                    v-if="step < TOTAL"
                    class="fit-btn-solid"
                    @click="nextStep"
                    :disabled="
                        (step === 1 && (!form.age || !form.gender || !form.height_cm || !form.weight_kg)) ||
                        (step === 2 && (!form.activity_level || !form.goal))
                    "
                >Next →</button>
                <button
                    v-else
                    class="fit-btn-solid"
                    @click="submit"
                    :disabled="!form.program_type || form.processing"
                >{{ form.processing ? 'Saving…' : 'Start training 🚀' }}</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fit-ob-page {
    min-height: 100vh;
    background: #080f1e;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    font-family: 'Segoe UI', system-ui, sans-serif;
    color: #e2e8f0;
}
.fit-ob-wrap {
    width: 100%;
    max-width: 560px;
}
.fit-ob-header { text-align: center; margin-bottom: 32px; }
.fit-ob-logo { font-size: 1.3rem; font-weight: 700; color: #34d399; margin-bottom: 20px; }
.fit-ob-steps { display: flex; gap: 8px; justify-content: center; margin-bottom: 8px; }
.fit-ob-dot {
    width: 32px; height: 6px; border-radius: 3px;
    background: #1a2d4a; transition: background 0.3s;
}
.fit-ob-dot.active { background: #34d399; }
.fit-ob-dot.done   { background: #065f46; }
.fit-ob-step-label { font-size: 0.8rem; color: #475569; }

.fit-ob-card {
    background: #0d1a2e;
    border: 1px solid #1a2d4a;
    border-radius: 20px;
    padding: 36px 32px;
    margin-bottom: 20px;
}
.fit-ob-card h2 { font-size: 1.4rem; font-weight: 700; margin-bottom: 6px; }
.fit-ob-sub     { color: #64748b; font-size: 0.9rem; margin-bottom: 28px; }

.fit-ob-grid2   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.fit-ob-field   { display: flex; flex-direction: column; gap: 8px; }
.fit-ob-field label { font-size: 0.82rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }

input[type=number], select {
    background: #0a1525;
    border: 1px solid #1e3a5f;
    border-radius: 10px;
    color: #e2e8f0;
    padding: 12px 14px;
    font-size: 0.95rem;
    width: 100%;
    outline: none;
    transition: border-color 0.2s;
}
input:focus, select:focus { border-color: #34d399; }
select option { background: #0d1a2e; }

.fit-ob-radio-row { display: flex; gap: 10px; }
.fit-ob-radio {
    flex: 1; text-align: center; padding: 12px;
    border: 1px solid #1e3a5f; border-radius: 10px; cursor: pointer;
    font-size: 0.9rem; transition: all 0.2s;
}
.fit-ob-radio.sel { border-color: #34d399; background: rgba(52,211,153,0.08); color: #34d399; }

.fit-ob-preview {
    margin-top: 20px;
    background: rgba(52,211,153,0.06);
    border: 1px solid rgba(52,211,153,0.2);
    border-radius: 10px;
    padding: 12px 16px;
    font-size: 0.9rem;
    color: #94a3b8;
    display: flex;
    gap: 10px;
    align-items: center;
}

.fit-ob-goal-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; }
.fit-ob-goal-card {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 4px; padding: 16px 8px; border: 1px solid #1e3a5f; border-radius: 12px;
    cursor: pointer; font-size: 0.9rem; font-weight: 600; text-align: center;
    transition: all 0.2s;
}
.fit-ob-goal-card span { font-size: 0.72rem; color: #64748b; font-weight: 400; }
.fit-ob-goal-card.sel  { border-color: #34d399; background: rgba(52,211,153,0.08); color: #34d399; }

.fit-ob-program-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.fit-ob-program-card {
    display: flex; flex-direction: column; align-items: center; text-align: center;
    gap: 8px; padding: 24px 16px; border: 1px solid #1e3a5f; border-radius: 14px;
    cursor: pointer; transition: all 0.2s;
}
.fit-ob-program-card.sel { border-color: #34d399; background: rgba(52,211,153,0.07); }
.fit-ob-prog-icon  { font-size: 2rem; }
.fit-ob-prog-days  { font-size: 0.75rem; color: #34d399; background: rgba(52,211,153,0.1); padding: 3px 10px; border-radius: 20px; }
.fit-ob-program-card p { font-size: 0.8rem; color: #64748b; line-height: 1.5; margin-top: 4px; }

.fit-ob-error { color: #f87171; font-size: 0.85rem; margin-top: 8px; }

.fit-ob-nav {
    display: flex; justify-content: space-between; align-items: center;
}
.fit-btn-ghost {
    padding: 12px 24px; border: 1px solid #2d4a6e; border-radius: 10px;
    color: #94a3b8; font-size: 0.95rem; cursor: pointer; background: transparent;
    transition: border-color 0.2s, color 0.2s;
}
.fit-btn-ghost:hover { border-color: #34d399; color: #34d399; }
.fit-btn-solid {
    padding: 12px 28px; background: #34d399; border-radius: 10px; border: none;
    color: #080f1e; font-size: 0.95rem; font-weight: 700; cursor: pointer;
    transition: background 0.2s; text-decoration: none; display: inline-block;
}
.fit-btn-solid:hover   { background: #10b981; }
.fit-btn-solid:disabled { opacity: 0.4; cursor: not-allowed; }

@media (max-width: 500px) {
    .fit-ob-grid2, .fit-ob-goal-grid, .fit-ob-program-grid { grid-template-columns: 1fr; }
    .fit-ob-card { padding: 24px 18px; }
}
</style>
