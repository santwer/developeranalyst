<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
	public function up(): void
	{
		Schema::connection(config('developerAnalyst.connection'))
			->create(config('developerAnalyst.database_table_prefix').'developer_dev_files', function (Blueprint $table) {
			$table->bigInteger('developer_id');
			$table->bigInteger('file_id');
		});
	}

	public function down(): void
	{
		Schema::connection(config('developerAnalyst.connection'))
			->dropIfExists(config('developerAnalyst.database_table_prefix').'developer_dev_files');
	}
};
