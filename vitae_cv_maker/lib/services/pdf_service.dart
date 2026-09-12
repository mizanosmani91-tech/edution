import 'dart:io';
import 'dart:typed_data';
import 'package:flutter/services.dart' show rootBundle;
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import '../models/cv_models.dart';

class PdfService {
  static pw.Font? _regular;

  static Future<pw.Font> _font() async {
    if (_regular != null) return _regular!;
    final bytes = await rootBundle.load('assets/fonts/NotoSansBengali.ttf');
    _regular = pw.Font.ttf(bytes);
    return _regular!;
  }

  static Future<Uint8List> build(CvRecord cv) async {
    final font = await _font();
    final tpl = templateById(cv.template);
    final accent = PdfColor.fromInt(int.parse(tpl.accentHex));
    final d = cv.data;

    final doc = pw.Document();
    pw.MemoryImage? photo;
    if (d.photoPath.isNotEmpty && File(d.photoPath).existsSync()) {
      photo = pw.MemoryImage(File(d.photoPath).readAsBytesSync());
    }

    pw.Widget sectionTitle(String text) => pw.Container(
          margin: const pw.EdgeInsets.only(top: 14, bottom: 6),
          padding: const pw.EdgeInsets.only(bottom: 3),
          decoration: pw.BoxDecoration(border: pw.Border(bottom: pw.BorderSide(color: accent, width: 1.6))),
          child: pw.Text(text, style: pw.TextStyle(font: font, fontSize: 11, fontWeight: pw.FontWeight.bold, color: const PdfColor.fromInt(0xFF242C4D))),
        );

    final children = <pw.Widget>[
      pw.Row(
        crossAxisAlignment: pw.CrossAxisAlignment.center,
        children: [
          if (photo != null) ...[
            pw.ClipOval(child: pw.Image(photo, width: 56, height: 56, fit: pw.BoxFit.cover)),
            pw.SizedBox(width: 14),
          ],
          pw.Expanded(
            child: pw.Column(
              crossAxisAlignment: pw.CrossAxisAlignment.start,
              children: [
                pw.Text(d.name.isEmpty ? 'আপনার নাম' : d.name, style: pw.TextStyle(font: font, fontSize: 18, fontWeight: pw.FontWeight.bold)),
                if (d.title.isNotEmpty) pw.Text(d.title, style: pw.TextStyle(font: font, fontSize: 11, color: const PdfColor.fromInt(0xFF8890A6))),
                pw.SizedBox(height: 6),
                pw.Wrap(
                  spacing: 12,
                  children: [d.phone, d.email, d.address]
                      .where((s) => s.isNotEmpty)
                      .map((s) => pw.Text(s, style: pw.TextStyle(font: font, fontSize: 9, color: const PdfColor.fromInt(0xFF8890A6))))
                      .toList(),
                ),
              ],
            ),
          ),
        ],
      ),
    ];

    if (d.objective.isNotEmpty) {
      children.add(sectionTitle('ক্যারিয়ার অবজেক্টিভ'));
      children.add(pw.Text(d.objective, style: pw.TextStyle(font: font, fontSize: 10, lineSpacing: 3)));
    }

    if (d.education.isNotEmpty) {
      children.add(sectionTitle('শিক্ষাগত যোগ্যতা'));
      for (final e in d.education) {
        if (e.degree.isEmpty && e.institute.isEmpty) continue;
        children.add(pw.Container(
          margin: const pw.EdgeInsets.only(bottom: 6),
          child: pw.Column(
            crossAxisAlignment: pw.CrossAxisAlignment.start,
            children: [
              pw.Text('${e.degree}${e.result.isNotEmpty ? " — ${e.result}" : ""}', style: pw.TextStyle(font: font, fontSize: 10.5, fontWeight: pw.FontWeight.bold)),
              pw.Text([e.institute, e.board, e.year].where((s) => s.isNotEmpty).join(', '), style: pw.TextStyle(font: font, fontSize: 9, color: const PdfColor.fromInt(0xFF8890A6))),
            ],
          ),
        ));
      }
    }

    if (d.experience.isNotEmpty) {
      children.add(sectionTitle('কর্ম অভিজ্ঞতা'));
      for (final e in d.experience) {
        if (e.position.isEmpty && e.company.isEmpty) continue;
        children.add(pw.Container(
          margin: const pw.EdgeInsets.only(bottom: 7),
          child: pw.Column(
            crossAxisAlignment: pw.CrossAxisAlignment.start,
            children: [
              pw.Text('${e.position}${e.company.isNotEmpty ? " — ${e.company}" : ""}', style: pw.TextStyle(font: font, fontSize: 10.5, fontWeight: pw.FontWeight.bold)),
              if (e.duration.isNotEmpty) pw.Text(e.duration, style: pw.TextStyle(font: font, fontSize: 9, color: const PdfColor.fromInt(0xFF8890A6))),
              if (e.description.isNotEmpty) pw.Text(e.description, style: pw.TextStyle(font: font, fontSize: 9.5, lineSpacing: 2)),
            ],
          ),
        ));
      }
    }

    if (d.skills.isNotEmpty) {
      children.add(sectionTitle('দক্ষতা'));
      children.add(pw.Wrap(
        spacing: 6, runSpacing: 6,
        children: d.skills
            .map((s) => pw.Container(
                  padding: const pw.EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                  decoration: pw.BoxDecoration(color: const PdfColor.fromInt(0xFFF1F5F9), borderRadius: pw.BorderRadius.circular(100)),
                  child: pw.Text(s, style: pw.TextStyle(font: font, fontSize: 9)),
                ))
            .toList(),
      ));
    }

    final langs = d.languages.where((l) => l.name.isNotEmpty).toList();
    if (langs.isNotEmpty) {
      children.add(sectionTitle('ভাষা দক্ষতা'));
      children.add(pw.Wrap(
        spacing: 6, runSpacing: 6,
        children: langs
            .map((l) => pw.Container(
                  padding: const pw.EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                  decoration: pw.BoxDecoration(color: const PdfColor.fromInt(0xFFF1F5F9), borderRadius: pw.BorderRadius.circular(100)),
                  child: pw.Text('${l.name}${l.level.isNotEmpty ? " (${l.level})" : ""}', style: pw.TextStyle(font: font, fontSize: 9)),
                ))
            .toList(),
      ));
    }

    final certs = d.certifications.where((c) => c.title.isNotEmpty).toList();
    if (certs.isNotEmpty) {
      children.add(sectionTitle('প্রশিক্ষণ / সার্টিফিকেট'));
      for (final c in certs) {
        children.add(pw.Container(
          margin: const pw.EdgeInsets.only(bottom: 6),
          child: pw.Column(
            crossAxisAlignment: pw.CrossAxisAlignment.start,
            children: [
              pw.Text(c.title, style: pw.TextStyle(font: font, fontSize: 10.5, fontWeight: pw.FontWeight.bold)),
              pw.Text([c.issuer, c.year].where((s) => s.isNotEmpty).join(', '), style: pw.TextStyle(font: font, fontSize: 9, color: const PdfColor.fromInt(0xFF8890A6))),
            ],
          ),
        ));
      }
    }

    if (d.refOnRequest) {
      children.add(sectionTitle('রেফারেন্স'));
      children.add(pw.Text('অনুরোধ সাপেক্ষে প্রদান করা হবে।', style: pw.TextStyle(font: font, fontSize: 10)));
    } else {
      final refs = d.references.where((r) => r.name.isNotEmpty).toList();
      if (refs.isNotEmpty) {
        children.add(sectionTitle('রেফারেন্স'));
        for (final r in refs) {
          children.add(pw.Container(
            margin: const pw.EdgeInsets.only(bottom: 6),
            child: pw.Column(
              crossAxisAlignment: pw.CrossAxisAlignment.start,
              children: [
                pw.Text(r.name, style: pw.TextStyle(font: font, fontSize: 10.5, fontWeight: pw.FontWeight.bold)),
                pw.Text([r.position, r.contact].where((s) => s.isNotEmpty).join(' • '), style: pw.TextStyle(font: font, fontSize: 9, color: const PdfColor.fromInt(0xFF8890A6))),
              ],
            ),
          ));
        }
      }
    }

    doc.addPage(
      pw.MultiPage(
        pageFormat: PdfPageFormat.a4,
        margin: const pw.EdgeInsets.all(28),
        build: (context) => children,
      ),
    );

    return doc.save();
  }
}
