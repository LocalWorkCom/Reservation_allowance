@foreach($children as $child)
    <option value="{{$child->id}}" {{$child->id == $departement_id ? "selected" : ""}}>
        {{ $child->name }}
        @if(count($child->children))
        @include('reservation_allowance.manageChildren',['children' => $child->children, 'parent_id'=>$parent_id])
        @endif
    </option>
@endforeach
