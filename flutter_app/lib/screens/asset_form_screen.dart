import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'dart:ui';
import '../models/models.dart';
import '../providers/app_providers.dart';

class AssetFormScreen extends StatefulWidget {
  final FixedAsset? asset; // null = crear, no-null = editar
  const AssetFormScreen({super.key, this.asset});

  @override
  State<AssetFormScreen> createState() => _AssetFormScreenState();
}

class _AssetFormScreenState extends State<AssetFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _codeCtrl = TextEditingController();
  final _serialCtrl = TextEditingController();
  final _priceCtrl = TextEditingController();

  int? _selectedItemId;
  int? _selectedProviderId;
  String _selectedStatus = 'nuevo';
  bool _saving = false;

  bool get isEditing => widget.asset != null;

  @override
  void initState() {
    super.initState();
    if (isEditing) {
      final a = widget.asset!;
      _codeCtrl.text = a.inventoryCode;
      _serialCtrl.text = a.serialNumber ?? '';
      _priceCtrl.text = a.purchasePrice?.toStringAsFixed(0) ?? '';
      _selectedItemId = a.itemId;
      _selectedProviderId = a.providerId;
      _selectedStatus = a.status;
    }
  }

  @override
  void dispose() {
    _codeCtrl.dispose();
    _serialCtrl.dispose();
    _priceCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _saving = true);

    final data = {
      'inventory_code': _codeCtrl.text.trim(),
      'item_id': _selectedItemId,
      'provider_id': _selectedProviderId,
      'serial_number': _serialCtrl.text.trim(),
      'purchase_price': double.tryParse(_priceCtrl.text),
      'status': _selectedStatus,
    };

    try {
      final inv = context.read<InventoryProvider>();
      if (isEditing) {
        await inv.updateAsset(widget.asset!.id, data);
      } else {
        await inv.addAsset(data);
      }
      if (mounted) {
        Navigator.pop(context);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
          backgroundColor: const Color(0xFF10b981),
          content: Text(
            isEditing ? 'Activo actualizado correctamente' : 'Activo creado correctamente',
            style: GoogleFonts.inter(color: Colors.white),
          ),
        ));
      }
    } catch (e) {
      setState(() => _saving = false);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        backgroundColor: const Color(0xFFef4444),
        content: Text(e.toString(),
            style: GoogleFonts.inter(color: Colors.white)),
      ));
    }
  }

  Future<void> _delete() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        backgroundColor: const Color(0xFF1e293b),
        title: Text('¿Eliminar activo?',
            style: GoogleFonts.inter(color: Colors.white)),
        content: Text('Esta acción es irreversible.',
            style: GoogleFonts.inter(color: const Color(0xFF94a3b8))),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Cancelar')),
          TextButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Eliminar',
                  style: TextStyle(color: Color(0xFFef4444)))),
        ],
      ),
    );
    if (ok == true && mounted) {
      await context.read<InventoryProvider>().deleteAsset(widget.asset!.id);
      if (mounted) Navigator.pop(context);
    }
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
            colors: [Color(0xFF0F111A), Color(0xFF1a1f3c)],
          ),
        ),
        child: SafeArea(
          child: Column(
            children: [
              // AppBar personalizada
              Padding(
                padding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.arrow_back_ios_new_rounded,
                          color: Colors.white, size: 20),
                      onPressed: () => Navigator.pop(context),
                    ),
                    Expanded(
                      child: Text(
                        isEditing ? 'Editar Activo' : 'Nuevo Activo Fijo',
                        style: GoogleFonts.inter(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.w700),
                      ),
                    ),
                    if (isEditing)
                      IconButton(
                        icon: const Icon(Icons.delete_outline_rounded,
                            color: Color(0xFFef4444)),
                        onPressed: _delete,
                      ),
                  ],
                ),
              ),

              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.symmetric(horizontal: 20),
                  child: Form(
                    key: _formKey,
                    child: Consumer<InventoryProvider>(
                      builder: (ctx, inv, _) {
                        return Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            _buildGlassForm([
                              _FormField(
                                controller: _codeCtrl,
                                label: 'Código de Inventario',
                                icon: Icons.qr_code_rounded,
                                validator: (v) => v!.isEmpty
                                    ? 'Campo obligatorio'
                                    : null,
                              ),
                              const SizedBox(height: 14),
                              _FormField(
                                controller: _serialCtrl,
                                label: 'Número de Serie',
                                icon: Icons.tag_rounded,
                              ),
                              const SizedBox(height: 14),
                              _FormField(
                                controller: _priceCtrl,
                                label: 'Precio de Compra',
                                icon: Icons.attach_money_rounded,
                                keyboardType: TextInputType.number,
                              ),
                            ]),
                            const SizedBox(height: 16),
                            _buildGlassForm([
                              // Selector de artículo
                              _GlassDropdown<int>(
                                label: 'Artículo Base',
                                icon: Icons.category_rounded,
                                value: _selectedItemId,
                                items: inv.items
                                    .map((i) => DropdownMenuItem(
                                          value: i.id,
                                          child: Text(i.name,
                                              style: GoogleFonts.inter(
                                                  color: Colors.white,
                                                  fontSize: 14)),
                                        ))
                                    .toList(),
                                onChanged: (v) =>
                                    setState(() => _selectedItemId = v),
                                validator: (v) =>
                                    v == null ? 'Selecciona un artículo' : null,
                              ),
                              const SizedBox(height: 14),
                              // Selector de proveedor
                              _GlassDropdown<int>(
                                label: 'Proveedor',
                                icon: Icons.business_rounded,
                                value: _selectedProviderId,
                                items: inv.providers
                                    .map((p) => DropdownMenuItem(
                                          value: p.id,
                                          child: Text(p.companyName,
                                              style: GoogleFonts.inter(
                                                  color: Colors.white,
                                                  fontSize: 14)),
                                        ))
                                    .toList(),
                                onChanged: (v) =>
                                    setState(() => _selectedProviderId = v),
                                validator: (v) =>
                                    v == null ? 'Selecciona un proveedor' : null,
                              ),
                              const SizedBox(height: 14),
                              // Selector de estado
                              _GlassDropdown<String>(
                                label: 'Estado del Activo',
                                icon: Icons.circle_rounded,
                                value: _selectedStatus,
                                items: const [
                                  DropdownMenuItem(
                                      value: 'nuevo',
                                      child: Text('Nuevo',
                                          style:
                                              TextStyle(color: Colors.white))),
                                  DropdownMenuItem(
                                      value: 'bueno',
                                      child: Text('Bueno',
                                          style:
                                              TextStyle(color: Colors.white))),
                                  DropdownMenuItem(
                                      value: 'regular',
                                      child: Text('Regular',
                                          style:
                                              TextStyle(color: Colors.white))),
                                  DropdownMenuItem(
                                      value: 'malo',
                                      child: Text('Malo',
                                          style:
                                              TextStyle(color: Colors.white))),
                                  DropdownMenuItem(
                                      value: 'baja',
                                      child: Text('De Baja',
                                          style:
                                              TextStyle(color: Colors.white))),
                                ],
                                onChanged: (v) => setState(
                                    () => _selectedStatus = v!),
                              ),
                            ]),
                            const SizedBox(height: 28),
                            // Botón guardar
                            GestureDetector(
                              onTap: _saving ? null : _save,
                              child: Container(
                                height: 54,
                                decoration: BoxDecoration(
                                  gradient: const LinearGradient(colors: [
                                    Color(0xFF6366f1),
                                    Color(0xFF8b5cf6)
                                  ]),
                                  borderRadius: BorderRadius.circular(14),
                                  boxShadow: [
                                    BoxShadow(
                                      color: const Color(0xFF6366f1)
                                          .withOpacity(0.35),
                                      blurRadius: 20,
                                      offset: const Offset(0, 8),
                                    )
                                  ],
                                ),
                                child: Center(
                                  child: _saving
                                      ? const SizedBox(
                                          width: 22,
                                          height: 22,
                                          child: CircularProgressIndicator(
                                              color: Colors.white,
                                              strokeWidth: 2.5),
                                        )
                                      : Text(
                                          isEditing
                                              ? 'Guardar Cambios'
                                              : 'Crear Activo Fijo',
                                          style: GoogleFonts.inter(
                                              color: Colors.white,
                                              fontSize: 15,
                                              fontWeight: FontWeight.w600),
                                        ),
                                ),
                              ),
                            ),
                            const SizedBox(height: 40),
                          ],
                        );
                      },
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildGlassForm(List<Widget> children) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(16),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 8, sigmaY: 8),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: const Color(0xFF1e293b).withOpacity(0.65),
            borderRadius: BorderRadius.circular(16),
            border:
                Border.all(color: Colors.white.withOpacity(0.08)),
          ),
          child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: children),
        ),
      ),
    );
  }
}

