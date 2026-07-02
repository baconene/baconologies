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
// Matches the RTF G_2 loop: EVT_SEQ, WO_EVT_TYP_DESCR, TRIGGER_DT, COMPLETION_DT, WO_EVT_STAT_DESC
interface WoEvent {
    seq: number
    eventType: string
    triggerDate: string
    completionDate: string
    status: string
}

// Matches the RTF G_1 loop: WO_PROC_ID, ACCT_ID, ACCOUNT_NAME, MAILING_ADDRESS, WO_CREATED_DT,
// WO_STATUS_DESC, WO_STAT_RSN_FLG, WO_PROC_TMPL_CD, TOTAL_WO_AMT
interface Account {
    id: string
    woId: string
    name: string
    address: string
    createdDate: string
    status: string
    statusReason: string
    template: string
    balance: number
    events: WoEvent[]
}

interface Change {
    a: Account
    b: Account
    delta: number
    eventChanges: string[]
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
const expandedRows = ref<Set<string>>(new Set())

const chartInstances: Chart[] = []
const canvasBreakdown = ref<HTMLCanvasElement | null>(null)
const canvasBalance   = ref<HTMLCanvasElement | null>(null)
const canvasStatus    = ref<HTMLCanvasElement | null>(null)
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
function toggleRow(id: string) {
    const s = new Set(expandedRows.value)
    s.has(id) ? s.delete(id) : s.add(id)
    expandedRows.value = s
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
        // Rejoin split dates: "06- 23- 2026" → "06-23-2026"
        .replace(/(\d{2})-\s+(\d{2})-\s+(\d{4})/g, '$1-$2-$3')
        // Collapse newlines and extra spaces
        .replace(/\n/g, ' ')
        .replace(/\s{2,}/g, ' ')
}

// ── Parsing (RTF format) ──────────────────────────────────────────────────────
//
// RTF G_1 columns (per write-off record):
//   WO_PROC_ID | ACCT_ID | ACCOUNT_NAME | MAILING_ADDRESS | WO_CREATED_DT | WO_STATUS_DESC | WO_STAT_RSN_FLG | WO_PROC_TMPL_CD | TOTAL_WO_AMT
//
// RTF G_2 columns (per event within a write-off):
//   EVT_SEQ | WO_EVT_TYP_DESCR | TRIGGER_DT | COMPLETION_DT | WO_EVT_STAT_DESC
//
// Strategy:
//   1. Scan for all 10-digit ACCT_IDs — each marks the start of one G_1 record.
//   2. For each block (from ACCT_ID to the next ACCT_ID), extract G_1 fields then G_2 events.
//   3. Name ends where the street address begins (first street number: digits followed by a word).
//   4. G_2 section starts at the "Seq" column header or at the first "1 <text>" event line.
//
function parseAccounts(raw: string): Record<string, Account> {
    const text = normalize(raw)
    const accounts: Record<string, Account> = {}

    // Collect positions of all 10-digit IDs
    const idHits: Array<{ id: string; pos: number }> = []
    const idScan = /\b(\d{10})\b/g
    let m: RegExpExecArray | null
    while ((m = idScan.exec(text)) !== null) {
        idHits.push({ id: m[1], pos: m.index })
    }

    for (let i = 0; i < idHits.length; i++) {
        const { id, pos } = idHits[i]
        if (accounts[id]) continue   // first occurrence wins; skip SA/BSEG repetitions

        // Grab the WO_PROC_ID that appears just before the ACCT_ID on the same "line"
        const lookback = text.slice(Math.max(0, pos - 30), pos)
        const woIdM = lookback.match(/(\d{4,9})\s*$/)
        const woId = woIdM ? woIdM[1] : ''

        // Text block from after ACCT_ID to start of next ACCT_ID (or EOF)
        const blockStart = pos + id.length
        const blockEnd   = i + 1 < idHits.length ? idHits[i + 1].pos : text.length
        const block      = text.slice(blockStart, blockEnd).trim()

        // ── Split block into G_1 tail and G_2 events section ──
        // G_2 starts at "Seq Event Trigger" header or at first numbered event line
        const seqHeaderIdx = block.search(/\bSeq\b/)
        const g1Raw = seqHeaderIdx >= 0 ? block.slice(0, seqHeaderIdx) : block
        const g2Raw = seqHeaderIdx >= 0 ? block.slice(seqHeaderIdx)    : ''

        // ── G_1: parse name, address, date, status, amount ──
        // The first MM-DD-YYYY date is WO_CREATED_DT; everything before it is name+address
        const firstDateM = g1Raw.match(/\b(\d{2}-\d{2}-\d{4})\b/)
        const createdDate  = firstDateM ? firstDateM[1] : ''
        const beforeDate   = firstDateM ? g1Raw.slice(0, firstDateM.index!).trim() : g1Raw.trim()
        const afterDate    = firstDateM ? g1Raw.slice(firstDateM.index! + 10).trim() : ''

        // Name ends where the street address begins.
        // Street addresses start with a number (e.g. "456 Oak St").
        // We look for: word-boundary + 1–5 digits + space + uppercase-letter
        // but skip patterns that are just floating numbers (zip codes, IDs).
        let name    = beforeDate
        let address = ''
        const streetM = beforeDate.match(/\b(\d{1,5})\s+([A-Z][a-z])/)
        if (streetM) {
            name    = beforeDate.slice(0, streetM.index!).trim()
            address = beforeDate.slice(streetM.index!).trim()
        } else {
            // Fallback: look for "CITY, ST ZIP" at the end to anchor address
            const cityM = beforeDate.match(/,\s*[A-Z]{2}\s+\d{5}/)
            if (cityM) {
                // Walk back from the city comma to find where address starts
                const addrGuessIdx = beforeDate.lastIndexOf(' ', cityM.index! - 1)
                name    = beforeDate.slice(0, addrGuessIdx).trim()
                address = beforeDate.slice(addrGuessIdx).trim()
            }
        }
        name = name.replace(/\s+/g, ' ').trim()

        // TOTAL_WO_AMT: last dollar-formatted decimal in the G_1 tail (after the date)
        const amountHits = [...afterDate.matchAll(/\b(\d{1,3}(?:,\d{3})*\.\d{2})\b/g)]
        const balance = amountHits.length
            ? parseFloat(amountHits[amountHits.length - 1][1].replace(/,/g, ''))
            : 0

        // WO_STATUS_DESC: first run of ALL-CAPS words after the date
        const statusM = afterDate.match(/\b([A-Z]{2,}(?:\s+[A-Z]{2,})*)\b/)
        const status  = statusM ? statusM[1] : ''

        // WO_STAT_RSN_FLG: text inside first parentheses after the date
        const reasonM = afterDate.match(/\(([^)]{1,20})\)/)
        const statusReason = reasonM ? reasonM[1] : ''

        // WO_PROC_TMPL_CD: alphanumeric code (letters+digits+dashes) appearing after reason
        const afterReason = reasonM
            ? afterDate.slice(afterDate.indexOf(reasonM[0]) + reasonM[0].length).trim()
            : afterDate
        const tmplM  = afterReason.match(/\b([A-Z][A-Z0-9\-]{2,})\b/)
        const template = tmplM ? tmplM[1] : ''

        // ── G_2: parse events (EVT_SEQ, WO_EVT_TYP_DESCR, TRIGGER_DT, COMPLETION_DT, WO_EVT_STAT_DESC) ──
        const events: WoEvent[] = []
        if (g2Raw) {
            // Each event row: [single digit] [description text] [MM-DD-YYYY] [optional MM-DD-YYYY] [status text]
            const evtRe = /\b([1-9])\s+([A-Za-z][^0-9]{2,40}?)\s+(\d{2}-\d{2}-\d{4})(?:\s+(\d{2}-\d{2}-\d{4}))?\s+([A-Za-z][A-Za-z\s]{1,20}?)(?=\s*\b[1-9]\s+[A-Za-z]|\s*$)/g
            let em: RegExpExecArray | null
            while ((em = evtRe.exec(g2Raw)) !== null) {
                const seq = parseInt(em[1])
                if (events.find(e => e.seq === seq)) continue
                events.push({
                    seq,
                    eventType:      em[2].trim(),
                    triggerDate:    em[3],
                    completionDate: em[4] ?? '',
                    status:         em[5].trim(),
                })
            }
            events.sort((a, b) => a.seq - b.seq)
        }

        accounts[id] = { id, woId, name, address, createdDate, status, statusReason, template, balance, events }
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

        // Detect event-level changes
        const eventChanges: string[] = []
        for (const bEvt of b.events) {
            const aEvt = a.events.find(e => e.seq === bEvt.seq)
            if (!aEvt) {
                eventChanges.push(`EVT${bEvt.seq} added`)
            } else if (aEvt.status !== bEvt.status) {
                eventChanges.push(`EVT${bEvt.seq}: ${aEvt.status} → ${bEvt.status}`)
            } else if (aEvt.triggerDate !== bEvt.triggerDate) {
                eventChanges.push(`EVT${bEvt.seq} trigger changed`)
            }
        }
        for (const aEvt of a.events) {
            if (!b.events.find(e => e.seq === aEvt.seq)) eventChanges.push(`EVT${aEvt.seq} removed`)
        }

        if (a.balance !== b.balance || a.status !== b.status || eventChanges.length) {
            changed.push({ a, b, delta: b.balance - a.balance, eventChanges })
        } else {
            unchanged.push(a)
        }
    }

