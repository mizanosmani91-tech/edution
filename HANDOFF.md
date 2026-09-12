# Vitae CV Maker — হ্যান্ডওভার নোট

এই নোটটা পিসিতে নতুন Claude চ্যাটে পেস্ট করে দিলে ও সেখান থেকে কাজ চালিয়ে যেতে পারবে।

## প্রজেক্ট কী

"Vitae" — বাংলাদেশি ইউজারদের জন্য ১-ক্লিক CV মেকার অ্যাপ। ইউজার একটা আপলোড করা ডিজাইন মকআপ (৭টা স্ক্রিন: অনবোর্ডিং, হোম, টেমপ্লেট গ্যালারি, এডিটর, প্রিভিউ, সেটিংস) অনুসরণ করে বানানো হয়েছে।

## রিপো ও ব্রাঞ্চ

- **রিপো**: `mizanosmani91-tech/edution` (GitHub)
- **ব্রাঞ্চ**: `claude/simple-cv-maker-app-a9lkxg`
- **PR**: https://github.com/mizanosmani91-tech/edution/pull/1 (এখনো **draft**, merge করা হয়নি)

⚠️ এই রিপোটা মূলত একটা Laravel স্কুল-ম্যানেজমেন্ট অ্যাপ (`edution`)। CV মেকার তার ভেতরে সম্পূর্ণ আলাদা দুইটা সাব-ফোল্ডারে আছে, একে অপরের সাথে কোনো সম্পর্ক নেই:

## দুইটা ভার্সন আছে

### ১. `cv-maker/` — PWA (ওয়েব, ইতিমধ্যে লাইভ)
- HTML/CSS/JavaScript, কোনো ব্যাকএন্ড/সার্ভার ছাড়া
- localStorage-এ ডেটা (প্রোফাইল + একাধিক CV)
- **লাইভ লিংক**: https://vitae-cv-maker-git-claude-s-5a1cd7-mizanosmani91-8023s-projects.vercel.app
  - Vercel প্রজেক্ট নাম: `vitae-cv-maker` (Vercel team: `mizanosmani91-8023s-projects`)
  - এই ব্রাঞ্চে পুশ করলেই অটো-রিডিপ্লয় হয় (GitHub ইন্টিগ্রেশন সেট করা আছে)
  - Deployment protection বন্ধ করা আছে, তাই যেকেউ সরাসরি খুলতে পারবে (লগইন লাগবে না)
- ফোনে Chrome দিয়ে খুলে "Add to Home Screen" করলে PWA হিসেবে ইনস্টল হয়ে যায়
- PDF এক্সপোর্ট: jsPDF + html2canvas (vendor করা, CDN নির্ভরতা নেই)
- স্ট্যাটাস: **সম্পূর্ণ কাজ করছে, টেস্ট করা হয়েছে**

### ২. `vitae_cv_maker/` — Flutter নেটিভ Android অ্যাপ (এখনকার ফোকাস)
- Dart/Flutter, একই ডিজাইন/ফিচার PWA-এর মতো
- `shared_preferences` দিয়ে লোকাল স্টোরেজ (একই মডেল, সার্ভার ছাড়া)
- PDF: `pdf` + `printing` প্যাকেজ, বাংলা টেক্সটের জন্য `assets/fonts/NotoSansBengali.ttf` বান্ডল করা আছে
- শেয়ার: `printing` প্যাকেজের `Printing.sharePdf` (নেটিভ শেয়ার শীট)
- `flutter analyze` — ✅ ০ এরর
- **⚠️ এখনো APK বিল্ড করা হয়নি** — sandbox এনভায়রনমেন্টে Android SDK ডাউনলোড ব্লকড ছিল (নেটওয়ার্ক পলিসি), তাই কম্পাইল করে যাচাই করা যায়নি

## এখন কোথায় আছি

পিসিতে (Windows, VS Code) Flutter সেটআপ করা হচ্ছে যাতে সেখান থেকে APK বিল্ড করা যায়:

1. VPS-এ (SSH) প্রথমে চেষ্টা করা হয়েছিল, কিন্তু snap install permission (sudo) নেই বলে ম্যানুয়াল tar.xz ডাউনলোড পদ্ধতি ব্যবহার করা হয়
2. তারপর লোকাল PC-তে (Windows) সুইচ করা হয় — সেখানে Flutter আগে থেকে ইনস্টল করা ছিল কিন্তু ভার্সন পুরনো (Dart 3.12.2), প্রজেক্টের জন্য Dart ^3.13.3 লাগে
3. `flutter upgrade` চালানোর কথা বলা হয়েছিল
4. এখন ইউজার `D:\Jagotech\Client\JagoTech Project\Android Apps` ফোল্ডারে ক্লোন করছে

