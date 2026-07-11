import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/story.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final ApiService _api = ApiService();
  String? _username;
  String? _userRole;
  List<Story> _bookmarks = [];
  List<Map<String, dynamic>> _history = [];
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final results = await Future.wait([
      _api.getUsername(),
      _api.getUserRole(),
      _api.getBookmarks(),
      _api.getHistory(),
    ]);
    if (!mounted) return;
    setState(() {
      _username = results[0] as String?;
      _userRole = results[1] as String?;
      _bookmarks = results[2] as List<Story>;
      _history = results[3] as List<Map<String, dynamic>>;
      _loading = false;
    });
  }

  String _roleName() {
    if (_userRole == 'administrator') return 'Quản trị viên';
    if (_userRole == 'tac_gia_role') return 'Tác giả';
    return 'Đọc giả';
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
        title: const Text('Hồ Sơ', style: TextStyle(color: Colors.white, fontSize: 18)),
        leading: IconButton(icon: const Icon(Icons.arrow_back, color: Colors.white), onPressed: () => Navigator.pop(context)),
      ),
      body: _loading
          ? Center(child: CircularProgressIndicator(color: accent))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(15),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 50,
                    backgroundColor: accent,
                    child: Text((_username ?? 'U').substring(0, 1).toUpperCase(),
                        style: const TextStyle(color: Colors.white, fontSize: 36, fontWeight: FontWeight.bold)),
                  ),
                  const SizedBox(height: 12),
                  Text(_username ?? 'User', style: TextStyle(color: textColor, fontSize: 22, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 6),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    decoration: BoxDecoration(color: accent.withOpacity(0.15), borderRadius: BorderRadius.circular(12)),
                    child: Text(_roleName(), style: TextStyle(color: accent, fontSize: 13)),
                  ),
                  const SizedBox(height: 24),
                  _buildSection('Yêu thích (${_bookmarks.length})', cardColor, textColor, borderColor, subColor,
                    _bookmarks.isEmpty
                        ? [Padding(padding: const EdgeInsets.all(20), child: Text('Chưa có truyện yêu thích', style: TextStyle(color: subColor, fontSize: 13)))]
                        : _bookmarks.take(10).map((s) => ListTile(
                            contentPadding: const EdgeInsets.symmetric(horizontal: 8),
                            leading: Icon(Icons.bookmark, color: accent, size: 18),
                            title: Text(s.title, style: TextStyle(color: textColor, fontSize: 14), maxLines: 1, overflow: TextOverflow.ellipsis),
                            dense: true,
                          )).toList(),
                  ),
                  const SizedBox(height: 16),
                  _buildSection('Lịch sử đọc (${_history.length})', cardColor, textColor, borderColor, subColor,
                    _history.isEmpty
                        ? [Padding(padding: const EdgeInsets.all(20), child: Text('Chưa có lịch sử đọc', style: TextStyle(color: subColor, fontSize: 13)))]
                        : _history.take(10).map((item) => ListTile(
                            contentPadding: const EdgeInsets.symmetric(horizontal: 8),
                            leading: Icon(Icons.history, color: accent, size: 18),
                            title: Text(item['title']?.toString() ?? 'Truyện', style: TextStyle(color: textColor, fontSize: 14), maxLines: 1, overflow: TextOverflow.ellipsis),
                            dense: true,
                          )).toList(),
                  ),
                ],
              ),
            ),
    );
  }

  Widget _buildSection(String title, Color cardColor, Color textColor, Color borderColor, Color subColor, List<Widget> children) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: cardColor, borderRadius: BorderRadius.circular(12), border: Border.all(color: borderColor)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: TextStyle(color: textColor, fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          ...children,
        ],
      ),
    );
  }
}
