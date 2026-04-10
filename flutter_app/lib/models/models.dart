// Modelo: FixedAsset
// Mapea exactamente los campos que retorna GET /api/fixed-assets

class FixedAsset {
  final int id;
  final int itemId;
  final int providerId;
  final String inventoryCode;
  final String? serialNumber;
  final double? purchasePrice;
  final String status;
  final String? imageUrl;
  final Item? item;
  final Provider? provider;
  final Assignment? activeAssignment;

  FixedAsset({
    required this.id,
    required this.itemId,
    required this.providerId,
    required this.inventoryCode,
    this.serialNumber,
    this.purchasePrice,
    required this.status,
    this.imageUrl,
    this.item,
    this.provider,
    this.activeAssignment,
  });

  factory FixedAsset.fromJson(Map<String, dynamic> json) {
    return FixedAsset(
      id: json['id'],
      itemId: json['item_id'],
      providerId: json['provider_id'],
      inventoryCode: json['inventory_code'],
      serialNumber: json['serial_number'],
      purchasePrice: json['purchase_price'] != null
          ? double.tryParse(json['purchase_price'].toString())
          : null,
      status: json['status'],
      imageUrl: json['image_url'],
      item: json['item'] != null ? Item.fromJson(json['item']) : null,
      provider: json['provider'] != null ? Provider.fromJson(json['provider']) : null,
      activeAssignment: json['active_assignment'] != null
          ? Assignment.fromJson(json['active_assignment'])
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'item_id': itemId,
      'provider_id': providerId,
      'inventory_code': inventoryCode,
      'serial_number': serialNumber,
      'purchase_price': purchasePrice,
      'status': status,
    };
  }

  // Color visual por estado
  String get statusLabel {
    const labels = {
      'nuevo': 'Nuevo',
      'bueno': 'Bueno',
      'regular': 'Regular',
      'malo': 'Malo',
      'baja': 'De Baja',
    };
    return labels[status] ?? status;
  }
}

// Modelo: Item (Artículo de inventario)
class Item {
  final int id;
  final String name;
  final bool isAsset;
  final Category? category;

  Item({
    required this.id,
    required this.name,
    required this.isAsset,
    this.category,
  });

  factory Item.fromJson(Map<String, dynamic> json) {
    return Item(
      id: json['id'],
      name: json['name'],
      isAsset: json['is_asset'] == 1 || json['is_asset'] == true,
      category: json['category'] != null ? Category.fromJson(json['category']) : null,
    );
  }
}

// Modelo: Category
class Category {
  final int id;
  final String name;
  final String code;

  Category({required this.id, required this.name, required this.code});

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'],
      name: json['name'],
      code: json['code'],
    );
  }
}

// Modelo: Provider (Proveedor)
class Provider {
  final int id;
  final String nit;
  final String companyName;
  final String? contact;

  Provider({
    required this.id,
    required this.nit,
    required this.companyName,
    this.contact,
  });

  factory Provider.fromJson(Map<String, dynamic> json) {
    return Provider(
      id: json['id'],
      nit: json['nit'],
      companyName: json['company_name'],
      contact: json['contact'],
    );
  }

  Map<String, dynamic> toJson() => {
        'nit': nit,
        'company_name': companyName,
        'contact': contact,
      };
}

// Modelo: Assignment (Asignación de activo a funcionario)
class Assignment {
  final int id;
  final int fixedAssetId;
  final int officeId;
  final String custodianName;
  final String assignmentDate;
  final bool isActive;
  final Office? office;

  Assignment({
    required this.id,
    required this.fixedAssetId,
    required this.officeId,
    required this.custodianName,
    required this.assignmentDate,
    required this.isActive,
    this.office,
  });

  factory Assignment.fromJson(Map<String, dynamic> json) {
    return Assignment(
      id: json['id'],
      fixedAssetId: json['fixed_asset_id'],
      officeId: json['office_id'],
      custodianName: json['custodian_name'],
      assignmentDate: json['assignment_date'],
      isActive: json['is_active'] == 1 || json['is_active'] == true,
      office: json['office'] != null ? Office.fromJson(json['office']) : null,
    );
  }
}

// Modelo: Office (Secretaría)
class Office {
  final int id;
  final String name;

  Office({required this.id, required this.name});

  factory Office.fromJson(Map<String, dynamic> json) {
    return Office(id: json['id'], name: json['name']);
  }
}
