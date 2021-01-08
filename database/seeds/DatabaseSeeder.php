<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * php artisan db:seed --class=CountrysTableSeeder
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(CountrysTableSeeder::class);
        $this->call(CitiesTableSeeder::class);
        $this->call(StatesTableSeeder::class);
        $this->call(SpecialitiesTableSeeder::class);
        $this->call(SettingsTableSeeder::class);
        $this->call(SystemSettingsTableSeeder::class);
        $this->call(TimeSchedulesTableSeeder::class);
        $this->call(ServicesTableSeeder::class);
        $this->call(AppointmentsTableSeeder::class);

    }

    private function CallSeeder($table)
	{
		// if ($this->command->confirm( "Confirm To Seed $table" )) {
			$this->call($table);
			$this->command->warn($table." Seeder Success\n");
		// }
	}

	private function TruncateTable($table)
	{
		// if ($this->command->confirm( "Confirm To Truncate $table" )) {
			DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Schema::disableForeignKeyConstraints();
			$table::truncate();
			DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Schema::enableForeignKeyConstraints();
			$this->command->warn($table." Truncated");
		// }
	}
}
