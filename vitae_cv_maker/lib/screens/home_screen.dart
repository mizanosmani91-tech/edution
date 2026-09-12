import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/cv_models.dart';
import '../state/app_state.dart';
import '../theme/app_theme.dart';
import '../widgets/vitae_bottom_nav.dart';
import 'template_screen.dart';
import 'editor_screen.dart';
import 'settings_screen.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({super.key});

  String _greet() {
    final h = DateTime.now().hour;
    if (h < 12) return 'শুভ সকাল 👋';
    if (h < 17) return 'শুভ অপরাহ্ন 👋';
    return 'শুভ সন্ধ্যা 👋';
  }

  String _timeAgo(int ts) {
    final diff = DateTime.now().millisecondsSinceEpoch - ts;
    final min = diff ~/ 60000;
    if (min < 1) return 'এইমাত্র';
    if (min < 60) return '$min মিনিট আগে';
    final hr = min ~/ 60;
    if (hr < 24) return '$hr ঘণ্টা আগে';
    final day = hr ~/ 24;
    if (day < 30) return '$day দিন আগে';
    final dt = DateTime.fromMillisecondsSinceEpoch(ts);
    return '${dt.day}/${dt.month}/${dt.year}';
  }

  void _goNew(BuildContext context) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => const TemplateScreen(mode: TemplateScreenMode.createNew)));
  }

  @override
  Widget build(BuildContext context) {
    final app = context.watch<AppState>();
    final name = app.profile?.name ?? 'অতিথি';
    final cvs = app.cvs;

    return Scaffold(
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(18, 8, 18, 0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(_greet(), style: const TextStyle(color: AppColors.inkMuted, fontSize: 12, fontWeight: FontWeight.w600)),
                      const SizedBox(height: 2),
                      Text(name, style: const TextStyle(color: AppColors.navy, fontSize: 19, fontWeight: FontWeight.w800)),
                    ],
                  ),
                  InkWell(
                    onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const SettingsScreen())),
                    child: Container(
                      width: 40, height: 40,
                      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), border: Border.all(color: AppColors.line)),
                      child: const Icon(Icons.person_outline_rounded, color: AppColors.navy, size: 19),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 18),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.fromLTRB(20, 18, 20, 16),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(22),
                  gradient: const LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [AppColors.navy, AppColors.navyDeep]),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('নতুন CV তৈরি করুন', style: TextStyle(color: Colors.white, fontSize: 15, fontWeight: FontWeight.w800)),
                    const SizedBox(height: 4),
                    Text('২ মিনিটে প্রফেশনাল সিভি বানিয়ে ফেলুন', style: TextStyle(color: Colors.white.withValues(alpha: 0.6), fontSize: 11.5)),
                    const SizedBox(height: 14),
                    ElevatedButton.icon(
                      onPressed: () => _goNew(context),
                      icon: const Icon(Icons.add_rounded, size: 16),
                      label: const Text('শুরু করুন', style: TextStyle(fontWeight: FontWeight.w700)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.mint, foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 18),
              Text('আমার সিভি (${cvs.length})', style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w800, color: AppColors.navy)),
              const SizedBox(height: 10),
              Expanded(
                child: cvs.isEmpty
                    ? const Center(
                        child: Padding(
                          padding: EdgeInsets.symmetric(horizontal: 20),
                          child: Text('এখনো কোনো CV তৈরি হয়নি। উপরের বাটনে চাপ দিয়ে প্রথমটা বানিয়ে ফেলুন।',
                              textAlign: TextAlign.center, style: TextStyle(color: AppColors.inkMuted, fontSize: 12.5)),
                        ),
                      )
                    : ListView.builder(
                        itemCount: cvs.length,
                        itemBuilder: (context, i) {
                          final sorted = [...cvs]..sort((a, b) => b.updatedAt.compareTo(a.updatedAt));
                          final cv = sorted[i];
                          final tpl = templateById(cv.template);
                          final title = cv.data.title.isNotEmpty ? cv.data.title : (cv.data.name.isNotEmpty ? cv.data.name : 'নতুন CV');
                          return _CvCard(
                            title: title,
                            tplName: tpl.name,
                            swatch: Color(int.parse(tpl.swatchHex)),
                            accent: Color(int.parse(tpl.accentHex)),
                            date: _timeAgo(cv.updatedAt),
                            onTap: () {
                              context.read<AppState>().activeId = cv.id;
                              Navigator.of(context).push(MaterialPageRoute(builder: (_) => const EditorScreen()));
                            },
                            onDelete: () async {
                              final ok = await showDialog<bool>(
                                context: context,
                                builder: (ctx) => AlertDialog(
                                  title: Text('"$title" মুছে ফেলবেন?'),
                                  actions: [
                                    TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('বাতিল')),
                                    TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('মুছুন', style: TextStyle(color: AppColors.danger))),
                                  ],
                                ),
                              );
                              if (ok == true) await context.read<AppState>().deleteCv(cv.id);
                            },
                          );
                        },
                      ),
              ),
            ],
          ),
        ),
      ),
      bottomNavigationBar: VitaeBottomNav(
        currentIndex: 0,
        onHome: () {},
        onTemplates: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const TemplateScreen(mode: TemplateScreenMode.createNew))),
        onNew: () => _goNew(context),
        onSettings: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const SettingsScreen())),
      ),
    );
  }
}

class _CvCard extends StatelessWidget {
  final String title, tplName, date;
  final Color swatch, accent;
  final VoidCallback onTap, onDelete;
  const _CvCard({required this.title, required this.tplName, required this.date, required this.swatch, required this.accent, required this.onTap, required this.onDelete});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: onTap,
          child: Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(borderRadius: BorderRadius.circular(16), border: Border.all(color: AppColors.line)),
            child: Row(
              children: [
                Container(
                  width: 44, height: 58,
                  decoration: BoxDecoration(color: swatch, borderRadius: BorderRadius.circular(8), border: Border.all(color: AppColors.line)),
                  padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 8),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(height: 3, width: double.infinity, color: accent, margin: const EdgeInsets.only(bottom: 6)),
                      Container(height: 3, width: 20, color: AppColors.inkSoft, margin: const EdgeInsets.only(bottom: 4)),
                      Container(height: 3, width: 24, color: AppColors.inkSoft),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(title, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.navy)),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                            decoration: BoxDecoration(color: AppColors.mintDim, borderRadius: BorderRadius.circular(100)),
                            child: Text(tplName, style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w700, color: AppColors.mintDark)),
                          ),
                          const SizedBox(width: 6),
                          Text(date, style: const TextStyle(fontSize: 10, color: AppColors.inkSoft)),
                        ],
                      ),
                    ],
                  ),
                ),
                IconButton(onPressed: onDelete, icon: const Icon(Icons.close_rounded, size: 16, color: AppColors.inkMuted)),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
