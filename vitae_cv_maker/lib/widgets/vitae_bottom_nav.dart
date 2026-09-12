import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

class VitaeBottomNav extends StatelessWidget {
  final int currentIndex; // 0 home, 1 templates, 2 settings
  final VoidCallback onHome;
  final VoidCallback onTemplates;
  final VoidCallback onNew;
  final VoidCallback onSettings;

  const VitaeBottomNav({
    super.key,
    required this.currentIndex,
    required this.onHome,
    required this.onTemplates,
    required this.onNew,
    required this.onSettings,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 74,
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: AppColors.line)),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          _NavItem(icon: Icons.home_rounded, label: 'হোম', active: currentIndex == 0, onTap: onHome),
          _NavItem(icon: Icons.grid_view_rounded, label: 'টেমপ্লেট', active: currentIndex == 1, onTap: onTemplates),
          _FabButton(onTap: onNew),
          _NavItem(icon: Icons.settings_rounded, label: 'সেটিংস', active: currentIndex == 2, onTap: onSettings),
        ],
      ),
    );
  }
}

class _NavItem extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool active;
  final VoidCallback onTap;
  const _NavItem({required this.icon, required this.label, required this.active, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final color = active ? AppColors.mint : AppColors.inkSoft;
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 8),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: color, size: 22),
            const SizedBox(height: 3),
            Text(label, style: TextStyle(color: color, fontSize: 10, fontWeight: FontWeight.w700)),
          ],
        ),
      ),
    );
  }
}

class _FabButton extends StatelessWidget {
  final VoidCallback onTap;
  const _FabButton({required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 50,
        height: 50,
        margin: const EdgeInsets.only(top: 0),
        transform: Matrix4.translationValues(0, -14, 0),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          gradient: const LinearGradient(colors: [AppColors.mint, Color(0xFF00A892)]),
          boxShadow: [BoxShadow(color: AppColors.mint.withValues(alpha: 0.5), blurRadius: 18, offset: const Offset(0, 10))],
        ),
        child: const Icon(Icons.add_rounded, color: Colors.white, size: 24),
      ),
    );
  }
}
