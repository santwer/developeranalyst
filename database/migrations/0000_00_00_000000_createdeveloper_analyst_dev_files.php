<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
	public function up(): void
	{
		Schema::connection(config('developerAnalyst.connection'))
			->create(config('developerAnalyst.database_table_prefix').'dev_files', function (Blueprint $table) {
				$table->id();
				$table->longText('filepath');
				$table->integer('translation_mistakes');
				$table->integer('html_mistakes');
				$table->date('batch_date');
				$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::connection(config('developerAnalyst.connection'))
			->dropIfExists(config('developerAnalyst.database_table_prefix').'dev_files');
	}
};
