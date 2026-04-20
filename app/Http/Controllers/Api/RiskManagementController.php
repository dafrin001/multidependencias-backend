<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RufeRecord;
use App\Models\RufeDemographic;
use App\Models\RufeAgrop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiskManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = RufeRecord::with(['demographics', 'agros']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('evento', 'like', "%{$search}%")
                  ->orWhere('municipio', 'like', "%{$search}%")
                  ->orWhereHas('demographics', function($dq) use ($search) {
                      $dq->where('nombres', 'like', "%{$search}%")
                         ->orWhere('apellidos', 'like', "%{$search}%")
                         ->orWhere('numero_documento', 'like', "%{$search}%");
                  });
            });
        }

        return response()->json($query->latest()->paginate(10));
    }

    public function store(Request $request)
    {
        return DB::transaction(function() use ($request) {
            $record = RufeRecord::create($request->all());

            if ($request->has('demographics')) {
                foreach ($request->demographics as $demographic) {
                    $record->demographics()->create($demographic);
                }
            }

            if ($request->has('agros')) {
                foreach ($request->agros as $agro) {
                    $record->agros()->create($agro);
                }
            }

            return response()->json($record->load(['demographics', 'agros']), 201);
        });
    }

    public function show($id)
    {
        return RufeRecord::with(['demographics', 'agros'])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        return DB::transaction(function() use ($request, $id) {
            $record = RufeRecord::findOrFail($id);
            $record->update($request->except(['demographics', 'agros']));

            if ($request->has('demographics')) {
                $record->demographics()->delete();
                foreach ($request->demographics as $demographic) {
                    $record->demographics()->create($demographic);
                }
            }

            if ($request->has('agros')) {
                $record->agros()->delete();
                foreach ($request->agros as $agro) {
                    $record->agros()->create($agro);
                }
            }

            return response()->json($record->load(['demographics', 'agros']));
        });
    }

    public function export(Request $request)
    {
        $records = RufeRecord::with(['demographics', 'agros'])->get();
        
        $filename = "RUFE_REPORT_" . date('Ymd_His') . ".csv";
        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'ID', 'Municipio', 'Evento', 'Fecha Evento', 'Fecha RUFE', 'Ubicacion', 'Sector', 'Tenencia', 'Estado Bien', 
            'Alojamiento', 'Tipo Bien', 'Cant. Personas', 'Observaciones'
        ];

        $callback = function() use ($records, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM
            fputcsv($file, $columns, ';');

            foreach ($records as $r) {
                fputcsv($file, [
                    $r->id,
                    $r->municipio,
                    $r->evento,
                    $r->fecha_evento,
                    $r->fecha_rufe,
                    $r->ubicacion_tipo,
                    $r->vereda_sector_barrio,
                    $r->forma_tenencia,
                    $r->estado_bien,
                    $r->alojamiento_actual_tipo,
                    $r->tipo_bien,
                    $r->demographics->count(),
                    $r->observaciones
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function stats()
    {
        $totalRecords = RufeRecord::count();
        $totalPeople = RufeDemographic::count();
        $totalAgroHectareas = RufeAgrop::sum('area_cantidad');
        $destroyedAssets = RufeRecord::where('estado_bien', 'DESTRUIDO')->count();

        // Eventos por tipo
        $eventsByType = RufeRecord::select('evento', DB::raw('count(*) as count'))
            ->groupBy('evento')
            ->get();

        // Registros por municipio
        $recordsByMunicipio = RufeRecord::select('municipio', DB::raw('count(*) as count'))
            ->groupBy('municipio')
            ->get();

        // Personas por género
        $peopleByGender = RufeDemographic::select('genero', DB::raw('count(*) as count'))
            ->groupBy('genero')
            ->get();

        // Tendencia mensual (últimos 6 meses)
        $monthlyTrend = RufeRecord::select(
            DB::raw("DATE_FORMAT(fecha_rufe, '%Y-%m') as month"),
            DB::raw('count(*) as count')
        )
        ->groupBy('month')
        ->orderBy('month', 'asc')
        ->take(6)
        ->get();

        return response()->json([
            'summary' => [
                'total_records' => $totalRecords,
                'total_people' => $totalPeople,
                'total_agro_hectareas' => $totalAgroHectareas,
                'destroyed_assets' => $destroyedAssets,
            ],
            'charts' => [
                'events_by_type' => $eventsByType,
                'records_by_municipio' => $recordsByMunicipio,
                'people_by_gender' => $peopleByGender,
                'monthly_trend' => $monthlyTrend,
            ]
        ]);
    }

    public function destroy($id)
    {
        $record = RufeRecord::findOrFail($id);
        $record->delete();
        return response()->json(['message' => 'Registro eliminado'], 204);
    }
}
