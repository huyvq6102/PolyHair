<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sqlFile = base_path('../polybarber.sql');
        
        if (!File::exists($sqlFile)) {
            $this->command->warn('File polybarber.sql not found. Skipping data import.');
            return;
        }

        $this->command->info('Importing data from polybarber.sql...');

        // Read SQL file
        $sql = File::get($sqlFile);
        
        // Remove comments and empty lines
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Split by semicolon
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($statement) => !empty($statement) && !preg_match('/^(SET|START|COMMIT|LOCK|UNLOCK)/i', $statement)
        );

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $progressBar = $this->command->getOutput()->createProgressBar(count($statements));
        $progressBar->start();

        foreach ($statements as $statement) {
            if (empty(trim($statement))) {
                continue;
            }

            try {
                // Skip CREATE TABLE and ALTER TABLE statements as tables already exist
                if (preg_match('/^(CREATE|ALTER|DROP)\s+TABLE/i', $statement)) {
                    $progressBar->advance();
                    continue;
                }

                // Only execute INSERT statements
                if (preg_match('/^INSERT\s+INTO/i', $statement)) {
                    DB::statement($statement);
                }
            } catch (\Exception $e) {
                // Skip errors for duplicate entries or other issues
                $this->command->warn("\nSkipping statement: " . substr($statement, 0, 50) . '...');
            }
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->command->newLine();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info('Data import completed!');
    }
}
