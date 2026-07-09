import 'package:flutter/material.dart';
import '../models/chapter.dart';

class ReaderScreen extends StatefulWidget {
  final Chapter chapter;
  const ReaderScreen({super.key, required this.chapter});

  @override
  State<ReaderScreen> createState() => _ReaderScreenState();
}

class _ReaderScreenState extends State<ReaderScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F0F1A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1A1A2E),
        leading: IconButton(icon: const Icon(Icons.arrow_back, color: Colors.white), onPressed: () => Navigator.pop(context)),
        title: Text('Chương ${widget.chapter.chapterNumber}', style: const TextStyle(color: Colors.white, fontSize: 15)),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(widget.chapter.title, style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold)),
            const SizedBox(height: 20),
            if (widget.chapter.audioUrl.isNotEmpty)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(15),
                margin: const EdgeInsets.only(bottom: 20),
                decoration: BoxDecoration(
                  color: const Color(0xFF1A1A2E),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFF2A2A4E)),
                ),
                child: Column(
                  children: [
                    const Row(
                      children: [
                        Icon(Icons.headphones, color: Color(0xFFF0C040), size: 20),
                        SizedBox(width: 8),
                        Text('🎧 Nghe chương này', style: TextStyle(color: Color(0xFFF0C040))),
                      ],
                    ),
                    const SizedBox(height: 10),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton.icon(
                        onPressed: () {},
                        icon: const Icon(Icons.play_arrow),
                        label: const Text('Phát audio'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFF0C040),
                          foregroundColor: const Color(0xFF1A1A2E),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            HtmlContent(content: widget.chapter.content),
          ],
        ),
      ),
    );
  }
}

class HtmlContent extends StatelessWidget {
  final String content;
  const HtmlContent({super.key, required this.content});

  @override
  Widget build(BuildContext context) {
    final cleaned = content
        .replaceAll(RegExp(r'<[^>]*>'), '\n')
        .replaceAll(RegExp(r'\n{3,}'), '\n\n')
        .trim();
    return Text(
      cleaned,
      style: const TextStyle(color: Color(0xFFDDDDDD), fontSize: 16, height: 2),
    );
  }
}
