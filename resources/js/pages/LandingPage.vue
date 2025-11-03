<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-gray-900 to-black text-white">
    <!-- Embedded Transparent Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-transparent backdrop-blur-sm">
      <div class="max-w-7xl mx-auto px-6 py-6 flex justify-between items-center">
        <h1 class="text-xl font-bold bg-gradient-to-r from-pink-400 to-orange-400 bg-clip-text text-transparent">
          Baconologies.com
        </h1>
        <div class="space-x-6">
          <a href="#hero" class="text-gray-300/80 hover:text-pink-400 transition-colors duration-300 text-sm font-medium">Home</a>
          <a href="#services" class="text-gray-300/80 hover:text-blue-400 transition-colors duration-300 text-sm font-medium">Services</a>
          <a href="#contact" class="text-gray-300/80 hover:text-orange-400 transition-colors duration-300 text-sm font-medium">Contact</a>
        </div>
      </div>
    </nav>
    <!-- Animated Background -->
    <div class="fixed inset-0 z-0">
      <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 via-pink-600/10 to-orange-600/10"></div>
      <div ref="particles" class="absolute inset-0"></div>
    </div>

    <!-- Hero Section -->
    <section id="hero" ref="hero" class="relative z-10 min-h-screen flex items-center justify-center px-4 pt-20 overflow-hidden">
      <!-- Diagonal Background Images -->
      <div class="absolute inset-0 z-0 opacity-30">
        <div class="absolute top-0 left-0 w-full h-full grid grid-cols-2 grid-rows-2 gap-4 transform rotate-12 scale-110 -translate-y-10">
          <!-- Top Left - Education/Learning -->
          <div class="diagonal-bg-education relative h-full w-full rounded-2xl overflow-hidden shadow-2xl shadow-blue-500/20">
            <img src="/images/learn.webp" alt="Learning Systems" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500/70 via-cyan-400/50 to-blue-600/70 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-blue-900/20"></div>
          </div>
          <!-- Top Right - Billing/Payment -->
          <div class="diagonal-bg-billing relative h-full w-full rounded-2xl overflow-hidden shadow-2xl shadow-pink-500/20">
            <img src="/images/billing.webp" alt="Billing Systems" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-bl from-pink-500/70 via-rose-400/50 to-pink-600/70 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-pink-900/20"></div>
          </div>
          <!-- Bottom Left - Mapping/Infrastructure -->
          <div class="diagonal-bg-mapping relative h-full w-full rounded-2xl overflow-hidden shadow-2xl shadow-orange-500/20">
            <img src="/images/geomap.webp" alt="Mapping Systems" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-tr from-orange-500/70 via-amber-400/50 to-orange-600/70 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-orange-900/20"></div>
          </div>
          <!-- Bottom Right - Utilities/Grid -->
          <div class="diagonal-bg-utilities relative h-full w-full rounded-2xl overflow-hidden shadow-2xl shadow-purple-500/20">
            <img src="/images/grid.webp" alt="Grid Systems" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-tl from-purple-500/70 via-violet-400/50 to-purple-600/70 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-purple-900/20"></div>
          </div>
        </div>
      </div>
      <div class="text-center max-w-6xl mx-auto relative z-10">
        <div ref="heroContent" class="space-y-8">
          <h1 class="text-6xl md:text-8xl font-bold bg-gradient-to-r from-pink-400 via-orange-400 to-blue-400 bg-clip-text text-transparent leading-tight">
            Engineering Smart Solutions
          </h1>
          <h2 class="text-3xl md:text-5xl font-light text-gray-300">
            for a <span class="text-transparent bg-gradient-to-r from-blue-400 to-pink-400 bg-clip-text">Digital Future</span>
          </h2>
          <p class="text-xl md:text-2xl text-gray-400 max-w-4xl mx-auto leading-relaxed">
            Empowering learning, utilities, billing, and mapping systems through modern innovation at 
            <span class="text-orange-400 font-semibold">Baconologies.com</span>
          </p>
          <div class="flex flex-col sm:flex-row gap-6 justify-center mt-12">
            <a href="/demo" ref="btnGetStarted" class="group relative px-8 py-4 bg-gradient-to-r from-pink-500 to-orange-500 rounded-full text-lg font-semibold hover:shadow-2xl hover:shadow-pink-500/25 transition-all duration-300 transform hover:scale-105 text-center">
              <span class="relative z-10">View Live Demos</span>
              <div class="absolute inset-0 rounded-full bg-gradient-to-r from-pink-400 to-orange-400 opacity-0 group-hover:opacity-75 blur transition-opacity duration-300"></div>
            </a>
            <a href="#services" ref="btnServices" class="group relative inline-block px-8 py-4 border-2 border-blue-400 rounded-full text-lg font-semibold text-blue-400 hover:bg-blue-400 hover:text-black transition-all duration-300 transform hover:scale-105">
              Our Services
              <div class="absolute inset-0 rounded-full bg-blue-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- About/Services Section -->
    <section id="services" ref="services" class="relative z-10 py-32 px-4 overflow-hidden">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-5xl md:text-6xl font-bold text-center mb-20 bg-gradient-to-r from-blue-400 to-pink-400 bg-clip-text text-transparent">
          What We Build
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 lg:gap-16">
          <div v-for="(service, index) in servicesData" :key="index" 
               ref="serviceCards"
               class="service-item group text-center space-y-6 transform transition-all duration-700 hover:scale-110">
            
            <!-- Animated SVG Icon -->
            <div class="service-icon-container relative mx-auto">
              <div class="w-20 h-20 mx-auto text-blue-400 group-hover:text-orange-400 transition-all duration-500 transform group-hover:scale-125 group-hover:rotate-12" 
                   v-html="service.svg"></div>
              
              <!-- Spotlight Effect -->
              <div class="service-spotlight"></div>
              
              <!-- Glow Effect -->
              <div class="absolute inset-0 w-20 h-20 mx-auto bg-gradient-to-r from-pink-500/30 to-blue-500/30 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
              
              <!-- Pulse Ring -->
              <div class="absolute inset-0 w-20 h-20 mx-auto rounded-full border-2 border-blue-400/20 group-hover:border-orange-400/60 transition-all duration-500"></div>
            </div>
            
            <!-- Animated Title -->
            <h3 class="text-xl lg:text-2xl font-bold text-white group-hover:text-transparent group-hover:bg-gradient-to-r group-hover:from-pink-400 group-hover:to-orange-400 group-hover:bg-clip-text transition-all duration-500 transform group-hover:translate-y-[-4px]">
              {{ service.title }}
            </h3>
            
            <!-- Animated Description -->
            <p class="text-sm lg:text-base text-gray-400 group-hover:text-gray-200 leading-relaxed transition-all duration-500 transform group-hover:translate-y-[-2px] opacity-80 group-hover:opacity-100">
              {{ service.description }}
            </p>
            
            <!-- Hover Line -->
            <div class="w-0 h-0.5 bg-gradient-to-r from-pink-400 to-orange-400 mx-auto transition-all duration-500 group-hover:w-16"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Technology Stack Section -->
    <section id="techStack" ref="techStack" class="relative z-10 py-20 px-4 bg-black/20">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-5xl font-bold text-center mb-16 bg-gradient-to-r from-orange-400 to-blue-400 bg-clip-text text-transparent">
          Our Stack
        </h2>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-7 gap-6">
          <div v-for="(tech, index) in technologiesData" :key="index"
               ref="techLogos"
               class="group flex flex-col items-center transition-all duration-300 transform hover:scale-110 hover:-translate-y-2">
            <div class="tech-logo-container relative mb-3">
              <img 
                :src="tech.logo" 
                :alt="tech.name"
                class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 mx-auto filter brightness-90 hover:brightness-110 transition-all duration-300 object-contain"
                style="filter: drop-shadow(0 0 15px rgba(251, 146, 60, 0.4));"
                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
              />
              <div class="w-12 h-12 sm:w-14 sm:h-14 lg:w-16 lg:h-16 mx-auto bg-gradient-to-br from-gray-700 to-gray-800 rounded-lg items-center justify-center text-lg font-bold text-orange-400 hidden">
                {{ tech.name.charAt(0) }}
              </div>
              <div class="absolute inset-0 bg-gradient-to-r from-orange-500/20 to-amber-500/20 rounded-full blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            </div>
            <span class="text-xs sm:text-sm font-medium text-gray-300 group-hover:text-orange-400 transition-colors text-center mb-1 leading-tight">
              {{ tech.name }}
            </span>
            <span class="text-xs text-gray-500 group-hover:text-gray-400 transition-colors text-center">
              {{ tech.category }}
            </span>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" ref="pricing" class="relative z-10 py-20 px-4">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-5xl font-bold text-center mb-8 bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent">
          Flexible Subscription Models
        </h2>
        <p class="text-xl text-gray-400 text-center mb-16 max-w-4xl mx-auto">
          We offer both SaaS (Software-as-a-Service) and IaaS (Infrastructure-as-a-Service) options — 
          pricing may vary depending on scope and complexity.
        </p>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="(plan, index) in pricingPlansData" :key="index"
               ref="pricingCards"
               class="group relative p-8 rounded-2xl bg-white/5 backdrop-blur-lg border border-white/10 hover:border-blue-400/50 transition-all duration-500 transform hover:scale-105 hover:-translate-y-4"
               :class="plan.featured ? 'border-pink-400/50 bg-gradient-to-b from-pink-500/10 to-blue-500/10' : ''">
            <div v-if="plan.featured" class="absolute -top-4 left-1/2 transform -translate-x-1/2">
              <span class="bg-gradient-to-r from-pink-400 to-orange-400 text-black px-4 py-2 rounded-full text-sm font-bold">
                Most Popular
              </span>
            </div>
            <div class="relative z-10">
              <h3 class="text-2xl font-bold mb-2 text-white">{{ plan.name }}</h3>
              <div class="text-4xl font-bold mb-6 text-transparent bg-gradient-to-r from-pink-400 to-orange-400 bg-clip-text">
                {{ plan.price }}
              </div>
              <p class="text-gray-400 mb-8">{{ plan.description }}</p>
              <ul class="space-y-3 mb-8">
                <li v-for="feature in plan.features" :key="feature" class="flex items-center text-gray-300">
                  <span class="text-green-400 mr-3">✓</span>
                  {{ feature }}
                </li>
              </ul>
              <button class="w-full py-3 rounded-lg font-semibold transition-all duration-300 transform group-hover:scale-105"
                      :class="plan.featured 
                        ? 'bg-gradient-to-r from-pink-500 to-orange-500 text-white hover:shadow-lg hover:shadow-pink-500/25' 
                        : 'border-2 border-blue-400 text-blue-400 hover:bg-blue-400 hover:text-black'">
                Get Started
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

   <!-- Team Section -->
  <section
    id="team"
    ref="team"
    class="relative z-10 py-24 px-6 bg-gradient-to-b from-black via-slate-900 to-black overflow-hidden text-gray-300"
  >
    <div class="max-w-6xl mx-auto">
      <!-- Section Title -->
      <h2
        class="text-5xl md:text-6xl font-extrabold text-center mb-20 bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent tracking-tight"
      >
        Meet the Developer
      </h2>

      <!-- Developer Profile Card -->
      <div
        ref="developerCard"
        class="relative mx-auto max-w-5xl bg-gradient-to-br from-slate-900/90 to-black/80 backdrop-blur-2xl rounded-3xl border border-emerald-500/10 shadow-[0_0_40px_-10px_rgba(0,255,200,0.25)] overflow-hidden group transform rotate-3"
      >
        <div
          class="grid lg:grid-cols-2 gap-10 items-center px-10 py-12 lg:px-16 lg:py-16"
        >
          <!-- Left Side - Developer Image -->
          <div class="flex justify-center lg:justify-start relative">
            <div
              class="w-64 h-64 lg:w-80 lg:h-80 rounded-3xl overflow-hidden border-4 border-emerald-500/30 shadow-2xl grayscale hover:grayscale-0 transition-all duration-500"
            >
              <img
                :src="developerData.avatar"
                :alt="developerData.name"
                class="w-full h-full object-cover"
              />
            </div>

            <!-- Glow Accent -->
            <div
              class="absolute -z-10 w-96 h-96 bg-emerald-500/30 blur-[120px] rounded-full opacity-20 group-hover:opacity-40 transition-all duration-700"
            ></div>
          </div>

          <!-- Right Side - Developer Info -->
          <div class="space-y-6">
            <div>
              <h3
                class="text-4xl font-bold bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent"
              >
                {{ developerData.name }}
              </h3>
              <p class="text-xl text-emerald-400 font-semibold mt-2">
                {{ developerData.title }}
              </p>
              <p class="text-gray-400 text-base mt-3 leading-relaxed">
                {{ developerData.subtitle }}
              </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
              <div class="flex items-center space-x-3">
                <div class="w-2 h-2 bg-emerald-400 rounded-full"></div>
                <span class="text-gray-400">Location:</span>
                <span class="text-white">{{ developerData.location }}</span>
              </div>
              <div class="flex items-center space-x-3">
                <div class="w-2 h-2 bg-cyan-400 rounded-full"></div>
                <span class="text-gray-400">Experience:</span>
                <span class="text-white">{{ developerData.experience }}</span>
              </div>
            </div>

            <!-- Bio -->
            <p class="text-gray-300 text-sm md:text-base leading-relaxed">
              {{ developerData.bio }}
            </p>

            <!-- Skills -->
            <div class="pt-3">
              <h4 class="text-lg font-semibold text-white mb-3">Core Skills</h4>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="skill in developerData.skills"
                  :key="skill"
                  class="px-3 py-1 bg-emerald-400/10 border border-emerald-500/30 rounded-full text-sm text-emerald-300 hover:bg-emerald-500/20 transition"
                >
                  {{ skill }}
                </span>
              </div>
            </div>

            <!-- Contact -->
            <div class="pt-6 text-sm space-y-2">
              <div class="flex items-center space-x-3">
                <i class="text-emerald-400">📧</i>
                <span class="text-gray-300">{{ developerData.email }}</span>
              </div>
              <div class="flex items-center space-x-3">
                <i class="text-cyan-400">📱</i>
                <span class="text-gray-300">{{ developerData.phone }}</span>
              </div>
            </div>
          </div>
        </div>


      </div>
    </div>
  </section>
    <!-- Contact Section -->
    <section id="contact" ref="contact" class="relative z-10 py-20 px-4">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-5xl font-bold text-center mb-16 bg-gradient-to-r from-pink-400 to-blue-400 bg-clip-text text-transparent">
          Let's Build Something Amazing
        </h2>
        
        <form ref="contactForm" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="group">
              <input type="text" placeholder="Your Name" 
                     class="w-full px-6 py-4 bg-white/5 backdrop-blur-lg border border-white/10 rounded-lg text-white placeholder-gray-400 focus:border-pink-400 focus:outline-none focus:shadow-lg focus:shadow-pink-400/25 transition-all duration-300">
            </div>
            <div class="group">
              <input type="email" placeholder="Your Email" 
                     class="w-full px-6 py-4 bg-white/5 backdrop-blur-lg border border-white/10 rounded-lg text-white placeholder-gray-400 focus:border-blue-400 focus:outline-none focus:shadow-lg focus:shadow-blue-400/25 transition-all duration-300">
            </div>
          </div>
          <div class="group">
            <textarea rows="6" placeholder="Tell us about your project..." 
                      class="w-full px-6 py-4 bg-white/5 backdrop-blur-lg border border-white/10 rounded-lg text-white placeholder-gray-400 focus:border-orange-400 focus:outline-none focus:shadow-lg focus:shadow-orange-400/25 transition-all duration-300 resize-none"></textarea>
          </div>
          <div class="text-center">
            <button type="submit" 
                    class="group relative px-12 py-4 bg-gradient-to-r from-pink-500 via-orange-500 to-blue-500 rounded-full text-lg font-semibold hover:shadow-2xl hover:shadow-pink-500/25 transition-all duration-300 transform hover:scale-105">
              <span class="relative z-10">Send Message</span>
              <div class="absolute inset-0 rounded-full bg-gradient-to-r from-pink-400 to-blue-400 opacity-0 group-hover:opacity-75 blur transition-opacity duration-300"></div>
            </button>
          </div>
        </form>
      </div>
    </section>

    <!-- Footer -->
    <footer class="relative z-10 py-12 px-4 border-t border-white/10 bg-black/40">
      <div class="max-w-7xl mx-auto">
        <div class="text-center">
          <div class="mb-6">
            <h3 class="text-2xl font-bold bg-gradient-to-r from-pink-400 to-orange-400 bg-clip-text text-transparent mb-2">
              Baconologies.com
            </h3>
            <p class="text-gray-400">Blending warmth and creativity with innovation and precision</p>
          </div>
          
          <div class="flex justify-center space-x-6 mb-6">
            <a href="#" class="text-gray-400 hover:text-pink-400 transition-colors text-2xl">📘</a>
            <a href="#" class="text-gray-400 hover:text-blue-400 transition-colors text-2xl">💼</a>
            <a href="#" class="text-gray-400 hover:text-orange-400 transition-colors text-2xl">🐙</a>
          </div>
          
          <p class="text-gray-500">
            © 2025 Baconologies.com - All rights reserved
          </p>
        </div>
      </div>
    </footer>

    <!-- Scroll to Top Button -->
    <a href="#hero" ref="scrollToTop" 
            class="fixed bottom-8 right-8 z-50 w-12 h-12 bg-gradient-to-r from-pink-500 to-orange-500 rounded-full flex items-center justify-center text-white shadow-lg hover:shadow-2xl hover:shadow-pink-500/25 transition-all duration-300 transform hover:scale-110 opacity-0 pointer-events-none">
      ↑
    </a>

    <!-- Dark Mode Toggle -->
    <button @click="toggleDarkMode" 
            class="fixed top-8 right-8 z-50 w-12 h-12 bg-white/10 backdrop-blur-lg border border-white/20 rounded-full flex items-center justify-center text-white hover:bg-white/20 transition-all duration-300">
      🌙
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, onBeforeUnmount } from 'vue'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

