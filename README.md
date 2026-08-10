# Laravel Multi-Tenant Security — Edution Migration

এই ফোল্ডারটা আপনার Next.js+Supabase (RLS) → Laravel+Postgres migration-এর জন্য
সিকিউরিটি বেসলাইন। লক্ষ্য: Supabase RLS যে গ্যারান্টি দেয়, সেটাই (বা আরও শক্ত)
Laravel-এ ধরে রাখা — **defense in depth**, একটা layer না, তিনটা layer।

## তিন লেয়ার প্রোটেকশন (একটা ফেল করলে আরেকটা ধরে রাখে)

1. **Postgres RLS (DB লেভেল)** — `database/migrations/xxxx_enable_rls_template.sql`
   এখনকার RLS policy গুলো Postgres-এই থেকে যায়। শুধু `auth.uid()`-এর বদলে
   Laravel থেকে সেট করা session variable (`app.current_institution_id`) ব্যবহার
   করবে। **এটাই মূল প্রোটেকশন — app কোডে বাগ থাকলেও DB নিজে আটকাবে।**

2. **Eloquent Global Scope (App লেভেল)** — `app/Models/Concerns/BelongsToTenant.php`
   প্রতিটা tenant-বাউন্ড মডেলে (Student, Teacher, Exam...) এই trait লাগালে
   প্রতিটা query অটোমেটিক `institution_id` ফিল্টার নিয়ে চলে। RLS ফেল করলেও
   (misconfiguration ইত্যাদি) এটা দ্বিতীয় ব্যাকস্টপ।

3. **Middleware (Request লেভেল)** — `app/Http/Middleware/SetTenantContext.php`
   প্রতিটা request-এ logged-in user-এর institution বের করে, Postgres session
   variable-এ সেট করে (লেয়ার ১-এর জন্য দরকার) এবং app-এ bind করে (লেয়ার ২-এর
   জন্য দরকার)।

## কেন তিন লেয়ার, একটা না?

Supabase RLS একাই যথেষ্ট ছিল কারণ সেটা bypass করা কঠিন। কিন্তু pure app-level
(Laravel scope) নির্ভরতা ঝুঁকিপূর্ণ কারণ মানুষ ভুল করে (raw query, `withoutGlobalScope()`
ভুলে ব্যবহার, নতুন developer প্যাটার্ন না জানা)। তিন লেয়ার রাখলে একটা মিস হলেও
বাকি দুইটা ধরে রাখে — এটাই RLS-এর "automatic" সুবিধার কাছাকাছি পৌঁছানোর উপায়।

## অবশ্যই করণীয় (checklist)

- [ ] প্রতিটা নতুন tenant-বাউন্ড মডেলে `BelongsToTenant` trait লাগান
- [ ] প্রতিটা টেবিলে RLS policy enable আছে কিনা যাচাই করুন (`database/migrations/`)
- [ ] কোনো raw `DB::table()` বা `DB::select()` ব্যবহারের আগে ম্যানুয়ালি
      `institution_id` ফিল্টার যোগ করেছেন কিনা দুইবার চেক করুন — এগুলো
      global scope বাইপাস করে
- [ ] **Validation rule `exists:table,column` এবং `unique:table,column` — এগুলোও
      global scope বাইপাস করে** (এরা সরাসরি DB টেবিলে query করে, Eloquent এর
      মধ্য দিয়ে না)। অন্য tenant-বাউন্ড টেবিলের ID রেফার করা মাত্র request
      field-এ (যেমন `student_id`, `class_id`) `Rule::exists('table', 'id')
      ->where('institution_id', app('tenant.institution_id'))` ব্যবহার করুন,
      প্লেইন স্ট্রিং `'exists:table,id'` না। (FeeCollectionController দেখুন উদাহরণ হিসেবে।)
