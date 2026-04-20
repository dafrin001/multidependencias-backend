<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;
use App\Models\Official;
use App\Models\TrainingProgram;
use App\Models\SstRecord;
use App\Models\Office;
use App\Models\HrCommittee;
use App\Models\HrCommitteeMember;
use App\Models\HrMeeting;
use App\Models\HrAdministrativeSituation;
use App\Models\ContractorContract;
use App\Models\ContractDeductionRule;
use App\Models\PayrollPeriod;
use Illuminate\Support\Facades\DB;

class HrSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cargos (Planta de Personal)
        $positions = [
            ['name' => 'Alcalde Municipal',           'code' => '001', 'grade' => '01', 'level' => 'directivo',   'base_salary' => 12500000],
            ['name' => 'Secretario de Despacho',      'code' => '020', 'grade' => '02', 'level' => 'asesor',      'base_salary' => 8200000],
            ['name' => 'Profesional Universitario',   'code' => '219', 'grade' => '01', 'level' => 'profesional', 'base_salary' => 4500000],
            ['name' => 'Técnico Administrativo',     'code' => '367', 'grade' => '03', 'level' => 'tecnico',     'base_salary' => 2800000],
            ['name' => 'Auxiliar Administrativo',      'code' => '405', 'grade' => '05', 'level' => 'asistencial', 'base_salary' => 1800000],
            ['name' => 'Contador Municipal',          'code' => '219', 'grade' => '04', 'level' => 'profesional', 'base_salary' => 5200000],
        ];

        foreach ($positions as $p) {
            Position::firstOrCreate(['name' => $p['name']], $p);
        }

        // 2. Vincular Oficiales Existentes con Datos de TH
        $mapping = [
            'Secretaria de Hacienda'       => 'Secretario de Despacho',
            'Arquitecto de Datos'          => 'Profesional Universitario',
            'Auxiliar Administrativo'      => 'Auxiliar Administrativo',
            'Coordinadora de Salud Pública' => 'Profesional Universitario',
            'Inspector de Educación'        => 'Profesional Universitario',
            'Jefe de Obras Públicas'        => 'Secretario de Despacho',
            'Secretaria del Despacho'      => 'Auxiliar Administrativo',
            'Contador Municipal'           => 'Contador Municipal',
            'Bióloga Ambiental'            => 'Profesional Universitario',
            'Coordinadora TIC'             => 'Profesional Universitario',
        ];

        $types = ['carrera', 'provisional', 'libre_nombramiento'];

        $officials = Official::all();
        
        // Asegurar una base mínima de 10 funcionarios para pruebas
        if ($officials->count() < 10) {
             $baseDept = Office::first();
             if (!$baseDept) $baseDept = Office::create(['name' => 'Secretaría General']);
             
             $basePos = Position::first();
             
             $names = ['Carlos Perez', 'Ana Martinez', 'Luis Rodriguez', 'Marta Gomez', 'Jose Sanchez', 'Elena Diaz', 'Pedro Lopez', 'Sofia Ruiz'];
             foreach ($names as $idx => $name) {
                 Official::firstOrCreate(
                    ['document_number' => "200000$idx"],
                    [
                        'full_name' => $name,
                        'office_id' => $baseDept->id,
                        'position_id' => $basePos->id,
                        'position' => $basePos->name,
                        'employment_type' => $types[array_rand($types)],
                        'employment_status' => 'activo',
                        'entry_date' => now()->subMonths(rand(1, 24)),
                    ]
                 );
             }
             $officials = Official::all();
        }

        foreach ($officials as $off) {
            $mappedPosition = $mapping[$off->position] ?? 'Auxiliar Administrativo';
            $pos = Position::where('name', $mappedPosition)->first();
            
            $off->update([
                'position_id' => $pos->id,
                'employment_type' => ($pos->level === 'asesor' || $pos->level === 'directivo') ? 'libre_nombramiento' : $types[array_rand($types)],
                'entry_date' => now()->subYears(rand(1, 5))->subMonths(rand(1, 11)),
                'sigep_updated' => rand(0, 1),
            ]);
        }
        
        
        $allOfficials = Official::all();

        // 3. Nóminas y Salarios (Periodos y Liquidaciones de Pruebas)
        $periodsList = [
            ['month' => 1, 'year' => 2024, 'start_date' => '2024-01-01', 'end_date' => '2024-01-31', 'status' => 'paid', 'type' => 'employees'],
            ['month' => 1, 'year' => 2024, 'start_date' => '2024-01-01', 'end_date' => '2024-01-31', 'status' => 'paid', 'type' => 'contractors'],
            ['month' => 2, 'year' => 2024, 'start_date' => '2024-02-01', 'end_date' => '2024-02-29', 'status' => 'paid', 'type' => 'employees'],
            ['month' => 2, 'year' => 2024, 'start_date' => '2024-02-01', 'end_date' => '2024-02-29', 'status' => 'paid', 'type' => 'contractors'],
            ['month' => 3, 'year' => 2024, 'start_date' => '2024-03-01', 'end_date' => '2024-03-31', 'status' => 'draft', 'type' => 'employees'],
        ];

        foreach ($periodsList as $pData) {
            $period = PayrollPeriod::firstOrCreate(
                ['month' => $pData['month'], 'year' => $pData['year'], 'type' => $pData['type']], 
                $pData
            );

            // Generar items para este periodo
            $totalMonth = 0;
            
            if ($pData['type'] === 'employees') {
                $planta = Official::where('employment_type', '!=', 'contratista')->get();
                foreach($planta as $emp) {
                    $base = $emp->position_rel->base_salary ?? 2000000;
                    $health = $base * 0.04;
                    $pension = $base * 0.04;
                    $net = $base - $health - $pension;
                    
                    \App\Models\PayrollItem::create([
                        'payroll_period_id' => $period->id,
                        'official_id' => $emp->id,
                        'salary_base' => $base,
                        'deductions_health' => $health,
                        'deductions_pension' => $pension,
                        'net_pay' => $net,
                        'details' => ['type' => 'planta', 'gross' => $base]
                    ]);
                    $totalMonth += $net;
                }
            }
            $period->update(['total_amount' => $totalMonth]);
        }

        // 3. Plan Institucional de Capacitación (PIC)
        $trainings = [
            ['title' => 'Taller de Servicio al Ciudadano', 'type' => 'tecnica',     'scheduled_date' => '2024-05-15', 'hours' => 8,  'status' => 'planned'],
            ['title' => 'Seminario SIGEP II y DAFP',     'type' => 'induccion',   'scheduled_date' => '2024-06-20', 'hours' => 4,  'status' => 'planned'],
            ['title' => 'Jornada de Integración Familiar', 'type' => 'bienestar',   'scheduled_date' => '2024-07-05', 'hours' => 12, 'status' => 'planned'],
        ];

        foreach ($trainings as $t) {
            TrainingProgram::firstOrCreate(['title' => $t['title']], $t);
        }

        // 4. Registros SST
        if ($officials->count() >= 3) {
            $sst = [
                ['official_id' => $officials[0]->id, 'type' => 'examen_periodico', 'record_date' => '2024-02-10', 'findings' => 'Apto sin restricciones'],
                ['official_id' => $officials[1]->id, 'type' => 'examen_periodico', 'record_date' => '2024-02-12', 'findings' => 'Apto con recomendaciones ergonómicas'],
                ['official_id' => $officials[2]->id, 'type' => 'examen_ingreso',   'record_date' => '2024-03-01', 'findings' => 'Ingreso satisfactorio'],
            ];
            foreach ($sst as $s) {
                SstRecord::create($s);
            }
        }

        // 5. Comités de Ley
        $copasst = HrCommittee::firstOrCreate(['name' => 'COPASST'], [
            'name' => 'COPASST',
            'description' => 'Comité Paritario de Seguridad y Salud en el Trabajo',
            'valid_from' => '2024-01-01',
            'valid_to' => '2025-12-31'
        ]);

        $convivencia = HrCommittee::firstOrCreate(['name' => 'Comité de Convivencia Laboral'], [
            'name' => 'Comité de Convivencia Laboral',
            'description' => 'Encargado de prevenir el acoso laboral y promover la armonía',
            'valid_from' => '2024-01-01',
            'valid_to' => '2025-12-31'
        ]);

        // Miembros de comités
        if ($officials->count() >= 4) {
             HrCommitteeMember::create(['hr_committee_id' => $copasst->id, 'official_id' => $officials[0]->id, 'role' => 'Presidente', 'appointment_date' => '2024-01-10']);
             HrCommitteeMember::create(['hr_committee_id' => $copasst->id, 'official_id' => $officials[1]->id, 'role' => 'Secretario', 'appointment_date' => '2024-01-10']);
             HrCommitteeMember::create(['hr_committee_id' => $convivencia->id, 'official_id' => $officials[2]->id, 'role' => 'Presidente', 'appointment_date' => '2024-01-15']);
             HrCommitteeMember::create(['hr_committee_id' => $convivencia->id, 'official_id' => $officials[3]->id, 'role' => 'Secretario', 'appointment_date' => '2024-01-15']);
        }

        // 6. Reuniones
        HrMeeting::create([
            'hr_committee_id' => $copasst->id,
            'title' => 'Sesión Ordinaria de Abril',
            'meeting_date' => '2016-04-10',
            'location' => 'Sala de Juntas',
            'agenda' => 'Revisión de dotación, inspección de extintores.',
            'minutes_content' => 'Se determinó que el 90% de la dotación ha sido entregada...',
            'status' => 'held'
        ]);

        // 7. Situaciones Administrativas
        if ($officials->count() >= 2) {
            HrAdministrativeSituation::create([
                'official_id' => $officials[0]->id,
                'type' => 'vacaciones',
                'start_date' => '2024-04-01',
                'end_date' => '2024-04-15',
                'reason' => 'Periodo ordinario 2023',
                'status' => 'aprobado'
            ]);
            HrAdministrativeSituation::create([
                'official_id' => $officials[1]->id,
                'type' => 'incapacidad',
                'start_date' => '2024-04-10',
                'end_date' => '2024-04-12',
                'reason' => 'Virosis fuerte',
                'status' => 'aprobado'
            ]);
        }

        // 8. Contratistas y Nómina (Estampillas)
        $office = Office::first();
        $officeId = $office ? $office->id : null;

        $contractorData = [
            'full_name' => 'Juan David Expert',
            'document_number' => '1098765432',
            'email' => 'juan.expert@municipio.gov.co',
            'position' => 'Consultor Externo TIC',
            'office_id' => $officeId,
            'employment_type' => 'contratista',
            'employment_status' => 'activo'
        ];
        
        $contractor = Official::firstOrCreate(['document_number' => '1098765432'], $contractorData);
        
        $contract = ContractorContract::create([
            'official_id' => $contractor->id,
            'contract_number' => 'C-2024-089',
            'cdp' => 'CDP-2024-552',
            'rp' => 'RP-2024-110',
            'rubro' => '2.1.2.01.01.003 - Servicios Profesionales',
            'object' => 'Prestación de servicios para la implementación del módulo de Talento Humano',
            'supervisor_name' => 'Dr. Mauricio Alcalde',
            'supervisor_position' => 'Secretario de Hacienda',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'monthly_payment_value' => 6000000,
            'total_contract_value' => 72000000,
            'is_active' => true
        ]);

        // Deducciones estándar (Estampillas Colombia)
        ContractDeductionRule::create(['contract_id' => $contract->id, 'name' => 'Estampilla Pro-Cultura (1.5%)', 'type' => 'percentage', 'value' => 1.5]);
        ContractDeductionRule::create(['contract_id' => $contract->id, 'name' => 'Estampilla Adulto Mayor (2.0%)', 'type' => 'percentage', 'value' => 2.0]);
        ContractDeductionRule::create(['contract_id' => $contract->id, 'name' => 'ReteICA (0.7%)', 'type' => 'percentage', 'value' => 0.7]);
        ContractDeductionRule::create(['contract_id' => $contract->id, 'name' => 'Póliza de Garantía', 'type' => 'fixed', 'value' => 45000]);

        // Otro contratista para pruebas masivas
        $contractor2 = Official::firstOrCreate(['document_number' => '12345678'], [
            'full_name' => 'Maria Logistica',
            'document_number' => '12345678',
            'email' => 'maria.log@municipio.gov.co',
            'position' => 'Auxiliar de Logistica',
            'office_id' => $officeId,
            'employment_type' => 'contratista',
            'employment_status' => 'activo'
        ]);

        $contract2 = ContractorContract::create([
            'official_id' => $contractor2->id,
            'contract_number' => 'C-2024-102',
            'cdp' => 'CDP-2024-555',
            'rp' => 'RP-2024-120',
            'rubro' => '2.1.2.01.01.004 - Apoyo a la Gestión',
            'object' => 'Apoyo operativo a la secretaría de Hacienda',
            'supervisor_name' => 'Dra. Elena Secretaria',
            'supervisor_position' => 'Secretaria General',
            'start_date' => '2024-02-01',
            'end_date' => '2024-07-31',
            'monthly_payment_value' => 3500000,
            'total_contract_value' => 21000000,
            'is_active' => true
        ]);
        
        ContractDeductionRule::create(['contract_id' => $contract2->id, 'name' => 'Estampilla Pro-Cultura', 'type' => 'percentage', 'value' => 1.5]);
        ContractDeductionRule::create(['contract_id' => $contract2->id, 'name' => 'Estampilla Adulto Mayor', 'type' => 'percentage', 'value' => 2.0]);

        // 9. Generar pagos históricos para los contratistas creados
        $contractorPeriods = PayrollPeriod::where('type', 'contractors')->get();
        foreach ($contractorPeriods as $cp) {
             $cTotal = 0;
             $contracts = ContractorContract::all();
             foreach ($contracts as $c) {
                 $base = $c->monthly_payment_value;
                 // Deducciones simplificadas para el seeder
                 $deduct = $base * 0.042; // Estampillas procultura (1.5) + adulto (2.0) + reteica (0.7)
                 $net = $base - $deduct - 45000; // poliza

                 \App\Models\PayrollItem::create([
                     'payroll_period_id' => $cp->id,
                     'official_id' => $c->official_id,
                     'contractor_contract_id' => $c->id,
                     'salary_base' => $base,
                     'net_pay' => $net,
                     'details' => [
                         'contract_number' => $c->contract_number,
                         'gross' => $base,
                         'deductions' => ['Estampillas' => $deduct, 'Poliza' => 45000]
                     ]
                 ]);
                 $cTotal += $net;
             }
             $cp->update(['total_amount' => $cTotal]);
        }
    }
}
