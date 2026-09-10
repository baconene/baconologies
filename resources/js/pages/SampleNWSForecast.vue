<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import gsap from 'gsap'
import {
    Chart,
    LineController, BarController,
    LineElement, BarElement, PointElement,
    LinearScale, CategoryScale,
    Filler, Tooltip,
} from 'chart.js'

Chart.register(
    LineController, BarController,
    LineElement, BarElement, PointElement,
    LinearScale, CategoryScale,
    Filler, Tooltip,
)

// ── types ──────────────────────────────────────────────────────────────
interface Period {
    startTime: string
    name: string
    temperature: number
    temperatureUnit: string
    windSpeed: string
    windDirection: string
    shortForecast: string
    isDaytime: boolean
    probabilityOfPrecipitation?: { value: number | null }
    relativeHumidity?: { value: number | null }
    visibility?: { value: number | null }
}

// ── state ──────────────────────────────────────────────────────────────
const loading    = ref(true)
const error      = ref('')
const updated    = ref('')
const statTemp   = ref('—')
const statDesc   = ref('—')
const statWind   = ref('—')
const statWindDir = ref('—')
const statHumidity = ref('—')
const statPrecip = ref('—')
const statVis    = ref('—')
const dailyCards = ref<Period[]>([])
const alerts     = ref<{ headline: string; description: string }[]>([])

const tempCanvas   = ref<HTMLCanvasElement | null>(null)
const precipCanvas = ref<HTMLCanvasElement | null>(null)
const windCanvas   = ref<HTMLCanvasElement | null>(null)

// ── helpers ────────────────────────────────────────────────────────────
const LAT = 39.9681
const LON = -86.1125
const HEADERS = {
    'User-Agent': '(baconologies weather page, john.adrian.bacon@gmail.com)',
    'Accept': 'application/geo+json',
}

async function apiFetch(url: string) {
    const res = await fetch(url, { headers: HEADERS })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    return res.json()
}

function weatherIcon(forecast = '', daytime = true) {
    const s = forecast.toLowerCase()
    if (s.includes('thunder'))                          return '⛈'
    if (s.includes('snow') || s.includes('blizzard'))  return '❄️'
    if (s.includes('sleet') || s.includes('freezing')) return '🌨'
    if (s.includes('rain') || s.includes('shower'))    return '🌧'
    if (s.includes('drizzle'))                         return '🌦'
    if (s.includes('fog') || s.includes('mist'))       return '🌫'
    if (s.includes('cloud') || s.includes('overcast')) return daytime ? '⛅' : '🌑'
    if (s.includes('partly'))                          return daytime ? '⛅' : '🌤'
    if (s.includes('sunny') || s.includes('clear'))    return daytime ? '☀️' : '🌙'
    if (s.includes('wind'))                            return '💨'
    return daytime ? '🌤' : '🌙'
}

function fmtHour(iso: string) {
    return new Date(iso).toLocaleTimeString([], { hour: 'numeric', hour12: true })
}

function makeLine(
    canvas: HTMLCanvasElement,
    labels: string[],
    data: number[],
    color: string,
    fill: string,
    suffix: string,
) {
    return new Chart(canvas, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data,
                borderColor: color,
                backgroundColor: fill,
                borderWidth: 2.5,
                pointRadius: 3,
                pointBackgroundColor: color,
                fill: true,
                tension: 0.4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 0 },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y}${suffix}` } },
            },
            scales: {
                x: { grid: { color: '#1e3a5f' }, ticks: { maxRotation: 45, font: { size: 11 }, color: '#7a9bbf' } },
                y: { grid: { color: '#1e3a5f' }, ticks: { font: { size: 11 }, color: '#7a9bbf' } },
            },
        },
    })
}

function makeBar(canvas: HTMLCanvasElement, labels: string[], data: number[]) {
    return new Chart(canvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: data.map(v =>
                    v >= 70 ? 'rgba(100,181,246,0.85)' :
                    v >= 40 ? 'rgba(100,181,246,0.55)' :
                              'rgba(100,181,246,0.25)',
                ),
                borderColor: 'rgba(100,181,246,0.8)',
                borderWidth: 1,
                borderRadius: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 0 },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y}%` } },
            },
            scales: {
                x: { grid: { color: '#1e3a5f' }, ticks: { maxRotation: 45, font: { size: 11 }, color: '#7a9bbf' } },
                y: {
                    min: 0, max: 100,
                    grid: { color: '#1e3a5f' },
                    ticks: { font: { size: 11 }, color: '#7a9bbf', callback: v => `${v}%` },
                },
            },
        },
    })
}

