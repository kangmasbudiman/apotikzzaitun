import 'package:flutter/material.dart';

import '../../core/theme.dart';
import '../../services/printer_service.dart';

/// Layar pengaturan printer Bluetooth.
///
/// Printer kasir tidak berubah sewaktu-waktu, jadi cukup satu kali pengaturan:
/// pilih printer, tentukan ukuran kertas, lalu printer itu dipakai otomatis
/// setiap kali transaksi disimpan.
class PrinterSettingScreen extends StatefulWidget {
  const PrinterSettingScreen({super.key, required this.printer});

  final PrinterService printer;

  @override
  State<PrinterSettingScreen> createState() => _PrinterSettingScreenState();
}

class _PrinterSettingScreenState extends State<PrinterSettingScreen> {
  late PrinterSetting _setelan = PrinterSetting.kosong;

  List<PrinterDevice> _perangkat = const [];
  bool _memuat = true;
  bool _menyambung = false;

  @override
  void initState() {
    super.initState();
    _muat();
  }

  Future<void> _muat() async {
    setState(() => _memuat = true);

    final setelan = await widget.printer.pengaturan();

    // Coba sambung otomatis supaya status di atas akurat.
    await widget.printer.siap();
    await widget.printer.sambungTersimpan();
    final perangkat = await widget.printer.perangkatTerpasang();

    if (!mounted) return;
    setState(() {
      _setelan = setelan;
      _perangkat = perangkat;
      _menyambung = widget.printer.tersambung;
      _memuat = false;
    });
  }