- [ ] `tests/Feature/TenantIsolationTest.php` প্রতিটা নতুন মডিউলের জন্য এক্সটেন্ড করুন
- [ ] CI/deploy pipeline-এ এই test suite বাধ্যতামূলক রাখুন — ফেল করলে deploy আটকাবে

## ⚠️ গুরুত্বপূর্ণ ফিক্স — UUID Primary Key

আসল `029_exam_result_weightings.sql` migration দেখে ধরা পড়ল, আপনার real schema
সব জায়গায় `uuid` primary key ব্যবহার করে (Supabase কনভেনশন)। শুরুতে আমি ভুলবশত
Laravel-এর ডিফল্ট bigint auto-increment ব্যবহার করেছিলাম — এখন সব মডেল/migration
`uuid` এ কনভার্ট করা হয়েছে (`UuidPrimaryKey` trait দেখুন)। এটা মাথায় রাখুন:
কোনো নতুন মডেল বানালে অবশ্যই `use UuidPrimaryKey;` যোগ করবেন, নাহলে সেই মডেলের
FK অন্য (uuid) টেবিলের সাথে টাইপ মিসম্যাচ করবে।

## Exam Weighting — কী পোর্ট হয়েছে

`2026_01_01_000012_ported_029_exam_result_weightings.sql` — আপনার আসল ৪টা
ফাংশন এখানে। তিনটা (`get_own_exam_subject_marks`, `compute_exam_subject_result`,
`get_effective_exam_marks`) **হুবহু অপরিবর্তিত** — এগুলো `auth.uid()` ব্যবহার
করত না, তাই পোর্ট করার দরকারই হয়নি। শুধু `is_user_institution_admin()`
পরিবর্তিত — Supabase `auth.uid()`/`profiles` এর বদলে এখন Laravel এর session
variable (`app.current_user_id`) ও `users` টেবিল পড়ে।

`ExamResultService` (app/Services/) এই ফাংশনগুলো `DB::select()` দিয়ে কল করে —
PHP-তে রিরাইট করা হয়নি, কারণ এটা প্রমাণিত রিকার্সিভ লজিক, নতুন করে লিখলে
সূক্ষ্ম বাগের ঝুঁকি ছিল।

## Frontend + মোবাইল অ্যাপ প্ল্যান

- **Web UI:** Livewire + Tailwind (session auth)। সব list/form পেজ বানানো:
  StudentList, TeacherList, FeeCollectionList (inline payment modal),
  AttendanceTaker (bulk tap-to-mark), ChatWindow (polling), RoutineBoard
  (day-tab UI), InstitutionSettingsForm (toggle switches)।
- **Layout:** `resources/views/components/layouts/app.blade.php` — ডেস্কটপে
  sidebar, মোবাইলে bottom navigation (৫টা প্রধান আইকন)। প্রতিটা Livewire
  কম্পোনেন্ট `->layout('components.layouts.app')` দিয়ে এই শেল ব্যবহার করে।
- **রেসপন্সিভ কৌশল:** প্রতিটা list এ মোবাইলে card-stack, `md:` ব্রেকপয়েন্টের
  উপরে টেবিল — দুইটা আলাদা markup (CSS দিয়ে টেবিলকে "কার্ড বানানো" hacky হয়)।
  ফর্ম/মোডাল মোবাইলে bottom-sheet স্টাইল, ডেস্কটপে center modal।
  Attendance এ ৪৪px+ tap target (thumb-friendly), sticky সেভ বাটন।
- **মোবাইল অ্যাপ (ভবিষ্যতে):** একই Controller গুলো `routes/api.php` দিয়ে
  Sanctum token auth-এ এক্সপোজ করা — `sanctum-setup-notes.php` দেখুন install
  steps এর জন্য। নতুন ওয়েব ফিচার বানালে `routes/api.php`-তেও যোগ করতে ভুলবেন
  না, নাহলে মোবাইল অ্যাপ সেটা পাবে না।
