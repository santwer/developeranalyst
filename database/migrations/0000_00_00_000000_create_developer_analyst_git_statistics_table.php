<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
	public function up(): void
	{
		Schema::connection(config('developerAnalyst.connection'))
			->create(config('developerAnalyst.database_table_prefix').'git_statistics', function (Blueprint $table) {
			$table->id();
			$table->bigInteger('user_id')->index();
			$table->date('date');
			$table->integer('commits')->default(0);
			$table->integer('files')->default(0);
			$table->integer('lines_of_code')->default(0);


			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::connection(config('developerAnalyst.connection'))->dropIfExists(config('developerAnalyst.database_table_prefix').'git_statistics');
	}
};
