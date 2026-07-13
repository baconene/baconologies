<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head } from '@inertiajs/vue3'

const activeTool = ref('comparator')
const sidebarOpen = ref(false)

const tools = [
    { id: 'comparator', name: 'Comparator', icon: '⚖️', description: 'Compare items side by side' },
    { id: 'analyzer', name: 'Analyzer', icon: '📊', description: 'Deep analysis tools' },
    { id: 'generator', name: 'Generator', icon: '✨', description: 'Generate content and data' },
    { id: 'validator', name: 'Validator', icon: '✓', description: 'Validate and verify data' },
    { id: 'converter', name: 'Converter', icon: '🔄', description: 'Convert between formats' },
]

const currentTool = computed(() => tools.find(t => t.id === activeTool.value))

const comparatorItems = ref([
    { name: 'Item 1', properties: { type: 'Type A', status: 'Active', value: 100 } },
    { name: 'Item 2', properties: { type: 'Type B', status: 'Inactive', value: 75 } },
])

function addComparatorItem() {
    comparatorItems.value.push({
        name: `Item ${comparatorItems.value.length + 1}`,
        properties: { type: '', status: '', value: 0 }
    })
}

function removeComparatorItem(index: number) {
    comparatorItems.value.splice(index, 1)
}
</script>

<template>
    <Head title="Carmel Dashboard" />

    <div class="flex h-screen overflow-hidden bg-[#0a0815] text-white" style="font-family: 'Courier New', Courier, monospace;">

        <!-- ══════════════════════════════════════════════════════════
             SIDEBAR
        ══════════════════════════════════════════════════════════ -->
        <div class="hidden md:flex flex-col w-64 bg-black/40 border-r border-green-400/10">
            <!-- Logo -->
            <div class="p-6 border-b border-green-400/10">
                <h1 class="text-xl font-black text-white tracking-wider">
                    <span class="text-green-400">CARMEL</span>
                </h1>
                <p class="text-xs text-white/40 mt-1 tracking-widest">DASHBOARD</p>
            </div>

            <!-- Tools Menu -->
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <div v-for="tool in tools" :key="tool.id"
                     @click="activeTool = tool.id"
                     class="group cursor-pointer p-3 rounded-lg transition-all duration-200"
                     :class="activeTool === tool.id
                         ? 'bg-green-400/15 border border-green-400/40'
                         : 'hover:bg-green-400/5 border border-transparent'">
                    <div class="flex items-center gap-3">
                        <span class="text-xl">{{ tool.icon }}</span>
                        <div>
                            <div class="font-bold text-sm">{{ tool.name }}</div>
                            <div class="text-xs text-white/40">{{ tool.description }}</div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-green-400/10">
                <p class="text-xs text-white/30 text-center tracking-widest">TOOLS v1.0</p>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             MAIN CONTENT
        ══════════════════════════════════════════════════════════ -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Top Bar (Mobile + Branding) -->
            <div class="h-16 bg-black/40 border-b border-green-400/10 flex items-center justify-between px-6">
                <div>
                    <h2 class="text-lg font-bold text-white">{{ currentTool?.name || 'Dashboard' }}</h2>
                    <p class="text-xs text-white/40 mt-0.5">{{ currentTool?.description }}</p>
                </div>
                <button class="md:hidden text-green-400 text-xl" @click="sidebarOpen = !sidebarOpen">☰</button>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-auto p-6">

                <!-- ══════════════════════════════════════════════════════
                     COMPARATOR TOOL
                ══════════════════════════════════════════════════════ -->
                <div v-if="activeTool === 'comparator'" class="space-y-6">
                    <div class="bg-gradient-to-br from-green-400/10 to-transparent border border-green-400/20 rounded-xl p-6">
                        <h3 class="text-xl font-bold text-green-400 mb-2">Comparator Tool</h3>
                        <p class="text-sm text-white/60">Add items to compare side by side. View all properties at a glance.</p>
                    </div>

                    <!-- Items Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="(item, index) in comparatorItems" :key="index"
                             class="bg-gradient-to-br from-blue-400/10 to-transparent border border-blue-400/20 rounded-xl p-5 relative group">

                            <!-- Remove Button -->
                            <button @click="removeComparatorItem(index)"
                                    class="absolute -top-3 -right-3 w-7 h-7 rounded-full bg-red-500/20 hover:bg-red-500/40 flex items-center justify-center text-red-400 text-sm opacity-0 group-hover:opacity-100 transition-all">
                                ✕
                            </button>

                            <!-- Item Header -->
                            <input v-model="item.name"
                                   class="w-full font-bold text-base text-white bg-transparent border-b border-blue-400/30 pb-2 mb-4 focus:outline-none focus:border-blue-400"
                                   placeholder="Item name">

                            <!-- Properties -->
                            <div class="space-y-3">
                                <div v-for="(value, key) in item.properties" :key="key"
                                     class="flex flex-col gap-1">
                                    <label class="text-xs text-white/50 uppercase tracking-wide">{{ key }}</label>
                                    <input v-model="item.properties[key as keyof typeof item.properties]"
                                           class="px-3 py-2 bg-white/5 border border-white/10 rounded text-sm text-white focus:outline-none focus:border-blue-400 transition-colors"
                                           placeholder="Enter value">
                                </div>
                            </div>
                        </div>

                        <!-- Add New Item Button -->
                        <button @click="addComparatorItem"
                                class="bg-gradient-to-br from-green-400/10 to-transparent border-2 border-dashed border-green-400/40 hover:border-green-400/60 rounded-xl p-5 flex items-center justify-center min-h-[280px] transition-all cursor-pointer group">
                            <div class="text-center">
                                <div class="text-3xl text-green-400 mb-2 group-hover:scale-110 transition-transform">+</div>
                                <div class="text-sm font-bold text-green-400">Add Item</div>
                                <div class="text-xs text-white/40 mt-1">Click to add</div>
                            </div>
                        </button>
                    </div>

                    <!-- Comparison Table -->
                    <div v-if="comparatorItems.length > 0" class="mt-8">
                        <h4 class="text-lg font-bold text-green-400 mb-4">Property Comparison</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse">
                                <thead>
                                    <tr class="border-b border-green-400/20">
                                        <th class="text-left p-3 text-green-400 font-bold">Property</th>
                                        <th v-for="item in comparatorItems" :key="item.name"
                                            class="text-left p-3 text-green-400 font-bold">{{ item.name }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(value, propKey) in comparatorItems[0]?.properties" :key="propKey"
                                        class="border-b border-green-400/10 hover:bg-green-400/5 transition-colors">
                                        <td class="p-3 text-white/70 font-bold capitalize">{{ propKey }}</td>
                                        <td v-for="item in comparatorItems" :key="item.name"
                                            class="p-3 text-white/80">{{ item.properties[propKey as keyof typeof item.properties] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════════
                     OTHER TOOLS (Placeholder)
                ══════════════════════════════════════════════════════ -->
                <div v-else class="text-center py-12">
                    <div class="text-4xl mb-4">{{ currentTool?.icon }}</div>
                    <h3 class="text-2xl font-bold text-white mb-2">{{ currentTool?.name }} Tool</h3>
                    <p class="text-white/60 max-w-md mx-auto">
                        This tool is coming soon. Stay tuned for more features!
                    </p>
                </div>

            </div>
        </div>

    </div>
</template>

<style scoped>
/* Smooth scrolling */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: rgba(34, 197, 94, 0.3);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(34, 197, 94, 0.5);
}
</style>
