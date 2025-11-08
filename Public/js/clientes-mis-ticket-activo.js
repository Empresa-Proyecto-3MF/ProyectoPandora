(function(){
  const ticketTrack = document.getElementById('carouselTicketTrack');
  if (!ticketTrack) return;
  const prevTicketBtn = document.getElementById('prevTicketBtn');
  const nextTicketBtn = document.getElementById('nextTicketBtn');
  const btnAddTicket = document.getElementById('btnAddTicket');

  const ticketCards = ticketTrack.querySelectorAll('.ticket-card');

  if (ticketCards.length < 5) {
    if (prevTicketBtn) prevTicketBtn.style.display = 'none';
    if (nextTicketBtn) nextTicketBtn.style.display = 'none';
  }

  if (ticketCards.length === 0 && btnAddTicket) {
    btnAddTicket.style.display = 'none';
  }

  const ticketCardWidth = 300;
  nextTicketBtn && nextTicketBtn.addEventListener('click', () => {
    ticketTrack.scrollBy({ left: ticketCardWidth, behavior: 'smooth' });
  });
  prevTicketBtn && prevTicketBtn.addEventListener('click', () => {
    ticketTrack.scrollBy({ left: -ticketCardWidth, behavior: 'smooth' });
  });
})();