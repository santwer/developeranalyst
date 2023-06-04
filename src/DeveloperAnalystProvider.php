<?php

namespace Santwer\DeveloperAnalyst;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use \Illuminate\Support\ServiceProvider;
use Santwer\DeveloperAnalyst\Apps\AppProvider;
use Illuminate\Contracts\Container\Container as ContainerContract;
use Santwer\DeveloperAnalyst\Commands\AnalyseGitStatisticsCommand;
use Santwer\DeveloperAnalyst\Dashboard\Http\Controllers\DashboardController;

class DeveloperAnalystProvider extends ServiceProvider
{
	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//dd('test');
		$this->publishes([
			//__DIR__.'/../config/websockets.php' => base_path('config/websockets.php'),
		], 'config');

		$this->publishes([
			__DIR__.'/../database/migrations/0000_00_00_000000_create_developer_analyst_git_statistics_table.php' =>
				database_path('migrations/0000_00_00_000000_create_developer_analyst_git_statistics_table.php'),
			__DIR__.'/../database/migrations/0000_00_00_000000_create_developer_analyst_developer_table.php' =>
				database_path('migrations/0000_00_00_000000_create_developer_analyst_developer_table.php'),
			__DIR__.'/../database/migrations/0000_00_00_000000_createdeveloper_analyst_dev_files.php' =>
				database_path('migrations/0000_00_00_000000_createdeveloper_analyst_dev_files.php'),
			__DIR__.'/../database/migrations/0000_00_00_000000_create_developer_analyst_developer_dev_files_table.php' =>
				database_path('migrations/0000_00_00_000000_create_developer_analyst_developer_dev_files_table.php'),
		], 'migrations');

		$this
			->registerRoutes()
			->registerDashboardGate();

		$this->loadViewsFrom(__DIR__.'/../resources/views/', 'developerAnalyst');

		$this->commands([
			AnalyseGitStatisticsCommand::class,
		]);
	}

	public function register()
	{
		$this->mergeConfigFrom(__DIR__.'/../config/developerAnalyst.php', 'developerAnalyst');
	}

	protected function registerRoutes()
	{
		Route::prefix(config('developerAnalyst.dashboard.path'))->group(function () {
			Route::middleware(config('developerAnalyst.dashboard.middleware', []))->group(function () {
				Route::get('/', [DashboardController::class, 'index']);
			});
		});

		return $this;
	}

	protected function registerDashboardGate()
	{
		Gate::define('viewDeveloperAnalystDashboard', function ($user = null) {
			return app()->environment('local');
		});

		return $this;
	}
}