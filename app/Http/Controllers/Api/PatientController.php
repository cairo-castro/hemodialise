<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Enums\PatientStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Enum;

class PatientController extends Controller
{
    /**
     * Lista pacientes com paginação e busca otimizada
     * Retorna os 100 últimos pacientes por padrão
     * Filtra automaticamente pela unidade do usuário autenticado
     * Por padrão, retorna apenas pacientes que podem ter sessões (status: ativo)
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = $request->input('per_page', 100);
        $search = $request->input('search', '');
        $includeInactive = $request->input('include_inactive', false);

        // Por padrão, apenas pacientes que podem ter sessões
        if ($includeInactive) {
            $query = Patient::query();
        } else {
            $query = Patient::where('status', PatientStatus::ATIVO->value);
        }

        // Aplicar filtro de unidade do middleware
        $scopedUnitId = $request->get('scoped_unit_id');
        if ($scopedUnitId !== null) {
            $query->where('unit_id', $scopedUnitId);
        }

        // Se houver busca, aplica filtros otimizados
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                  ->orWhere('blood_group', 'LIKE', "%{$search}%");
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
                    'blood_type' => $patient->blood_type,
                    'age' => $patient->age,
                    'allergies' => $patient->allergies,
                    'observations' => $patient->observations,
                    'status' => $patient->status->value,
                    'status_label' => $patient->status->label(),
                    'status_color' => $patient->status->color(),
                    'can_have_sessions' => $patient->canHaveSessions(),
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
     * Por padrão, retorna apenas pacientes que podem ter sessões (status: ativo)
     */
    public function quickSearch(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:255'
        ]);

        $user = auth()->user();
        $searchTerm = $request->input('query');
        $limit = $request->input('limit', 20); // Apenas 20 resultados na busca rápida

        // Por padrão, apenas pacientes que podem ter sessões
        $query = Patient::where('status', PatientStatus::ATIVO->value);

        // Aplicar filtro de unidade do middleware
        $scopedUnitId = $request->get('scoped_unit_id');
        if ($scopedUnitId !== null) {
            $query->where('unit_id', $scopedUnitId);
        }

        $patients = $query->where(function ($q) use ($searchTerm) {
                // Busca otimizada com prioridade
                $q->where('full_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('blood_group', 'LIKE', "%{$searchTerm}%");
            })
            // Ordena por relevância: nomes que começam com o termo vêm primeiro
            ->orderByRaw("CASE
                WHEN full_name LIKE ? THEN 1
                ELSE 2
            END", ["{$searchTerm}%"])
            ->orderBy('full_name', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'full_name' => $patient->full_name,
                    'birth_date' => $patient->birth_date->format('Y-m-d'),
                    'blood_type' => $patient->blood_type,
                    'age' => $patient->age,
                    'status' => $patient->status->value,
                    'status_label' => $patient->status->label(),
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

    /**
     * Retorna um paciente específico pelo ID
     * Filtra automaticamente pela unidade do usuário autenticado
     */
    public function show(Request $request, $id): JsonResponse
    {
        $query = Patient::where('id', $id);

        // Aplicar filtro de unidade do middleware
        $scopedUnitId = $request->get('scoped_unit_id');
        if ($scopedUnitId !== null) {
            $query->where('unit_id', $scopedUnitId);
        }

        $patient = $query->with('safetyChecklists')->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Paciente não encontrado ou não pertence à sua unidade.'
            ], 404);
        }

        // Conta os checklists do paciente
        $checklistsCount = $patient->safetyChecklists()->count();

        return response()->json([
            'success' => true,
            'patient' => [
                'id' => $patient->id,
                'full_name' => $patient->full_name,
                'birth_date' => $patient->birth_date->format('Y-m-d'),
                'blood_type' => $patient->blood_type,
                'age' => $patient->age,
                'allergies' => $patient->allergies,
                'observations' => $patient->observations,
                'unit_id' => $patient->unit_id,
                'status' => $patient->status->value,
                'status_label' => $patient->status->label(),
                'status_color' => $patient->status->color(),
                'can_have_sessions' => $patient->canHaveSessions(),
                'checklists_count' => $checklistsCount,
            ]
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'birth_date' => 'required|date_format:Y-m-d',
        ]);

        // Aplicar filtro de unidade do middleware
        $scopedUnitId = $request->get('scoped_unit_id');

        // SEGURANÇA: Requer seleção de unidade antes de buscar/criar pacientes
        if ($scopedUnitId === null) {
            return response()->json([
                'found' => false,
                'error' => 'Selecione uma unidade antes de buscar pacientes.'
            ], 400);
        }

        // SEGURANÇA: First, try to find existing patient in the scoped unit
        // Busca apenas pacientes que podem ter sessões (status: ativo)
        $query = Patient::where('full_name', $request->full_name)
            ->where('birth_date', $request->birth_date)
            ->where('status', PatientStatus::ATIVO->value)
            ->where('unit_id', $scopedUnitId);

        $patient = $query->first();

        if ($patient) {
            return response()->json([
                'found' => true,
                'patient' => [
                    'id' => $patient->id,
                    'full_name' => $patient->full_name,
                    'birth_date' => $patient->birth_date->format('Y-m-d'),
                    'blood_type' => $patient->blood_type,
                    'age' => $patient->age,
                    'status' => $patient->status->value,
                    'status_label' => $patient->status->label(),
                ]
            ]);
        }

        // Patient not found, create new one automatically with status ATIVO
        try {
            $newPatient = Patient::create([
                'full_name' => $request->full_name,
                'birth_date' => $request->birth_date,
                'status' => PatientStatus::ATIVO->value,
                'active' => true, // Backward compatibility
                'unit_id' => $scopedUnitId, // SEGURANÇA: Associa à unidade selecionada pelo middleware
            ]);

            return response()->json([
                'found' => true,
                'created' => true,
                'patient' => [
                    'id' => $newPatient->id,
                    'full_name' => $newPatient->full_name,
                    'birth_date' => $newPatient->birth_date->format('Y-m-d'),
                    'blood_type' => $newPatient->blood_type,
                    'age' => $newPatient->age,
                    'status' => $newPatient->status->value,
                    'status_label' => $newPatient->status->label(),
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
            // Aplicar filtro de unidade do middleware
            $scopedUnitId = $request->get('scoped_unit_id');

            // SEGURANÇA: Requer seleção de unidade antes de criar pacientes
            if ($scopedUnitId === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selecione uma unidade antes de criar pacientes.'
                ], 400);
            }

            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'birth_date' => 'required|date_format:Y-m-d',
                'blood_group' => 'nullable|in:A,B,AB,O',
                'rh_factor' => 'nullable|in:+,-',
                'allergies' => 'nullable|string',
                'observations' => 'nullable|string',
                'status' => ['nullable', new Enum(PatientStatus::class)],
            ]);

            // SEGURANÇA: Associa automaticamente o paciente à unidade selecionada pelo middleware
            $validated['unit_id'] = $scopedUnitId;

            // Set default status if not provided
            if (!isset($validated['status'])) {
                $validated['status'] = PatientStatus::ATIVO->value;
            }

            // Set active field for backward compatibility
            $validated['active'] = ($validated['status'] === PatientStatus::ATIVO->value);

            $patient = Patient::create($validated);

            return response()->json([
                'success' => true,
                'patient' => [
                    'id' => $patient->id,
                    'full_name' => $patient->full_name,
                    'birth_date' => $patient->birth_date->format('Y-m-d'),
                    'blood_type' => $patient->blood_type,
                    'age' => $patient->age,
                    'status' => $patient->status->value,
                    'status_label' => $patient->status->label(),
                    'status_color' => $patient->status->color(),
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'patients_name_birth_unit_unique')) {
                return response()->json([
                    'success' => false,
                    'errors' => [
                        'patient' => ['Já existe um paciente com este nome e data de nascimento nesta unidade.']
                    ]
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor.'
            ], 500);
        }
    }

    /**
     * Ativa ou desativa um paciente (toggle entre ATIVO e INATIVO)
     * Mantido para compatibilidade com código existente
     */
    public function toggleActive(Request $request, $id): JsonResponse
    {
        try {
            $query = Patient::where('id', $id);

            // Aplicar filtro de unidade do middleware
            $scopedUnitId = $request->get('scoped_unit_id');
            if ($scopedUnitId !== null) {
                $query->where('unit_id', $scopedUnitId);
            }

            $patient = $query->first();

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paciente não encontrado ou não pertence à sua unidade.'
                ], 404);
            }

            // Alterna entre ATIVO e INATIVO
            if ($patient->status === PatientStatus::ATIVO) {
                $patient->status = PatientStatus::INATIVO;
                $patient->active = false;
            } else {
                $patient->status = PatientStatus::ATIVO;
                $patient->active = true;
            }
            $patient->save();

            return response()->json([
                'success' => true,
                'message' => $patient->status === PatientStatus::ATIVO ? 'Paciente ativado com sucesso.' : 'Paciente desativado com sucesso.',
                'patient' => [
                    'id' => $patient->id,
                    'status' => $patient->status->value,
                    'status_label' => $patient->status->label(),
                    'status_color' => $patient->status->color(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status do paciente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualiza o status de um paciente
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => ['required', new Enum(PatientStatus::class)],
            ]);

            $query = Patient::where('id', $id);

            // Aplicar filtro de unidade do middleware
            $scopedUnitId = $request->get('scoped_unit_id');
            if ($scopedUnitId !== null) {
                $query->where('unit_id', $scopedUnitId);
            }

            $patient = $query->first();

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paciente não encontrado ou não pertence à sua unidade.'
                ], 404);
            }

            // Valida se é uma mudança válida
            $newStatus = PatientStatus::from($validated['status']);

            // Não permite reverter status terminal (alta, óbito)
            if ($patient->isTerminal() && $newStatus !== $patient->status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível alterar o status de um paciente com alta ou óbito registrado.'
                ], 422);
            }

            $patient->status = $newStatus;
            $patient->active = ($newStatus === PatientStatus::ATIVO);
            $patient->save();

            return response()->json([
                'success' => true,
                'message' => 'Status do paciente atualizado com sucesso.',
                'patient' => [
                    'id' => $patient->id,
                    'status' => $patient->status->value,
                    'status_label' => $patient->status->label(),
                    'status_color' => $patient->status->color(),
                    'can_have_sessions' => $patient->canHaveSessions(),
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar status do paciente.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
