document.addEventListener('DOMContentLoaded', function(){
  const nav = document.querySelector('nav');
  window.addEventListener('scroll', function(){
    nav.style.background = window.scrollY > 60 ? 'rgba(10,18,40,0.97)' : 'rgba(10,18,40,0.92)';
  });

  const revealEls = document.querySelectorAll('.sobre-grid, .pilares-grid, .caminhos-grid, .valores-inner, .geracao-grid, .cta-inner, .id-grid, .id-intro, .threat-card, .pilar-card');
  const observer = new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
      if(entry.isIntersecting){
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, {threshold: 0.08});

  revealEls.forEach(function(el){
    el.style.opacity = '0';
    el.style.transform = 'translateY(28px)';
    el.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
    observer.observe(el);
  });

  const form = document.querySelector('.cta-section form');
  const btn = document.querySelector('.form-submit');
  if(btn){
    btn.addEventListener('click', function(e){
      e.preventDefault();
      btn.textContent = 'Enviado! Em breve você saberá ✓';
      btn.style.background = '#1C2D5C';
      btn.disabled = true;
    });
  }
});