- Livewire ইনস্টল: `composer require livewire/livewire`
- Tailwind ইনস্টল/কনফিগার Laravel এর সাথে: `resources/css/app.css` এ
  `@import "tailwindcss";` (Tailwind v4) বা v3 হলে `npm install -D tailwindcss`
  + `tailwind.config.js` কনফিগার — এই স্ক্যাফোল্ডে config ফাইল দেওয়া হয়নি,
  `composer create-project` করার পর `php artisan install:api` বা Laravel এর
  starter kit অনুযায়ী সেটআপ করবেন।

## নতুন যোগ হওয়া মডিউল (এই রাউন্ডে)

- **File Upload:** `FileUploadService` — institution-namespaced path (`institutions/{id}/{category}/{uuid}.ext`),
  size/mime validation, cross-tenant delete protection। `PhotoUpload` reusable Livewire কম্পোনেন্ট।
- **Guardian Portal:** multi-child selector, `User::children()` এর মধ্য দিয়েই সব query (নিজের সন্তান ছাড়া
  আর কারো ডেটা দেখা technically সম্ভবই না)। attendance summary + fee due।
- **Teacher Portal:** `users.teacher_id` লিংক কলাম দিয়ে নিজের routine + pending mark entry।
- **Student Portal:** `users.student_id` লিংক কলাম দিয়ে নিজের attendance, শুধু **published** exam এর ফলাফল, বকেয়া ফি।
- **Superadmin Panel:** `EnsureSuperAdmin` middleware (আলাদা RLS session var `app.is_superadmin`),
  Institution list + trial status, `institution_payments` pending approval workflow।
  ⚠️ `InstitutionPayment` মডেল `BelongsToTenant` ব্যবহার করে না — কাস্টম scope, কারণ superadmin কে
  সব institution জুড়ে দেখতে হয়।
- **PDF Generation:** `barryvdh/laravel-dompdf` দিয়ে class marksheet, individual marksheet (ownership
  চেক সহ — guardian শুধু নিজের সন্তানের, student শুধু নিজের), admit card (২টা/পেজ, কাটার জন্য)।
  Print orientation (`portrait`/`landscape`) প্যারামিটার সাপোর্ট করে।

**নতুন কম্পোজার ডিপেন্ডেন্সি:** `composer require barryvdh/laravel-dompdf`

## এই রাউন্ডে যোগ হওয়া সব ফিচার (A to Z সম্পূর্ণ)

- **White-labeling:** `institution_settings.theme_primary_color/theme_accent_color`, layout-এ
  `[data-institution-theme]` স্কোপড CSS variable হিসেবে inject, settings ফর্মে color picker।
- **Notifications:** `app_notifications` টেবিল (owner-only RLS — শুধু institution না, নিজের
  notification ছাড়া অন্য কারোটা দেখা যায় না)। Trigger পয়েন্ট: attendance absent, exam publish,
  fee overdue (cron command `edution:notify-overdue-fees`)। `NotificationBell` Livewire — layout এ
  বসানো, unread badge সহ।
- **Data Export (CSV):** Students/Attendance/Fees — নেটিভ streaming (কোনো composer package লাগেনি),
  UTF-8 BOM দেওয়া আছে যেন এক্সেলে বাংলা ঠিকভাবে দেখায়।
- **Bulk Import (CSV):** Students — row-by-row validation, partial success রিপোর্ট করে (পুরো ফাইল
  ব্যর্থ হয় না একটা সারিতে ভুল থাকলে), class/section নাম দিয়ে খোঁজে (tenant-scoped)।
- **Print Orientation:** Class marksheet + admit card দুটোতেই portrait/landscape প্যারামিটার।
- **Dashboard Analytics:** `DashboardStats` widget — মোট ছাত্র/শিক্ষক, আজকের attendance rate,
  এই মাসের কালেকশন।
- **Chat File/Image Sharing:** `messages.attachment_path/attachment_type`, ছবি ইনলাইন প্রিভিউ,
  অন্য ফাইল ডাউনলোড লিংক।
