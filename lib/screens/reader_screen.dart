import 'package:flutter/material.dart';
import 'package:just_audio/just_audio.dart';
import '../models/chapter.dart';

class ReaderScreen extends StatefulWidget {
  final int storyId;
  final List<Chapter> chapters;
  final int initialIndex;
  const ReaderScreen({
    super.key,
    required this.storyId,
    required this.chapters,
    this.initialIndex = 0,
  });

  @override
  State<ReaderScreen> createState() => _ReaderScreenState();
}

class _ReaderScreenState extends State<ReaderScreen> {
  late int _currentIndex;
  late AudioPlayer _player;
  bool _audioPlaying = false;
  bool _audioLoading = false;
  Duration _audioDuration = Duration.zero;
  Duration _audioPosition = Duration.zero;

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex.clamp(0, widget.chapters.length - 1);
    _player = AudioPlayer();
    _player.positionStream.listen((pos) { if (mounted) setState(() => _audioPosition = pos); });
    _player.durationStream.listen((dur) { if (mounted && dur != null) setState(() => _audioDuration = dur); });
    _player.playerStateStream.listen((state) {
      if (!mounted) return;
      setState(() {
        _audioPlaying = state.playing;
        _audioLoading = state.processingState == ProcessingState.loading || state.processingState == ProcessingState.buffering;
      });
      if (state.processingState == ProcessingState.completed) {
        if (_hasNext) { _stopAudio(); setState(() => _currentIndex++); }
      }
    });
  }

  @override
  void dispose() { _player.dispose(); super.dispose(); }

  Chapter get _current => widget.chapters[_currentIndex];
  bool get _hasPrev => _currentIndex > 0;
  bool get _hasNext => _currentIndex < widget.chapters.length - 1;

  Future<void> _playAudio(String url) async {
    try {
      setState(() => _audioLoading = true);
      await _player.setUrl(url);
      await _player.play();
    } catch (e) { if (mounted) setState(() => _audioLoading = false); }
  }

  void _stopAudio() {
    _player.stop();
    if (mounted) setState(() {
      _audioPlaying = false;
      _audioPosition = Duration.zero;
      _audioDuration = Duration.zero;
    });
  }

  void _togglePlay() {
    if (_current.audioUrl.isEmpty) return;
    if (_audioPlaying) _player.pause();
    else if (_audioPosition > Duration.zero) _player.play();
    else _playAudio(_current.audioUrl);
  }

  String _fmt(Duration d) {
    final m = d.inMinutes.remainder(60).toString().padLeft(2, '0');
    final s = d.inSeconds.remainder(60).toString().padLeft(2, '0');
    return '${d.inHours > 0 ? '${d.inHours}:' : ''}$m:$s';
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardColor = isDark ? const Color(0xFF141929) : Colors.white;
    final textColor = isDark ? Colors.white : const Color(0xFF1A1A2E);
    final subColor = isDark ? const Color(0xFF8899AA) : Colors.grey[600]!;
    final bgColor = isDark ? const Color(0xFF0A0E1A) : const Color(0xFFF0F2F5);
    final borderColor = isDark ? const Color(0xFF1E2A45) : const Color(0xFFE0E5EC);
    final accent = isDark ? const Color(0xFF64B5F6) : const Color(0xFF2D4373);

    return Scaffold(
      backgroundColor: bgColor,
      appBar: AppBar(
        backgroundColor: isDark ? const Color(0xFF141929) : const Color(0xFF2D4373),
        leading: IconButton(icon: const Icon(Icons.arrow_back, color: Colors.white), onPressed: () { _stopAudio(); Navigator.pop(context); }),
        title: Text('Ch.${_current.chapterNumber}: ${_current.title}', style: const TextStyle(color: Colors.white, fontSize: 14), maxLines: 1, overflow: TextOverflow.ellipsis),
        actions: [
          if (widget.chapters.length > 1)
            TextButton(
              onPressed: () => _showChapterList(context, cardColor, textColor, borderColor, accent),
              child: Text('Chapters', style: TextStyle(color: accent)),
            ),
        ],
      ),
      bottomNavigationBar: _buildBottomNav(cardColor, textColor, borderColor, accent),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (_current.audioUrl.isNotEmpty) _buildAudioPlayer(cardColor, textColor, borderColor, subColor, accent),
            const SizedBox(height: 24),
            Text(_current.title, style: TextStyle(color: textColor, fontSize: 22, fontWeight: FontWeight.bold)),
            const SizedBox(height: 20),
            _HtmlContent(content: _current.content),
          ],
        ),
      ),
    );
  }

  Widget _buildAudioPlayer(Color cardColor, Color textColor, Color borderColor, Color subColor, Color accent) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: cardColor, borderRadius: BorderRadius.circular(14), border: Border.all(color: borderColor)),
      child: Column(
        children: [
          Row(children: [
            Icon(Icons.headphones, color: accent, size: 20),
            const SizedBox(width: 8),
            Text('Nghe chương này', style: TextStyle(color: accent, fontWeight: FontWeight.w600)),
          ]),
          const SizedBox(height: 12),
          SliderTheme(
            data: SliderThemeData(activeTrackColor: accent, inactiveTrackColor: borderColor, thumbColor: accent, thumbShape: const RoundSliderThumbShape(enabledThumbRadius: 6)),
            child: Slider(
              value: _audioDuration.inMilliseconds > 0 ? _audioPosition.inMilliseconds.toDouble().clamp(0, _audioDuration.inMilliseconds.toDouble()) : 0,
              max: _audioDuration.inMilliseconds > 0 ? _audioDuration.inMilliseconds.toDouble() : 1,
              onChanged: (v) => _player.seek(Duration(milliseconds: v.toInt())),
            ),
          ),
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Text(_fmt(_audioPosition), style: TextStyle(color: subColor, fontSize: 12)),
            Text(_fmt(_audioDuration), style: TextStyle(color: subColor, fontSize: 12)),
          ]),
          const SizedBox(height: 8),
          Row(mainAxisAlignment: MainAxisAlignment.center, children: [
            IconButton(icon: Icon(Icons.replay_10, color: textColor), onPressed: () => _player.seek(_audioPosition - const Duration(seconds: 10))),
            const SizedBox(width: 16),
            GestureDetector(
              onTap: _audioLoading ? null : _togglePlay,
              child: Container(
                width: 56, height: 56,
                decoration: BoxDecoration(shape: BoxShape.circle, color: accent),
                child: _audioLoading
                    ? Padding(padding: const EdgeInsets.all(16), child: CircularProgressIndicator(strokeWidth: 3, color: Colors.white))
                    : Icon(_audioPlaying ? Icons.pause_rounded : Icons.play_arrow_rounded, color: Colors.white, size: 32),
              ),
            ),
            const SizedBox(width: 16),
            IconButton(icon: Icon(Icons.forward_30, color: textColor), onPressed: () => _player.seek(_audioPosition + const Duration(seconds: 30))),
          ]),
        ],
      ),
    );
  }

  Widget _buildBottomNav(Color cardColor, Color textColor, Color borderColor, Color accent) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(color: cardColor, border: Border(top: BorderSide(color: borderColor))),
      child: SafeArea(
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            _hasPrev
                ? TextButton.icon(onPressed: () { _stopAudio(); setState(() => _currentIndex--); },
                    icon: Icon(Icons.skip_previous, color: textColor, size: 20),
                    label: Text('Trước', style: TextStyle(color: textColor, fontSize: 12)))
                : const SizedBox(width: 80),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(color: accent.withOpacity(0.12), borderRadius: BorderRadius.circular(20)),
              child: Text('${_currentIndex + 1} / ${widget.chapters.length}', style: TextStyle(color: accent, fontSize: 14, fontWeight: FontWeight.w600)),
            ),
            _hasNext
                ? TextButton.icon(onPressed: () { _stopAudio(); setState(() => _currentIndex++); },
                    label: Icon(Icons.skip_next, color: textColor, size: 20),
                    icon: Text('Sau', style: TextStyle(color: textColor, fontSize: 12)))
                : const SizedBox(width: 80),
          ],
        ),
      ),
    );
  }

  void _showChapterList(BuildContext context, Color cardColor, Color textColor, Color borderColor, Color accent) {
    showModalBottomSheet(
      context: context,
      backgroundColor: cardColor,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(16))),
      builder: (_) => DraggableScrollableSheet(
        initialChildSize: 0.6, maxChildSize: 0.85, minChildSize: 0.3, expand: false,
        builder: (_, ctrl) => Column(
          children: [
            Container(
              padding: const EdgeInsets.all(15),
              decoration: BoxDecoration(border: Border(bottom: BorderSide(color: borderColor))),
              child: Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                Text('Danh sách chương', style: TextStyle(color: textColor, fontSize: 16, fontWeight: FontWeight.bold)),
                IconButton(icon: Icon(Icons.close, color: textColor), onPressed: () => Navigator.pop(context)),
              ]),
            ),
            Expanded(
              child: ListView.builder(
                controller: ctrl,
                itemCount: widget.chapters.length,
                itemBuilder: (_, i) {
                  final ch = widget.chapters[i];
                  final isCurrent = i == _currentIndex;
                  return ListTile(
                    selected: isCurrent,
                    selectedTileColor: accent.withOpacity(0.15),
                    leading: CircleAvatar(
                      radius: 16,
                      backgroundColor: isCurrent ? accent : borderColor,
                      child: Text('${ch.chapterNumber}', style: TextStyle(color: isCurrent ? Colors.white : textColor, fontSize: 12, fontWeight: FontWeight.bold)),
                    ),
                    title: Text(ch.title, style: TextStyle(color: isCurrent ? accent : textColor, fontSize: 14), maxLines: 1, overflow: TextOverflow.ellipsis),
                    trailing: Row(mainAxisSize: MainAxisSize.min, children: [
                      if (ch.isVip) Container(
                        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                        decoration: BoxDecoration(color: const Color(0xFFF0C040), borderRadius: BorderRadius.circular(3)),
                        child: const Text('VIP', style: TextStyle(color: Color(0xFF1A1A2E), fontSize: 9)),
                      ),
                      if (ch.audioUrl.isNotEmpty) Padding(
                        padding: const EdgeInsets.only(left: 4),
                        child: Icon(Icons.headphones, color: accent, size: 14),
                      ),
                    ]),
                    onTap: () { Navigator.pop(context); _stopAudio(); setState(() => _currentIndex = i); },
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _HtmlContent extends StatelessWidget {
  final String content;
  const _HtmlContent({required this.content});

  @override
  Widget build(BuildContext context) {
    final cleaned = content.replaceAll(RegExp(r'<[^>]*>'), '\n').replaceAll(RegExp(r'\n{3,}'), '\n\n').trim();
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Text(cleaned, style: TextStyle(color: isDark ? const Color(0xFFCCCCDD) : const Color(0xFF333333), fontSize: 16, height: 2));
  }
}