// Register GSAP plugins
gsap.registerPlugin(ScrollTrigger)

// Template refs
const hero = ref<HTMLElement>()
const heroContent = ref<HTMLElement>()
const btnGetStarted = ref<HTMLElement>()
const btnServices = ref<HTMLElement>()
const scrollIndicator = ref<HTMLElement>()
const services = ref<HTMLElement>()
const serviceCards = ref<HTMLElement[]>([])
const techStack = ref<HTMLElement>()
const techLogos = ref<HTMLElement[]>([])
const pricing = ref<HTMLElement>()
const pricingCards = ref<HTMLElement[]>([])
const team = ref<HTMLElement>()
const teamCards = ref<HTMLElement>()
const contact = ref<HTMLElement>()
const contactForm = ref<HTMLElement>()
const scrollToTop = ref<HTMLElement>()
const particles = ref<HTMLElement>()
const developerCard = ref(null);

// Removed smooth scrolling functionality

// Data
const servicesData = [
  {
    svg: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 14l9-5-9-5-9 5 9 5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`,
    title: 'Learning Management',
    description: 'Custom eLearning and LMS Platforms tailored for modern educational needs with interactive features.'
  },
  {
    svg: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`,
    title: 'Utility Systems',
    description: 'Enterprise Utility Management Platforms based on Oracle OUAF features for optimal resource management.'
  },
  {
    svg: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <rect x="1" y="3" width="15" height="13" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
      <path d="M16 8l4-4m0 0l4 4m-4-4v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M1 9h15" stroke="currentColor" stroke-width="2"/>
    </svg>`,
    title: 'Billing Systems',
    description: 'Smart Billing and Customer Care Systems inspired by Oracle CCB concepts for seamless operations.'
  },
  {
    svg: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
      <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M16 8l-3-3 3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>`,
    title: 'Geomapping Services',
    description: 'Geospatial Hazard & Infrastructure Mapping Tools inspired by Project NOAH and Hazard.ph initiatives.'
  }
]

