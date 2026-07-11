import 'package:flutter/material.dart';
import '../services/api_service.dart';

class AuthorScreen extends StatefulWidget {
  const AuthorScreen({super.key});

  @override
  State<AuthorScreen> createState() => _AuthorScreenState();
}

class _AuthorScreenState extends State<AuthorScreen> {
  final ApiService _api = ApiService();
  bool _loading = true;
  Map<String, dynamic> _stats = {};
  bool _isAuthor = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final role = await _api.getUserRole();
    if (role != 'tac_gia_role' && role != 'administrator') {
      if (mounted) setState(() { _loading = false; _isAuthor = false; });
      return;
    }
    final stats = await _api.getAuthorStats();
    if (mounted) setState(() {
      _isAuthor = true;
      _stats = stats ?? {};
      _loading = false;
    });
  }

  Future<void> _upgradeToAuthor() async {
    final ok = await _api.upgradeToAuthor();
    if (ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: const Text('Chúc mừng! Bạn đã trở thành tác giả!'), backgroundColor: Theme.of(context).colorScheme.primary),
      );
      _load();
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardColor = isDark ? const Color(0xFF141929) : Colors.white;
    final textColor = isDark ? Colors.white : const Color(0xFF1A1A2E);
    final subColor = isDark ? const Color(0xFF8899AA) : Colors.grey[600]!;
    final borderColor = isDark ? const Color(0xFF1E2A45) : const Color(0xFFE0E5EC);
    final bgColor = isDark ? const Color(0xFF0A0E1A) : const Color(0xFFF0F2F5);
    final accent = isDark ? const Color(0xFF64B5F6) : const Color(0xFF2D4373);

    return Scaffold(
      backgroundColor: bgColor,
      appBar: AppBar(
        backgroundColor: isDark ? const Color(0xFF141929) : const Color(0xFF2D4373),
        title: const Text('Tác Giả', style: TextStyle(color: Colors.white, fontSize: 17)),
        leading: IconButton(icon: const Icon(Icons.arrow_back, color: Colors.white), onPressed: () => Navigator.pop(context)),
      ),
      body: _loading
          ? Center(child: CircularProgressIndicator(color: accent))
          : !_isAuthor
              ? _buildNotAuthor(cardColor, textColor, borderColor, accent)
              : _buildDashboard(cardColor, textColor, subColor, borderColor, accent),
    );
  }

  Widget _buildNotAuthor(Color cardColor, Color textColor, Color borderColor, Color accent) {
    return Center(
      child: Container(
        margin: const EdgeInsets.all(30),
        padding: const EdgeInsets.all(30),
        decoration: BoxDecoration(color: cardColor, borderRadius: BorderRadius.circular(16), border: Border.all(color: borderColor)),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.edit_note, size: 60, color: accent),
            const SizedBox(height: 16),
            Text('Trở Thành Tác Giả', style: TextStyle(color: textColor, fontSize: 22, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            Text('Xuất bản truyện của bạn và kiếm thu nhập!',
                style: TextStyle(color: Colors.grey[500], fontSize: 14), textAlign: TextAlign.center),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _upgradeToAuthor,
                style: ElevatedButton.styleFrom(backgroundColor: accent, foregroundColor: Colors.white, padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
                child: const Text('Nâng Cấp Thành Tác Giả', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDashboard(Color cardColor, Color textColor, Color subColor, Color borderColor, Color accent) {
    final stories = (_stats['story_list'] as List?) ?? [];
    return SingleChildScrollView(
      padding: const EdgeInsets.all(15),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Thống Kê', style: TextStyle(color: textColor, fontSize: 18, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          GridView.count(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            crossAxisCount: 2,
            crossAxisSpacing: 10,
            mainAxisSpacing: 10,
            childAspectRatio: 1.8,
            children: [
              _statCard(Icons.book, 'Truyện', '${_stats['stories'] ?? 0}', const Color(0xFF3568D4), cardColor, textColor),
              _statCard(Icons.article, 'Chương', '${_stats['total_chapters'] ?? 0}', const Color(0xFF4CAF50), cardColor, textColor),
              _statCard(Icons.visibility, 'Lượt xem', '${_stats['total_views'] ?? 0}', const Color(0xFFFF9800), cardColor, textColor),
              _statCard(Icons.diamond, 'Doanh thu', '${_stats['total_revenue'] ?? 0} LT', const Color(0xFFE91E63), cardColor, textColor),
            ],
          ),
          const SizedBox(height: 24),
          Text('Truyện Của Tôi', style: TextStyle(color: textColor, fontSize: 18, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          if (stories.isEmpty)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(30),
              decoration: BoxDecoration(color: cardColor, borderRadius: BorderRadius.circular(12), border: Border.all(color: borderColor)),
              child: Center(child: Text('Chưa có truyện nào', style: TextStyle(color: subColor, fontSize: 14))),
            )
          else
            ...stories.map((s) => Container(
              margin: const EdgeInsets.only(bottom: 10),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(color: cardColor, borderRadius: BorderRadius.circular(12), border: Border.all(color: borderColor)),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(s['title']?.toString() ?? '', style: TextStyle(color: textColor, fontSize: 15, fontWeight: FontWeight.w600)),
                  const SizedBox(height: 8),
                  Row(children: [
                    _statChip(Icons.visibility, '${s['views'] ?? 0}', accent),
                    const SizedBox(width: 14),
                    _statChip(Icons.article, '${s['chapters'] ?? 0} chương', accent),
                    const SizedBox(width: 14),
                    _statChip(Icons.diamond, '${s['revenue'] ?? 0} LT', accent),
                  ]),
                ],
              ),
            )),
        ],
      ),
    );
  }

  Widget _statCard(IconData icon, String label, String value, Color color, Color cardColor, Color textColor) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: cardColor,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: color.withOpacity(0.2)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: color, size: 22),
          const SizedBox(height: 6),
          Text(value, style: TextStyle(color: textColor, fontSize: 20, fontWeight: FontWeight.bold)),
          Text(label, style: TextStyle(color: Colors.grey[500], fontSize: 12)),
        ],
      ),
    );
  }

  Widget _statChip(IconData icon, String text, Color accent) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: accent),
        const SizedBox(width: 4),
        Text(text, style: TextStyle(color: Colors.grey[500], fontSize: 12)),
      ],
    );
  }
}
