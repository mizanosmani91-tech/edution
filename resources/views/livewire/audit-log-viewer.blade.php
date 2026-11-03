<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">সেটিংস / অডিট লগ</div>
            <h2 style="margin:0;">অডিট লগ</h2>
            <p class="sub" style="margin:4px 0 0;">ফি, নম্বর ও বেতনের তৈরি, পরিবর্তন ও মোছার ইতিহাস। এই লগ বদলানো বা মোছা যায় না।</p>
        </div>
    </div>

    <div class="select-card" style="margin-bottom:16px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <select wire:model.live="typeFilter" style="max-width:180px;">
            <option value="">সব ধরন</option>
            @foreach ($types as $class => $label)
                <option value="{{ $class }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="eventFilter" style="max-width:150px;">
            <option value="">সব ঘটনা</option>
            @foreach ($events as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="userFilter" style="max-width:200px;">
            <option value="">সব ব্যবহারকারী</option>
            @foreach ($actors as $a)
                <option value="{{ $a->user_id }}">{{ $a->user_name ?? 'অজানা' }}</option>
            @endforeach
        </select>
        <input type="date" wire:model.live="dateFrom" style="max-width:160px;" aria-label="শুরুর তারিখ">
        <input type="date" wire:model.live="dateTo" style="max-width:160px;" aria-label="শেষের তারিখ">
        <button class="btn-ghost" type="button" wire:click="clearFilters">ফিল্টার মুছুন</button>
    </div>

    <div class="table-card">
        <table>
            <thead><tr><th>সময়</th><th>কে</th><th>ঘটনা</th><th>কোন রেকর্ড</th><th></th></tr></thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr wire:key="log-{{ $log->id }}">
                        <td>{{ $log->created_at->format('d M, Y h:i A') }}</td>
                        <td>{{ $log->user_name ?? 'সিস্টেম' }}@if($log->user_role) <span class="cc-sub">({{ $log->user_role }})</span>@endif</td>
                        <td>
                            <span class="pill {{ match($log->event) { 'created' => 'active', 'deleted' => 'due', default => 'day' } }}">{{ $log->event_label }}</span>
                        </td>
                        <td>{{ $log->type_label }}@if($log->auditable_label) — {{ $log->auditable_label }}@endif</td>
                        <td><button class="btn-ghost" style="padding:6px 12px;font-size:12.5px;" type="button" wire:click="toggle('{{ $log->id }}')">{{ $expandedId === $log->id ? 'লুকান' : 'বিস্তারিত' }}</button></td>
                    </tr>
                    @if ($expandedId === $log->id)
                        <tr wire:key="log-detail-{{ $log->id }}">
                            <td colspan="5">
                                @php
                                    $old = $log->old_values ?? [];
                                    $new = $log->new_values ?? [];
                                    $fields = array_unique(array_merge(array_keys($old), array_keys($new)));
                                    $show = fn ($v) => is_null($v) ? '—' : (is_scalar($v) ? (string) $v : json_encode($v, JSON_UNESCAPED_UNICODE));
                                @endphp
                                <table class="info-table">
                                    <tr><td style="font-weight:600;">ফিল্ড</td><td style="font-weight:600;">আগে</td><td style="font-weight:600;">পরে</td></tr>
                                    @foreach ($fields as $f)
                                        <tr>
                                            <td>{{ $f }}</td>
                                            <td>{{ $show($old[$f] ?? null) }}</td>
                                            <td>{{ $show($new[$f] ?? null) }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                                @if ($log->ip_address)<div class="cc-sub" style="margin-top:6px;">IP: {{ $log->ip_address }}</div>@endif
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="5" style="text-align:center;color:var(--ink-soft);padding:30px 0;">কোনো লগ পাওয়া যায়নি</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:14px;">{{ $logs->links() }}</div>
</div>