const technologiesData = [
  { 
    name: 'C#', 
    logo: 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/csharp/csharp-original.svg',
    category: 'Language'
  },
  { 
    name: 'Laravel', 
    logo: 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/laravel/laravel-original.svg',
    category: 'Framework'
  },
  { 
    name: 'Vue.js', 
    logo: 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/vuejs/vuejs-original.svg',
    category: 'Frontend'
  },
  { 
    name: 'React', 
    logo: 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/react/react-original.svg',
    category: 'Frontend'
  },
  { 
    name: 'Node.js', 
    logo: 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/nodejs/nodejs-original.svg',
    category: 'Runtime'
  },
  { 
    name: 'DigitalOcean', 
    logo: 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/digitalocean/digitalocean-original.svg',
    category: 'Cloud'
  },
  { 
    name: 'PHP', 
    logo: 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg',
    category: 'Backend'
  },
  { 
    name: 'MySQL', 
    logo: 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg',
    category: 'Database'
  },
  { 
    name: 'Oracle SQL', 
    logo: 'https://cdn.jsdelivr.net/gh/devicons/devicon/icons/oracle/oracle-original.svg',
    category: 'Database'
  },
  { 
    name: 'Oracle CCS', 
    logo: 'https://cdn.worldvectorlogo.com/logos/oracle-6.svg',
    category: 'Cloud Service'
  },
  { 
    name: 'Oracle Integration Cloud', 
    logo: 'https://cdn.worldvectorlogo.com/logos/oracle-6.svg',
    category: 'Integration'
  },
  { 
    name: 'Oracle OCI', 
    logo: 'https://cdn.worldvectorlogo.com/logos/oracle-6.svg',
    category: 'Cloud Infrastructure'
  },
  { 
    name: 'Oracle Field Service', 
    logo: 'https://cdn.worldvectorlogo.com/logos/oracle-6.svg',
    category: 'Field Service'
  },
  { 
    name: 'Oracle CC&B', 
    logo: 'https://cdn.worldvectorlogo.com/logos/oracle-6.svg',
    category: 'Utilities'
  }
]

