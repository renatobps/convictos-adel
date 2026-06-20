document.addEventListener('DOMContentLoaded', function(){
  const nav = document.querySelector('nav');
  if(nav){
    window.addEventListener('scroll', function(){
      nav.style.background = window.scrollY > 60 ? 'rgba(10,18,40,0.97)' : 'rgba(10,18,40,0.92)';
    });
  }

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

  // Mascara de telefone: (99) 99999-9999
  function maskPhone(value){
    const d = value.replace(/\D/g, '').slice(0, 11);
    if(d.length === 0) return '';
    if(d.length <= 2) return '(' + d;
    if(d.length <= 7) return '(' + d.slice(0, 2) + ') ' + d.slice(2);
    return '(' + d.slice(0, 2) + ') ' + d.slice(2, 7) + '-' + d.slice(7);
  }

  document.querySelectorAll('[data-phone]').forEach(function(input){
    input.addEventListener('input', function(){
      input.value = maskPhone(input.value);
    });
    // Formata valor pre-existente (ex.: old() apos erro de validacao)
    if(input.value){
      input.value = maskPhone(input.value);
    }
  });
});
