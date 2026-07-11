import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/story.dart';
import 'story_detail_screen.dart';

class SearchScreen extends StatefulWidget {
  const SearchScreen({super.key});

  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  final ApiService _api = ApiService();
  final TextEditingController _ctrl = TextEditingController();
  List<Story> _results = [];
  bool _loading = false;
  bool _searched = false;

  Future<void> _search() async {
    final q = _ctrl.text.trim();
    if (q.isEmpty) return;
    setState(() { _loading = true; _searched = true; });
    final stories = await _api.searchStories(q);
    if (mounted) setState(() { _results = stories; _loading = false; });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final bgColor = isDark ? const Color(0xFF0A0E1A) : const Color(0xFFF0F2F5);
    final cardColor = isDark ? const Color(0xFF141929) : Colors.white;
    final textColor = isDark ? Colors.white : const Color(0xFF1A1A2E);
    final fillColor = isDark ? const Color(0xFF141929) : const Color(0xFFE8EDF5);
    final accent = isDark ? const Color(0xFF64B5F6) : const Color(0xFF2D4373);

    return Scaffold(
      backgroundColor: bgColor,
      appBar: AppBar(
        backgroundColor: isDark ? const Color(0xFF141929) : const Color(0xFF2D4373),
        title: const Text('Tìm Kiếm', style: TextStyle(color: Colors.white, fontSize: 18)),
        leading: IconButton(icon: const Icon(Icons.arrow_back, color: Colors.white), onPressed: () => Navigator.pop(context)),
      ),
      body: Column(
        children: [
          Container(
            color: isDark ? const Color(0xFF141929) : const Color(0xFF2D4373),
            padding: const EdgeInsets.fromLTRB(15, 0, 15, 15),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _ctrl,
                    style: TextStyle(color: textColor),
                    decoration: InputDecoration(
                      hintText: 'Tìm kiếm truyện...',
                      hintStyle: TextStyle(color: Colors.grey[500]),
                      prefixIcon: Icon(Icons.search, color: accent),
                      filled: true,
                      fillColor: fillColor,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 15, vertical: 12),
                    ),
                    onSubmitted: (_) => _search(),
                  ),
                ),
                const SizedBox(width: 10),
                GestureDetector(
                  onTap: _search,
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(color: accent, borderRadius: BorderRadius.circular(12)),
                    child: const Icon(Icons.search, color: Colors.white),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: _loading
                ? Center(child: CircularProgressIndicator(color: accent))
                : !_searched
                    ? Center(child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.search, size: 80, color: Colors.grey[600]),
                          const SizedBox(height: 16),
                          Text('Nhập từ khóa để tìm truyện', style: TextStyle(color: Colors.grey[500], fontSize: 16)),
                        ],
                      ))
                    : _results.isEmpty
                        ? Center(child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.search_off, size: 80, color: Colors.grey[600]),
                              const SizedBox(height: 16),
                              Text('Không tìm thấy kết quả', style: TextStyle(color: Colors.grey[500], fontSize: 16)),
                            ],
                          ))
                        : ListView.builder(
                            padding: const EdgeInsets.all(15),
                            itemCount: _results.length,
                            itemBuilder: (_, i) => _buildResultCard(_results[i], cardColor, textColor, accent),
                          ),
          ),
        ],
      ),
    );
  }

  Widget _buildResultCard(Story s, Color cardColor, Color textColor, Color accent) {
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
                  Text('${s.chapterCount} chương', style: TextStyle(color: Colors.grey[500], fontSize: 12)),
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
  }
}
