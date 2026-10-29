(function () {
  var root = document.querySelector('[data-weather-live]');
  if (!root) return;
  var url = 'https://api.open-meteo.com/v1/forecast?latitude=10.2381&longitude=77.4892' +
    '&current=temperature_2m,relative_humidity_2m,weather_code' +
    '&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max' +
    '&timezone=Asia%2FKolkata&forecast_days=15';
  var codes = { 0: 'Clear', 1: 'Mostly clear', 2: 'Partly cloudy', 3: 'Overcast', 45: 'Fog', 48: 'Fog',
    51: 'Light drizzle', 53: 'Drizzle', 55: 'Heavy drizzle', 61: 'Light rain', 63: 'Rain', 65: 'Heavy rain',
    80: 'Showers', 81: 'Showers', 82: 'Heavy showers', 95: 'Thunderstorm', 96: 'Thunderstorm', 99: 'Thunderstorm' };

  function set(sel, text) {
    var el = root.querySelector(sel);
    if (el) el.textContent = text;
  }

  fetch(url).then(function (r) { return r.json(); }).then(function (d) {
    set('[data-w-temp]', Math.round(d.current.temperature_2m) + '°C');
    set('[data-w-desc]', (codes[d.current.weather_code] || 'Mixed conditions') + ', humidity ' + d.current.relative_humidity_2m + '%');
    set('[data-w-hi]', Math.round(d.daily.temperature_2m_max[0]) + '°C');
    set('[data-w-lo]', Math.round(d.daily.temperature_2m_min[0]) + '°C');
    var strip = root.querySelector('[data-w-strip]');
    if (!strip) return;
    strip.innerHTML = '';
    d.daily.time.forEach(function (t, i) {
      var day = new Date(t + 'T00:00:00');
      var el = document.createElement('div');
      el.className = 'tg-w-day';
      el.innerHTML = '<small>' + (i === 0 ? 'Today' : day.toLocaleDateString('en-IN', { weekday: 'short', day: 'numeric' })) +
        '</small><strong>' + Math.round(d.daily.temperature_2m_max[i]) + '° / ' + Math.round(d.daily.temperature_2m_min[i]) +
        '°</strong><span>Rain ' + (d.daily.precipitation_probability_max[i] || 0) + '%</span>';
      strip.appendChild(el);
    });
  }).catch(function () {
    set('[data-w-desc]', 'Live forecast unavailable. Call us for current conditions.');
  });
})();