const pricingPlansData = [
  {
    name: 'Starter',
    price: 'Custom',
    description: 'Perfect for small LMS projects and basic utility management',
    features: [
      'Basic LMS Setup',
      'Up to 100 Users',
      'Standard Support',
      'Cloud Hosting',
      'Basic Analytics'
    ],
    featured: false
  },
  {
    name: 'Professional',
    price: 'Enterprise',
    description: 'Ideal for utility and billing platforms with advanced features',
    features: [
      'Full Platform Access',
      'Unlimited Users',
      'Priority Support',
      'Custom Integrations',
      'Advanced Analytics',
      'API Access'
    ],
    featured: true
  },
  {
    name: 'Enterprise',
    price: 'Contact Us',
    description: 'Complete IaaS integration with full customization',
    features: [
      'Full IaaS Solution',
      'Dedicated Infrastructure',
      '24/7 Support',
      'Custom Development',
      'Advanced Security',
      'SLA Guarantee'
    ],
    featured: false
  }
]

const developerData = {
  name: "John Bacon",
  title: "Full Stack Engineer",
  subtitle: "Building enterprise systems and cloud-integrated solutions.",
  location: "Manila, Philippines",
  experience: "8+ Years",
  bio: "Bacon is a passionate full-stack developer specializing in enterprise-grade development of usable systems. He blends creativity and precision to build digital solutions for complex business ecosystems.",
  avatar: "/images/developer-avatar.jpg",
  email: "john@baconologies.com",
  phone: "+63 *** *** ****",
  skills: [
    "Laravel",
    "Vue.js",
    "TailwindCSS",
    "Node.js",
    "Oracle SQL",
    "DigitalOcean",
    "React",
    "GSAP",
  ],
};
// Methods
const toggleDarkMode = () => {
  // Dark mode is already default, but this could toggle light mode
  console.log('Dark mode toggled')
}

