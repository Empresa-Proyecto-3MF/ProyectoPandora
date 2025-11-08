(function(){
  const track = document.getElementById('carouselTrack');
  if (!track) return;
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const btnAdd = document.getElementById('btnAdd');

  const cards = track.querySelectorAll('.device-card');
  if (cards.length < 5) {
    if (prevBtn) prevBtn.style.display = 'none';
    if (nextBtn) nextBtn.style.display = 'none';
  }

  if (cards.length === 0 && btnAdd) {
    btnAdd.style.display = 'none';
  }

  const cardWidth = 300;
  nextBtn && nextBtn.addEventListener('click', () => {
    track.scrollBy({ left: cardWidth, behavior: 'smooth' });
  });
  prevBtn && prevBtn.addEventListener('click', () => {
    track.scrollBy({ left: -cardWidth, behavior: 'smooth' });
  });
})();