import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'dart:ui';
import '../models/models.dart';
import '../services/api_service.dart';
import 'asset_form_screen.dart';

class AssetDetailScreen extends StatefulWidget {
  final FixedAsset asset;
  const AssetDetailScreen({super.key, required this.asset});

  @override
  State<AssetDetailScreen> createState() => _AssetDetailScreenState();
}

class _AssetDetailScreenState extends State<AssetDetailScreen> {
  List<Assignment> _assignments = [];
  bool _loadingHistory = true;

  @override
  void initState() {
    super.initState();
    _loadHistory();
  }

  Future<void> _loadHistory() async {
    try {
      final list = await ApiService.getAssetAssignments(widget.asset.id);
      setState(() {
        _assignments = list;
        _loadingHistory = false;
      });
    } catch (_) {
      setState(() => _loadingHistory = false);
    }
  }

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
    final asset = widget.asset;
    final sc = _statusColor(asset.status);

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
        child: CustomScrollView(
          slivers: [
            SliverAppBar(
              backgroundColor: Colors.transparent,
              pinned: true,
              expandedHeight: 220,
              leading: IconButton(
                icon: const Icon(Icons.arrow_back_ios_new_rounded,
                    color: Colors.white),
                onPressed: () => Navigator.pop(context),
              ),
              actions: [
                IconButton(
                  icon: const Icon(Icons.edit_rounded, color: Color(0xFF6366f1)),
                  onPressed: () => Navigator.push(
                    context,
                    MaterialPageRoute(
                        builder: (_) => AssetFormScreen(asset: asset)),
                  ),
                ),
              ],
              flexibleSpace: FlexibleSpaceBar(
                background: Container(
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [
                        sc.withOpacity(0.2),
                        const Color(0xFF0F111A).withOpacity(0),
                      ],
                    ),
                  ),
                  child: Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const SizedBox(height: 50),
                        Container(
                          width: 80,
                          height: 80,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: sc.withOpacity(0.15),
                            border: Border.all(color: sc.withOpacity(0.3)),
                          ),
                          child: Icon(Icons.devices_rounded,
                              color: sc, size: 40),
                        ),
                        const SizedBox(height: 12),
                        Text(asset.inventoryCode,
                            style: GoogleFonts.inter(
                                color: Colors.white,
                                fontSize: 22,
                                fontWeight: FontWeight.w700)),
                        Text(asset.item?.name ?? '',
                            style: GoogleFonts.inter(
                                color: const Color(0xFF94a3b8), fontSize: 14)),
                      ],
                    ),
                  ),
                ),
              ),
            ),
            SliverPadding(
              padding: const EdgeInsets.all(20),
              sliver: SliverList(
                delegate: SliverChildListDelegate([
                  _GlassSection(
                    title: 'Detalles del Activo',
                    icon: Icons.info_outline_rounded,
                    children: [
                      _DetailRow('Código', asset.inventoryCode),
                      _DetailRow('N° de Serie', asset.serialNumber ?? 'N/A'),
                      _DetailRow('Estado', asset.statusLabel,
                          valueColor: sc),
                      _DetailRow('Año / Precio',
                          asset.purchasePrice != null
                              ? '\$${asset.purchasePrice!.toStringAsFixed(0)}'
                              : 'N/A'),
                      _DetailRow('Categoría',
                          asset.item?.category?.name ?? 'Sin categoría'),
                    ],
                  ),
                  const SizedBox(height: 16),
                  _GlassSection(
                    title: 'Proveedor',
                    icon: Icons.business_rounded,
                    children: [
                      _DetailRow('Empresa',
                          asset.provider?.companyName ?? 'Desconocido'),
                      _DetailRow('NIT', asset.provider?.nit ?? 'N/A'),
                      _DetailRow('Contacto',
                          asset.provider?.contact ?? 'N/A'),
                    ],
                  ),
                  const SizedBox(height: 16),
                  _GlassSection(
                    title: 'Responsable Actual',
                    icon: Icons.person_rounded,
                    children: asset.activeAssignment != null
                        ? [
                            _DetailRow('Custodio',
                                asset.activeAssignment!.custodianName),
                            _DetailRow('Secretaría',
                                asset.activeAssignment!.office?.name ?? 'N/A'),
                            _DetailRow('Fecha',
                                asset.activeAssignment!.assignmentDate),
                          ]
                        : [
                            Center(
                              child: Padding(
                                padding:
                                    const EdgeInsets.symmetric(vertical: 8),
                                child: Text('Sin asignación activa',
                                    style: GoogleFonts.inter(
                                        color: const Color(0xFF94a3b8))),
                              ),
                            )
                          ],
                  ),
                  const SizedBox(height: 16),
                  _GlassSection(
                    title: 'Historial de Asignaciones',
                    icon: Icons.history_rounded,
                    children: [
                      if (_loadingHistory)
                        const Padding(
                          padding: EdgeInsets.all(16),
                          child: Center(
                              child: CircularProgressIndicator(
                                  color: Color(0xFF6366f1))),
                        )
                      else if (_assignments.isEmpty)
                        Center(
                          child: Padding(
                            padding: const EdgeInsets.symmetric(vertical: 8),
                            child: Text('Sin historial',
                                style: GoogleFonts.inter(
                                    color: const Color(0xFF94a3b8))),
                          ),
                        )
                      else
                        ..._assignments.map((a) => _HistoryTile(assignment: a)),
                    ],
                  ),
                  const SizedBox(height: 40),
                ]),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ─── Widgets ----------------------------------------------------------------

class _GlassSection extends StatelessWidget {
  final String title;
  final IconData icon;
  final List<Widget> children;

  const _GlassSection(
      {required this.title, required this.icon, required this.children});

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(16),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 8, sigmaY: 8),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: const Color(0xFF1e293b).withOpacity(0.65),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: Colors.white.withOpacity(0.08)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(children: [
                Icon(icon, color: const Color(0xFF6366f1), size: 18),
                const SizedBox(width: 8),
                Text(title,
                    style: GoogleFonts.inter(
                        color: Colors.white,
                        fontWeight: FontWeight.w600,
                        fontSize: 15)),
              ]),
              const SizedBox(height: 12),
              const Divider(color: Color(0xFF1e293b)),
              const SizedBox(height: 8),
              ...children,
            ],
          ),
        ),
      ),
    );
  }
}

