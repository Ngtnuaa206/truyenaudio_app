import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/story.dart';
import '../models/chapter.dart';
import 'reader_screen.dart';
import 'login_screen.dart';

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
  bool _loggedIn = false;
  double _userRating = 0;
  bool _bookmarked = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final results = await Future.wait([
      _api.getStory(widget.storyId),
      _api.getChapters(widget.storyId),
      _api.isLoggedIn(),
    ]);
    if (!mounted) return;
    setState(() {
      _story = results[0] as Story?;
      _chapters = results[1] as List<Chapter>;
      _loggedIn = results[2] as bool;
      _loading = false;
    });
  }

  Future<void> _submitRating(double rating) async {
    if (!_loggedIn) {
      Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
      return;
    }
    final success = await _api.submitRating(widget.storyId, rating.toInt());
    if (success && mounted) {
      setState(() => _userRating = rating);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: const Text('Đánh giá thành công!'), backgroundColor: Theme.of(context).colorScheme.primary),
      );
      _load();
    }
  }

  Future<void> _toggleBookmark() async {
    if (!_loggedIn) {
      Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
      return;
    }
    final result = await _api.toggleBookmark(widget.storyId);
    if (result != null && mounted) {
      setState(() => _bookmarked = result);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result ? 'Đã thêm vào yêu thích' : 'Đã bỏ yêu thích'),
          backgroundColor: Theme.of(context).colorScheme.primary,
        ),
      );
    }
  }

  void _openChapter(Chapter ch) {
    _api.saveHistory(widget.storyId, ch.id);
    Navigator.push(context, MaterialPageRoute(
      builder: (_) => ReaderScreen(
        storyId: widget.storyId,
        chapters: _chapters,
        initialIndex: _chapters.indexOf(ch),
      ),
    ));
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardColor = isDark ? const Color(0xFF141929) : Colors.white;
    final textColor = isDark ? Colors.white : const Color(0xFF1A1A2E);
    final subColor = isDark ? const Color(0xFF8899AA) : Colors.grey[600]!;
    final borderColor = isDark ? const Color(0xFF1E2A45) : const Color(0xFFE0E5EC);
    final accent = isDark ? const Color(0xFF64B5F6) : const Color(0xFF2D4373);

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0A0E1A) : const Color(0xFFF0F2F5),
      appBar: AppBar(
        backgroundColor: isDark ? const Color(0xFF141929) : const Color(0xFF2D4373),
        leading: IconButton(icon: const Icon(Icons.arrow_back, color: Colors.white), onPressed: () => Navigator.pop(context)),
        title: Text(_story?.title ?? 'Chi tiết truyện', style: const TextStyle(color: Colors.white, fontSize: 16), maxLines: 1, overflow: TextOverflow.ellipsis),
        actions: [
          IconButton(
            icon: Icon(_bookmarked ? Icons.bookmark : Icons.bookmark_border, color: _bookmarked ? const Color(0xFFF0C040) : Colors.white70),
            onPressed: _toggleBookmark,
          ),
        ],
      ),
      body: _loading
          ? Center(child: CircularProgressIndicator(color: accent))
          : _story == null
              ? const Center(child: Text('Không tìm thấy truyện', style: TextStyle(color: Colors.grey)))
              : SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildHeader(isDark, cardColor, textColor, subColor, borderColor, accent),
                      _buildRatingSection(isDark, cardColor, textColor, borderColor, accent),
                      _buildDescription(isDark, cardColor, textColor, accent),
                      _buildChapterList(isDark, cardColor, textColor, subColor, borderColor, accent),
                    ],
                  ),
                ),
    );
  }

  Widget _buildHeader(bool isDark, Color cardColor, Color textColor, Color subColor, Color borderColor, Color accent) {
    return Container(
      padding: const EdgeInsets.all(16),
      color: cardColor,
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: _story!.thumbnail.isNotEmpty
                ? Image.network(_story!.thumbnail, width: 120, height: 170, fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => _placeholder(120, 170, borderColor))
                : _placeholder(120, 170, borderColor),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(_story!.title, style: TextStyle(color: textColor, fontSize: 17, fontWeight: FontWeight.bold)),
                const SizedBox(height: 10),
                if (_story!.genres.isNotEmpty)
                  Wrap(spacing: 6, runSpacing: 4,
                    children: _story!.genres.map((g) => _tag(g, accent)).toList()),
                if (_story!.authors.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Row(children: [
                    Icon(Icons.person_outline, size: 14, color: subColor),
                    const SizedBox(width: 4),
                    Text(_story!.authors.join(', '), style: TextStyle(color: subColor, fontSize: 12)),
                  ]),
                ],
                const SizedBox(height: 8),
                Row(children: [
                  Icon(Icons.menu_book, size: 14, color: subColor),
                  const SizedBox(width: 4),
                  Text('${_chapters.length} chương', style: TextStyle(color: subColor, fontSize: 12)),
                  const SizedBox(width: 12),
                  Icon(Icons.visibility, size: 14, color: subColor),
                  const SizedBox(width: 4),
                  Text('${_formatViews(_story!.views)}', style: TextStyle(color: subColor, fontSize: 12)),
                ]),
                const SizedBox(height: 8),
                Row(
                  children: [
                    ...List.generate(5, (i) => Icon(Icons.star_rounded, size: 20,
                        color: i < _story!.rating.round() ? const Color(0xFFF0C040) : borderColor)),
                    const SizedBox(width: 6),
                    Text(_story!.rating.toStringAsFixed(1), style: const TextStyle(color: Color(0xFFF0C040), fontSize: 13, fontWeight: FontWeight.bold)),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRatingSection(bool isDark, Color cardColor, Color textColor, Color borderColor, Color accent) {
    return Container(
      margin: const EdgeInsets.fromLTRB(15, 15, 15, 0),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: cardColor, borderRadius: BorderRadius.circular(12), border: Border.all(color: borderColor)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Đánh giá của bạn', style: TextStyle(color: textColor, fontSize: 15, fontWeight: FontWeight.bold)),
          const SizedBox(height: 10),
          Row(
            children: List.generate(5, (i) {
              final starVal = (i + 1).toDouble();
              return GestureDetector(
                onTap: () => _submitRating(starVal),
                child: Padding(
                  padding: const EdgeInsets.only(right: 4),
                  child: Icon(Icons.star_rounded, size: 34,
                      color: starVal <= _userRating ? const Color(0xFFF0C040) : borderColor),
                ),
              );
            }),
          ),
        ],
      ),
    );
  }

  Widget _buildDescription(bool isDark, Color cardColor, Color textColor, Color accent) {
    final clean = _story!.excerpt.replaceAll(RegExp(r'<[^>]*>'), '').trim();
    if (clean.isEmpty) return const SizedBox.shrink();
    return Container(
      margin: const EdgeInsets.fromLTRB(15, 15, 15, 0),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: cardColor, borderRadius: BorderRadius.circular(12)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            Icon(Icons.info_outline, size: 18, color: accent),
            const SizedBox(width: 6),
            Text('Nội dung', style: TextStyle(color: textColor, fontSize: 15, fontWeight: FontWeight.bold)),
          ]),
          const SizedBox(height: 10),
          Text(clean, style: TextStyle(color: textColor.withOpacity(0.8), fontSize: 14, height: 1.6)),
        ],
      ),
    );
  }

  Widget _tag(String text, Color accent) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(color: accent.withOpacity(0.15), borderRadius: BorderRadius.circular(12)),
      child: Text(text, style: TextStyle(color: accent, fontSize: 11, fontWeight: FontWeight.w500)),
    );
  }

  Widget _placeholder(double w, double h, Color borderColor) {
    return Container(width: w, height: h, color: borderColor,
        child: const Center(child: Icon(Icons.book, color: Color(0xFF555555), size: 50)));
  }

  Widget _buildChapterList(bool isDark, Color cardColor, Color textColor, Color subColor, Color borderColor, Color accent) {
    return Container(
      margin: const EdgeInsets.fromLTRB(15, 15, 15, 15),
      decoration: BoxDecoration(color: cardColor, borderRadius: BorderRadius.circular(12), border: Border.all(color: borderColor)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Icon(Icons.list_alt, size: 20, color: accent),
                const SizedBox(width: 8),
                Text('Danh sách chương (${_chapters.length})', style: TextStyle(color: textColor, fontSize: 16, fontWeight: FontWeight.bold)),
              ],
            ),
          ),
          if (_chapters.isEmpty)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 20),
              child: Center(
                child: Column(
                  children: [
                    Icon(Icons.menu_book, size: 48, color: subColor.withOpacity(0.4)),
                    const SizedBox(height: 8),
                    Text('Chưa có chương nào', style: TextStyle(color: subColor, fontSize: 14)),
                  ],
                ),
              ),
            )
          else
            ...(_chapters.map((ch) => InkWell(
              onTap: () => _openChapter(ch),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 13, horizontal: 16),
                decoration: BoxDecoration(border: Border(top: BorderSide(color: borderColor, width: 0.5))),
                child: Row(
                  children: [
                    Container(
                      width: 36, height: 36,
                      decoration: BoxDecoration(color: accent.withOpacity(0.12), borderRadius: BorderRadius.circular(8)),
                      child: Center(
                        child: Text('${ch.chapterNumber}', style: TextStyle(color: accent, fontSize: 14, fontWeight: FontWeight.bold)),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(ch.title, style: TextStyle(color: textColor, fontSize: 14), maxLines: 1, overflow: TextOverflow.ellipsis),
                          if (ch.isVip || ch.audioUrl.isNotEmpty)
                            Padding(
                              padding: const EdgeInsets.only(top: 3),
                              child: Row(children: [
                                if (ch.isVip) ...[
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                                    decoration: BoxDecoration(color: const Color(0xFFF0C040), borderRadius: BorderRadius.circular(4)),
                                    child: const Text('VIP', style: TextStyle(color: Color(0xFF1A1A2E), fontSize: 9, fontWeight: FontWeight.bold)),
                                  ),
                                  const SizedBox(width: 6),
                                ],
                                if (ch.audioUrl.isNotEmpty) ...[
                                  Icon(Icons.headphones, color: accent, size: 14),
                                  const SizedBox(width: 3),
                                  Text('Audio', style: TextStyle(color: accent, fontSize: 11)),
                                ],
                              ]),
                            ),
                        ],
                      ),
                    ),
                    Icon(Icons.chevron_right, color: subColor, size: 22),
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