## পরবর্তী ধাপ (পিসিতে, PowerShell)

```powershell
cd "D:\Jagotech\Client\JagoTech Project\Android Apps"
git clone https://github.com/mizanosmani91-tech/edution.git
cd edution
git checkout claude/simple-cv-maker-app-a9lkxg
cd vitae_cv_maker
flutter pub get
flutter doctor
```

`flutter doctor` আউটপুট দেখে Android toolchain ঠিক আছে কিনা যাচাই করতে হবে (না থাকলে Android Studio ইনস্টল করে SDK কম্পোনেন্ট নামাতে হবে, `flutter doctor --android-licenses` দিয়ে লাইসেন্স accept করতে হবে)।

এরপর:
```powershell
flutter build apk --release
```
→ আউটপুট: `build\app\outputs\flutter-apk\app-release.apk`

ডিভাইসে টেস্ট করতে (USB debugging অন রেখে ফোন কানেক্ট করে):
```powershell
flutter run
```

## এরপর যা বাকি (ইউজারের চাওয়া অনুযায়ী, অগ্রাধিকার অনুসারে)

1. **APK বিল্ড ভেরিফাই করা** — উপরের কমান্ড দিয়ে, আসল ফোনে ইনস্টল করে সব স্ক্রিন/ফিচার টেস্ট করা
2. **কয়েন সিস্টেম** (ইউজার চেয়েছে): রিওয়ার্ডেড এড দেখে কয়েন রিচার্জ, Play Store দিয়ে কিনতে পারা
   - এর জন্য লাগবে: **Google Play Console** ডেভেলপার অ্যাকাউন্ট ($25 এককালীন, নিজের identity লাগবে) + **AdMob** অ্যাকাউন্ট (Play Console-এর সাথে লিংকড)
   - এই দুইটা অ্যাকাউন্ট ইউজারকেই বানাতে হবে (identity/payment-লিংকড, Claude করতে পারবে না)
   - অ্যাকাউন্ট/ID পাওয়ার পর কোড (রিওয়ার্ডেড এড ট্রিগার, কয়েন ব্যালেন্স, Play Billing ফ্লো) লেখা যাবে
3. **মাল্টি-ডিভাইস সিঙ্ক** (ঐচ্ছিক): এখন সব ডেটা এক ফোনেই সীমাবদ্ধ (localStorage/shared_preferences)। ক্রস-ডিভাইস সিঙ্ক চাইলে Firebase (Auth + Firestore) লাগবে — Supabase বাদ দেওয়া হয়েছিল কারণ ফ্রি টায়ারে ৭ দিন নিষ্ক্রিয় থাকলে প্রজেক্ট pause হয়ে যায় এবং ম্যানুয়াল Restore লাগে; Firebase-এর ফ্রি Spark প্ল্যান কখনো pause হয় না। Firebase সেটআপের ৪-ধাপের নির্দেশনা আগে দেওয়া হয়েছিল (console.firebase.google.com এ প্রজেক্ট বানানো, Authentication + Firestore চালু করা, Web app যোগ করে firebaseConfig কপি করা) — এটা ইউজারকেই করতে হবে (নিজের Google লগইন লাগে)।
4. Play Console-এ আপলোড ও পাবলিশ

## গুরুত্বপূর্ণ সিদ্ধান্ত যা আগে নেওয়া হয়েছে

- **কোনো ব্যাকএন্ড/সার্ভার/লগইন-পাসওয়ার্ড নেই** — সবকিছু লোকাল ডিভাইসে, প্রাইভেসি-ফার্স্ট ডিজাইন সিদ্ধান্ত
- Supabase ব্যবহার করা হয়নি (pause হওয়ার সমস্যার কারণে)
- Firebase MCP টুল না থাকায় Firebase সেটআপ নিজে করা সম্ভব হয়নি (ইউজারের Google লগইন লাগে, Claude Code-এর এই সেশন থেকে ব্রাউজার/অ্যাকাউন্ট এক্সেস করা যায় না)
- কয়েন/এড/IAP সিস্টেম যোগ করার আগে আগে অ্যাপটা publish/টেস্ট করে ফেলার সিদ্ধান্ত নেওয়া হয়েছিল

## PR মনিটরিং

এই সেশন PR #1-এ subscribe করা আছে (CI/রিভিউ ইভেন্ট মনিটর করছিল)। নতুন চ্যাট/সেশনে এই সাবস্ক্রিপশন থাকবে না — নতুন সেশনে দরকার হলে আবার subscribe করতে বলতে হবে।
