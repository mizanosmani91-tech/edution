(function () {
  'use strict';

  var PROFILE_KEY = 'vitae.profile';
  var CVS_KEY = 'vitae.cvs';
  var ACTIVE_KEY = 'vitae.activeId';

  var TEMPLATES = [
    { id: 'modern', name: 'মডার্ন মিন্ট', swatch: '#EAF7F4', accent: '#00C2A8' },
    { id: 'classic', name: 'ক্লাসিক গ্রে', swatch: '#F5F6FA', accent: '#242C4D' },
    { id: 'minimal', name: 'মিনিমাল কোরাল', swatch: '#FFF1EF', accent: '#FF6B5D' },
    { id: 'ats', name: 'ATS বেসিক', swatch: '#FFF8E8', accent: '#FFB020' },
  ];

  var profile = loadProfile();
  var cvs = loadCvs();
  var activeId = localStorage.getItem(ACTIVE_KEY) || null;
  var pendingTemplateTarget = 'editor'; // where template selection should route to

  function $(id) { return document.getElementById(id); }

  function loadProfile() {
    try { return JSON.parse(localStorage.getItem(PROFILE_KEY)) || null; } catch (e) { return null; }
  }
  function saveProfile() { localStorage.setItem(PROFILE_KEY, JSON.stringify(profile)); }

  function loadCvs() {
    try { return JSON.parse(localStorage.getItem(CVS_KEY)) || []; } catch (e) { return []; }
  }
  function saveCvs() {
    try { localStorage.setItem(CVS_KEY, JSON.stringify(cvs)); }
    catch (e) { showToast('⚠️ সেভ করা যায়নি (স্টোরেজ পূর্ণ)'); }
  }
  function saveActiveId() { localStorage.setItem(ACTIVE_KEY, activeId || ''); }

  function uid() { return Math.random().toString(36).slice(2, 9) + Date.now().toString(36).slice(-4); }

  function showToast(msg) {
    var el = $('toast');
    el.textContent = msg;
    el.hidden = false;
    clearTimeout(showToast._t);
    showToast._t = setTimeout(function () { el.hidden = true; }, 2200);
  }

  function defaultCvData() {
    return {
      photo: '', name: '', title: '', phone: '', email: '', address: '', dob: '', nid: '',
      category: '', objective: '',
      education: [], experience: [], skills: [], languages: [], certifications: [], references: [],
      refOnRequest: false,
    };
  }

  function getActiveCv() {
    return cvs.find(function (c) { return c.id === activeId; }) || null;
  }

  function createNewCv(template) {
    var cv = { id: uid(), template: template || 'modern', updatedAt: Date.now(), data: defaultCvData() };
    cvs.unshift(cv);
    saveCvs();
    activeId = cv.id;
    saveActiveId();
    return cv;
  }

  function touchActiveCv() {
    var cv = getActiveCv();
    if (cv) { cv.updatedAt = Date.now(); saveCvs(); }
  }

  // ---------------- Router ----------------
  var SCREENS = ['scrOnboarding', 'scrHome', 'scrTemplates', 'scrEditor', 'scrPreview', 'scrSettings'];

  function navigate(name) {
    var map = { onboarding: 'scrOnboarding', home: 'scrHome', templates: 'scrTemplates', editor: 'scrEditor', preview: 'scrPreview', settings: 'scrSettings' };
    var targetId = map[name];
    SCREENS.forEach(function (id) { $(id).hidden = (id !== targetId); });
    window.scrollTo(0, 0);
    if (name === 'home') renderHome();
    if (name === 'templates') renderTemplateGrid();
    if (name === 'editor') renderEditor();
    if (name === 'preview') renderPreviewScreen();
    if (name === 'settings') renderSettings();
  }

  function initNav() {
    document.querySelectorAll('[data-nav]').forEach(function (btn) {
      btn.addEventListener('click', function () { navigate(btn.getAttribute('data-nav')); });
    });
    document.querySelectorAll('[data-back]').forEach(function (btn) {
      btn.addEventListener('click', function () { navigate(btn.getAttribute('data-back')); });
    });
    document.querySelectorAll('[data-nav-to]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        pendingTemplateTarget = 'preview';
        navigate(btn.getAttribute('data-nav-to'));
      });
    });
    ['btnNewCv', 'btnFabNew', 'btnFabNew2', 'btnFabNew3'].forEach(function (id) {
      var el = $(id);
      if (el) el.addEventListener('click', function () {
        pendingTemplateTarget = 'editor-new';
        navigate('templates');
      });
    });
    $('btnSettingsShortcut').addEventListener('click', function () { navigate('settings'); });
  }

  // ---------------- Onboarding ----------------
  function initOnboarding() {
    $('btnObStart').addEventListener('click', function () {
      var name = $('obName').value.trim();
      if (!name) { showToast('⚠️ নাম লিখুন'); return; }
      profile = { name: name };
      saveProfile();
      navigate('home');
    });
    $('obName').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') $('btnObStart').click();
    });
  }

  // ---------------- Home ----------------
  function timeAgo(ts) {
    var diff = Date.now() - ts;
    var min = Math.floor(diff / 60000);
    if (min < 1) return 'এইমাত্র';
    if (min < 60) return min + ' মিনিট আগে';
    var hr = Math.floor(min / 60);
    if (hr < 24) return hr + ' ঘণ্টা আগে';
    var day = Math.floor(hr / 24);
    if (day < 30) return day + ' দিন আগে';
    return new Date(ts).toLocaleDateString('bn-BD');
  }

  function greetByTime() {
    var h = new Date().getHours();
    if (h < 12) return 'শুভ সকাল 👋';
    if (h < 17) return 'শুভ অপরাহ্ন 👋';
    return 'শুভ সন্ধ্যা 👋';
  }

  function renderHome() {
    $('homeGreetTime').textContent = greetByTime();
    $('homeUserName').textContent = (profile && profile.name) || 'অতিথি';
    $('homeCvCount').textContent = 'আমার সিভি (' + cvs.length + ')';
    var list = $('cvList');
    list.innerHTML = '';
    $('homeEmptyHint').hidden = cvs.length > 0;

    cvs.slice().sort(function (a, b) { return b.updatedAt - a.updatedAt; }).forEach(function (cv) {
      var tpl = TEMPLATES.find(function (t) { return t.id === cv.template; }) || TEMPLATES[0];
      var card = document.createElement('div');
      card.className = 'cv-card';
      var title = cv.data.title || cv.data.name || 'নতুন CV';
      card.innerHTML =
        '<div class="cv-thumb" style="background:' + tpl.swatch + ';">' +
          '<div class="bar" style="top:8px; background:' + tpl.accent + ';"></div>' +
          '<div class="bar" style="top:15px; width:60%;"></div>' +
          '<div class="bar" style="top:22px; width:70%;"></div>' +
        '</div>' +
        '<div class="cv-info"><div class="nm"></div><div class="meta"><span class="tpl-tag"></span><span class="date"></span></div></div>' +
        '<button class="cv-more" type="button">✕</button>';
      card.querySelector('.nm').textContent = title;
      card.querySelector('.tpl-tag').textContent = tpl.name;
      card.querySelector('.date').textContent = timeAgo(cv.updatedAt);
      card.addEventListener('click', function (e) {
        if (e.target.closest('.cv-more')) return;
        activeId = cv.id; saveActiveId();
        pendingTemplateTarget = 'editor';
        navigate('editor');
      });
      card.querySelector('.cv-more').addEventListener('click', function () {
        if (confirm('"' + title + '" মুছে ফেলবেন?')) {
          cvs = cvs.filter(function (c) { return c.id !== cv.id; });
          saveCvs();
          renderHome();
        }
      });
      list.appendChild(card);
    });
  }

  // ---------------- Template gallery ----------------
  function renderTemplateGrid() {
    var grid = $('tplGrid');
    grid.innerHTML = '';
    var current = getActiveCv();
    TEMPLATES.forEach(function (tpl) {
      var card = document.createElement('div');
      card.className = 'tpl-card' + (current && pendingTemplateTarget !== 'editor-new' && current.template === tpl.id ? ' selected' : '');
      card.innerHTML =
        '<div class="tpl-preview" style="background:' + tpl.swatch + ';">' +
          '<div class="tpl-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>' +
          '<div class="wf-photo" style="background:' + tpl.accent + ';"></div>' +
          '<div class="wf-line w80" style="background:#242C4D;"></div>' +
          '<div class="wf-line w40" style="background:' + tpl.accent + ';"></div>' +
          '<div style="height:8px;"></div>' +
          '<div class="wf-line w60" style="background:#D8DEEC;"></div>' +
          '<div class="wf-line w50" style="background:#D8DEEC;"></div>' +
          '<div class="wf-line w80" style="background:#D8DEEC;"></div>' +
        '</div>' +
        '<div class="nm"></div>';
      card.querySelector('.nm').textContent = tpl.name;
      card.addEventListener('click', function () {
        if (pendingTemplateTarget === 'editor-new') {
          createNewCv(tpl.id);
          navigate('editor');
        } else {
          var cv = getActiveCv();
          if (cv) { cv.template = tpl.id; touchActiveCv(); }
          navigate(pendingTemplateTarget === 'preview' ? 'preview' : 'editor');
        }
      });
      grid.appendChild(card);
    });
  }

  // ---------------- Editor ----------------
  var simpleFieldMap = [
    ['fName', 'name'], ['fTitle', 'title'], ['fPhone', 'phone'], ['fEmail', 'email'],
    ['fAddress', 'address'], ['fDob', 'dob'], ['fNid', 'nid'], ['fObjective', 'objective'],
  ];

  var repeatConfigs = {
    education: {
      listId: 'listEducation',
      fields: [
        { key: 'degree', label: 'ডিগ্রি/পরীক্ষা', type: 'select', options: CV_LIBRARY.degrees },
        { key: 'result', label: 'ফলাফল (GPA/Division)', type: 'text' },
        { key: 'institute', label: 'প্রতিষ্ঠানের নাম', type: 'text', wide: true },
        { key: 'year', label: 'পাসের সাল', type: 'text' },
        { key: 'board', label: 'বোর্ড/বিশ্ববিদ্যালয়', type: 'text' },
      ],
    },
    experience: {
      listId: 'listExperience',
      fields: [
        { key: 'position', label: 'পদবি', type: 'text' },
        { key: 'company', label: 'প্রতিষ্ঠান', type: 'text' },
        { key: 'duration', label: 'সময়কাল', type: 'text', wide: true, placeholder: 'যেমন: জানু ২০২২ - বর্তমান' },
        { key: 'description', label: 'দায়িত্ব সংক্ষেপে', type: 'textarea', wide: true },
      ],
    },
    language: {
      listId: 'listLanguage',
      fields: [
        { key: 'name', label: 'ভাষা', type: 'text' },
        { key: 'level', label: 'দক্ষতা', type: 'select', options: CV_LIBRARY.languageLevels },
      ],
    },
    certification: {
      listId: 'listCertification',
      fields: [
        { key: 'title', label: 'কোর্স/সার্টিফিকেট', type: 'text', wide: true },
        { key: 'issuer', label: 'প্রদানকারী', type: 'text' },
        { key: 'year', label: 'সাল', type: 'text' },
      ],
    },
    reference: {
      listId: 'listReference',
      fields: [
        { key: 'name', label: 'নাম', type: 'text' },
        { key: 'position', label: 'পদবি ও প্রতিষ্ঠান', type: 'text' },
        { key: 'contact', label: 'ফোন/ইমেইল', type: 'text', wide: true },
      ],
    },
  };
  var stateKeyByType = { education: 'education', experience: 'experience', language: 'languages', certification: 'certifications', reference: 'references' };

  function esc(str) {
    var d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
  }

  function initCategorySelect() {
    var sel = $('fCategory');
    sel.innerHTML = '<option value="">— নির্বাচন করুন —</option>';
    CV_LIBRARY.categories.forEach(function (c) {
      var opt = document.createElement('option');
      opt.value = c.id; opt.textContent = c.label;
      sel.appendChild(opt);
    });
    sel.addEventListener('change', function () {
      var cv = getActiveCv(); if (!cv) return;
      cv.data.category = sel.value;
      var cat = CV_LIBRARY.getCategory(sel.value);
      if (cat) {
        if (!cv.data.objective.trim()) { cv.data.objective = cat.objective; $('fObjective').value = cat.objective; }
        cat.skills.forEach(function (s) { if (cv.data.skills.indexOf(s) === -1) cv.data.skills.push(s); });
      }
      touchActiveCv();
      renderSkills();
      updateProgress();
    });
  }

  function renderRepeatList(type) {
    var cfg = repeatConfigs[type];
    var stateKey = stateKeyByType[type];
    var cv = getActiveCv(); if (!cv) return;
    var container = $(cfg.listId);
    container.innerHTML = '';
    cv.data[stateKey].forEach(function (row) {
      var rowEl = document.createElement('div');
      rowEl.className = 'exp-entry';
      cfg.fields.forEach(function (f) {
        var wrap = document.createElement('div');
        wrap.className = 'field' + (f.wide ? '' : '');
        if (f.wide) wrap.style.gridColumn = '1/-1';
        var label = document.createElement('label'); label.textContent = f.label;
        wrap.appendChild(label);
        var input;
        if (f.type === 'select') {
          input = document.createElement('select');
          input.innerHTML = '<option value="">—</option>';
          f.options.forEach(function (o) {
            var opt = document.createElement('option'); opt.value = o; opt.textContent = o;
            input.appendChild(opt);
          });
        } else if (f.type === 'textarea') {
          input = document.createElement('textarea'); input.rows = 2;
        } else {
          input = document.createElement('input'); input.type = 'text';
          if (f.placeholder) input.placeholder = f.placeholder;
        }
        input.value = row[f.key] || '';
        input.addEventListener('input', function () {
          row[f.key] = input.value;
          touchActiveCv();
          updateProgress();
        });
        wrap.appendChild(input);
        rowEl.appendChild(wrap);
      });
      var rm = document.createElement('button');
      rm.type = 'button'; rm.className = 'row-remove'; rm.textContent = '✕ মুছুন';
      rm.addEventListener('click', function () {
        cv.data[stateKey] = cv.data[stateKey].filter(function (r) { return r._id !== row._id; });
        touchActiveCv();
        renderRepeatList(type);
        updateProgress();
      });
      rowEl.appendChild(rm);
      container.appendChild(rowEl);
    });
  }

  function initRepeatAdd() {
    document.querySelectorAll('[data-add]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var type = btn.getAttribute('data-add');
        var stateKey = stateKeyByType[type];
        var cv = getActiveCv(); if (!cv) return;
        cv.data[stateKey].push({ _id: uid() });
        touchActiveCv();
        renderRepeatList(type);
      });
    });
  }

  function renderSkills() {
    var cv = getActiveCv(); if (!cv) return;
    var cat = CV_LIBRARY.getCategory(cv.data.category);
    var suggestWrap = $('skillChips');
    suggestWrap.innerHTML = '';
    if (cat) {
      cat.skills.forEach(function (s) {
        var chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'chip' + (cv.data.skills.indexOf(s) !== -1 ? ' is-selected' : '');
        chip.textContent = s;
        chip.addEventListener('click', function () {
          var idx = cv.data.skills.indexOf(s);
          if (idx !== -1) cv.data.skills.splice(idx, 1); else cv.data.skills.push(s);
          touchActiveCv();
          renderSkills();
          updateProgress();
        });
        suggestWrap.appendChild(chip);
      });
    }
    var selectedWrap = $('skillSelected');
    selectedWrap.innerHTML = '';
    cv.data.skills.forEach(function (s) {
      var chip = document.createElement('span'); chip.className = 'chip'; chip.textContent = s + ' ';
      var rm = document.createElement('button'); rm.type = 'button'; rm.textContent = '✕';
      rm.addEventListener('click', function () {
        cv.data.skills = cv.data.skills.filter(function (x) { return x !== s; });
        touchActiveCv();
        renderSkills();
        updateProgress();
      });
      chip.appendChild(rm);
      selectedWrap.appendChild(chip);
    });
  }

  function initSkillInput() {
    $('fSkillInput').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        var cv = getActiveCv(); if (!cv) return;
        var v = $('fSkillInput').value.trim();
        if (v && cv.data.skills.indexOf(v) === -1) cv.data.skills.push(v);
        $('fSkillInput').value = '';
        touchActiveCv();
        renderSkills();
        updateProgress();
      }
    });
  }

  function resizeImage(file, maxW, maxH, cb) {
    var img = new Image();
    var reader = new FileReader();
    reader.onload = function (e) { img.src = e.target.result; };
    img.onload = function () {
      var width = img.width, height = img.height;
      var ratio = Math.min(maxW / width, maxH / height, 1);
      width = Math.round(width * ratio); height = Math.round(height * ratio);
      var canvas = document.createElement('canvas');
      canvas.width = width; canvas.height = height;
      canvas.getContext('2d').drawImage(img, 0, 0, width, height);
      cb(canvas.toDataURL('image/jpeg', 0.85));
    };
    reader.readAsDataURL(file);
  }

  function initPhoto() {
    $('photoCircle').addEventListener('click', function () { $('fPhoto').click(); });
    $('fPhoto').addEventListener('change', function (e) {
      var file = e.target.files[0]; if (!file) return;
      var cv = getActiveCv(); if (!cv) return;
      resizeImage(file, 300, 300, function (dataUrl) {
        cv.data.photo = dataUrl;
        $('photoPreview').src = dataUrl; $('photoPreview').hidden = false;
        touchActiveCv();
        updateProgress();
      });
    });
  }

  function initAccordion() {
    document.querySelectorAll('.acc-item .acc-head').forEach(function (head) {
      head.addEventListener('click', function () { head.parentElement.classList.toggle('open'); });
    });
  }

  function initSimpleFields() {
    simpleFieldMap.forEach(function (pair) {
      var el = $(pair[0]);
      el.addEventListener('input', function () {
        var cv = getActiveCv(); if (!cv) return;
        cv.data[pair[1]] = el.value;
        touchActiveCv();
        updateProgress();
      });
    });
  }

  function updateProgress() {
    var cv = getActiveCv(); if (!cv) return;
    var d = cv.data;
    var checks = [
      !!d.name, !!d.title, !!d.phone || !!d.email, !!d.objective,
      d.education.length > 0, d.experience.length > 0, d.skills.length > 0,
    ];
    var done = checks.filter(Boolean).length;
    var pct = Math.round((done / checks.length) * 100);
    $('edProgressPct').textContent = pct + '%';
    $('edProgressFill').style.width = pct + '%';
  }

  function renderEditor() {
    var cv = getActiveCv();
    if (!cv) { navigate('home'); return; }
    $('edTitle').textContent = cv.data.title || cv.data.name || 'নতুন CV';

    simpleFieldMap.forEach(function (pair) { $(pair[0]).value = cv.data[pair[1]] || ''; });
    $('fCategory').value = cv.data.category || '';
    if (cv.data.photo) { $('photoPreview').src = cv.data.photo; $('photoPreview').hidden = false; }
    else { $('photoPreview').hidden = true; }
    $('fRefOnRequest').checked = !!cv.data.refOnRequest;

    Object.keys(repeatConfigs).forEach(function (type) { renderRepeatList(type); });
    renderSkills();
    updateProgress();

    $('btnEdPreview').onclick = function () { navigate('preview'); };
    $('fRefOnRequest').onchange = function () {
      cv.data.refOnRequest = $('fRefOnRequest').checked;
      touchActiveCv();
    };
    $('btnDeleteCv').onclick = function () {
      if (confirm('এই CV পুরোপুরি মুছে ফেলবেন?')) {
        cvs = cvs.filter(function (c) { return c.id !== cv.id; });
        saveCvs();
        activeId = null; saveActiveId();
        navigate('home');
      }
    };
  }

  // ---------------- Preview / PDF / Share ----------------
  function renderCvHtml(cv) {
    var d = cv.data;
    var hasAnything = d.name || d.objective || d.education.length || d.experience.length;
    if (!hasAnything) return '<div class="cv-empty-hint">এডিটরে তথ্য যোগ করলে এখানে লাইভ প্রিভিউ দেখতে পাবেন।</div>';

    var html = '<div class="cv-header">';
    if (d.photo) html += '<img class="cv-photo" src="' + d.photo + '" alt="" />';
    html += '<div>';
    html += '<p class="cv-name">' + (esc(d.name) || 'আপনার নাম') + '</p>';
    if (d.title) html += '<p class="cv-title">' + esc(d.title) + '</p>';
    var contacts = [d.phone, d.email, d.address].filter(Boolean).map(esc);
    if (contacts.length) html += '<div class="cv-contact">' + contacts.map(function (c) { return '<span>' + c + '</span>'; }).join('') + '</div>';
    html += '</div></div>';

    if (d.objective) html += '<div class="cv-section-title">ক্যারিয়ার অবজেক্টিভ</div><p class="cv-para">' + esc(d.objective) + '</p>';

    if (d.education.length) {
      html += '<div class="cv-section-title">শিক্ষাগত যোগ্যতা</div>';
      d.education.forEach(function (r) {
        if (!r.degree && !r.institute) return;
        html += '<div class="cv-item"><div class="cv-item-title">' + esc(r.degree) + (r.result ? ' — ' + esc(r.result) : '') + '</div>';
        html += '<div class="cv-item-sub">' + [r.institute, r.board, r.year].filter(Boolean).map(esc).join(', ') + '</div></div>';
      });
    }

    if (d.experience.length) {
      html += '<div class="cv-section-title">কর্ম অভিজ্ঞতা</div>';
      d.experience.forEach(function (r) {
        if (!r.position && !r.company) return;
        html += '<div class="cv-item"><div class="cv-item-title">' + esc(r.position) + (r.company ? ' — ' + esc(r.company) : '') + '</div>';
        if (r.duration) html += '<div class="cv-item-sub">' + esc(r.duration) + '</div>';
        if (r.description) html += '<p class="cv-para">' + esc(r.description) + '</p>';
        html += '</div>';
      });
    }

    if (d.skills.length) {
      html += '<div class="cv-section-title">দক্ষতা</div><div class="cv-skill-list">' + d.skills.map(function (s) { return '<span class="cv-skill-pill">' + esc(s) + '</span>'; }).join('') + '</div>';
    }

    var langs = d.languages.filter(function (r) { return r.name; });
    if (langs.length) html += '<div class="cv-section-title">ভাষা দক্ষতা</div><div class="cv-lang-list">' + langs.map(function (r) { return '<span class="cv-skill-pill">' + esc(r.name) + (r.level ? ' (' + esc(r.level) + ')' : '') + '</span>'; }).join('') + '</div>';

    var certs = d.certifications.filter(function (r) { return r.title; });
    if (certs.length) {
      html += '<div class="cv-section-title">প্রশিক্ষণ / সার্টিফিকেট</div>';
      certs.forEach(function (r) { html += '<div class="cv-item"><div class="cv-item-title">' + esc(r.title) + '</div><div class="cv-item-sub">' + [r.issuer, r.year].filter(Boolean).map(esc).join(', ') + '</div></div>'; });
    }

    if (d.refOnRequest) {
      html += '<div class="cv-section-title">রেফারেন্স</div><p class="cv-para">অনুরোধ সাপেক্ষে প্রদান করা হবে।</p>';
    } else {
      var refs = d.references.filter(function (r) { return r.name; });
      if (refs.length) {
        html += '<div class="cv-section-title">রেফারেন্স</div>';
        refs.forEach(function (r) { html += '<div class="cv-item"><div class="cv-item-title">' + esc(r.name) + '</div><div class="cv-item-sub">' + [r.position, r.contact].filter(Boolean).map(esc).join(' • ') + '</div></div>'; });
      }
    }
    return html;
  }

  function renderPreviewScreen() {
    var cv = getActiveCv();
    if (!cv) { navigate('home'); return; }
    var tpl = TEMPLATES.find(function (t) { return t.id === cv.template; }) || TEMPLATES[0];
    $('pvTplName').textContent = tpl.name;
    var el = $('cvPreview');
    el.className = 'cv-page tpl-' + cv.template;
    el.innerHTML = renderCvHtml(cv);

    $('btnPvEdit').onclick = function () { navigate('editor'); };
    $('btnDownload').onclick = handleDownload;
    $('btnShare').onclick = handleShare;
  }

  async function generatePdfBlob(cv) {
    var jsPDFCtor = window.jspdf.jsPDF;
    var source = $('cvPreview');
    var canvas = await html2canvas(source, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
    var imgData = canvas.toDataURL('image/jpeg', 0.95);
    var pdf = new jsPDFCtor({ unit: 'mm', format: 'a4', orientation: 'portrait' });
    var pageW = pdf.internal.pageSize.getWidth();
    var pageH = pdf.internal.pageSize.getHeight();
    var imgW = pageW;
    var imgH = (canvas.height * imgW) / canvas.width;
    if (imgH <= pageH) {
      pdf.addImage(imgData, 'JPEG', 0, 0, imgW, imgH);
    } else {
      var heightLeft = imgH, position = 0;
      pdf.addImage(imgData, 'JPEG', 0, position, imgW, imgH);
      heightLeft -= pageH;
      while (heightLeft > 0) {
        position = heightLeft - imgH;
        pdf.addPage();
        pdf.addImage(imgData, 'JPEG', 0, position, imgW, imgH);
        heightLeft -= pageH;
      }
    }
    return pdf.output('blob');
  }

  function fileNameForCv(cv) {
    var base = (cv.data.name || 'cv').trim().replace(/\s+/g, '_');
    return base + '_CV.pdf';
  }

  async function handleDownload() {
    var cv = getActiveCv(); if (!cv) return;
    if (!cv.data.name) { showToast('⚠️ প্রথমে নাম লিখুন'); return; }
    showToast('⏳ পিডিএফ তৈরি হচ্ছে...');
    try {
      var blob = await generatePdfBlob(cv);
      var url = URL.createObjectURL(blob);
      var a = document.createElement('a');
      a.href = url; a.download = fileNameForCv(cv);
      document.body.appendChild(a); a.click(); a.remove();
      setTimeout(function () { URL.revokeObjectURL(url); }, 4000);
      showToast('✅ ডাউনলোড সম্পন্ন');
    } catch (e) { showToast('❌ পিডিএফ তৈরি করা যায়নি'); }
  }

  async function handleShare() {
    var cv = getActiveCv(); if (!cv) return;
    if (!cv.data.name) { showToast('⚠️ প্রথমে নাম লিখুন'); return; }
    showToast('⏳ শেয়ারের জন্য প্রস্তুত করা হচ্ছে...');
    try {
      var blob = await generatePdfBlob(cv);
      var file = new File([blob], fileNameForCv(cv), { type: 'application/pdf' });
      if (navigator.canShare && navigator.canShare({ files: [file] })) {
        await navigator.share({ files: [file], title: 'আমার সিভি', text: cv.data.name + ' এর সিভি' });
        showToast('✅ শেয়ার হয়েছে');
      } else {
        await handleDownload();
        showToast('ℹ️ এই ব্রাউজারে সরাসরি শেয়ার সাপোর্ট নেই, তাই ডাউনলোড হয়েছে');
      }
    } catch (e) {
      if (e && e.name === 'AbortError') return;
      showToast('❌ শেয়ার করা যায়নি');
    }
  }

  // ---------------- Settings ----------------
  function renderSettings() {
    $('setProfileName').textContent = (profile && profile.name) || '—';
    $('setAvatarInitial').textContent = ((profile && profile.name) || '?').trim().charAt(0).toUpperCase();
    $('setCvCount').textContent = cvs.length;

    $('btnEditName').onclick = function () {
      var name = prompt('আপনার নাম:', (profile && profile.name) || '');
      if (name && name.trim()) {
        profile = { name: name.trim() };
        saveProfile();
        renderSettings();
      }
    };
    $('btnLogout').onclick = function () {
      if (confirm('এই ফোন থেকে আপনার প্রোফাইল ও সব CV মুছে ফেলা হবে। নিশ্চিত?')) {
        localStorage.removeItem(PROFILE_KEY);
        localStorage.removeItem(CVS_KEY);
        localStorage.removeItem(ACTIVE_KEY);
        profile = null; cvs = []; activeId = null;
        navigate('onboarding');
      }
    };
  }

  // ---------------- Init ----------------
  function init() {
    initNav();
    initOnboarding();
    initCategorySelect();
    initSimpleFields();
    initPhoto();
    initRepeatAdd();
    initSkillInput();
    initAccordion();

    if (profile && profile.name) {
      navigate('home');
    } else {
      navigate('onboarding');
    }

    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('sw.js').catch(function () {});
    }
  }

  document.addEventListener('DOMContentLoaded', init);
})();