// ─── Widgets ----------------------------------------------------------------

class _FormField extends StatelessWidget {
  final TextEditingController controller;
  final String label;
  final IconData icon;
  final String? Function(String?)? validator;
  final TextInputType? keyboardType;

  const _FormField({
    required this.controller,
    required this.label,
    required this.icon,
    this.validator,
    this.keyboardType,
  });

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      validator: validator,
      keyboardType: keyboardType,
      style: GoogleFonts.inter(color: Colors.white),
      decoration: InputDecoration(
        labelText: label,
        labelStyle:
            GoogleFonts.inter(color: const Color(0xFF94a3b8), fontSize: 13),
        prefixIcon: Icon(icon, color: const Color(0xFF6366f1), size: 18),
        filled: true,
        fillColor: const Color(0xFF0f172a).withOpacity(0.5),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide:
              BorderSide(color: Colors.white.withOpacity(0.08)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide:
              const BorderSide(color: Color(0xFF6366f1), width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Color(0xFFef4444)),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Color(0xFFef4444), width: 1.5),
        ),
      ),
    );
  }
}

class _GlassDropdown<T> extends StatelessWidget {
  final String label;
  final IconData icon;
  final T? value;
  final List<DropdownMenuItem<T>> items;
  final void Function(T?) onChanged;
  final String? Function(T?)? validator;

  const _GlassDropdown({
    required this.label,
    required this.icon,
    required this.value,
    required this.items,
    required this.onChanged,
    this.validator,
  });

  @override
  Widget build(BuildContext context) {
    return DropdownButtonFormField<T>(
      value: value,
      items: items,
      onChanged: onChanged,
      validator: validator,
      dropdownColor: const Color(0xFF1e293b),
      style: GoogleFonts.inter(color: Colors.white),
      decoration: InputDecoration(
        labelText: label,
        labelStyle:
            GoogleFonts.inter(color: const Color(0xFF94a3b8), fontSize: 13),
        prefixIcon: Icon(icon, color: const Color(0xFF6366f1), size: 18),
        filled: true,
        fillColor: const Color(0xFF0f172a).withOpacity(0.5),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide:
              BorderSide(color: Colors.white.withOpacity(0.08)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide:
              const BorderSide(color: Color(0xFF6366f1), width: 1.5),
        ),
      ),
    );
  }
}
