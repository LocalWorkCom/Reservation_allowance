@foreach($children as $child)
<?php /*<option value="{{$child->id}}" {{$child->id == $departement_id ? "selected" : ""}}>*/?>
<option value="{{$child->id}}">
{{ $child->name }}
        @if(count($child->children))
        @include('reservation_allowance.manageChildren',['children' => $child->children, 'parent_id'=>$parent_id])
        @endif
    </option>
@endforeach
