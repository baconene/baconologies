<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-gray-900 to-black text-white">
    <!-- Header -->
    <header class="border-b border-white/10 bg-black/20 backdrop-blur-lg">
      <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between">
          <h1 class="text-2xl font-bold bg-gradient-to-r from-pink-400 to-orange-400 bg-clip-text text-transparent">
            Baconologies Demo Systems
          </h1>
          <a href="/" class="text-gray-400 hover:text-pink-400 transition-colors">← Back to Home</a>
        </div>
      </div>
    </header>

    <!-- Demo Systems Grid -->
    <main class="py-20 px-4">
      <div class="max-w-7xl mx-auto">
        <div class="text-center mb-16">
          <h2 class="text-5xl font-bold mb-6 bg-gradient-to-r from-blue-400 to-pink-400 bg-clip-text text-transparent">
            Live Demo Systems
          </h2>
          <p class="text-xl text-gray-400 max-w-3xl mx-auto">
            Experience our production-ready systems currently serving real clients and users worldwide.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- LEMA LMS -->
          <div class="group relative p-8 rounded-2xl bg-white/5 backdrop-blur-lg border border-white/10 hover:border-pink-400/50 transition-all duration-500">
            <div class="absolute inset-0 bg-gradient-to-br from-pink-500/10 to-blue-500/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10">
              <div class="w-16 h-16 mb-6 rounded-full bg-gradient-to-br from-pink-400 to-orange-400 flex items-center justify-center text-2xl">
                🎓
              </div>
              <h3 class="text-3xl font-bold mb-4 text-white group-hover:text-pink-400 transition-colors">
                LEMA Learning Management System
              </h3>
              <p class="text-gray-400 group-hover:text-gray-300 transition-colors leading-relaxed mb-6">
                A comprehensive LMS platform designed for modern educational institutions, featuring interactive courses, 
                assessment tools, and progress tracking capabilities.
              </p>
              
              <div class="space-y-3 mb-6">
                <div class="flex items-center text-green-400">
                  <span class="mr-3">✓</span>
                  <span>Interactive Course Builder</span>
                </div>
                <div class="flex items-center text-green-400">
                  <span class="mr-3">✓</span>
                  <span>Real-time Progress Tracking</span>
                </div>
                <div class="flex items-center text-green-400">
                  <span class="mr-3">✓</span>
                  <span>Multi-media Content Support</span>
                </div>
                <div class="flex items-center text-green-400">
                  <span class="mr-3">✓</span>
                  <span>Advanced Assessment Tools</span>
                </div>
              </div>

              <div class="flex space-x-4">
                <a href="https://lema.baconologies.com" target="_blank" 
                   class="flex-1 text-center py-3 bg-gradient-to-r from-pink-500 to-orange-500 rounded-lg font-semibold hover:shadow-lg hover:shadow-pink-500/25 transition-all duration-300 transform hover:scale-105">
                  Visit LEMA →
                </a>
                <button class="px-6 py-3 border-2 border-blue-400 rounded-lg text-blue-400 hover:bg-blue-400 hover:text-black transition-all duration-300">
                  Learn More
                </button>
              </div>
            </div>
          </div>

          <!-- LyrSync -->
          <div class="group relative p-8 rounded-2xl bg-white/5 backdrop-blur-lg border border-white/10 hover:border-blue-400/50 transition-all duration-500">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-pink-500/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10">
              <div class="w-16 h-16 mb-6 rounded-full bg-gradient-to-br from-blue-400 to-pink-400 flex items-center justify-center text-2xl">
                🎵
              </div>
              <h3 class="text-3xl font-bold mb-4 text-white group-hover:text-blue-400 transition-colors">
                LyrSync Music Platform
              </h3>
              <p class="text-gray-400 group-hover:text-gray-300 transition-colors leading-relaxed mb-6">
                An innovative music learning and synchronization platform that combines lyrics, timing, and interactive 
                music education tools for enhanced learning experiences.
              </p>
              
              <div class="space-y-3 mb-6">
                <div class="flex items-center text-green-400">
                  <span class="mr-3">✓</span>
                  <span>Synchronized Lyrics Display</span>
                </div>
                <div class="flex items-center text-green-400">
                  <span class="mr-3">✓</span>
                  <span>Interactive Music Lessons</span>
                </div>
                <div class="flex items-center text-green-400">
                  <span class="mr-3">✓</span>
                  <span>Tempo and Pitch Analysis</span>
                </div>
                <div class="flex items-center text-green-400">
                  <span class="mr-3">✓</span>
                  <span>Performance Tracking</span>
                </div>
              </div>

              <div class="flex space-x-4">
                <a href="https://lyrsync.baconologies.com" target="_blank" 
                   class="flex-1 text-center py-3 bg-gradient-to-r from-blue-500 to-pink-500 rounded-lg font-semibold hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-300 transform hover:scale-105">
                  Visit LyrSync →
                </a>
                <button class="px-6 py-3 border-2 border-orange-400 rounded-lg text-orange-400 hover:bg-orange-400 hover:text-black transition-all duration-300">
                  Learn More
                </button>
              </div>
            </div>
          </div>

          <!-- Coming Soon: Utility Management -->
          <div class="group relative p-8 rounded-2xl bg-white/5 backdrop-blur-lg border border-white/10 hover:border-orange-400/50 transition-all duration-500 opacity-75">
            <div class="absolute inset-0 bg-gradient-to-br from-orange-500/10 to-yellow-500/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10">
              <div class="w-16 h-16 mb-6 rounded-full bg-gradient-to-br from-orange-400 to-yellow-400 flex items-center justify-center text-2xl">
                ⚡
              </div>
              <h3 class="text-3xl font-bold mb-4 text-white group-hover:text-orange-400 transition-colors">
                Utility Management Platform
                <span class="text-sm bg-orange-500/20 text-orange-400 px-3 py-1 rounded-full ml-3">Coming Soon</span>
              </h3>
              <p class="text-gray-400 group-hover:text-gray-300 transition-colors leading-relaxed mb-6">
                Enterprise-grade utility management system based on Oracle OUAF framework, designed for 
                comprehensive resource tracking, billing integration, and operational efficiency.
              </p>
              
              <div class="space-y-3 mb-6">
                <div class="flex items-center text-orange-400">
                  <span class="mr-3">⏳</span>
                  <span>Resource Usage Tracking</span>
                </div>
                <div class="flex items-center text-orange-400">
                  <span class="mr-3">⏳</span>
                  <span>Automated Billing Integration</span>
                </div>
                <div class="flex items-center text-orange-400">
                  <span class="mr-3">⏳</span>
                  <span>Real-time Monitoring</span>
                </div>
                <div class="flex items-center text-orange-400">
                  <span class="mr-3">⏳</span>
                  <span>Predictive Analytics</span>
                </div>
              </div>

              <div class="flex space-x-4">
                <button disabled 
                        class="flex-1 text-center py-3 bg-gray-600 rounded-lg font-semibold cursor-not-allowed opacity-50">
                  Coming Q2 2025
                </button>
                <button class="px-6 py-3 border-2 border-orange-400 rounded-lg text-orange-400 hover:bg-orange-400 hover:text-black transition-all duration-300">
                  Get Notified
                </button>
              </div>
            </div>
          </div>

          <!-- Coming Soon: Geomapping -->
          <div class="group relative p-8 rounded-2xl bg-white/5 backdrop-blur-lg border border-white/10 hover:border-green-400/50 transition-all duration-500 opacity-75">
            <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-blue-500/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10">
              <div class="w-16 h-16 mb-6 rounded-full bg-gradient-to-br from-green-400 to-blue-400 flex items-center justify-center text-2xl">
                🗺️
              </div>
              <h3 class="text-3xl font-bold mb-4 text-white group-hover:text-green-400 transition-colors">
                Geospatial Hazard Mapping
                <span class="text-sm bg-green-500/20 text-green-400 px-3 py-1 rounded-full ml-3">In Development</span>
              </h3>
              <p class="text-gray-400 group-hover:text-gray-300 transition-colors leading-relaxed mb-6">
                Advanced geospatial mapping platform inspired by Project NOAH and Hazard.ph, providing 
                real-time hazard monitoring, infrastructure mapping, and disaster preparedness tools.
              </p>
              
              <div class="space-y-3 mb-6">
                <div class="flex items-center text-green-400">
                  <span class="mr-3">🚧</span>
                  <span>Real-time Hazard Monitoring</span>
                </div>
                <div class="flex items-center text-green-400">
                  <span class="mr-3">🚧</span>
                  <span>Infrastructure Asset Mapping</span>
                </div>
                <div class="flex items-center text-green-400">
                  <span class="mr-3">🚧</span>
                  <span>Disaster Risk Assessment</span>
                </div>
                <div class="flex items-center text-green-400">
                  <span class="mr-3">🚧</span>
                  <span>Emergency Response Integration</span>
                </div>
              </div>

              <div class="flex space-x-4">
                <button disabled 
                        class="flex-1 text-center py-3 bg-gray-600 rounded-lg font-semibold cursor-not-allowed opacity-50">
                  Beta Q3 2025
                </button>
                <button class="px-6 py-3 border-2 border-green-400 rounded-lg text-green-400 hover:bg-green-400 hover:text-black transition-all duration-300">
                  Join Beta
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Call to Action -->
        <div class="text-center mt-16">
          <div class="max-w-3xl mx-auto p-8 rounded-2xl bg-gradient-to-r from-pink-500/10 via-purple-500/10 to-blue-500/10 border border-white/20">
            <h3 class="text-3xl font-bold mb-4 bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent">
              Ready to Build Your Custom Solution?
            </h3>
            <p class="text-gray-300 mb-6">
              Whether you need an LMS like LEMA, a specialized platform like LyrSync, or a completely custom solution, 
              we're here to turn your vision into reality.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
              <button class="px-8 py-4 bg-gradient-to-r from-pink-500 to-orange-500 rounded-full text-lg font-semibold hover:shadow-2xl hover:shadow-pink-500/25 transition-all duration-300 transform hover:scale-105">
                Start Your Project
              </button>
              <button class="px-8 py-4 border-2 border-blue-400 rounded-full text-lg font-semibold text-blue-400 hover:bg-blue-400 hover:text-black transition-all duration-300">
                Schedule Consultation
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'

onMounted(() => {
  // Add any demo-specific initialization here
  console.log('Baconologies Demo Systems loaded')
})
</script>