<?php

namespace Santwer\DeveloperAnalyst\Dashboard\Http\Models;

use Illuminate\Database\Eloquent\Model;

class Developer extends Model
{
	protected $fillable = [
		'name', 'mail'
	];
	public function getConnectionName()
	{
		return config('developerAnalyst.connection');
	}
	public function getTable()
	{
		return config('developerAnalyst.database_table_prefix').parent::getTable(); // TODO: Change the autogenerated stub
	}
	protected $casts = [
		'updated_at' => 'datetime',
		'created_at' => 'datetime',
	];

	public function files()
	{
		return $this->belongsToMany(Files::class, config('developerAnalyst.connection').'developer_dev_files',
		'developer_id', 'file_id');
	}
}
