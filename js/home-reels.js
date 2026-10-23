/* Home reels strip: autoplay muted clips only while visible, tap-to-unmute
   (one clip with sound at a time), arrow scrolling on desktop. Clips are
   optional — if videos/<slug>.mp4 is missing, the poster image just stays. */
(function () {
  var rail = document.querySelector(".hr-rail");
  if (!rail) return;

  var frames = Array.prototype.slice.call(rail.querySelectorAll(".hr-frame"));
  var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function load(video) {
    if (video.getAttribute("src") || !video.dataset.src) return;
    video.addEventListener("loadeddata", function () {
      video.parentNode.classList.add("has-video");
    }, { once: true });
    video.addEventListener("error", function () {
      video.parentNode.classList.remove("has-video");
      video.removeAttribute("src");
    }, { once: true });
    video.src = video.dataset.src;
  }

  function mute(frame) {
    var v = frame.querySelector("video");
    var b = frame.querySelector(".hr-sound");
    v.muted = true;
    b.setAttribute("aria-pressed", "false");
    b.setAttribute("aria-label", b.getAttribute("aria-label").replace(/^Mute/, "Unmute"));
  }

  frames.forEach(function (frame) {
    var video = frame.querySelector("video");
    var btn = frame.querySelector(".hr-sound");
    btn.addEventListener("click", function () {
      var turnOn = video.muted;
      frames.forEach(mute);
      if (turnOn) {
        video.muted = false;
        btn.setAttribute("aria-pressed", "true");
        btn.setAttribute("aria-label", btn.getAttribute("aria-label").replace(/^Unmute/, "Mute"));
        if (video.paused) video.play().catch(function () {});
      }
    });
  });

  if ("IntersectionObserver" in window && !reduceMotion) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        var video = e.target.querySelector("video");
        if (e.isIntersecting) {
          load(video);
          var p = video.play();
          if (p && p.catch) p.catch(function () {});
        } else {
          video.pause();
          if (!video.muted) mute(e.target);
        }
      });
    }, { root: null, threshold: 0.6 });
    frames.forEach(function (f) { io.observe(f); });
  }

  var prev = document.querySelector(".hr-nav.prev");
  var next = document.querySelector(".hr-nav.next");
  function step() { return Math.max(240, rail.clientWidth * 0.8); }
  function sync() {
    if (!prev || !next) return;
    prev.disabled = rail.scrollLeft < 8;
    next.disabled = rail.scrollLeft + rail.clientWidth >= rail.scrollWidth - 8;
  }
  if (prev && next) {
    prev.addEventListener("click", function () { rail.scrollBy({ left: -step(), behavior: "smooth" }); });
    next.addEventListener("click", function () { rail.scrollBy({ left: step(), behavior: "smooth" }); });
    rail.addEventListener("scroll", sync, { passive: true });
    window.addEventListener("resize", sync);
    sync();
  }
})();
