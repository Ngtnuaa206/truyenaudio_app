import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/story.dart';
import '../models/chapter.dart';

class ApiService {
  static const String baseUrl = 'http://192.168.1.17/wordpress/wp-json/wp/v2';
  static const String customUrl = 'http://192.168.1.17/wordpress/wp-json/truyenaudio/v1';
  static const String siteUrl = 'http://192.168.1.17/wordpress';

  Future<String?> _token() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  Map<String, String> _authHeaders(String? token) {
    final h = <String, String>{'Content-Type': 'application/json'};
    if (token != null) h['Authorization'] = 'Bearer $token';
    return h;
  }

  Future<List<Story>> getStories({int page = 1, int perPage = 20, String? genre, String orderBy = 'date'}) async {
    final params = <String, String>{
      'page': page.toString(),
      'per_page': perPage.toString(),
      '_embed': 'wp:featuredmedia,wp:term',
      'orderby': orderBy == 'views' ? 'meta_value_num' : orderBy,
    };
    if (orderBy == 'views') params['meta_key'] = '_views';
    if (genre != null && genre.isNotEmpty) params['the_loai'] = genre;

    final uri = Uri.parse('$baseUrl/truyen').replace(queryParameters: params);
    final res = await http.get(uri);
    if (res.statusCode == 200) {
      final List data = jsonDecode(res.body);
      return data.map((e) => Story.fromJson(e)).toList();
    }
    return [];
  }

  Future<List<Story>> searchStories(String query) async {
    final params = {'search': query, 'per_page': '20', '_embed': 'wp:featuredmedia,wp:term'};
    final uri = Uri.parse('$baseUrl/truyen').replace(queryParameters: params);
    final res = await http.get(uri);
    if (res.statusCode == 200) {
      final List data = jsonDecode(res.body);
      return data.map((e) => Story.fromJson(e)).toList();
    }
    return [];
  }

  Future<Story?> getStory(int id) async {
    final res = await http.get(Uri.parse('$customUrl/stories/$id'));
    if (res.statusCode == 200) {
      return Story.fromJson(jsonDecode(res.body));
    }
    return null;
  }

  Future<List<Chapter>> getChapters(int storyId) async {
    final res = await http.get(Uri.parse('$customUrl/stories/$storyId/chapters'));
    if (res.statusCode == 200) {
      final List data = jsonDecode(res.body);
      final chapters = data.map((e) => Chapter.fromJson(e)).toList();
      chapters.sort((a, b) => a.chapterNumber.compareTo(b.chapterNumber));
      return chapters;
    }
    return [];
  }

  Future<Chapter?> getChapter(int id) async {
    final res = await http.get(Uri.parse('$baseUrl/chapter/$id'));
    if (res.statusCode == 200) return Chapter.fromJson(jsonDecode(res.body));
    return null;
  }

  Future<List<Story>> getTrending() async {
    final res = await http.get(Uri.parse('$baseUrl/truyen?_embed=wp:featuredmedia,wp:term&meta_key=_views&orderby=meta_value_num&order=desc&per_page=10'));
    if (res.statusCode == 200) {
      final List data = jsonDecode(res.body);
      return data.map((e) => Story.fromJson(e)).toList();
    }
    return [];
  }

  Future<List<Story>> getPopular() async {
    final res = await http.get(Uri.parse('$baseUrl/truyen?_embed=wp:featuredmedia,wp:term&meta_key=_rating&orderby=meta_value_num&order=desc&per_page=10'));
    if (res.statusCode == 200) {
      final List data = jsonDecode(res.body);
      return data.map((e) => Story.fromJson(e)).toList();
    }
    return [];
  }

  Future<List<Map<String, dynamic>>> getGenres() async {
    final res = await http.get(Uri.parse('$baseUrl/the_loai?per_page=50'));
    if (res.statusCode == 200) return List<Map<String, dynamic>>.from(jsonDecode(res.body));
    return [];
  }

  Future<Map<String, dynamic>?> login(String username, String password) async {
    final res = await http.post(
      Uri.parse('$siteUrl/wp-json/jwt-auth/v1/token'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'username': username, 'password': password}),
    );
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('token', data['token']);
      await prefs.setString('username', data['user_display_name'] ?? username);

      final userRes = await http.get(
        Uri.parse('$siteUrl/wp-json/wp/v2/users/me'),
        headers: {'Authorization': 'Bearer ${data["token"]}'},
      );
      if (userRes.statusCode == 200) {
        final userData = jsonDecode(userRes.body);
        final roles = (userData['roles'] as List?)?.cast<String>() ?? [];
        String role = 'subscriber';
        if (roles.contains('administrator')) role = 'administrator';
        await prefs.setString('user_role', role);
      }
      return data;
    }
    return null;
  }

  Future<bool> register(String username, String email, String password) async {
    final res = await http.post(
      Uri.parse('$siteUrl/wp-json/wp/v2/users/register'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'username': username, 'email': email, 'password': password}),
    );
    return res.statusCode == 200 || res.statusCode == 201;
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    await prefs.remove('username');
    await prefs.remove('user_role');
  }

  Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.containsKey('token');
  }

  Future<String?> getUsername() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('username');
  }

  Future<String?> getUserRole() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('user_role');
  }

  Future<bool> isAdmin() async {
    final role = await getUserRole();
    return role == 'administrator';
  }

  Future<bool> submitRating(int postId, int rating) async {
    final token = await _token();
    final res = await http.post(
      Uri.parse('$baseUrl/rate_story'),
      headers: _authHeaders(token),
      body: jsonEncode({'post_id': postId, 'rating': rating}),
    );
    return res.statusCode == 200;
  }

  Future<bool?> toggleBookmark(int postId) async {
    final token = await _token();
    if (token == null) return null;
    final res = await http.post(
      Uri.parse('$baseUrl/toggle_bookmark'),
      headers: _authHeaders(token),
      body: jsonEncode({'post_id': postId}),
    );
    if (res.statusCode == 200) {
      final data = jsonDecode(res.body);
      return data['status'] == 'added';
    }
    return null;
  }

  Future<void> saveHistory(int storyId, int chapterId) async {
    final token = await _token();
    if (token == null) return;
    await http.post(
      Uri.parse('$baseUrl/save_history'),
      headers: _authHeaders(token),
      body: jsonEncode({'story_id': storyId, 'chapter_id': chapterId}),
    );
  }

  Future<List<Story>> getBookmarks() async {
    final token = await _token();
    if (token == null) return [];
    final res = await http.get(
      Uri.parse('$baseUrl/truyen?_embed=wp:featuredmedia,wp:term&per_page=50'),
      headers: _authHeaders(token),
    );
    if (res.statusCode == 200) {
      final List data = jsonDecode(res.body);
      return data.map((e) => Story.fromJson(e)).toList();
    }
    return [];
  }

  Future<List<Map<String, dynamic>>> getHistory() async {
    final token = await _token();
    if (token == null) return [];
    final res = await http.get(Uri.parse('$baseUrl/save_history'), headers: _authHeaders(token));
    if (res.statusCode == 200) return List<Map<String, dynamic>>.from(jsonDecode(res.body));
    return [];
  }
}
