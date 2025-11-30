<div
	class="searchable-input-wrapper"
	x-data="{
		isOpen: @entangle('is_open'),
		highlightedIndex: @entangle('highlighted_index'),
		itemCount: {{ count( $filtered_items ) }},

		handleKeydown(event) {
			if ( !this.isOpen ) {
				return;
			}

			// Arrow Down
			if ( event.key === 'ArrowDown' ) {
				event.preventDefault();
				this.highlightedIndex = this.highlightedIndex < this.itemCount - 1
					? this.highlightedIndex + 1
					: 0;
				this.scrollToHighlighted();
			}

			// Arrow Up
			else if ( event.key === 'ArrowUp' ) {
				event.preventDefault();
				this.highlightedIndex = this.highlightedIndex > 0
					? this.highlightedIndex - 1
					: this.itemCount - 1;
				this.scrollToHighlighted();
			}

			// Enter
			else if ( event.key === 'Enter' && this.highlightedIndex >= 0 ) {
				event.preventDefault();
				$wire.selectItem( this.highlightedIndex );
			}

			// Escape
			else if ( event.key === 'Escape' ) {
				event.preventDefault();
				$wire.closeDropdown();
			}
		},

		scrollToHighlighted() {
			this.$nextTick( () => {
				const dropdown = this.$refs.dropdown;
				const highlighted = dropdown?.querySelector( '.searchable-input-item.highlighted' );

				if ( highlighted && dropdown ) {
					const dropdownRect = dropdown.getBoundingClientRect();
					const highlightedRect = highlighted.getBoundingClientRect();

					if ( highlightedRect.bottom > dropdownRect.bottom ) {
						highlighted.scrollIntoView( { block: 'nearest', behavior: 'smooth' } );
					} else if ( highlightedRect.top < dropdownRect.top ) {
						highlighted.scrollIntoView( { block: 'nearest', behavior: 'smooth' } );
					}
				}
			} );
		}
	}"
	x-on:click.away="$wire.closeDropdown()"
	wire:ignore.self
>
	<div class="searchable-input-container">
		<!-- Input Field -->
		<div class="searchable-input-field">
			<!-- Selected Flag (shown inside input) -->
			@if ( $show_flags && $selected_flag_code )
				<span class="fi fi-{{ $selected_flag_code }} searchable-input-selected-flag"></span>
			@endif

			<input
				type="text"
				wire:model.live="search"
				placeholder="{{ $placeholder }}"
				class="searchable-input {{ $show_flags && $selected_flag_code ? 'has-flag' : '' }}"
				x-on:keydown="handleKeydown"
				x-on:focus="isOpen = true"
				autocomplete="off"
			/>

			<!-- Chevron Icon -->
			<svg
				class="searchable-input-chevron"
				:class="{ 'rotated': isOpen }"
				xmlns="http://www.w3.org/2000/svg"
				viewBox="0 0 24 24"
				fill="none"
				stroke="currentColor"
				stroke-width="2"
				stroke-linecap="round"
				stroke-linejoin="round"
			>
				<polyline points="6 9 12 15 18 9"></polyline>
			</svg>
		</div>

		<!-- Dropdown List -->
		<div
			class="searchable-input-dropdown"
			x-show="isOpen"
			x-cloak
			x-ref="dropdown"
			x-transition:enter="searchable-input-transition-enter"
			x-transition:enter-start="searchable-input-transition-enter-start"
			x-transition:enter-end="searchable-input-transition-enter-end"
			x-transition:leave="searchable-input-transition-leave"
			x-transition:leave-start="searchable-input-transition-leave-start"
			x-transition:leave-end="searchable-input-transition-leave-end"
		>
			@if ( count( $filtered_items ) > 0 )
				<ul class="searchable-input-list">
					@foreach ( $filtered_items as $index => $item )
						@php
							$item_name = is_array( $item ) && isset( $item['name'] ) ? $item['name'] : $item;
							$item_code = is_array( $item ) && isset( $item['code'] ) ? $item['code'] : '';
						@endphp

						<li
							class="searchable-input-item"
							:class="{ 'highlighted': highlightedIndex === {{ $index }} }"
							wire:click="selectItem( {{ $index }} )"
							x-on:mouseenter="highlightedIndex = {{ $index }}"
						>
							@if ( $show_flags && $item_code )
								<span class="fi fi-{{ $item_code }} searchable-input-flag"></span>
							@endif

							<span class="searchable-input-item-text">{{ $item_name }}</span>
						</li>
					@endforeach
				</ul>
			@else
				<div class="searchable-input-no-results">
					No results found
				</div>
			@endif
		</div>
	</div>
</div>
