<script setup lang="ts">
import { ref, onBeforeUnmount } from 'vue'
import { Head } from '@inertiajs/vue3'
import * as pdfjsLib from 'pdfjs-dist'
import {
    Chart,
    DoughnutController, BarController,
    ArcElement, BarElement,
    LinearScale, CategoryScale,
    Tooltip, Legend,
} from 'chart.js'

Chart.register(DoughnutController, BarController, ArcElement, BarElement, LinearScale, CategoryScale, Tooltip, Legend)

pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
    'pdfjs-dist/build/pdf.worker.min.mjs',
    import.meta.url,
).toString()

// ── Types ───────────────────────────────────────────────────────────────────
interface Account {
    id: string
    name: string
    cls: string
    sas: number
    billDate: string
    dueDate: string
    balance: number
    freezeStatus: string
}

interface Change {
    a: Account
    b: Account
    delta: number
}

interface Results {
    acc1: Record<string, Account>
    acc2: Record<string, Account>
    changed: Change[]
    removed: Account[]
    added: Account[]
    unchanged: Account[]
    bal1: number
    bal2: number
}

// ── State ───────────────────────────────────────────────────────────────────
const file1 = ref<File | null>(null)
const file2 = ref<File | null>(null)
const prog1 = ref(0)
const prog2 = ref(0)
const loading = ref(false)
const results = ref<Results | null>(null)
const activeTab = ref<'overview' | 'changes' | 'removed' | 'added' | 'unchanged'>('overview')
const search = ref({ changes: '', removed: '', added: '', unchanged: '' })

const chartInstances: Chart[] = []
const canvasBreakdown = ref<HTMLCanvasElement | null>(null)
const canvasBalance   = ref<HTMLCanvasElement | null>(null)
const canvasFreeze    = ref<HTMLCanvasElement | null>(null)
const canvasTop       = ref<HTMLCanvasElement | null>(null)

// ── File handling ────────────────────────────────────────────────────────────
function onFile(e: Event, n: 1 | 2) {
    const f = (e.target as HTMLInputElement).files?.[0]
    if (!f) return
    if (n === 1) file1.value = f; else file2.value = f
}
function onDrop(e: DragEvent, n: 1 | 2) {
    e.preventDefault()
    const f = e.dataTransfer?.files[0]
    if (!f) return
    if (n === 1) file1.value = f; else file2.value = f
}

// ── PDF extraction ───────────────────────────────────────────────────────────
async function extractText(file: File, onProgress: (p: number) => void): Promise<string> {
    const ab = await file.arrayBuffer()
    const pdf = await pdfjsLib.getDocument({ data: ab }).promise
    const pages: string[] = []
    for (let i = 1; i <= pdf.numPages; i++) {
        const page = await pdf.getPage(i)
        const content = await page.getTextContent()
        pages.push(content.items.map((it: any) => it.str).join(' '))
        onProgress(i / pdf.numPages)
    }
    return pages.join('\n')
}

// ── Normalization ────────────────────────────────────────────────────────────
function normalize(raw: string): string {
    return raw
        .replace(/FREEZAB\s*LE\s*\/\s*PENDI\s*NG/gi, 'FREEZABLE/PENDING')
        .replace(/(\d{2})-\s+(\d{2})-\s+(\d{4})/g, '$1-$2-$3')
        .replace(/\n/g, ' ')
        .replace(/\s{2,}/g, ' ')
}

