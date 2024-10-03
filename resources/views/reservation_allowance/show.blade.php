<option selected disabled>اختار من القائمة</option>
@if($get_departements)
    @foreach($get_departements as $departement)
        <option value="{{ $departement->id }}">{{ $departement->name }}</option>
    @endforeach
@endif
