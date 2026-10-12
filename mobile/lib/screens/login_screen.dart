import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/api_client.dart';
import '../core/theme.dart';
import '../providers/auth_provider.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _userCtl = TextEditingController();
  final _sandiCtl = TextEditingController();

  bool _sembunyi = true;
  bool _menyimpan = false;

  @override
  void dispose() {
    _userCtl.dispose();
    _sandiCtl.dispose();
    super.dispose();
  }

  Future<void> _masuk() async {
    if (!_formKey.currentState!.validate()) return;

    FocusScope.of(context).unfocus();
    setState(() => _menyimpan = true);

    await context.read<AuthProvider>().login(
      _userCtl.text.trim(),
      _sandiCtl.text,
    );

    if (mounted) setState(() => _menyimpan = false);
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final lebar = MediaQuery.sizeOf(context).width;

    return Scaffold(
      backgroundColor: AppTheme.primaryDeep,
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [AppTheme.primaryDeep, Color(0xFF065F46), AppTheme.primary],
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 420),
                child: Column(
                  children: [
                    // Logo + nama (dari pengaturan toko bila sudah Termuat)
                    _Header(auth: auth),

                    const SizedBox(height: 28),

                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(22),
                        child: Form(
                          key: _formKey,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              TextFormField(
                                controller: _userCtl,
                                autocorrect: false,
                                textInputAction: TextInputAction.next,
                                decoration: const InputDecoration(
                                  labelText: 'Username',
                                  prefixIcon: Icon(Icons.person_outline),
                                ),
                                validator: (v) =>
                                    (v == null || v.trim().isEmpty)
                                    ? 'Username wajib diisi'
                                    : null,
                              ),
                              const SizedBox(height: 14),
                              TextFormField(
                                controller: _sandiCtl,
                                obscureText: _sembunyi,
                                textInputAction: TextInputAction.done,
                                onFieldSubmitted: (_) => _masuk(),
                                decoration: InputDecoration(
                                  labelText: 'Password',
                                  prefixIcon: const Icon(Icons.lock_outline),
                                  suffixIcon: IconButton(
                                    icon: Icon(
                                      _sembunyi
                                          ? Icons.visibility_off_outlined
                                          : Icons.visibility_outlined,
                                    ),
                                    onPressed: () =>
                                        setState(() => _sembunyi = !_sembunyi),
                                  ),
                                ),
                                validator: (v) => (v == null || v.isEmpty)
                                    ? 'Password wajib diisi'
                                    : null,
                              ),

                              if (auth.pesanError != null) ...[
                                const SizedBox(height: 14),
                                _Galat(pesan: auth.pesanError!),
                              ],

                              const SizedBox(height: 20),
                              ElevatedButton(
                                onPressed:
                                    _menyimpan ||
                                        auth.status == AuthStatus.memuat
                                    ? null
                                    : _masuk,
                                child:
                                    _menyimpan ||
                                        auth.status == AuthStatus.memuat
                                    ? const SizedBox(
                                        height: 20,
                                        width: 20,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2.4,
                                          color: Colors.white,
                                        ),
                                      )
                                    : const Text('Masuk'),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),

                    if (lebar > 600) ...[
                      const SizedBox(height: 18),
                      const Text(
                        'Kasir Apotikdear POS',
                        style: TextStyle(color: Colors.white70, fontSize: 13),
                      ),
                    ],

                    const SizedBox(height: 14),
                    _AlamatServer(api: context.read<ApiClient>()),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.auth});

  final AuthProvider auth;

  @override
  Widget build(BuildContext context) {
    final nama = auth.site?.namaUntukTampilan ?? 'Apotikdear POS';

    return Column(
      children: [
        Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.18),
                blurRadius: 22,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: const Icon(
            Icons.local_pharmacy_rounded,
            size: 46,
            color: AppTheme.primary,
          ),
        ),
        const SizedBox(height: 16),
        Text(
          nama,
          textAlign: TextAlign.center,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 24,
            fontWeight: FontWeight.w900,
            letterSpacing: 0.3,
          ),
        ),
        if (auth.site?.namaToko.isNotEmpty == true) ...[
          const SizedBox(height: 4),
          Text(
            auth.site!.namaToko,
            textAlign: TextAlign.center,
            style: const TextStyle(color: Colors.white70, fontSize: 13.5),
          ),
        ],
      ],
    );
  }
}

class _AlamatServer extends StatelessWidget {
  const _AlamatServer({required this.api});

  final ApiClient api;

  Future<void> _ubah(BuildContext context) async {
    final ctl = TextEditingController(text: api.baseUrl);

    final hasil = await showDialog<String>(
      context: context,
      builder: (c) => AlertDialog(
        title: const Text('Alamat Server'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Isi alamat API server kasir. Pilih sesuai perangkat yang dipakai:',
              style: TextStyle(fontSize: 13),
            ),
            const SizedBox(height: 12),
            _preset(context, 'Emulator Android', 'http://10.0.2.2:8000/api'),
            _preset(
              context,
              'iOS Simulator / Web',
              'http://127.0.0.1:8000/api',
            ),
            _preset(
              context,
              'HP di Wi-Fi lokal',
              'http://192.168.1.8:8000/api',
            ),
            const SizedBox(height: 12),
            TextField(
              controller: ctl,
              autocorrect: false,
              keyboardType: TextInputType.url,
              decoration: const InputDecoration(
                labelText: 'Alamat API',
                hintText: 'http://192.168.1.8:8000/api',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(c),
            child: const Text('Batal'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(c, ctl.text.trim()),
            child: const Text('Simpan'),
          ),
        ],
      ),
    );

    if (hasil != null && hasil.isNotEmpty && context.mounted) {
      await api.simpanBaseUrl(hasil);
    }
  }

  Widget _preset(BuildContext dialogContext, String label, String url) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 4),
      child: InkWell(
        borderRadius: BorderRadius.circular(8),
        onTap: () => Navigator.pop(dialogContext, url),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 6, horizontal: 8),
          child: Row(
            children: [
              const Icon(Icons.touch_app_outlined, size: 15),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  '$label — $url',
                  style: const TextStyle(fontSize: 12),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return TextButton.icon(
      onPressed: () => _ubah(context),
      style: TextButton.styleFrom(
        foregroundColor: Colors.white70,
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        minimumSize: Size.zero,
        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
      ),
      icon: const Icon(Icons.dns_outlined, size: 15),
      label: Text(
        api.baseUrl,
        style: const TextStyle(fontSize: 11.5, color: Colors.white70),
      ),
    );
  }
}

class _Galat extends StatelessWidget {
  const _Galat({required this.pesan});

  final String pesan;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppTheme.danger.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppTheme.danger.withValues(alpha: 0.3)),
      ),
      child: Row(
        children: [
          const Icon(Icons.error_outline, color: AppTheme.danger, size: 20),
          const SizedBox(width: 10),
          Expanded(
            // Pesan error boleh panjang (berisi alamat server & petunjuk),
            // jadi dibungkus scroll supaya tidak meluber.
            child: SingleChildScrollView(
              child: Text(
                pesan,
                style: const TextStyle(color: AppTheme.danger, fontSize: 13),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
