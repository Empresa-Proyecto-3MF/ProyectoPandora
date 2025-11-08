(function(){
  const track = document.getElementById('carouselTicketTrackTech');
  if (!track) return;
  const prev = document.getElementById('prevTicketBtnTech');
  const next = document.getElementById('nextTicketBtnTech');
  const cards = track.querySelectorAll('.ticket-card');

  if (cards.length < 5) {
    if (prev) prev.style.display = 'none';
    if (next) next.style.display = 'none';
  }

  const width = 300;
  next && next.addEventListener('click', () => track.scrollBy({ left: width, behavior: 'smooth' }));
  prev && prev.addEventListener('click', () => track.scrollBy({ left: -width, behavior: 'smooth' }));
})();