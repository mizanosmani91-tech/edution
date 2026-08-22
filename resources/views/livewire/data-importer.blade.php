<div class="lifecycle-page">
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">ইমপোর্ট / {{ $entityLabel }}</div>
            <h2>{{ $entityLabel }} ইমপোর্ট করুন</h2>
            <p>পুরাতন সিস্টেম/এক্সেল/ওয়ার্ড ফাইল থেকে {{ $entityLabel }} ডাটা একসাথে আপলোড করুন</p>
        </div>
    </div>

    <div class="tabs-bar" style="margin-bottom:16px;">
        <span class="tab-btn {{ $step === 'upload' ? 'active' : '' }}">১. ফাইল আপলোড</span>
        <span class="tab-btn {{ $step === 'mapping' ? 'active' : '' }}">২. কলাম মিলান</span>
        <span class="tab-btn {{ $step === 'preview' ? 'active' : '' }}">৩. যাচাই</span>
        <span class="tab-btn {{ $step === 'done' ? 'active' : '' }}">৪. ফলাফল</span>
    </div>

    @if ($fileError)
        <div class="alert-note" style="margin-bottom:16px;border-color:var(--bad);">{{ $fileError }}</div>
    @endif

    {{-- ধাপ ১: আপলোড --}}
    @if ($step === 'upload')
        <div class="cert-form-card">
            <div class="alert-note" style="margin-bottom:16px;">
                সাপোর্টেড ফরম্যাট: Excel (.xlsx, .xls), CSV (.csv), Word (.docx — শুধু আসল টেবিল থাকলে কাজ করবে)। ফাইলের প্রথম সারি অবশ্যই কলামের নাম (header) হতে হবে — এরপর প্রতিটা সারিতে একজন করে {{ $entityLabel }}-এর তথ্য। কলামের ক্রম/নাম যেকোনো কিছু হতে পারে, পরের ধাপে আপনি নিজে মিলিয়ে দেবেন।
            </div>

            @if ($entity === 'exam-results')
                <div class="field">
                    <label>কোন পরীক্ষার মার্কস? <span class="req">*</span></label>
                    <select wire:model="examId">
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($exams as $e)
                            <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->academic_year }})</option>
                        @endforeach
                    </select>
                    <p class="hint">প্রতিটা বিষয় আগে থেকেই "পরীক্ষার সময়সূচি"-তে ক্লাস-ভিত্তিক নির্ধারিত থাকতে হবে, নাহলে সেই বিষয়ের মার্কস ইমপোর্ট হবে না।</p>
                </div>
            @endif

            @if ($entity === 'attendance-device')
                <div class="alert-note" style="margin-bottom:16px;">
                    আপনার বায়োমেট্রিক/অ্যাটেন্ডেন্স ডিভাইস (যেমন ZKTeco, eSSL) থেকে অ্যাটেন্ডেন্স রিপোর্ট CSV/Excel আকারে এক্সপোর্ট করুন (সাধারণত প্রতিদিন প্রতি ব্যক্তির জন্য একটা সারি, User ID + First In + Last Out কলাম সহ)। ডিভাইসে যে User ID দিয়ে একজনকে এনরোল করা হয়েছে, সেটা অবশ্যই সিস্টেমের শিক্ষক/স্টাফ আইডি বা শিক্ষার্থী আইডির সাথে হুবহু মিলতে হবে — নাহলে সেই সারি ইমপোর্ট হবে না।
                </div>
                <div class="field">
                    <label>ডিভাইস ইউজাররা কারা? <span class="req">*</span></label>
                    <select wire:model="personType">
                        <option value="teacher">শিক্ষক/স্টাফ</option>
                        <option value="student">শিক্ষার্থী</option>
                    </select>
                </div>
            @endif

            <div class="field">
                <label>ফাইল নির্বাচন করুন</label>
                <input type="file" wire:model="file" accept=".xlsx,.xls,.csv,.docx">
                <div wire:loading wire:target="file" style="font-size:12px;color:var(--ink-soft);margin-top:6px;">ফাইল পড়া হচ্ছে…</div>
            </div>

            @if (! empty($headers))
                <div class="alert-note" style="margin-top:12px;">
                    ফাইল থেকে {{ count($headers) }}টা কলাম ও {{ count($rows) }}টা সারি পাওয়া গেছে। কলাম: {{ implode(', ', $headers) }}
                </div>
            @endif

            <button class="btn-primary" style="margin-top:16px;" wire:click="goToMapping" type="button">পরবর্তী ধাপ</button>
        </div>
    @endif

    {{-- ধাপ ২: ম্যাপিং --}}
    @if ($step === 'mapping')
        <div class="cert-form-card">
            <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:0 0 14px;">আপনার ফাইলের কলামগুলো সিস্টেমের ফিল্ডের সাথে মিলিয়ে দিন</h3>
            <div class="info-grid" style="grid-template-columns:1fr 1fr;">
                @foreach ($fields as $field)
                    <div class="field">
                        <label>{{ $field['label'] }} @if($field['required'])<span class="req">*</span>@endif</label>
                        <select wire:model="mapping.{{ $field['key'] }}">
                            <option value="">— মিলাবেন না —</option>
                            @foreach ($headers as $i => $h)
                                <option value="{{ $i }}">{{ $h !== '' ? $h : '(কলাম '.($i+1).')' }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>
            <div class="row-actions" style="margin-top:10px;">
                <button class="btn-ghost" wire:click="backToUpload" type="button">পেছনে যান</button>
                <button class="btn-primary" wire:click="buildPreview" type="button">যাচাই করুন</button>
            </div>
        </div>
    @endif

    {{-- ধাপ ৩: প্রিভিউ/যাচাই --}}
    @if ($step === 'preview')
        <div class="stat-strip">
            <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 6 9 17l-5-5"/></svg></div><div><div class="sv">{{ $validCount }}</div><div class="sl">সঠিক সারি</div></div></div>
            <div class="stat-chip" style="--accent:var(--bad)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg></div><div><div class="sv">{{ $invalidCount }}</div><div class="sl">সমস্যাযুক্ত সারি</div></div></div>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>স্ট্যাটাস</th>
                        @foreach ($fields as $field)
                            <th>{{ $field['label'] }}</th>
                        @endforeach
                        <th>কারণ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($previewRows as $r)
                        <tr>
                            <td><span class="pill {{ $r['valid'] ? 'active' : 'due' }}">{{ $r['valid'] ? 'ঠিক আছে' : 'সমস্যা' }}</span></td>
                            @foreach ($fields as $field)
                                <td>{{ $r['data'][$field['key']] ?? '—' }}</td>
                            @endforeach
                            <td style="color:var(--bad);font-size:12px;">{{ $r['reason'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="row-actions" style="margin-top:14px;">
            <button class="btn-ghost" wire:click="backToUpload" type="button">শুরু থেকে</button>
            <button class="btn-primary" wire:click="runImport" type="button" @if($validCount === 0) disabled @endif>{{ $validCount }}টা সারি ইমপোর্ট করুন</button>
        </div>
    @endif

    {{-- ধাপ ৪: ফলাফল --}}
    @if ($step === 'done')
        <div class="stat-strip">
            <div class="stat-chip" style="--accent:var(--good)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 6 9 17l-5-5"/></svg></div><div><div class="sv">{{ $importedCount }}</div><div class="sl">সফলভাবে ইমপোর্ট হয়েছে</div></div></div>
            <div class="stat-chip" style="--accent:var(--bad)"><div class="sic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg></div><div><div class="sv">{{ $failedCount }}</div><div class="sl">ব্যর্থ হয়েছে</div></div></div>
        </div>

        @if (! empty($failedRows))
            <div class="table-card">
                <div class="list-head"><h3>যেসব সারি ইমপোর্ট হয়নি</h3></div>
                <table>
                    <thead>
                        <tr>
                            @foreach ($fields as $field)
                                <th>{{ $field['label'] }}</th>
                            @endforeach
                            <th>কারণ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($failedRows as $r)
                            <tr>
                                @foreach ($fields as $field)
                                    <td>{{ $r['data'][$field['key']] ?? '—' }}</td>
                                @endforeach
                                <td style="color:var(--bad);font-size:12px;">{{ $r['reason'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <button class="btn-primary" style="margin-top:16px;" wire:click="startOver" type="button">আরেকটা ফাইল ইমপোর্ট করুন</button>
    @endif
</div>
