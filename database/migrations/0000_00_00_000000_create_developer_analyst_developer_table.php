<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
	public function up(): void
	{
		Schema::connection(config('developerAnalyst.connection'))
			->create(config('developerAnalyst.database_table_prefix').'developers', function (Blueprint $table) {
			$table->id();
			$table->string('name')->nullable();
			$table->string('mail')->index();

			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::connection(config('developerAnalyst.connection'))
			->dropIfExists(config('developerAnalyst.database_table_prefix').'developers');
	}
};
