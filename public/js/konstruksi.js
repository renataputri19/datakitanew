/*
 * Survei Konstruksi — shared form runtime.
 *
 * Eight of the ten blok pages are "a JSON object edited through a table", so
 * they all bind the same way: inputs declare data-k-path="a.b.c" and this
 * writes them into one model object, formats figures id-ID, recomputes the
 * JUMLAH rows and autosaves the whole field.
 *
 * Figures are kept AS TYPED ("12.312.312") and posted that way, because that
 * is what the server stores and what SibstrFormat::num() reads back. Parsing
 * to a JS number here would round long rupiah figures and lose the tail.
 *
 * No build step: served straight from public/, like the statistik pages.
 */
(function (global) {
  'use strict';

  var nf0 = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 });
  var nf2 = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });

  /** Parse an id-ID figure into a Number. Mirrors SibstrFormat::num(). */
  function num(v) {
    if (v === null || v === undefined) return 0;
    var s = String(v).trim();
    if (s === '') return 0;
    var neg = s.charAt(0) === '-';
    if (neg) s = s.slice(1);
    if (s.indexOf(',') !== -1) {
      // comma is the decimal separator; dots group thousands
      var cut = s.lastIndexOf(',');
      s = s.slice(0, cut).replace(/[.,]/g, '') + '.' + s.slice(cut + 1).replace(/[.,]/g, '');
    } else if (/^\d{1,3}(\.\d{3})+$/.test(s)) {
      s = s.replace(/\./g, '');
    }
    var n = parseFloat(s);
    if (!isFinite(n)) return 0;
    return neg ? -n : n;
  }

  /** Format a Number for display. */
  function fmt(n, decimals) {
    if (n === null || n === undefined || n === '') return '';
    var v = typeof n === 'number' ? n : num(n);
    if (!isFinite(v)) return '';
    return (decimals ? nf2 : nf0).format(v);
  }

  /**
   * Re-group the digits the user typed without changing them.
   * Keeps a trailing comma/decimals intact so typing "1.234," still works.
   */
  function regroup(raw, allowDecimal) {
    var s = String(raw).replace(/[^0-9,]/g, '');
    if (!allowDecimal) s = s.replace(/,/g, '');
    var parts = s.split(',');
    var whole = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    if (parts.length < 2) return whole;
    return whole + ',' + parts.slice(1).join('').slice(0, 2);
  }

  function getPath(obj, path) {
    var cur = obj;
    var keys = path.split('.');
    for (var i = 0; i < keys.length; i++) {
      if (cur === null || typeof cur !== 'object') return undefined;
      cur = cur[keys[i]];
    }
    return cur;
  }

  function setPath(obj, path, value) {
    var keys = path.split('.');
    var cur = obj;
    for (var i = 0; i < keys.length - 1; i++) {
      var k = keys[i];
      if (cur[k] === null || typeof cur[k] !== 'object') cur[k] = {};
      cur = cur[k];
    }
    cur[keys[keys.length - 1]] = value;
  }

  /* ── Autosave status strip ── */
  function Status(el) {
    this.el = el;
  }
  Status.prototype.show = function (msg, kind) {
    if (!this.el) return;
    this.el.hidden = false;
    this.el.className = 'k-status ' + (kind || '');
    this.el.textContent = msg;
  };

  /**
   * Bind one page.
   *
   * cfg = {
   *   form:        selector of the <form>
   *   autoSaveUrl: POST endpoint
   *   csrf:        token
   *   models:      { fieldName: initialObject }   -- JSON fields on this page
   *   recompute:   optional fn(models) run after every edit
   * }
   */
  function bind(cfg) {
    var form = document.querySelector(cfg.form || '#k-form');
    if (!form) return null;

    var status = new Status(document.getElementById('k-status'));
    var models = cfg.models || {};
    var timers = {};

    function save(field) {
      status.show('Menyimpan…', 'saving');
      fetch(cfg.autoSaveUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': cfg.csrf,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ field: field, value: models[field] })
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            status.show('Tersimpan otomatis · ' + (data.last_saved_at || ''), 'success');
            document.dispatchEvent(new CustomEvent('konstruksi:saved', {
              detail: { field: field, blok_completed: data.blok_completed }
            }));
          } else {
            status.show('Gagal menyimpan: ' + (data.message || 'tidak diketahui'), 'error');
          }
        })
        .catch(function (e) { status.show('Gagal menyimpan: ' + e.message, 'error'); });
    }

    function schedule(field) {
      clearTimeout(timers[field]);
      timers[field] = setTimeout(function () { save(field); }, 1200);
    }

    function recompute() {
      if (typeof cfg.recompute === 'function') cfg.recompute(models, { num: num, fmt: fmt });
    }

    // Every model-bound control declares which field and path it writes to.
    form.addEventListener('input', function (e) {
      var el = e.target;
      var path = el.getAttribute && el.getAttribute('data-k-path');
      if (!path) return;
      var field = el.getAttribute('data-k-field');
      if (!field || !(field in models)) return;

      var value = el.value;
      if (el.classList.contains('k-num')) {
        var allowDecimal = el.hasAttribute('data-k-decimal');
        var start = el.selectionStart;
        var before = el.value.length;
        value = regroup(value, allowDecimal);
        el.value = value;
        // keep the caret where the user left it after re-grouping
        if (typeof start === 'number') {
          var delta = el.value.length - before;
          try { el.setSelectionRange(start + delta, start + delta); } catch (_e) { /* not a text input */ }
        }
      }

      setPath(models[field], path, value);
      recompute();
      schedule(field);
    });

    form.addEventListener('change', function (e) {
      var el = e.target;
      var path = el.getAttribute && el.getAttribute('data-k-path');
      if (!path) return;
      var field = el.getAttribute('data-k-field');
      if (!field || !(field in models)) return;
      if (el.type === 'radio' && !el.checked) return;
      setPath(models[field], path, el.value);
      recompute();
      schedule(field);
    });

    // Serialise every model into its hidden input just before a normal POST.
    form.addEventListener('submit', function () {
      Object.keys(models).forEach(function (field) {
        var input = form.querySelector('input[type="hidden"][name="' + field + '"]');
        if (input) input.value = JSON.stringify(models[field]);
        clearTimeout(timers[field]);
      });
    });

    recompute();

    return {
      models: models,
      save: save,
      schedule: schedule,
      recompute: recompute,
      status: status,
      /** Re-render every model-bound input from the model (after adding a row). */
      sync: function () {
        form.querySelectorAll('[data-k-path][data-k-field]').forEach(function (el) {
          var v = getPath(models[el.getAttribute('data-k-field')], el.getAttribute('data-k-path'));
          if (v === undefined || v === null) v = '';
          if (el.type === 'radio') el.checked = String(el.value) === String(v);
          else el.value = v;
        });
        recompute();
      }
    };
  }

  /**
   * Wire the plain-field autosave used by Blok I, where each input maps to one
   * column rather than into a JSON model.
   */
  function bindFields(cfg) {
    var form = document.querySelector(cfg.form || '#k-form');
    if (!form) return;

    var status = new Status(document.getElementById('k-status'));
    var timers = {};

    function save(name, value) {
      status.show('Menyimpan…', 'saving');
      fetch(cfg.autoSaveUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': cfg.csrf,
          'Accept': 'application/json'
        },
        body: JSON.stringify({ field: name, value: value })
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.success) {
            status.show('Tersimpan otomatis · ' + (data.last_saved_at || ''), 'success');
            document.dispatchEvent(new CustomEvent('konstruksi:saved', {
              detail: { field: name, blok_completed: data.blok_completed }
            }));
          } else {
            status.show('Gagal menyimpan: ' + (data.message || 'tidak diketahui'), 'error');
          }
        })
        .catch(function (e) { status.show('Gagal menyimpan: ' + e.message, 'error'); });
    }

    function handle(el, immediate) {
      var name = el.getAttribute('name');
      if (!name || el.hasAttribute('data-k-noautosave')) return;
      // Hidden inputs carry the serialised JSON models, and model-bound
      // controls are saved whole by bind(). Neither is a plain column, and
      // posting one here would overwrite a model with an empty string.
      if (el.type === 'hidden' || el.hasAttribute('data-k-path')) return;
      clearTimeout(timers[name]);
      if (immediate) save(name, el.value);
      else timers[name] = setTimeout(function () { save(name, el.value); }, 1200);
    }

    form.addEventListener('input', function (e) {
      if (e.target.matches('input[name], textarea[name], select[name]')) handle(e.target, false);
    });
    form.addEventListener('blur', function (e) {
      if (e.target.matches('input[name], textarea[name], select[name]')) handle(e.target, true);
    }, true);
    form.addEventListener('change', function (e) {
      if (e.target.type === 'radio' && e.target.checked) handle(e.target, true);
    });
  }

  /** Client-side required check with inline messages, shared by every blok. */
  function requireFields(formSel, rules) {
    var form = document.querySelector(formSel);
    if (!form) return;

    form.addEventListener('submit', function (e) {
      var firstBad = null;
      Object.keys(rules).forEach(function (name) {
        var els = form.querySelectorAll('[name="' + name + '"]');
        if (!els.length) return;
        var filled = els[0].type === 'radio'
          ? Array.prototype.some.call(els, function (r) { return r.checked; })
          : String(els[0].value).trim() !== '';
        var err = form.querySelector('.k-err[data-field="' + name + '"]');
        if (!filled) {
          if (err) err.textContent = rules[name];
          els[0].classList.add('is-error');
          if (!firstBad) firstBad = els[0];
        } else {
          if (err) err.textContent = '';
          els[0].classList.remove('is-error');
        }
      });
      if (firstBad) {
        e.preventDefault();
        firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstBad.focus({ preventScroll: true });
      }
    });

    form.addEventListener('input', function (e) {
      if (!e.target.name) return;
      var err = form.querySelector('.k-err[data-field="' + e.target.name + '"]');
      if (err) err.textContent = '';
      e.target.classList.remove('is-error');
    });
  }

  /* ── Live blok rail ──────────────────────────────────────────────────────
   *
   * The questionnaire branches on the preamble: "Tidak Ada Proyek Konstruksi"
   * drops Blok III on SKTR and Blok II–IX on SKTH. Those answers autosave, so
   * the rail would otherwise keep offering blocks the server has already
   * stopped accepting until the responden happened to navigate.
   *
   * KonstruksiBlokPath::path() stays the single source of truth — this refetches
   * the rendered rows from survey.konstruksi.nav rather than reimplementing the
   * rule in JS, where it could drift.
   */
  function navWatcher() {
    var rail = document.getElementById('k-rail');
    if (!rail) return;

    var url = rail.getAttribute('data-k-nav-url');
    if (!url) return;

    var current = rail.getAttribute('data-k-nav-current') || '';

    // Answers that can change the SHAPE of the path. Anything else can only
    // flip a tick, which is caught by the blok_completed comparison below.
    var PATH_FIELDS = ['status_perusahaan', 'kesediaan_responden', 'keberadaan_proyek'];

    var lastCompleted = null;
    var inFlight = false;
    var pending = false;
    var timer = null;

    function apply(html) {
      document.querySelectorAll('.k-rail-nav').forEach(function (nav) {
        nav.innerHTML = html;
        // Restart the animation: re-adding a class the element already has
        // does nothing, so it has to come off first.
        nav.classList.remove('is-updated');
        void nav.offsetWidth;
        nav.classList.add('is-updated');
      });

      // The floating mobile button summarises the rail, so it moves with it.
      var items = document.querySelectorAll('#k-rail-nav .k-rail-item');
      var done = document.querySelectorAll('#k-rail-nav .k-rail-item.is-done');
      var active = document.querySelector('#k-rail-nav .k-rail-item.is-active .k-rail-label b');
      var countEl = document.querySelector('[data-k-fab-count]');
      var labelEl = document.querySelector('[data-k-fab-label]');
      if (countEl) countEl.textContent = done.length + '/' + items.length;
      if (labelEl && active) labelEl.textContent = active.textContent;
    }

    function fetchRows() {
      if (inFlight) { pending = true; return; }
      inFlight = true;

      fetch(url + (url.indexOf('?') === -1 ? '?' : '&') + 'current=' + encodeURIComponent(current), {
        headers: { 'Accept': 'text/html' },
        credentials: 'same-origin'
      })
        .then(function (r) { return (r.ok && r.status !== 204) ? r.text() : null; })
        .then(function (html) { if (html) apply(html); })
        // A failed refresh is not worth interrupting the responden over: the
        // rail simply stays as it was, and the server still enforces the path.
        .catch(function () {})
        .then(function () {
          inFlight = false;
          if (pending) { pending = false; fetchRows(); }
        });
    }

    document.addEventListener('konstruksi:saved', function (e) {
      var d = (e && e.detail) || {};

      var shapeChanged = PATH_FIELDS.indexOf(d.field) !== -1;
      var tickChanged = d.blok_completed !== lastCompleted;
      lastCompleted = d.blok_completed;

      // Every keystroke on a grid page autosaves; refetching the rail each time
      // would be a request per character for a list that did not move.
      if (!shapeChanged && !tickChanged) return;

      clearTimeout(timer);
      timer = setTimeout(fetchRows, shapeChanged ? 0 : 300);
    });
  }

  /** Mobile blok-picker sheet. */
  function mobileNav() {
    var fab = document.getElementById('k-fab');
    var sheet = document.getElementById('k-sheet');
    if (!fab || !sheet) return;
    var open = false;

    function toggle(next) {
      open = next;
      sheet.hidden = !next;
    }
    fab.addEventListener('click', function (e) { e.stopPropagation(); toggle(!open); });
    sheet.addEventListener('click', function (e) { e.stopPropagation(); });
    document.addEventListener('click', function () { if (open) toggle(false); });
  }

  document.addEventListener('DOMContentLoaded', function () {
    mobileNav();
    navWatcher();
  });

  global.Konstruksi = {
    num: num,
    fmt: fmt,
    regroup: regroup,
    getPath: getPath,
    setPath: setPath,
    bind: bind,
    bindFields: bindFields,
    requireFields: requireFields
  };
})(window);
