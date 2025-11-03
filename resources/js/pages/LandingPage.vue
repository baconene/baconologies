<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-gray-900 to-black text-white">
    <!-- Simple Navigation for testing -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-black/20 backdrop-blur-lg border-b border-white/10">
      <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold bg-gradient-to-r from-pink-400 to-orange-400 bg-clip-text text-transparent">
          Baconologies.com
        </h1>
        <div class="space-x-4">
          <button @click="scrollToSection('hero')" class="text-gray-400 hover:text-pink-400 transition-colors">Home</button>
          <button @click="scrollToSection('services')" class="text-gray-400 hover:text-blue-400 transition-colors">Services</button>
          <button @click="scrollToSection('contact')" class="text-gray-400 hover:text-orange-400 transition-colors">Contact</button>
        </div>
      </div>
    </nav>
    <!-- Animated Background -->
    <div class="fixed inset-0 z-0">
      <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 via-pink-600/10 to-orange-600/10"></div>
      <div ref="particles" class="absolute inset-0"></div>
    </div>

    <!-- Hero Section -->
    <section id="hero" ref="hero" class="relative z-10 min-h-screen flex items-center justify-center px-4 pt-20">
      <div class="text-center max-w-6xl mx-auto">
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
            <button ref="btnServices" @click="scrollToServices" class="group relative px-8 py-4 border-2 border-blue-400 rounded-full text-lg font-semibold text-blue-400 hover:bg-blue-400 hover:text-black transition-all duration-300 transform hover:scale-105">
              Our Services
              <div class="absolute inset-0 rounded-full bg-blue-400/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </button>
          </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div ref="scrollIndicator" class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
          <div class="w-6 h-10 border-2 border-gray-400 rounded-full flex justify-center">
            <div class="w-1 h-3 bg-gradient-to-b from-pink-400 to-orange-400 rounded-full mt-2 animate-bounce"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- About/Services Section -->
    <section id="services" ref="services" class="relative z-10 py-20 px-4">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-5xl font-bold text-center mb-16 bg-gradient-to-r from-blue-400 to-pink-400 bg-clip-text text-transparent">
          What We Build
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="(service, index) in servicesData" :key="index" 
               ref="serviceCards"
               class="group relative p-8 rounded-2xl bg-white/5 backdrop-blur-lg border border-white/10 hover:border-pink-400/50 transition-all duration-500 transform hover:scale-105 hover:rotate-1">
            <div class="absolute inset-0 bg-gradient-to-br from-pink-500/10 to-blue-500/10 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <div class="relative z-10">
              <div class="w-16 h-16 mb-6 rounded-full bg-gradient-to-br from-pink-400 to-orange-400 flex items-center justify-center text-2xl">
                {{ service.icon }}
              </div>
              <h3 class="text-2xl font-bold mb-4 text-white group-hover:text-pink-400 transition-colors">
                {{ service.title }}
              </h3>
              <p class="text-gray-400 group-hover:text-gray-300 transition-colors leading-relaxed">
                {{ service.description }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Technology Stack Section -->
    <section ref="techStack" class="relative z-10 py-20 px-4 bg-black/20">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-5xl font-bold text-center mb-16 bg-gradient-to-r from-orange-400 to-blue-400 bg-clip-text text-transparent">
          Our Stack
        </h2>
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8">
          <div v-for="(tech, index) in technologiesData" :key="index"
               ref="techLogos"
               class="group flex flex-col items-center p-6 rounded-xl bg-white/5 backdrop-blur-sm border border-white/10 hover:border-orange-400/50 transition-all duration-300 transform hover:scale-110 hover:-translate-y-2">
            <div class="w-16 h-16 mb-4 rounded-lg bg-gradient-to-br from-gray-700 to-gray-800 flex items-center justify-center text-2xl group-hover:shadow-lg group-hover:shadow-orange-400/25 transition-all duration-300">
              {{ tech.icon }}
            </div>
            <span class="text-sm font-medium text-gray-300 group-hover:text-orange-400 transition-colors text-center">
              {{ tech.name }}
            </span>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section ref="pricing" class="relative z-10 py-20 px-4">
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
    <section ref="team" class="relative z-10 py-20 px-4 bg-black/20">
      <div class="max-w-7xl mx-auto">
        <h2 class="text-5xl font-bold text-center mb-16 bg-gradient-to-r from-blue-400 to-orange-400 bg-clip-text text-transparent">
          Meet the Baconologists
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-8">
          <div v-for="(member, index) in teamMembersData" :key="index"
               ref="teamCards"
               class="group relative">
            <div class="relative overflow-hidden rounded-2xl bg-white/5 backdrop-blur-lg border border-white/10 hover:border-pink-400/50 transition-all duration-500 transform hover:scale-105 hover:rotate-2">
              <div class="p-6 text-center">
                <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-gradient-to-br from-pink-400 to-orange-400 flex items-center justify-center text-3xl font-bold text-black">
                  {{ member.initials }}
                </div>
                <h3 class="text-xl font-bold mb-2 text-white group-hover:text-pink-400 transition-colors">
                  {{ member.name }}
                </h3>
                <p class="text-blue-400 mb-4 font-medium">{{ member.role }}</p>
                <p class="text-gray-400 text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                  {{ member.funFact }}
                </p>
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
    <button ref="scrollToTop" 
            @click="scrollToTopAction"
            class="fixed bottom-8 right-8 z-50 w-12 h-12 bg-gradient-to-r from-pink-500 to-orange-500 rounded-full flex items-center justify-center text-white shadow-lg hover:shadow-2xl hover:shadow-pink-500/25 transition-all duration-300 transform hover:scale-110 opacity-0 pointer-events-none">
      ↑
    </button>

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
import { ScrollToPlugin } from 'gsap/ScrollToPlugin'

// Register GSAP plugins
gsap.registerPlugin(ScrollTrigger, ScrollToPlugin)

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
const teamCards = ref<HTMLElement[]>([])
const contact = ref<HTMLElement>()
const contactForm = ref<HTMLElement>()
const scrollToTop = ref<HTMLElement>()
const particles = ref<HTMLElement>()

// Data
const servicesData = [
  {
    icon: '🎓',
    title: 'Learning Management',
    description: 'Custom eLearning and LMS Platforms tailored for modern educational needs with interactive features.'
  },
  {
    icon: '⚡',
    title: 'Utility Systems',
    description: 'Enterprise Utility Management Platforms based on Oracle OUAF features for optimal resource management.'
  },
  {
    icon: '💳',
    title: 'Billing Systems',
    description: 'Smart Billing and Customer Care Systems inspired by Oracle CCB concepts for seamless operations.'
  },
  {
    icon: '🗺️',
    title: 'Geomapping Services',
    description: 'Geospatial Hazard & Infrastructure Mapping Tools inspired by Project NOAH and Hazard.ph initiatives.'
  }
]

const technologiesData = [
  { name: 'C#', icon: '#' },
  { name: 'Laravel', icon: '🚀' },
  { name: 'Vue.js', icon: '💚' },
  { name: 'React', icon: '⚛️' },
  { name: 'Node.js', icon: '🟢' },
  { name: 'DigitalOcean', icon: '🌊' },
  { name: 'PHP', icon: '🐘' },
  { name: 'MySQL', icon: '🗄️' },
  { name: 'Oracle SQL', icon: '🔴' },
  { name: 'Oracle CCS', icon: '☁️' },
  { name: 'Oracle OIC', icon: '🔗' },
  { name: 'Oracle OFS', icon: '💼' }
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

const teamMembersData = [
  {
    name: 'Alex Chen',
    role: 'CEO & Founder',
    initials: 'AC',
    funFact: 'Codes with bacon-themed variable names'
  },
  {
    name: 'Sarah Kim',
    role: 'CTO',
    initials: 'SK',
    funFact: 'Can debug while eating actual bacon'
  },
  {
    name: 'Mike Rodriguez',
    role: 'Lead Engineer',
    initials: 'MR',
    funFact: 'Built our first system on a bacon budget'
  },
  {
    name: 'Emma Thompson',
    role: 'UX Designer',
    initials: 'ET',
    funFact: 'Designs interfaces as crispy as bacon'
  },
  {
    name: 'David Park',
    role: 'Cloud Architect',
    initials: 'DP',
    funFact: 'Scales systems faster than bacon cooks'
  }
]

// Methods
const toggleDarkMode = () => {
  // Dark mode is already default, but this could toggle light mode
  console.log('Dark mode toggled')
}

const scrollToTopAction = () => {
  gsap.to(window, { 
    duration: 1.2, 
    scrollTo: { y: 0, autoKill: false }, 
    ease: "power2.inOut" 
  })
}

const scrollToServices = () => {
  if (services.value) {
    gsap.to(window, {
      duration: 1,
      scrollTo: { y: services.value, offsetY: -100, autoKill: false },
      ease: "power2.inOut"
    })
  }
}

const scrollToSection = (sectionId: string) => {
  const element = document.getElementById(sectionId)
  if (element) {
    gsap.to(window, {
      duration: 1.2,
      scrollTo: { y: element, offsetY: -100, autoKill: false },
      ease: "power2.inOut"
    })
  }
}

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

  // Service cards animation
  if (serviceCards.value.length > 0 && services.value) {
    gsap.set(serviceCards.value, { opacity: 0, y: 100, rotation: 5 })
    ScrollTrigger.create({
      trigger: services.value,
      start: "top 80%",
      onEnter: () => {
        gsap.to(serviceCards.value, {
          opacity: 1,
          y: 0,
          rotation: 0,
          duration: 1,
          stagger: 0.2,
          ease: "power3.out"
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

  // Team cards animation
  if (teamCards.value.length > 0 && team.value) {
    gsap.set(teamCards.value, { opacity: 0, y: 80, rotation: -10 })
    ScrollTrigger.create({
      trigger: team.value,
      start: "top 80%",
      onEnter: () => {
        gsap.to(teamCards.value, {
          opacity: 1,
          y: 0,
          rotation: 0,
          duration: 1,
          stagger: 0.15,
          ease: "power2.out"
        })
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
  })
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