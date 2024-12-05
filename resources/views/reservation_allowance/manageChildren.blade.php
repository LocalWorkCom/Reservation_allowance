@foreach($children as $child)
<?php /*<option value="{{$child->id}}" {{$child->id == $departement_id ? "selected" : ""}}>*/?>
<option value="{{$child->uuid}}" data-name="{{ $child->name }}" {{$child->id == $departement_id ? "selected" : ""}}>
{{ $child->name }}
        @if(count($child->children))
        @include('reservation_allowance.manageChildren',['children' => $child->children, 'parent_id'=>$parent_id])
        @endif
    </option>
@endforeach
