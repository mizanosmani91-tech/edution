import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:path_provider/path_provider.dart';
import 'package:provider/provider.dart';
import '../data/cv_library.dart';
import '../models/cv_models.dart';
import '../state/app_state.dart';
import '../theme/app_theme.dart';
import 'preview_screen.dart';

class EditorScreen extends StatefulWidget {
  const EditorScreen({super.key});

  @override
  State<EditorScreen> createState() => _EditorScreenState();
}

class _EditorScreenState extends State<EditorScreen> {
  final Set<String> _open = {'personal'};

  late final TextEditingController _name, _title, _phone, _email, _address, _dob, _nid, _objective, _skillInput;

  @override
  void initState() {
    super.initState();
    final d = _cv.data;
    _name = TextEditingController(text: d.name);
    _title = TextEditingController(text: d.title);
    _phone = TextEditingController(text: d.phone);
    _email = TextEditingController(text: d.email);
    _address = TextEditingController(text: d.address);
    _dob = TextEditingController(text: d.dob);
    _nid = TextEditingController(text: d.nid);
    _objective = TextEditingController(text: d.objective);
    _skillInput = TextEditingController();
  }

  CvRecord get _cv => context.read<AppState>().activeCv!;

  Future<void> _save() async {
    final d = _cv.data;
    d.name = _name.text;
    d.title = _title.text;
    d.phone = _phone.text;
    d.email = _email.text;
    d.address = _address.text;
    d.dob = _dob.text;
    d.nid = _nid.text;
    d.objective = _objective.text;
    await context.read<AppState>().touchActive();
  }

  double _progress(CvData d) {
    final checks = [
      d.name.isNotEmpty,
      d.title.isNotEmpty,
      d.phone.isNotEmpty || d.email.isNotEmpty,
      d.objective.isNotEmpty,
      d.education.isNotEmpty,
      d.experience.isNotEmpty,
      d.skills.isNotEmpty,
    ];
    return checks.where((c) => c).length / checks.length;
  }

  Future<void> _pickPhoto() async {
    final picker = ImagePicker();
    final file = await picker.pickImage(source: ImageSource.gallery, maxWidth: 600, maxHeight: 600, imageQuality: 80);
    if (file == null) return;
    final dir = await getApplicationDocumentsDirectory();
    final dest = '${dir.path}/${_cv.id}.jpg';
    await File(file.path).copy(dest);
    _cv.data.photoPath = dest;
    await context.read<AppState>().touchActive();
    setState(() {});
  }

