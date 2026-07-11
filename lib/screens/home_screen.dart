import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../widgets/story_card.dart';
import '../models/story.dart';
import 'story_detail_screen.dart';
import 'login_screen.dart';
import 'search_screen.dart';
import 'profile_screen.dart';
import 'genre_stories_screen.dart';

class HomeScreen extends StatefulWidget {
  final VoidCallback onToggleTheme;
  final bool isDark;
  const HomeScreen({super.key, required this.onToggleTheme, required this.isDark});

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
  String? _selectedGenreId;

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
    if (!mounted) return;
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

  Future<void> _filterByGenre(String? genreId) async {
    setState(() { _selectedGenreId = genreId; _loading = true; });
    final stories = await _api.getStories(perPage: 20, genre: genreId);
    if (!mounted) return;
    setState(() { _newStories = stories; _loading = false; });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final bgColor = isDark ? const Color(0xFF0A0E1A) : const Color(0xFFF0F2F5);
    final textColor = isDark ? Colors.white : const Color(0xFF1A1A2E);

    return Scaffold(
      backgroundColor: bgColor,
      appBar: AppBar(
        backgroundColor: isDark ? const Color(0xFF141929) : const Color(0xFF2D4373),
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('Truyện', style: TextStyle(color: isDark ? const Color(0xFF64B5F6) : Colors.white, fontWeight: FontWeight.w800, fontSize: 20)),
            Text('Audio', style: TextStyle(color: isDark ? Colors.white70 : Colors.white70, fontWeight: FontWeight.w600, fontSize: 20)),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.search, color: Colors.white),
            onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const SearchScreen())),
          ),
          IconButton(
            icon: Icon(widget.isDark ? Icons.light_mode_rounded : Icons.dark_mode_rounded, color: Colors.white),
            onPressed: widget.onToggleTheme,
          ),
          if (_loggedIn)
            PopupMenuButton<String>(
              icon: CircleAvatar(
                radius: 15,
                backgroundColor: isDark ? const Color(0xFF64B5F6) : Colors.white,
                child: Text(_username?.substring(0, 1).toUpperCase() ?? 'U',
                    style: TextStyle(color: isDark ? const Color(0xFF141929) : const Color(0xFF2D4373), fontWeight: FontWeight.bold, fontSize: 13)),
              ),
              color: isDark ? const Color(0xFF1A1F33) : Colors.white,
              onSelected: (v) async {
                if (v == 'logout') {
                  await _api.logout();
                  if (mounted) setState(() { _loggedIn = false; });
                } else if (v == 'profile') {
                  Navigator.push(context, MaterialPageRoute(builder: (_) => const ProfileScreen()));
                }
              },
              itemBuilder: (_) => [
                PopupMenuItem(value: 'profile', child: Row(children: [
                  Icon(Icons.person_outline, size: 18, color: isDark ? Colors.white70 : Colors.black54),
                  const SizedBox(width: 10),
                  Text('Hồ sơ', style: TextStyle(color: isDark ? Colors.white : Colors.black87)),
                ])),
                const PopupMenuItem(value: 'logout', child: Row(children: [
                  Icon(Icons.logout, size: 18, color: Colors.red),
                  SizedBox(width: 10),
                  Text('Đăng xuất', style: TextStyle(color: Colors.red)),
                ])),
              ],
            )
          else
            TextButton(
              onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => const LoginScreen())).then((_) => _load()),
              child: const Text('Đăng nhập', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
            ),
        ],
      ),
      body: _loading
          ? Center(child: CircularProgressIndicator(color: isDark ? const Color(0xFF64B5F6) : const Color(0xFF2D4373)))
          : RefreshIndicator(
              color: isDark ? const Color(0xFF64B5F6) : const Color(0xFF2D4373),
              onRefresh: _load,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildHero(isDark, textColor),
                    _buildFilter(isDark, textColor),
                    if (_newStories.isNotEmpty) _buildSection('Truyện Mới Đăng', _newStories, isDark, textColor),
                    if (_trending.isNotEmpty) _buildSection('Trending', _trending, isDark, textColor),
                    if (_popular.isNotEmpty) _buildSection('Phổ Biến', _popular, isDark, textColor),
                    if (_genres.isNotEmpty) _buildGenres(isDark, textColor),
                    const SizedBox(height: 30),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildHero(bool isDark, Color textColor) {
    final accent = isDark ? const Color(0xFF64B5F6) : const Color(0xFF2D4373);
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 32, horizontal: 20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: isDark
              ? [const Color(0xFF141929), const Color(0xFF1A2744)]
              : [const Color(0xFF2D4373), const Color(0xFF3D5BA9)],
        ),
      ),
      child: Column(
        children: [
          Icon(Icons.headphones_rounded, size: 48, color: accent.withOpacity(0.7)),
          const SizedBox(height: 12),
          const Text('Nghe là nghiện, Đọc là Mê', style: TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold), textAlign: TextAlign.center),
          const SizedBox(height: 8),
          Text('Kho truyện audio khổng lồ', style: TextStyle(color: Colors.white54, fontSize: 13), textAlign: TextAlign.center),
        ],
      ),
    );
  }

  Widget _buildFilter(bool isDark, Color textColor) {
    final chipBg = isDark ? const Color(0xFF1A2040) : const Color(0xFFE8EDF5);
    final chipSel = isDark ? const Color(0xFF64B5F6) : const Color(0xFF2D4373);

    return Container(
      margin: const EdgeInsets.fromLTRB(15, 15, 15, 5),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Thể loại', style: TextStyle(color: textColor, fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 10),
          SizedBox(
            height: 38,
            child: ListView(
              scrollDirection: Axis.horizontal,
              children: [
                _filterChip('Tất cả', _selectedGenreId == null, chipBg, chipSel, () => _filterByGenre(null)),
                ...(_genres.take(15).map((g) => _filterChip(
                  g['name']?.toString() ?? '',
                  _selectedGenreId?.toString() == g['id'].toString(),
                  chipBg, chipSel,
                  () => _filterByGenre(g['id'].toString()),
                ))),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _filterChip(String label, bool selected, Color chipBg, Color chipSel, VoidCallback onTap) {
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: GestureDetector(
        onTap: onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          decoration: BoxDecoration(
            color: selected ? chipSel : chipBg,
            borderRadius: BorderRadius.circular(20),
          ),
          child: Text(label, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500,
              color: selected ? Colors.white : Colors.grey)),
        ),
      ),
    );
  }

  Widget _buildSection(String title, List<Story> stories, bool isDark, Color textColor) {
    return Padding(
      padding: const EdgeInsets.only(left: 15, right: 15, bottom: 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: TextStyle(color: textColor, fontSize: 17, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          SizedBox(
            height: 260,
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

  Widget _buildGenres(bool isDark, Color textColor) {
    final chipBg = isDark ? const Color(0xFF141929) : Colors.white;
    final borderColor = isDark ? const Color(0xFF1E2A45) : const Color(0xFFE0E5EC);
    final subColor = isDark ? Colors.grey[500]! : Colors.grey[600]!;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 15),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text('Thể Loại', style: TextStyle(color: textColor, fontSize: 17, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: _genres.map((g) {
              return GestureDetector(
                onTap: () => Navigator.push(context, MaterialPageRoute(
                  builder: (_) => GenreStoriesScreen(genreId: g['id'].toString(), genreName: g['name']?.toString() ?? ''),
                )),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                  decoration: BoxDecoration(color: chipBg, borderRadius: BorderRadius.circular(10), border: Border.all(color: borderColor)),
                  child: Column(
                    children: [
                      Text(g['name']?.toString() ?? '', style: TextStyle(color: textColor, fontSize: 13, fontWeight: FontWeight.w600)),
                      Text('${g['count'] ?? 0} truyện', style: TextStyle(color: subColor, fontSize: 11)),
                    ],
                  ),
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
