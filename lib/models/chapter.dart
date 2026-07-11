class Chapter {
  final int id;
  final String title;
  final String content;
  final int chapterNumber;
  final String audioUrl;
  final bool isVip;
  final int vipPrice;
  final int storyId;
  final bool canRead;

  Chapter({
    required this.id,
    required this.title,
    this.content = '',
    this.chapterNumber = 0,
    this.audioUrl = '',
    this.isVip = false,
    this.vipPrice = 5,
    this.storyId = 0,
    this.canRead = true,
  });

  factory Chapter.fromJson(Map<String, dynamic> json) {
    String getMeta(String key) {
      try { return json['meta'][key]?.toString() ?? ''; } catch (_) { return ''; }
    }

    final contentRaw = json['content'];
    String contentText;
    if (contentRaw is String) {
      contentText = contentRaw;
    } else if (contentRaw is Map) {
      contentText = contentRaw['rendered']?.toString() ?? '';
    } else {
      contentText = '';
    }

    return Chapter(
      id: json['id'] ?? 0,
      title: json['title']?['rendered'] ?? json['title']?.toString() ?? '',
      content: contentText,
      chapterNumber: int.tryParse(getMeta('_chapter_number')) ?? 0,
      audioUrl: getMeta('_audio_url'),
      isVip: getMeta('_is_vip') == '1',
      vipPrice: int.tryParse(getMeta('_vip_price')) ?? 5,
      storyId: int.tryParse(getMeta('_story_id')) ?? 0,
      canRead: json['can_read'] ?? true,
    );
  }
}