  @override
  Widget build(BuildContext context) {
    final app = context.watch<AppState>();
    final cv = app.activeCv;
    if (cv == null) {
      WidgetsBinding.instance.addPostFrameCallback((_) => Navigator.of(context).pop());
      return const SizedBox.shrink();
    }
    final d = cv.data;
    final title = d.title.isNotEmpty ? d.title : (d.name.isNotEmpty ? d.name : 'নতুন CV');
    final pct = (_progress(d) * 100).round();

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 16), onPressed: () => Navigator.of(context).pop()),
        title: Text(title, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800)),
        actions: [
          IconButton(
            icon: const Icon(Icons.remove_red_eye_outlined),
            onPressed: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => const PreviewScreen())),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(18, 4, 18, 24),
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('প্রোফাইল সম্পূর্ণতা', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.inkMuted)),
              Text('$pct%', style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.mint)),
            ],
          ),
          const SizedBox(height: 7),
          ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: LinearProgressIndicator(value: pct / 100, minHeight: 7, backgroundColor: const Color(0xFFE7E9F2), color: AppColors.mint),
          ),
          const SizedBox(height: 16),

          _Accordion(
            id: 'personal', open: _open,
            iconBg: AppColors.mintDim, icon: Icons.person_outline_rounded, iconColor: AppColors.mintDark,
            title: 'ব্যক্তিগত তথ্য ও অবজেক্টিভ',
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                GestureDetector(
                  onTap: _pickPhoto,
                  child: Container(
                    width: 84, height: 84,
                    margin: const EdgeInsets.only(bottom: 10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFEAF7F4), shape: BoxShape.circle,
                      border: Border.all(color: AppColors.mint, width: 2, style: BorderStyle.solid),
                    ),
                    child: d.photoPath.isNotEmpty
                        ? ClipOval(child: Image.file(File(d.photoPath), fit: BoxFit.cover, width: 84, height: 84))
                        : const Icon(Icons.camera_alt_outlined, color: AppColors.mint, size: 26),
                  ),
                ),
                const Text('প্রোফাইল ছবি যোগ করুন', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: AppColors.inkMuted)),
                const SizedBox(height: 16),
                _field('পূর্ণ নাম *', _name, onChanged: (_) => _save()),
                _field('পদবি / টাইটেল', _title, onChanged: (_) => _save()),
                Row(children: [
                  Expanded(child: _field('মোবাইল', _phone, onChanged: (_) => _save())),
                  const SizedBox(width: 8),
                  Expanded(child: _field('ইমেইল', _email, onChanged: (_) => _save())),
                ]),
                _field('ঠিকানা', _address, onChanged: (_) => _save()),
                Row(children: [
                  Expanded(child: _field('জন্ম তারিখ', _dob, onChanged: (_) => _save())),
                  const SizedBox(width: 8),
                  Expanded(child: _field('NID/জন্মনিবন্ধন', _nid, onChanged: (_) => _save())),
                ]),
                Align(
                  alignment: Alignment.centerLeft,
                  child: Padding(
                    padding: const EdgeInsets.only(bottom: 6, top: 4),
                    child: Text('পেশার ক্যাটাগরি — বেছে নিলে অবজেক্টিভ অটো-ফিল হবে', style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w700, color: AppColors.inkMuted)),
                  ),
                ),
                DropdownButtonFormField<String>(
                  initialValue: d.category.isEmpty ? null : d.category,
                  isExpanded: true,
                  decoration: const InputDecoration(border: OutlineInputBorder()),
                  items: CvLibrary.categories.map((c) => DropdownMenuItem(value: c.id, child: Text(c.label))).toList(),
                  onChanged: (val) async {
                    if (val == null) return;
                    d.category = val;
                    final cat = CvLibrary.byId(val);
                    if (cat != null) {
                      if (d.objective.trim().isEmpty) {
                        d.objective = cat.objective;
                        _objective.text = cat.objective;
                      }
                      for (final s in cat.skills) {
                        if (!d.skills.contains(s)) d.skills.add(s);
                      }
                    }
                    await context.read<AppState>().touchActive();
                    setState(() {});
                  },
                ),
                const SizedBox(height: 12),
                _field('ক্যারিয়ার অবজেক্টিভ', _objective, maxLines: 4, onChanged: (_) => _save()),
              ],
            ),
          ),

          _Accordion(
            id: 'experience', open: _open,
            iconBg: AppColors.coralDim, icon: Icons.work_outline_rounded, iconColor: AppColors.danger,
            title: 'কর্ম অভিজ্ঞতা',
            child: Column(
              children: [
                ...d.experience.map((e) => _RepeatCard(
                      onRemove: () async {
                        d.experience.remove(e);
                        await context.read<AppState>().touchActive();
                        setState(() {});
                      },
                      children: [
                        _quickField('পদবি', e.position, (v) => e.position = v),
                        _quickField('প্রতিষ্ঠান', e.company, (v) => e.company = v),
                        _quickField('সময়কাল (যেমন: জানু ২০২২ - বর্তমান)', e.duration, (v) => e.duration = v),
                        _quickField('দায়িত্ব সংক্ষেপে', e.description, (v) => e.description = v, maxLines: 2),
                      ],
                    )),
                _addButton('আরেকটি অভিজ্ঞতা যোগ করুন', () async {
                  d.experience.add(ExperienceEntry());
                  await context.read<AppState>().touchActive();
                  setState(() {});
                }),
              ],
            ),
          ),

          _Accordion(
            id: 'education', open: _open,
            iconBg: const Color(0xFFEFF1FA), icon: Icons.school_outlined, iconColor: const Color(0xFF5B6489),
            title: 'শিক্ষাগত যোগ্যতা',
            child: Column(
              children: [
                ...d.education.map((e) => _RepeatCard(
                      onRemove: () async {
                        d.education.remove(e);
                        await context.read<AppState>().touchActive();
                        setState(() {});
                      },
                      children: [
                        _dropdownField('ডিগ্রি/পরীক্ষা', e.degree, CvLibrary.degrees, (v) => e.degree = v ?? ''),
                        _quickField('ফলাফল (GPA/Division)', e.result, (v) => e.result = v),
                        _quickField('প্রতিষ্ঠানের নাম', e.institute, (v) => e.institute = v),
                        Row(children: [
                          Expanded(child: _quickField('পাসের সাল', e.year, (v) => e.year = v)),
                          const SizedBox(width: 8),
                          Expanded(child: _quickField('বোর্ড/বিশ্ববিদ্যালয়', e.board, (v) => e.board = v)),
                        ]),
                      ],
                    )),
                _addButton('আরেকটি যোগ্যতা যোগ করুন', () async {
                  d.education.add(EducationEntry());
                  await context.read<AppState>().touchActive();
                  setState(() {});
                }),
              ],
            ),
          ),

          _Accordion(
            id: 'skills', open: _open,
            iconBg: const Color(0xFFFFF8E8), icon: Icons.star_border_rounded, iconColor: const Color(0xFFB8860B),
            title: 'দক্ষতা',
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (CvLibrary.byId(d.category) != null)
                  Wrap(
                    spacing: 6, runSpacing: 6,
                    children: CvLibrary.byId(d.category)!.skills.map((s) {
                      final selected = d.skills.contains(s);
                      return _Chip(
                        label: s, selected: selected,
                        onTap: () async {
                          if (selected) {
                            d.skills.remove(s);
                          } else {
                            d.skills.add(s);
                          }
                          await context.read<AppState>().touchActive();
                          setState(() {});
                        },
                      );
                    }).toList(),
                  ),
                const SizedBox(height: 10),
                TextField(
                  controller: _skillInput,
                  decoration: const InputDecoration(hintText: 'নিজের স্কিল লিখে Enter চাপুন', border: OutlineInputBorder()),
                  onSubmitted: (v) async {
                    final val = v.trim();
                    if (val.isNotEmpty && !d.skills.contains(val)) d.skills.add(val);
                    _skillInput.clear();
                    await context.read<AppState>().touchActive();
                    setState(() {});
                  },
                ),
                const SizedBox(height: 10),
                Wrap(
                  spacing: 6, runSpacing: 6,
                  children: d.skills.map((s) => _SelectedChip(
                        label: s,
                        onRemove: () async {
                          d.skills.remove(s);
                          await context.read<AppState>().touchActive();
                          setState(() {});
                        },
                      )).toList(),
                ),
              ],
            ),
          ),

          _Accordion(
            id: 'extra', open: _open,
            iconBg: const Color(0xFFF0F7FF), icon: Icons.language_rounded, iconColor: const Color(0xFF2E6FE0),
            title: 'ভাষা, সার্টিফিকেট ও রেফারেন্স',
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _subHead('ভাষা দক্ষতা'),
                ...d.languages.map((e) => _RepeatCard(
                      onRemove: () async { d.languages.remove(e); await context.read<AppState>().touchActive(); setState(() {}); },
                      children: [
                        _quickField('ভাষা', e.name, (v) => e.name = v),
                        _dropdownField('দক্ষতা', e.level, CvLibrary.languageLevels, (v) => e.level = v ?? ''),
                      ],
                    )),
                _addButton('ভাষা যোগ করুন', () async { d.languages.add(LanguageEntry()); await context.read<AppState>().touchActive(); setState(() {}); }),

                _subHead('প্রশিক্ষণ / সার্টিফিকেট'),
                ...d.certifications.map((e) => _RepeatCard(
                      onRemove: () async { d.certifications.remove(e); await context.read<AppState>().touchActive(); setState(() {}); },
                      children: [
                        _quickField('কোর্স/সার্টিফিকেট', e.title, (v) => e.title = v),
                        Row(children: [
                          Expanded(child: _quickField('প্রদানকারী', e.issuer, (v) => e.issuer = v)),
                          const SizedBox(width: 8),
                          Expanded(child: _quickField('সাল', e.year, (v) => e.year = v)),
                        ]),
                      ],
                    )),
                _addButton('সার্টিফিকেট যোগ করুন', () async { d.certifications.add(CertificationEntry()); await context.read<AppState>().touchActive(); setState(() {}); }),

                _subHead('রেফারেন্স'),
                CheckboxListTile(
                  contentPadding: EdgeInsets.zero,
                  controlAffinity: ListTileControlAffinity.leading,
                  value: d.refOnRequest,
                  title: const Text('"অনুরোধ সাপেক্ষে প্রদান করা হবে" লিখুন', style: TextStyle(fontSize: 12.5, color: AppColors.inkMuted)),
                  onChanged: (v) async { d.refOnRequest = v ?? false; await context.read<AppState>().touchActive(); setState(() {}); },
                ),
                ...d.references.map((e) => _RepeatCard(
                      onRemove: () async { d.references.remove(e); await context.read<AppState>().touchActive(); setState(() {}); },
                      children: [
                        _quickField('নাম', e.name, (v) => e.name = v),
                        _quickField('পদবি ও প্রতিষ্ঠান', e.position, (v) => e.position = v),
                        _quickField('ফোন/ইমেইল', e.contact, (v) => e.contact = v),
                      ],
                    )),
                _addButton('রেফারেন্স যোগ করুন', () async { d.references.add(ReferenceEntry()); await context.read<AppState>().touchActive(); setState(() {}); }),
              ],
            ),
          ),

          const SizedBox(height: 10),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () async {
                final ok = await showDialog<bool>(
                  context: context,
                  builder: (ctx) => AlertDialog(
                    title: const Text('এই CV পুরোপুরি মুছে ফেলবেন?'),
                    actions: [
                      TextButton(onPressed: () => Navigator.pop(ctx, false), child: const Text('বাতিল')),
                      TextButton(onPressed: () => Navigator.pop(ctx, true), child: const Text('মুছুন', style: TextStyle(color: AppColors.danger))),
                    ],
                  ),
                );
                if (ok == true && context.mounted) {
                  await context.read<AppState>().deleteCv(cv.id);
                  if (context.mounted) Navigator.of(context).pop();
                }
              },
              icon: const Icon(Icons.delete_outline_rounded, color: AppColors.danger),
              label: const Text('এই CV মুছে ফেলুন', style: TextStyle(color: AppColors.danger, fontWeight: FontWeight.w700)),
              style: OutlinedButton.styleFrom(backgroundColor: AppColors.coralDim, side: BorderSide.none, padding: const EdgeInsets.symmetric(vertical: 12)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _field(String label, TextEditingController c, {int maxLines = 1, ValueChanged<String>? onChanged}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: TextField(
        controller: c, maxLines: maxLines, onChanged: onChanged,
        decoration: InputDecoration(labelText: label, border: const OutlineInputBorder()),
      ),
    );
  }

  Widget _quickField(String label, String value, ValueChanged<String> onChanged, {int maxLines = 1}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: TextFormField(
        initialValue: value, maxLines: maxLines, onChanged: onChanged,
        decoration: InputDecoration(labelText: label, isDense: true, border: const OutlineInputBorder()),
      ),
    );
  }

  Widget _dropdownField(String label, String value, List<String> options, ValueChanged<String?> onChanged) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: DropdownButtonFormField<String>(
        initialValue: value.isEmpty ? null : value,
        isExpanded: true,
        decoration: InputDecoration(labelText: label, isDense: true, border: const OutlineInputBorder()),
        items: options.map((o) => DropdownMenuItem(value: o, child: Text(o))).toList(),
        onChanged: (v) { onChanged(v); context.read<AppState>().touchActive(); setState(() {}); },
      ),
    );
  }

  Widget _subHead(String text) => Padding(
        padding: const EdgeInsets.only(top: 10, bottom: 8),
        child: Text(text, style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: AppColors.inkMuted, letterSpacing: 0.4)),
      );

  Widget _addButton(String label, VoidCallback onTap) => SizedBox(
        width: double.infinity,
        child: OutlinedButton.icon(
          onPressed: onTap,
          icon: const Icon(Icons.add_rounded, size: 15, color: AppColors.mint),
          label: Text(label, style: const TextStyle(color: AppColors.mint, fontWeight: FontWeight.w700, fontSize: 12)),
          style: OutlinedButton.styleFrom(side: const BorderSide(color: AppColors.line2, style: BorderStyle.solid), padding: const EdgeInsets.symmetric(vertical: 11)),
        ),
      );
}

