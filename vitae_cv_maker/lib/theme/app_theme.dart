import 'package:flutter/material.dart';

class AppColors {
  static const navy = Color(0xFF242C4D);
  static const navyDeep = Color(0xFF171C33);
  static const mint = Color(0xFF00C2A8);
  static const mintDark = Color(0xFF00816F);
  static const mintDim = Color(0x2100C2A8);
  static const coral = Color(0xFFFF6B5D);
  static const coralDim = Color(0x21FF6B5D);
  static const amber = Color(0xFFFFB020);
  static const bg = Color(0xFFF6F7FB);
  static const card = Color(0xFFFFFFFF);
  static const ink = Color(0xFF242C4D);
  static const inkMuted = Color(0xFF8890A6);
  static const inkSoft = Color(0xFFC7CCDA);
  static const line = Color(0x14242C4D);
  static const line2 = Color(0x24242C4D);
  static const danger = Color(0xFFD8503F);
}

final appTheme = ThemeData(
  useMaterial3: true,
  scaffoldBackgroundColor: AppColors.bg,
  colorScheme: ColorScheme.fromSeed(
    seedColor: AppColors.mint,
    primary: AppColors.mint,
    secondary: AppColors.coral,
    surface: AppColors.card,
  ),
  fontFamily: 'NotoSansBengali',
  appBarTheme: const AppBarTheme(
    backgroundColor: AppColors.bg,
    foregroundColor: AppColors.navy,
    elevation: 0,
    centerTitle: true,
  ),
  inputDecorationTheme: InputDecorationTheme(
    filled: true,
    fillColor: Colors.white,
    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(10),
      borderSide: const BorderSide(color: AppColors.line),
    ),
    enabledBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(10),
      borderSide: const BorderSide(color: AppColors.line),
    ),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(10),
      borderSide: const BorderSide(color: AppColors.mint, width: 1.5),
    ),
    labelStyle: const TextStyle(color: AppColors.inkMuted, fontSize: 12, fontWeight: FontWeight.w600),
  ),
);
