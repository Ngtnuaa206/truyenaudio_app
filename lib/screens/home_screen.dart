import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/story_card.dart';
import '../models/story.dart';
import 'story_detail_screen.dart';
import 'login_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final ApiService _api = ApiService();
  List<Story> _newStories = [];
  List<Story> _trending = [];
  List<Story> _popular = [];
  List<Map<String, dynamic>> _genres = [];
  bool _loading = true;
  bool _loggedIn = false;
  String? _username;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final results = await Future.wait([
      _api.getStories(perPage: 12),
      _api.getTrending(),
      _api.getPopular(),
      _api.getGenres(),
      _api.isLoggedIn(),
      _api.getUsername(),
    ]);
    setState(() {
      _newStories = results[0] as List<Story>;
      _trending = results[1] as List<Story>;
      _popular = results[2] as List<Story>;
      _genres = results[3] as List<Map<String, dynamic>>;
      _loggedIn = results[4] as bool;
      _username = results[5] as String?;
      _loading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F0F1A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1A1A2E),
        title: const Row(
          children: [
            Text('Truyen', style: TextStyle(color: Color(0xFFF0C040), fontWeight: FontWeight.w800, fontSize: 20)),
            Text('Audio', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800, fontSize: 20)),
          ],
        ),
        actions: [
          if (_loggedIn)
            PopupMenuButton<String>(
              icon: CircleAvatar(
                radius: 15,
                backgroundColor: const Color(0xFFF0C040),
                child: Text(_username?.substring(0, 1).toUpperCase() ?? 'U', style: const TextStyle(color: Color(0xFF1A1A2E), fontWeight: FontWeight.bold)),
              ),
              color: const Color(0xFF1A1A2E),
              onSelected: (v) async {
                if (v == 'logout') {
                  await _api.logout();
                  setState(() => _loggedIn = false);
                }
              },
              itemBuilder: (_) => [
                const PopupMenuItem(value: 'profile', child: Text('Profile', style: TextStyle(color: Colors.white))),
                const PopupMenuItem(value: 'logout', child: Text('Đăng xuất', style: TextStyle(color: Colors.red))),
              ],
            )
          else
            TextButton(
              onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen())).then((_) => _load()),
              child: const Text('Đăng nhập', style: TextStyle(color: Color(0xFFF0C040))),
            ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFFF0C040)))
          : RefreshIndicator(
              color: const Color(0xFFF0C040),
              onRefresh: _load,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildHero(),
                    _buildFilter(),
                    if (_newStories.isNotEmpty) _buildSection('Truyện Mới Đăng', _newStories),
                    if (_trending.isNotEmpty) _buildSection('Truyện Trending', _trending),
                    if (_genres.isNotEmpty) _buildGenres(),
                    if (_popular.isNotEmpty) _buildSection('Truyện Phổ Biến', _popular),
                    const SizedBox(height: 20),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildHero() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 40, horizontal: 20),
      decoration: const BoxDecoration(
        gradient: LinearGradient(colors: [Color(0xFF1A1A2E), Color(0xFF16213E)]),
      ),
      child: Column(
        children: [
          const Text('Nghe là nghiện, Đọc là Mê', style: TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.bold)),
          const SizedBox(height: 10),
          Text('Kho truyện audio khổng lồ với hàng ngàn bộ truyện', style: TextStyle(color: Colors.grey[500], fontSize: 14)),
          const SizedBox(height: 20),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _heroBtn('Khám Phá Ngay', true),
              const SizedBox(width: 12),
              _heroBtn('Bảng Xếp Hạng', false),
            ],
          ),
        ],
      ),
    );
  }

  Widget _heroBtn(String text, bool primary) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
      decoration: BoxDecoration(
        color: primary ? const Color(0xFFF0C040) : Colors.transparent,
        borderRadius: BorderRadius.circular(8),
        border: Border.all(color: const Color(0xFFF0C040)),
      ),
      child: Text(text, style: TextStyle(color: primary ? const Color(0xFF1A1A2E) : const Color(0xFFF0C040), fontWeight: FontWeight.w600)),
    );
  }

  Widget _buildFilter() {
    return Container(
      margin: const EdgeInsets.all(15),
      padding: const EdgeInsets.all(15),
      decoration: BoxDecoration(
        color: const Color(0xFF1A1A2E),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFF2A2A4E)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Bộ Lọc Truyện', style: TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w600)),
          const SizedBox(height: 10),
          SizedBox(
            height: 40,
            child: ListView(
              scrollDirection: Axis.horizontal,
              children: [
                _filterChip('Tất cả', true),
                ...(_genres.take(15).map((g) => _filterChip(g['name']?.toString() ?? '', false))),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _filterChip(String label, bool selected) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: Chip(
        label: Text(label, style: TextStyle(fontSize: 12, color: selected ? const Color(0xFF1A1A2E) : Colors.white)),
        backgroundColor: selected ? const Color(0xFFF0C040) : const Color(0xFF2A2A4E),
        side: BorderSide.none,
        padding: EdgeInsets.zero,
        materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
      ),
    );
  }

  Widget _buildSection(String title, List<Story> stories) {
    return Padding(
      padding: const EdgeInsets.only(left: 15, right: 15, bottom: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(title, style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
              const Text('Xem tất cả →', style: TextStyle(color: Color(0xFFF0C040), fontSize: 13)),
            ],
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 280,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: stories.length,
              itemBuilder: (_, i) {
                final s = stories[i];
                return SizedBox(
                  width: 150,
                  child: Padding(
                    padding: const EdgeInsets.only(right: 12),
                    child: StoryCard(
                      id: s.id,
                      title: s.title,
                      thumbnail: s.thumbnail,
                      views: _formatViews(s.views),
                      chapters: s.chapterCount.toString(),
                      rating: s.rating.toStringAsFixed(1),
                      genres: s.genres,
                      onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => StoryDetailScreen(storyId: s.id))),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildGenres() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 15),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Khám Phá Thể Loại', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: _genres.map((g) {
              return Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                decoration: BoxDecoration(
                  color: const Color(0xFF1A1A2E),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: const Color(0xFF2A2A4E)),
                ),
                child: Column(
                  children: [
                    Text(g['name']?.toString() ?? '', style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600)),
                    Text('${g['count'] ?? 0} truyện', style: const TextStyle(color: Color(0xFF888888), fontSize: 11)),
                  ],
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 20),
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
