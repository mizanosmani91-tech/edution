<div class="app" id="app" x-data="{ collapsed: false, mobileOpen: false }" :class="{ collapsed: collapsed, 'mobile-open': mobileOpen }">

  <div class="mobile-backdrop" @click="mobileOpen = false"></div>

  <aside class="sidebar">
    <div class="sidebar-head">
      <div class="sidebar-emblem"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 6.5c2.8-1.4 5.6-1.4 8 0v11c-2.4-1.4-5.2-1.4-8 0v-11Z"/><path d="M20 6.5c-2.8-1.4-5.6-1.4-8 0v11c2.4-1.4 5.2-1.4 8 0v-11Z"/></svg></div>
      <div class="sidebar-brand"><div class="name">EDUTION</div><div class="inst">SUPER ADMIN CONSOLE</div></div>
    </div>
    <nav class="nav-scroll">
      <div class="nav-item {{ $activeSection === 'overview' ? 'active' : '' }}"><button class="nav-btn" wire:click="setSection('overview')" type="button"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 12 12 4l8 8"/><path d="M6 10v9h12v-9"/></svg></span><span class="lbl">ওভারভিউ</span></button></div>
      <div class="nav-item {{ $activeSection === 'applications' ? 'active' : '' }}"><button class="nav-btn" wire:click="setSection('applications')" type="button"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 5v14M5 12h14"/></svg></span><span class="lbl">নতুন আবেদন</span>@if($stats['pendingInstitutions'] > 0)<span class="cnt">{{ $stats['pendingInstitutions'] }}</span>@endif</button></div>
      <div class="nav-item {{ $activeSection === 'institutions' ? 'active' : '' }}"><button class="nav-btn" wire:click="setSection('institutions')" type="button"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A2 2 0 0 1 6 4h13v14H6a2 2 0 0 0-2 2V5.5Z"/></svg></span><span class="lbl">প্রতিষ্ঠানসমূহ</span></button></div>
      <div class="nav-item {{ $activeSection === 'billing' ? 'active' : '' }}"><button class="nav-btn" wire:click="setSection('billing')" type="button"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/><path d="M3.5 11h17"/></svg></span><span class="lbl">প্যাকেজ ও বিলিং</span></button></div>
      <div class="nav-item {{ $activeSection === 'notices' ? 'active' : '' }}"><button class="nav-btn" wire:click="setSection('notices')" type="button"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5h13l3 4-3 4H4z"/><path d="M6 13v6"/></svg></span><span class="lbl">নোটিশ ও ঘোষণা</span></button></div>
      <div class="nav-item {{ $activeSection === 'support' ? 'active' : '' }}"><button class="nav-btn" wire:click="setSection('support')" type="button"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 15a5 5 0 0 0 5-5V8a5 5 0 0 0-10 0v2a5 5 0 0 0 5 5Z"/><path d="M8 21h8M12 15v6"/></svg></span><span class="lbl">সাপোর্ট টিকেট</span>@if($stats['openTickets'] > 0)<span class="cnt">{{ $stats['openTickets'] }}</span>@endif</button></div>
      <div class="nav-item {{ $activeSection === 'trash' ? 'active' : '' }}"><button class="nav-btn" wire:click="setSection('trash')" type="button"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/></svg></span><span class="lbl">রিসাইকেল বিন</span>@if($stats['trashedInstitutions'] > 0)<span class="cnt">{{ $stats['trashedInstitutions'] }}</span>@endif</button></div>
      <div class="nav-item {{ $activeSection === 'demo-access' ? 'active' : '' }}"><button class="nav-btn" wire:click="setSection('demo-access')" type="button"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 15v-3.5M12 8h.01"/><circle cx="12" cy="12" r="9"/></svg></span><span class="lbl">ডেমো এক্সেস</span>@if($stats['pendingDemoRequests'] > 0)<span class="cnt">{{ $stats['pendingDemoRequests'] }}</span>@endif</button></div>
      <div class="nav-item {{ $activeSection === 'system-health' ? 'active' : '' }}"><button class="nav-btn" wire:click="setSection('system-health')" type="button"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></span><span class="lbl">সিস্টেম হেলথ</span></button></div>
      <div class="nav-item {{ $activeSection === 'settings' ? 'active' : '' }}"><button class="nav-btn" wire:click="setSection('settings')" type="button"><span class="ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="3.2"/><path d="M19.4 13.5a7.7 7.7 0 0 0 0-3l1.9-1.4-2-3.4-2.2.8a7.6 7.6 0 0 0-2.6-1.5L14 2.5h-4l-.5 2.5a7.6 7.6 0 0 0-2.6 1.5l-2.2-.8-2 3.4L4.6 10.5a7.7 7.7 0 0 0 0 3L2.7 14.9l2 3.4 2.2-.8c.77.66 1.65 1.17 2.6 1.5l.5 2.5h4l.5-2.5a7.6 7.6 0 0 0 2.6-1.5l2.2.8 2-3.4-1.9-1.4Z"/></svg></span><span class="lbl">সেটিংস</span></button></div>
    </nav>
    <div class="sidebar-foot"><button class="collapse-btn" @click="collapsed = !collapsed" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M15 6l-6 6 6 6"/></svg><span>সাইডবার সংকুচিত করুন</span></button></div>
  </aside>

  <main class="main">
    <div class="topbar">
      <button class="menu-toggle" @click="mobileOpen = !mobileOpen" type="button"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M4 12h16M4 17h16"/></svg></button>
      <div class="breadcrumb"><div class="path">সুপার এডমিন</div><h1>{{ ['overview'=>'ওভারভিউ','applications'=>'নতুন আবেদন','institutions'=>'প্রতিষ্ঠানসমূহ','billing'=>'প্যাকেজ ও বিলিং','notices'=>'নোটিশ ও ঘোষণা','support'=>'সাপোর্ট টিকেট','trash'=>'রিসাইকেল বিন','demo-access'=>'ডেমো এক্সেস','system-health'=>'সিস্টেম হেলথ','settings'=>'সেটিংস'][$activeSection] ?? '' }}</h1></div>
      <div class="topbar-actions">
        <button class="icon-btn" wire:click="setSection('applications')" type="button" title="নতুন প্রতিষ্ঠান আবেদন">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M18 8a6 6 0 1 0-12 0c0 5-2 6-2 6h16s-2-1-2-6"/><path d="M9.5 20a2.5 2.5 0 0 0 5 0"/></svg>
          @php $bellCount = $stats['pendingInstitutions'] + $stats['pendingPayments']; @endphp
          @if ($bellCount > 0)<span class="badge" style="position:absolute; top:-4px; right:-4px; background:var(--bad); color:#fff; font-size:9.5px; font-weight:700; width:16px;height:16px;border-radius:50%; display:flex;align-items:center;justify-content:center;">{{ $bellCount > 9 ? '9+' : $bellCount }}</span>@endif
        </button>
        <div class="profile-chip"><div class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</div><div class="who">{{ auth()->user()->name }}<div class="role">Platform Owner</div></div></div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="icon-btn" type="submit" title="লগআউট"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg></button></form>
      </div>
    </div>

    <div class="content">

      {{-- ============ OVERVIEW ============ --}}
      @if ($activeSection === 'overview')
        <div class="page-head"><div><h2>প্ল্যাটফর্ম ওভারভিউ</h2><p>সমস্ত প্রতিষ্ঠানের সার্বিক পরিসংখ্যান এক নজরে</p></div></div>

        <div class="stat-strip">
          <div class="stat-chip" style="--accent:var(--info)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5A2 2 0 0 1 6 4h13v14H6a2 2 0 0 0-2 2V5.5Z"/></svg></div><div><div class="sv">{{ number_format($stats['totalInstitutions']) }}</div><div class="sl">মোট প্রতিষ্ঠান</div></div></div>
          <div class="stat-chip" style="--accent:var(--gold)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div><div><div class="sv">{{ $stats['trialCount'] }}</div><div class="sl">সক্রিয় ট্রায়াল</div></div></div>
          <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/></svg></div><div><div class="sv">৳{{ number_format($stats['mrr']) }}</div><div class="sl">মাসিক রাজস্ব (MRR)</div></div></div>
          <div class="stat-chip" style="--accent:var(--bad)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 15a5 5 0 0 0 5-5V8a5 5 0 0 0-10 0v2a5 5 0 0 0 5 5Z"/></svg></div><div><div class="sv">{{ $stats['openTickets'] }}</div><div class="sl">খোলা সাপোর্ট টিকেট</div>@if($stats['urgentTickets'] > 0)<div class="trend down">{{ $stats['urgentTickets'] }} জরুরি</div>@endif</div></div>
        </div>

        <div class="charts-grid">
          <div class="card">
            <h3>প্রতিষ্ঠান বৃদ্ধির প্রবণতা</h3>
            <p class="sub">গত ৮ মাস — মোট নিবন্ধিত প্রতিষ্ঠান</p>
            <div class="chart-box" wire:ignore>
              <canvas x-init="
                if ($el.chartInstance) { $el.chartInstance.destroy(); }
                Chart.defaults.font.family = &quot;'Hind Siliguri', sans-serif&quot;; Chart.defaults.color = '#6B7280';
                $el.chartInstance = new Chart($el, { type:'line', data:{ labels: @js($growthMonths['labels']), datasets:[{ data: @js($growthMonths['data']), borderColor:'#F59E0B', backgroundColor:'rgba(245,158,11,.14)', tension:.4, fill:true, pointRadius:0, borderWidth:2.5 }] }, options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{ y:{grid:{color:'rgba(31,36,50,.06)'}}, x:{grid:{display:false}} } } });
              "></canvas>
            </div>
          </div>
          <div class="card">
            <h3>বিলিং ধরন বণ্টন</h3>
            <p class="sub">postpaid বনাম prepaid প্রতিষ্ঠান</p>
            <div class="chart-box" style="height:190px;" wire:ignore>
              <canvas x-init="
                if ($el.chartInstance) { $el.chartInstance.destroy(); }
                $el.chartInstance = new Chart($el, { type:'doughnut', data:{ labels:['পোস্টপেইড','প্রিপেইড'], datasets:[{ data: @js($planDistribution), backgroundColor:['#3B82F6','#F59E0B'], borderWidth:3, borderColor:'#fff' }] }, options:{ responsive:true, maintainAspectRatio:false, cutout:'68%', plugins:{legend:{display:false}} } });
              "></canvas>
            </div>
            <div class="legend">
              <span><i style="background:#3B82F6"></i>পোস্টপেইড (মাসিক, ছাত্রসংখ্যা টায়ার)</span>
              <span><i style="background:#F59E0B"></i>প্রিপেইড (৳৫/ছাত্র, ব্যালেন্স)</span>
            </div>
          </div>
        </div>

        <div class="split-grid" style="grid-template-columns:1.3fr 1fr;">
          <div class="card">
            <h3>সাম্প্রতিক নিবন্ধিত প্রতিষ্ঠান</h3>
            <table>
              <thead><tr><th>প্রতিষ্ঠান</th><th>ধরন</th><th>প্ল্যান</th><th>স্ট্যাটাস</th></tr></thead>
              <tbody>
                @forelse ($recentInstitutions as $inst)
                  <tr>
                    <td>{{ $inst->name }}</td>
                    <td>{{ ['school'=>'স্কুল','madrasa'=>'মাদরাসা','kindergarten'=>'কিন্ডারগার্টেন'][$inst->institution_type] ?? $inst->institution_type }}</td>
                    <td>
                      @if ($inst->isPrepaid())
                        <span class="tag gold">প্রিপেইড • ৳{{ number_format((float) $inst->prepaid_balance) }}</span>
                      @else
                        @php $due = $billingSvc->postpaidDueAmount($inst); @endphp
                        <span class="tag info">পোস্টপেইড • {{ $due ? '৳'.number_format($due) : 'কাস্টম' }}</span>
                      @endif
                    </td>
                    <td><span class="tag {{ $inst->status === 'active' ? 'good' : ($inst->status === 'suspended' ? 'bad' : 'gold') }}">{{ ['active'=>'সক্রিয়','trial'=>'ট্রায়াল','suspended'=>'স্থগিত'][$inst->status] ?? $inst->status }}</span></td>
                  </tr>
                @empty
                  <tr><td colspan="4" style="text-align:center;color:var(--ink-soft);padding:24px 0;">এখনো কোনো প্রতিষ্ঠান নেই</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
          <div class="card">
            <h3>জরুরি সাপোর্ট টিকেট</h3>
            @forelse ($urgentTickets as $t)
              <div class="ticket-row" style="margin-bottom:8px;" wire:click="setSection('support')">
                <div class="ticket-top"><div class="ticket-title">{{ $t->subject }}</div><span class="ticket-priority high">জরুরি</span></div>
                <div class="ticket-meta"><span>{{ $t->institution->name ?? 'অজ্ঞাত' }}</span><span>{{ $t->created_at->diffForHumans() }}</span></div>
              </div>
            @empty
              <p style="font-size:12.5px;color:var(--ink-soft);">কোনো জরুরি টিকেট নেই</p>
            @endforelse
          </div>
        </div>

        @if ($pendingInstitutions->count() > 0)
          <div class="card">
            <h3>নতুন প্রতিষ্ঠান রেজিস্ট্রেশন — অনুমোদন বাকি</h3>
            @foreach ($pendingInstitutions as $p)
              <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 0; border-bottom:1px dashed var(--line);">
                <div><div style="font-weight:700;font-size:13.5px;">{{ $p->name }}</div><div style="font-size:11.5px;color:var(--ink-soft);">{{ $p->registration_email }} • {{ $p->phone }}</div></div>
                <div style="display:flex;gap:8px;">
                  <button class="btn-primary" style="padding:8px 14px;font-size:12px;" wire:click="approvePendingInstitution('{{ $p->id }}')">অনুমোদন</button>
                  <button class="btn-ghost" style="padding:8px 14px;font-size:12px;" wire:click="rejectPendingInstitution('{{ $p->id }}')">বাতিল</button>
                </div>
              </div>
            @endforeach
          </div>
        @endif
      @endif

      {{-- ============ NEW APPLICATIONS ============ --}}
      @if ($activeSection === 'applications')
        <div class="page-head">
          <div><h2>নতুন প্রতিষ্ঠান আবেদন</h2><p>সদ্য রেজিস্ট্রেশন করা প্রতিষ্ঠানসমূহ যাচাই করে অনুমোদন বা বাতিল করুন</p></div>
        </div>

        @if ($justApprovedPassword)
          <div class="card" style="border-color:var(--good);">
            <h3 style="color:var(--good);">অনুমোদন সম্পন্ন হয়েছে</h3>
            <p class="sub">সাময়িক পাসওয়ার্ড: <b style="direction:ltr; display:inline-block;">{{ $justApprovedPassword }}</b> — এটা প্রতিষ্ঠানের ফোন নম্বরে SMS হিসেবে পাঠানো হয়েছে (নম্বর দেওয়া থাকলে), তবু কপি করে রাখাই নিরাপদ।</p>
          </div>
        @endif

        <div class="card">
          <h3>যাচাইয়ের অপেক্ষায় ({{ $pendingInstitutions->count() }})</h3>
          @forelse ($pendingInstitutions as $p)
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 0; border-bottom:1px dashed var(--line);" wire:key="pending-{{ $p->id }}">
              <div>
                <div style="font-weight:700;font-size:13.5px;">{{ $p->name }}</div>
                <div style="font-size:11.5px;color:var(--ink-soft);">{{ $p->registration_email }} • {{ $p->phone }}</div>
                <div style="font-size:11px;color:var(--ink-soft);">{{ $p->address }}, {{ $p->district }} • {{ ['school'=>'স্কুল','madrasa'=>'মাদরাসা','kindergarten'=>'কিন্ডারগার্টেন'][$p->institution_type] ?? $p->institution_type }} • আনুমানিক ছাত্র: {{ $p->student_count_estimate ?? '—' }}</div>
                <div style="font-size:10.5px;color:var(--ink-soft);margin-top:2px;">আবেদন করেছে: {{ $p->created_at->diffForHumans() }}</div>
              </div>
              <div style="display:flex;gap:8px; flex-shrink:0;">
                <button class="btn-primary" style="padding:8px 14px;font-size:12px;" wire:click="approvePendingInstitution('{{ $p->id }}')">অনুমোদন</button>
                <button class="btn-ghost" style="padding:8px 14px;font-size:12px;" wire:click="rejectPendingInstitution('{{ $p->id }}')">বাতিল</button>
              </div>
            </div>
          @empty
            <p style="color:var(--ink-soft);font-size:13px;">যাচাইয়ের অপেক্ষায় কোনো নতুন আবেদন নেই</p>
          @endforelse
        </div>

        @if ($recentlyReviewed->count() > 0)
          <div class="card">
            <h3>সাম্প্রতিক বাতিল হওয়া আবেদন</h3>
            <table>
              <thead><tr><th>প্রতিষ্ঠান</th><th>ইমেইল</th><th>তারিখ</th></tr></thead>
              <tbody>
                @foreach ($recentlyReviewed as $r)
                  <tr><td>{{ $r->name }}</td><td>{{ $r->registration_email }}</td><td>{{ $r->updated_at->format('d M, Y') }}</td></tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      @endif

      {{-- ============ INSTITUTIONS ============ --}}
      @if ($activeSection === 'institutions')
        <div class="page-head">
          <div><h2>সকল প্রতিষ্ঠান</h2><p>মডিউল অ্যাক্সেস, প্ল্যান ও অ্যাকাউন্ট স্ট্যাটাস নিয়ন্ত্রণ করুন</p></div>
        </div>

        @if ($justApprovedPassword)
          <div class="card" style="border-color:var(--good);">
            <h3 style="color:var(--good);">অনুমোদন সম্পন্ন হয়েছে</h3>
            <p class="sub">সাময়িক পাসওয়ার্ড: <b style="direction:ltr; display:inline-block;">{{ $justApprovedPassword }}</b> — এটা প্রতিষ্ঠানের ফোন নম্বরে SMS হিসেবে পাঠানো হয়েছে (নম্বর দেওয়া থাকলে), তবু কপি করে রাখাই নিরাপদ।</p>
          </div>
        @endif

        <div class="filter-card">
          <div class="f-field"><label>ধরন</label>
            <select wire:model.live="instType"><option value="">সকল</option><option value="school">স্কুল</option><option value="madrasa">মাদরাসা</option><option value="kindergarten">কিন্ডারগার্টেন</option></select>
          </div>
          <div class="f-field"><label>প্ল্যান</label>
            <select wire:model.live="instBillingType"><option value="">সকল</option><option value="postpaid">পোস্টপেইড</option><option value="prepaid">প্রিপেইড</option></select>
          </div>
          <div class="f-field"><label>স্ট্যাটাস</label>
            <select wire:model.live="instStatus"><option value="">সকল</option><option value="active">সক্রিয়</option><option value="trial">ট্রায়াল</option><option value="suspended">স্থগিত</option></select>
          </div>
          <div class="f-field f-search"><label>খুঁজুন</label><input type="text" wire:model.live.debounce.400ms="instSearch" placeholder="প্রতিষ্ঠানের নাম বা সাবডোমেইন"></div>
        </div>

        <div class="table-card">
          <table>
            <thead><tr><th>প্রতিষ্ঠান</th><th>ধরন</th><th>প্ল্যান</th><th>শিক্ষার্থী</th><th>স্ট্যাটাস</th><th>যোগদান</th><th>কার্যক্রম</th></tr></thead>
            <tbody>
              @forelse ($institutions as $inst)
                <tr wire:key="inst-{{ $inst->id }}">
                  <td><div class="inst-cell"><div class="ini" style="background:#6C5CE7">{{ mb_substr($inst->name, 0, 1) }}</div><div><div class="nm">{{ $inst->name }}</div><div class="sub">{{ $inst->slug }}.edution.xyz</div></div></div></td>
                  <td>{{ ['school'=>'স্কুল','madrasa'=>'মাদরাসা','kindergarten'=>'কিন্ডারগার্টেন'][$inst->institution_type] ?? '—' }}</td>
                  <td>
                      @if ($inst->isPrepaid())
                        <span class="tag gold">প্রিপেইড • ৳{{ number_format((float) $inst->prepaid_balance) }}</span>
                      @else
                        @php $due = $billingSvc->postpaidDueAmount($inst); @endphp
                        <span class="tag info">পোস্টপেইড • {{ $due ? '৳'.number_format($due) : 'কাস্টম' }}</span>
                      @endif
                    </td>
                  <td>{{ number_format($inst->students_count) }}</td>
                  <td><span class="tag {{ $inst->status === 'active' ? 'good' : ($inst->status === 'suspended' ? 'bad' : 'gold') }}">{{ ['active'=>'সক্রিয়','trial'=>'ট্রায়াল','suspended'=>'স্থগিত','rejected'=>'বাতিল'][$inst->status] ?? $inst->status }}</span></td>
                  <td>{{ $inst->created_at->format('d M, Y') }}</td>
                  <td><button class="manage-btn" wire:click="openManageModal('{{ $inst->id }}')">পরিচালনা করুন</button></td>
                </tr>
              @empty
                <tr><td colspan="7" style="text-align:center;color:var(--ink-soft);padding:26px 0;">কোনো প্রতিষ্ঠান পাওয়া যায়নি</td></tr>
              @endforelse
            </tbody>
          </table>
          <div class="table-foot"><div>মোট {{ $institutions->count() }}টি প্রতিষ্ঠান দেখানো হচ্ছে</div></div>
        </div>
      @endif

      {{-- ============ BILLING ============ --}}
      @if ($activeSection === 'billing')
        <div class="page-head"><div><h2>প্যাকেজ ও বিলিং</h2><p>রাজস্ব ও প্রতিষ্ঠানের পেমেন্ট ইতিহাস</p></div></div>

        <div class="stat-strip">
          <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="7" width="17" height="12" rx="2.5"/></svg></div><div><div class="sv">৳{{ number_format($stats['mrr']) }}</div><div class="sl">মাসিক রাজস্ব</div></div></div>
          <div class="stat-chip" style="--accent:var(--info)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 19V10l8-6 8 6v9"/></svg></div><div><div class="sv">৳{{ number_format($stats['arr']) }}</div><div class="sl">বার্ষিক রাজস্ব (ARR)</div></div></div>
          <div class="stat-chip" style="--accent:var(--bad)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 9v4"/><circle cx="12" cy="16" r=".4" fill="currentColor"/><path d="M10.3 4.5 2.6 18a1.8 1.8 0 0 0 1.6 2.7h15.6a1.8 1.8 0 0 0 1.6-2.7L13.7 4.5a1.8 1.8 0 0 0-3.4 0Z"/></svg></div><div><div class="sv">{{ $stats['pendingPayments'] }}</div><div class="sl">যাচাইয়ের অপেক্ষায়</div></div></div>
        </div>

        <div class="card">
          <h3>রাজস্ব প্রবণতা</h3>
          <p class="sub">গত ৮ মাস — approved পেমেন্ট অনুযায়ী</p>
          <div class="chart-box" wire:ignore>
            <canvas x-init="
              if ($el.chartInstance) { $el.chartInstance.destroy(); }
              Chart.defaults.font.family = &quot;'Hind Siliguri', sans-serif&quot;; Chart.defaults.color = '#6B7280';
              $el.chartInstance = new Chart($el, { type:'bar', data:{ labels: @js($revenueMonths['labels']), datasets:[{ data: @js($revenueMonths['data']), backgroundColor:'#6C5CE7', borderRadius:6, maxBarThickness:28 }] }, options:{ responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}}, scales:{ y:{grid:{color:'rgba(31,36,50,.06)'}}, x:{grid:{display:false}} } } });
            "></canvas>
          </div>
        </div>

        @if ($pendingPayments->count() > 0)
          <div class="card">
            <h3>যাচাইয়ের অপেক্ষায় থাকা পেমেন্ট</h3>
            @foreach ($pendingPayments as $pay)
              <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 0; border-bottom:1px dashed var(--line);">
                <div><div style="font-weight:700;font-size:13.5px;">{{ $pay->institution->name ?? 'অজ্ঞাত' }} <span class="tag {{ $pay->purpose === 'wallet_topup' ? 'gold' : 'info' }}" style="margin-left:6px;">{{ $pay->purpose === 'wallet_topup' ? 'প্রিপেইড টপ-আপ' : 'সাবস্ক্রিপশন' }}</span></div><div style="font-size:11.5px;color:var(--ink-soft);">৳{{ number_format($pay->amount, 2) }} • {{ $pay->method }} • {{ $pay->transaction_ref }} • {{ $pay->for_month }}</div></div>
                <div style="display:flex;gap:8px;">
                  <button class="btn-primary" style="padding:8px 14px;font-size:12px;" wire:click="approvePayment('{{ $pay->id }}')">অনুমোদন</button>
                  <button class="btn-ghost" style="padding:8px 14px;font-size:12px;" wire:click="rejectPayment('{{ $pay->id }}')">বাতিল</button>
                </div>
              </div>
            @endforeach
          </div>
        @endif

        <div class="split-grid">
          <div class="card">
            <h3>পোস্টপেইড — বকেয়া/গ্রেস পিরিয়ড</h3>
            <p class="sub">গ্রেস পিরিয়ড শেষ হওয়ার তারিখ অনুযায়ী সাজানো</p>
            @forelse ($postpaidDue as $inst)
              <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px dashed var(--line);">
                <div>
                  <div style="font-weight:700;font-size:13px;">{{ $inst->name }}</div>
                  <div style="font-size:11.5px;color:var(--ink-soft);">বিল ৳{{ number_format($billingSvc->postpaidDueAmount($inst) ?? 0) }} • গ্রেস শেষ: {{ $inst->billing_grace_ends_at?->translatedFormat('d M') }}</div>
                </div>
                <span class="tag {{ $inst->billing_suspended ? 'bad' : ((int) $inst->graceDaysLeft() <= 3 ? 'gold' : 'good') }}">
                  {{ $inst->billing_suspended ? 'সাসপেন্ড' : ((int) $inst->graceDaysLeft()) . ' দিন বাকি' }}
                </span>
              </div>
            @empty
              <p style="color:var(--ink-soft);font-size:13px;">কোনো পোস্টপেইড প্রতিষ্ঠানের বিল বকেয়া নেই</p>
            @endforelse
          </div>
          <div class="card">
            <h3>প্রিপেইড — কম ব্যালেন্স</h3>
            <p class="sub">ব্যালেন্স অনুযায়ী সাজানো (কম থেকে বেশি)</p>
            @forelse ($prepaidLow->take(10) as $inst)
              <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px dashed var(--line);">
                <div>
                  <div style="font-weight:700;font-size:13px;">{{ $inst->name }}</div>
                  <div style="font-size:11.5px;color:var(--ink-soft);">মাসিক খরচ আনুমানিক ৳{{ number_format($billingSvc->prepaidMonthlyCost($inst)) }}</div>
                </div>
                <span class="tag {{ $inst->billing_suspended ? 'bad' : ((float) $inst->prepaid_balance < $billingSvc->prepaidMonthlyCost($inst) ? 'gold' : 'good') }}">৳{{ number_format((float) $inst->prepaid_balance) }}</span>
              </div>
            @empty
              <p style="color:var(--ink-soft);font-size:13px;">কোনো প্রিপেইড প্রতিষ্ঠান নেই</p>
            @endforelse
          </div>
        </div>

        <div class="table-card">
          <table>
            <thead><tr><th>প্রতিষ্ঠান</th><th>মাস</th><th>পরিমাণ</th><th>পদ্ধতি</th><th>স্ট্যাটাস</th></tr></thead>
            <tbody>
              @forelse ($payments as $pay)
                <tr>
                  <td>{{ $pay->institution->name ?? 'অজ্ঞাত' }}</td>
                  <td>{{ $pay->for_month }}</td>
                  <td>৳{{ number_format($pay->amount, 2) }}</td>
                  <td>{{ $pay->method }}</td>
                  <td><span class="tag {{ $pay->status === 'approved' ? 'good' : 'bad' }}">{{ $pay->status === 'approved' ? 'পরিশোধিত' : 'বাতিল' }}</span></td>
                </tr>
              @empty
                <tr><td colspan="5" style="text-align:center;color:var(--ink-soft);padding:24px 0;">এখনো কোনো পেমেন্ট রেকর্ড নেই</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      @endif

      {{-- ============ NOTICES ============ --}}
      @if ($activeSection === 'notices')
        <div class="page-head"><div><h2>প্ল্যাটফর্ম নোটিশ</h2><p>সকল প্রতিষ্ঠান বা নির্দিষ্ট শ্রেণির কাছে ঘোষণা পাঠান</p></div></div>

        <div class="split-grid">
          <div>
            @forelse ($notices as $n)
              <div class="notice-card">
                <div class="notice-top"><div class="notice-title">{{ $n->title }}</div><span class="tag {{ match($n->audience) { 'trial'=>'gold','premium'=>'info','overdue'=>'bad', default=>'gold' } }}">{{ ['all'=>'সকল প্রতিষ্ঠান','trial'=>'শুধু ট্রায়াল','premium'=>'শুধু প্রিমিয়াম','overdue'=>'বকেয়া প্রতিষ্ঠান'][$n->audience] ?? $n->audience }}</span></div>
                <div class="notice-body">{{ $n->body }}</div>
                <div class="notice-meta"><span>পাঠানো হয়েছে: {{ $n->created_at->format('d M, Y') }}</span><span>{{ $n->reached_count }}টি প্রতিষ্ঠানে পৌঁছেছে</span></div>
              </div>
            @empty
              <p style="color:var(--ink-soft);font-size:13px;">এখনো কোনো নোটিশ পাঠানো হয়নি</p>
            @endforelse
          </div>

          <aside class="compose-card">
            <div class="compose-head">
              <div class="compose-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h13l3 4-3 4H4z"/><path d="M6 13v6"/></svg></div>
              <div><div class="t1">নতুন নোটিশ পাঠান</div><div class="t2">সকল বা নির্বাচিত প্রতিষ্ঠানে</div></div>
            </div>
            <div class="field"><label>শিরোনাম</label><input type="text" wire:model="noticeTitle" placeholder="নোটিশের শিরোনাম"></div>
            @error('noticeTitle')<div class="err">{{ $message }}</div>@enderror
            <div class="field"><label>বার্তা</label><textarea wire:model="noticeBody" placeholder="বিস্তারিত লিখুন"></textarea></div>
            @error('noticeBody')<div class="err">{{ $message }}</div>@enderror
            <div class="field"><label>প্রাপক</label>
              <select wire:model="noticeAudience">
                <option value="all">সকল প্রতিষ্ঠান</option>
                <option value="trial">শুধু ট্রায়াল</option>
                <option value="premium">শুধু প্রিমিয়াম</option>
                <option value="overdue">বকেয়া প্রতিষ্ঠান</option>
              </select>
            </div>
            <div class="field"><label>ধরন</label>
              <select wire:model="noticeType">
                <option value="general">সাধারণ</option>
                <option value="urgent">জরুরি</option>
                <option value="feature">নতুন ফিচার</option>
                <option value="maintenance">মেইনটেন্যান্স</option>
              </select>
            </div>
            <button class="btn-submit" wire:click="sendNotice" type="button"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 5h13l3 4-3 4H4z"/><path d="M6 13v6"/></svg>নোটিশ পাঠান</button>
          </aside>
        </div>
      @endif

      {{-- ============ SUPPORT ============ --}}
      @if ($activeSection === 'support')
        <div class="page-head"><div><h2>সাপোর্ট টিকেট</h2><p>প্রতিষ্ঠানসমূহের সমস্যা পর্যালোচনা করুন ও সমাধান দিন</p></div></div>

        <div class="split-grid">
          <div>
            @forelse ($tickets as $t)
              <div class="ticket-row {{ $activeTicketId === $t->id ? 'active' : '' }}" wire:click="loadTicket('{{ $t->id }}')" wire:key="ticket-{{ $t->id }}">
                <div class="ticket-top"><div class="ticket-title">{{ $t->subject }}</div><span class="ticket-priority {{ $t->priority }}">{{ ['high'=>'জরুরি','med'=>'মধ্যম','low'=>'সাধারণ'][$t->priority] }}</span></div>
                <div class="ticket-meta"><span>{{ $t->institution->name ?? 'অজ্ঞাত' }}</span><span>{{ $t->status === 'resolved' ? 'সমাধান হয়েছে' : 'খোলা' }}</span><span>{{ $t->created_at->diffForHumans() }}</span></div>
              </div>
            @empty
              <p style="color:var(--ink-soft);font-size:13px;">এখনো কোনো সাপোর্ট টিকেট নেই</p>
            @endforelse
          </div>

          <aside class="reply-panel">
            @if ($activeTicket)
              <h3 style="font-family:'Tiro Bangla',serif; font-size:16px; margin:0 0 4px;">{{ $activeTicket->subject }}</h3>
              <p style="font-size:11.5px; color:var(--ink-muted); margin:0 0 14px;">{{ $activeTicket->institution->name ?? 'অজ্ঞাত' }} • {{ $activeTicket->status === 'resolved' ? 'সমাধান হয়েছে' : 'খোলা' }}</p>

              <div class="reply-thread">
                @foreach ($activeTicket->messages as $m)
                  <div class="msg {{ $m->sender_type === 'superadmin' ? 'admin' : '' }}">
                    <div class="who">{{ $m->sender_name }}</div>
                    {{ $m->body }}
                    <div class="time">{{ $m->created_at->format('d M, Y — h:i A') }}</div>
                  </div>
                @endforeach
              </div>

              @if ($activeTicket->status !== 'resolved')
                <div class="field"><label>উত্তর লিখুন</label><textarea wire:model="replyBody" placeholder="প্রতিষ্ঠানকে উত্তর দিন…"></textarea></div>
                <div style="display:flex; gap:8px;">
                  <button class="btn-submit" style="flex:1;" wire:click="sendReply" type="button">উত্তর পাঠান</button>
                </div>
                <div style="display:flex; gap:8px; margin-top:10px;">
                  <button class="btn-ghost" style="flex:1; justify-content:center;" wire:click="resolveTicket" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>সমাধান হয়েছে</button>
                  <select wire:change="updateTicketPriority($event.target.value)" style="border:1.4px solid var(--line); border-radius:9px; padding:0 10px; font-size:12.5px; font-family:inherit;">
                    <option value="high" {{ $activeTicket->priority === 'high' ? 'selected' : '' }}>অগ্রাধিকার: জরুরি</option>
                    <option value="med" {{ $activeTicket->priority === 'med' ? 'selected' : '' }}>অগ্রাধিকার: মধ্যম</option>
                    <option value="low" {{ $activeTicket->priority === 'low' ? 'selected' : '' }}>অগ্রাধিকার: সাধারণ</option>
                  </select>
                </div>
              @endif
            @else
              <p style="color:var(--ink-soft);font-size:13px;">বাম পাশ থেকে একটা টিকেট নির্বাচন করুন</p>
            @endif
          </aside>
        </div>
      @endif

      {{-- ============ SETTINGS ============ --}}
      @if ($activeSection === 'trash')
        <div class="page-head"><div><h2>রিসাইকেল বিন</h2><p>সরানো প্রতিষ্ঠান এখান থেকে ফিরিয়ে আনুন অথবা স্থায়ীভাবে ডিলিট করুন</p></div></div>

        <div class="card">
          <table>
            <thead><tr><th>প্রতিষ্ঠান</th><th>সরানো হয়েছে</th><th>কে সরিয়েছে</th><th>কার্যক্রম</th></tr></thead>
            <tbody>
              @forelse ($trashedInstitutions as $inst)
                <tr>
                  <td><b>{{ $inst->name }}</b><div class="sub" style="font-size:11.5px;">{{ $inst->slug }}.edution.xyz</div></td>
                  <td>{{ $inst->deleted_at?->format('d M, Y h:i A') }}</td>
                  <td>{{ \App\Models\User::find($inst->deleted_by)?->name ?? '—' }}</td>
                  <td style="display:flex; gap:8px;">
                    <button class="btn-ghost" wire:click="restoreInstitution('{{ $inst->id }}')" type="button">পুনরুদ্ধার করুন</button>
                    <button class="btn-ghost" style="border-color:var(--bad);color:var(--bad);" wire:click="openPurgeModal('{{ $inst->id }}')" type="button">স্থায়ীভাবে ডিলিট</button>
                  </td>
                </tr>
              @empty
                <tr><td colspan="4" style="text-align:center;color:var(--ink-soft);padding:24px;">রিসাইকেল বিন খালি</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      @endif

      @if ($activeSection === 'demo-access')
        <div class="page-head"><div><h2>ডেমো এক্সেস</h2><p>পাবলিক ডেমোতে কারা রেজিস্ট্রেশন করেছে, আর কারা শিক্ষক/অভিভাবক পোর্টাল দেখতে অনুরোধ করেছে — সব এখানে</p></div></div>

        <div class="card" style="margin-bottom:18px;">
          <h3 style="margin:0 0 4px;">সব ডেমো লিড <span class="sub" style="font-weight:400;">({{ $demoLeads->count() }} জন রেজিস্ট্রেশন করেছে)</span></h3>
          <table>
            <thead><tr><th>নাম/ফোন</th><th>প্রতিষ্ঠান</th><th>রেজিস্ট্রেশন</th><th>এডমিন</th><th>শিক্ষক</th><th>অভিভাবক</th></tr></thead>
            <tbody>
              @forelse ($demoLeads as $lead)
                @php
                  $tReq = $lead->accessRequests->firstWhere('role', 'teacher');
                  $gReq = $lead->accessRequests->firstWhere('role', 'guardian');
                  $badge = function ($req) {
                      if (! $req) return '<span class="pill inactive">দেখেনি</span>';
                      if ($req->isCurrentlyUnlocked()) return '<span class="pill active">আনলক আছে</span>';
                      if ($req->status === 'pending') return '<span class="pill day">অনুরোধ পেন্ডিং</span>';
                      if ($req->status === 'rejected') return '<span class="pill due">প্রত্যাখ্যাত</span>';
                      return '<span class="pill inactive">মেয়াদ শেষ</span>';
                  };
                @endphp
                <tr wire:key="lead-{{ $lead->id }}">
                  <td><b>{{ $lead->name }}</b><div class="sub" style="font-size:11.5px;">{{ $lead->phone }}</div></td>
                  <td>{{ $lead->institution_name ?? '—' }}</td>
                  <td>{{ $lead->created_at?->format('d M, Y h:i A') }}</td>
                  <td><span class="pill active">দেখেছে</span></td>
                  <td>{!! $badge($tReq) !!}</td>
                  <td>{!! $badge($gReq) !!}</td>
                </tr>
              @empty
                <tr><td colspan="6" style="text-align:center;color:var(--ink-soft);padding:24px;">এখনো কেউ রেজিস্ট্রেশন করেনি</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="card">
          <h3 style="margin:0 0 4px;">শিক্ষক/অভিভাবক এক্সেস রিকোয়েস্ট</h3>
          <table>
            <thead><tr><th>নাম/ফোন</th><th>প্রতিষ্ঠান</th><th>কোন পোর্টাল</th><th>স্ট্যাটাস</th><th>কার্যক্রম</th></tr></thead>
            <tbody>
              @forelse ($demoRequests as $req)
                <tr wire:key="dar-{{ $req->id }}">
                  <td><b>{{ $req->lead->name }}</b><div class="sub" style="font-size:11.5px;">{{ $req->lead->phone }}</div></td>
                  <td>{{ $req->lead->institution_name ?? '—' }}</td>
                  <td>{{ $req->role === 'teacher' ? 'শিক্ষক পোর্টাল' : 'অভিভাবক পোর্টাল' }}</td>
                  <td>
                    @if ($req->isCurrentlyUnlocked())
                      <span class="pill active">আনলক আছে — {{ $req->unlocked_until->diffForHumans() }}</span>
                    @elseif ($req->status === 'approved')
                      <span class="pill inactive">মেয়াদ শেষ</span>
                    @elseif ($req->status === 'rejected')
                      <span class="pill due">প্রত্যাখ্যাত</span>
                    @else
                      <span class="pill day">অপেক্ষমাণ</span>
                    @endif
                  </td>
                  <td style="display:flex; gap:6px; flex-wrap:wrap;">
                    <button class="btn-ghost" wire:click="unlockDemoAccess('{{ $req->id }}', 5)" type="button">৫ মিনিট</button>
                    <button class="btn-ghost" wire:click="unlockDemoAccess('{{ $req->id }}', 10)" type="button">১০ মিনিট</button>
                    <button class="btn-ghost" onclick="let m = prompt('কত মিনিটের জন্য আনলক করবেন?', '30'); if (m && parseInt(m) > 0) { @this.call('unlockDemoAccess', '{{ $req->id }}', parseInt(m)); }" type="button">কাস্টম</button>
                    <button class="btn-ghost" style="border-color:var(--bad);color:var(--bad);" wire:click="rejectDemoAccess('{{ $req->id }}')" type="button">বাতিল</button>
                  </td>
                </tr>
              @empty
                <tr><td colspan="5" style="text-align:center;color:var(--ink-soft);padding:24px;">কোনো ডেমো এক্সেস রিকোয়েস্ট নেই</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      @endif

      {{-- ============ SYSTEM HEALTH ============ --}}
      @if ($activeSection === 'system-health')
        <div class="page-head"><div><h2>সিস্টেম হেলথ</h2><p>সাম্প্রতিক এরর ও সার্ভার স্ট্যাটাস — SSH না করেই এখান থেকে দেখুন</p></div></div>

        <div class="stat-strip">
          <div class="stat-chip" style="--accent:{{ $diskUsagePercent >= 85 ? 'var(--bad)' : 'var(--good)' }}"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></div><div><div class="sv">{{ $diskUsagePercent }}%</div><div class="sl">ডিস্ক ব্যবহৃত</div></div></div>
          <div class="stat-chip" style="--accent:var(--info)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg></div><div><div class="sv">{{ $diskFreeGb }} GB</div><div class="sl">ডিস্ক খালি</div></div></div>
          <div class="stat-chip" style="--accent:{{ count($recentErrors) > 0 ? 'var(--bad)' : 'var(--good)' }}"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 9v4"/><circle cx="12" cy="16" r=".4" fill="currentColor"/><path d="M10.3 4.5 2.6 18a1.8 1.8 0 0 0 1.6 2.7h15.6a1.8 1.8 0 0 0 1.6-2.7L13.7 4.5a1.8 1.8 0 0 0-3.4 0Z"/></svg></div><div><div class="sv">{{ count($recentErrors) }}</div><div class="sl">সাম্প্রতিক এরর (সর্বোচ্চ ৩০টা)</div></div></div>
          <div class="stat-chip" style="--accent:var(--gold)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg></div><div><div class="sv">{{ $logSizeMb }} MB</div><div class="sl">লগ ফাইল সাইজ</div></div></div>
        </div>

        <div class="card">
          <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:6px;">
            <div>
              <h3 style="margin:0;">সাম্প্রতিক এরর লগ</h3>
              <p class="sub" style="margin:2px 0 0;">edution.xyz, panel.edution.xyz ও সব প্রতিষ্ঠানের সাবডোমেইন — একই laravel.log এ, নতুন থেকে পুরাতন ক্রমে</p>
            </div>
            <button class="btn-ghost" style="border-color:var(--bad);color:var(--bad);" onclick="if(confirm('লগ ফাইল খালি করবেন? এটা ফিরিয়ে আনা যাবে না।')) { @this.call('clearSystemLog'); }" type="button">লগ খালি করুন</button>
          </div>

          @forelse ($recentErrors as $i => $err)
            <div style="border-bottom:1px dashed var(--line);padding:12px 0;">
              <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span class="tag {{ in_array($err['level'], ['ERROR','CRITICAL','ALERT','EMERGENCY']) ? 'bad' : 'gold' }}">{{ $err['level'] }}</span>
                <span style="font-size:11.5px;color:var(--ink-soft);">{{ $err['time'] }}</span>
              </div>
              <div style="font-size:13px;margin-top:6px;">{{ $err['message'] }}</div>
              <details style="margin-top:6px;">
                <summary style="cursor:pointer;font-size:11.5px;color:var(--ink-soft);">পুরো ডিটেইল দেখুন</summary>
                <pre style="white-space:pre-wrap;word-break:break-all;font-size:11px;background:var(--paper-deep);padding:10px;border-radius:8px;margin-top:6px;max-height:260px;overflow:auto;">{{ $err['detail'] }}</pre>
              </details>
            </div>
          @empty
            <p style="color:var(--ink-soft);font-size:13px;padding:20px 0;">কোনো এরর পাওয়া যায়নি — সব ঠিকঠাক চলছে।</p>
          @endforelse
        </div>
      @endif

      @if ($activeSection === 'settings')
        <div class="page-head"><div><h2>প্ল্যাটফর্ম সেটিংস</h2><p>সুপার এডমিন টিম ও সাধারণ সিস্টেম সেটিংস পরিচালনা করুন</p></div></div>

        @if ($justApprovedPassword)
          <div class="card" style="border-color:var(--good);">
            <h3 style="color:var(--good);">নতুন সুপার এডমিন যোগ হয়েছে</h3>
            <p class="sub">সাময়িক পাসওয়ার্ড: <b style="direction:ltr; display:inline-block;">{{ $justApprovedPassword }}</b></p>
          </div>
        @endif

        <div class="card">
          <h3>সুপার এডমিন টিম</h3>
          <p class="sub">যাদের প্ল্যাটফর্ম নিয়ন্ত্রণের অ্যাক্সেস আছে</p>
          <table>
            <thead><tr><th>নাম</th><th>ইমেইল</th><th>যোগদান</th></tr></thead>
            <tbody>
              @foreach ($superadmins as $sa)
                <tr><td>{{ $sa->name }}</td><td>{{ $sa->email }}</td><td>{{ $sa->created_at->format('d M, Y') }}</td></tr>
              @endforeach
            </tbody>
          </table>
          <div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
            <div class="f-field"><label>নাম</label><input type="text" wire:model="inviteName" placeholder="পূর্ণ নাম"></div>
            <div class="f-field"><label>ইমেইল</label><input type="email" wire:model="inviteEmail" placeholder="email@@edution.xyz"></div>
            <div class="f-field"><label>মোবাইল (forgot-password SMS-এর জন্য)</label><input type="text" wire:model="invitePhone" placeholder="01XXXXXXXXX"></div>
            <button class="btn-primary" wire:click="inviteSuperadmin" type="button">যোগ করুন</button>
          </div>
          @error('inviteName')<div class="err" style="margin-top:6px;">{{ $message }}</div>@enderror
          @error('inviteEmail')<div class="err" style="margin-top:6px;">{{ $message }}</div>@enderror
          @error('invitePhone')<div class="err" style="margin-top:6px;">{{ $message }}</div>@enderror
        </div>

        <div class="card">
          <h3>সাধারণ সেটিংস</h3>
          <div class="module-grid" style="grid-template-columns:1fr;">
            <div class="module-item"><span class="lbl">নতুন প্রতিষ্ঠান স্বয়ংক্রিয় অনুমোদন</span><label class="switch small"><input type="checkbox" wire:model="settingAutoApprove"><span class="switch-track"></span></label></div>
            <div class="module-item"><span class="lbl">ট্রায়াল শেষে স্বয়ংক্রিয় সাসপেন্ড</span><label class="switch small"><input type="checkbox" wire:model="settingAutoSuspendTrial"><span class="switch-track"></span></label></div>
            <div class="module-item"><span class="lbl">বকেয়া বিলিং সতর্কতা এসএমএস</span><label class="switch small"><input type="checkbox" wire:model="settingBillingSms"><span class="switch-track"></span></label></div>
            <div class="module-item"><span class="lbl">মেইনটেন্যান্স মোড</span><label class="switch small"><input type="checkbox" wire:model="settingMaintenance"><span class="switch-track"></span></label></div>
          </div>
          <div style="margin-top:16px;"><button class="btn-primary" wire:click="saveSettings" type="button">সেটিংস সংরক্ষণ করুন</button></div>
        </div>
      @endif

    </div>
  </main>

  {{-- ============ INSTITUTION MANAGE MODAL ============ --}}
  @if ($manageInstitutionId)
    @php $manageInst = \App\Models\Institution::find($manageInstitutionId); @endphp
    @if ($manageInst)
      <div class="modal-overlay open" wire:click.self="closeManageModal">
        <div class="modal">
          <div class="modal-head">
            <div class="modal-head-left">
              <div class="modal-ini">{{ mb_substr($manageInst->name, 0, 1) }}</div>
              <div><h3>{{ $manageInst->name }}</h3><div class="sub">{{ $manageInst->slug }}.edution.xyz • {{ ['school'=>'স্কুল','madrasa'=>'মাদরাসা','kindergarten'=>'কিন্ডারগার্টেন'][$manageInst->institution_type] ?? '' }}</div></div>
            </div>
            <button class="modal-close" wire:click="closeManageModal" type="button">&times;</button>
          </div>
          <div class="modal-body">

            <div class="modal-sec">
              <h4>অ্যাকাউন্ট স্ট্যাটাস</h4>
              <div class="status-toggle-row">
                <div><div class="lbl">অ্যাকাউন্ট সক্রিয় রাখুন</div><div class="desc">বন্ধ করলে প্রতিষ্ঠানের সকল ব্যবহারকারী লগইন করতে পারবে না</div></div>
                <label class="switch"><input type="checkbox" wire:model="manageActive"><span class="switch-track"></span></label>
              </div>
            </div>

            <div class="modal-sec">
              <h4>বিলিং ধরন</h4>
              <div class="plan-row">
                <select wire:model="manageBillingType">
                  <option value="postpaid">পোস্টপেইড — মাসিক, ছাত্রসংখ্যা অনুযায়ী টায়ার (১-২০০=৳৪৯৯, ২০১-৫০০=৳৯৯৯, ৫০১-১০০০=৳১৯৯৯)</option>
                  <option value="prepaid">প্রিপেইড — ছাত্র প্রতি ৳৫, আগে ব্যালেন্স লোড করা লাগবে</option>
                </select>
                <input type="number" wire:model="manageLimit" placeholder="শিক্ষার্থী সীমা override (ঐচ্ছিক)">
              </div>

              @if ($manageInst->isPrepaid())
                <div style="margin-top:10px;padding:12px 14px;background:var(--paper-deep);border-radius:10px;">
                  <div class="sub" style="margin-bottom:6px;">বর্তমান ব্যালেন্স: <strong>৳{{ number_format((float) $manageInst->prepaid_balance) }}</strong> • আনুমানিক মাসিক খরচ: ৳{{ number_format($billingSvc->prepaidMonthlyCost($manageInst)) }}</div>
                  <div class="plan-row">
                    <input type="number" wire:model="manageTopUpAmount" placeholder="টপ-আপ টাকার পরিমাণ">
                    <button class="btn-secondary" type="button" wire:click="manualTopUp">টপ-আপ করুন</button>
                  </div>
                </div>
              @else
                <div class="sub" style="margin-top:8px;">চলতি মাসের বিল: ৳{{ number_format($billingSvc->postpaidDueAmount($manageInst) ?? 0) }}
                  @if ($manageInst->billing_grace_ends_at)
                    • গ্রেস পিরিয়ড শেষ: {{ $manageInst->billing_grace_ends_at->translatedFormat('d M, Y') }}
                  @endif
                </div>
              @endif
            </div>

            <div class="modal-sec">
              <h4>মডিউল অ্যাক্সেস</h4>
              <div class="module-grid">
                @foreach (\App\Models\Institution::TOGGLEABLE_MODULES as $key => $label)
                  <div class="module-item">
                    <span class="lbl">{{ $label }}</span>
                    <label class="switch small"><input type="checkbox" wire:model="manageModules.{{ $key }}"><span class="switch-track"></span></label>
                  </div>
                @endforeach
              </div>
            </div>

            <div class="modal-sec">
              <h4>এডমিন পাসওয়ার্ড</h4>
              <div class="status-toggle-row">
                <div><div class="lbl">পাসওয়ার্ড ভুলে গেছে বা হারিয়ে ফেলেছে?</div><div class="desc">নতুন সাময়িক পাসওয়ার্ড জেনারেট হয়ে প্রতিষ্ঠানের ফোনে SMS চলে যাবে</div></div>
                <button class="btn-ghost" wire:click="resetAdminPassword('{{ $manageInstitutionId }}')" type="button">রিসেট করুন</button>
              </div>
            </div>

            <div class="modal-sec" style="border:1.5px solid rgba(239,68,68,.3);border-radius:12px;padding:14px 16px;background:rgba(239,68,68,.04);">
              <h4 style="color:var(--bad);">⚠ বিপজ্জনক অঞ্চল</h4>
              <p style="font-size:12.5px;color:var(--ink-muted);margin:4px 0 10px;line-height:1.7;">
                এই প্রতিষ্ঠান রিসাইকেল বিনে সরানো হবে — subdomain সাথে সাথে বন্ধ হয়ে যাবে, কিন্তু সব ডেটা (শিক্ষার্থী, শিক্ষক, হাজিরা, ফি) অক্ষত থাকবে। ভুল করে সরালেও রিসাইকেল বিন থেকে ফিরিয়ে আনা যাবে।
              </p>
              <button
                class="btn-ghost"
                style="border-color:var(--bad);color:var(--bad);"
                wire:click="moveInstitutionToTrash"
                wire:confirm="&quot;{{ $manageInst->name }}&quot; রিসাইকেল বিনে সরাতে চান?"
                type="button"
              >
                রিসাইকেল বিনে সরান
              </button>
            </div>

          </div>
          <div class="modal-foot">
            <button class="danger-link" wire:click="suspendFromModal" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M15 9l-6 6M9 9l6 6"/></svg>অ্যাকাউন্ট বন্ধ করুন</button>
            <div style="display:flex; gap:10px;">
              <button class="btn-ghost" wire:click="closeManageModal" type="button">বাতিল</button>
              <button class="btn-primary" wire:click="saveManageModal" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="20 6 9 17 4 12"/></svg>পরিবর্তন সংরক্ষণ করুন</button>
            </div>
          </div>
        </div>
      </div>
    @endif
  @endif

  {{-- ============ স্থায়ী ডিলিট (PURGE) মোডাল ============ --}}
  @if ($purgeInstitutionId)
    @php $purgeInst = \App\Models\Institution::onlyTrashed()->find($purgeInstitutionId); @endphp
    @if ($purgeInst)
      <div class="modal-overlay open" wire:click.self="closePurgeModal">
        <div class="modal">
          <div class="modal-head">
            <div class="modal-head-left">
              <div class="modal-ini" style="background:var(--bad);">{{ mb_substr($purgeInst->name, 0, 1) }}</div>
              <div><h3>স্থায়ীভাবে ডিলিট করুন</h3><div class="sub">{{ $purgeInst->name }} ({{ $purgeInst->slug }}.edution.xyz)</div></div>
            </div>
            <button class="modal-close" wire:click="closePurgeModal" type="button">&times;</button>
          </div>
          <div class="modal-body">
            <div class="modal-sec" style="border:1.5px solid rgba(239,68,68,.3);border-radius:12px;padding:14px 16px;background:rgba(239,68,68,.04);">
              <p style="font-size:12.5px;color:var(--ink-muted);margin:0 0 10px;line-height:1.7;">
                এই প্রতিষ্ঠান ও এর সকল ডেটা (শিক্ষার্থী, শিক্ষক, হাজিরা, ফি, পরীক্ষা — সবকিছু) <b>স্থায়ীভাবে</b> ডিলিট হয়ে যাবে। এটা আর ফেরানো যাবে না। নিশ্চিত করতে নিচে স্লাগ (<b>{{ $purgeInst->slug }}</b>) হুবুহু টাইপ করুন।
              </p>
              <input type="text" wire:model="purgeConfirmText" placeholder="{{ $purgeInst->slug }}" style="width:100%;">
            </div>
          </div>
          <div class="modal-foot">
            <div></div>
            <div style="display:flex; gap:10px;">
              <button class="btn-ghost" wire:click="closePurgeModal" type="button">বাতিল</button>
              <button
                class="btn-primary"
                style="background:var(--bad);"
                wire:click="permanentlyDeleteInstitution"
                type="button"
                {{ $purgeConfirmText !== $purgeInst->slug ? 'disabled' : '' }}
              >
                স্থায়ীভাবে ডিলিট করুন
              </button>
            </div>
          </div>
        </div>
      </div>
    @endif
  @endif

</div>
