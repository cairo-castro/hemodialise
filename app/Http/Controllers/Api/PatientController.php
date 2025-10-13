<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    /**
     * Lista pacientes com paginação e busca otimizada
     * Retorna os 100 últimos pacientes por padrão
     * Filtra automaticamente pela unidade do usuário autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = $request->input('per_page', 100);
        $search = $request->input('search', '');
        
        $query = Patient::where('active', true);
        
        // SEGURANÇA: Filtra apenas pacientes da unidade do usuário
        // Admin pode ver todos, outros usuários apenas da sua unidade
        if (!$user->isAdmin() && $user->unit_id) {
            $query->where('unit_id', $user->unit_id);
        }
        
        // Se houver busca, aplica filtros otimizados
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('medical_record', 'LIKE', "%{$search}%")
                  ->orWhere('blood_type', 'LIKE', "%{$search}%");
            });
            // Quando há busca, ordena por relevância (nome primeiro)
            $query->orderByRaw("CASE WHEN full_name LIKE ? THEN 0 ELSE 1 END", ["{$search}%"])
                  ->orderBy('full_name', 'asc');
        } else {
            // Sem busca, retorna os mais recentes primeiro
            $query->orderBy('created_at', 'desc');
        }
        
        $patients = $query->limit($perPage)
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'full_name' => $patient->full_name,
                    'birth_date' => $patient->birth_date->format('Y-m-d'),
                    'medical_record' => $patient->medical_record,
                    'blood_type' => $patient->blood_type,
                    'age' => $patient->age,
                    'allergies' => $patient->allergies,
                    'observations' => $patient->observations,
                ];
            });

        return response()->json([
            'success' => true,
            'patients' => $patients,
            'count' => $patients->count(),
            'per_page' => $perPage,
            'unit_id' => $user->unit_id ?? null
        ]);
    }
    
    /**
     * Busca inteligente de pacientes com debounce
     * Otimizada para grandes volumes de dados
     * Filtra automaticamente pela unidade do usuário autenticado
     */
    public function quickSearch(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:255'
        ]);
        
        $user = auth()->user();
        $searchTerm = $request->input('query');
        $limit = $request->input('limit', 20); // Apenas 20 resultados na busca rápida
        
        $query = Patient::where('active', true);
        
        // SEGURANÇA: Filtra apenas pacientes da unidade do usuário
        // Admin pode ver todos, outros usuários apenas da sua unidade
        if (!$user->isAdmin() && $user->unit_id) {
            $query->where('unit_id', $user->unit_id);
        }
        
        $patients = $query->where(function ($q) use ($searchTerm) {
                // Busca otimizada com prioridade
                $q->where('full_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('medical_record', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('blood_type', 'LIKE', "%{$searchTerm}%");
            })
            // Ordena por relevância: nomes que começam com o termo vêm primeiro
            ->orderByRaw("CASE 
                WHEN full_name LIKE ? THEN 1 
                WHEN medical_record LIKE ? THEN 2 
                ELSE 3 
            END", ["{$searchTerm}%", "{$searchTerm}%"])
            ->orderBy('full_name', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'full_name' => $patient->full_name,
                    'birth_date' => $patient->birth_date->format('Y-m-d'),
                    'medical_record' => $patient->medical_record,
                    'blood_type' => $patient->blood_type,
                    'age' => $patient->age,
                ];
            });

        return response()->json([
            'success' => true,
            'patients' => $patients,
            'count' => $patients->count(),
            'query' => $searchTerm,
            'unit_id' => $user->unit_id ?? null
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'birth_date' => 'required|date_format:Y-m-d',
        ]);

        $user = auth()->user();

        // SEGURANÇA: First, try to find existing patient in the user's unit
        $query = Patient::where('full_name', $request->full_name)
            ->where('birth_date', $request->birth_date)
            ->where('active', true);
            
        // Filtra pela unidade do usuário (exceto admin)
        if (!$user->isAdmin() && $user->unit_id) {
            $query->where('unit_id', $user->unit_id);
        }
        
        $patient = $query->first();

        if ($patient) {
            return response()->json([
                'found' => true,
                'patient' => [
                    'id' => $patient->id,
                    'full_name' => $patient->full_name,
                    'birth_date' => $patient->birth_date->format('Y-m-d'),
                    'medical_record' => $patient->medical_record,
                    'blood_type' => $patient->blood_type,
                    'age' => $patient->age,
                ]
            ]);
        }

        // Patient not found, create new one automatically
        try {
            // Generate medical record number
            $medicalRecord = 'PAC' . str_pad(Patient::count() + 1, 6, '0', STR_PAD_LEFT);

            $newPatient = Patient::create([
                'full_name' => $request->full_name,
                'birth_date' => $request->birth_date,
                'medical_record' => $medicalRecord,
                'active' => true,
                'unit_id' => $user->unit_id, // SEGURANÇA: Associa à unidade do usuário
            ]);

            return response()->json([
                'found' => true,
                'created' => true,
                'patient' => [
                    'id' => $newPatient->id,
                    'full_name' => $newPatient->full_name,
                    'birth_date' => $newPatient->birth_date->format('Y-m-d'),
                    'medical_record' => $newPatient->medical_record,
                    'blood_type' => $newPatient->blood_type,
                    'age' => $newPatient->age,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'found' => false,
                'error' => 'Erro ao cadastrar paciente automaticamente.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'birth_date' => 'required|date_format:Y-m-d',
                'medical_record' => 'required|string|max:255|unique:patients',
                'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
                'allergies' => 'nullable|string',
                'observations' => 'nullable|string',
            ]);

            // SEGURANÇA: Associa automaticamente o paciente à unidade do usuário
            $validated['unit_id'] = $user->unit_id;

            $patient = Patient::create($validated);

            return response()->json([
                'success' => true,
                'patient' => [
                    'id' => $patient->id,
                    'full_name' => $patient->full_name,
                    'birth_date' => $patient->birth_date->format('Y-m-d'),
                    'medical_record' => $patient->medical_record,
                    'blood_type' => $patient->blood_type,
                    'age' => $patient->age,
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'patients_name_birth_unique')) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'patient' => ['Já existe um paciente com este nome e data de nascimento.']
                    ]
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }
}
