<option value="0" selected>اختار الادارة</option>
@if ($get_departements)
    @foreach ($get_departements as $departement)
        <option value="{{ $departement->uuid }}">{{ $departement->name }}</option>
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
