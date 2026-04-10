import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/models.dart';

class ApiService {
  // 🔧 Cambia esta URL cuando tu app esté en producción (VPS)
  static const String baseUrl = 'http://10.0.2.2:8000/api'; // Emulador Android
  // static const String baseUrl = 'http://TU_IP_VPS/api'; // Producción

  // ============================================================
  // Token Management
  // ============================================================
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('sanctum_token');
  }

  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('sanctum_token', token);
  }

  static Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('sanctum_token');
  }

  static Future<Map<String, String>> _headers({bool auth = false}) async {
    final headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    };
    if (auth) {
      final token = await getToken();
      if (token != null) {
        headers['Authorization'] = 'Bearer $token';
      }
    }
    return headers;
  }

  // ============================================================
  // Auth
  // ============================================================
  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: await _headers(),
      body: jsonEncode({'email': email, 'password': password}),
    );
    final data = jsonDecode(response.body);
    if (response.statusCode == 200 && data['token'] != null) {
      await saveToken(data['token']);
    }
    return data;
  }

  static Future<void> logout() async {
    final headers = await _headers(auth: true);
    await http.post(Uri.parse('$baseUrl/logout'), headers: headers);
    await clearToken();
  }

  // ============================================================
  // Fixed Assets (Activos Fijos)
  // ============================================================
  static Future<List<FixedAsset>> getFixedAssets() async {
    final response = await http.get(
      Uri.parse('$baseUrl/fixed-assets'),
      headers: await _headers(),
    );
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body)['data'] as List;
      return data.map((e) => FixedAsset.fromJson(e)).toList();
    }
    throw Exception('Error al cargar activos fijos');
  }

  static Future<FixedAsset> getFixedAsset(int id) async {
    final response = await http.get(
      Uri.parse('$baseUrl/fixed-assets/$id'),
      headers: await _headers(),
    );
    if (response.statusCode == 200) {
      return FixedAsset.fromJson(jsonDecode(response.body)['data']);
    }
    throw Exception('Activo no encontrado');
  }

  static Future<FixedAsset> createFixedAsset(Map<String, dynamic> data) async {
    final response = await http.post(
      Uri.parse('$baseUrl/fixed-assets'),
      headers: await _headers(),
      body: jsonEncode(data),
    );
    if (response.statusCode == 201) {
      return FixedAsset.fromJson(jsonDecode(response.body)['data']);
    }
    final err = jsonDecode(response.body);
    throw Exception(err['message'] ?? 'Error al crear activo');
  }

  static Future<FixedAsset> updateFixedAsset(int id, Map<String, dynamic> data) async {
    final response = await http.put(
      Uri.parse('$baseUrl/fixed-assets/$id'),
      headers: await _headers(),
      body: jsonEncode(data),
    );
    if (response.statusCode == 200) {
      return FixedAsset.fromJson(jsonDecode(response.body)['data']);
    }
    final err = jsonDecode(response.body);
    throw Exception(err['message'] ?? 'Error al actualizar activo');
  }

  static Future<void> deleteFixedAsset(int id) async {
    final response = await http.delete(
      Uri.parse('$baseUrl/fixed-assets/$id'),
      headers: await _headers(),
    );
    if (response.statusCode != 200) {
      throw Exception('Error al eliminar activo');
    }
  }

  // ============================================================
  // Inventory (Items/Artículos)
  // ============================================================
  static Future<List<Item>> getInventory({bool? isAsset}) async {
    String url = '$baseUrl/inventory';
    if (isAsset != null) {
      url += '?is_asset=${isAsset ? 1 : 0}';
    }
    final response = await http.get(Uri.parse(url), headers: await _headers());
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body)['data'] as List;
      return data.map((e) => Item.fromJson(e)).toList();
    }
    throw Exception('Error al cargar inventario');
  }

  // ============================================================
  // Providers (Proveedores)
  // ============================================================
  static Future<List<Provider>> getProviders() async {
    final response = await http.get(
      Uri.parse('$baseUrl/providers'),
      headers: await _headers(),
    );
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body)['data'] as List;
      return data.map((e) => Provider.fromJson(e)).toList();
    }
    throw Exception('Error al cargar proveedores');
  }

  static Future<Provider> createProvider(Map<String, dynamic> data) async {
    final response = await http.post(
      Uri.parse('$baseUrl/providers'),
      headers: await _headers(),
      body: jsonEncode(data),
    );
    if (response.statusCode == 201) {
      return Provider.fromJson(jsonDecode(response.body)['data']);
    }
    final err = jsonDecode(response.body);
    throw Exception(err['message'] ?? 'Error al crear proveedor');
  }

  // ============================================================
  // Assignments (Asignaciones)
  // ============================================================
  static Future<Map<String, dynamic>> assignAsset({
    required int fixedAssetId,
    required int officeId,
    required String custodianName,
    required String assignmentDate,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/assignments/assign'),
      headers: await _headers(auth: true),
      body: jsonEncode({
        'fixed_asset_id': fixedAssetId,
        'office_id': officeId,
        'custodian_name': custodianName,
        'assignment_date': assignmentDate,
      }),
    );
    return jsonDecode(response.body);
  }

  static Future<List<Assignment>> getAssetAssignments(int assetId) async {
    final response = await http.get(
      Uri.parse('$baseUrl/fixed-assets/$assetId/assignments'),
      headers: await _headers(auth: true),
    );
    if (response.statusCode == 200) {
      final data = jsonDecode(response.body)['data'] as List;
      return data.map((e) => Assignment.fromJson(e)).toList();
    }
    throw Exception('Error al cargar asignaciones');
  }
}
