import 'package:flutter/foundation.dart';
import 'package:uuid/uuid.dart';
import '../models/cv_models.dart';
import '../services/storage_service.dart';

class AppState extends ChangeNotifier {
  final StorageService _storage = StorageService();
  final _uuid = const Uuid();

  Profile? profile;
  List<CvRecord> cvs = [];
  String? activeId;
  bool loaded = false;

  Future<void> init() async {
    profile = await _storage.loadProfile();
    cvs = await _storage.loadCvs();
    loaded = true;
    notifyListeners();
  }

  bool get hasProfile => profile != null && profile!.name.isNotEmpty;

  CvRecord? get activeCv {
    if (activeId == null) return null;
    try {
      return cvs.firstWhere((c) => c.id == activeId);
    } catch (_) {
      return null;
    }
  }

  Future<void> setProfileName(String name) async {
    profile = Profile(name: name);
    await _storage.saveProfile(profile!);
    notifyListeners();
  }

  Future<void> logout() async {
    await _storage.clearAll();
    profile = null;
    cvs = [];
    activeId = null;
    notifyListeners();
  }

  Future<CvRecord> createCv(String template) async {
    final cv = CvRecord(id: _uuid.v4(), template: template, updatedAt: DateTime.now().millisecondsSinceEpoch);
    cvs.insert(0, cv);
    activeId = cv.id;
    await _storage.saveCvs(cvs);
    notifyListeners();
    return cv;
  }

  Future<void> touchActive() async {
    final cv = activeCv;
    if (cv == null) return;
    cv.updatedAt = DateTime.now().millisecondsSinceEpoch;
    await _storage.saveCvs(cvs);
    notifyListeners();
  }

  Future<void> deleteCv(String id) async {
    cvs.removeWhere((c) => c.id == id);
    if (activeId == id) activeId = null;
    await _storage.saveCvs(cvs);
    notifyListeners();
  }

  Future<void> setActiveTemplate(String templateId) async {
    final cv = activeCv;
    if (cv == null) return;
    cv.template = templateId;
    await touchActive();
  }
}
