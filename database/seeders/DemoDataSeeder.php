<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;
use App\Models\Machine;
use App\Models\Patient;
use App\Models\SafetyChecklist;
use App\Models\User;
use App\Enums\PatientStatus;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates demo data for all 25 units with machines, patients, and checklists
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        // Get all units
        $units = Unit::all();

        if ($units->isEmpty()) {
            $this->command->error('No units found! Run UnitsSeeder first.');
            return;
        }

        $this->command->info('Creating demo data for ' . $units->count() . ' units...');

        // Get a user for each unit (or create one if needed)
        $defaultUser = User::first();

        if (!$defaultUser) {
            $this->command->error('No users found! Run UserSeeder first.');
            return;
        }

        foreach ($units as $unit) {
            $this->command->info("Processing unit: {$unit->name}");

            // Get or create 5-15 machines per unit
            $existingMachines = Machine::where('unit_id', $unit->id)->get();

            if ($existingMachines->count() > 0) {
                $machines = $existingMachines->all();
                $this->command->warn("  - Unit already has {$existingMachines->count()} machines, skipping machine creation");
            } else {
                $machineCount = rand(5, 15);
                $machines = [];

                for ($i = 1; $i <= $machineCount; $i++) {
                    $machine = Machine::create([
                        'name' => "Máquina {$i}",
                        'identifier' => "{$unit->code}-M{$i}",
                        'description' => $faker->sentence(),
                        'active' => $faker->boolean(90), // 90% chance of being active
                        'unit_id' => $unit->id,
                        'status' => $faker->randomElement(['available', 'reserved', 'occupied', 'maintenance']),
                    ]);

                    $machines[] = $machine;
                }
                $this->command->info("  - Created {$machineCount} machines");
            }

            // Get or create 20-50 patients per unit
            $existingPatients = Patient::where('unit_id', $unit->id)->get();

            if ($existingPatients->count() > 0) {
                $patients = $existingPatients->all();
                $this->command->warn("  - Unit already has {$existingPatients->count()} patients, skipping patient creation");
            } else {
                $patientCount = rand(20, 50);
                $patients = [];

                for ($i = 0; $i < $patientCount; $i++) {
                    $bloodGroups = ['A', 'B', 'AB', 'O'];
                    $rhFactors = ['+', '-'];

                    // Realistic distribution:
                    // 75% ativo, 10% inativo, 8% transferido, 5% alta, 2% obito
                    $statuses = [
                        PatientStatus::ATIVO->value,
                        PatientStatus::INATIVO->value,
                        PatientStatus::TRANSFERIDO->value,
                        PatientStatus::ALTA->value,
                        PatientStatus::OBITO->value,
                    ];
                    $statusWeights = [75, 10, 8, 5, 2];
                    $status = $this->weightedRandom($statuses, $statusWeights);

                    $patient = Patient::create([
                        'full_name' => $faker->name(),
                        'birth_date' => $faker->dateTimeBetween('-80 years', '-18 years'),
                        'blood_group' => $faker->randomElement($bloodGroups),
                        'rh_factor' => $faker->randomElement($rhFactors),
                        'allergies' => $faker->boolean(30) ? $faker->words(3, true) : null,
                        'observations' => $faker->boolean(40) ? $faker->sentence() : null,
                        'status' => $status,
                        'active' => $status === PatientStatus::ATIVO->value, // Backward compatibility
                        'unit_id' => $unit->id,
                    ]);

                    $patients[] = $patient;
                }
                $this->command->info("  - Created {$patientCount} patients");
            }

            // Create checklists for the last 30 days
            // Only active patients can have sessions
            $activePatients = array_filter($patients, function($patient) {
                return $patient->status === PatientStatus::ATIVO->value;
            });

            // Skip if no active patients
            if (count($activePatients) === 0) {
                $this->command->warn("  - No active patients, skipping checklist creation");
                continue;
            }

            $checklistsPerDay = rand(5, 15);
            $checklistsCreated = 0;

            for ($day = 0; $day < 30; $day++) {
                $date = Carbon::now()->subDays($day);

                for ($c = 0; $c < $checklistsPerDay; $c++) {
                    try {
                        // Random shift based on time
                        $hour = rand(6, 22);
                        $shift = $hour >= 6 && $hour < 12 ? 'Matutino' : ($hour >= 12 && $hour < 18 ? 'Vespertino' : 'Noturno');

                        $sessionDate = $date->copy()->setHour($hour)->setMinute(rand(0, 59))->setSecond(rand(0, 59));

                        // Random phase
                        $phases = ['pre_dialysis', 'during_session', 'post_dialysis', 'completed'];
                        $phaseWeights = [10, 20, 20, 50]; // More completed checklists
                        $randomPhase = $this->weightedRandom($phases, $phaseWeights);

                        $checklist = SafetyChecklist::create([
                        'patient_id' => $faker->randomElement($activePatients)->id,
                        'machine_id' => $faker->randomElement($machines)->id,
                        'unit_id' => $unit->id,
                        'user_id' => $defaultUser->id,
                        'session_date' => $sessionDate,
                        'shift' => $shift,
                        'current_phase' => $randomPhase,

                        // Pre-dialysis checks (always filled if phase >= pre_dialise)
                        'machine_disinfected' => true,
                        'capillary_lines_identified' => true,
                        'reagent_test_performed' => true,
                        'pressure_sensors_verified' => true,
                        'air_bubble_detector_verified' => true,
                        'patient_identification_confirmed' => true,
                        'vascular_access_evaluated' => true,
                        'av_fistula_arm_washed' => true,
                        'patient_weighed' => true,
                        'vital_signs_checked' => true,
                        'medications_reviewed' => true,
                        'dialyzer_membrane_checked' => true,
                        'equipment_functioning_verified' => true,

                        'pre_dialysis_started_at' => $sessionDate,
                        'pre_dialysis_completed_at' => $sessionDate->copy()->addMinutes(rand(10, 20)),

                        // During session (filled if phase >= during_session)
                        'dialysis_parameters_verified' => in_array($randomPhase, ['during_session', 'post_dialysis', 'completed']) ? true : false,
                        'heparin_double_checked' => in_array($randomPhase, ['during_session', 'post_dialysis', 'completed']) ? true : false,
                        'antisepsis_performed' => in_array($randomPhase, ['during_session', 'post_dialysis', 'completed']) ? true : false,
                        'vascular_access_monitored' => in_array($randomPhase, ['during_session', 'post_dialysis', 'completed']) ? true : false,
                        'vital_signs_monitored_during' => in_array($randomPhase, ['during_session', 'post_dialysis', 'completed']) ? true : false,
                        'patient_comfort_assessed' => in_array($randomPhase, ['during_session', 'post_dialysis', 'completed']) ? true : false,
                        'fluid_balance_monitored' => in_array($randomPhase, ['during_session', 'post_dialysis', 'completed']) ? true : false,
                        'alarms_responded' => in_array($randomPhase, ['during_session', 'post_dialysis', 'completed']) ? true : false,

                        'during_session_started_at' => in_array($randomPhase, ['during_session', 'post_dialysis', 'completed']) ? $sessionDate->copy()->addMinutes(rand(20, 30)) : null,
                        'during_session_completed_at' => in_array($randomPhase, ['during_session', 'post_dialysis', 'completed']) ? $sessionDate->copy()->addHours(rand(3, 4)) : null,

                        // Post-dialysis (filled only if phase >= post_dialysis)
                        'session_completed_safely' => in_array($randomPhase, ['post_dialysis', 'completed']) ? true : false,
                        'vascular_access_secured' => in_array($randomPhase, ['post_dialysis', 'completed']) ? true : false,
                        'patient_vital_signs_stable' => in_array($randomPhase, ['post_dialysis', 'completed']) ? true : false,
                        'complications_assessed' => in_array($randomPhase, ['post_dialysis', 'completed']) ? true : false,
                        'equipment_cleaned' => in_array($randomPhase, ['post_dialysis', 'completed']) ? true : false,

                        'post_dialysis_started_at' => in_array($randomPhase, ['post_dialysis', 'completed']) ? $sessionDate->copy()->addHours(rand(3, 4)) : null,
                        'post_dialysis_completed_at' => in_array($randomPhase, ['post_dialysis', 'completed']) ? $sessionDate->copy()->addHours(rand(4, 5)) : null,

                        'is_interrupted' => false,
                        'observations' => $faker->boolean(20) ? $faker->sentence() : null,
                        'created_at' => $sessionDate,
                        'updated_at' => $sessionDate,
                        ]);
                        $checklistsCreated++;
                    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                        // Skip duplicate entries silently
                        continue;
                    }
                }
            }

            if (!isset($machineCount)) $machineCount = count($machines);
            if (!isset($patientCount)) $patientCount = count($patients);

            $this->command->info("  - Machines: {$machineCount}");
            $this->command->info("  - Patients: {$patientCount}");
            $this->command->info("  - Checklists created: {$checklistsCreated}");
        }

        $this->command->info('Demo data created successfully!');

        // Summary
        $totalMachines = Machine::count();
        $totalPatients = Patient::count();
        $totalChecklists = SafetyChecklist::count();

        // Patient status distribution
        $statusDistribution = [
            'Ativo' => Patient::where('status', PatientStatus::ATIVO->value)->count(),
            'Inativo' => Patient::where('status', PatientStatus::INATIVO->value)->count(),
            'Transferido' => Patient::where('status', PatientStatus::TRANSFERIDO->value)->count(),
            'Alta' => Patient::where('status', PatientStatus::ALTA->value)->count(),
            'Óbito' => Patient::where('status', PatientStatus::OBITO->value)->count(),
        ];

        $this->command->info("\n=== SUMMARY ===");
        $this->command->info("Total Units: {$units->count()}");
        $this->command->info("Total Machines: {$totalMachines}");
        $this->command->info("Total Patients: {$totalPatients}");
        $this->command->info("\nPatient Status Distribution:");
        foreach ($statusDistribution as $status => $count) {
            $percentage = $totalPatients > 0 ? round(($count / $totalPatients) * 100, 1) : 0;
            $this->command->info("  - {$status}: {$count} ({$percentage}%)");
        }
        $this->command->info("\nTotal Checklists: {$totalChecklists}");
    }

    /**
     * Weighted random selection
     */
    private function weightedRandom($values, $weights)
    {
        $total = array_sum($weights);
        $rand = rand(1, $total);

        $sum = 0;
        foreach ($values as $i => $value) {
            $sum += $weights[$i];
            if ($rand <= $sum) {
                return $value;
            }
        }

        return $values[0];
    }
}
