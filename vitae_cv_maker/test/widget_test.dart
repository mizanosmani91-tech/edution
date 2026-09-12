import 'package:flutter_test/flutter_test.dart';

import 'package:vitae_cv_maker/main.dart';

void main() {
  testWidgets('App boots to onboarding screen', (WidgetTester tester) async {
    await tester.pumpWidget(const VitaeApp());
    await tester.pumpAndSettle();

    expect(find.text('শুরু করুন'), findsWidgets);
  });
}
