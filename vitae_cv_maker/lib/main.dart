import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'screens/home_screen.dart';
import 'screens/onboarding_screen.dart';
import 'state/app_state.dart';
import 'theme/app_theme.dart';

void main() {
  runApp(const VitaeApp());
}

class VitaeApp extends StatelessWidget {
  const VitaeApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => AppState()..init(),
      child: MaterialApp(
        title: 'Vitae — CV মেকার',
        debugShowCheckedModeBanner: false,
        theme: appTheme,
        home: const _Root(),
      ),
    );
  }
}

class _Root extends StatelessWidget {
  const _Root();

  @override
  Widget build(BuildContext context) {
    final app = context.watch<AppState>();
    if (!app.loaded) {
      return const Scaffold(backgroundColor: AppColors.navy, body: Center(child: CircularProgressIndicator(color: AppColors.mint)));
    }
    return app.hasProfile ? const HomeScreen() : const OnboardingScreen();
  }
}
