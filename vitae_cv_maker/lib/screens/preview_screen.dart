import 'package:flutter/material.dart';
import 'package:printing/printing.dart';
import 'package:provider/provider.dart';
import '../models/cv_models.dart';
import '../services/pdf_service.dart';
import '../state/app_state.dart';
import '../theme/app_theme.dart';
import 'editor_screen.dart';
import 'template_screen.dart';

class PreviewScreen extends StatelessWidget {
  const PreviewScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final app = context.watch<AppState>();
    final cv = app.activeCv;
    if (cv == null) {
      WidgetsBinding.instance.addPostFrameCallback((_) => Navigator.of(context).pop());
      return const SizedBox.shrink();
    }
    final tpl = templateById(cv.template);
    final fileBase = (cv.data.name.isEmpty ? 'cv' : cv.data.name).trim().replaceAll(RegExp(r'\s+'), '_');
    final fileName = '${fileBase}_CV.pdf';

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 16), onPressed: () => Navigator.of(context).pop()),
        title: Text(tpl.name, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.inkMuted)),
        actions: [
          IconButton(
            icon: const Icon(Icons.grid_view_rounded),
            onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const TemplateScreen(mode: TemplateScreenMode.changeThenPreview))),
          ),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: PdfPreview(
              key: ValueKey('${cv.id}-${cv.updatedAt}'),
              build: (format) => PdfService.build(cv),
              canChangeOrientation: false,
              canChangePageFormat: false,
              canDebug: false,
              allowPrinting: false,
              allowSharing: false,
              useActions: false,
              maxPageWidth: 560,
              scrollViewDecoration: const BoxDecoration(color: AppColors.bg),
            ),
          ),
          Container(
            color: AppColors.bg,
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
            child: Row(
              children: [
                IconButton.filledTonal(
                  onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const EditorScreen())),
                  icon: const Icon(Icons.edit_outlined),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () async {
                      if (cv.data.name.isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('⚠️ প্রথমে নাম লিখুন')));
                        return;
                      }
                      final bytes = await PdfService.build(cv);
                      if (!context.mounted) return;
                      await Printing.layoutPdf(onLayout: (_) async => bytes, name: fileName);
                    },
                    icon: const Icon(Icons.download_rounded, size: 18),
                    label: const Text('ডাউনলোড'),
                    style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14)),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  flex: 2,
                  child: ElevatedButton.icon(
                    onPressed: () async {
                      if (cv.data.name.isEmpty) {
                        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('⚠️ প্রথমে নাম লিখুন')));
                        return;
                      }
                      final bytes = await PdfService.build(cv);
                      await Printing.sharePdf(bytes: bytes, filename: fileName);
                    },
                    icon: const Icon(Icons.ios_share_rounded, size: 18),
                    label: const Text('শেয়ার করুন', style: TextStyle(fontWeight: FontWeight.w700)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.mint, foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
