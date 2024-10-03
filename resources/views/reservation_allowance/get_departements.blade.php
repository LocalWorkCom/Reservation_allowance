<option selected disabled>اختار من القائمة</option>
@if($get_departements)
    @foreach($get_departements as $departement)
        <option value="{{ $departement->id }}">{{ $departement->name }}</option>
        @if(count($departement->children))
            @include('reservation_allowance.manageChildren',['children' => $departement->children, 'parent_id'=>''])
        @endif
    @endforeach
@endif

