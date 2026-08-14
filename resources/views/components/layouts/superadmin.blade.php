<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>সুপার এডমিন কন্ট্রোল প্যানেল — EDUTION</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tiro+Bangla:ital@0;1&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>
@livewireStyles
<style>
  :root{
    --ink-bg:#1B181E; --ink-bg-deep:#0E0D10;
    --gold:#C9A227; --gold-light:#E7C767;
    --panel-line:rgba(231,199,103,.14);
    --paper:#F7F2E5; --paper-deep:#EFE7D3; --card:#FFFFFF;
    --ink:#2A2320; --ink-muted:#7A7061; --ink-soft:#AFA593;
    --line:rgba(42,35,32,.09);
    --good:#2F6E52; --bad:#A6412E; --warn:#C9A227; --info:#35528F;
    --sidebar-w:270px; --sidebar-w-collapsed:76px;
  }
  *{box-sizing:border-box;}
  html,body{margin:0;padding:0;}
  body{ font-family:'Hind Siliguri',sans-serif; background:var(--paper); color:var(--ink); min-height:100vh; }
  ::-webkit-scrollbar{width:8px;height:8px;} ::-webkit-scrollbar-thumb{background:rgba(0,0,0,.15);border-radius:8px;}
  a{color:inherit;text-decoration:none;} button{font-family:inherit;}

  .app{ display:grid; grid-template-columns:var(--sidebar-w) 1fr; min-height:100vh; transition:grid-template-columns .22s ease; }
  .app.collapsed{ grid-template-columns:var(--sidebar-w-collapsed) 1fr; }

  .sidebar{ background:linear-gradient(175deg, var(--ink-bg) 0%, var(--ink-bg-deep) 100%); color:#EDE7DA; display:flex; flex-direction:column; position:sticky; top:0; height:100vh; overflow:hidden; }
  .sidebar-head{ display:flex; align-items:center; gap:11px; padding:20px 18px 16px; border-bottom:1px solid var(--panel-line); flex-shrink:0; }
  .sidebar-emblem{ width:36px;height:36px;border-radius:10px; background:rgba(201,162,39,.14); border:1.5px solid rgba(201,162,39,.4); display:flex;align-items:center;justify-content:center;flex-shrink:0; }
  .sidebar-emblem svg{width:18px;height:18px; color:var(--gold-light);}
  .sidebar-brand{ overflow:hidden; white-space:nowrap; }
  .sidebar-brand .name{ font-family:'Tiro Bangla',serif; font-size:17px; color:var(--gold-light); line-height:1.1; }
  .sidebar-brand .inst{ font-size:10.5px; color:rgba(237,231,218,.5); margin-top:2px; letter-spacing:.04em; }
  .nav-scroll{ flex:1; overflow-y:auto; overflow-x:hidden; padding:14px 10px 10px; }
  .nav-item{ margin-bottom:2px; }
  .nav-btn{ width:100%; display:flex; align-items:center; gap:11px; padding:11px 11px; background:none; border:0; cursor:pointer; border-radius:10px; color:rgba(237,231,218,.72); font-size:13.7px; font-weight:500; text-align:right; position:relative; transition:background .15s ease, color .15s ease; }
  .nav-btn .ic{ width:18px;height:18px; flex-shrink:0; display:flex; }
  .nav-btn .lbl{ flex:1; text-align:right; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .nav-btn .cnt{ font-size:10.5px; font-weight:700; background:rgba(210,96,74,.25); color:#E0A797; padding:2px 7px; border-radius:20px; }
  .nav-btn:hover{ background:rgba(255,255,255,.05); color:#fff; }
  .nav-item.active .nav-btn{ background:rgba(201,162,39,.14); color:var(--gold-light); }
  .nav-item.active .nav-btn::before{ content:""; position:absolute; right:-10px; top:8px; bottom:8px; width:3px; background:var(--gold); border-radius:3px 0 0 3px; }
  .sidebar-foot{ flex-shrink:0; border-top:1px solid var(--panel-line); padding:12px; }
  .collapse-btn{ width:100%; display:flex; align-items:center; gap:10px; background:none;border:0;cursor:pointer; color:rgba(237,231,218,.6); padding:9px 10px; border-radius:9px; font-size:12.5px; }
  .collapse-btn:hover{ background:rgba(255,255,255,.06); color:#fff; }
  .collapse-btn svg{ width:16px;height:16px; flex-shrink:0; transition:transform .2s ease;}
  .app.collapsed .collapse-btn svg{ transform:rotate(180deg); }
  .app.collapsed .sidebar-brand, .app.collapsed .nav-btn .lbl, .app.collapsed .nav-btn .cnt, .app.collapsed .collapse-btn span{ display:none; }
  .app.collapsed .sidebar-head{ justify-content:center; padding:20px 0 16px; }
  .app.collapsed .nav-btn{ justify-content:center; padding:11px; }
  .app.collapsed .collapse-btn{ justify-content:center; }

  .main{ min-width:0; }
  .topbar{ position:sticky; top:0; z-index:20; display:flex; align-items:center; gap:14px; background:rgba(247,242,229,.92); backdrop-filter:blur(6px); border-bottom:1px solid var(--line); padding:15px 26px; }
  .menu-toggle{ display:none; background:#fff;border:1px solid var(--line);border-radius:9px; width:36px;height:36px;align-items:center;justify-content:center;cursor:pointer; }
  .breadcrumb{ flex:1; min-width:0; }
  .breadcrumb .path{ font-size:11.5px; color:var(--ink-soft); margin-bottom:1px; }
  .breadcrumb h1{ font-family:'Tiro Bangla',serif; font-size:20px; margin:0; color:var(--ink); }
  .topbar-actions{ display:flex; align-items:center; gap:10px; flex-shrink:0; }
  .icon-btn{ width:38px;height:38px; border-radius:10px; background:#fff; border:1px solid var(--line); display:flex;align-items:center;justify-content:center; cursor:pointer; position:relative; flex-shrink:0; }
  .icon-btn svg{ width:17px;height:17px; color:var(--ink-muted); }
  .profile-chip{ display:flex; align-items:center; gap:9px; background:#fff; border:1px solid var(--line); border-radius:10px; padding:5px 12px 5px 5px; }
  .avatar{ width:30px;height:30px;border-radius:50%; background:linear-gradient(135deg,var(--gold-light),var(--gold)); display:flex;align-items:center;justify-content:center; font-weight:700;font-size:12.5px;color:var(--ink-bg-deep); flex-shrink:0; }
  .profile-chip .who{ font-size:13px; line-height:1.25; text-align:right; white-space:nowrap;}
  .profile-chip .who .role{ font-size:11px; color:var(--ink-soft); }
  .content{ padding:22px 26px 70px; }

  .btn-primary{ display:inline-flex; align-items:center; gap:8px; border:0; border-radius:11px; padding:11px 18px; background:linear-gradient(180deg, var(--gold-light), var(--gold)); color:#3E1120; font-weight:700; font-size:13.5px; cursor:pointer; box-shadow:0 8px 18px -8px rgba(201,162,39,.55); white-space:nowrap; }
  .btn-ghost{ display:inline-flex; align-items:center; gap:8px; border:1.5px solid var(--line); border-radius:11px; padding:10px 16px; background:#fff; color:var(--ink-muted); font-weight:700; font-size:13px; cursor:pointer; white-space:nowrap; }
  .btn-ghost:hover{ border-color:var(--ink-soft); color:var(--ink); }
  .btn-primary svg,.btn-ghost svg{ width:15px;height:15px; }

  .page-head{ display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:20px; flex-wrap:wrap; }
  .page-head h2{ font-family:'Tiro Bangla',serif; font-size:23px; margin:0 0 4px; }
  .page-head p{ margin:0; font-size:13px; color:var(--ink-muted); }

  .stat-strip{ display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; margin-bottom:20px; }
  .stat-chip{ background:var(--card); border:1px solid var(--line); border-radius:14px; padding:15px 17px; display:flex; align-items:center; gap:12px; }
  .stat-chip .sic{ width:38px;height:38px;border-radius:10px; display:flex;align-items:center;justify-content:center; background:color-mix(in srgb, var(--accent) 14%, white); flex-shrink:0; }
  .stat-chip .sic svg{ width:18px;height:18px; color:var(--accent); }
  .stat-chip .sv{ font-size:20px; font-weight:700; line-height:1.1; }
  .stat-chip .sl{ font-size:11.5px; color:var(--ink-muted); margin-top:2px; }
  .stat-chip .trend{ font-size:11px; margin-top:4px; }
  .stat-chip .trend.up{ color:var(--good); } .stat-chip .trend.down{ color:var(--bad); }

  .card{ background:var(--card); border:1px solid var(--line); border-radius:16px; padding:20px 22px; margin-bottom:16px; }
  .card h3{ font-family:'Tiro Bangla',serif; font-size:16.5px; margin:0 0 4px; }
  .card .sub{ font-size:12.5px; color:var(--ink-muted); margin:0 0 14px; }
  .charts-grid{ display:grid; grid-template-columns:1.5fr 1fr; gap:16px; margin-bottom:16px; }
  .chart-box{ position:relative; height:220px; }
  .legend{ display:flex; gap:12px; flex-wrap:wrap; margin-top:10px; justify-content:center; }
  .legend span{ display:flex; align-items:center; gap:5px; font-size:11.5px; color:var(--ink-muted); }
  .legend i{ width:8px;height:8px;border-radius:50%; display:inline-block; }

  .filter-card{ background:var(--card); border:1px solid var(--line); border-radius:15px; padding:14px 16px; margin-bottom:16px; display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; }
  .f-field{ display:flex; flex-direction:column; gap:5px; min-width:130px; }
  .f-field label{ font-size:11px; font-weight:700; color:var(--ink-muted); }
  .f-field select, .f-field input{ border:1.4px solid var(--line); border-radius:9px; padding:8px 10px; font-size:12.5px; font-family:inherit; background:#fff; color:var(--ink); outline:0; }
  .f-search{ flex:1; min-width:180px; }

  .table-card{ background:var(--card); border:1px solid var(--line); border-radius:16px; overflow:hidden; }
  table{ width:100%; border-collapse:collapse; font-size:13px; }
  thead th{ text-align:right; font-size:11.3px; color:var(--ink-muted); font-weight:700; padding:12px 12px; background:var(--paper-deep); border-bottom:1px solid var(--line); white-space:nowrap; }
  tbody td{ padding:11px 12px; border-bottom:1px solid var(--line); vertical-align:middle; }
  tbody tr:last-child td{ border-bottom:0; }
  tbody tr:hover{ background:rgba(201,162,39,.04); }
  .inst-cell{ display:flex; align-items:center; gap:10px; }
  .inst-cell .ini{ width:34px;height:34px;border-radius:10px; font-weight:700; font-size:12.5px; display:flex;align-items:center;justify-content:center; flex-shrink:0; color:#fff; }
  .inst-cell .nm{ font-weight:600; }
  .inst-cell .sub{ font-size:11px; color:var(--ink-soft); }
  .tag{ display:inline-block; font-size:10.5px; font-weight:700; padding:3px 9px; border-radius:20px; }
  .tag.good{ background:color-mix(in srgb, var(--good) 14%, white); color:var(--good); }
  .tag.bad{ background:color-mix(in srgb, var(--bad) 14%, white); color:var(--bad); }
  .tag.gold{ background:color-mix(in srgb, var(--gold) 18%, white); color:#8a6c17; }
  .tag.info{ background:color-mix(in srgb, var(--info) 14%, white); color:var(--info); }
  .manage-btn{ border:1.4px solid var(--line); background:#fff; border-radius:9px; padding:7px 14px; font-size:11.5px; font-weight:700; color:var(--ink-muted); cursor:pointer; }
  .manage-btn:hover{ border-color:var(--gold); color:var(--ink); }
  .table-foot{ display:flex; align-items:center; justify-content:space-between; padding:13px 18px; font-size:12.5px; color:var(--ink-muted); flex-wrap:wrap; gap:10px; }

  .modal-overlay{ position:fixed; inset:0; background:rgba(20,15,10,.55); z-index:200; display:none; align-items:flex-start; justify-content:center; padding:40px 16px; overflow-y:auto; }
  .modal-overlay.open{ display:flex; }
  .modal{ background:var(--card); border-radius:20px; width:100%; max-width:640px; overflow:hidden; box-shadow:0 30px 70px -20px rgba(0,0,0,.4); }
  .modal-head{ background:linear-gradient(120deg,#2A2730,var(--ink-bg-deep)); color:#EDE7DA; padding:22px 26px; display:flex; align-items:center; justify-content:space-between; gap:14px; }
  .modal-head-left{ display:flex; align-items:center; gap:12px; }
  .modal-ini{ width:44px;height:44px;border-radius:12px; background:rgba(201,162,39,.18); display:flex;align-items:center;justify-content:center; font-family:'Tiro Bangla',serif; font-size:18px; color:var(--gold-light); flex-shrink:0; }
  .modal-head h3{ font-family:'Tiro Bangla',serif; font-size:18px; margin:0 0 2px; }
  .modal-head .sub{ font-size:11.5px; color:rgba(237,231,218,.6); }
  .modal-close{ background:rgba(255,255,255,.08); border:0; width:32px;height:32px;border-radius:9px; color:#EDE7DA; cursor:pointer; display:flex;align-items:center;justify-content:center; flex-shrink:0; font-size:16px; }
  .modal-body{ padding:24px 26px 26px; max-height:70vh; overflow-y:auto; }

  .modal-sec{ margin-bottom:22px; }
  .modal-sec h4{ font-size:13px; font-weight:700; color:var(--ink); margin:0 0 12px; display:flex; align-items:center; gap:7px; }
  .modal-sec h4 svg{ width:15px;height:15px; color:var(--gold); }

  .status-toggle-row{ display:flex; align-items:center; justify-content:space-between; background:var(--paper-deep); border-radius:12px; padding:13px 15px; }
  .status-toggle-row .lbl{ font-size:13px; font-weight:700; }
  .status-toggle-row .desc{ font-size:11.5px; color:var(--ink-muted); margin-top:2px; }
  .switch{ position:relative; width:44px; height:25px; flex-shrink:0; display:inline-block; }
  .switch input{ opacity:0; width:0; height:0; position:absolute; }
  .switch-track{ position:absolute; inset:0; background:#D8CFB8; border-radius:20px; cursor:pointer; transition:background .2s ease; }
  .switch-track::before{ content:""; position:absolute; width:19px;height:19px; border-radius:50%; background:#fff; top:3px; right:3px; transition:right .2s ease; box-shadow:0 1px 3px rgba(0,0,0,.25); }
  .switch input:checked + .switch-track{ background:var(--good); }
  .switch input:checked + .switch-track::before{ right:22px; }

  .plan-row{ display:grid; grid-template-columns:1fr 1fr; gap:10px; }
  .plan-row select, .plan-row input{ border:1.4px solid var(--line); border-radius:9px; padding:9px 11px; font-size:13px; font-family:inherit; color:var(--ink); outline:0; }

  .module-grid{ display:grid; grid-template-columns:1fr 1fr; gap:8px; }
  .module-item{ display:flex; align-items:center; justify-content:space-between; background:var(--paper-deep); border-radius:10px; padding:10px 12px; }
  .module-item .lbl{ font-size:12.5px; font-weight:600; display:flex; align-items:center; gap:8px; }
  .switch.small{ width:36px; height:21px; }
  .switch.small .switch-track::before{ width:15px;height:15px; top:3px; right:3px; }
  .switch.small input:checked + .switch-track::before{ right:18px; }

  .modal-foot{ display:flex; justify-content:space-between; align-items:center; padding:16px 26px; border-top:1px solid var(--line); background:var(--paper-deep); }
  .danger-link{ color:var(--bad); font-size:12.5px; font-weight:700; background:none; border:0; cursor:pointer; display:flex; align-items:center; gap:6px; }

  .split-grid{ display:grid; grid-template-columns:1fr 330px; gap:20px; align-items:start; }
  .compose-card{ position:sticky; top:86px; background:var(--card); border:1px solid var(--line); border-radius:18px; padding:20px 20px 18px; }
  .compose-head{ display:flex; align-items:center; gap:10px; margin-bottom:16px; }
  .compose-ic{ width:36px;height:36px;border-radius:10px; background:rgba(201,162,39,.12); color:var(--gold); display:flex;align-items:center;justify-content:center; flex-shrink:0; }
  .compose-head .t1{ font-size:15px; font-weight:700; font-family:'Tiro Bangla',serif; }
  .compose-head .t2{ font-size:11.5px; color:var(--ink-soft); }
  .field{ margin-bottom:14px; }
  .field label{ display:block; font-size:11.5px; font-weight:700; color:var(--ink-muted); margin-bottom:6px; }
  .field select, .field input, .field textarea{ width:100%; border:1.4px solid var(--line); border-radius:9px; padding:9px 11px; font-size:13px; font-family:inherit; color:var(--ink); outline:0; }
  .field select:focus, .field input:focus, .field textarea:focus{ border-color:var(--gold); }
  .field textarea{ resize:vertical; min-height:90px; }
  .field .err{ font-size:11px; color:var(--bad); margin-top:5px; }
  .aud-select{ display:flex; flex-wrap:wrap; gap:7px; }
  .aud-opt{ display:flex; align-items:center; gap:6px; border:1.5px solid var(--line); border-radius:9px; padding:7px 11px; font-size:12px; font-weight:600; color:var(--ink-muted); cursor:pointer; }
  .aud-opt input{ width:14px;height:14px; accent-color:var(--gold); }
  .aud-opt.checked{ border-color:var(--gold); background:rgba(201,162,39,.08); color:var(--ink); }
  .btn-submit{ width:100%; border:0; border-radius:11px; padding:12px; background:linear-gradient(180deg, var(--gold-light), var(--gold)); color:#3E1120; font-weight:700; font-size:14px; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; box-shadow:0 8px 18px -8px rgba(201,162,39,.55); }

  .notice-card{ background:var(--card); border:1px solid var(--line); border-radius:14px; padding:16px 18px; margin-bottom:12px; }
  .notice-top{ display:flex; justify-content:space-between; gap:10px; margin-bottom:8px; }
  .notice-title{ font-size:14px; font-weight:700; }
  .notice-body{ font-size:12.5px; color:var(--ink-muted); line-height:1.6; margin-bottom:10px; }
  .notice-meta{ display:flex; justify-content:space-between; font-size:11px; color:var(--ink-soft); padding-top:8px; border-top:1px dashed var(--line); }

  .ticket-row{ background:var(--card); border:1px solid var(--line); border-radius:14px; padding:15px 18px; margin-bottom:10px; cursor:pointer; }
  .ticket-row:hover{ border-color:var(--ink-soft); }
  .ticket-row.active{ border-color:var(--gold); background:rgba(201,162,39,.05); }
  .ticket-top{ display:flex; justify-content:space-between; gap:10px; margin-bottom:6px; }
  .ticket-title{ font-size:13.5px; font-weight:700; }
  .ticket-meta{ display:flex; gap:12px; font-size:11.5px; color:var(--ink-soft); flex-wrap:wrap; }
  .ticket-priority.high{ color:var(--bad); font-weight:700; }
  .ticket-priority.med{ color:var(--gold); font-weight:700; }
  .ticket-priority.low{ color:var(--good); font-weight:700; }

  .reply-panel{ position:sticky; top:86px; background:var(--card); border:1px solid var(--line); border-radius:18px; padding:20px; }
  .reply-thread{ display:flex; flex-direction:column; gap:12px; margin-bottom:16px; max-height:260px; overflow-y:auto; }
  .msg{ background:var(--paper-deep); border-radius:12px; padding:11px 13px; font-size:12.5px; }
  .msg.admin{ background:rgba(201,162,39,.1); }
  .msg .who{ font-weight:700; font-size:11.5px; margin-bottom:4px; }
  .msg .time{ font-size:10px; color:var(--ink-soft); margin-top:5px; }

  .toast{ position:fixed; bottom:24px; left:50%; transform:translateX(-50%) translateY(20px); background:var(--ink); color:#fff; padding:10px 18px; border-radius:10px; font-size:13px; opacity:0; pointer-events:none; transition:opacity .2s ease, transform .2s ease; z-index:300; }
  .toast.show{ opacity:1; transform:translateX(-50%) translateY(0); }

  @media (max-width:1080px){ .charts-grid, .split-grid{ grid-template-columns:1fr; } .compose-card, .reply-panel{ position:static; } }
  @media (max-width:860px){
    .app{ grid-template-columns:1fr; }
    .sidebar{ position:fixed; inset:0 auto 0 0; width:270px; z-index:50; transform:translateX(100%); transition:transform .25s ease; }
    .app.mobile-open .sidebar{ transform:translateX(0); }
    .app.collapsed{ grid-template-columns:1fr; }
    .menu-toggle{ display:flex; }
    .content{ padding:16px 14px 60px; }
    .topbar{ padding:12px 14px; }
    .table-card{ overflow-x:auto; }
    table{ min-width:680px; }
    .module-grid{ grid-template-columns:1fr; }
  }
</style>
</head>
<body>

{{ $slot }}

<div class="toast" id="toast"></div>

<script>
  document.addEventListener('livewire:init', () => {
    Livewire.on('toast', (event) => {
      const msg = Array.isArray(event) ? event[0]?.message : event.message;
      const t = document.getElementById('toast');
      t.textContent = msg || 'সংরক্ষণ করা হয়েছে';
      t.classList.add('show');
      clearTimeout(window.__toastTimer);
      window.__toastTimer = setTimeout(() => t.classList.remove('show'), 2500);
    });
  });
</script>

@livewireScripts
</body>
</html>
