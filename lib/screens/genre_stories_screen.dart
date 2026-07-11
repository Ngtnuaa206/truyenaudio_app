import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/story.dart';
import 'story_detail_screen.dart';

class GenreStoriesScreen extends StatefulWidget {
  final String genreId;
  final String genreName;
  const GenreStoriesScreen({super.key, required this.genreId, required this.genreName});

  @override
  State<GenreStoriesScreen> createState() => _GenreStoriesScreenState();
}

class _GenreStoriesScreenState extends State<GenreStoriesScreen> {
  final ApiService _api = ApiService();
  List<Story> _stories = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final stories = await _api.getStories(perPage: 50, genre: widget.genreId);
    if (mounted) setState(() { _stories = stories; _loading = false; });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardColor = isDark ? const Color(0xFF141929) : Colors.white;
    final textColor = isDark ? Colors.white : const Color(0xFF1A1A2E);
    final subColor = isDark ? const Color(0xFF8899AA) : Colors.grey[600]!;
    final bgColor = isDark ? const Color(0xFF0A0E1A) : const Color(0xFFF0F2F5);
    final accent = isDark ? const Color(0xFF64B5F6) : const Color(0xFF2D4373);

    return Scaffold(
      backgroundColor: bgColor,
      appBar: AppBar(
        backgroundColor: isDark ? const Color(0xFF141929) : const Color(0xFF2D4373),
        title: Text(widget.genreName, style: const TextStyle(color: Colors.white, fontSize: 18)),
        leading: IconButton(icon: const Icon(Icons.arrow_back, color: Colors.white), onPressed: () => Navigator.pop(context)),
      ),
      body: _loading
          ? Center(child: CircularProgressIndicator(color: accent))
          : _stories.isEmpty
              ? Center(child: Text('Chưa có truyện thể loại này', style: TextStyle(color: subColor, fontSize: 16)))
              : ListView.builder(
                  padding: const EdgeInsets.all(15),
                  itemCount: _stories.length,
                  itemBuilder: (_, i) {
                    final s = _stories[i];
                    return GestureDetector(
                      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => StoryDetailScreen(storyId: s.id))),
                      child: Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(color: cardColor, borderRadius: BorderRadius.circular(12)),
                        child: Row(
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: Image.network(s.thumbnail, width: 60, height: 80, fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => Container(width: 60, height: 80, color: accent.withOpacity(0.2), child: Icon(Icons.book, color: accent))),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(s.title, style: TextStyle(color: textColor, fontWeight: FontWeight.w600, fontSize: 15), maxLines: 2, overflow: TextOverflow.ellipsis),
                                  const SizedBox(height: 4),
                                  Text('${s.chapterCount} chương · ${_formatViews(s.views)} lượt xem', style: TextStyle(color: subColor, fontSize: 12)),
                                  const SizedBox(height: 4),
                                  Row(children: [
                                    const Icon(Icons.star, color: Color(0xFFF0C040), size: 14),
                                    const SizedBox(width: 4),
                                    Text(s.rating.toStringAsFixed(1), style: const TextStyle(color: Color(0xFFF0C040), fontSize: 12)),
                                  ]),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
    );
  }

  String _formatViews(int count) {
    if (count >= 1000000) return '${(count / 1000000).toStringAsFixed(1)}M';
    if (count >= 1000) return '${(count / 1000).toStringAsFixed(1)}K';
    return count.toString();
  }
}
