class Story {
  final int id;
  final String title;
  final String excerpt;
  final String content;
  final String thumbnail;
  final int views;
  final double rating;
  final int ratingCount;
  final int chapterCount;
  final String status;
  final List<String> genres;
  final List<String> authors;

  Story({
    required this.id,
    required this.title,
    this.excerpt = '',
    this.content = '',
    this.thumbnail = '',
    this.views = 0,
    this.rating = 0,
    this.ratingCount = 0,
    this.chapterCount = 0,
    this.status = '',
    this.genres = const [],
    this.authors = const [],
  });

  factory Story.fromJson(Map<String, dynamic> json) {
    String getMeta(String key) {
      try { return json['meta'][key]?.toString() ?? ''; } catch (_) { return ''; }
    }
    int getMetaInt(String key) {
      try { return int.tryParse(json['meta'][key]?.toString() ?? '0') ?? 0; } catch (_) { return 0; }
    }
    double getMetaDouble(String key) {
      try { return double.tryParse(json['meta'][key]?.toString() ?? '0') ?? 0; } catch (_) { return 0; }
    }

    List<String> getTerms(String taxonomy) {
      try {
        // Custom API returns flat lists
        if (json['genres'] != null && taxonomy == 'the_loai') {
          return (json['genres'] as List).map<String>((t) => t.toString()).toList();
        }
        if (json['authors'] != null && taxonomy == 'tac_gia') {
          return (json['authors'] as List).map<String>((t) => t.toString()).toList();
        }
        // WP embedded terms
        return (json['_embedded']?['wp:term'] as List?)
            ?.expand((l) => l as List)
            ?.where((t) => t['taxonomy'] == taxonomy)
            ?.map<String>((t) => t['name']?.toString() ?? '')
            ?.toList() ?? [];
      } catch (_) { return []; }
    }

    // Custom API returns 'thumbnail' directly
    String thumb = '';
    if (json['thumbnail'] != null && json['thumbnail'].toString().isNotEmpty) {
      thumb = json['thumbnail'].toString();
    } else if (json['_embedded']?['wp:featuredmedia'] != null) {
      thumb = json['_embedded']?['wp:featuredmedia']?[0]?['source_url'] ?? '';
    }

    int chapterCount = getMetaInt('_chapter_count');
    if (chapterCount == 0 && json['chapter_count'] != null) {
      chapterCount = int.tryParse(json['chapter_count'].toString()) ?? 0;
    }

    return Story(
      id: json['id'] ?? 0,
      title: json['title']?['rendered'] ?? json['title']?.toString() ?? '',
      excerpt: (json['excerpt']?['rendered'] ?? '').replaceAll(RegExp(r'<[^>]*>'), ''),
      content: json['content']?['rendered'] ?? '',
      thumbnail: thumb,
      views: getMetaInt('_views'),
      rating: getMetaDouble('_rating'),
      ratingCount: getMetaInt('_rating_count'),
      chapterCount: chapterCount,
      status: getMeta('_status'),
      genres: getTerms('the_loai'),
      authors: getTerms('tac_gia'),
    );
  }
}
