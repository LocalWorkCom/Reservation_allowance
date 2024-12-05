<option value="0" selected data-name="">اختار الادارة</option>

@if ($get_departements)
@if(Auth::user()->rule_id != 3)
<option value="all" data-name="all">اختار الكل</option>
@endif
    @foreach ($get_departements as $departement)
        <option value="{{ $departement->uuid }}" data-name="{{ $departement->name }}">{{ $departement->name }}</option>
        @if (count($departement->children))
            @include('reservation_allowance.manageChildren', [
                'children' => $departement->children,
                'parent_id' => '',
            ])
        @endif
    @endforeach
@endif
{{-- 
<script>
    $("#departement_id").select2({
        dir: "rtl"
    });
</script> --}}