// ── main load ──────────────────────────────────────────────────────────
async function loadForecast() {
    try {
        const points = await apiFetch(`https://api.weather.gov/points/${LAT},${LON}`)
        const { forecast: forecastUrl, forecastHourly: hourlyUrl } = points.properties

        const [daily, hourly] = await Promise.all([
            apiFetch(forecastUrl),
            apiFetch(hourlyUrl),
        ])

        const periods: Period[]       = daily.properties.periods
        const hourlyPeriods: Period[] = hourly.properties.periods

        loading.value = false

        updated.value = 'Updated ' + new Date(daily.properties.generatedAt)
            .toLocaleString([], { dateStyle: 'short', timeStyle: 'short' })

        const now  = periods[0]
        const nowH = hourlyPeriods[0]

        statTemp.value    = `${now.temperature}°`
        statDesc.value    = now.shortForecast
        statWind.value    = now.windSpeed
        statWindDir.value = now.windDirection
        statPrecip.value  = `${now.probabilityOfPrecipitation?.value ?? 0}%`

        const hum = nowH?.relativeHumidity?.value
        statHumidity.value = hum != null ? `${Math.round(hum)}%` : '—'

        const vis = nowH?.visibility?.value
        statVis.value = vis != null ? (vis / 1609.34).toFixed(1) : '—'

        // 7-day cards
        const dayPs = periods.filter(p => p.isDaytime).slice(0, 7)
        dailyCards.value = dayPs.length >= 4 ? dayPs : periods.slice(0, 7)

        // hourly chart data
        const next24  = hourlyPeriods.slice(0, 24)
        const labels  = next24.map(p => fmtHour(p.startTime))
        const temps   = next24.map(p => p.temperature)
        const precips = next24.map(p => p.probabilityOfPrecipitation?.value ?? 0)
        const winds   = next24.map(p => {
            const m = (p.windSpeed ?? '').match(/(\d+)/)
            return m ? parseInt(m[1]) : 0
        })

        // wait a tick for canvases to mount
        await new Promise(r => setTimeout(r, 50))

        if (tempCanvas.value)   makeLine(tempCanvas.value,   labels, temps,   '#ff8a65', 'rgba(255,138,101,0.15)', '°F')
        if (precipCanvas.value) makeBar(precipCanvas.value,  labels, precips)
        if (windCanvas.value)   makeLine(windCanvas.value,   labels, winds,   '#4fc3f7', 'rgba(79,195,247,0.12)',  ' mph')

        // alerts
        try {
            const alertData = await apiFetch(`https://api.weather.gov/alerts/active?point=${LAT},${LON}`)
            alerts.value = (alertData.features ?? []).map((a: any) => ({
                headline:    a.properties.headline,
                description: a.properties.description?.slice(0, 200) ?? '',
            }))
        } catch { /* alerts optional */ }

        // GSAP
        const tl = gsap.timeline({ defaults: { ease: 'power3.out' } })
        tl.from('#nws-header',    { opacity: 0, y: -30, duration: 0.7 })
          .from('#nws-strip',     { opacity: 0, y: 20,  duration: 0.6 }, '-=0.3')
          .from('#nws-sec-temp',  { opacity: 0, y: 24,  duration: 0.6 }, '-=0.2')
          .from('#nws-sec-precip',{ opacity: 0, y: 24,  duration: 0.6 }, '-=0.3')
          .from('#nws-sec-wind',  { opacity: 0, y: 24,  duration: 0.6 }, '-=0.3')
          .from('#nws-daily',     { opacity: 0, y: 24,  duration: 0.6 }, '-=0.3')

        gsap.from('.nws-stat-card', { opacity: 0, y: 16, duration: 0.4, stagger: 0.08, ease: 'power2.out', delay: 0.6 })
        gsap.from('.nws-day-card',  { opacity: 0, scale: 0.88, duration: 0.45, stagger: 0.06, ease: 'back.out(1.4)', delay: 1.2 })

    } catch (e: any) {
        loading.value = false
        error.value = e.message
    }
}

function tempColor(f: number) { return f >= 80 ? 'hot' : 'cool' }

