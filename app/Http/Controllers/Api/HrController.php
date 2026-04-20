<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Position;
use App\Models\Official;
use App\Models\PayrollPeriod;
use App\Models\PayrollItem;
use App\Models\TrainingProgram;
use App\Models\SstRecord;
use App\Models\HrCommittee;
use App\Models\HrCommitteeMember;
use App\Models\HrMeeting;
use App\Models\HrAdministrativeSituation;
use App\Models\ContractorContract;
use App\Models\ContractDeductionRule;
use App\Models\EdlRecord;
use App\Models\HrSetting;
use App\Models\StampCatalog;
use Illuminate\Support\Facades\DB;

class HrController extends Controller
{
    /**
     * Dashboard de Talento Humano
     */
    public function stats()
    {
        $lastPayroll = PayrollPeriod::where('status', 'paid')->orderBy('year', 'desc')->orderBy('month', 'desc')->first();
        
        return [
            'total_officials' => Official::count(),
            'by_type' => Official::select('employment_type', DB::raw('count(*) as total'))->groupBy('employment_type')->get(),
            'by_status' => Official::select('employment_status', DB::raw('count(*) as total'))->groupBy('employment_status')->get(),
            'pending_sigep' => Official::where('sigep_updated', false)->count(),
            'active_trainings' => TrainingProgram::where('status', 'planned')->count(),
            'recent_sst' => SstRecord::where('record_date', '>=', now()->subDays(30))->count(),
            'last_payroll_amount' => $lastPayroll ? $lastPayroll->total_amount : 0,
            'last_payroll_month' => $lastPayroll ? $lastPayroll->month . '/' . $lastPayroll->year : '-'
        ];
    }

