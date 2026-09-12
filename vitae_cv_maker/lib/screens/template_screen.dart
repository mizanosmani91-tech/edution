import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../models/cv_models.dart';
import '../state/app_state.dart';
import '../theme/app_theme.dart';
import 'editor_screen.dart';
import 'preview_screen.dart';

enum TemplateScreenMode { createNew, changeForActive, changeThenPreview }

class TemplateScreen extends StatelessWidget {
  final TemplateScreenMode mode;
  const TemplateScreen({super.key, required this.mode});

  @override
  Widget build(BuildContext context) {
    final app = context.watch<AppState>();
    final currentTplId = mode == TemplateScreenMode.createNew ? null : app.activeCv?.template;

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 16), onPressed: () => Navigator.of(context).pop()),
        title: const Text('টেমপ্লেট বেছে নিন', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800)),
      ),
      body: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 18),
        child: GridView.builder(
          padding: const EdgeInsets.only(top: 6, bottom: 20),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 2, mainAxisSpacing: 14, crossAxisSpacing: 14, childAspectRatio: 0.78),
          itemCount: kTemplates.length,
          itemBuilder: (context, i) {
            final tpl = kTemplates[i];
            final selected = tpl.id == currentTplId;
            return _TemplateCard(
              tpl: tpl,
              selected: selected,
              onTap: () async {
                final app = context.read<AppState>();
                if (mode == TemplateScreenMode.createNew) {
                  await app.createCv(tpl.id);
                  if (context.mounted) {
                    Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const EditorScreen()));
                  }
                } else {
                  await app.setActiveTemplate(tpl.id);
                  if (context.mounted) {
                    if (mode == TemplateScreenMode.changeThenPreview) {
                      Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const PreviewScreen()));
                    } else {
                      Navigator.of(context).pop();
                    }
                  }
                }
              },
            );
          },
        ),
      ),
    );
  }
}

class _TemplateCard extends StatelessWidget {
  final CvTemplate tpl;
  final bool selected;
  final VoidCallback onTap;
  const _TemplateCard({required this.tpl, required this.selected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final accent = Color(int.parse(tpl.accentHex));
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(9),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: selected ? AppColors.mint : AppColors.line, width: selected ? 1.5 : 1),
          boxShadow: selected ? [BoxShadow(color: AppColors.mint.withValues(alpha: 0.25), blurRadius: 0, spreadRadius: 3)] : null,
        ),
        child: Column(
          children: [
            Expanded(
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(color: Color(int.parse(tpl.swatchHex)), borderRadius: BorderRadius.circular(11)),
                child: Stack(
                  children: [
                    if (selected)
                      Positioned(
                        top: 0, left: 0,
                        child: Container(
                          width: 18, height: 18,
                          decoration: const BoxDecoration(color: AppColors.mint, shape: BoxShape.circle),
                          child: const Icon(Icons.check_rounded, size: 12, color: Colors.white),
                        ),
                      ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(width: 20, height: 20, margin: const EdgeInsets.only(bottom: 6), decoration: BoxDecoration(color: accent, shape: BoxShape.circle)),
                        Container(height: 4, width: 60, color: AppColors.navy, margin: const EdgeInsets.only(bottom: 4)),
                        Container(height: 3, width: 30, color: accent, margin: const EdgeInsets.only(bottom: 8)),
                        Container(height: 3, width: 50, color: const Color(0xFFD8DEEC), margin: const EdgeInsets.only(bottom: 4)),
                        Container(height: 3, width: 40, color: const Color(0xFFD8DEEC), margin: const EdgeInsets.only(bottom: 4)),
                        Container(height: 3, width: 55, color: const Color(0xFFD8DEEC)),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 6),
            Text(tpl.name, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700)),
          ],
        ),
      ),
    );
  }
}