onMounted(loadForecast)
</script>

<template>
    <Head title="NWS Forecast — Indianapolis" />

    <div class="nws-page">

        <!-- HEADER -->
        <header id="nws-header" class="nws-header">
            <h1>📡 NWS Forecast</h1>
            <p>Indianapolis, IN &nbsp;·&nbsp; 39.9681°N, 86.1125°W</p>
            <p v-if="updated" class="nws-muted" style="font-size:0.8rem;margin-top:4px">{{ updated }}</p>
        </header>

        <main class="nws-main">

            <!-- LOADING -->
            <div v-if="loading" class="nws-loading">
                <div class="nws-spinner"></div>
                <p class="nws-muted">Fetching forecast data…</p>
            </div>

            <!-- ERROR -->
            <div v-if="error" class="nws-error">
                <strong>⚠ Could not load forecast</strong>
                <p style="margin-top:8px;font-size:0.85rem">{{ error }}</p>
            </div>

            <template v-if="!loading && !error">

                <!-- ALERTS -->
                <div v-if="alerts.length" class="nws-alert-banner">
                    <div v-for="(a, i) in alerts" :key="i">
                        <strong>⚠ {{ a.headline }}</strong>
                        <p style="font-size:0.82rem;margin-top:4px">{{ a.description }}…</p>
                        <hr v-if="i < alerts.length - 1" class="nws-alert-hr" />
                    </div>
                </div>

                <!-- CURRENT STRIP -->
                <div id="nws-strip" class="nws-strip">
                    <div class="nws-stat-card">
                        <div class="nws-label">Now</div>
                        <div class="nws-value">{{ statTemp }}</div>
                        <div class="nws-sub">{{ statDesc }}</div>
                    </div>
                    <div class="nws-stat-card">
                        <div class="nws-label">Wind</div>
                        <div class="nws-value">{{ statWind }}</div>
                        <div class="nws-sub">{{ statWindDir }}</div>
                    </div>
                    <div class="nws-stat-card">
                        <div class="nws-label">Humidity</div>
                        <div class="nws-value">{{ statHumidity }}</div>
                        <div class="nws-sub">relative</div>
                    </div>
                    <div class="nws-stat-card">
                        <div class="nws-label">Precip chance</div>
                        <div class="nws-value">{{ statPrecip }}</div>
                        <div class="nws-sub">next period</div>
                    </div>
                    <div class="nws-stat-card">
                        <div class="nws-label">Visibility</div>
                        <div class="nws-value">{{ statVis }}</div>
                        <div class="nws-sub">miles</div>
                    </div>
                </div>

                <!-- TEMPERATURE CHART -->
                <div id="nws-sec-temp" class="nws-chart-section">
                    <div class="nws-chart-card">
                        <h2>🌡 Temperature — Next 24 hrs (°F)</h2>
                        <div class="nws-chart-wrap">
                            <canvas ref="tempCanvas"></canvas>
                        </div>
                    </div>
                </div>

                <!-- PRECIP CHART -->
                <div id="nws-sec-precip" class="nws-chart-section">
                    <div class="nws-chart-card">
                        <h2>🌧 Precipitation Probability — Next 24 hrs (%)</h2>
                        <div class="nws-chart-wrap">
                            <canvas ref="precipCanvas"></canvas>
                        </div>
                    </div>
                </div>

                <!-- WIND CHART -->
                <div id="nws-sec-wind" class="nws-chart-section">
                    <div class="nws-chart-card">
                        <h2>💨 Wind Speed — Next 24 hrs (mph)</h2>
                        <div class="nws-chart-wrap">
                            <canvas ref="windCanvas"></canvas>
                        </div>
                    </div>
                </div>

                <!-- 7-DAY -->
                <div id="nws-daily">
                    <h2 class="nws-section-title">📅 7-Day Forecast</h2>
                    <div class="nws-daily-grid">
                        <div
                            v-for="p in dailyCards"
                            :key="p.name"
                            class="nws-day-card"
                        >
                            <div class="nws-day-name">{{ p.name }}</div>
                            <div class="nws-day-icon">{{ weatherIcon(p.shortForecast, p.isDaytime) }}</div>
                            <div :class="['nws-day-temp', tempColor(p.temperature)]">
                                {{ p.temperature }}°{{ p.temperatureUnit }}
                            </div>
                            <div v-if="(p.probabilityOfPrecipitation?.value ?? 0) > 0" class="nws-day-wind">
                                💧 {{ p.probabilityOfPrecipitation?.value }}%
                            </div>
                            <div class="nws-day-wind">{{ p.windSpeed }} {{ p.windDirection }}</div>
                            <div class="nws-day-short">{{ p.shortForecast }}</div>
                        </div>
                    </div>
                </div>

            </template>
        </main>
    </div>
