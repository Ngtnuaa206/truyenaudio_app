import 'package:flutter/material.dart';

class StoryCard extends StatelessWidget {
  final int id;
  final String title;
  final String thumbnail;
  final String views;
  final String chapters;
  final String rating;
  final List<String> genres;
  final VoidCallback onTap;

  const StoryCard({
    super.key,
    required this.id,
    required this.title,
    this.thumbnail = '',
    this.views = '0',
    this.chapters = '0',
    this.rating = '0',
    this.genres = const [],
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardColor = isDark ? const Color(0xFF141929) : Colors.white;
    final borderColor = isDark ? const Color(0xFF1E2A45) : const Color(0xFFE0E5EC);
    final titleColor = isDark ? Colors.white : const Color(0xFF1A1A2E);
    final subColor = isDark ? const Color(0xFF8899AA) : const Color(0xFF666666);
    final accent = isDark ? const Color(0xFF64B5F6) : const Color(0xFF2D4373);

    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: cardColor,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: borderColor),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 8, offset: const Offset(0, 2))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(11)),
              child: AspectRatio(
                aspectRatio: 3 / 4,
                child: thumbnail.isNotEmpty
                    ? Image.network(thumbnail, fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => _placeholder(borderColor, accent))
                    : _placeholder(borderColor, accent),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: TextStyle(color: titleColor, fontSize: 13, fontWeight: FontWeight.w600), maxLines: 2, overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 4),
                  if (genres.isNotEmpty)
                    Text(genres.first, style: TextStyle(color: subColor, fontSize: 11)),
                  const SizedBox(height: 3),
                  Row(children: [
                    Icon(Icons.visibility, size: 12, color: accent),
                    const SizedBox(width: 3),
                    Text(views, style: TextStyle(color: accent, fontSize: 11)),
                    const Spacer(),
                    const Icon(Icons.star, size: 12, color: Color(0xFFF0C040)),
                    const SizedBox(width: 2),
                    Text(rating, style: const TextStyle(color: Color(0xFFF0C040), fontSize: 11)),
                  ]),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _placeholder(Color borderColor, Color accent) {
    return Container(color: borderColor, child: Center(child: Icon(Icons.book, color: accent, size: 40)));
  }
}
