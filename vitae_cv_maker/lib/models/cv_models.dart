class EducationEntry {
  String degree, result, institute, year, board;
  EducationEntry({this.degree = '', this.result = '', this.institute = '', this.year = '', this.board = ''});

  factory EducationEntry.fromJson(Map<String, dynamic> j) => EducationEntry(
        degree: j['degree'] ?? '', result: j['result'] ?? '', institute: j['institute'] ?? '',
        year: j['year'] ?? '', board: j['board'] ?? '',
      );
  Map<String, dynamic> toJson() => {'degree': degree, 'result': result, 'institute': institute, 'year': year, 'board': board};
  bool get isEmpty => degree.isEmpty && institute.isEmpty;
}

class ExperienceEntry {
  String position, company, duration, description;
  ExperienceEntry({this.position = '', this.company = '', this.duration = '', this.description = ''});

  factory ExperienceEntry.fromJson(Map<String, dynamic> j) => ExperienceEntry(
        position: j['position'] ?? '', company: j['company'] ?? '', duration: j['duration'] ?? '', description: j['description'] ?? '',
      );
  Map<String, dynamic> toJson() => {'position': position, 'company': company, 'duration': duration, 'description': description};
  bool get isEmpty => position.isEmpty && company.isEmpty;
}

class LanguageEntry {
  String name, level;
  LanguageEntry({this.name = '', this.level = ''});
  factory LanguageEntry.fromJson(Map<String, dynamic> j) => LanguageEntry(name: j['name'] ?? '', level: j['level'] ?? '');
  Map<String, dynamic> toJson() => {'name': name, 'level': level};
}

class CertificationEntry {
  String title, issuer, year;
  CertificationEntry({this.title = '', this.issuer = '', this.year = ''});
  factory CertificationEntry.fromJson(Map<String, dynamic> j) => CertificationEntry(title: j['title'] ?? '', issuer: j['issuer'] ?? '', year: j['year'] ?? '');
  Map<String, dynamic> toJson() => {'title': title, 'issuer': issuer, 'year': year};
}

class ReferenceEntry {
  String name, position, contact;
  ReferenceEntry({this.name = '', this.position = '', this.contact = ''});
  factory ReferenceEntry.fromJson(Map<String, dynamic> j) => ReferenceEntry(name: j['name'] ?? '', position: j['position'] ?? '', contact: j['contact'] ?? '');
  Map<String, dynamic> toJson() => {'name': name, 'position': position, 'contact': contact};
}

class CvData {
  String photoPath;
  String name, title, phone, email, address, dob, nid;
  String category, objective;
  List<EducationEntry> education;
  List<ExperienceEntry> experience;
  List<String> skills;
  List<LanguageEntry> languages;
  List<CertificationEntry> certifications;
  List<ReferenceEntry> references;
  bool refOnRequest;

  CvData({
    this.photoPath = '',
    this.name = '', this.title = '', this.phone = '', this.email = '', this.address = '', this.dob = '', this.nid = '',
    this.category = '', this.objective = '',
    List<EducationEntry>? education,
    List<ExperienceEntry>? experience,
    List<String>? skills,
    List<LanguageEntry>? languages,
    List<CertificationEntry>? certifications,
    List<ReferenceEntry>? references,
    this.refOnRequest = false,
  })  : education = education ?? [],
        experience = experience ?? [],
        skills = skills ?? [],
        languages = languages ?? [],
        certifications = certifications ?? [],
        references = references ?? [];

  factory CvData.fromJson(Map<String, dynamic> j) => CvData(
        photoPath: j['photoPath'] ?? '',
        name: j['name'] ?? '', title: j['title'] ?? '', phone: j['phone'] ?? '', email: j['email'] ?? '',
        address: j['address'] ?? '', dob: j['dob'] ?? '', nid: j['nid'] ?? '',
        category: j['category'] ?? '', objective: j['objective'] ?? '',
        education: ((j['education'] ?? []) as List).map((e) => EducationEntry.fromJson(e)).toList(),
        experience: ((j['experience'] ?? []) as List).map((e) => ExperienceEntry.fromJson(e)).toList(),
        skills: ((j['skills'] ?? []) as List).map((e) => e.toString()).toList(),
        languages: ((j['languages'] ?? []) as List).map((e) => LanguageEntry.fromJson(e)).toList(),
        certifications: ((j['certifications'] ?? []) as List).map((e) => CertificationEntry.fromJson(e)).toList(),
        references: ((j['references'] ?? []) as List).map((e) => ReferenceEntry.fromJson(e)).toList(),
        refOnRequest: j['refOnRequest'] ?? false,
      );

  Map<String, dynamic> toJson() => {
        'photoPath': photoPath,
        'name': name, 'title': title, 'phone': phone, 'email': email, 'address': address, 'dob': dob, 'nid': nid,
        'category': category, 'objective': objective,
        'education': education.map((e) => e.toJson()).toList(),
        'experience': experience.map((e) => e.toJson()).toList(),
        'skills': skills,
        'languages': languages.map((e) => e.toJson()).toList(),
        'certifications': certifications.map((e) => e.toJson()).toList(),
        'references': references.map((e) => e.toJson()).toList(),
        'refOnRequest': refOnRequest,
      };
}

class CvRecord {
  String id;
  String template;
  int updatedAt;
  CvData data;

  CvRecord({required this.id, this.template = 'modern', required this.updatedAt, CvData? data}) : data = data ?? CvData();

  factory CvRecord.fromJson(Map<String, dynamic> j) => CvRecord(
        id: j['id'], template: j['template'] ?? 'modern', updatedAt: j['updatedAt'] ?? 0,
        data: CvData.fromJson(j['data'] ?? {}),
      );
  Map<String, dynamic> toJson() => {'id': id, 'template': template, 'updatedAt': updatedAt, 'data': data.toJson()};
}

class Profile {
  String name;
  Profile({this.name = ''});
  factory Profile.fromJson(Map<String, dynamic> j) => Profile(name: j['name'] ?? '');
  Map<String, dynamic> toJson() => {'name': name};
}

class CvTemplate {
  final String id, name, swatchHex, accentHex;
  const CvTemplate(this.id, this.name, this.swatchHex, this.accentHex);
}

const List<CvTemplate> kTemplates = [
  CvTemplate('modern', 'মডার্ন মিন্ট', '0xFFEAF7F4', '0xFF00C2A8'),
  CvTemplate('classic', 'ক্লাসিক গ্রে', '0xFFF5F6FA', '0xFF242C4D'),
  CvTemplate('minimal', 'মিনিমাল কোরাল', '0xFFFFF1EF', '0xFFFF6B5D'),
  CvTemplate('ats', 'ATS বেসিক', '0xFFFFF8E8', '0xFFFFB020'),
];

CvTemplate templateById(String id) => kTemplates.firstWhere((t) => t.id == id, orElse: () => kTemplates.first);
