import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../models/models.dart';

/// AuthProvider: Maneja el estado global de autenticación con Laravel Sanctum.
class AuthProvider extends ChangeNotifier {
  bool _isLoggedIn = false;
  bool _isLoading = false;
  String? _errorMessage;

  bool get isLoggedIn => _isLoggedIn;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  Future<void> checkSession() async {
    final token = await ApiService.getToken();
    _isLoggedIn = token != null;
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final result = await ApiService.login(email, password);
      if (result['token'] != null) {
        _isLoggedIn = true;
        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = result['message'] ?? 'Credenciales inválidas';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } catch (e) {
      _errorMessage = 'Error de conexión con el servidor';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await ApiService.logout();
    _isLoggedIn = false;
    notifyListeners();
  }
}

/// InventoryProvider: Gestiona el estado de activos fijos con soporte de búsqueda.
class InventoryProvider extends ChangeNotifier {
  List<FixedAsset> _assets = [];
  List<FixedAsset> _filtered = [];
  List<Item> _items = [];
  List<Provider> _providers = [];
  bool _isLoading = false;
  String? _error;

  List<FixedAsset> get assets => _filtered;
  List<Item> get items => _items;
  List<Provider> get providers => _providers;
  bool get isLoading => _isLoading;
  String? get error => _error;

  Future<void> loadAll() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final results = await Future.wait([
        ApiService.getFixedAssets(),
        ApiService.getInventory(isAsset: true),
        ApiService.getProviders(),
      ]);
      _assets = results[0] as List<FixedAsset>;
      _items = results[1] as List<Item>;
      _providers = results[2] as List<Provider>;
      _filtered = _assets;
    } catch (e) {
      _error = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void search(String query) {
    if (query.isEmpty) {
      _filtered = _assets;
    } else {
      final lq = query.toLowerCase();
      _filtered = _assets.where((a) {
        return a.inventoryCode.toLowerCase().contains(lq) ||
            (a.item?.name.toLowerCase().contains(lq) ?? false) ||
            (a.serialNumber?.toLowerCase().contains(lq) ?? false);
      }).toList();
    }
    notifyListeners();
  }

  Future<void> addAsset(Map<String, dynamic> data) async {
    final asset = await ApiService.createFixedAsset(data);
    _assets.insert(0, asset);
    _filtered = _assets;
    notifyListeners();
  }

  Future<void> updateAsset(int id, Map<String, dynamic> data) async {
    final updated = await ApiService.updateFixedAsset(id, data);
    final index = _assets.indexWhere((a) => a.id == id);
    if (index != -1) {
      _assets[index] = updated;
      _filtered = _assets;
      notifyListeners();
    }
  }

  Future<void> deleteAsset(int id) async {
    await ApiService.deleteFixedAsset(id);
    _assets.removeWhere((a) => a.id == id);
    _filtered = _assets;
    notifyListeners();
  }
}
