/* Styled dropdown for the hero "What kind of stay?" field.
   The native <select id="hero-staytype"> stays in the DOM (hidden) so main.js keeps working:
   picking an option sets select.value and fires its "change" event, and any script that sets
   select.value (e.g. the category tabs) refreshes the button label through the value hook below. */
(() => {
  const select = document.getElementById("hero-staytype");
  if (!select || select.dataset.sdReady) return;
  select.dataset.sdReady = "1";

  const host = select.parentElement;
  const label = host.querySelector("label");

  const wrap = document.createElement("div");
  wrap.className = "sd";

  const btn = document.createElement("button");
  btn.type = "button";
  btn.className = "sd-btn";
  btn.id = "hero-staytype-btn";
  btn.setAttribute("aria-haspopup", "listbox");
  btn.setAttribute("aria-expanded", "false");
  btn.setAttribute("aria-controls", "hero-staytype-list");
  if (label) label.htmlFor = btn.id;

  const list = document.createElement("ul");
  list.className = "sd-list";
  list.id = "hero-staytype-list";
  list.setAttribute("role", "listbox");
  list.setAttribute("aria-label", "What kind of stay?");
  list.hidden = true;

  const items = [...select.options].map((opt) => {
    const li = document.createElement("li");
    li.className = "sd-opt";
    li.setAttribute("role", "option");
    li.dataset.value = opt.value;
    li.textContent = opt.textContent;
    list.appendChild(li);
    return li;
  });

  select.classList.add("sd-native");
  select.tabIndex = -1;
  select.setAttribute("aria-hidden", "true");
  wrap.append(btn, list);
  host.appendChild(wrap);

  let active = -1;

  function refresh() {
    const current = select.value;
    const opt = select.options[select.selectedIndex];
    btn.textContent = opt ? opt.textContent : "";
    items.forEach((li) => li.setAttribute("aria-selected", String(li.dataset.value === current)));
  }

  function setActive(i) {
    active = Math.max(0, Math.min(items.length - 1, i));
    items.forEach((li, idx) => li.classList.toggle("is-active", idx === active));
    items[active]?.scrollIntoView({ block: "nearest" });
  }

  function open() {
    list.hidden = false;
    btn.setAttribute("aria-expanded", "true");
    setActive(select.selectedIndex);
  }

  function close(focusBtn) {
    list.hidden = true;
    btn.setAttribute("aria-expanded", "false");
    items.forEach((li) => li.classList.remove("is-active"));
    if (focusBtn) btn.focus();
  }

  function choose(i) {
    const value = items[i]?.dataset.value;
    if (value == null) return;
    select.value = value;
    select.dispatchEvent(new Event("change", { bubbles: true }));
    refresh();
    close(true);
  }

  btn.addEventListener("click", () => (list.hidden ? open() : close()));
  btn.addEventListener("keydown", (e) => {
    if (e.key === "ArrowDown" || e.key === "ArrowUp") {
      e.preventDefault();
      if (list.hidden) open();
      else setActive(active + (e.key === "ArrowDown" ? 1 : -1));
    } else if ((e.key === "Enter" || e.key === " ") && !list.hidden) {
      e.preventDefault();
      choose(active);
    } else if (e.key === "Escape" && !list.hidden) {
      e.preventDefault();
      close(true);
    } else if (e.key === "Tab") {
      close();
    }
  });
  list.addEventListener("click", (e) => {
    const li = e.target.closest(".sd-opt");
    if (li) choose(items.indexOf(li));
  });
  list.addEventListener("pointermove", (e) => {
    const li = e.target.closest(".sd-opt");
    if (li) setActive(items.indexOf(li));
  });
  document.addEventListener("pointerdown", (e) => {
    if (!list.hidden && !wrap.contains(e.target)) close();
  });

  /* keep the label in sync when other code assigns select.value */
  const proto = Object.getOwnPropertyDescriptor(HTMLSelectElement.prototype, "value");
  Object.defineProperty(select, "value", {
    configurable: true,
    get() { return proto.get.call(this); },
    set(v) { proto.set.call(this, v); refresh(); },
  });

  refresh();
})();
