import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/story.dart';
import '../models/chapter.dart';
import 'reader_screen.dart';

class StoryDetailScreen extends StatefulWidget {
  final int storyId;
  const StoryDetailScreen({super.key, required this.storyId});

  @override
  State<StoryDetailScreen> createState() => _StoryDetailScreenState();
}

class _StoryDetailScreenState extends State<StoryDetailScreen> {
  final ApiService _api = ApiService();
  Story? _story;
  List<Chapter> _chapters = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final results = await Future.wait([
      _api.getStory(widget.storyId),
      _api.getChapters(widget.storyId),
    ]);
    setState(() {
      _story = results[0] as Story?;
      _chapters = results[1] as List<Chapter>;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F0F1A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1A1A2E),
        leading: IconButton(icon: const Icon(Icons.arrow_back, color: Colors.white), onPressed: () => Navigator.pop(context)),
        title: Text(_story?.title ?? 'Chi tiết truyện', style: const TextStyle(color: Colors.white, fontSize: 16), maxLines: 1, overflow: TextOverflow.ellipsis),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFFF0C040)))
          : _story == null
              ? const Center(child: Text('Không tìm thấy', style: TextStyle(color: Colors.grey)))
              : SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildHeader(),
                      if (_chapters.isNotEmpty) _buildChapterList(),
                    ],
                  ),
                ),
    );
  }

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.all(15),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: _story!.thumbnail.isNotEmpty
                ? Image.network(_story!.thumbnail, width: 160, height: 220, fit: BoxFit.cover, errorBuilder: (_, __, ___) => _placeholder(160, 220))
                : _placeholder(160, 220),
          ),
          const SizedBox(width: 15),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(_story!.title, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                const SizedBox(height: 8),
                if (_story!.genres.isNotEmpty)
                  Wrap(
                    spacing: 6, runSpacing: 4,
                    children: _story!.genres.map((g) => _tag(g)).toList(),
                  ),
                if (_story!.authors.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Text('✍ ${_story!.authors.join(', ')}', style: const TextStyle(color: Color(0xFF888888), fontSize: 13)),
                ],
                const SizedBox(height: 6),
                Text('📖 ${_chapters.length} chương', style: const TextStyle(color: Color(0xFF888888), fontSize: 13)),
                Text('👁 ${_formatViews(_story!.views)} lượt xem', style: const TextStyle(color: Color(0xFF888888), fontSize: 13)),
                if (_story!.status.isNotEmpty)
                  Text('📌 ${_story!.status}', style: const TextStyle(color: Color(0xFF888888), fontSize: 13)),
                const SizedBox(height: 8),
                Row(
                  children: [
                    ...List.generate(5, (i) => Icon(Icons.star, size: 18, color: i < _story!.rating.round() ? const Color(0xFFF0C040) : const Color(0xFF555555))),
                    const SizedBox(width: 6),
                    Text('${_story!.rating.toStringAsFixed(1)}/5', style: const TextStyle(color: Color(0xFFF0C040), fontSize: 13)),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _tag(String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: const Color(0xFF2A2A4E),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(text, style: const TextStyle(color: Color(0xFFF0C040), fontSize: 11)),
    );
  }

  Widget _placeholder(double w, double h) {
    return Container(width: w, height: h, color: const Color(0xFF2A2A4E), child: const Center(child: Icon(Icons.book, color: Color(0xFF555555), size: 50)));
  }

  Widget _buildChapterList() {
    return Padding(
      padding: const EdgeInsets.all(15),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Danh sách chương (${_chapters.length})', style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 10),
          ...(_chapters.map((ch) => Container(
            padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 10),
            decoration: const BoxDecoration(border: Border(bottom: BorderSide(color: Color(0xFF2A2A4E)))),
            child: InkWell(
              onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => ReaderScreen(chapter: ch))),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      'Chương ${ch.chapterNumber}: ${ch.title}',
                      style: const TextStyle(color: Colors.white70, fontSize: 14),
                      maxLines: 1, overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  Row(
                    children: [
                      if (ch.isVip) Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(color: const Color(0xFFF0C040), borderRadius: BorderRadius.circular(4)),
                        child: const Text('VIP', style: TextStyle(color: Color(0xFF1A1A2E), fontSize: 10, fontWeight: FontWeight.bold)),
                      ),
                      if (ch.audioUrl.isNotEmpty) ...[
                        const SizedBox(width: 6),
                        const Icon(Icons.headphones, color: Color(0xFFF0C040), size: 16),
                      ],
                    ],
                  ),
                ],
              ),
            ),
          ))),
        ],
      ),
    );
  }

  String _formatViews(int count) {
    if (count >= 1000000) return '${(count / 1000000).toStringAsFixed(1)}M';
    if (count >= 1000) return '${(count / 1000).toStringAsFixed(1)}K';
    return count.toString();
  }
}
