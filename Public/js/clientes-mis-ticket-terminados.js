(function(){
  const ticketTrack = document.getElementById('carouselTicketTrackFinished');
  if (!ticketTrack) return;
  const prevBtn = document.getElementById('prevTicketBtnFinished');
  const nextBtn = document.getElementById('nextTicketBtnFinished');

  const cards = ticketTrack.querySelectorAll('.ticket-card');
  if (cards.length < 5) {
    if (prevBtn) prevBtn.style.display = 'none';
    if (nextBtn) nextBtn.style.display = 'none';
  }

  const width = 300;
  nextBtn && nextBtn.addEventListener('click', () => ticketTrack.scrollBy({ left: width, behavior: 'smooth' }));
  prevBtn && prevBtn.addEventListener('click', () => ticketTrack.scrollBy({ left: -width, behavior: 'smooth' }));
})();