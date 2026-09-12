# Vitae — CV Maker (Flutter)

নেটিভ Android অ্যাপ (Flutter/Dart) — আগের `cv-maker/` PWA-এর জায়গায়, একই Vitae ডিজাইন অনুসরণ করে।

## স্ক্রিন
অনবোর্ডিং (লোকাল প্রোফাইল, নাম দিয়ে শুরু) → হোম (একাধিক সেভ করা CV) → টেমপ্লেট গ্যালারি (৪টা) → অ্যাকর্ডিয়ন এডিটর → প্রিভিউ/এক্সপোর্ট (PDF) → সেটিংস।

## স্ট্যাক
- **State**: `provider` (ChangeNotifier) — `lib/state/app_state.dart`
- **Storage**: `shared_preferences` (JSON), মডেল `lib/models/cv_models.dart`
- **PDF**: `pdf` প্যাকেজ, বাংলা টেক্সটের জন্য `assets/fonts/NotoSansBengali.ttf` bundled
- **শেয়ার**: `printing` প্যাকেজের `Printing.sharePdf` — সরাসরি নেটিভ শেয়ার শীট খোলে
- **ছবি**: `image_picker`

## চালানো / বিল্ড করা
```bash
flutter pub get
flutter run                 # ডিভাইস/এমুলেটরে চালাতে
flutter build apk --release # রিলিজ APK বানাতে (signing key লাগবে)
```

**নোট**: এই প্রজেক্টটা লেখা হয়েছে একটা sandbox পরিবেশে যেখানে Android SDK ডাউনলোড করা যায়নি (নেটওয়ার্ক পলিসি ব্লক), তাই এখানে `flutter build apk` চালিয়ে সরাসরি যাচাই করা যায়নি। `flutter analyze` দিয়ে সম্পূর্ণ কোডবেস যাচাই করা হয়েছে (০ এরর)। Android SDK থাকা যেকোনো মেশিনে (বা GitHub Actions/Codemagic-এর মতো CI-তে) এটা `flutter build apk` দিয়ে সরাসরি কম্পাইল হবে।

## পরবর্তী ধাপ
- সাইনিং কীস্টোর বানিয়ে রিলিজ বিল্ড সাইন করা
- AdMob rewarded ads + Play Billing (কয়েন সিস্টেম) ইন্টিগ্রেশন
- Play Console-এ আপলোড
