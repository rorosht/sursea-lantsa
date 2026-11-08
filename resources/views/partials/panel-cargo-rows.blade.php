@php
    $defaults = $defaults ?? [['name' => 'Box of Goods', 'qty' => 2], ['name' => 'Sack of Rice', 'qty' => 1]];
@endphp

<div class="cargo-rows panel-cargo-rows" id="cargoRows">
    @foreach($defaults as $row)
        <div class="cargo-row">
            <div class="form-field">
                <label>Cargo item name</label>
                <input type="text" name="cargo_name[]" value="{{ $row['name'] }}" placeholder="e.g. Box, Sack, Crate" required>
            </div>
            <div class="form-field qty">
                <label>Quantity</label>
                <input type="number" name="cargo_qty[]" min="1" max="99" value="{{ $row['qty'] }}" required>
            </div>
            <button type="button" class="trash" data-remove-row aria-label="Remove cargo item">
                @include('partials.icon', ['name' => 'trash'])
            </button>
        </div>
    @endforeach
</div>

<button type="button" class="add-cargo panel-add-cargo" id="addCargo">
    @include('partials.icon', ['name' => 'plus-circle'])
    <span>Add Another Cargo</span>
</button>

<template id="cargoTemplate">
    <div class="cargo-row">
        <div class="form-field">
            <label>Cargo item name</label>
            <input type="text" name="cargo_name[]" value="" placeholder="e.g. Box, Sack, Crate" required>
        </div>
        <div class="form-field qty">
            <label>Quantity</label>
            <input type="number" name="cargo_qty[]" min="1" max="99" value="1" required>
        </div>
        <button type="button" class="trash" data-remove-row aria-label="Remove cargo item">
            @include('partials.icon', ['name' => 'trash'])
        </button>
    </div>
</template>

