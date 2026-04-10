import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'dart:ui';
import '../providers/app_providers.dart';
import '../models/models.dart';
import 'asset_detail_screen.dart';
import 'asset_form_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final _searchCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<InventoryProvider>().loadAll();
    });
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F111A),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [Color(0xFF0F111A), Color(0xFF1a1f3c), Color(0xFF0f1a2e)],
          ),
        ),
        child: SafeArea(
          child: Column(
            children: [
              _buildHeader(),
              _buildSearchBar(),
              _buildStatsRow(),
              Expanded(child: _buildAssetList()),
            ],
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: const Color(0xFF6366f1),
        icon: const Icon(Icons.add_rounded, color: Colors.white),
        label: Text('Nuevo Activo',
            style: GoogleFonts.inter(
                color: Colors.white, fontWeight: FontWeight.w600)),
        onPressed: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const AssetFormScreen()),
        ).then((_) => context.read<InventoryProvider>().loadAll()),
      ),
    );
  }

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 10),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: const LinearGradient(
                  colors: [Color(0xFF6366f1), Color(0xFF8b5cf6)]),
            ),
            child: const Icon(Icons.inventory_2_rounded,
                color: Colors.white, size: 22),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Inventario Municipal',
                    style: GoogleFonts.inter(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: Colors.white)),
                Text('Centro de Gestión de Activos',
                    style: GoogleFonts.inter(
                        fontSize: 12, color: const Color(0xFF94a3b8))),
              ],
            ),
          ),
          IconButton(
            icon: const Icon(Icons.logout_rounded,
                color: Color(0xFF94a3b8), size: 22),
            onPressed: () async {
              await context.read<AuthProvider>().logout();
              if (mounted) Navigator.pushReplacementNamed(context, '/login');
            },
          ),
        ],
      ),
    );
  }

  Widget _buildSearchBar() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
      child: Container(
        decoration: BoxDecoration(
          color: const Color(0xFF1e293b).withOpacity(0.7),
          borderRadius: BorderRadius.circular(14),
          border:
              Border.all(color: Colors.white.withOpacity(0.08)),
        ),
        child: TextField(
          controller: _searchCtrl,
          style: GoogleFonts.inter(color: Colors.white),
          onChanged: context.read<InventoryProvider>().search,
          decoration: InputDecoration(
            hintText: 'Buscar por código, artículo o serie...',
            hintStyle:
                GoogleFonts.inter(color: const Color(0xFF64748b), fontSize: 14),
            prefixIcon: const Icon(Icons.search_rounded,
                color: Color(0xFF6366f1), size: 20),
            border: InputBorder.none,
            contentPadding: const EdgeInsets.symmetric(vertical: 14),
          ),
        ),
      ),
    );
  }

  Widget _buildStatsRow() {
    return Consumer<InventoryProvider>(builder: (ctx, inv, _) {
      final total = inv.assets.length;
      final nuevos =
          inv.assets.where((a) => a.status == 'nuevo').length;
      final baja =
          inv.assets.where((a) => a.status == 'baja').length;

      return Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
        child: Row(
          children: [
            _StatChip(label: 'Total', value: '$total', color: const Color(0xFF6366f1)),
            const SizedBox(width: 10),
            _StatChip(
                label: 'Nuevos', value: '$nuevos', color: const Color(0xFF10b981)),
            const SizedBox(width: 10),
            _StatChip(
                label: 'De Baja', value: '$baja', color: const Color(0xFFef4444)),
          ],
        ),
      );
    });
  }

  Widget _buildAssetList() {
    return Consumer<InventoryProvider>(
      builder: (ctx, inv, _) {
        if (inv.isLoading) {
          return const Center(
              child: CircularProgressIndicator(color: Color(0xFF6366f1)));
        }
        if (inv.error != null) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(Icons.wifi_off_rounded,
                    color: Color(0xFF94a3b8), size: 60),
                const SizedBox(height: 16),
                Text('Sin conexión con el servidor',
                    style: GoogleFonts.inter(color: const Color(0xFF94a3b8))),
                const SizedBox(height: 16),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF6366f1)),
                  onPressed: inv.loadAll,
                  child: const Text('Reintentar'),
                ),
              ],
            ),
          );
        }
        if (inv.assets.isEmpty) {
          return Center(
            child: Text('No hay activos registrados',
                style:
                    GoogleFonts.inter(color: const Color(0xFF94a3b8))),
          );
        }
        return RefreshIndicator(
          color: const Color(0xFF6366f1),
          onRefresh: inv.loadAll,
          child: ListView.builder(
            padding: const EdgeInsets.fromLTRB(20, 4, 20, 100),
            itemCount: inv.assets.length,
            itemBuilder: (ctx, i) => _AssetCard(asset: inv.assets[i]),
          ),
        );
      },
    );
  }
}

// ─── Widgets auxiliares ─────────────────────────────────────────────────────

class _StatChip extends StatelessWidget {
  final String label, value;
  final Color color;
  const _StatChip(
      {required this.label, required this.value, required this.color});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color.withOpacity(0.2)),
        ),
        child: Column(
          children: [
            Text(value,
                style: GoogleFonts.inter(
                    fontSize: 22,
                    fontWeight: FontWeight.w700,
                    color: color)),
            Text(label,
                style: GoogleFonts.inter(
                    fontSize: 11, color: const Color(0xFF94a3b8))),
          ],
        ),
      ),
    );
  }
}

class _AssetCard extends StatelessWidget {
  final FixedAsset asset;
  const _AssetCard({required this.asset});

  Color _statusColor(String s) {
    return const {
      'nuevo': Color(0xFF10b981),
      'bueno': Color(0xFF6366f1),
      'regular': Color(0xFFf59e0b),
      'malo': Color(0xFFef4444),
      'baja': Color(0xFF64748b),
    }[s] ??
        const Color(0xFF6366f1);
  }

  @override
  Widget build(BuildContext context) {
    final statusColor = _statusColor(asset.status);

    return GestureDetector(
      onTap: () => Navigator.push(
        context,
        MaterialPageRoute(
            builder: (_) => AssetDetailScreen(asset: asset)),
      ),
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        decoration: BoxDecoration(
          color: const Color(0xFF1e293b).withOpacity(0.6),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.white.withOpacity(0.07)),
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(16),
          child: BackdropFilter(
            filter: ImageFilter.blur(sigmaX: 6, sigmaY: 6),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  // Ícono de estado
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: statusColor.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(Icons.devices_rounded,
                        color: statusColor, size: 24),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(asset.inventoryCode,
                            style: GoogleFonts.inter(
                                color: const Color(0xFF818cf8),
                                fontWeight: FontWeight.w700,
                                fontSize: 15)),
                        const SizedBox(height: 2),
                        Text(asset.item?.name ?? 'Sin artículo',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: GoogleFonts.inter(
                                color: Colors.white,
                                fontWeight: FontWeight.w500)),
                        const SizedBox(height: 3),
                        Text(
                          asset.provider?.companyName ?? 'Sin proveedor',
                          style: GoogleFonts.inter(
                              color: const Color(0xFF94a3b8), fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: statusColor.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(asset.statusLabel,
                            style: GoogleFonts.inter(
                                color: statusColor,
                                fontSize: 11,
                                fontWeight: FontWeight.w600)),
                      ),
                      const SizedBox(height: 8),
                      const Icon(Icons.chevron_right_rounded,
                          color: Color(0xFF475569), size: 20),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