class _Accordion extends StatefulWidget {
  final String id;
  final Set<String> open;
  final Color iconBg, iconColor;
  final IconData icon;
  final String title;
  final Widget child;
  const _Accordion({required this.id, required this.open, required this.iconBg, required this.iconColor, required this.icon, required this.title, required this.child});

  @override
  State<_Accordion> createState() => _AccordionState();
}

class _AccordionState extends State<_Accordion> {
  @override
  Widget build(BuildContext context) {
    final isOpen = widget.open.contains(widget.id);
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(14), border: Border.all(color: AppColors.line)),
      child: Column(
        children: [
          InkWell(
            onTap: () => setState(() => isOpen ? widget.open.remove(widget.id) : widget.open.add(widget.id)),
            child: Padding(
              padding: const EdgeInsets.all(13),
              child: Row(
                children: [
                  Container(
                    width: 32, height: 32,
                    decoration: BoxDecoration(color: widget.iconBg, borderRadius: BorderRadius.circular(9)),
                    child: Icon(widget.icon, size: 16, color: widget.iconColor),
                  ),
                  const SizedBox(width: 11),
                  Expanded(child: Text(widget.title, style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700))),
                  Icon(isOpen ? Icons.keyboard_arrow_up_rounded : Icons.keyboard_arrow_down_rounded, color: AppColors.inkSoft),
                ],
              ),
            ),
          ),
          if (isOpen) Padding(padding: const EdgeInsets.fromLTRB(14, 0, 14, 16), child: widget.child),
        ],
      ),
    );
  }
}

