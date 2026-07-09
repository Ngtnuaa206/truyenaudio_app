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
        return (json['_embedded']?['wp:term'] as List?)
            ?.expand((l) => l as List)
            ?.where((t) => t['taxonomy'] == taxonomy)
            ?.map<String>((t) => t['name']?.toString() ?? '')
            ?.toList() ?? [];
      } catch (_) { return []; }
    }

    return Story(
      id: json['id'] ?? 0,
      title: json['title']?['rendered'] ?? json['title']?.toString() ?? '',
      excerpt: (json['excerpt']?['rendered'] ?? '').replaceAll(RegExp(r'<[^>]*>'), ''),
      content: json['content']?['rendered'] ?? '',
      thumbnail: json['_embedded']?['wp:featuredmedia']?[0]?['source_url'] ?? '',
      views: getMetaInt('_views'),
      rating: getMetaDouble('_rating'),
      ratingCount: getMetaInt('_rating_count'),
      chapterCount: getMetaInt('_chapter_count'),
      status: getMeta('_status'),
      genres: getTerms('the_loai'),
      authors: getTerms('tac_gia'),
    );
  }
}
