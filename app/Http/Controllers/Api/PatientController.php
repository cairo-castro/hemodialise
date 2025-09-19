<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'birth_date' => 'required|date_format:Y-m-d',
        ]);

        $patient = Patient::where('full_name', $request->full_name)
            ->where('birth_date', $request->birth_date)
            ->where('active', true)
            ->first();

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

        return response()->json(['found' => false]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'full_name' => 'required|string|max:255',
                'birth_date' => 'required|date_format:Y-m-d',
                'medical_record' => 'required|string|max:255|unique:patients',
                'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
                'allergies' => 'nullable|string',
                'observations' => 'nullable|string',
            ]);

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
