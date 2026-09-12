import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../state/app_state.dart';
import '../theme/app_theme.dart';
import '../widgets/vitae_bottom_nav.dart';
import 'home_screen.dart';
import 'onboarding_screen.dart';
import 'template_screen.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final app = context.watch<AppState>();
    final name = app.profile?.name ?? '—';
    final initial = name.isNotEmpty ? name.substring(0, 1).toUpperCase() : '?';

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 16), onPressed: () => Navigator.of(context).pop()),
        title: const Text('সেটিংস', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800)),
      ),
      body: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), border: Border.all(color: AppColors.line)),
              child: Row(
                children: [
                  Container(
                    width: 48, height: 48,
                    decoration: BoxDecoration(borderRadius: BorderRadius.circular(12), gradient: const LinearGradient(colors: [AppColors.coral, Color(0xFFFF8A70)])),
                    alignment: Alignment.center,
                    child: Text(initial, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 16)),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(name, style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800)),
                        const Text('লোকাল প্রোফাইল · এই ফোনেই সেভ থাকে', style: TextStyle(fontSize: 10.5, color: AppColors.inkMuted)),
                      ],
                    ),
                  ),
                  TextButton(
                    onPressed: () async {
                      final controller = TextEditingController(text: name);
                      final result = await showDialog<String>(
                        context: context,
                        builder: (ctx) => AlertDialog(
                          title: const Text('আপনার নাম'),
                          content: TextField(controller: controller, autofocus: true),
                          actions: [
                            TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('বাতিল')),
                            TextButton(onPressed: () => Navigator.pop(ctx, controller.text.trim()), child: const Text('সেভ')),
                          ],
                        ),
                      );
                      if (result != null && result.isNotEmpty) {
                        await context.read<AppState>().setProfileName(result);
                      }
                    },
                    child: const Text('এডিট', style: TextStyle(color: AppColors.mint, fontWeight: FontWeight.w700)),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            Align(alignment: Alignment.centerLeft, child: Text('ডেটা', style: TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: AppColors.inkSoft, letterSpacing: 0.5))),
            const SizedBox(height: 9),
            Container(
              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14), border: Border.all(color: AppColors.line)),
              child: Column(
                children: [
                  _row(Icons.description_outlined, AppColors.mintDim, AppColors.mintDark, 'মোট সংরক্ষিত CV', value: '${app.cvs.length}'),
                  const Divider(height: 1, color: AppColors.line),
                  _row(Icons.lock_outline_rounded, const Color(0xFFF0F7FF), const Color(0xFF2E6FE0), 'প্রাইভেসি', value: 'কোনো সার্ভার নেই'),
                ],
              ),
            ),
            const Spacer(),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: () async {
                  final ok = await showDialog<bool>(
                    context: context,
                    builder: (ctx) => AlertDialog(
                      title: const Text('এই ফোন থেকে আপনার প্রোফাইল ও সব CV মুছে ফেলা হবে। নিশ্চিত?'),
                      actions: [
                        TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('বাতিল')),
                        TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('মুছুন', style: TextStyle(color: AppColors.danger))),
                      ],
                    ),
                  );
                  if (ok == true && context.mounted) {
                    await context.read<AppState>().logout();
                    if (context.mounted) {
                      Navigator.of(context).pushAndRemoveUntil(MaterialPageRoute(builder: (_) => const OnboardingScreen()), (route) => false);
                    }
                  }
                },
                icon: const Icon(Icons.logout_rounded, size: 16, color: AppColors.danger),
                label: const Text('এই ফোন থেকে প্রোফাইল ও সব CV মুছে ফেলুন', style: TextStyle(color: AppColors.danger, fontWeight: FontWeight.w700, fontSize: 12.5)),
                style: OutlinedButton.styleFrom(backgroundColor: AppColors.coralDim, side: BorderSide.none, padding: const EdgeInsets.symmetric(vertical: 13)),
              ),
            ),
            const SizedBox(height: 10),
            const Text('Vitae CV Maker v1.0', style: TextStyle(fontSize: 10.5, color: AppColors.inkSoft)),
          ],
        ),
      ),
      bottomNavigationBar: VitaeBottomNav(
        currentIndex: 2,
        onHome: () => Navigator.of(context).pushAndRemoveUntil(MaterialPageRoute(builder: (_) => const HomeScreen()), (r) => false),
        onTemplates: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const TemplateScreen(mode: TemplateScreenMode.createNew))),
        onNew: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const TemplateScreen(mode: TemplateScreenMode.createNew))),
        onSettings: () {},
      ),
    );
  }

  Widget _row(IconData icon, Color bg, Color fg, String label, {required String value}) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      child: Row(
        children: [
          Container(width: 32, height: 32, decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(9)), child: Icon(icon, size: 15, color: fg)),
          const SizedBox(width: 12),
          Expanded(child: Text(label, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700))),
          Text(value, style: const TextStyle(fontSize: 11, color: AppColors.inkMuted, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}