    /**
     * Gestión de Cargos
     */
    public function getPositions() { return Position::all(); }
    public function storePosition(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string',
            'grade' => 'nullable|string',
            'base_salary' => 'required|numeric',
            'level' => 'required|in:directivo,asesor,profesional,tecnico,asistencial',
        ]);
        return Position::create($validated);
    }

    /**
     * Gestión de Nómina
     */
    public function getPayrollPeriods() {
        return PayrollPeriod::withCount('items')->orderBy('year', 'desc')->orderBy('month', 'desc')->get();
    }

    public function getPayrollPeriod($id) {
        return PayrollPeriod::with('items.official')->findOrFail($id);
    }

    public function processPayroll(Request $request) {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
            'resolution_number' => 'nullable',
            'resolution_date' => 'nullable|date',
        ]);

        $start = date("Y-m-d", strtotime("{$request->year}-{$request->month}-01"));
        $end = date("Y-m-t", strtotime($start));

        $period = PayrollPeriod::create([
            'month' => $request->month,
            'year' => $request->year,
            'start_date' => $start,
            'end_date' => $end,
            'type' => 'employees',
            'status' => 'processed',
            'resolution_number' => $request->resolution_number,
            'resolution_date' => $request->resolution_date,
        ]);

        // Simulación de liquidación masiva para servidores (no contratistas)
        $officials = Official::with('position_rel')->where('employment_type', '!=', 'contratista')->get();
        $total = 0;

        $healthRate = (float) HrSetting::where('key', 'health_deduction_rate')->value('value') / 100;
        $pensionRate = (float) HrSetting::where('key', 'pension_deduction_rate')->value('value') / 100;

        foreach($officials as $off) {
            $base = $off->position_rel->base_salary ?? 0;
            $health = $base * $healthRate;
            $pension = $base * $pensionRate;
            $net = $base - $health - $pension;

            PayrollItem::create([
                'payroll_period_id' => $period->id,
                'official_id' => $off->id,
                'salary_base' => $base,
                'deductions_health' => $health,
                'deductions_pension' => $pension,
                'net_pay' => $net,
                'details' => ['base' => $base, 'salud_rate' => $healthRate, 'pension_rate' => $pensionRate]
            ]);
            $total += $net;
        }

        $period->update(['total_amount' => $total]);
        return $period->load('items.official');
    }

    public function markPayrollPaid($id) {
        $period = PayrollPeriod::findOrFail($id);
        $period->update(['status' => 'paid']);
        return $period;
    }

    public function updatePayrollItem(Request $request, $id) {
        $item = PayrollItem::findOrFail($id);
        $data = $request->validate([
            'salary_base' => 'numeric',
            'allowances' => 'numeric',
            'overtime' => 'numeric',
        ]);
        
        $item->update($data);
        
        // Recalcular neto (simplificado)
        $totalDevengado = $item->salary_base + ($item->allowances ?? 0) + ($item->overtime ?? 0);
        $totalDeducciones = $item->deductions_health + $item->deductions_pension;
        
        $item->update(['net_pay' => $totalDevengado - $totalDeducciones]);
        
        return $item;
    }

    /**
     * Gestión de Configuraciones (TH)
     */
    public function getSettings() { return HrSetting::all(); }
    public function updateSetting(Request $request, $id) {
        $setting = HrSetting::findOrFail($id);
        $setting->update($request->validate(['value' => 'required']));
        return $setting;
    }

    public function getStampCatalog() { return StampCatalog::all(); }
    public function storeStampCatalog(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string',
            'default_value' => 'required|numeric',
            'type' => 'required|in:percentage,fixed'
        ]);
        return StampCatalog::create($validated);
    }

    public function updateStampCatalog(Request $request, $id) {
        $stamp = StampCatalog::findOrFail($id);
        $validated = $request->validate([
            'name' => 'string',
            'default_value' => 'numeric',
            'type' => 'in:percentage,fixed'
        ]);
        $stamp->update($validated);
        return $stamp;
    }

    public function deleteStampCatalog($id) {
        StampCatalog::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Plan Institucional de Capacitación (PIC)
     */
    public function getTrainingPrograms() { return TrainingProgram::withCount('attendees')->get(); }
    public function storeTraining(Request $request) {
        $validated = $request->validate([
            'title' => 'required',
            'type' => 'required|in:tecnica,profesional,induccion,bienestar',
            'scheduled_date' => 'required|date',
            'hours' => 'required|integer'
        ]);
        return TrainingProgram::create($validated);
    }

    /**
     * Seguridad y Salud en el Trabajo (SST)
     */
    public function getSstRecords() { return SstRecord::with('official')->get(); }
    
    public function storeSstRecord(Request $request) {
        $validated = $request->validate([
            'official_id' => 'required|exists:officials,id',
            'type' => 'required|in:examen_ingreso,examen_periodico,examen_egreso,incidente,accidente',
            'record_date' => 'required|date',
            'provider_name' => 'nullable|string',
            'findings' => 'nullable|string',
        ]);
        return SstRecord::create($validated);
    }

    /**
     * Comités (COPASST, Convivencia)
     */
    public function getCommittees() {
        return HrCommittee::withCount('members')->with('members.official')->get();
    }

    public function storeCommittee(Request $request) {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'nullable',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date',
        ]);
        return HrCommittee::create($validated);
    }

    public function storeMember(Request $request) {
        $validated = $request->validate([
            'hr_committee_id' => 'required|exists:hr_committees,id',
            'official_id' => 'required|exists:officials,id',
            'role' => 'required',
            'appointment_date' => 'nullable|date',
        ]);
        return HrCommitteeMember::create($validated);
    }

    /**
     * Reuniones y Actas
     */
    public function getMeetings() {
        return HrMeeting::with('committee')->orderBy('meeting_date', 'desc')->get();
    }

    public function storeMeeting(Request $request) {
        $validated = $request->validate([
            'hr_committee_id' => 'required|exists:hr_committees,id',
            'title' => 'required',
            'meeting_date' => 'required|date',
            'meeting_time' => 'nullable',
            'location' => 'nullable',
            'agenda' => 'nullable',
            'minutes_content' => 'nullable',
        ]);
        return HrMeeting::create($validated);
    }

    /**
     * Situaciones Administrativas
     */
    public function getSituations() {
        return HrAdministrativeSituation::with('official')->orderBy('start_date', 'desc')->get();
    }

    public function storeSituation(Request $request) {
        $validated = $request->validate([
            'official_id' => 'required|exists:officials,id',
            'type' => 'required|in:vacaciones,permiso,licencia_maternidad,licencia_paternidad,licencia_luto,incapacidad,comision,encargo,otro',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable',
        ]);
        return HrAdministrativeSituation::create($validated);
    }

    /**
     * Gestión de Contratistas (Nómina y Honorarios)
     */
    public function getContracts() { 
        $contracts = ContractorContract::with('official', 'deductionRules', 'payments')->get(); 
        
        return $contracts->map(function($c) {
            $totalPaid = $c->payments->sum('salary_base');
            $c->total_paid = $totalPaid;
            $c->balance = $c->total_contract_value - $totalPaid;
            return $c;
        });
    }

    public function storeContract(Request $request) {
        $data = $request->validate([
            'official_id' => 'required|exists:officials,id',
            'contract_number' => 'required',
            'cdp' => 'nullable|string',
            'rp' => 'nullable|string',
            'rubro' => 'nullable|string',
            'contract_type' => 'nullable|string',
            'object' => 'nullable',
            'supervisor_name' => 'nullable|string',
            'supervisor_position' => 'nullable|string',
            'arl_risk_level' => 'nullable|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'total_contract_value' => 'nullable|numeric',
            'monthly_payment_value' => 'required|numeric',
            'observations' => 'nullable|string',
        ]);
        return ContractorContract::create($data);
    }

    public function storeDeductionRule(Request $request) {
        $data = $request->validate([
            'contract_id' => 'required|exists:contractor_contracts,id',
            'name' => 'required',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric',
            'is_recurrent' => 'boolean'
        ]);
        return ContractDeductionRule::create($data);
    }

    public function processContractorPayroll(Request $request) {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer',
            'resolution_number' => 'nullable',
            'resolution_date' => 'nullable|date',
        ]);

        $start = date("Y-m-d", strtotime("{$request->year}-{$request->month}-01"));
        $end = date("Y-m-t", strtotime($start));

        $period = PayrollPeriod::create([
            'month' => $request->month,
            'year' => $request->year,
            'start_date' => $start,
            'end_date' => $end,
            'type' => 'contractors',
            'status' => 'processed',
            'resolution_number' => $request->resolution_number,
            'resolution_date' => $request->resolution_date,
        ]);

        $totalPeriod = 0;

        if ($request->clone_previous) {
            $lastPeriod = PayrollPeriod::where('month', $request->month == 1 ? 12 : $request->month - 1)
                ->where('year', $request->month == 1 ? $request->year - 1 : $request->year)
                ->first();

            if ($lastPeriod) {
                foreach ($lastPeriod->items as $item) {
                    // Solo clonar contratistas si el tipo de nómina es contratistas (suponiendo flag o via official_type)
                    if ($item->official->employment_type === 'contratista') {
                        $newItem = $item->replicate();
                        $newItem->payroll_period_id = $period->id;
                        $newItem->save();
                        $totalPeriod += $newItem->net_pay;
                    }
                }
            }
        } else {
            // Liquidación basada en contratos activos
            $activeContracts = ContractorContract::with('deductionRules', 'official')
                ->where('is_active', true)
                ->where('start_date', '<=', $end)
                ->where('end_date', '>=', $start)
                ->get();

            foreach ($activeContracts as $contract) {
                $base = $contract->monthly_payment_value;
                $deductions = [];
                $totalDeductions = 0;

                foreach ($contract->deductionRules as $rule) {
                    $val = 0;
                    if ($rule->type === 'percentage') {
                        $val = $base * ($rule->value / 100);
                    } else {
                        $val = $rule->value;
                    }
                    $deductions[$rule->name] = $val;
                    $totalDeductions += $val;
                }

                $net = $base - $totalDeductions;

                PayrollItem::create([
                    'payroll_period_id' => $period->id,
                    'official_id' => $contract->official_id,
                    'contractor_contract_id' => $contract->id,
                    'salary_base' => $base,
                    'net_pay' => $net,
                    'details' => [
                        'contract_number' => $contract->contract_number,
                        'gross' => $base,
                        'deductions' => $deductions,
                        'total_deductions' => $totalDeductions
                    ]
                ]);
                $totalPeriod += $net;
            }
        }

        $period->update(['total_amount' => $totalPeriod]);
        return $period->load('items.official');
    }
    public function exportReport(Request $request)
    {
        $type = $request->input('type', 'officials');
        $fileName = "reporte_{$type}_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($type) {
            $file = fopen('php://output', 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

            if ($type === 'officials') {
                fputcsv($file, ['ID', 'Nombre Completo', 'Documento', 'Cargo', 'Dependencia', 'Tipo', 'Estado']);
                $data = Official::with('position_rel', 'office')->get();
                foreach($data as $o) {
                    fputcsv($file, [$o->id, $o->full_name, $o->document_number, $o->position_rel->name ?? 'N/A', $o->office->name ?? 'N/A', $o->employment_type, $o->employment_status]);
                }
            } elseif ($type === 'payroll') {
                fputcsv($file, ['ID', 'Mes/Año', 'Tipo', 'Total', 'Estado', 'Items']);
                $data = PayrollPeriod::withCount('items')->get();
                foreach($data as $p) {
                    fputcsv($file, [$p->id, "{$p->month}/{$p->year}", $p->type, $p->total_amount, $p->status, $p->items_count]);
                }
            } elseif ($type === 'pic') {
                fputcsv($file, ['ID', 'Actividad', 'Tipo', 'Fecha', 'Horas', 'Asistentes', 'Estado']);
                $data = TrainingProgram::withCount('attendees')->get();
                foreach($data as $t) {
                    fputcsv($file, [$t->id, $t->title, $t->type, $t->scheduled_date, $t->hours, $t->attendees_count, $t->status]);
                }
            } elseif ($type === 'sst') {
                fputcsv($file, ['ID', 'Funcionario', 'Tipo', 'Fecha', 'Hallazgos']);
                $data = SstRecord::with('official')->get();
                foreach($data as $s) {
                    fputcsv($file, [$s->id, $s->official->full_name, $s->type, $s->record_date, $s->findings]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function deletePosition($id) { Position::findOrFail($id)->delete(); return response()->json(['success' => true]); }
    public function deleteOfficial($id) { Official::findOrFail($id)->delete(); return response()->json(['success' => true]); }
    
    public function updateSituation(Request $request, $id) {
        $situation = HrAdministrativeSituation::findOrFail($id);
        $situation->update(['status' => $request->status]);
        return $situation;
    }

    public function getOfficials() { 
        return Official::with('position_rel', 'office')->orderBy('full_name')->get(); 
    }

    public function storeOfficial(Request $request) {
        $data = $request->validate([
            'full_name' => 'required',
            'document_number' => 'required|unique:officials,document_number',
            'document_type' => 'required',
            'position_id' => 'required|exists:positions,id',
            'office_id' => 'required|exists:offices,id',
            'employment_type' => 'required',
            'employment_status' => 'required',
            'entry_date' => 'nullable|date',
            'email' => 'nullable|email',
            'sigep_updated' => 'boolean'
        ]);
        // Compatibilidad con campo 'position' string antiguo
        $pos = Position::find($request->position_id);
        if($pos) $data['position'] = $pos->name;
        
        return Official::create($data);
    }

    /**
     * Evaluación del Desempeño Laboral (EDL)
     */
    public function getEdlRecords() {
        return EdlRecord::with('official')->orderBy('year', 'desc')->get();
    }

    public function storeEdlRecord(Request $request) {
        $validated = $request->validate([
            'official_id' => 'required|exists:officials,id',
            'year' => 'required|integer',
            'period' => 'required|in:semestral_1,semestral_2,anual',
            'compromises' => 'nullable|string',
        ]);
        return EdlRecord::create($validated);
    }

    public function updateEdlScore(Request $request, $id) {
        $record = EdlRecord::findOrFail($id);
        $record->update($request->validate([
            'score' => 'required|integer|between:0,100',
            'feedback' => 'nullable|string',
            'status' => 'nullable|in:evaluated,closed'
        ]));
        return $record;
    }
}
