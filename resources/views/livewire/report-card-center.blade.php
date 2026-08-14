<div>
    <div class="page-head">
        <div>
            <div style="font-size:12px;color:var(--ink-soft);margin-bottom:2px;">পরীক্ষা ও ফলাফল / রিপোর্ট কার্ড ও প্রবেশপত্র</div>
            <h2>রিপোর্ট কার্ড / মার্কশিট ও প্রবেশপত্র</h2>
            <p>পরীক্ষা ও শ্রেণি নির্বাচন করে পুরো ক্লাসের PDF ডাউনলোড করুন</p>
        </div>
    </div>

    <div class="cert-form-card lifecycle-page" style="margin-bottom:16px;">
        <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:0 0 14px;">রিপোর্ট কার্ড / মার্কশিট</h3>
        <form method="GET" action="{{ route('marksheet.class') }}" target="_blank">
            <div class="info-grid" style="grid-template-columns:1fr 1fr 1fr;">
                <div class="field">
                    <label>পরীক্ষা <span class="req">*</span></label>
                    <select name="exam_id" required>
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($exams as $e)
                            <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->academic_year }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>শ্রেণি <span class="req">*</span></label>
                    <select name="class_id" required>
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>প্রিন্ট ওরিয়েন্টেশন</label>
                    <select name="orientation">
                        <option value="portrait">পোর্ট্রেট</option>
                        <option value="landscape">ল্যান্ডস্কেপ</option>
                    </select>
                </div>
            </div>
            <button class="btn-primary" type="submit">PDF ডাউনলোড করুন</button>
        </form>
    </div>

    <div class="cert-form-card lifecycle-page">
        <h3 style="font-family:'Tiro Bangla',serif;font-size:16px;margin:0 0 14px;">প্রবেশপত্র (Admit Card)</h3>
        <form method="GET" action="{{ route('admit-cards.class') }}" target="_blank">
            <div class="info-grid" style="grid-template-columns:1fr 1fr 1fr;">
                <div class="field">
                    <label>পরীক্ষা <span class="req">*</span></label>
                    <select name="exam_id" required>
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($exams as $e)
                            <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->academic_year }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>শ্রেণি <span class="req">*</span></label>
                    <select name="class_id" required>
                        <option value="">নির্বাচন করুন</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->full_label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>প্রিন্ট ওরিয়েন্টেশন</label>
                    <select name="orientation">
                        <option value="portrait">পোর্ট্রেট</option>
                        <option value="landscape">ল্যান্ডস্কেপ</option>
                    </select>
                </div>
            </div>
            <button class="btn-primary" type="submit">PDF ডাউনলোড করুন</button>
        </form>
    </div>
</div>
