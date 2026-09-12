import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/cv_models.dart';

class StorageService {
  static const _kProfile = 'vitae_profile';
  static const _kCvs = 'vitae_cvs';

  Future<Profile?> loadProfile() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_kProfile);
    if (raw == null) return null;
    try {
      return Profile.fromJson(jsonDecode(raw));
    } catch (_) {
      return null;
    }
  }

  Future<void> saveProfile(Profile profile) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kProfile, jsonEncode(profile.toJson()));
  }

  Future<void> clearProfile() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_kProfile);
  }

  Future<List<CvRecord>> loadCvs() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_kCvs);
    if (raw == null) return [];
    try {
      final list = jsonDecode(raw) as List;
      return list.map((e) => CvRecord.fromJson(e)).toList();
    } catch (_) {
      return [];
    }
  }

  Future<void> saveCvs(List<CvRecord> cvs) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_kCvs, jsonEncode(cvs.map((c) => c.toJson()).toList()));
  }

  Future<void> clearAll() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_kProfile);
    await prefs.remove(_kCvs);
  }
}