// ── Parsing ──────────────────────────────────────────────────────────────────
function parseAccounts(raw: string): Record<string, Account> {
    const text = normalize(raw)
    const accounts: Record<string, Account> = {}
    const re = /\b(\d{10})\b\s+([\w,'\-. ]+?)\s+(RES|COM)\s+(\d{1,2})\s+(\d{2}-\d{2}-\d{4})\s+(\d{2}-\d{2}-\d{4})\s+([\d,]+\.?\d*)\s+(FROZEN|FREEZABLE\/PENDING)/g
    let m: RegExpExecArray | null
    while ((m = re.exec(text)) !== null) {
        const id = m[1]
        if (accounts[id]) continue
        accounts[id] = {
            id,
            name: m[2].trim(),
            cls: m[3],
            sas: parseInt(m[4], 10),
            billDate: m[5],
            dueDate: m[6],
            balance: parseFloat(m[7].replace(/,/g, '')) || 0,
            freezeStatus: m[8],
        }
    }
    return accounts
}

// ── Comparison ───────────────────────────────────────────────────────────────
function compare(acc1: Record<string, Account>, acc2: Record<string, Account>): Omit<Results, 'acc1' | 'acc2' | 'bal1' | 'bal2'> {
    const ids1 = new Set(Object.keys(acc1))
    const ids2 = new Set(Object.keys(acc2))
    const removed = [...ids1].filter(id => !ids2.has(id)).map(id => acc1[id])
    const added   = [...ids2].filter(id => !ids1.has(id)).map(id => acc2[id])
    const changed: Change[] = []
    const unchanged: Account[] = []
    for (const id of ids1) {
        if (!ids2.has(id)) continue
        const a = acc1[id], b = acc2[id]
        if (a.balance !== b.balance || a.freezeStatus !== b.freezeStatus || a.sas !== b.sas) {
            changed.push({ a, b, delta: b.balance - a.balance })
        } else {
            unchanged.push(a)
        }
    }
    changed.sort((x, y) => Math.abs(y.delta) - Math.abs(x.delta))
    return { changed, removed, added, unchanged }
}

// ── Run ───────────────────────────────────────────────────────────────────────
async function run() {
    if (!file1.value || !file2.value) return
    loading.value = true
    results.value = null
    try {
        const [raw1, raw2] = await Promise.all([
            extractText(file1.value, p => { prog1.value = p }),
            extractText(file2.value, p => { prog2.value = p }),
        ])
        const acc1 = parseAccounts(raw1)
        const acc2 = parseAccounts(raw2)
        const bal1 = Object.values(acc1).reduce((s, a) => s + a.balance, 0)
        const bal2 = Object.values(acc2).reduce((s, a) => s + a.balance, 0)
        results.value = { acc1, acc2, bal1, bal2, ...compare(acc1, acc2) }
        activeTab.value = 'overview'
        await new Promise(r => setTimeout(r, 80))
        renderCharts()
    } finally {
        loading.value = false
    }
}

// ── Charts ────────────────────────────────────────────────────────────────────
function renderCharts() {
    chartInstances.forEach(c => c.destroy())
    chartInstances.length = 0
    const r = results.value!

    const DARK = '#8892b0'
    const GRID = '#2e3350'

    if (canvasBreakdown.value) {
        chartInstances.push(new Chart(canvasBreakdown.value, {
            type: 'doughnut',
            data: {
                labels: ['Unchanged', 'Changed', 'Removed', 'New'],
                datasets: [{ data: [r.unchanged.length, r.changed.length, r.removed.length, r.added.length],
                    backgroundColor: ['#22c55e', '#eab308', '#4f8ef7', '#ef4444'], borderWidth: 0 }]
            },
            options: { cutout: '65%', plugins: { legend: { labels: { color: DARK } } } }
        }))
    }

    if (canvasBalance.value) {
        chartInstances.push(new Chart(canvasBalance.value, {
            type: 'bar',
            data: {
                labels: ['PDF #1', 'PDF #2'],
                datasets: [{ label: 'Balance ($)', data: [r.bal1, r.bal2],
                    backgroundColor: ['#4f8ef7', '#7c3aed'], borderRadius: 6 }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { ticks: { color: DARK, callback: (v: any) => '$' + Number(v).toLocaleString() }, grid: { color: GRID } },
                    x: { ticks: { color: DARK }, grid: { display: false } }
                }
            }
        }))
    }

    if (canvasFreeze.value) {
        const countF = (acc: Record<string, Account>, s: string) => Object.values(acc).filter(a => a.freezeStatus === s).length
        chartInstances.push(new Chart(canvasFreeze.value, {
            type: 'bar',
            data: {
                labels: ['FROZEN', 'FREEZABLE/PENDING'],
                datasets: [
                    { label: 'PDF #1', data: [countF(r.acc1, 'FROZEN'), countF(r.acc1, 'FREEZABLE/PENDING')], backgroundColor: '#4f8ef7', borderRadius: 4 },
                    { label: 'PDF #2', data: [countF(r.acc2, 'FROZEN'), countF(r.acc2, 'FREEZABLE/PENDING')], backgroundColor: '#7c3aed', borderRadius: 4 },
                ]
            },
            options: {
                plugins: { legend: { labels: { color: DARK } } },
                scales: {
                    y: { ticks: { color: DARK }, grid: { color: GRID } },
                    x: { ticks: { color: DARK }, grid: { display: false } }
                }
            }
        }))
    }

    if (canvasTop.value) {
        const top = r.changed.slice(0, 10)
        chartInstances.push(new Chart(canvasTop.value, {
            type: 'bar',
            data: {
                labels: top.map(c => c.a.id),
                datasets: [{ label: 'Δ Balance ($)', data: top.map(c => c.delta),
                    backgroundColor: top.map(c => c.delta > 0 ? '#ef4444' : '#22c55e'), borderRadius: 4 }]
            },
            options: {
                indexAxis: 'y' as const,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: DARK, callback: (v: any) => '$' + Number(v).toLocaleString() }, grid: { color: GRID } },
                    y: { ticks: { color: DARK, font: { size: 10 } }, grid: { display: false } }
                }
            }
        }))
    }
}

