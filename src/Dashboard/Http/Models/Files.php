<?php

namespace Santwer\DeveloperAnalyst\Dashboard\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
	protected $fillable = ['filepath', 'translation_mistakes', 'html_mistakes', 'batch_date', 'updated_at', 'created_at'];

	protected $table = 'dev_files';
	public function getConnectionName()
	{
		return config('developerAnalyst.connection');
	}
	public function getTable()
	{
		return config('developerAnalyst.database_table_prefix').parent::getTable(); // TODO: Change the autogenerated stub
	}
	protected $casts = [
		'batch_date' => 'date',
	];
}