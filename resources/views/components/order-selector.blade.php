@props(['applications', 'current_application_id', 'country_slug'])

@if(count($applications) > 1)
<div
	class="order-selector"
	x-data="{
		open: false,
		search: '',
		get filteredOrders() {
			if (!this.search) return this.orders;
			return this.orders.filter(order =>
				order.label.toLowerCase().includes(this.search.toLowerCase())
			);
		},
		orders: {{ Js::from($applications->map(function($app) {
			$country_name = \App\Helpers\Countries::getCountryName($app->destination_country_code);
			return [
				'id' => $app->id,
				'order_number' => $app->order_number,
				'country_slug' => \App\Helpers\Countries::getCountrySlugs()[$app->destination_country_code] ?? strtolower($app->destination_country_code),
				'label' => $country_name . ': ' . $app->order_number,
			];
		})->values()) }}
	}"
	x-on:click.outside="open = false"
>
	<label for="order-selector">@lang('View another order:')</label>

	<!-- Dropdown Button -->
	<button
		type="button"
		class="order-selector-button"
		x-on:click="open = !open"
		x-bind:aria-expanded="open"
		style="width: 100%; text-align: left; padding: 12px; border: 1px solid #ccc; border-radius: 8px; background: white; cursor: pointer; display: flex; justify-content: space-between; align-items: center;"
	>
		<span x-text="orders.find(o => o.id === {{ $current_application_id }})?.label || '@lang('Select an order')'"></span>
		<svg style="width: 20px; height: 20px; transition: transform 0.2s;" x-bind:style="open && 'transform: rotate(180deg)'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
		</svg>
	</button>

	<!-- Dropdown Menu -->
	<div
		x-show="open"
		x-transition
		class="order-selector-menu"
		style="position: absolute; z-index: 50; width: 100%; margin-top: 4px; background: white; border: 1px solid #ccc; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-height: 300px; overflow: hidden;"
	>
		<!-- Search Input -->
		<div style="padding: 8px;">
			<input
				type="text"
				x-model="search"
				placeholder="@lang('Search orders...')"
				class="order-selector-search"
				style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
				x-on:keydown.escape="open = false"
			>
		</div>

		<!-- Order List -->
		<div style="max-height: 240px; overflow-y: auto;">
			<template x-for="order in filteredOrders" x-bind:key="order.id">
				<a
					x-bind:href="'{{ route('payment.success', ['country' => '__COUNTRY__']) }}'.replace('__COUNTRY__', order.country_slug) + '?order_number=' + order.order_number"
					class="order-selector-item"
					x-bind:class="{ 'active': order.id === {{ $current_application_id }} }"
					style="display: block; padding: 12px 16px; text-decoration: none; color: inherit; border-bottom: 1px solid #eee; transition: background 0.2s;"
					x-on:mouseenter="$el.style.background = '#f5f5f5'"
					x-on:mouseleave="$el.style.background = order.id === {{ $current_application_id }} ? '#e3f2fd' : 'white'"
					x-bind:style="order.id === {{ $current_application_id }} && 'background: #e3f2fd; font-weight: 600;'"
				>
					<span x-text="order.label"></span>
				</a>
			</template>

			<!-- No results -->
			<div x-show="filteredOrders.length === 0" style="padding: 16px; text-align: center; color: #999;">
				@lang('No orders found')
			</div>
		</div>
	</div>
</div>

<style>
	.order-selector {
		position: relative;
		margin-bottom: 24px;
	}

	.order-selector-button:hover {
		border-color: #999;
	}

	.order-selector-button:focus {
		outline: 2px solid #0066cc;
		outline-offset: 2px;
	}

	.order-selector-item:last-child {
		border-bottom: none;
	}

	.order-selector-item.active {
		background: #e3f2fd;
		font-weight: 600;
	}

	.order-selector label {
		display: block;
		margin-bottom: 8px;
		font-weight: 600;
	}
</style>
@endif