onBeforeUnmount(() => chartInstances.forEach(c => c.destroy()))

// ── Helpers ───────────────────────────────────────────────────────────────────
function fmt$(v: number) {
    const sign = v > 0 ? '+' : ''
    return sign + '$' + v.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
function fmtAbs$(v: number) {
    return '$' + Math.abs(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}
function filtered(list: Account[], key: keyof typeof search.value) {
    const q = search.value[key].toLowerCase()
    if (!q) return list
    return list.filter(a =>
        a.id.includes(q) || a.name.toLowerCase().includes(q) || a.cls.toLowerCase().includes(q)
    )
}
function filteredChanges() {
    const q = search.value.changes.toLowerCase()
    if (!q || !results.value) return results.value?.changed ?? []
    return results.value.changed.filter(c =>
        c.a.id.includes(q) || c.a.name.toLowerCase().includes(q)
    )
}

const tabs = [
    { key: 'overview',   label: 'Overview' },
    { key: 'changes',    label: 'Changed' },
    { key: 'removed',    label: 'Removed / Resolved' },
    { key: 'added',      label: 'New Accounts' },
    { key: 'unchanged',  label: 'Unchanged' },
] as const
</script>

<template>
    <Head title="Write-Off Forecast Comparator" />

    <div class="wof-page">
        <!-- Header -->
        <header class="wof-header">
            <h1>Write-Off Forecast Comparator</h1>
            <span class="wof-tag">PDF ANALYSIS</span>
        </header>

        <main class="wof-main">
            <!-- Upload -->
            <div class="wof-upload-grid">
                <label
                    class="wof-drop"
                    :class="{ loaded: file1 }"
                    @dragover.prevent
                    @drop="onDrop($event, 1)"
                >
                    <input type="file" accept=".pdf" @change="onFile($event, 1)" />
                    <span class="wof-drop-icon">📄</span>
                    <div class="wof-drop-label">PDF #1 — Baseline Report<br /><small>Drag &amp; drop or click</small></div>
                    <div v-if="file1" class="wof-drop-name">📎 {{ file1.name }}</div>
                    <div v-if="prog1 > 0 && prog1 < 1" class="wof-progress">
                        <div class="wof-progress-fill" :style="{ width: (prog1 * 100) + '%' }"></div>
                    </div>
                </label>
                <label
                    class="wof-drop"
                    :class="{ loaded: file2 }"
                    @dragover.prevent
                    @drop="onDrop($event, 2)"
                >
                    <input type="file" accept=".pdf" @change="onFile($event, 2)" />
                    <span class="wof-drop-icon">📄</span>
                    <div class="wof-drop-label">PDF #2 — Comparison Report<br /><small>Drag &amp; drop or click</small></div>
                    <div v-if="file2" class="wof-drop-name">📎 {{ file2.name }}</div>
                    <div v-if="prog2 > 0 && prog2 < 1" class="wof-progress">
                        <div class="wof-progress-fill" :style="{ width: (prog2 * 100) + '%' }"></div>
                    </div>
                </label>
            </div>

            <button
                class="wof-btn"
                :disabled="!file1 || !file2 || loading"
                @click="run"
            >
                {{ loading ? '⏳ Processing…' : '⚡ Compare Reports' }}
            </button>

            <!-- Results -->
            <template v-if="results">
                <!-- Metrics -->
                <div class="wof-metrics">
                    <div class="wof-metric blue">
                        <div class="wof-metric-val">{{ Object.keys(results.acc1).length.toLocaleString() }}</div>
                        <div class="wof-metric-lbl">Accounts PDF #1</div>
                    </div>
                    <div class="wof-metric blue">
                        <div class="wof-metric-val">{{ Object.keys(results.acc2).length.toLocaleString() }}</div>
                        <div class="wof-metric-lbl">Accounts PDF #2</div>
                    </div>
                    <div class="wof-metric" :class="(results.changed.length + results.removed.length + results.added.length) > 0 ? 'yellow' : 'green'">
                        <div class="wof-metric-val">{{ (results.changed.length + results.removed.length + results.added.length).toLocaleString() }}</div>
                        <div class="wof-metric-lbl">Total Differences</div>
                    </div>
                    <div class="wof-metric yellow">
                        <div class="wof-metric-val">{{ results.changed.length.toLocaleString() }}</div>
                        <div class="wof-metric-lbl">Changed</div>
                    </div>
                    <div class="wof-metric green">
                        <div class="wof-metric-val">{{ results.removed.length.toLocaleString() }}</div>
                        <div class="wof-metric-lbl">Removed / Resolved</div>
                    </div>
                    <div class="wof-metric red">
                        <div class="wof-metric-val">{{ results.added.length.toLocaleString() }}</div>
                        <div class="wof-metric-lbl">New Accounts</div>
                    </div>
                    <div class="wof-metric muted">
                        <div class="wof-metric-val">{{ results.unchanged.length.toLocaleString() }}</div>
                        <div class="wof-metric-lbl">Unchanged</div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="wof-tab-bar">
                    <button
                        v-for="t in tabs"
                        :key="t.key"
                        class="wof-tab"
                        :class="{ active: activeTab === t.key }"
                        @click="activeTab = t.key"
                    >{{ t.label }}</button>
                </div>

                <!-- Overview -->
                <div v-if="activeTab === 'overview'">
                    <div class="wof-charts-grid">
                        <div class="wof-chart-card">
                            <h3>Account Breakdown</h3>
                            <canvas ref="canvasBreakdown" style="max-height:260px"></canvas>
                        </div>
                        <div class="wof-chart-card">
                            <h3>Total Outstanding Balance</h3>
                            <canvas ref="canvasBalance" style="max-height:260px"></canvas>
                        </div>
                        <div class="wof-chart-card">
                            <h3>Freeze Status Distribution</h3>
                            <canvas ref="canvasFreeze" style="max-height:260px"></canvas>
                        </div>
                        <div class="wof-chart-card">
                            <h3>Top 10 Balance Changes</h3>
                            <canvas ref="canvasTop" style="max-height:260px"></canvas>
                        </div>
                    </div>

                    <!-- Insight cards -->
                    <div class="wof-insights">
                        <div class="wof-insight">
                            <div class="wof-insight-title">Balance Delta (PDF1 → PDF2)</div>
                            <div class="wof-insight-val" :class="results.bal2 - results.bal1 <= 0 ? 'green' : 'red'">
                                {{ fmt$(results.bal2 - results.bal1) }}
                            </div>
                            <div class="wof-insight-sub">{{ results.bal2 - results.bal1 <= 0 ? 'Exposure reduced' : 'Exposure increased' }}</div>
                        </div>
                        <div class="wof-insight">
                            <div class="wof-insight-title">Balance Recovered (Removed)</div>
                            <div class="wof-insight-val green">
                                {{ fmtAbs$(results.removed.reduce((s,a) => s + a.balance, 0)) }}
                            </div>
                            <div class="wof-insight-sub">{{ results.removed.length }} accounts resolved</div>
                        </div>
                        <div class="wof-insight">
                            <div class="wof-insight-title">Frozen Accounts Removed</div>
                            <div class="wof-insight-val green">
                                {{ results.removed.filter(a => a.freezeStatus === 'FROZEN').length }}
                            </div>
                            <div class="wof-insight-sub">from PDF #1 → not in PDF #2</div>
                        </div>
                        <div class="wof-insight">
                            <div class="wof-insight-title">New Accounts Exposure</div>
                            <div class="wof-insight-val red">
                                {{ fmtAbs$(results.added.reduce((s,a) => s + a.balance, 0)) }}
                            </div>
                            <div class="wof-insight-sub">{{ results.added.length }} new accounts in PDF #2</div>
                        </div>
                        <template v-if="results.changed.length">
                            <div class="wof-insight">
                                <div class="wof-insight-title">Largest Balance Increase</div>
                                <div class="wof-insight-val red">
                                    {{ fmt$(results.changed.filter(c => c.delta > 0).sort((a,b) => b.delta - a.delta)[0]?.delta ?? 0) }}
                                </div>
                                <div class="wof-insight-sub">{{ results.changed.filter(c => c.delta > 0).sort((a,b) => b.delta - a.delta)[0]?.a.name ?? '—' }}</div>
                            </div>
                            <div class="wof-insight">
                                <div class="wof-insight-title">Largest Balance Decrease</div>
                                <div class="wof-insight-val green">
                                    {{ fmt$(results.changed.filter(c => c.delta < 0).sort((a,b) => a.delta - b.delta)[0]?.delta ?? 0) }}
                                </div>
                                <div class="wof-insight-sub">{{ results.changed.filter(c => c.delta < 0).sort((a,b) => a.delta - b.delta)[0]?.a.name ?? '—' }}</div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Changed -->
                <div v-if="activeTab === 'changes'">
                    <div class="wof-table-ctrl">
                        <input v-model="search.changes" class="wof-search" type="search" placeholder="Search…" />
                        <span class="wof-count">{{ filteredChanges().length }} accounts</span>
                    </div>
                    <div class="wof-table-wrap">
                        <table>
                            <thead><tr>
                                <th>Account ID</th><th>Name</th><th>Class</th>
                                <th>Balance #1</th><th>Balance #2</th><th>Δ Balance</th>
                                <th>Freeze #1</th><th>Freeze #2</th>
                            </tr></thead>
                            <tbody>
                                <tr v-for="c in filteredChanges()" :key="c.a.id">
                                    <td><code>{{ c.a.id }}</code></td>
                                    <td>{{ c.a.name }}</td>
                                    <td>{{ c.a.cls }}</td>
                                    <td>${{ c.a.balance.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                                    <td>${{ c.b.balance.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                                    <td :class="c.delta > 0 ? 'wof-pos' : c.delta < 0 ? 'wof-neg' : 'wof-zero'">{{ fmt$(c.delta) }}</td>
                                    <td><span class="wof-badge" :class="c.a.freezeStatus === 'FROZEN' ? 'frozen' : 'freezable'">{{ c.a.freezeStatus }}</span></td>
                                    <td><span class="wof-badge" :class="c.b.freezeStatus === 'FROZEN' ? 'frozen' : 'freezable'">{{ c.b.freezeStatus }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Removed -->
                <div v-if="activeTab === 'removed'">
                    <div class="wof-table-ctrl">
                        <input v-model="search.removed" class="wof-search" type="search" placeholder="Search…" />
                        <span class="wof-count">{{ filtered(results.removed, 'removed').length }} accounts</span>
                    </div>
                    <div class="wof-table-wrap">
                        <table>
                            <thead><tr>
                                <th>Account ID</th><th>Name</th><th>Class</th><th>SAs</th><th>Balance</th><th>Freeze Status</th><th>Bill Date</th><th>Due Date</th>
                            </tr></thead>
                            <tbody>
                                <tr v-for="a in filtered(results.removed, 'removed')" :key="a.id">
                                    <td><code>{{ a.id }}</code></td>
                                    <td>{{ a.name }}</td><td>{{ a.cls }}</td><td>{{ a.sas }}</td>
                                    <td>${{ a.balance.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                                    <td><span class="wof-badge" :class="a.freezeStatus === 'FROZEN' ? 'frozen' : 'freezable'">{{ a.freezeStatus }}</span></td>
                                    <td>{{ a.billDate }}</td><td>{{ a.dueDate }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Added -->
                <div v-if="activeTab === 'added'">
                    <div class="wof-table-ctrl">
                        <input v-model="search.added" class="wof-search" type="search" placeholder="Search…" />
                        <span class="wof-count">{{ filtered(results.added, 'added').length }} accounts</span>
                    </div>
                    <div class="wof-table-wrap">
                        <table>
                            <thead><tr>
                                <th>Account ID</th><th>Name</th><th>Class</th><th>SAs</th><th>Balance</th><th>Freeze Status</th><th>Bill Date</th><th>Due Date</th>
                            </tr></thead>
                            <tbody>
                                <tr v-for="a in filtered(results.added, 'added')" :key="a.id">
                                    <td><code>{{ a.id }}</code></td>
                                    <td>{{ a.name }}</td><td>{{ a.cls }}</td><td>{{ a.sas }}</td>
                                    <td>${{ a.balance.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                                    <td><span class="wof-badge" :class="a.freezeStatus === 'FROZEN' ? 'frozen' : 'freezable'">{{ a.freezeStatus }}</span></td>
                                    <td>{{ a.billDate }}</td><td>{{ a.dueDate }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Unchanged -->
                <div v-if="activeTab === 'unchanged'">
                    <div class="wof-table-ctrl">
                        <input v-model="search.unchanged" class="wof-search" type="search" placeholder="Search…" />
                        <span class="wof-count">{{ filtered(results.unchanged, 'unchanged').length }} accounts</span>
                    </div>
                    <div class="wof-table-wrap">
                        <table>
                            <thead><tr>
                                <th>Account ID</th><th>Name</th><th>Class</th><th>SAs</th><th>Balance</th><th>Freeze Status</th>
                            </tr></thead>
                            <tbody>
                                <tr v-for="a in filtered(results.unchanged, 'unchanged')" :key="a.id">
                                    <td><code>{{ a.id }}</code></td>
                                    <td>{{ a.name }}</td><td>{{ a.cls }}</td><td>{{ a.sas }}</td>
                                    <td>${{ a.balance.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                                    <td><span class="wof-badge" :class="a.freezeStatus === 'FROZEN' ? 'frozen' : 'freezable'">{{ a.freezeStatus }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </main>
    </div>
</template>

<style scoped>
:root {
    --bg: #0f1117;
    --surface: #1a1d27;
    --surface2: #22263a;
    --border: #2e3350;
    --accent: #4f8ef7;
    --accent2: #7c3aed;
    --green: #22c55e;
    --red: #ef4444;
    --yellow: #eab308;
    --text: #e8eaf6;
    --muted: #8892b0;
}

.wof-page {
    min-height: 100vh;
    background: #0f1117;
    color: #e8eaf6;
    font-family: 'Segoe UI', system-ui, sans-serif;
    font-size: 14px;
}

.wof-header {
    background: linear-gradient(90deg, #1a1d27, #22263a);
    border-bottom: 1px solid #2e3350;
    padding: 1.2rem 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.wof-header h1 { font-size: 1.3rem; font-weight: 700; color: #4f8ef7; letter-spacing: 0.02em; }
.wof-tag {
    background: #7c3aed;
    color: #fff;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 99px;
    letter-spacing: 0.06em;
}

.wof-main { max-width: 1280px; margin: 0 auto; padding: 2rem; }

/* Upload */
.wof-upload-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
@media (max-width: 720px) { .wof-upload-grid { grid-template-columns: 1fr; } }

.wof-drop {
    display: block;
    border: 2px dashed #2e3350;
    border-radius: 12px;
    background: #1a1d27;
    padding: 2.5rem 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s;
    position: relative;
}
.wof-drop input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.wof-drop:hover, .wof-drop.loaded { border-color: #4f8ef7; }
.wof-drop.loaded { border-color: #22c55e; }
.wof-drop-icon { font-size: 2.5rem; display: block; margin-bottom: 0.5rem; }
.wof-drop-label { color: #8892b0; font-size: 0.9rem; }
.wof-drop-label small { font-size: 0.8rem; }
.wof-drop-name { color: #22c55e; font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem; }
.wof-progress { margin-top: 0.8rem; height: 4px; background: #2e3350; border-radius: 99px; overflow: hidden; }
.wof-progress-fill { height: 100%; background: #4f8ef7; transition: width 0.1s; }

.wof-btn {
    display: block;
    width: 100%;
    padding: 1rem;
    background: linear-gradient(90deg, #4f8ef7, #7c3aed);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    margin-bottom: 2.5rem;
    transition: opacity 0.2s;
}
.wof-btn:disabled { opacity: 0.4; cursor: not-allowed; }

/* Metrics */
.wof-metrics { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.wof-metric { background: #1a1d27; border: 1px solid #2e3350; border-radius: 12px; padding: 1.2rem; text-align: center; }
.wof-metric-val { font-size: 2rem; font-weight: 800; line-height: 1; }
.wof-metric-lbl { font-size: 0.7rem; color: #8892b0; margin-top: 0.4rem; text-transform: uppercase; letter-spacing: 0.06em; }
.wof-metric.blue .wof-metric-val   { color: #4f8ef7; }
.wof-metric.green .wof-metric-val  { color: #22c55e; }
.wof-metric.red .wof-metric-val    { color: #ef4444; }
.wof-metric.yellow .wof-metric-val { color: #eab308; }
.wof-metric.muted .wof-metric-val  { color: #8892b0; }

/* Tabs */
.wof-tab-bar { display: flex; gap: 4px; border-bottom: 1px solid #2e3350; margin-bottom: 1.5rem; flex-wrap: wrap; }
.wof-tab { padding: 0.6rem 1.1rem; border: none; background: transparent; color: #8892b0; font-size: 0.9rem; font-weight: 600; cursor: pointer; border-bottom: 2px solid transparent; transition: color 0.15s, border-color 0.15s; }
.wof-tab.active { color: #4f8ef7; border-bottom-color: #4f8ef7; }

/* Charts */
.wof-charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem; }
@media (max-width: 900px) { .wof-charts-grid { grid-template-columns: 1fr; } }
.wof-chart-card { background: #1a1d27; border: 1px solid #2e3350; border-radius: 12px; padding: 1.4rem; }
.wof-chart-card h3 { font-size: 0.8rem; font-weight: 700; color: #8892b0; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 1rem; }

/* Insights */
.wof-insights { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.wof-insight { background: #1a1d27; border: 1px solid #2e3350; border-radius: 12px; padding: 1.2rem; }
.wof-insight-title { font-size: 0.72rem; color: #8892b0; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.5rem; }
.wof-insight-val { font-size: 1.15rem; font-weight: 700; }
.wof-insight-val.green { color: #22c55e; }
.wof-insight-val.red   { color: #ef4444; }
.wof-insight-sub { font-size: 0.78rem; color: #8892b0; margin-top: 0.3rem; }

/* Tables */
.wof-table-ctrl { display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem; }
.wof-search { flex: 1; background: #1a1d27; border: 1px solid #2e3350; border-radius: 8px; color: #e8eaf6; padding: 0.5rem 0.8rem; font-size: 0.85rem; }
.wof-search:focus { outline: none; border-color: #4f8ef7; }
.wof-count { font-size: 0.8rem; color: #8892b0; white-space: nowrap; }
.wof-table-wrap { overflow-x: auto; border-radius: 12px; border: 1px solid #2e3350; }
table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
th { background: #22263a; padding: 0.7rem 0.9rem; text-align: left; font-size: 0.72rem; font-weight: 700; color: #8892b0; text-transform: uppercase; letter-spacing: 0.06em; white-space: nowrap; }
td { padding: 0.6rem 0.9rem; border-top: 1px solid #2e3350; }
tr:hover td { background: #1e2035; }
code { font-size: 0.8rem; color: #a78bfa; }

.wof-badge { display: inline-block; padding: 2px 7px; border-radius: 99px; font-size: 0.7rem; font-weight: 700; }
.wof-badge.frozen    { background: #1e40af; color: #93c5fd; }
.wof-badge.freezable { background: #78350f; color: #fcd34d; }

.wof-pos  { color: #ef4444; font-weight: 700; }
.wof-neg  { color: #22c55e; font-weight: 700; }
.wof-zero { color: #8892b0; }
</style>