    changed.sort((x, y) => Math.abs(y.delta) - Math.abs(x.delta))
    return { changed, removed, added, unchanged }
}

// ── Run ──────────────────────────────────────────────────────────────────────
async function run() {
    if (!file1.value || !file2.value) return
    loading.value = true
    results.value = null
    expandedRows.value = new Set()
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
                    backgroundColor: ['#22c55e', '#eab308', '#4f8ef7', '#ef4444'], borderWidth: 0 }],
            },
            options: { cutout: '65%', plugins: { legend: { labels: { color: DARK } } } },
        }))
    }

    if (canvasBalance.value) {
        chartInstances.push(new Chart(canvasBalance.value, {
            type: 'bar',
            data: {
                labels: ['PDF #1', 'PDF #2'],
                datasets: [{ label: 'Total WO Amount ($)', data: [r.bal1, r.bal2],
                    backgroundColor: ['#4f8ef7', '#7c3aed'], borderRadius: 6 }],
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    y: { ticks: { color: DARK, callback: (v: any) => '$' + Number(v).toLocaleString() }, grid: { color: GRID } },
                    x: { ticks: { color: DARK }, grid: { display: false } },
                },
            },
        }))
    }

    if (canvasStatus.value) {
        // WO_STATUS_DESC distribution across both PDFs
        const allStatuses = new Set([
            ...Object.values(r.acc1).map(a => a.status || 'UNKNOWN'),
            ...Object.values(r.acc2).map(a => a.status || 'UNKNOWN'),
        ])
        const statusLabels = [...allStatuses].filter(Boolean).slice(0, 8)
        const count = (acc: Record<string, Account>, s: string) =>
            Object.values(acc).filter(a => (a.status || 'UNKNOWN') === s).length
        chartInstances.push(new Chart(canvasStatus.value, {
            type: 'bar',
            data: {
                labels: statusLabels,
                datasets: [
                    { label: 'PDF #1', data: statusLabels.map(s => count(r.acc1, s)), backgroundColor: '#4f8ef7', borderRadius: 4 },
                    { label: 'PDF #2', data: statusLabels.map(s => count(r.acc2, s)), backgroundColor: '#7c3aed', borderRadius: 4 },
                ],
            },
            options: {
                plugins: { legend: { labels: { color: DARK } } },
                scales: {
                    y: { ticks: { color: DARK }, grid: { color: GRID } },
                    x: { ticks: { color: DARK, maxRotation: 30, font: { size: 10 } }, grid: { display: false } },
                },
            },
        }))
    }

    if (canvasTop.value) {
        const top = r.changed.slice(0, 10)
        chartInstances.push(new Chart(canvasTop.value, {
            type: 'bar',
            data: {
                labels: top.map(c => c.a.id),
                datasets: [{ label: 'Δ WO Amount ($)', data: top.map(c => c.delta),
                    backgroundColor: top.map(c => c.delta > 0 ? '#ef4444' : '#22c55e'), borderRadius: 4 }],
            },
            options: {
                indexAxis: 'y' as const,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: DARK, callback: (v: any) => '$' + Number(v).toLocaleString() }, grid: { color: GRID } },
                    y: { ticks: { color: DARK, font: { size: 10 } }, grid: { display: false } },
                },
            },
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
function statusClass(s: string) {
    const u = s.toUpperCase()
    if (u.includes('ACTIVE') || u.includes('PROGRESS')) return 'stat-active'
    if (u.includes('COMPLET') || u.includes('WRITTEN')) return 'stat-done'
    if (u.includes('CANCEL') || u.includes('VOID'))    return 'stat-cancel'
    return 'stat-pending'
}
function evtStatusClass(s: string) {
    const u = s.toUpperCase()
    if (u.includes('COMPLET') || u.includes('DONE')) return 'evt-done'
    if (u.includes('PENDING') || u.includes('WAIT')) return 'evt-pending'
    if (u.includes('SKIP') || u.includes('CANCEL'))  return 'evt-skip'
    return ''
}
function filtered(list: Account[], key: keyof typeof search.value) {
    const q = search.value[key].toLowerCase()
    if (!q) return list
    return list.filter(a =>
        a.id.includes(q) || a.name.toLowerCase().includes(q) || a.status.toLowerCase().includes(q)
    )
}
function filteredChanges() {
    const q = search.value.changes.toLowerCase()
    if (!q || !results.value) return results.value?.changed ?? []
    return results.value.changed.filter(c =>
        c.a.id.includes(q) || c.a.name.toLowerCase().includes(q) || c.a.status.toLowerCase().includes(q)
    )
}

const tabs = [
    { key: 'overview',  label: 'Overview' },
    { key: 'changes',   label: 'Changed' },
    { key: 'removed',   label: 'Removed / Resolved' },
    { key: 'added',     label: 'New Accounts' },
    { key: 'unchanged', label: 'Unchanged' },
] as const
</script>

<template>
    <Head title="Write-Off Process Comparator" />

    <div class="wof-page">
        <header class="wof-header">
            <h1>Write-Off Process Comparator</h1>
            <span class="wof-tag">PDF ANALYSIS</span>
        </header>

        <main class="wof-main">
            <!-- Upload -->
            <div class="wof-upload-grid">
                <label class="wof-drop" :class="{ loaded: file1 }" @dragover.prevent @drop="onDrop($event, 1)">
                    <input type="file" accept=".pdf" @change="onFile($event, 1)" />
                    <span class="wof-drop-icon">📄</span>
                    <div class="wof-drop-label">PDF #1 — Baseline Report<br /><small>Drag &amp; drop or click</small></div>
                    <div v-if="file1" class="wof-drop-name">📎 {{ file1.name }}</div>
                    <div v-if="prog1 > 0 && prog1 < 1" class="wof-progress"><div class="wof-progress-fill" :style="{ width: (prog1 * 100) + '%' }"></div></div>
                </label>
                <label class="wof-drop" :class="{ loaded: file2 }" @dragover.prevent @drop="onDrop($event, 2)">
                    <input type="file" accept=".pdf" @change="onFile($event, 2)" />
                    <span class="wof-drop-icon">📄</span>
                    <div class="wof-drop-label">PDF #2 — Comparison Report<br /><small>Drag &amp; drop or click</small></div>
                    <div v-if="file2" class="wof-drop-name">📎 {{ file2.name }}</div>
                    <div v-if="prog2 > 0 && prog2 < 1" class="wof-progress"><div class="wof-progress-fill" :style="{ width: (prog2 * 100) + '%' }"></div></div>
                </label>
            </div>

            <button class="wof-btn" :disabled="!file1 || !file2 || loading" @click="run">
                {{ loading ? '⏳ Processing…' : '⚡ Compare Reports' }}
            </button>

            <template v-if="results">
                <!-- Metrics -->
                <div class="wof-metrics">
                    <div class="wof-metric blue"><div class="wof-metric-val">{{ Object.keys(results.acc1).length.toLocaleString() }}</div><div class="wof-metric-lbl">Accounts PDF #1</div></div>
                    <div class="wof-metric blue"><div class="wof-metric-val">{{ Object.keys(results.acc2).length.toLocaleString() }}</div><div class="wof-metric-lbl">Accounts PDF #2</div></div>
                    <div class="wof-metric" :class="(results.changed.length + results.removed.length + results.added.length) > 0 ? 'yellow' : 'green'">
                        <div class="wof-metric-val">{{ (results.changed.length + results.removed.length + results.added.length).toLocaleString() }}</div>
                        <div class="wof-metric-lbl">Total Differences</div>
                    </div>
                    <div class="wof-metric yellow"><div class="wof-metric-val">{{ results.changed.length.toLocaleString() }}</div><div class="wof-metric-lbl">Changed</div></div>
                    <div class="wof-metric green"><div class="wof-metric-val">{{ results.removed.length.toLocaleString() }}</div><div class="wof-metric-lbl">Removed / Resolved</div></div>
                    <div class="wof-metric red"><div class="wof-metric-val">{{ results.added.length.toLocaleString() }}</div><div class="wof-metric-lbl">New Accounts</div></div>
                    <div class="wof-metric muted"><div class="wof-metric-val">{{ results.unchanged.length.toLocaleString() }}</div><div class="wof-metric-lbl">Unchanged</div></div>
                </div>

                <!-- Tabs -->
                <div class="wof-tab-bar">
                    <button v-for="t in tabs" :key="t.key" class="wof-tab" :class="{ active: activeTab === t.key }" @click="activeTab = t.key">{{ t.label }}</button>
                </div>

                <!-- ── Overview ── -->
                <div v-if="activeTab === 'overview'">
                    <div class="wof-charts-grid">
                        <div class="wof-chart-card"><h3>Account Breakdown</h3><canvas ref="canvasBreakdown" style="max-height:260px"></canvas></div>
                        <div class="wof-chart-card"><h3>Total WO Amount</h3><canvas ref="canvasBalance" style="max-height:260px"></canvas></div>
                        <div class="wof-chart-card"><h3>WO Status Distribution</h3><canvas ref="canvasStatus" style="max-height:260px"></canvas></div>
                        <div class="wof-chart-card"><h3>Top 10 Balance Changes</h3><canvas ref="canvasTop" style="max-height:260px"></canvas></div>
                    </div>
                    <div class="wof-insights">
                        <div class="wof-insight">
                            <div class="wof-insight-title">Balance Delta (PDF1 → PDF2)</div>
                            <div class="wof-insight-val" :class="results.bal2 - results.bal1 <= 0 ? 'green' : 'red'">{{ fmt$(results.bal2 - results.bal1) }}</div>
                            <div class="wof-insight-sub">{{ results.bal2 - results.bal1 <= 0 ? 'Exposure reduced' : 'Exposure increased' }}</div>
                        </div>
                        <div class="wof-insight">
                            <div class="wof-insight-title">Amount Recovered (Removed)</div>
                            <div class="wof-insight-val green">{{ fmtAbs$(results.removed.reduce((s,a) => s + a.balance, 0)) }}</div>
                            <div class="wof-insight-sub">{{ results.removed.length }} accounts resolved</div>
                        </div>
                        <div class="wof-insight">
                            <div class="wof-insight-title">New Accounts Exposure</div>
                            <div class="wof-insight-val red">{{ fmtAbs$(results.added.reduce((s,a) => s + a.balance, 0)) }}</div>
                            <div class="wof-insight-sub">{{ results.added.length }} new accounts in PDF #2</div>
                        </div>
                        <template v-if="results.changed.length">
                            <div class="wof-insight">
                                <div class="wof-insight-title">Largest Increase</div>
                                <div class="wof-insight-val red">{{ fmt$(results.changed.filter(c => c.delta > 0).sort((a,b) => b.delta - a.delta)[0]?.delta ?? 0) }}</div>
                                <div class="wof-insight-sub">{{ results.changed.filter(c => c.delta > 0).sort((a,b) => b.delta - a.delta)[0]?.a.name ?? '—' }}</div>
                            </div>
                            <div class="wof-insight">
                                <div class="wof-insight-title">Largest Decrease</div>
                                <div class="wof-insight-val green">{{ fmt$(results.changed.filter(c => c.delta < 0).sort((a,b) => a.delta - b.delta)[0]?.delta ?? 0) }}</div>
                                <div class="wof-insight-sub">{{ results.changed.filter(c => c.delta < 0).sort((a,b) => a.delta - b.delta)[0]?.a.name ?? '—' }}</div>
                            </div>
                            <div class="wof-insight">
                                <div class="wof-insight-title">Event-Only Changes</div>
                                <div class="wof-insight-val yellow">{{ results.changed.filter(c => c.delta === 0 && c.eventChanges.length).length }}</div>
                                <div class="wof-insight-sub">accounts with event status changes only</div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- ── Changed ── -->
                <div v-if="activeTab === 'changes'">
                    <div class="wof-table-ctrl">
                        <input v-model="search.changes" class="wof-search" type="search" placeholder="Search by ID, name, or status…" />
                        <span class="wof-count">{{ filteredChanges().length }} accounts</span>
                    </div>
                    <div class="wof-table-wrap">
                        <table>
                            <thead><tr>
                                <th></th>
                                <th>Account ID</th><th>WO ID</th><th>Name</th>
                                <th>Amount #1</th><th>Amount #2</th><th>Δ Amount</th>
                                <th>Status #1</th><th>Status #2</th><th>Created Date</th><th>Event Changes</th>
                            </tr></thead>
                            <tbody>
                                <template v-for="c in filteredChanges()" :key="c.a.id">
                                    <tr>
                                        <td>
                                            <button class="wof-expand-btn" @click="toggleRow(c.a.id)" :title="expandedRows.has(c.a.id) ? 'Hide events' : 'Show events'">
                                                {{ expandedRows.has(c.a.id) ? '▲' : '▼' }}
                                            </button>
                                        </td>
                                        <td><code>{{ c.a.id }}</code></td>
                                        <td><code class="wo-id">{{ c.a.woId }}</code></td>
                                        <td>{{ c.a.name }}</td>
                                        <td>${{ c.a.balance.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                                        <td>${{ c.b.balance.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                                        <td :class="c.delta > 0 ? 'wof-pos' : c.delta < 0 ? 'wof-neg' : 'wof-zero'">{{ fmt$(c.delta) }}</td>
                                        <td><span class="wof-status" :class="statusClass(c.a.status)">{{ c.a.status || '—' }}</span></td>
                                        <td><span class="wof-status" :class="statusClass(c.b.status)">{{ c.b.status || '—' }}</span></td>
                                        <td>{{ c.a.createdDate }}</td>
                                        <td>
                                            <span v-if="c.eventChanges.length" class="evt-change-pill">{{ c.eventChanges.length }} change{{ c.eventChanges.length > 1 ? 's' : '' }}</span>
                                            <span v-else class="wof-zero">—</span>
                                        </td>
                                    </tr>
                                    <tr v-if="expandedRows.has(c.a.id)" class="event-expand-row">
                                        <td colspan="11">
                                            <div class="event-panels">
                                                <div class="event-panel">
                                                    <div class="event-panel-label">PDF #1 — Events</div>
                                                    <table class="evt-table">
                                                        <thead><tr><th>Seq</th><th>Event Type</th><th>Trigger</th><th>Completion</th><th>Status</th></tr></thead>
                                                        <tbody>
                                                            <tr v-for="e in c.a.events" :key="e.seq">
                                                                <td>{{ e.seq }}</td>
                                                                <td>{{ e.eventType }}</td>
                                                                <td>{{ e.triggerDate }}</td>
                                                                <td>{{ e.completionDate || '—' }}</td>
                                                                <td><span class="evt-status" :class="evtStatusClass(e.status)">{{ e.status }}</span></td>
                                                            </tr>
                                                            <tr v-if="!c.a.events.length"><td colspan="5" class="wof-zero">No events parsed</td></tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div class="event-panel">
                                                    <div class="event-panel-label">PDF #2 — Events</div>
                                                    <table class="evt-table">
                                                        <thead><tr><th>Seq</th><th>Event Type</th><th>Trigger</th><th>Completion</th><th>Status</th></tr></thead>
                                                        <tbody>
                                                            <tr v-for="e in c.b.events" :key="e.seq" :class="c.eventChanges.some(x => x.startsWith('EVT' + e.seq)) ? 'evt-highlight' : ''">
                                                                <td>{{ e.seq }}</td>
                                                                <td>{{ e.eventType }}</td>
                                                                <td>{{ e.triggerDate }}</td>
                                                                <td>{{ e.completionDate || '—' }}</td>
                                                                <td><span class="evt-status" :class="evtStatusClass(e.status)">{{ e.status }}</span></td>
                                                            </tr>
                                                            <tr v-if="!c.b.events.length"><td colspan="5" class="wof-zero">No events parsed</td></tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div v-if="c.eventChanges.length" class="evt-change-list">
                                                <span v-for="ch in c.eventChanges" :key="ch" class="evt-change-tag">{{ ch }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ── Removed ── -->
                <div v-if="activeTab === 'removed'">
                    <div class="wof-table-ctrl">
                        <input v-model="search.removed" class="wof-search" type="search" placeholder="Search…" />
                        <span class="wof-count">{{ filtered(results.removed, 'removed').length }} accounts</span>
                    </div>
                    <div class="wof-table-wrap">
                        <table>
                            <thead><tr>
                                <th></th><th>Account ID</th><th>WO ID</th><th>Name</th>
                                <th>Amount</th><th>Status</th><th>Created Date</th><th>Template</th><th>Events</th>
                            </tr></thead>
                            <tbody>
                                <template v-for="a in filtered(results.removed, 'removed')" :key="a.id">
                                    <tr>
                                        <td><button class="wof-expand-btn" @click="toggleRow(a.id)">{{ expandedRows.has(a.id) ? '▲' : '▼' }}</button></td>
                                        <td><code>{{ a.id }}</code></td>
                                        <td><code class="wo-id">{{ a.woId }}</code></td>
                                        <td>{{ a.name }}</td>
                                        <td>${{ a.balance.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                                        <td><span class="wof-status" :class="statusClass(a.status)">{{ a.status || '—' }}</span></td>
                                        <td>{{ a.createdDate }}</td>
                                        <td><code class="tmpl">{{ a.template || '—' }}</code></td>
                                        <td class="wof-zero">{{ a.events.length }} event{{ a.events.length !== 1 ? 's' : '' }}</td>
                                    </tr>
                                    <tr v-if="expandedRows.has(a.id)" class="event-expand-row">
                                        <td colspan="9">
                                            <table class="evt-table">
                                                <thead><tr><th>Seq</th><th>Event Type</th><th>Trigger Date</th><th>Completion Date</th><th>Status</th></tr></thead>
                                                <tbody>
                                                    <tr v-for="e in a.events" :key="e.seq">
                                                        <td>{{ e.seq }}</td><td>{{ e.eventType }}</td><td>{{ e.triggerDate }}</td>
                                                        <td>{{ e.completionDate || '—' }}</td>
                                                        <td><span class="evt-status" :class="evtStatusClass(e.status)">{{ e.status }}</span></td>
                                                    </tr>
                                                    <tr v-if="!a.events.length"><td colspan="5" class="wof-zero">No events parsed</td></tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ── Added ── -->
                <div v-if="activeTab === 'added'">
                    <div class="wof-table-ctrl">
                        <input v-model="search.added" class="wof-search" type="search" placeholder="Search…" />
                        <span class="wof-count">{{ filtered(results.added, 'added').length }} accounts</span>
                    </div>
                    <div class="wof-table-wrap">
                        <table>
                            <thead><tr>
                                <th></th><th>Account ID</th><th>WO ID</th><th>Name</th>
                                <th>Amount</th><th>Status</th><th>Created Date</th><th>Template</th><th>Events</th>
                            </tr></thead>
                            <tbody>
                                <template v-for="a in filtered(results.added, 'added')" :key="a.id">
                                    <tr>
                                        <td><button class="wof-expand-btn" @click="toggleRow(a.id)">{{ expandedRows.has(a.id) ? '▲' : '▼' }}</button></td>
                                        <td><code>{{ a.id }}</code></td>
                                        <td><code class="wo-id">{{ a.woId }}</code></td>
                                        <td>{{ a.name }}</td>
                                        <td>${{ a.balance.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                                        <td><span class="wof-status" :class="statusClass(a.status)">{{ a.status || '—' }}</span></td>
                                        <td>{{ a.createdDate }}</td>
                                        <td><code class="tmpl">{{ a.template || '—' }}</code></td>
                                        <td class="wof-zero">{{ a.events.length }} event{{ a.events.length !== 1 ? 's' : '' }}</td>
                                    </tr>
                                    <tr v-if="expandedRows.has(a.id)" class="event-expand-row">
                                        <td colspan="9">
                                            <table class="evt-table">
                                                <thead><tr><th>Seq</th><th>Event Type</th><th>Trigger Date</th><th>Completion Date</th><th>Status</th></tr></thead>
                                                <tbody>
                                                    <tr v-for="e in a.events" :key="e.seq">
                                                        <td>{{ e.seq }}</td><td>{{ e.eventType }}</td><td>{{ e.triggerDate }}</td>
                                                        <td>{{ e.completionDate || '—' }}</td>
                                                        <td><span class="evt-status" :class="evtStatusClass(e.status)">{{ e.status }}</span></td>
                                                    </tr>
                                                    <tr v-if="!a.events.length"><td colspan="5" class="wof-zero">No events parsed</td></tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ── Unchanged ── -->
                <div v-if="activeTab === 'unchanged'">
                    <div class="wof-table-ctrl">
                        <input v-model="search.unchanged" class="wof-search" type="search" placeholder="Search…" />
                        <span class="wof-count">{{ filtered(results.unchanged, 'unchanged').length }} accounts</span>
                    </div>
                    <div class="wof-table-wrap">
                        <table>
                            <thead><tr>
                                <th></th><th>Account ID</th><th>WO ID</th><th>Name</th>
                                <th>Amount</th><th>Status</th><th>Created Date</th><th>Template</th><th>Events</th>
                            </tr></thead>
                            <tbody>
                                <template v-for="a in filtered(results.unchanged, 'unchanged')" :key="a.id">
                                    <tr>
                                        <td><button class="wof-expand-btn" @click="toggleRow(a.id)">{{ expandedRows.has(a.id) ? '▲' : '▼' }}</button></td>
                                        <td><code>{{ a.id }}</code></td>
                                        <td><code class="wo-id">{{ a.woId }}</code></td>
                                        <td>{{ a.name }}</td>
                                        <td>${{ a.balance.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                                        <td><span class="wof-status" :class="statusClass(a.status)">{{ a.status || '—' }}</span></td>
                                        <td>{{ a.createdDate }}</td>
                                        <td><code class="tmpl">{{ a.template || '—' }}</code></td>
                                        <td class="wof-zero">{{ a.events.length }} event{{ a.events.length !== 1 ? 's' : '' }}</td>
                                    </tr>
                                    <tr v-if="expandedRows.has(a.id)" class="event-expand-row">
                                        <td colspan="9">
                                            <table class="evt-table">
                                                <thead><tr><th>Seq</th><th>Event Type</th><th>Trigger Date</th><th>Completion Date</th><th>Status</th></tr></thead>
                                                <tbody>
                                                    <tr v-for="e in a.events" :key="e.seq">
                                                        <td>{{ e.seq }}</td><td>{{ e.eventType }}</td><td>{{ e.triggerDate }}</td>
                                                        <td>{{ e.completionDate || '—' }}</td>
                                                        <td><span class="evt-status" :class="evtStatusClass(e.status)">{{ e.status }}</span></td>
                                                    </tr>
                                                    <tr v-if="!a.events.length"><td colspan="5" class="wof-zero">No events parsed</td></tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </main>
    </div>
</template>

<style scoped>
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
    display: flex; align-items: center; gap: 1rem;
}
.wof-header h1 { font-size: 1.3rem; font-weight: 700; color: #4f8ef7; letter-spacing: 0.02em; }
.wof-tag { background: #7c3aed; color: #fff; font-size: 0.7rem; font-weight: 600; padding: 2px 8px; border-radius: 99px; letter-spacing: 0.06em; }
.wof-main { max-width: 1400px; margin: 0 auto; padding: 2rem; }

/* Upload */
.wof-upload-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
@media (max-width: 720px) { .wof-upload-grid { grid-template-columns: 1fr; } }
.wof-drop { display: block; border: 2px dashed #2e3350; border-radius: 12px; background: #1a1d27; padding: 2.5rem 1.5rem; text-align: center; cursor: pointer; transition: border-color 0.2s; position: relative; }
.wof-drop input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.wof-drop:hover { border-color: #4f8ef7; }
.wof-drop.loaded { border-color: #22c55e; }
.wof-drop-icon { font-size: 2.5rem; display: block; margin-bottom: 0.5rem; }
.wof-drop-label { color: #8892b0; font-size: 0.9rem; }
.wof-drop-label small { font-size: 0.8rem; }
.wof-drop-name { color: #22c55e; font-size: 0.85rem; font-weight: 600; margin-top: 0.5rem; }
.wof-progress { margin-top: 0.8rem; height: 4px; background: #2e3350; border-radius: 99px; overflow: hidden; }
.wof-progress-fill { height: 100%; background: #4f8ef7; transition: width 0.1s; }

/* Compare button */
.wof-btn { display: block; width: 100%; padding: 1rem; background: linear-gradient(90deg, #4f8ef7, #7c3aed); color: #fff; border: none; border-radius: 12px; font-size: 1rem; font-weight: 700; cursor: pointer; margin-bottom: 2.5rem; transition: opacity 0.2s; }
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
.wof-insights { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.wof-insight { background: #1a1d27; border: 1px solid #2e3350; border-radius: 12px; padding: 1.2rem; }
.wof-insight-title { font-size: 0.72rem; color: #8892b0; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.5rem; }
.wof-insight-val { font-size: 1.15rem; font-weight: 700; }
.wof-insight-val.green  { color: #22c55e; }
.wof-insight-val.red    { color: #ef4444; }
.wof-insight-val.yellow { color: #eab308; }
.wof-insight-sub { font-size: 0.78rem; color: #8892b0; margin-top: 0.3rem; }

/* Tables */
.wof-table-ctrl { display: flex; gap: 1rem; align-items: center; margin-bottom: 1rem; }
.wof-search { flex: 1; background: #1a1d27; border: 1px solid #2e3350; border-radius: 8px; color: #e8eaf6; padding: 0.5rem 0.8rem; font-size: 0.85rem; }
.wof-search:focus { outline: none; border-color: #4f8ef7; }
.wof-count { font-size: 0.8rem; color: #8892b0; white-space: nowrap; }
.wof-table-wrap { overflow-x: auto; border-radius: 12px; border: 1px solid #2e3350; }
table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
th { background: #22263a; padding: 0.7rem 0.9rem; text-align: left; font-size: 0.72rem; font-weight: 700; color: #8892b0; text-transform: uppercase; letter-spacing: 0.06em; white-space: nowrap; }
td { padding: 0.6rem 0.9rem; border-top: 1px solid #2e3350; vertical-align: middle; }
tr:hover td { background: #1e2035; }
code { font-size: 0.8rem; color: #a78bfa; }
code.wo-id { color: #7dd3fc; }
code.tmpl  { color: #86efac; font-size: 0.75rem; }

/* Expand button */
.wof-expand-btn { background: none; border: 1px solid #2e3350; border-radius: 4px; color: #8892b0; cursor: pointer; font-size: 0.7rem; padding: 2px 6px; transition: color 0.15s, border-color 0.15s; }
.wof-expand-btn:hover { color: #4f8ef7; border-color: #4f8ef7; }

/* Event expand row */
.event-expand-row td { background: #12151f !important; padding: 0.8rem 1rem; }
.event-panels { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.8rem; }
@media (max-width: 900px) { .event-panels { grid-template-columns: 1fr; } }
.event-panel-label { font-size: 0.72rem; font-weight: 700; color: #8892b0; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.5rem; }

/* Event sub-table */
.evt-table { width: 100%; border-collapse: collapse; font-size: 0.78rem; border: 1px solid #2e3350; border-radius: 8px; overflow: hidden; }
.evt-table th { background: #1a1d27; padding: 0.45rem 0.7rem; font-size: 0.68rem; }
.evt-table td { padding: 0.4rem 0.7rem; border-top: 1px solid #1e2035; }
.evt-table tr.evt-highlight td { background: #1e2a1a; }

/* Event status badges */
.evt-status { display: inline-block; padding: 1px 6px; border-radius: 99px; font-size: 0.68rem; font-weight: 700; background: #1e293b; color: #94a3b8; }
.evt-status.evt-done    { background: #14532d; color: #86efac; }
.evt-status.evt-pending { background: #422006; color: #fdba74; }
.evt-status.evt-skip    { background: #1e1b4b; color: #a5b4fc; }

/* Event change summary */
.evt-change-list { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
.evt-change-tag  { background: #2d1a00; border: 1px solid #92400e; color: #fbbf24; font-size: 0.72rem; padding: 2px 8px; border-radius: 99px; }
.evt-change-pill { background: #422006; color: #fdba74; font-size: 0.72rem; padding: 2px 8px; border-radius: 99px; font-weight: 700; }

/* WO status badges */
.wof-status { display: inline-block; padding: 2px 7px; border-radius: 99px; font-size: 0.7rem; font-weight: 700; }
.wof-status.stat-active  { background: #1e3a5f; color: #7dd3fc; }
.wof-status.stat-done    { background: #14532d; color: #86efac; }
.wof-status.stat-cancel  { background: #1c1917; color: #a8a29e; }
.wof-status.stat-pending { background: #422006; color: #fdba74; }

/* Delta colours */
.wof-pos  { color: #ef4444; font-weight: 700; }
.wof-neg  { color: #22c55e; font-weight: 700; }
.wof-zero { color: #8892b0; }
</style>
