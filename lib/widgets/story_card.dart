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
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: const Color(0xFF1A1A2E),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFF2A2A4E)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(11)),
              child: AspectRatio(
                aspectRatio: 3 / 4,
                child: thumbnail.isNotEmpty
                    ? Image.network(thumbnail, fit: BoxFit.cover, errorBuilder: (_, __, ___) => _placeholder())
                    : _placeholder(),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600), maxLines: 2, overflow: TextOverflow.ellipsis),
                  const SizedBox(height: 4),
                  if (genres.isNotEmpty)
                    Text(genres.first, style: const TextStyle(color: Color(0xFF888888), fontSize: 11)),
                  const SizedBox(height: 2),
                  Row(
                    children: [
                      const Icon(Icons.visibility, size: 12, color: Color(0xFFF0C040)),
                      const SizedBox(width: 3),
                      Text(views, style: const TextStyle(color: Color(0xFFF0C040), fontSize: 11)),
                      const Spacer(),
                      const Icon(Icons.star, size: 12, color: Color(0xFFF0C040)),
                      const SizedBox(width: 2),
                      Text(rating, style: const TextStyle(color: Color(0xFFF0C040), fontSize: 11)),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _placeholder() {
    return Container(
      color: const Color(0xFF2A2A4E),
      child: const Center(child: Icon(Icons.book, color: Color(0xFF555555), size: 40)),
    );
  }
}
