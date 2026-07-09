class Chapter {
  final int id;
  final String title;
  final String content;
  final int chapterNumber;
  final String audioUrl;
  final bool isVip;
  final int storyId;

  Chapter({
    required this.id,
    required this.title,
    this.content = '',
    this.chapterNumber = 0,
    this.audioUrl = '',
    this.isVip = false,
    this.storyId = 0,
  });

  factory Chapter.fromJson(Map<String, dynamic> json) {
    String getMeta(String key) {
      try { return json['meta'][key]?.toString() ?? ''; } catch (_) { return ''; }
    }

    return Chapter(
      id: json['id'] ?? 0,
      title: json['title']?['rendered'] ?? json['title']?.toString() ?? '',
      content: json['content']?['rendered'] ?? '',
      chapterNumber: int.tryParse(getMeta('_chapter_number')) ?? 0,
      audioUrl: getMeta('_audio_url'),
      isVip: getMeta('_is_vip') == '1',
      storyId: int.tryParse(getMeta('_story_id')) ?? 0,
    );
  }
}
