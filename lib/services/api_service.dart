import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/story.dart';
import '../models/chapter.dart';

class ApiService {
  static const String baseUrl = 'http://192.168.1.17/wordpress/wp-json/wp/v2';
  static const String siteUrl = 'http://192.168.1.17/wordpress';

  Future<List<Story>> getStories({
    int page = 1,
    int perPage = 20,
    String? genre,
    String? author,
    String? status,
    String orderBy = 'date',
  }) async {
    final params = {
      'page': page.toString(),
      'per_page': perPage.toString(),
      '_embed': 'wp:featuredmedia,wp:term',
      'orderby': orderBy == 'views' ? 'meta_value_num' : orderBy,
    };
    if (orderBy == 'views') params['meta_key'] = '_views';

    final uri = Uri.parse('$baseUrl/truyen').replace(queryParameters: params);
    final res = await http.get(uri);

    if (res.statusCode == 200) {
      final List data = jsonDecode(res.body);
      return data.map((e) => Story.fromJson(e)).toList();
    }
    return [];
  }

  Future<Story?> getStory(int id) async {
    final res = await http.get(Uri.parse('$baseUrl/truyen/$id?_embed=wp:featuredmedia,wp:term'));
    if (res.statusCode == 200) {
      return Story.fromJson(jsonDecode(res.body));
    }
    return null;
  }

  Future<List<Chapter>> getChapters(int storyId) async {
    final res = await http.get(Uri.parse('$baseUrl/chapter?meta_key=_story_id&meta_value=$storyId&orderby=meta_value_num&order=asc&meta_key=_chapter_number&per_page=100'));
    if (res.statusCode == 200) {
      final List data = jsonDecode(res.body);
      return data.map((e) => Chapter.fromJson(e)).toList();
    }
    return [];
  }

  Future<Chapter?> getChapter(int id) async {
    final res = await http.get(Uri.parse('$baseUrl/chapter/$id'));
    if (res.statusCode == 200) {
      return Chapter.fromJson(jsonDecode(res.body));
    }
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
    if (res.statusCode == 200) {
      return List<Map<String, dynamic>>.from(jsonDecode(res.body));
    }
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
      return data;
    }
    return null;
  }

  Future<bool> register(String username, String email, String password) async {
    final res = await http.post(
      Uri.parse('$siteUrl/wp-json/wp/v2/users/register'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'username': username,
        'email': email,
        'password': password,
      }),
    );
    return res.statusCode == 200 || res.statusCode == 201;
  }

  Future<Map<String, dynamic>?> getUser() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('token');
    if (token == null) return null;

    final res = await http.get(
      Uri.parse('$siteUrl/wp-json/wp/v2/users/me'),
      headers: {'Authorization': 'Bearer $token'},
    );
    if (res.statusCode == 200) return jsonDecode(res.body);
    return null;
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('token');
    await prefs.remove('username');
  }

  Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.containsKey('token');
  }

  Future<String?> getUsername() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('username');
  }
}
