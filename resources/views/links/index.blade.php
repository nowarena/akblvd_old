@extends('app')

@section('content')


<h2>Links</h2>
<br>

@if ( !$linksObj->count() )
    You have no links 
@else
    <ul class='list-unstyled'>
        @foreach( $linksObj as $obj )
            <li class='cat_row'>
                {!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('links.destroy', $obj->id))) !!}
                    <div class='category_label'>{{ $obj->name }}</div>
                        {!! link_to_route('links.edit', 'Edit', array($obj->id), array('class' => 'btn btn-info')) !!} &nbsp;   
                        {!! Form::submit('Delete', array('class' => 'btn btn-danger btn-delete')) !!}
                {!! Form::close() !!}
            </li>
        @endforeach
    </ul>
@endif


@include('admin/partials/_footer')
    


@endsection