- **Leave Request Workflow:** `leave_requests` টেবিল — guardian portal থেকে আবেদন,
  admin/teacher approve করলে **অটোমেটিক attendance status='leave' বসে যায়** (Carbon period লুপ)।
- **Fee Fine ফিল্ড:** `fine_amount`/`fine_reason`, due amount ক্যালকুলেশনে যোগ হয়।
- **Exam Publish Workflow:** `ExamController::publish()` — publish করলেই notification যায়,
  আর তখনই student/guardian portal-এ ফলাফল দেখা যায় (আগে না)।

**নতুন migration/RLS ফাইল:** `theme_settings`, `app_notifications`, `leave_requests`,
`fine_to_fee_collections`, `attachment_to_messages` — সব যথাযথ RLS সহ।

## ফাইল ম্যাপ (এখন যা যা আছে)

```
app/Models/Institution.php          → tenant root (scope নেই, subdomain resolve করে)
app/Models/User.php                 → ইচ্ছাকৃতভাবে scope নেই (login chicken-egg এড়াতে)
app/Models/Student.php              → রেফারেন্স: BelongsToTenant ব্যবহারের উদাহরণ
app/Http/Controllers/Auth/LoginController.php  → subdomain-scoped login, rate limiting
app/Http/Controllers/StudentController.php     → রেফারেন্স: institution_id কখনো request থেকে নেয়নি
app/Http/Middleware/SetTenantContext.php       → লেয়ার ৩: RLS session var + app binding সেট করে
app/Models/Concerns/BelongsToTenant.php        → লেয়ার ২: global scope, fail-closed
database/migrations/2026_01_01_..._foundation.php → institutions/users/students টেবিল
database/migrations/..._enable_rls_template.sql   → লেয়ার ১: RLS policy টেমপ্লেট
routes/web.php                       → সঠিক middleware order দেখাচ্ছে (auth → tenant.context)
bootstrap-app-middleware-snippet.php → middleware রেজিস্ট্রেশন + DB role নোট
tests/Feature/TenantIsolationTest.php → গার্ডরেল টেস্ট suite
```

## সেটআপ ধাপ (ক্রম অনুযায়ী)

1. লোকালি নতুন প্রজেক্ট বানান: `composer create-project laravel/laravel edution-laravel`
2. উপরের ফাইলগুলো একই path-এ কপি করুন
3. `.env`-এ Postgres কানেকশন দিন — **`DB_USERNAME` যেন superuser না হয়** (নিচে নোট দেখুন)
4. `bootstrap-app-middleware-snippet.php` অনুযায়ী middleware রেজিস্টার করুন
5. `EnsureSuperAdmin` middleware নিজে বানান (ছোট — শুধু `$request->user()->isSuperAdmin()` চেক করে 403/next)
6. `php artisan migrate` চালিয়ে foundation টেবিল বানান
7. `enable_rls_template.sql` টা `students` টেবিলে চালান (পরে প্রতিটা নতুন tenant টেবিলে কপি করবেন)
8. `php artisan test --filter=TenantIsolationTest` রান করে isolation যাচাই করুন — এটা pass না করা পর্যন্ত সামনে এগোবেন না

## এরপর কী (module migration অর্ডার — recommend)

1. ✅ Institutions + Users + Auth (এই স্ক্যাফোল্ডে আছে)
2. ✅ Students (রেফারেন্স module হিসেবে আছে — এই প্যাটার্নটাই বাকি সব মডিউলে কপি করুন)
3. Teachers (Students-এর মতোই structure)
4. Exams + exam_result_weightings + এর ৪টা PostgreSQL function (RLS-friendly ভাবে পোর্ট করা লাগবে — এগুলো জটিল, আলাদা সেশনে বসে করা ভালো)
5. Fees, Attendance
6. Chat system (Realtime বাদ, polling দিয়ে সিমুলেট করতে হবে)