class _DetailRow extends StatelessWidget {
  final String label, value;
  final Color? valueColor;
  const _DetailRow(this.label, this.value, {this.valueColor});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 5),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label,
              style: GoogleFonts.inter(
                  color: const Color(0xFF94a3b8), fontSize: 13)),
          Text(value,
              style: GoogleFonts.inter(
                  color: valueColor ?? Colors.white,
                  fontWeight: FontWeight.w500,
                  fontSize: 13)),
        ],
      ),
    );
  }
}

class _HistoryTile extends StatelessWidget {
  final Assignment assignment;
  const _HistoryTile({required this.assignment});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: const Color(0xFF0f172a).withOpacity(0.5),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
            color: assignment.isActive
                ? const Color(0xFF6366f1).withOpacity(0.4)
                : Colors.white.withOpacity(0.05)),
      ),
      child: Row(
        children: [
          Icon(
            assignment.isActive
                ? Icons.radio_button_checked_rounded
                : Icons.radio_button_unchecked_rounded,
            color: assignment.isActive
                ? const Color(0xFF10b981)
                : const Color(0xFF475569),
            size: 16,
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(assignment.custodianName,
                    style: GoogleFonts.inter(
                        color: Colors.white,
                        fontWeight: FontWeight.w500,
                        fontSize: 13)),
                Text(
                    '${assignment.office?.name ?? 'Sin secretaría'} · ${assignment.assignmentDate}',
                    style: GoogleFonts.inter(
                        color: const Color(0xFF94a3b8), fontSize: 11)),
              ],
            ),
          ),
          if (assignment.isActive)
            Container(
              padding:
                  const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              decoration: BoxDecoration(
                color: const Color(0xFF10b981).withOpacity(0.12),
                borderRadius: BorderRadius.circular(4),
              ),
              child: Text('Activo',
                  style: GoogleFonts.inter(
                      color: const Color(0xFF10b981),
                      fontSize: 10,
                      fontWeight: FontWeight.w600)),
            ),
        ],
      ),
    );
  }
}
