// Timer logic for Tecnicos/MisStats
(function(){
  const el = document.getElementById('timer');
  const startBtn = document.getElementById('startBtn');
  const pauseBtn = document.getElementById('pauseBtn');
  const resetBtn = document.getElementById('resetBtn');
  if (!el || !startBtn || !pauseBtn || !resetBtn) return;

  let timerInterval = null;
  let elapsed = 0;
  let running = false;

  function fmt(s){
    const h = Math.floor(s/3600);
    const m = Math.floor((s%3600)/60);
    const sec = s%60;
    return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(sec).padStart(2,'0')}`;
  }
  function tick(){ elapsed++; el.textContent = fmt(elapsed); }

  startBtn.addEventListener('click', () => {
    if (!running) { timerInterval = setInterval(tick, 1000); running = true; }
  });
  pauseBtn.addEventListener('click', () => {
    if (running) { clearInterval(timerInterval); running = false; }
  });
  resetBtn.addEventListener('click', () => {
    clearInterval(timerInterval); running = false; elapsed = 0; el.textContent = fmt(0);
  });
})();