class _RepeatCard extends StatelessWidget {
  final List<Widget> children;
  final VoidCallback onRemove;
  const _RepeatCard({required this.children, required this.onRemove});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.fromLTRB(12, 12, 12, 4),
      decoration: BoxDecoration(color: const Color(0xFFF8F9FC), borderRadius: BorderRadius.circular(12)),
      child: Column(
        children: [
          ...children,
          Align(
            alignment: Alignment.centerRight,
            child: TextButton.icon(
              onPressed: onRemove,
              icon: const Icon(Icons.close_rounded, size: 13, color: AppColors.danger),
              label: const Text('মুছুন', style: TextStyle(color: AppColors.danger, fontSize: 11)),
            ),
          ),
        ],
      ),
    );
  }
}

class _Chip extends StatelessWidget {
  final String label;
  final bool selected;
  final VoidCallback onTap;
  const _Chip({required this.label, required this.selected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(100),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 7),
        decoration: BoxDecoration(
          color: selected ? AppColors.mint : Colors.white,
          borderRadius: BorderRadius.circular(100),
          border: Border.all(color: AppColors.mint),
        ),
        child: Text(label, style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600, color: selected ? Colors.white : AppColors.mintDark)),
      ),
    );
  }
}

class _SelectedChip extends StatelessWidget {
  final String label;
  final VoidCallback onRemove;
  const _SelectedChip({required this.label, required this.onRemove});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.only(left: 11, right: 4, top: 6, bottom: 6),
      decoration: BoxDecoration(color: AppColors.mint, borderRadius: BorderRadius.circular(100)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(label, style: const TextStyle(fontSize: 11.5, color: Colors.white, fontWeight: FontWeight.w600)),
          InkWell(onTap: onRemove, child: const Padding(padding: EdgeInsets.all(4), child: Icon(Icons.close_rounded, size: 13, color: Colors.white))),
        ],
      ),
    );
  }
}
