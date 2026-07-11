import 'package:flutter/material.dart';
import '../services/api_service.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final ApiService _api = ApiService();
  final _userCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _regUserCtrl = TextEditingController();
  final _regEmailCtrl = TextEditingController();
  final _regPassCtrl = TextEditingController();
  bool _loginMode = true;
  bool _loading = false;

  @override
  void dispose() {
    _userCtrl.dispose();
    _passCtrl.dispose();
    _regUserCtrl.dispose();
    _regEmailCtrl.dispose();
    _regPassCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardColor = isDark ? const Color(0xFF141929) : Colors.white;
    final borderColor = isDark ? const Color(0xFF1E2A45) : const Color(0xFFE0E5EC);
    final textColor = isDark ? Colors.white : const Color(0xFF1A1A2E);
    final fillColor = isDark ? const Color(0xFF0A0E1A) : const Color(0xFFF0F2F5);
    final bgColor = isDark ? const Color(0xFF0A0E1A) : const Color(0xFFF0F2F5);
    final accent = isDark ? const Color(0xFF64B5F6) : const Color(0xFF2D4373);

    return Scaffold(
      backgroundColor: bgColor,
      appBar: AppBar(
        backgroundColor: isDark ? const Color(0xFF141929) : const Color(0xFF2D4373),
        leading: IconButton(icon: const Icon(Icons.arrow_back, color: Colors.white), onPressed: () => Navigator.pop(context)),
        title: Text(_loginMode ? 'Đăng nhập' : 'Đăng ký', style: const TextStyle(color: Colors.white)),
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Container(
            padding: const EdgeInsets.all(25),
            decoration: BoxDecoration(color: cardColor, borderRadius: BorderRadius.circular(16), border: Border.all(color: borderColor)),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.headphones_rounded, size: 48, color: accent),
                const SizedBox(height: 12),
                Text(_loginMode ? 'Đăng nhập' : 'Đăng ký', style: TextStyle(color: textColor, fontSize: 22, fontWeight: FontWeight.bold)),
                const SizedBox(height: 24),
                if (_loginMode) _buildLogin(fillColor, borderColor, textColor, accent) else _buildRegister(fillColor, borderColor, textColor, accent),
                const SizedBox(height: 15),
                TextButton(
                  onPressed: () => setState(() => _loginMode = !_loginMode),
                  child: Text(_loginMode ? 'Chưa có tài khoản? Đăng ký' : 'Đã có tài khoản? Đăng nhập', style: TextStyle(color: accent)),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildLogin(Color fillColor, Color borderColor, Color textColor, Color accent) {
    return Column(
      children: [
        _input('Tên đăng nhập', _userCtrl, fillColor, borderColor, textColor, accent),
        const SizedBox(height: 12),
        _input('Mật khẩu', _passCtrl, fillColor, borderColor, textColor, accent, obscure: true),
        const SizedBox(height: 20),
        _submitBtn('Đăng nhập', accent, () => _login()),
      ],
    );
  }

  Widget _buildRegister(Color fillColor, Color borderColor, Color textColor, Color accent) {
    return Column(
      children: [
        _input('Tên đăng nhập', _regUserCtrl, fillColor, borderColor, textColor, accent),
        const SizedBox(height: 12),
        _input('Email', _regEmailCtrl, fillColor, borderColor, textColor, accent),
        const SizedBox(height: 12),
        _input('Mật khẩu', _regPassCtrl, fillColor, borderColor, textColor, accent, obscure: true),
        const SizedBox(height: 20),
        _submitBtn('Đăng ký', accent, () => _register()),
      ],
    );
  }

  Widget _input(String label, TextEditingController ctrl, Color fillColor, Color borderColor, Color textColor, Color accent, {bool obscure = false}) {
    return TextField(
      controller: ctrl,
      obscureText: obscure,
      style: TextStyle(color: textColor),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(color: Color(0xFF888888)),
        filled: true,
        fillColor: fillColor,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: borderColor)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: borderColor)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide(color: accent)),
      ),
    );
  }

  Widget _submitBtn(String text, Color accent, VoidCallback onTap) {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton(
        onPressed: _loading ? null : onTap,
        style: ElevatedButton.styleFrom(
          backgroundColor: accent,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(vertical: 14),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
        child: _loading
            ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
            : Text(text, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
      ),
    );
  }

  Future<void> _login() async {
    setState(() => _loading = true);
    final res = await _api.login(_userCtrl.text, _passCtrl.text);
    setState(() => _loading = false);
    if (res != null && mounted) Navigator.pop(context);
    else if (mounted) ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đăng nhập thất bại'), backgroundColor: Colors.red));
  }

  Future<void> _register() async {
    setState(() => _loading = true);
    final ok = await _api.register(_regUserCtrl.text, _regEmailCtrl.text, _regPassCtrl.text);
    setState(() => _loading = false);
    if (ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đăng ký thành công!'), backgroundColor: Colors.green));
      setState(() => _loginMode = true);
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đăng ký thất bại'), backgroundColor: Colors.red));
    }
  }
}
