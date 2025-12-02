@props( [ 'name' => 'count', 'min' => 1, 'max' => 10, 'initial' => 1 ] )

<div
	class="stepper"
	x-data="{
		count: {{ $initial }},
		min: {{ $min }},
		max: {{ $max }},
		decrement() {
			if (this.count > this.min) {
				this.count--;
			}
		},
		increment() {
			if (this.count < this.max) {
				this.count++;
			}
		}
	}"
>
	<button
		type="button"
		class="stepper-button stepper-minus"
		x-on:click="decrement()"
		x-bind:disabled="count <= min"
		aria-label="Decrease"
	>
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<line x1="5" y1="12" x2="19" y2="12"></line>
		</svg>
	</button>

	<div class="stepper-display" x-text="count"></div>

	<button
		type="button"
		class="stepper-button stepper-plus"
		x-on:click="increment()"
		x-bind:disabled="count >= max"
		aria-label="Increase"
	>
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<line x1="12" y1="5" x2="12" y2="19"></line>
			<line x1="5" y1="12" x2="19" y2="12"></line>
		</svg>
	</button>

	<input type="hidden" name="{{ $name }}" x-bind:value="count">
</div>