  Future<void> _sambung(PrinterDevice d) async {
    final messenger = ScaffoldMessenger.of(context);
    setState(() => _menyambung = false);

    final ok = await widget.printer.sambung(d.mac);

    if (!mounted) return;

    await widget.printer.simpanPengaturan(
      _setelan.copyWith(mac: d.mac, nama: d.nama),
    );

    if (!mounted) return;
    setState(() {
      _menyambung = ok;
      _setelan = _setelan.copyWith(mac: d.mac, nama: d.nama);
    });

    messenger
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          content: Text(
            ok ? 'Terhubung ke ${d.nama}' : 'Gagal terhubung ke ${d.nama}',
          ),
          backgroundColor: ok ? AppTheme.success : AppTheme.danger,
        ),
      );
  }

  Future<void> _cetakUji() async {
    final messenger = ScaffoldMessenger.of(context);

    try {
      await widget.printer.cetakUji();
      messenger
        ..hideCurrentSnackBar()
        ..showSnackBar(const SnackBar(content: Text('Struk uji dicetak.')));
    } on PrintException catch (e) {
      messenger
        ..hideCurrentSnackBar()
        ..showSnackBar(
          SnackBar(content: Text(e.pesan), backgroundColor: AppTheme.danger),
        );
    }
  }

  Future<void> _simpan() async {
    final s = _setelan;

    await widget.printer.simpanPengaturan(
      s.copyWith(
        karakterPerBaris: int.tryParse(s.karakterPerBaris.toString()) ?? 32,
      ),
    );

    if (!mounted) return;
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(
        const SnackBar(content: Text('Pengaturan printer disimpan.')),
      );

    Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Pengaturan Printer')),
      body: _memuat
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                _Kartu(
                  judul: 'Status',
                  isi: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(
                            _menyambung
                                ? Icons.bluetooth_connected_rounded
                                : Icons.bluetooth_disabled_rounded,
                            color: _menyambung
                                ? AppTheme.success
                                : AppTheme.muted,
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              _menyambung
                                  ? 'Terhubung ke ${_setelan.nama}'
                                  : 'Belum tersambung',
                              style: const TextStyle(
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                        ],
                      ),
                      if (_setelan.sudahDipilih && !_menyambung) ...[
                        const SizedBox(height: 8),
                        Text(
                          'MAC: ${_setelan.mac}',
                          style: const TextStyle(
                            fontSize: 11.5,
                            color: AppTheme.muted,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(height: 14),

                _Kartu(
                  judul: 'Printer',
                  isi: _perangkat.isEmpty
                      ? Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Belum ada perangkat Bluetooth yang dipasangkan.\n\n'
                              'Pasangkan printer dulu lewat pengaturan Bluetooth HP:\n'
                              'Pengaturan → Bluetooth → Pasangkan perangkat baru.\n\n'
                              'Tips: printer thermal kasir biasanya bernama\n'
                              '"Bluetooth Printer", "MTP-II", atau "BT-310".',
                              style: TextStyle(
                                fontSize: 12.5,
                                color: AppTheme.muted,
                                height: 1.5,
                              ),
                            ),
                            const SizedBox(height: 12),
                            OutlinedButton.icon(
                              onPressed: _muat,
                              icon: const Icon(Icons.refresh_rounded),
                              label: const Text('Muat Ulang'),
                            ),
                          ],
                        )
                      : RadioGroup<String>(
                          groupValue: _setelan.mac,
                          onChanged: (v) {
                            if (v == null) return;
                            _sambung(_perangkat.firstWhere((e) => e.mac == v));
                          },
                          child: Column(
                            children: [
                              for (final d in _perangkat)
                                RadioListTile<String>(
                                  value: d.mac,
                                  contentPadding: EdgeInsets.zero,
                                  title: Text(
                                    d.nama,
                                    style: const TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                  subtitle: Text(
                                    d.mac,
                                    style: const TextStyle(
                                      fontSize: 11,
                                      color: AppTheme.muted,
                                      fontFamily: 'monospace',
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        ),
                ),
                const SizedBox(height: 14),

                _Kartu(
                  judul: 'Kertas',
                  isi: Column(
                    children: [
                      RadioGroup<int>(
                        groupValue: _setelan.karakterPerBaris,
                        onChanged: (v) => setState(
                          () => _setelan = _setelan.copyWith(
                            karakterPerBaris: v ?? 32,
                          ),
                        ),
                        child: Column(
                          children: [
                            RadioListTile<int>(
                              value: 32,
                              contentPadding: EdgeInsets.zero,
                              title: const Text(
                                '58 mm — 32 karakter per baris',
                              ),
                              subtitle: const Text(
                                'Printer kasir yang paling umum dipakai',
                                style: TextStyle(
                                  fontSize: 11.5,
                                  color: AppTheme.muted,
                                ),
                              ),
                            ),
                            RadioListTile<int>(
                              value: 48,
                              contentPadding: EdgeInsets.zero,
                              title: const Text(
                                '80 mm — 48 karakter per baris',
                              ),
                              subtitle: const Text(
                                'Untuk kertas yang lebih lebar',
                                style: TextStyle(
                                  fontSize: 11.5,
                                  color: AppTheme.muted,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                      SwitchListTile(
                        value: _setelan.potongOtomatis,
                        onChanged: (v) => setState(
                          () => _setelan = _setelan.copyWith(potongOtomatis: v),
                        ),
                        contentPadding: EdgeInsets.zero,
                        title: const Text('Potong kertas otomatis'),
                        subtitle: const Text(
                          'Matikan kalau printer tidak punya pemotong kertas',
                          style: TextStyle(
                            fontSize: 11.5,
                            color: AppTheme.muted,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 14),

                _Kartu(
                  judul: 'Teks penutup',
                  isi: TextField(
                    controller: TextEditingController(text: _setelan.footer),
                    maxLines: 2,
                    decoration: const InputDecoration(
                      hintText: 'Terima kasih telah berbelanja',
                    ),
                    onChanged: (v) => _setelan = _setelan.copyWith(footer: v),
                  ),
                ),
                const SizedBox(height: 18),

                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: _menyambung ? _cetakUji : null,
                        icon: const Icon(Icons.print_outlined),
                        label: const Text('Cetak Uji'),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: ElevatedButton.icon(
                        onPressed: _simpan,
                        icon: const Icon(Icons.save_rounded),
                        label: const Text('Simpan'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
    );
  }
}

class _Kartu extends StatelessWidget {
  const _Kartu({required this.judul, required this.isi});

  final String judul;
  final Widget isi;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              judul.toUpperCase(),
              style: const TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w800,
                color: AppTheme.primaryDark,
                letterSpacing: 0.4,
              ),
            ),
            const SizedBox(height: 12),
            isi,
          ],
        ),
      ),
    );
  }
}
