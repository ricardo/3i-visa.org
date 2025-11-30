@extends( 'layouts.main' )

@section( 'content' )
	<main class="container">
		<article style="max-width: 600px; margin: 3rem auto;">
			<header>
				<h1>Searchable Input Component Demo</h1>
				<p>Examples of the Livewire searchable input component</p>
			</header>

			<!-- Example 1: Country Search with Flags -->
			<section style="margin-top: 3rem;">
				<h3>Country Search (with flags)</h3>
				<p>Search for a country destination. Type "B" to see Brazil, United Kingdom, etc.</p>

				@livewire( 'searchable-input', [
					'items' => \App\Helpers\Countries::getCountries(),
					'placeholder' => 'Select a country...',
					'show_flags' => true,
					'wire_model' => 'selected_country'
				] )

				<div
					x-data="{ selectedCountry: null }"
					x-on:item-selected.window="selectedCountry = $event.detail.value"
					style="margin-top: 1rem; padding: 1rem; background: var(--pico-card-background-color); border-radius: var(--pico-border-radius);"
				>
					<strong>Selected:</strong>
					<span x-text="selectedCountry ? selectedCountry.name + ' (' + selectedCountry.code.toUpperCase() + ')' : 'None'"></span>
				</div>
			</section>

			<!-- Example 2: Generic List Search -->
			<section style="margin-top: 3rem;">
				<h3>Generic List Search (no flags)</h3>
				<p>Search through a simple list of programming languages.</p>

				@livewire( 'searchable-input', [
					'items' => [ 'PHP', 'JavaScript', 'Python', 'Ruby', 'Java', 'C++', 'Go', 'Rust', 'TypeScript', 'Swift' ],
					'placeholder' => 'Select a programming language...',
					'show_flags' => false,
					'wire_model' => 'selected_language'
				] )

				<div
					x-data="{ selectedLanguage: null }"
					x-on:item-selected.window="selectedLanguage = $event.detail.value"
					style="margin-top: 1rem; padding: 1rem; background: var(--pico-card-background-color); border-radius: var(--pico-border-radius);"
				>
					<strong>Selected:</strong>
					<span x-text="selectedLanguage || 'None'"></span>
				</div>
			</section>

			<!-- Example 3: Another Custom List with Flags -->
			<section style="margin-top: 3rem;">
				<h3>Popular Destinations (with flags)</h3>
				<p>Pre-filtered list of popular travel destinations.</p>

				@livewire( 'searchable-input', [
					'items' => [
						[ 'name' => 'United States', 'code' => 'us' ],
						[ 'name' => 'United Kingdom', 'code' => 'gb' ],
						[ 'name' => 'France', 'code' => 'fr' ],
						[ 'name' => 'Germany', 'code' => 'de' ],
						[ 'name' => 'Italy', 'code' => 'it' ],
						[ 'name' => 'Spain', 'code' => 'es' ],
						[ 'name' => 'Japan', 'code' => 'jp' ],
						[ 'name' => 'Australia', 'code' => 'au' ],
						[ 'name' => 'Canada', 'code' => 'ca' ],
						[ 'name' => 'Brazil', 'code' => 'br' ],
					],
					'placeholder' => 'Select a destination...',
					'show_flags' => true,
					'wire_model' => 'selected_destination'
				] )

				<div
					x-data="{ selectedDestination: null }"
					x-on:item-selected.window="selectedDestination = $event.detail.value"
					style="margin-top: 1rem; padding: 1rem; background: var(--pico-card-background-color); border-radius: var(--pico-border-radius);"
				>
					<strong>Selected:</strong>
					<span x-text="selectedDestination ? selectedDestination.name : 'None'"></span>
				</div>
			</section>

			<!-- Usage Instructions -->
			<section style="margin-top: 3rem;">
				<h3>Features</h3>
				<ul>
					<li><strong>Keyboard Navigation:</strong> Use ↑↓ arrow keys to navigate, Enter to select, Escape to close</li>
					<li><strong>Click Outside:</strong> Click anywhere outside the dropdown to close it</li>
					<li><strong>Partial Search:</strong> Type any part of the name (e.g., "bra" finds Brazil)</li>
					<li><strong>4 Visible Items:</strong> Scrollable list shows 4 items at a time</li>
					<li><strong>Event Emission:</strong> Listen for 'item-selected' event on the window</li>
					<li><strong>No Results:</strong> Shows "No results found" when search returns empty</li>
				</ul>
			</section>

			<!-- Code Example -->
			<section style="margin-top: 3rem;">
				<h3>Usage Example</h3>
				<pre><code>// In your Blade view
@livewire( 'searchable-input', [
	'items' => \App\Helpers\Countries::getCountries(),
	'placeholder' => 'Select a country...',
	'show_flags' => true,
	'wire_model' => 'selected_country'
] )

// Listen for selection event
&lt;div x-on:item-selected.window="handleSelection($event.detail.value)"&gt;
	...
&lt;/div&gt;</code></pre>
			</section>
		</article>
	</main>
@endsection
