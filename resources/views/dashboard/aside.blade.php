<aside class="aside is-placed-left is-expanded">
	<div class="aside-tools">
		<div class="aside-tools-label">
			<span>{{ config('developerAnalyst.dashboard.title') }}</span>
		</div>
	</div>
	<div class="menu is-menu-main">
		<p class="menu-label">General</p>
		<ul class="menu-list">
			<li>
				<a href="{{ route('developerAnalyst.dashboard.index') }}" class="is-active router-link-active has-icon">
					<span class="icon"><i class="mdi mdi-desktop-mac"></i></span>
					<span class="menu-item-label">Dashboard</span>
				</a>
			</li>
		</ul>
		<p class="menu-label">Git</p>
		<ul class="menu-list">
			<li>
				<a href="{{ route('developerAnalyst.dashboard.git-statistics') }}" class="has-icon">
					<span class="icon has-update-mark"><i class="mdi mdi-table"></i></span>
					<span class="menu-item-label">git Statistics</span>
				</a>
			</li>
		</ul>
		<p class="menu-label">About</p>
		<ul class="menu-list">
			<li>
				<a href="https://github.com/santwer/developeranalyst" target="_blank" class="has-icon">
					<span class="icon"><i class="mdi mdi-github-circle"></i></span>
					<span class="menu-item-label">GitHub</span>
				</a>
			</li>
		</ul>
	</div>
</aside>