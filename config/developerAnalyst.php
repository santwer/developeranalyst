<?php
return [
	'dashboard'       => [
		'path'       => env('DEVELOPER_ANALYST_DASHBOARD_PATH', 'dev/analyst'),
		'middleware' => ['web'],
		'title' => 'Developer Analyst',
	],
	/*
	 * This array contains the hosts of which you want to allow incoming requests.
	 * Leave this empty if you want to accept requests from all hosts.
	 */
	'allowed_origins' => [
		//
	],

	'repository' => [
		'./' => 'main'
	],

	'lang_path' => lang_path(),
	'default_language' => env('DEVELOPER_ANALYST_DEFAULT_LANGUAGE', 'de'),

	'log_start' => today()->startOfYear(),

	'code_folders' => [
		app_path()
	],
	'blade_folders' => [
		resource_path('views')
	],
	'connection' => env('DEVELOPER_ANALYST_DATABASE_CONNECTION', config('database.default')),
	'database_table_prefix' => env('DEVELOPER_ANALYST_DATABASE_TABLE_PREFIX', 'developer_analyst_'),
	'cache_driver' => env('DEVELOPER_ANALYST_CACHE_DRIVER', config('cache.default')),




];