# Inventario Municipal - App Flutter

Aplicación móvil en Flutter para el Sistema de Gestión de Inventario y Activos Fijos. Conecta directamente con el backend de Laravel 11.

## Estructura del Proyecto

```
flutter_app/
├── pubspec.yaml              → Dependencias del proyecto
└── lib/
    ├── main.dart             → Punto de entrada, rutas y providers globales
    ├── models/
    │   └── models.dart       → Clases Dart (FixedAsset, Item, Provider, etc.)
    ├── services/
    │   └── api_service.dart  → Todas las peticiones HTTP a la API Laravel
    ├── providers/
    │   └── app_providers.dart → AuthProvider e InventoryProvider (estado global)
    └── screens/
        ├── login_screen.dart       → Pantalla de login con Glassmorphism
        ├── dashboard_screen.dart   → Panel principal con lista de activos
        ├── asset_detail_screen.dart → Detalle completo + historial de custodios
        └── asset_form_screen.dart  → Formulario crear/editar activos
```

## Pasos para ejecutar el proyecto

### 1. Obtener Flutter SDK
Descarga e instala Flutter desde: https://docs.flutter.dev/get-started/install/windows

### 2. Crear el proyecto base
En tu terminal, navega a la carpeta donde quieras el proyecto y corre:
```bash
flutter create inventario_municipal
cd inventario_municipal
```

### 3. Reemplazar archivos
Copia los archivos de la carpeta `flutter_app/` de este repositorio dentro del proyecto Flutter recién creado.

### 4. Configurar la URL de la API
Abre `lib/services/api_service.dart` y ajusta la constante `baseUrl`:

```dart
// Para el emulador Android (localhost del PC):
static const String baseUrl = 'http://10.0.2.2:8000/api';

// Para producción (tu VPS):
static const String baseUrl = 'http://TU_IP_O_DOMINIO/api';
```

### 5. Instalar dependencias
```bash
flutter pub get
```

### 6. Ejecutar
```bash
flutter run
```

## Pantallas implementadas

| Pantalla | Descripción |
|---|---|
| `LoginScreen` | Login de funcionarios con token Sanctum |
| `DashboardScreen` | Lista de todos los activos con buscador y métricas |
| `AssetDetailScreen` | Detalles completos, proveedor, custodio actual e historial |
| `AssetFormScreen` | Formulario para crear, editar y eliminar activos |

## Credenciales de prueba

El usuario de prueba fue creado por el `DummyDataSeeder` de Laravel.  
Verifícalas en tu base de datos o créalas con:

```bash
php artisan tinker
User::create(['name' => 'Admin', 'email' => 'admin@alcaldia.gov', 'password' => bcrypt('password')]);
```

## Notas de seguridad
- El token de Sanctum se guarda con `shared_preferences` en el dispositivo.
- Las rutas de `assignments` requieren token (`Bearer`). Las demás están abiertas para facilitar las pruebas, **recuerda protegerlas antes de producción**.
