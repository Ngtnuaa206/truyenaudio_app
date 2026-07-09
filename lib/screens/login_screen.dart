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
    return Scaffold(
      backgroundColor: const Color(0xFF0F0F1A),
      appBar: AppBar(
        backgroundColor: const Color(0xFF1A1A2E),
        leading: IconButton(icon: const Icon(Icons.arrow_back, color: Colors.white), onPressed: () => Navigator.pop(context)),
        title: Text(_loginMode ? 'Đăng nhập' : 'Đăng ký', style: const TextStyle(color: Colors.white)),
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Container(
            padding: const EdgeInsets.all(25),
            decoration: BoxDecoration(
              color: const Color(0xFF1A1A2E),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFF2A2A4E)),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(_loginMode ? 'Đăng nhập' : 'Đăng ký', style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.bold)),
                const SizedBox(height: 20),
                if (_loginMode) _buildLogin() else _buildRegister(),
                const SizedBox(height: 15),
                TextButton(
                  onPressed: () => setState(() => _loginMode = !_loginMode),
                  child: Text(
                    _loginMode ? 'Chưa có tài khoản? Đăng ký' : 'Đã có tài khoản? Đăng nhập',
                    style: const TextStyle(color: Color(0xFFF0C040)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildLogin() {
    return Column(
      children: [
        _input('Tên đăng nhập', _userCtrl),
        const SizedBox(height: 12),
        _input('Mật khẩu', _passCtrl, obscure: true),
        const SizedBox(height: 20),
        _submitBtn('Đăng nhập', () => _login()),
      ],
    );
  }

  Widget _buildRegister() {
    return Column(
      children: [
        _input('Tên đăng nhập', _regUserCtrl),
        const SizedBox(height: 12),
        _input('Email', _regEmailCtrl),
        const SizedBox(height: 12),
        _input('Mật khẩu', _regPassCtrl, obscure: true),
        const SizedBox(height: 20),
        _submitBtn('Đăng ký', () => _register()),
      ],
    );
  }

  Widget _input(String label, TextEditingController ctrl, {bool obscure = false}) {
    return TextField(
      controller: ctrl,
      obscureText: obscure,
      style: const TextStyle(color: Colors.white),
      decoration: InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(color: Color(0xFF888888)),
        filled: true,
        fillColor: const Color(0xFF0F0F1A),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: Color(0xFF2A2A4E)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: Color(0xFF2A2A4E)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: Color(0xFFF0C040)),
        ),
      ),
    );
  }

  Widget _submitBtn(String text, VoidCallback onTap) {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton(
        onPressed: _loading ? null : onTap,
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFFF0C040),
          foregroundColor: const Color(0xFF1A1A2E),
          padding: const EdgeInsets.symmetric(vertical: 14),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        ),
        child: _loading
            ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF1A1A2E)))
            : Text(text, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
      ),
    );
  }

  Future<void> _login() async {
    setState(() => _loading = true);
    final res = await _api.login(_userCtrl.text, _passCtrl.text);
    setState(() => _loading = false);
    if (res != null && mounted) {
      Navigator.pop(context);
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đăng nhập thất bại'), backgroundColor: Colors.red));
    }
  }

  Future<void> _register() async {
    setState(() => _loading = true);
    final ok = await _api.register(_regUserCtrl.text, _regEmailCtrl.text, _regPassCtrl.text);
    setState(() => _loading = false);
    if (ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đăng ký thành công! Đăng nhập ngay.'), backgroundColor: Colors.green));
      setState(() => _loginMode = true);
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Đăng ký thất bại'), backgroundColor: Colors.red));
    }
  }
}
