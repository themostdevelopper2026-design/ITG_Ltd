document.addEventListener('DOMContentLoaded', () => {
  // Mobile menu toggle
  const btn = document.getElementById('mobile-menu-btn');
  const menu = document.getElementById('mobile-menu');

  if (btn && menu) {
    btn.addEventListener('click', () => {
      menu.classList.toggle('hidden');
    });
  }

  // Scroll animations
  const observerOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
  };

  const observer = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('fade-in-up');
        observer.unobserve(entry.target);
      }
    });
  }, observerOptions);

  document.querySelectorAll('.animate-on-scroll').forEach((el) => {
    observer.observe(el);
  });

  // Services Carousel helpers
  window.svcCarouselGo = function(carouselId, index, totalSlides) {
    const container = document.getElementById(carouselId);
    if (!container) return;
    const track = container.querySelector('.svc-slide-track');
    if (!track) return;
    
    let newIndex = index;
    if (newIndex < 0) newIndex = totalSlides - 1;
    if (newIndex >= totalSlides) newIndex = 0;
    
    container.dataset.current = newIndex;
    track.style.transform = `translateX(-${newIndex * 100}%)`;
    
    const dotsContainer = container.querySelector('[id^="dots-"]');
    if (dotsContainer) {
      const dots = dotsContainer.querySelectorAll('.svc-dot');
      dots.forEach((dot, idx) => {
        if (idx === newIndex) {
          dot.classList.remove('bg-gray-200');
          if (carouselId.includes('dev')) dot.classList.add('bg-itg-blue');
          else if (carouselId.includes('ia')) dot.classList.add('bg-itg-orange');
          else if (carouselId.includes('equip')) dot.classList.add('bg-yellow-500');
          else dot.classList.add('bg-itg-blue');
          dot.style.width = '1.25rem';
        } else {
          dot.className = 'svc-dot w-2 h-2 rounded-full bg-gray-200 transition-all';
          dot.style.width = '0.5rem';
        }
      });
    }
  };

  window.svcCarouselPrev = function(carouselId, totalSlides) {
    const container = document.getElementById(carouselId);
    if (!container) return;
    const current = parseInt(container.dataset.current || '0', 10);
    window.svcCarouselGo(carouselId, current - 1, totalSlides);
  };

  window.svcCarouselNext = function(carouselId, totalSlides) {
    const container = document.getElementById(carouselId);
    if (!container) return;
    const current = parseInt(container.dataset.current || '0', 10);
    window.svcCarouselGo(carouselId, current + 1, totalSlides);
  };
});
