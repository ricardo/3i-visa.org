# Searchable Input Component Usage

A reusable Livewire component for searchable dropdowns with optional country flag support.

## Features

- ✅ 100% width responsive input field
- ✅ Optional country flags (left side)
- ✅ Chevron icon (right side, rotates when open)
- ✅ 4 visible lines with scrollable dropdown
- ✅ Partial search matching (e.g., "B" finds "Brazil")
- ✅ Keyboard navigation (↑↓ arrows, Enter, Escape)
- ✅ Click outside to close
- ✅ "No results found" message
- ✅ All data cached in-memory (no API calls)
- ✅ Event emission for parent component integration

## Basic Usage

### Country Search with Flags

```blade
@livewire( 'searchable-input', [
	'items' => \App\Helpers\Countries::getCountries(),
	'placeholder' => 'Select a country...',
	'show_flags' => true,
	'wire_model' => 'selected_country'
] )
```

### Generic List Search

```blade
@livewire( 'searchable-input', [
	'items' => [ 'PHP', 'JavaScript', 'Python', 'Ruby', 'Java' ],
	'placeholder' => 'Select a language...',
	'show_flags' => false,
	'wire_model' => 'selected_language'
] )
```

### Custom List with Flags

```blade
@livewire( 'searchable-input', [
	'items' => [
		[ 'name' => 'United States', 'code' => 'us' ],
		[ 'name' => 'Brazil', 'code' => 'br' ],
		[ 'name' => 'France', 'code' => 'fr' ],
	],
	'placeholder' => 'Select a destination...',
	'show_flags' => true,
	'wire_model' => 'selected_destination'
] )
```

## Component Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `items` | array | Yes | Array of items (strings or objects with 'name' and 'code' keys) |
| `placeholder` | string | No | Input placeholder text (default: 'Search...') |
| `show_flags` | boolean | No | Whether to display country flags (default: false) |
| `wire_model` | string | No | Name for wire:model binding |

## Listening for Selection Events

The component emits an `item-selected` event when a user selects an item:

```blade
<div
	x-data="{ selectedItem: null }"
	x-on:item-selected.window="selectedItem = $event.detail.value"
>
	<strong>Selected:</strong>
	<span x-text="selectedItem ? selectedItem.name : 'None'"></span>
</div>
```

## Keyboard Shortcuts

- **↓ Arrow Down**: Navigate to next item
- **↑ Arrow Up**: Navigate to previous item
- **Enter**: Select highlighted item
- **Escape**: Close dropdown
- **Type to search**: Filter items by partial match

## Countries Helper

The `\App\Helpers\Countries::getCountries()` method returns an array of ~190 countries with:
- `name`: Country name (e.g., "Brazil")
- `code`: ISO 2-letter code in lowercase (e.g., "br")

All countries are cached in-memory for fast access.

## Demo Page

Visit `/demo/searchable-input` to see live examples with different configurations.

## Styling

The component uses PicoCSS conventions and respects your theme colors. All styles are defined in:
- `resources/scss/components/searchable-input.scss`

CSS variables used:
- `--pico-background-color`
- `--pico-muted-border-color`
- `--pico-primary`
- `--pico-primary-focus`
- `--pico-primary-hover`
- `--pico-color`
- `--pico-muted-color`

## Technical Details

### Files Created

1. **Component Class**: `app/Livewire/SearchableInput.php`
2. **Component View**: `resources/views/livewire/searchable-input.blade.php`
3. **Countries Helper**: `app/Helpers/Countries.php`
4. **Component Styles**: `resources/scss/components/searchable-input.scss`
5. **Demo Page**: `resources/views/examples/searchable-input-demo.blade.php`

### Dependencies

- **Livewire v3.7**: Already installed
- **Alpine.js**: Included with Livewire
- **flag-icons**: NPM package for flag SVGs (~600KB)

### Item Data Structure

**Simple strings:**
```php
[ 'PHP', 'JavaScript', 'Python' ]
```

**Objects with name and code:**
```php
[
	[ 'name' => 'Brazil', 'code' => 'br' ],
	[ 'name' => 'France', 'code' => 'fr' ],
]
```

When using objects, flags are displayed if `show_flags` is true and `code` is provided.

## Advanced Usage

### In a Livewire Parent Component

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;

class MyComponent extends Component {

	public $selected_country = null;

	#[On('item-selected')]
	public function handleItemSelected( $value ) {
		$this->selected_country = $value;
		// Do something with the selected country
	}

	public function render() {
		return view( 'livewire.my-component' );
	}
}
```

```blade
<!-- resources/views/livewire/my-component.blade.php -->
<div>
	@livewire( 'searchable-input', [
		'items' => \App\Helpers\Countries::getCountries(),
		'placeholder' => 'Select your destination...',
		'show_flags' => true,
	] )

	@if ( $selected_country )
		<p>You selected: {{ $selected_country['name'] }}</p>
	@endif
</div>
```

## Browser Compatibility

- Modern browsers with ES6 support
- Alpine.js x-data and x-on directives
- CSS transitions and transforms
- Smooth scrolling

## Performance

- **Flag Icons**: ~600KB (cached by browser)
- **Countries List**: ~20KB in-memory
- **No API calls**: All data loaded client-side
- **Efficient filtering**: Uses native PHP array_filter
