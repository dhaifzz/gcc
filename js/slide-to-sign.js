document.querySelectorAll('a.h').forEach(link => {
  link.addEventListener('click', function(e) {
    e.preventDefault();

    const container = document.querySelector('.container-sign');

    if (container) {
      container.scrollIntoView({ behavior: 'smooth' });
    }
  });
});
