import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/config.dart';
import '../core/theme.dart';
import '../providers/auth_provider.dart';
import '../providers/cart_provider.dart';
import '../services/printer_service.dart';
import '../widgets/app_header.dart';
import '../widgets/sync_status.dart';
import 'dashboard/dashboard_screen.dart';
import 'kasir/kasir_screen.dart';
import 'master/kategori_rak_screen.dart';
import 'master/obat_list_screen.dart';
import 'printer/printer_setting_screen.dart';
import 'transaksi/riwayat_screen.dart';

/// Menu dinamis sesuai hak akses, supaya kasir tidak pernah membuka
/// halaman yang nanti dibalas 403.
class MenuItem {
  const MenuItem({
    required this.label,
    required this.icon,
    required this.aktifIcon,
    required this.bisa,
    required this.bikin,
  });

  final String label;
  final IconData icon;
  final IconData aktifIcon;
  final bool bisa;
  final Widget Function() bikin;
}

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _index = 0;

  /// Status printer — ditampilkan sebagai titik hijau/abu di header.
  ///
  /// Dicek ulang tiap kali kembali ke layar ini karena koneksi Bluetooth
  /// bisa terputus sendiri.
  bool _printerTersambung = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _cekPrinter());
  }

  Future<void> _cekPrinter() async {
    if (!mounted) return;

    final tersambung = context.read<PrinterService>().tersambung;
    if (tersambung != _printerTersambung) {
      setState(() => _printerTersambung = tersambung);
    }
  }

  List<MenuItem> _menu(AuthProvider auth) {
    final master = auth.user?.bisaMaster ?? false;
    final transaksi = auth.user?.bisaTransaksi ?? false;

    return [
      MenuItem(
        label: 'Dashboard',
        icon: Icons.dashboard_outlined,
        aktifIcon: Icons.dashboard_rounded,
        bisa: true,
        bikin: () => const DashboardScreen(),
      ),
      MenuItem(
        label: 'Kasir',
        icon: Icons.point_of_sale_outlined,
        aktifIcon: Icons.point_of_sale_rounded,
        bisa: transaksi,
        bikin: () => const KasirScreen(),
      ),
      MenuItem(
        label: 'Obat',
        icon: Icons.medication_outlined,
        aktifIcon: Icons.medication_rounded,
        bisa: true,
        bikin: () => const ObatListScreen(),
      ),
      MenuItem(
        label: 'Kategori & Rak',
        icon: Icons.category_outlined,
        aktifIcon: Icons.category_rounded,
        bisa: master,
        bikin: () => const KategoriRakScreen(),
      ),
      MenuItem(
        label: 'Riwayat',
        icon: Icons.receipt_long_outlined,
        aktifIcon: Icons.receipt_long_rounded,
        bisa: transaksi,
        bikin: () => const RiwayatScreen(),
      ),
    ];
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final cart = context.watch<CartProvider>();

    final semua = _menu(auth);
    final menu = semua.where((m) => m.bisa).toList();

    // Kalau role berubah (mis. logout lalu login sebagai role lain),
    // pastikan index tidak keluar jangkauan.
    if (_index >= menu.length) _index = 0;

    final lebar = MediaQuery.sizeOf(context).width;
    final pakaiRail = lebar >= AppConfig.breakpointTable;

    final konten = menu[_index].bikin();

    return Scaffold(
      appBar: AppHeader(
        judul: menu[_index].label,
        subtitle: auth.site?.namaUntukTampilan,
        ikon: menu[_index].aktifIcon,
        aksi: [
          BadgeAngka(
            jumlah: cart.jumlahItem,
            ikon: Icons.shopping_cart_rounded,
            aktif: cart.jumlahItem > 0,
            onTap: () => setState(() => _index = 0),
          ),
          const SyncStatusChip(),
          TitikStatus(
            aktif: _printerTersambung,
            aktifLabel: 'Printer tersambung',
            nonaktifLabel: 'Printer belum tersambung',
            onTap: _bukaPrinter,
          ),
          AvatarUser(
            inisial: auth.user?.inisial ?? '?',
            nama: auth.user?.nama ?? '-',
            onTap: () => _menuAkun(),
          ),
        ],
      ),

      // Navigasi berubah bentuk mengikuti lebar layar.
      body: pakaiRail
          ? Row(
              children: [
                NavigationRail(
                  extended: lebar >= AppConfig.breakpointFullTable,
                  selectedIndex: _index,
                  onDestinationSelected: (i) => setState(() => _index = i),
                  labelType: lebar >= AppConfig.breakpointFullTable
                      ? NavigationRailLabelType.none
                      : NavigationRailLabelType.all,
                  destinations: [
                    for (final m in menu)
                      NavigationRailDestination(
                        icon: Icon(m.icon),
                        selectedIcon: Icon(m.aktifIcon),
                        label: Text(m.label),
                      ),
                  ],
                ),
                const VerticalDivider(width: 1),
                Expanded(child: konten),
              ],
            )
          : konten,

      bottomNavigationBar: pakaiRail
          ? null
          : NavigationBar(
              selectedIndex: _index,
              onDestinationSelected: (i) => setState(() => _index = i),
              destinations: [
                for (final m in menu)
                  NavigationDestination(
                    icon: Icon(m.icon),
                    selectedIcon: Icon(m.aktifIcon),
                    label: m.label,
                  ),
              ],
            ),
    );
  }

  /// Buka pengaturan printer.
  Future<void> _bukaPrinter() async {
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) =>
            PrinterSettingScreen(printer: context.read<PrinterService>()),
      ),
    );
    if (mounted) setState(() {});
  }

  /// Menu akun: pengaturan printer & keluar.
  Future<void> _menuAkun() async {
    final auth = context.read<AuthProvider>();

    final pilihan = await showModalBottomSheet<String>(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (c) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            //.handle geser
            Container(
              width: 42,
              height: 4,
              margin: const EdgeInsets.only(top: 12, bottom: 14),
              decoration: BoxDecoration(
                color: AppTheme.line,
                borderRadius: BorderRadius.circular(4),
              ),
            ),
            ListTile(
              leading: CircleAvatar(
                backgroundColor: AppTheme.primary,
                child: Text(
                  auth.user?.inisial ?? '?',
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
              title: Text(
                auth.user?.nama ?? '-',
                style: const TextStyle(fontWeight: FontWeight.w800),
              ),
              subtitle: Text(
                _labelRole(auth.user?.role),
                style: const TextStyle(fontSize: 12, color: AppTheme.muted),
              ),
            ),
            const Divider(height: 1),
            ListTile(
              leading: const Icon(Icons.print_outlined),
              title: const Text('Pengaturan Printer'),
              trailing: Text(
                _printerTersambung ? 'Tersambung' : 'Belum',
                style: TextStyle(
                  fontSize: 12,
                  color: _printerTersambung ? AppTheme.success : AppTheme.muted,
                  fontWeight: FontWeight.w700,
                ),
              ),
              onTap: () => Navigator.pop(c, 'printer'),
            ),
            ListTile(
              leading: const Icon(Icons.logout_rounded, color: AppTheme.danger),
              title: const Text(
                'Keluar',
                style: TextStyle(
                  color: AppTheme.danger,
                  fontWeight: FontWeight.w700,
                ),
              ),
              onTap: () => Navigator.pop(c, 'keluar'),
            ),
            const SizedBox(height: 8),
          ],
        ),
      ),
    );

    if (!mounted || pilihan == null) return;

    if (pilihan == 'printer') {
      await _bukaPrinter();
      return;
    }

    if (pilihan == 'keluar') {
      final keluar = await showDialog<bool>(
        context: context,
        builder: (c) => AlertDialog(
          title: const Text('Keluar dari aplikasi?'),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(c, false),
              child: const Text('Batal'),
            ),
            FilledButton(
              style: FilledButton.styleFrom(backgroundColor: AppTheme.danger),
              onPressed: () => Navigator.pop(c, true),
              child: const Text('Keluar'),
            ),
          ],
        ),
      );

      if (keluar == true && mounted) {
        await context.read<AuthProvider>().logout();
      }
    }
  }

  static String _labelRole(String? role) => switch (role) {
    'admin' => 'Administrator',
    'gudang' => 'Gudang',
    _ => 'Kasir',
  };
}
