import 'package:flutter/material.dart';
import 'screens/home_screen.dart';

void main() {
  runApp(const TruyenAudioApp());
}

class TruyenAudioApp extends StatelessWidget {
  const TruyenAudioApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'TruyenAudio',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.dark(
          primary: const Color(0xFFF0C040),
          surface: const Color(0xFF0F0F1A),
        ),
        scaffoldBackgroundColor: const Color(0xFF0F0F1A),
        appBarTheme: const AppBarTheme(
          backgroundColor: Color(0xFF1A1A2E),
          elevation: 0,
        ),
      ),
      home: const HomeScreen(),
    );
  }
}
