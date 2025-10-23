document.addEventListener('DOMContentLoaded', function () {
  const yearEl = document.getElementById('year');
  if (yearEl) {
    yearEl.textContent = new Date().getFullYear();
  }

  if (typeof Chart === 'undefined') {
    return;
  }

  const ctxTickets = document.getElementById('ticketsChart');
  const ctxRanking = document.getElementById('rankingChart');
  const ctxCategory = document.getElementById('categoryChart');
  if (!ctxTickets || !ctxRanking || !ctxCategory) {
    return;
  }

  const chartsStatus = document.getElementById('chartsStatus');
  const state = { failures: 0, maxFailures: 3 };

  const palette = {
    active: '#8b5cf6',        // violeta principal
    finalized: '#22d3ee',     // cyan brillante
    canceled: '#f97316',      // naranja acento
    techBar: 'rgba(139,92,246,0.7)',
    techBorder: '#c4b5fd',
    lineStroke: '#22d3ee',
    lineFill: 'rgba(34,211,238,0.18)',
    grid: 'rgba(148,163,184,0.18)'
  };

  const charts = {
    tickets: new Chart(ctxTickets, {
      type: 'doughnut',
      data: {
        labels: ['Activos', 'Finalizados', 'Cancelados'],
        datasets: [{
          label: 'Tickets',
          data: [0, 0, 0],
          backgroundColor: [palette.active, palette.finalized, palette.canceled],
          borderColor: '#111827',
          borderWidth: 2
        }]
      },
      options: {
        plugins: {
          legend: { labels: { color: '#c9d1d9' } },
          title: {
            display: true,
            text: 'Tickets activos vs finalizados',
            color: '#c9d1d9'
          }
        }
      }
    }),
    ranking: new Chart(ctxRanking, {
      type: 'bar',
      data: {
        labels: ['1', '2', '3', '4', '5'],
        datasets: [{
          label: 'Cantidad de técnicos',
          data: [0, 0, 0, 0, 0],
          backgroundColor: palette.techBar,
          borderColor: palette.techBorder,
          borderWidth: 1
        }]
      },
      options: {
        plugins: {
          legend: { labels: { color: '#c9d1d9' } },
          title: {
            display: true,
            text: 'Promedio de técnicos (estrellas)',
            color: '#c9d1d9'
          }
        },
        scales: {
          x: {
            ticks: { color: '#c9d1d9' },
            grid: { color: palette.grid }
          },
          y: {
            beginAtZero: true,
            ticks: { color: '#c9d1d9', precision: 0 },
            grid: { color: palette.grid }
          }
        }
      }
    }),
    categories: new Chart(ctxCategory, {
      type: 'line',
      data: {
        labels: [],
        datasets: [{
          label: 'Reparaciones',
          data: [],
          borderColor: palette.lineStroke,
          backgroundColor: palette.lineFill,
          tension: 0.3,
          fill: true
        }]
      },
      options: {
        plugins: {
          legend: { labels: { color: '#c9d1d9' } },
          title: {
            display: true,
            text: 'Reparaciones por categoría',
            color: '#c9d1d9'
          }
        },
        scales: {
          x: {
            ticks: { color: '#c9d1d9' },
            grid: { color: palette.grid }
          },
          y: {
            beginAtZero: true,
            ticks: { color: '#c9d1d9' },
            grid: { color: palette.grid }
          }
        }
      }
    })
  };

  const updateStats = (stats) => {
    if (!stats) return;
    const activeEl = document.getElementById('activeTickets');
    if (activeEl) {
      activeEl.textContent = String(stats.activeTickets ?? 0);
    }
    const ratingEl = document.getElementById('avgRating');
    if (ratingEl) {
      if (stats.avgRating !== null && stats.avgRating !== undefined) {
        const avg = Number(stats.avgRating);
        ratingEl.textContent = Number.isFinite(avg) ? avg.toFixed(1) : String(stats.avgRating);
      } else {
        ratingEl.textContent = '—';
      }
    }
    const lastEl = document.getElementById('lastUpdate');
    if (lastEl) {
      if (stats.lastUpdateIso) {
        let timeEl = lastEl.querySelector('time');
        if (!timeEl) {
          lastEl.textContent = '';
          timeEl = document.createElement('time');
          lastEl.appendChild(timeEl);
        }
        timeEl.dateTime = stats.lastUpdateIso;
        timeEl.title = stats.lastUpdateIso;
        timeEl.textContent = stats.lastUpdateHuman || stats.lastUpdateIso;
      } else {
        lastEl.textContent = '—';
      }
    }
  };

  const updateCharts = (payload) => {
    if (!payload) return;

    const t = payload.tickets;
    if (t) {
      charts.tickets.data.labels = t.labels || charts.tickets.data.labels;
      charts.tickets.data.datasets[0].data = Array.isArray(t.data) ? t.data : [0, 0, 0];
      charts.tickets.update('none');
    }

    const r = payload.ranking;
    if (r) {
      const labels = Array.isArray(r.labels) ? r.labels.map((v) => String(v)) : ['1','2','3','4','5'];
      charts.ranking.data.labels = labels;
      charts.ranking.data.datasets[0].data = Array.isArray(r.data) ? r.data : [0,0,0,0,0];
      charts.ranking.update('none');
    }

    const c = payload.categories;
    if (c) {
      charts.categories.data.labels = Array.isArray(c.labels) ? c.labels : [];
      charts.categories.data.datasets[0].data = Array.isArray(c.data) ? c.data : [];
      charts.categories.update('none');
    }
  };

  const handleError = () => {
    state.failures += 1;
    if (!chartsStatus) return;
    chartsStatus.style.display = 'block';
    chartsStatus.textContent = state.failures >= state.maxFailures
      ? 'No se pudo actualizar las estadísticas. Verificá tu conexión.'
      : 'Reintentando actualizar estadísticas...';
  };

  const resetStatus = () => {
    state.failures = 0;
    if (chartsStatus) {
      chartsStatus.style.display = 'none';
      chartsStatus.textContent = '';
    }
  };

  const loadMetrics = () => {
    fetch('/ProyectoPandora/Public/index.php?route=Default/HomeMetrics', {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      cache: 'no-store'
    })
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
      })
      .then((data) => {
        resetStatus();
        updateStats(data.stats || null);
        updateCharts(data.charts || null);
      })
      .catch(() => {
        handleError();
      });
  };

  loadMetrics();
  setInterval(loadMetrics, 60000);
});