// Removed all smooth scrolling functions

// Animation functions
const createParticles = () => {
  if (!particles.value) return
  
  const particleCount = 50
  const particlesHTML = Array.from({ length: particleCount }, (_, i) => {
    return `<div class="particle absolute w-1 h-1 bg-gradient-to-r from-pink-400 to-blue-400 rounded-full opacity-20" style="left: ${Math.random() * 100}%; top: ${Math.random() * 100}%;"></div>`
  }).join('')
  
  particles.value.innerHTML = particlesHTML
  
  // Animate particles
  gsap.to('.particle', {
    y: 'random(-100, 100)',
    x: 'random(-100, 100)',
    duration: 'random(3, 6)',
    ease: 'none',
    repeat: -1,
    yoyo: true,
    stagger: {
      amount: 2,
      from: 'random'
    }
  })
}

const initAnimations = () => {
  // Clear any existing ScrollTriggers
  ScrollTrigger.getAll().forEach(trigger => trigger.kill())
  
  // Hero animations
  const tl = gsap.timeline()
  
  if (heroContent.value) {
    gsap.set(heroContent.value.children, { opacity: 0, y: 50 })
    tl.to(heroContent.value.children, {
      opacity: 1,
      y: 0,
      duration: 1.5,
      stagger: 0.2,
      ease: "power3.out"
    })
  }
  
  if (btnGetStarted.value && btnServices.value) {
    gsap.set([btnGetStarted.value, btnServices.value], { opacity: 0, y: 30 })
    tl.to([btnGetStarted.value, btnServices.value], {
      opacity: 1,
      y: 0,
      duration: 1,
      stagger: 0.2,
      ease: "power2.out"
    }, "-=0.8")
  }
  
  if (scrollIndicator.value) {
    gsap.set(scrollIndicator.value, { opacity: 0, y: 20 })
    tl.to(scrollIndicator.value, {
      opacity: 1,
      y: 0,
      duration: 0.8,
      ease: "power2.out"
    }, "-=0.5")
  }

  // Service items animation
  if (serviceCards.value.length > 0 && services.value) {
    gsap.set(serviceCards.value, { 
      opacity: 0, 
      y: 80, 
      scale: 0.8,
      filter: "blur(10px)" 
    })
    
    ScrollTrigger.create({
      trigger: services.value,
      start: "top 70%",
      onEnter: () => {
        gsap.to(serviceCards.value, {
          opacity: 1,
          y: 0,
          scale: 1,
          filter: "blur(0px)",
          duration: 1.2,
          stagger: 0.15,
          ease: "power3.out"
        })
        
        // Animate SVG icons with initial visibility
        const svgIcons = serviceCards.value.map(card => card.querySelector('svg'))
        gsap.set(svgIcons, { opacity: 1, scale: 1 })
        gsap.from(svgIcons, {
          scale: 0.3,
          rotation: -90,
          opacity: 0,
          duration: 0.8,
          stagger: 0.1,
          ease: "back.out(1.7)",
          delay: 0.4
        })
      }
    })
  }

  // Tech logos animation
  if (techLogos.value.length > 0 && techStack.value) {
    gsap.set(techLogos.value, { opacity: 0, scale: 0, rotation: 180 })
    ScrollTrigger.create({
      trigger: techStack.value,
      start: "top 80%",
      onEnter: () => {
        gsap.to(techLogos.value, {
          opacity: 1,
          scale: 1,
          rotation: 0,
          duration: 0.8,
          stagger: 0.1,
          ease: "back.out(1.7)"
        })
      }
    })
  }

  // Pricing cards animation
  if (pricingCards.value.length > 0 && pricing.value) {
    gsap.set(pricingCards.value, { opacity: 0, y: 100, scale: 0.8 })
    ScrollTrigger.create({
      trigger: pricing.value,
      start: "top 80%",
      onEnter: () => {
        gsap.to(pricingCards.value, {
          opacity: 1,
          y: 0,
          scale: 1,
          duration: 1,
          stagger: 0.3,
          ease: "power3.out"
        })
      }
    })
  }

  // Team card animation
  if (teamCards.value && team.value) {
    gsap.set(teamCards.value, { opacity: 0, y: 80, scale: 0.8 })
    ScrollTrigger.create({
      trigger: team.value,
      start: "top 80%",
      onEnter: () => {
        if (teamCards.value) {
          gsap.to(teamCards.value, {
            opacity: 1,
            y: 0,
            scale: 1,
            duration: 1.2,
            ease: "back.out(1.7)"
          })
        }
      }
    })
  }

  // Contact form animation
  if (contactForm.value && contact.value) {
    gsap.set(contactForm.value, { opacity: 0, y: 60 })
    ScrollTrigger.create({
      trigger: contact.value,
      start: "top 80%",
      onEnter: () => {
        if (contactForm.value) {
          gsap.to(contactForm.value, {
            opacity: 1,
            y: 0,
            duration: 1.2,
            ease: "power2.out"
          })
        }
      }
    })
  }

  // Scroll to top button
  if (scrollToTop.value) {
    ScrollTrigger.create({
      start: "top -100",
      end: "max",
      onUpdate: self => {
        if (scrollToTop.value) {
          if (self.progress > 0.1) {
            gsap.to(scrollToTop.value, { 
              opacity: 1, 
              pointerEvents: 'auto', 
              duration: 0.3,
              scale: 1
            })
          } else {
            gsap.to(scrollToTop.value, { 
              opacity: 0, 
              pointerEvents: 'none', 
              duration: 0.3,
              scale: 0.8
            })
          }
        }
      }
    })
  }
}

onMounted(() => {
  console.log('Landing page mounted, initializing...')
  nextTick(() => {
    setTimeout(() => {
      createParticles()
      initAnimations()
      console.log('Animations initialized!')
    }, 200)
  });
  
  // Smooth scrolling removed - using native browser scrolling
  
  gsap.from(developerCard.value, {
    duration: 1.8,
    x: 300,
    y: -300,
    opacity: 0,
    rotate: 8,
    ease: "power3.out",
  });
  gsap.to(developerCard.value, {
    duration: 1,
    rotate: 0,
    delay: 1.8,
    ease: "back.out(1.7)",
  });
})

onBeforeUnmount(() => {
  // Clean up GSAP animations and ScrollTriggers
  ScrollTrigger.getAll().forEach(trigger => trigger.kill())
  gsap.killTweensOf("*")
})
</script>

<style scoped>
.particle {
  animation: float 6s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) translateX(0px); }
  25% { transform: translateY(-20px) translateX(10px); }
  50% { transform: translateY(-10px) translateX(-10px); }
  75% { transform: translateY(-30px) translateX(5px); }
}
</style>