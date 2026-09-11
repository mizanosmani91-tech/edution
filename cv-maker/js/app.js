(function () {
  'use strict';

  const STORAGE_KEY = 'cvmaker.v1';

  const defaultState = () => ({
    photo: '',
    name: '',
    title: '',
    phone: '',
    email: '',
    address: '',
    dob: '',
    nid: '',
    category: '',
    objective: '',
    education: [],
    experience: [],
    skills: [],
    languages: [],
    certifications: [],
    references: [],
    refOnRequest: false,
    template: 'modern',
  });

  let state = loadState();

  function loadState() {
    try {
      const raw = localStorage.getItem(STORAGE_KEY);
      if (!raw) return defaultState();
      return Object.assign(defaultState(), JSON.parse(raw));
    } catch (e) {
      return defaultState();
    }
  }

  function saveState() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    } catch (e) {
      showToast('⚠️ সেভ করা যায়নি (স্টোরেজ পূর্ণ হয়ে থাকতে পারে)');
    }
  }

  // ---------- helpers ----------
  const $ = (id) => document.getElementById(id);

  function showToast(msg) {
    const el = $('toast');
    el.textContent = msg;
    el.hidden = false;
    clearTimeout(showToast._t);
    showToast._t = setTimeout(() => { el.hidden = true; }, 2200);
  }

  function uid() {
    return Math.random().toString(36).slice(2, 9);
  }

  // ---------- category select ----------
  function initCategorySelect() {
    const sel = $('fCategory');
    CV_LIBRARY.categories.forEach((c) => {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.label;
      sel.appendChild(opt);
    });
    sel.value = state.category;

    sel.addEventListener('change', () => {
      state.category = sel.value;
      const cat = CV_LIBRARY.getCategory(sel.value);
      if (cat) {
        if (!state.objective.trim()) {
          state.objective = cat.objective;
          $('fObjective').value = state.objective;
        }
        cat.skills.forEach((s) => addSkill(s));
      }
      persistAndRender();
    });
  }

  // ---------- simple text fields ----------
  const simpleFieldMap = [
    ['fName', 'name'], ['fTitle', 'title'], ['fPhone', 'phone'], ['fEmail', 'email'],
    ['fAddress', 'address'], ['fDob', 'dob'], ['fNid', 'nid'], ['fObjective', 'objective'],
  ];

  function initSimpleFields() {
    simpleFieldMap.forEach(([id, key]) => {
      const el = $(id);
      el.value = state[key] || '';
      el.addEventListener('input', () => {
        state[key] = el.value;
        persistAndRender();
      });
    });
  }

  // ---------- photo ----------
  function initPhoto() {
    if (state.photo) {
      $('photoPreview').src = state.photo;
      $('photoPreview').hidden = false;
      $('btnRemovePhoto').hidden = false;
    }
    $('fPhoto').addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;
      resizeImage(file, 300, 300, (dataUrl) => {
        state.photo = dataUrl;
        $('photoPreview').src = dataUrl;
        $('photoPreview').hidden = false;
        $('btnRemovePhoto').hidden = false;
        persistAndRender();
      });
    });
    $('btnRemovePhoto').addEventListener('click', () => {
      state.photo = '';
      $('fPhoto').value = '';
      $('photoPreview').hidden = true;
      $('btnRemovePhoto').hidden = true;
      persistAndRender();
    });
  }

  function resizeImage(file, maxW, maxH, cb) {
    const img = new Image();
    const reader = new FileReader();
    reader.onload = (e) => { img.src = e.target.result; };
    img.onload = () => {
      let { width, height } = img;
      const ratio = Math.min(maxW / width, maxH / height, 1);
      width = Math.round(width * ratio);
      height = Math.round(height * ratio);
      const canvas = document.createElement('canvas');
      canvas.width = width; canvas.height = height;
      canvas.getContext('2d').drawImage(img, 0, 0, width, height);
      cb(canvas.toDataURL('image/jpeg', 0.85));
    };
    reader.readAsDataURL(file);
  }

  // ---------- repeatable sections ----------
  const repeatConfigs = {
    education: {
      listId: 'listEducation',
      fields: [
        { key: 'degree', label: 'ডিগ্রি/পরীক্ষা', type: 'select', options: CV_LIBRARY.degrees, wide: false },
        { key: 'result', label: 'ফলাফল (GPA/Division)', type: 'text', wide: false },
        { key: 'institute', label: 'প্রতিষ্ঠানের নাম', type: 'text', wide: true },
        { key: 'year', label: 'পাসের সাল', type: 'text', wide: false },
        { key: 'board', label: 'বোর্ড/বিশ্ববিদ্যালয়', type: 'text', wide: false },
      ],
    },
    experience: {
      listId: 'listExperience',
      fields: [
        { key: 'position', label: 'পদবি', type: 'text', wide: false },
        { key: 'company', label: 'প্রতিষ্ঠান', type: 'text', wide: false },
        { key: 'duration', label: 'সময়কাল', type: 'text', wide: false, placeholder: 'যেমন: জানু ২০২২ - বর্তমান' },
        { key: 'description', label: 'দায়িত্ব সংক্ষেপে', type: 'textarea', wide: true },
      ],
    },
    language: {
      listId: 'listLanguage',
      fields: [
        { key: 'name', label: 'ভাষা', type: 'text', wide: false },
        { key: 'level', label: 'দক্ষতা', type: 'select', options: CV_LIBRARY.languageLevels, wide: false },
      ],
    },
    certification: {
      listId: 'listCertification',
      fields: [
        { key: 'title', label: 'কোর্স/সার্টিফিকেট', type: 'text', wide: false },
        { key: 'issuer', label: 'প্রদানকারী', type: 'text', wide: false },
        { key: 'year', label: 'সাল', type: 'text', wide: false },
      ],
    },
    reference: {
      listId: 'listReference',
      fields: [
        { key: 'name', label: 'নাম', type: 'text', wide: false },
        { key: 'position', label: 'পদবি ও প্রতিষ্ঠান', type: 'text', wide: false },
        { key: 'contact', label: 'ফোন/ইমেইল', type: 'text', wide: true },
      ],
    },
  };

  const stateKeyByType = {
    education: 'education', experience: 'experience', language: 'languages',
    certification: 'certifications', reference: 'references',
  };

  function renderRepeatList(type) {
    const cfg = repeatConfigs[type];
    const stateKey = stateKeyByType[type];
    const container = $(cfg.listId);
    container.innerHTML = '';
    state[stateKey].forEach((row) => {
      const rowEl = document.createElement('div');
      rowEl.className = 'repeat-row';
      cfg.fields.forEach((f) => {
        const wrap = document.createElement('label');
        wrap.className = 'field' + (f.wide ? ' field-wide' : '');
        const span = document.createElement('span');
        span.textContent = f.label;
        wrap.appendChild(span);
        let input;
        if (f.type === 'select') {
          input = document.createElement('select');
          const blank = document.createElement('option');
          blank.value = ''; blank.textContent = '—';
          input.appendChild(blank);
          f.options.forEach((o) => {
            const opt = document.createElement('option');
            opt.value = o; opt.textContent = o;
            input.appendChild(opt);
          });
        } else if (f.type === 'textarea') {
          input = document.createElement('textarea');
          input.rows = 2;
        } else {
          input = document.createElement('input');
          input.type = 'text';
          if (f.placeholder) input.placeholder = f.placeholder;
        }
        input.value = row[f.key] || '';
        input.addEventListener('input', () => {
          row[f.key] = input.value;
          persistAndRender(true);
        });
        wrap.appendChild(input);
        rowEl.appendChild(wrap);
      });
      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'btn-remove-row row-remove';
      removeBtn.textContent = '✕ মুছুন';
      removeBtn.addEventListener('click', () => {
        state[stateKey] = state[stateKey].filter((r) => r._id !== row._id);
        renderRepeatList(type);
        renderPreview();
        saveState();
      });
      rowEl.appendChild(removeBtn);
      container.appendChild(rowEl);
    });
  }

  function initRepeatSections() {
    Object.keys(repeatConfigs).forEach((type) => renderRepeatList(type));
    document.querySelectorAll('[data-add]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const type = btn.getAttribute('data-add');
        const stateKey = stateKeyByType[type];
        state[stateKey].push({ _id: uid() });
        renderRepeatList(type);
        saveState();
      });
    });
  }

  // ---------- skills ----------
  function addSkill(name) {
    const trimmed = name.trim();
    if (!trimmed) return;
    if (!state.skills.includes(trimmed)) state.skills.push(trimmed);
  }

  function renderSkills() {
    const cat = CV_LIBRARY.getCategory(state.category);
    const suggestWrap = $('skillChips');
    suggestWrap.innerHTML = '';
    if (cat) {
      cat.skills.forEach((s) => {
        const chip = document.createElement('button');
        chip.type = 'button';
        chip.className = 'chip' + (state.skills.includes(s) ? ' is-selected' : '');
        chip.textContent = s;
        chip.addEventListener('click', () => {
          if (state.skills.includes(s)) {
            state.skills = state.skills.filter((x) => x !== s);
          } else {
            addSkill(s);
          }
          persistAndRender(true);
        });
        suggestWrap.appendChild(chip);
      });
    }

    const selectedWrap = $('skillSelected');
    selectedWrap.innerHTML = '';
    state.skills.forEach((s) => {
      const chip = document.createElement('span');
      chip.className = 'chip';
      chip.textContent = s + ' ';
      const rm = document.createElement('button');
      rm.type = 'button';
      rm.textContent = '✕';
      rm.addEventListener('click', () => {
        state.skills = state.skills.filter((x) => x !== s);
        persistAndRender(true);
      });
      chip.appendChild(rm);
      selectedWrap.appendChild(chip);
    });
  }

  function initSkillInput() {
    const input = $('fSkillInput');
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        addSkill(input.value);
        input.value = '';
        persistAndRender(true);
      }
    });
  }

  // ---------- reference on-request checkbox ----------
  function initRefCheckbox() {
    const cb = $('fRefOnRequest');
    cb.checked = !!state.refOnRequest;
    cb.addEventListener('change', () => {
      state.refOnRequest = cb.checked;
      persistAndRender();
    });
  }

  // ---------- template picker ----------
  function initTemplatePicker() {
    const picker = $('templatePicker');
    picker.querySelectorAll('.tpl-swatch').forEach((btn) => {
      if (btn.dataset.template === state.template) btn.classList.add('is-active');
      btn.addEventListener('click', () => {
        state.template = btn.dataset.template;
        picker.querySelectorAll('.tpl-swatch').forEach((b) => b.classList.toggle('is-active', b === btn));
        persistAndRender();
      });
    });
  }

  // ---------- mobile tabs ----------
  function initMobileTabs() {
    document.querySelectorAll('.tab-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach((b) => b.classList.toggle('is-active', b === btn));
        const tab = btn.getAttribute('data-tab');
        $('panelForm').classList.toggle('is-hidden', tab !== 'form');
        $('panelPreview').classList.toggle('is-hidden', tab !== 'preview');
      });
    });
  }

  // ---------- clear all ----------
  function initClearButton() {
    $('btnClear').addEventListener('click', () => {
      if (!confirm('সব তথ্য মুছে ফেলা হবে, নিশ্চিত?')) return;
      state = defaultState();
      localStorage.removeItem(STORAGE_KEY);
      location.reload();
    });
  }

  // ---------- preview rendering ----------
  function esc(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
  }

  function renderPreview() {
    const el = $('cvPreview');
    el.className = 'cv-page tpl-' + state.template;

    const hasAnything = state.name || state.objective || state.education.length || state.experience.length;
    if (!hasAnything) {
      el.innerHTML = '<div class="cv-empty-hint">বাম পাশে ফর্ম পূরণ করলে এখানে আপনার সিভি লাইভ দেখতে পাবেন।</div>';
      return;
    }

    let html = '<div class="cv-header">';
    if (state.photo) html += `<img class="cv-photo" src="${state.photo}" alt="" />`;
    html += '<div>';
    html += `<p class="cv-name">${esc(state.name) || 'আপনার নাম'}</p>`;
    if (state.title) html += `<p class="cv-title">${esc(state.title)}</p>`;
    const contacts = [state.phone, state.email, state.address].filter(Boolean).map(esc);
    if (contacts.length) html += `<div class="cv-contact">${contacts.map((c) => `<span>${c}</span>`).join('')}</div>`;
    html += '</div></div>';

    if (state.objective) {
      html += `<div class="cv-section-title">ক্যারিয়ার অবজেক্টিভ</div><p class="cv-para">${esc(state.objective)}</p>`;
    }

    if (state.education.length) {
      html += '<div class="cv-section-title">শিক্ষাগত যোগ্যতা</div>';
      state.education.forEach((r) => {
        if (!r.degree && !r.institute) return;
        html += `<div class="cv-item"><div class="cv-item-title">${esc(r.degree)}${r.result ? ' — ' + esc(r.result) : ''}</div>`;
        html += `<div class="cv-item-sub">${[r.institute, r.board, r.year].filter(Boolean).map(esc).join(', ')}</div></div>`;
      });
    }

    if (state.experience.length) {
      html += '<div class="cv-section-title">অভিজ্ঞতা</div>';
      state.experience.forEach((r) => {
        if (!r.position && !r.company) return;
        html += `<div class="cv-item"><div class="cv-item-title">${esc(r.position)}${r.company ? ' — ' + esc(r.company) : ''}</div>`;
        if (r.duration) html += `<div class="cv-item-sub">${esc(r.duration)}</div>`;
        if (r.description) html += `<p class="cv-para">${esc(r.description)}</p>`;
        html += '</div>';
      });
    }

    if (state.skills.length) {
      html += `<div class="cv-section-title">দক্ষতা</div><div class="cv-skill-list">${state.skills.map((s) => `<span class="cv-skill-pill">${esc(s)}</span>`).join('')}</div>`;
    }

    if (state.languages.length) {
      const rows = state.languages.filter((r) => r.name);
      if (rows.length) {
        html += `<div class="cv-section-title">ভাষা দক্ষতা</div><div class="cv-lang-list">${rows.map((r) => `<span class="cv-skill-pill">${esc(r.name)}${r.level ? ' (' + esc(r.level) + ')' : ''}</span>`).join('')}</div>`;
      }
    }

    if (state.certifications.length) {
      const rows = state.certifications.filter((r) => r.title);
      if (rows.length) {
        html += '<div class="cv-section-title">প্রশিক্ষণ / সার্টিফিকেট</div>';
        rows.forEach((r) => {
          html += `<div class="cv-item"><div class="cv-item-title">${esc(r.title)}</div><div class="cv-item-sub">${[r.issuer, r.year].filter(Boolean).map(esc).join(', ')}</div></div>`;
        });
      }
    }

    if (state.refOnRequest) {
      html += '<div class="cv-section-title">রেফারেন্স</div><p class="cv-para">অনুরোধ সাপেক্ষে প্রদান করা হবে।</p>';
    } else if (state.references.length) {
      const rows = state.references.filter((r) => r.name);
      if (rows.length) {
        html += '<div class="cv-section-title">রেফারেন্স</div>';
        rows.forEach((r) => {
          html += `<div class="cv-item"><div class="cv-item-title">${esc(r.name)}</div><div class="cv-item-sub">${[r.position, r.contact].filter(Boolean).map(esc).join(' • ')}</div></div>`;
        });
      }
    }

    el.innerHTML = html;
  }

  function persistAndRender(skipRepeatRedraw) {
    saveState();
    renderSkills();
    renderPreview();
  }

  // ---------- PDF generation & share ----------
  async function generatePdfBlob() {
    const { jsPDF } = window.jspdf;
    const source = $('cvPreview');
    const canvas = await html2canvas(source, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
    const imgData = canvas.toDataURL('image/jpeg', 0.95);

    const pdf = new jsPDF({ unit: 'mm', format: 'a4', orientation: 'portrait' });
    const pageW = pdf.internal.pageSize.getWidth();
    const pageH = pdf.internal.pageSize.getHeight();
    const imgW = pageW;
    const imgH = (canvas.height * imgW) / canvas.width;

    if (imgH <= pageH) {
      pdf.addImage(imgData, 'JPEG', 0, 0, imgW, imgH);
    } else {
      // একাধিক পৃষ্ঠায় ভাগ করা — লম্বা সিভির জন্য
      let heightLeft = imgH;
      let position = 0;
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

  function fileNameForCv() {
    const base = (state.name || 'cv').trim().replace(/\s+/g, '_');
    return `${base}_CV.pdf`;
  }

  async function handleDownload() {
    if (!state.name) { showToast('⚠️ প্রথমে নাম লিখুন'); return; }
    showToast('⏳ পিডিএফ তৈরি হচ্ছে...');
    try {
      const blob = await generatePdfBlob();
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = fileNameForCv();
      document.body.appendChild(a);
      a.click();
      a.remove();
      setTimeout(() => URL.revokeObjectURL(url), 4000);
      showToast('✅ ডাউনলোড সম্পন্ন');
    } catch (e) {
      showToast('❌ পিডিএফ তৈরি করা যায়নি');
    }
  }

  async function handleShare() {
    if (!state.name) { showToast('⚠️ প্রথমে নাম লিখুন'); return; }
    showToast('⏳ শেয়ারের জন্য প্রস্তুত করা হচ্ছে...');
    try {
      const blob = await generatePdfBlob();
      const file = new File([blob], fileNameForCv(), { type: 'application/pdf' });

      if (navigator.canShare && navigator.canShare({ files: [file] })) {
        await navigator.share({
          files: [file],
          title: 'আমার সিভি',
          text: `${state.name} এর সিভি`,
        });
        showToast('✅ শেয়ার হয়েছে');
      } else {
        await handleDownload();
        showToast('ℹ️ এই ব্রাউজারে সরাসরি শেয়ার সাপোর্ট নেই, তাই ডাউনলোড হয়েছে — ফাইলটি সরাসরি পাঠিয়ে দিন');
      }
    } catch (e) {
      if (e && e.name === 'AbortError') return;
      showToast('❌ শেয়ার করা যায়নি');
    }
  }

  // ---------- init ----------
  function init() {
    initCategorySelect();
    initSimpleFields();
    initPhoto();
    initRepeatSections();
    initSkillInput();
    initRefCheckbox();
    initTemplatePicker();
    initMobileTabs();
    initClearButton();
    renderSkills();
    renderPreview();

    $('btnDownload').addEventListener('click', handleDownload);
    $('btnShare').addEventListener('click', handleShare);

    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('sw.js').catch(() => {});
    }
  }

  document.addEventListener('DOMContentLoaded', init);
})();
