// Theme toggle functionality
document.addEventListener("DOMContentLoaded", () => {
    // Only use dark mode if explicitly set in localStorage
    // Default to light mode
    if (localStorage.theme === "dark") {
      document.documentElement.classList.add("dark")
    } else {
      document.documentElement.classList.remove("dark")
      // Set light mode as default
      localStorage.theme = "light"
    }

    // Initialize AOS animation library
    AOS.init({
      duration: 800,
      easing: "ease-in-out",
      once: true,
      mirror: false,
    })

    // Add smooth scrolling to all links
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
      anchor.addEventListener("click", function (e) {
        e.preventDefault()

        const targetId = this.getAttribute("href")
        if (targetId === "#") return

        const targetElement = document.querySelector(targetId)
        if (targetElement) {
          targetElement.scrollIntoView({
            behavior: "smooth",
          })
        }
      })
    })

    // Add parallax effect to hero section
    const heroSection = document.querySelector(".hero-pattern")
    if (heroSection) {
      window.addEventListener("scroll", () => {
        const scrollPosition = window.scrollY
        if (scrollPosition < 600) {
          heroSection.style.backgroundPositionY = scrollPosition * 0.5 + "px"
        }
      })
    }

    // Add counter animation to numbers
    const counters = document.querySelectorAll(".counter")
    if (counters.length > 0) {
      const animateCounter = (counter, start = 0, end, duration = 2000) => {
        let startTimestamp = null
        const step = (timestamp) => {
          if (!startTimestamp) startTimestamp = timestamp
          const progress = Math.min((timestamp - startTimestamp) / duration, 1)
          const value = Math.floor(progress * (end - start) + start)
          counter.textContent = value.toLocaleString()
          if (progress < 1) {
            window.requestAnimationFrame(step)
          }
        }
        window.requestAnimationFrame(step)
      }

      const observerOptions = {
        root: null,
        rootMargin: "0px",
        threshold: 0.1,
      }

      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const counter = entry.target
            const end = Number.parseInt(counter.getAttribute("data-count"), 10)
            animateCounter(counter, 0, end)
            observer.unobserve(counter)
          }
        })
      }, observerOptions)

      counters.forEach((counter) => {
        observer.observe(counter)
      })
    }

    // Add hover effects to cards
    const cards = document.querySelectorAll(".card-hover-effect")
    cards.forEach((card) => {
      card.addEventListener("mouseenter", function () {
        this.classList.add("transform", "scale-105", "shadow-lg")
      })

      card.addEventListener("mouseleave", function () {
        this.classList.remove("transform", "scale-105", "shadow-lg")
      })
    })

    // Add typing effect to hero title
    const heroTitle = document.querySelector(".hero-title")
    if (heroTitle) {
      const text = heroTitle.textContent
      heroTitle.textContent = ""

      let i = 0
      const typeWriter = () => {
        if (i < text.length) {
          heroTitle.textContent += text.charAt(i)
          i++
          setTimeout(typeWriter, 100)
        }
      }

      typeWriter()
    }
  })
