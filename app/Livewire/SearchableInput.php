<?php

namespace App\Livewire;

use Livewire\Component;

class SearchableInput extends Component {

	public $items = [];
	public $placeholder = 'Search...';
	public $show_flags = false;
	public $wire_model = '';
	public $initial_value = null;
	public $required = false;

	public $search = '';
	public $selected_value = '';
	public $selected_flag_code = '';
	public $previous_value = '';
	public $previous_flag_code = '';
	public $is_open = false;
	public $highlighted_index = -1;

	/**
	 * Mount the component
	 *
	 * @param array $items Array of items to search
	 * @param string $placeholder Input placeholder text
	 * @param bool $show_flags Whether to show country flags
	 * @param string $wire_model Name for wire:model binding
	 * @param array|string|null $initial_value Initial selected value
	 * @param bool $required Whether the field is required (restores value on blur if empty)
	 */
	public function mount( $items = [], $placeholder = 'Search...', $show_flags = false, $wire_model = '', $initial_value = null, $required = false ) {
		$this->items = $items;
		$this->placeholder = $placeholder;
		$this->show_flags = $show_flags;
		$this->wire_model = $wire_model;
		$this->initial_value = $initial_value;
		$this->required = $required;

		// Set initial value if provided
		if ( $this->initial_value ) {
			if ( is_array( $this->initial_value ) && isset( $this->initial_value['name'] ) ) {
				$this->search = $this->initial_value['name'];
				$this->selected_value = $this->initial_value;
				$this->selected_flag_code = $this->initial_value['code'] ?? '';
				$this->previous_value = $this->initial_value['name'];
				$this->previous_flag_code = $this->initial_value['code'] ?? '';
			} elseif ( is_string( $this->initial_value ) ) {
				$this->search = $this->initial_value;
				$this->selected_value = $this->initial_value;
				$this->previous_value = $this->initial_value;
			}
		}
	}

	/**
	 * Update search and open dropdown
	 */
	public function updatedSearch() {
		$this->is_open = true;
		$this->highlighted_index = -1;

		// Clear selected flag when user starts typing
		if ( $this->show_flags ) {
			$this->selected_flag_code = '';
		}
	}

	/**
	 * Select an item from the dropdown
	 *
	 * @param int $index Index of selected item in filtered results
	 */
	public function selectItem( $index ) {
		$filtered_items = $this->getFilteredItems();

		if ( isset( $filtered_items[$index] ) ) {
			$item = $filtered_items[$index];

			// If item is an array with 'name' key, use that; otherwise use the item directly
			if ( is_array( $item ) && isset( $item['name'] ) ) {
				$this->search = $item['name'];
				$this->selected_value = $item;
				$this->selected_flag_code = $item['code'] ?? '';
				// Save previous value for restoration
				$this->previous_value = $item['name'];
				$this->previous_flag_code = $item['code'] ?? '';
			} else {
				$this->search = $item;
				$this->selected_value = $item;
				$this->selected_flag_code = '';
				// Save previous value for restoration
				$this->previous_value = $item;
				$this->previous_flag_code = '';
			}

			$this->is_open = false;
			$this->highlighted_index = -1;

			// Emit event for parent component
			$this->dispatch( 'item-selected', value: $this->selected_value );
		}
	}

	/**
	 * Close the dropdown
	 */
	public function closeDropdown() {
		$this->is_open = false;
		$this->highlighted_index = -1;
	}

	/**
	 * Handle blur event - restore previous value if required and no valid selection
	 */
	public function handleBlur() {
		// Only restore if required is true and user didn't make a valid selection
		if ( $this->required && $this->previous_value ) {
			// Get the name of the currently selected value for comparison
			$current_selected_name = '';
			if ( is_array( $this->selected_value ) && isset( $this->selected_value['name'] ) ) {
				$current_selected_name = $this->selected_value['name'];
			} elseif ( is_string( $this->selected_value ) ) {
				$current_selected_name = $this->selected_value;
			}

			// If search text doesn't match the selected value, restore previous
			if ( $this->search !== $current_selected_name ) {
				$this->search = $this->previous_value;
				$this->selected_flag_code = $this->previous_flag_code;
			}
		}

		$this->closeDropdown();
	}

	/**
	 * Clear search field for new selection (used when clicking input with required=true)
	 */
	public function clearForNewSelection() {
		// Only clear if required and search has content
		if ( $this->required && ! empty( $this->search ) ) {
			$this->search = '';
			$this->selected_flag_code = '';
		}
		// Always open dropdown when clicking input
		$this->is_open = true;
		$this->highlighted_index = -1;
	}

	/**
	 * Get filtered items based on search query
	 *
	 * @return array Filtered items
	 */
	public function getFilteredItems() {
		if ( empty( $this->search ) ) {
			return $this->items;
		}

		$search_lower = mb_strtolower( $this->search );

		return array_values( array_filter( $this->items, function( $item ) use ( $search_lower ) {
			// Handle array items with 'name' key
			if ( is_array( $item ) && isset( $item['name'] ) ) {
				return str_contains( mb_strtolower( $item['name'] ), $search_lower );
			}

			// Handle simple string items
			return str_contains( mb_strtolower( $item ), $search_lower );
		} ) );
	}

	/**
	 * Render the component
	 */
	public function render() {
		return view( 'livewire.searchable-input', [
			'filtered_items' => $this->getFilteredItems(),
		] );
	}

}
