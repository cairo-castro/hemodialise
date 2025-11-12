<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Change columns from TINYINT to VARCHAR temporarily to hold string values
        DB::statement("ALTER TABLE `cleaning_controls` MODIFY COLUMN `hd_machine_cleaning` VARCHAR(10) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE `cleaning_controls` MODIFY COLUMN `osmosis_cleaning` VARCHAR(10) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE `cleaning_controls` MODIFY COLUMN `serum_support_cleaning` VARCHAR(10) NULL DEFAULT NULL");

        // Step 2: Convert existing boolean data (0/1) to ENUM format
        // 1 -> 'C' (Conforme), 0 -> 'NC' (Não Conforme), NULL stays NULL
        DB::statement("UPDATE `cleaning_controls` SET `hd_machine_cleaning` = CASE WHEN `hd_machine_cleaning` = '1' THEN 'C' WHEN `hd_machine_cleaning` = '0' THEN 'NC' ELSE `hd_machine_cleaning` END WHERE `hd_machine_cleaning` IS NOT NULL");
        DB::statement("UPDATE `cleaning_controls` SET `osmosis_cleaning` = CASE WHEN `osmosis_cleaning` = '1' THEN 'C' WHEN `osmosis_cleaning` = '0' THEN 'NC' ELSE `osmosis_cleaning` END WHERE `osmosis_cleaning` IS NOT NULL");
        DB::statement("UPDATE `cleaning_controls` SET `serum_support_cleaning` = CASE WHEN `serum_support_cleaning` = '1' THEN 'C' WHEN `serum_support_cleaning` = '0' THEN 'NC' ELSE `serum_support_cleaning` END WHERE `serum_support_cleaning` IS NOT NULL");

        // Step 3: Finally change columns from VARCHAR to ENUM
        DB::statement("ALTER TABLE `cleaning_controls` MODIFY COLUMN `hd_machine_cleaning` ENUM('C', 'NC', 'NA') NULL DEFAULT NULL");
        DB::statement("ALTER TABLE `cleaning_controls` MODIFY COLUMN `osmosis_cleaning` ENUM('C', 'NC', 'NA') NULL DEFAULT NULL");
        DB::statement("ALTER TABLE `cleaning_controls` MODIFY COLUMN `serum_support_cleaning` ENUM('C', 'NC', 'NA') NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert columns back to boolean
        DB::statement("ALTER TABLE `cleaning_controls` MODIFY COLUMN `hd_machine_cleaning` TINYINT(1) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE `cleaning_controls` MODIFY COLUMN `osmosis_cleaning` TINYINT(1) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE `cleaning_controls` MODIFY COLUMN `serum_support_cleaning` TINYINT(1) NULL DEFAULT NULL");
    }
};
