import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../state/app_state.dart';
import '../theme/app_theme.dart';
import 'home_screen.dart';

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final _controller = TextEditingController();

  Future<void> _start() async {
    final name = _controller.text.trim();
    if (name.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('⚠️ নাম লিখুন')));
      return;
    }
    final app = context.read<AppState>();
    await app.setProfileName(name);
    if (!mounted) return;
    Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const HomeScreen()));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft, end: Alignment.bottomRight,
            colors: [AppColors.navy, AppColors.navyDeep],
          ),
        ),
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(26, 20, 26, 24),
            child: Column(
              children: [
                Row(
                  children: [
                    Container(
                      width: 32, height: 32,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(9),
                        gradient: const LinearGradient(colors: [AppColors.mint, Color(0xFF00A892)]),
                      ),
                      child: const Icon(Icons.badge_rounded, color: Colors.white, size: 17),
                    ),
                    const SizedBox(width: 9),
                    const Text('Vitae', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w800)),
                  ],
                ),
                const Spacer(),
                Icon(Icons.description_rounded, size: 96, color: Colors.white.withValues(alpha: 0.9)),
                const SizedBox(height: 24),
                const Text(
                  'নিজের সেরা সিভিটা\nতৈরি করুন',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.w800, height: 1.3),
                ),
                const SizedBox(height: 12),
                Text(
                  'প্রফেশনাল টেমপ্লেট, স্মার্ট সাজেশন আর এক-ক্লিক PDF এক্সপোর্ট — সব একটা অ্যাপে, সম্পূর্ণ অফলাইন',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: Colors.white.withValues(alpha: 0.65), fontSize: 13, height: 1.6),
                ),
                const Spacer(),
                Align(
                  alignment: Alignment.centerLeft,
                  child: Text('আপনার নাম লিখুন', style: TextStyle(color: Colors.white.withValues(alpha: 0.7), fontSize: 12, fontWeight: FontWeight.w700)),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: _controller,
                  style: const TextStyle(color: Colors.white),
                  onSubmitted: (_) => _start(),
                  decoration: InputDecoration(
                    hintText: 'যেমন: আরিফ হাসান',
                    hintStyle: TextStyle(color: Colors.white.withValues(alpha: 0.4)),
                    filled: true,
                    fillColor: Colors.white.withValues(alpha: 0.08),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.white.withValues(alpha: 0.2))),
                    enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: Colors.white.withValues(alpha: 0.2))),
                    focusedBorder: const OutlineInputBorder(borderRadius: BorderRadius.all(Radius.circular(12)), borderSide: BorderSide(color: AppColors.mint)),
                  ),
                ),
                const SizedBox(height: 18),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _start,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.mint,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 15),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                    ),
                    child: const Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [Text('শুরু করুন', style: TextStyle(fontWeight: FontWeight.w700)), SizedBox(width: 8), Icon(Icons.arrow_forward_rounded, size: 18)],
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                Text('কোনো পাসওয়ার্ড লাগবে না — সব তথ্য শুধু এই ফোনেই থাকবে।',
                    textAlign: TextAlign.center, style: TextStyle(color: Colors.white.withValues(alpha: 0.5), fontSize: 11)),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