</template>

<style scoped>
.nws-page {
    min-height: 100vh;
    background: #0a1628;
    color: #e0f0ff;
    font-family: 'Segoe UI', system-ui, sans-serif;
}

.nws-header {
    padding: 48px 24px 24px;
    text-align: center;
}
.nws-header h1 {
    font-size: clamp(1.6rem, 4vw, 2.8rem);
    font-weight: 700;
    color: #4fc3f7;
    letter-spacing: -0.5px;
}
.nws-header p { margin-top: 6px; color: #7a9bbf; font-size: 0.95rem; }

.nws-main {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 20px 60px;
}

.nws-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    padding: 80px 24px;
}
.nws-spinner {
    width: 48px; height: 48px;
    border: 4px solid #1e3a5f;
    border-top-color: #4fc3f7;
    border-radius: 50%;
    animation: nws-spin 0.8s linear infinite;
}
@keyframes nws-spin { to { transform: rotate(360deg); } }

.nws-muted { color: #7a9bbf; }

.nws-error {
    max-width: 480px;
    margin: 40px auto;
    background: #2d1515;
    border: 1px solid #7b3535;
    border-radius: 16px;
    padding: 24px;
    text-align: center;
    color: #ff8a8a;
}

.nws-alert-banner {
    background: #2a1500;
    border: 1px solid #ff8a00;
    border-radius: 16px;
    padding: 16px 20px;
    color: #ffcc80;
    font-size: 0.9rem;
    margin-bottom: 28px;
}
.nws-alert-hr { border-color: #7b3535; margin: 10px 0; }

/* stat strip */
.nws-strip {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: center;
    margin-bottom: 32px;
}
.nws-stat-card {
    flex: 1 1 140px;
    max-width: 200px;
    background: #0d1f3c;
    border: 1px solid #1e3a5f;
    border-radius: 16px;
    padding: 20px 16px;
    text-align: center;
}
.nws-label {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #7a9bbf;
    margin-bottom: 8px;
}
.nws-value { font-size: 1.8rem; font-weight: 700; line-height: 1; }
.nws-sub   { font-size: 0.8rem; color: #7a9bbf; margin-top: 4px; }

/* charts */
.nws-chart-section { margin-bottom: 28px; }
.nws-chart-card {
    background: #0d1f3c;
    border: 1px solid #1e3a5f;
    border-radius: 16px;
    padding: 24px;
}
.nws-chart-card h2 {
    font-size: 1rem;
    font-weight: 600;
    color: #4fc3f7;
    margin-bottom: 18px;
}
.nws-chart-wrap { position: relative; height: 220px; }

/* daily */
.nws-section-title {
    font-size: 1rem;
    font-weight: 600;
    color: #4fc3f7;
    margin-bottom: 14px;
}
.nws-daily-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 10px;
}
.nws-day-card {
    background: #0d1f3c;
    border: 1px solid #1e3a5f;
    border-radius: 16px;
    padding: 16px 12px;
    text-align: center;
    transition: border-color 0.2s, transform 0.2s;
    cursor: default;
}
.nws-day-card:hover { border-color: #4fc3f7; transform: translateY(-3px); }
.nws-day-name {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #7a9bbf;
    margin-bottom: 10px;
}
.nws-day-icon  { font-size: 2rem; margin-bottom: 8px; line-height: 1; }
.nws-day-temp  { font-size: 1.4rem; font-weight: 700; }
.nws-day-temp.hot  { color: #ff8a65; }
.nws-day-temp.cool { color: #64b5f6; }
.nws-day-wind  { font-size: 0.72rem; color: #7a9bbf; margin-top: 6px; }
.nws-day-short { font-size: 0.72rem; color: #7a9bbf; margin-top: 4px; line-height: 1.3; }

@media (max-width: 600px) {
    .nws-chart-wrap { height: 180px; }
}
</style>
