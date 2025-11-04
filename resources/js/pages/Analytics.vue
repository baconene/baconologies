<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 flex">
    <!-- Sidebar -->
    <div class="w-64 bg-black/40 backdrop-blur-xl border-r border-white/10 flex-shrink-0">
      <div class="p-6">
        <div class="flex items-center space-x-3 mb-8">
          <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"/>
              <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-xl font-bold text-white">Analytics</h2>
            <p class="text-xs text-gray-400">Visitor Insights</p>
          </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="space-y-2">
          <Link 
            href="/analytics" 
            :class="[
              'flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200',
              currentView === 'dashboard' 
                ? 'bg-purple-600/20 text-purple-400 border border-purple-500/30' 
                : 'text-gray-300 hover:bg-white/5 hover:text-white'
            ]"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
            </svg>
            <span>Dashboard</span>
          </Link>

          <Link 
            href="/analytics/visitors" 
            :class="[
              'flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200',
              currentView === 'visitors' 
                ? 'bg-purple-600/20 text-purple-400 border border-purple-500/30' 
                : 'text-gray-300 hover:bg-white/5 hover:text-white'
            ]"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
            </svg>
            <span>Visitors</span>
          </Link>

          <button 
            @click="currentView = 'charts'"
            :class="[
              'w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 text-left',
              currentView === 'charts' 
                ? 'bg-purple-600/20 text-purple-400 border border-purple-500/30' 
                : 'text-gray-300 hover:bg-white/5 hover:text-white'
            ]"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
            </svg>
            <span>Charts</span>
          </button>

          <button 
            @click="currentView = 'realtime'"
            :class="[
              'w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 text-left',
              currentView === 'realtime' 
                ? 'bg-purple-600/20 text-purple-400 border border-purple-500/30' 
                : 'text-gray-300 hover:bg-white/5 hover:text-white'
            ]"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
            </svg>
            <span>Real-time</span>
          </button>
        </nav>

        <!-- Quick Stats in Sidebar -->
        <div class="mt-8 p-4 bg-white/5 rounded-lg border border-white/10">
          <h3 class="text-sm font-medium text-gray-400 mb-3">Quick Stats</h3>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-xs text-gray-400">Online Now</span>
              <span class="text-green-400 font-medium">{{ stats?.online_visitors || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-xs text-gray-400">Today</span>
              <span class="text-white font-medium">{{ stats?.todays_visitors || 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-xs text-gray-400">Total</span>
              <span class="text-purple-400 font-medium">{{ stats?.total_visitors || 0 }}</span>
            </div>
          </div>
        </div>

        <!-- Back to Home -->
        <div class="mt-8">
          <Link 
            href="/" 
            class="w-full flex items-center justify-center space-x-2 px-4 py-3 bg-gray-700/50 hover:bg-gray-600/50 text-gray-300 hover:text-white rounded-lg transition-colors border border-gray-600/30"
          >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm">Back to Home</span>
          </Link>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
      <!-- Top Bar -->
      <div class="bg-black/20 backdrop-blur-sm border-b border-white/10 p-4">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-2xl font-bold text-white">
              {{ currentViewTitle }}
            </h1>
            <p class="text-sm text-gray-400 mt-1">
              {{ currentViewDescription }}
            </p>
          </div>
          <div class="flex items-center space-x-4">
            <!-- Refresh Button -->
            <button 
              @click="refreshData" 
              :disabled="isRefreshing"
              class="p-2 bg-purple-600/20 hover:bg-purple-600/30 text-purple-400 rounded-lg transition-colors disabled:opacity-50"
            >
              <svg 
                :class="['w-5 h-5', isRefreshing ? 'animate-spin' : '']" 
                fill="currentColor" 
                viewBox="0 0 20 20"
              >
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
              </svg>
            </button>
            
            <!-- Date Filter -->
            <select 
              v-model="dateFilter" 
              @change="applyDateFilter"
              class="bg-gray-800/50 text-white border border-gray-600 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-purple-500"
            >
              <option value="today">Today</option>
              <option value="7d">Last 7 days</option>
              <option value="30d">Last 30 days</option>
              <option value="90d">Last 90 days</option>
              <option value="all">All time</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Dynamic Content Area -->
      <div class="flex-1 p-6 overflow-y-auto">
        <!-- Dashboard View -->
        <div v-if="currentView === 'dashboard'">
          <!-- Stats Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
              <div class="flex items-center">
                <div class="p-3 bg-blue-500/20 rounded-lg">
                  <svg class="w-6 h-6 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm text-gray-300">Total Visitors</p>
                  <p class="text-2xl font-bold text-white">{{ stats?.total_visitors || 0 }}</p>
                </div>
              </div>
            </div>

            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
              <div class="flex items-center">
                <div class="p-3 bg-green-500/20 rounded-lg">
                  <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm text-gray-300">Today's Visits</p>
                  <p class="text-2xl font-bold text-white">{{ stats?.todays_visitors || 0 }}</p>
                </div>
              </div>
            </div>

            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
              <div class="flex items-center">
                <div class="p-3 bg-yellow-500/20 rounded-lg">
                  <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm text-gray-300">This Week</p>
                  <p class="text-2xl font-bold text-white">{{ stats?.weekly_visitors || 0 }}</p>
                </div>
              </div>
            </div>

            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
              <div class="flex items-center">
                <div class="p-3 bg-purple-500/20 rounded-lg">
                  <svg class="w-6 h-6 text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </div>
                <div class="ml-4">
                  <p class="text-sm text-gray-300">Bounce Rate</p>
                  <p class="text-2xl font-bold text-white">{{ stats?.bounce_rate || 0 }}%</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Charts Row -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Daily Visits Chart -->
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
              <h3 class="text-xl font-semibold text-white mb-4">Daily Visits (Last 7 Days)</h3>
              <div class="relative h-64">
                <canvas ref="dailyChart" class="w-full h-full"></canvas>
              </div>
            </div>

            <!-- Device Types Chart -->
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
              <h3 class="text-xl font-semibold text-white mb-4">Device Types</h3>
              <div class="relative h-64">
                <canvas ref="deviceChart" class="w-full h-full"></canvas>
              </div>
            </div>
          </div>

          <!-- Traffic Sources and Top Countries -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Traffic Sources -->
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
              <h3 class="text-xl font-semibold text-white mb-4">Traffic Sources</h3>
              <div class="space-y-3">
                <div v-for="source in stats?.traffic_sources || []" :key="source.source" class="flex justify-between items-center">
                  <span class="text-gray-300">{{ source.source || 'Direct' }}</span>
                  <div class="flex items-center">
                    <div class="w-32 bg-gray-700 rounded-full h-2 mr-3">
                      <div 
                        class="bg-purple-500 h-2 rounded-full" 
                        :style="{ width: (source.count / (stats?.total_visitors || 1) * 100) + '%' }"
                      ></div>
                    </div>
                    <span class="text-white font-medium">{{ source.count }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Top Countries -->
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
              <h3 class="text-xl font-semibold text-white mb-4">Top Countries</h3>
              <div class="space-y-3">
                <div v-for="country in stats?.top_countries || []" :key="country.country" class="flex justify-between items-center">
                  <span class="text-gray-300">{{ country.country || 'Unknown' }}</span>
                  <div class="flex items-center">
                    <div class="w-32 bg-gray-700 rounded-full h-2 mr-3">
                      <div 
                        class="bg-blue-500 h-2 rounded-full" 
                        :style="{ width: (country.count / (stats?.total_visitors || 1) * 100) + '%' }"
                      ></div>
                    </div>
                    <span class="text-white font-medium">{{ country.count }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Visitors View -->
        <div v-else-if="currentView === 'visitors'" class="space-y-6">
          <!-- Filters Bar -->
          <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
            <div class="flex flex-wrap gap-4">
              <div>
                <label class="block text-sm text-gray-400 mb-1">Device Type</label>
                <select 
                  v-model="visitorFilters.device"
                  class="bg-gray-800 text-white border border-gray-600 rounded px-3 py-2 text-sm focus:outline-none focus:border-purple-500"
                >
                  <option value="">All Devices</option>
                  <option value="mobile">Mobile</option>
                  <option value="desktop">Desktop</option>
                  <option value="tablet">Tablet</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm text-gray-400 mb-1">Traffic Source</label>
                <select 
                  v-model="visitorFilters.traffic_source"
                  class="bg-gray-800 text-white border border-gray-600 rounded px-3 py-2 text-sm focus:outline-none focus:border-purple-500"
                >
                  <option value="">All Sources</option>
                  <option value="direct">Direct</option>
                  <option value="social">Social</option>
                  <option value="search">Search</option>
                  <option value="referral">Referral</option>
                </select>
              </div>

              <div>
                <label class="block text-sm text-gray-400 mb-1">Date From</label>
                <input 
                  v-model="visitorFilters.date_from"
                  type="date"
                  class="bg-gray-800 text-white border border-gray-600 rounded px-3 py-2 text-sm focus:outline-none focus:border-purple-500"
                />
              </div>

              <div class="flex items-end">
                <button 
                  @click="applyVisitorFilters"
                  class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded text-sm transition-colors"
                >
                  Apply Filters
                </button>
              </div>
            </div>
          </div>

          <!-- Visitors Table -->
          <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
            <h3 class="text-xl font-semibold text-white mb-6">Visitor Details</h3>
            
            <div class="overflow-x-auto">
              <table class="w-full text-left">
                <thead>
                  <tr class="border-b border-gray-700">
                    <th class="pb-3 text-gray-300 font-medium">IP Address</th>
                    <th class="pb-3 text-gray-300 font-medium">Location</th>
                    <th class="pb-3 text-gray-300 font-medium">Device</th>
                    <th class="pb-3 text-gray-300 font-medium">Browser</th>
                    <th class="pb-3 text-gray-300 font-medium">Traffic Source</th>
                    <th class="pb-3 text-gray-300 font-medium">Visit Time</th>
                  </tr>
                </thead>
                <tbody>
                  <tr 
                    v-for="visitor in visitors?.data || []" 
                    :key="visitor.id"
                    class="border-b border-gray-800 hover:bg-white/5 transition-colors cursor-pointer"
                    @click="selectVisitor(visitor)"
                  >
                    <td class="py-4 text-white">{{ visitor.ip_address }}</td>
                    <td class="py-4 text-gray-300">{{ visitor.location || 'Unknown' }}</td>
                    <td class="py-4">
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-500/20 text-blue-400">
                        {{ visitor.device_type }}
                      </span>
                    </td>
                    <td class="py-4 text-gray-300">{{ visitor.browser_name }}</td>
                    <td class="py-4">
                      <span 
                        v-if="visitor.traffic_source"
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-500/20 text-green-400"
                      >
                        {{ visitor.traffic_source }}
                      </span>
                      <span v-else class="text-gray-500">Direct</span>
                    </td>
                    <td class="py-4 text-gray-300">{{ formatDate(visitor.created_at) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6" v-if="visitors?.total">
              <div class="text-gray-300">
                Showing {{ visitors.from || 0 }}-{{ visitors.to || 0 }} of {{ visitors.total }}
              </div>
              <div class="flex space-x-2">
                <Link 
                  v-if="visitors.prev_page_url"
                  :href="visitors.prev_page_url"
                  class="px-3 py-2 bg-gray-700 text-white hover:bg-gray-600 rounded-lg transition-colors"
                >
                  Previous
                </Link>
                <Link 
                  v-if="visitors.next_page_url"
                  :href="visitors.next_page_url"
                  class="px-3 py-2 bg-gray-700 text-white hover:bg-gray-600 rounded-lg transition-colors"
                >
                  Next
                </Link>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts View -->
        <div v-else-if="currentView === 'charts'" class="space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
              <h3 class="text-xl font-semibold text-white mb-4">Daily Visits Trend</h3>
              <div class="relative h-64">
                <canvas ref="trendChart" class="w-full h-full"></canvas>
              </div>
            </div>
            
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
              <h3 class="text-xl font-semibold text-white mb-4">Browser Distribution</h3>
              <div class="relative h-64">
                <canvas ref="browserChart" class="w-full h-full"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Real-time View -->
        <div v-else-if="currentView === 'realtime'" class="space-y-6">
          <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-semibold text-white">Live Visitors</h3>
              <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                <span class="text-sm text-green-400">Live</span>
              </div>
            </div>
            
            <div class="text-center py-12">
              <div class="text-4xl font-bold text-white mb-2">{{ liveVisitors }}</div>
              <p class="text-gray-400">Visitors in the last 5 minutes</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Visitor Detail Modal -->
    <div v-if="selectedVisitor" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click="closeVisitorModal">
      <div class="bg-gray-900 rounded-lg p-6 max-w-2xl w-full mx-4" @click.stop>
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-semibold text-white">Visitor Details</h3>
          <button @click="closeVisitorModal" class="text-gray-400 hover:text-white">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
          </button>
        </div>
        
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div><span class="text-gray-400">IP Address:</span> <span class="text-white">{{ selectedVisitor.ip_address }}</span></div>
          <div><span class="text-gray-400">Location:</span> <span class="text-white">{{ selectedVisitor.location || 'Unknown' }}</span></div>
          <div><span class="text-gray-400">Device:</span> <span class="text-white">{{ selectedVisitor.device_type }}</span></div>
          <div><span class="text-gray-400">Browser:</span> <span class="text-white">{{ selectedVisitor.browser_name }} {{ selectedVisitor.browser_version }}</span></div>
          <div><span class="text-gray-400">OS:</span> <span class="text-white">{{ selectedVisitor.os_name }} {{ selectedVisitor.os_version }}</span></div>
          <div><span class="text-gray-400">Traffic Source:</span> <span class="text-white">{{ selectedVisitor.traffic_source || 'Direct' }}</span></div>
          <div class="col-span-2"><span class="text-gray-400">Landing Page:</span> <span class="text-white break-all">{{ selectedVisitor.landing_page }}</span></div>
          <div class="col-span-2"><span class="text-gray-400">User Agent:</span> <span class="text-white break-all">{{ selectedVisitor.user_agent }}</span></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ref, onMounted, computed, nextTick, watch } from 'vue'
import Chart from 'chart.js/auto'

interface Visitor {
  id: number
  ip_address: string
  location?: string
  device_type: string
  browser_name: string
  browser_version?: string
  os_name?: string
  os_version?: string
  traffic_source?: string
  landing_page?: string
  user_agent?: string
  created_at: string
}

interface Stats {
  total_visitors: number
  todays_visitors: number
  weekly_visitors: number
  bounce_rate: number
  online_visitors?: number
  traffic_sources: Array<{source: string, count: number}>
  top_countries: Array<{country: string, count: number}>
}

interface PaginatedVisitors {
  data: Visitor[]
  total: number
  from: number
  to: number
  prev_page_url?: string
  next_page_url?: string
}

const props = defineProps<{
  stats?: Stats
  visitors?: PaginatedVisitors | Visitor[]
  dailyVisits?: Array<{date: string, count: number}>
  deviceBreakdown?: Array<{device: string, count: number}>
  activeView?: string
}>()

// Reactive state
const currentView = ref(props.activeView || 'dashboard')
const dateFilter = ref('7d')
const isRefreshing = ref(false)
const selectedVisitor = ref<Visitor | null>(null)
const liveVisitors = ref(0)

// Visitor filters
const visitorFilters = ref({
  device: '',
  traffic_source: '',
  date_from: '',
  date_to: ''
})

// Canvas refs
const dailyChart = ref<HTMLCanvasElement>()
const deviceChart = ref<HTMLCanvasElement>()
const trendChart = ref<HTMLCanvasElement>()
const browserChart = ref<HTMLCanvasElement>()

// Computed properties
const currentViewTitle = computed(() => {
  switch (currentView.value) {
    case 'dashboard': return 'Analytics Dashboard'
    case 'visitors': return 'Visitor Management'
    case 'charts': return 'Analytics Charts'
    case 'realtime': return 'Real-time Analytics'
    default: return 'Analytics'
  }
})

const currentViewDescription = computed(() => {
  switch (currentView.value) {
    case 'dashboard': return 'Overview of your website analytics and visitor statistics'
    case 'visitors': return 'Detailed visitor information and traffic analysis'
    case 'charts': return 'Visual representation of your analytics data'
    case 'realtime': return 'Live visitor tracking and real-time analytics'
    default: return 'Website analytics and visitor tracking'
  }
})

// Methods
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString()
}

const refreshData = async () => {
  isRefreshing.value = true
  // Simulate refresh - in real app, you'd reload data from server
  setTimeout(() => {
    isRefreshing.value = false
    // You can use Inertia.reload() here or make an API call
  }, 1000)
}

const applyDateFilter = () => {
  // Handle date filter changes
  console.log('Applying date filter:', dateFilter.value)
}

const applyVisitorFilters = () => {
  // Handle visitor filters
  console.log('Applying visitor filters:', visitorFilters.value)
  // In a real app, you'd use Inertia.get() to reload with filters
}

const selectVisitor = (visitor: Visitor) => {
  selectedVisitor.value = visitor
}

const closeVisitorModal = () => {
  selectedVisitor.value = null
}

const renderCharts = () => {
  try {
    console.log('Rendering charts...', { 
      dailyVisits: props.dailyVisits, 
      deviceBreakdown: props.deviceBreakdown,
      stats: props.stats
    })
    
    // Daily visits chart
    if (dailyChart.value && Chart) {
      const ctx = dailyChart.value.getContext('2d')
      if (ctx) {
        console.log('Creating daily chart...')
        
        // Sample data if no real data available
        const sampleDates = Array.from({length: 7}, (_, i) => {
          const date = new Date()
          date.setDate(date.getDate() - (6 - i))
          return date.toLocaleDateString()
        })
        
        const sampleData = props.dailyVisits && props.dailyVisits.length > 0 
          ? props.dailyVisits.map(item => item.visitors || item.count || 0)
          : [2, 5, 8, 3, 7, 4, 6]
        
        new Chart(ctx, {
          type: 'line',
          data: {
            labels: props.dailyVisits && props.dailyVisits.length > 0
              ? props.dailyVisits.map(item => new Date(item.date).toLocaleDateString())
              : sampleDates,
            datasets: [{
              label: 'Daily Visits',
              data: sampleData,
              borderColor: '#8b5cf6',
              backgroundColor: 'rgba(139, 92, 246, 0.1)',
              tension: 0.4,
              fill: true
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                labels: { color: '#ffffff' }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: { color: '#9ca3af' },
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
              },
              x: {
                ticks: { color: '#9ca3af' },
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
              }
            }
          }
        })
      }
    }
    
    // Device chart (Doughnut)
    if (deviceChart.value && Chart) {
      const ctx = deviceChart.value.getContext('2d')
      if (ctx) {
        console.log('Creating device chart...')
        
        // Use real data from deviceBreakdown or fallback to stats
        const deviceData = props.deviceBreakdown && props.deviceBreakdown.length > 0
          ? props.deviceBreakdown
          : [
              { device: 'Desktop', count: props.stats?.total_visitors || 10 },
              { device: 'Mobile', count: 2 },
              { device: 'Tablet', count: 1 }
            ]
        
        new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: deviceData.map(item => item.device),
            datasets: [{
              data: deviceData.map(item => item.count),
              backgroundColor: [
                '#3b82f6',
                '#10b981',
                '#f59e0b'
              ],
              borderWidth: 0
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: { 
                  color: '#ffffff',
                  padding: 20
                }
              }
            }
          }
        })
      }
    }

    // Trend chart (for charts view)
    if (trendChart.value && Chart) {
      const ctx = trendChart.value.getContext('2d')
      if (ctx) {
        console.log('Creating trend chart...')
        
        const trendLabels = props.dailyVisits && props.dailyVisits.length > 0
          ? props.dailyVisits.map(item => new Date(item.date).toLocaleDateString())
          : Array.from({length: 7}, (_, i) => {
              const date = new Date()
              date.setDate(date.getDate() - (6 - i))
              return date.toLocaleDateString()
            })
        
        const trendData = props.dailyVisits && props.dailyVisits.length > 0
          ? props.dailyVisits.map(item => item.visitors || item.count || 0)
          : [3, 6, 4, 8, 2, 7, 5]
        
        new Chart(ctx, {
          type: 'bar',
          data: {
            labels: trendLabels,
            datasets: [{
              label: 'Visits Trend',
              data: trendData,
              backgroundColor: 'rgba(16, 185, 129, 0.8)',
              borderColor: '#10b981',
              borderWidth: 1
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                labels: { color: '#ffffff' }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: { color: '#9ca3af' },
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
              },
              x: {
                ticks: { color: '#9ca3af' },
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
              }
            }
          }
        })
      }
    }

    // Browser chart (Polar Area)
    if (browserChart.value && Chart) {
      const ctx = browserChart.value.getContext('2d')
      if (ctx) {
        console.log('Creating browser chart...')
        
        const browserData = [
          { browser: 'Chrome', count: props.stats?.total_visitors || 8 },
          { browser: 'Firefox', count: 2 },
          { browser: 'Safari', count: 1 },
          { browser: 'Edge', count: 1 }
        ]
        
        new Chart(ctx, {
          type: 'polarArea',
          data: {
            labels: browserData.map(item => item.browser),
            datasets: [{
              data: browserData.map(item => item.count),
              backgroundColor: [
                'rgba(245, 158, 11, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(59, 130, 246, 0.8)',
                'rgba(16, 185, 129, 0.8)'
              ],
              borderColor: [
                '#f59e0b',
                '#ef4444',
                '#3b82f6',
                '#10b981'
              ],
              borderWidth: 2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: { 
                  color: '#ffffff',
                  padding: 20
                }
              }
            },
            scales: {
              r: {
                ticks: { color: '#9ca3af' },
                grid: { color: 'rgba(255, 255, 255, 0.1)' }
              }
            }
          }
        })
      }
    }
  } catch (error) {
    console.error('Error rendering charts:', error)
  }
}

const updateLiveVisitors = () => {
  // Simulate live visitor count updates
  liveVisitors.value = Math.floor(Math.random() * 10) + 1
}

// Watch for view changes to re-render charts
watch(currentView, (newView) => {
  if (newView === 'charts' || newView === 'dashboard') {
    setTimeout(() => {
      renderCharts()
    }, 100)
  }
})

onMounted(() => {
  // Render charts after DOM is ready
  setTimeout(() => {
    renderCharts()
  }, 100)
  
  // Update live visitors every 30 seconds
  const interval = setInterval(updateLiveVisitors, 30000)
  updateLiveVisitors() // Initial call
  
  // Cleanup interval on unmount
  return () => clearInterval(interval)
})
</script>