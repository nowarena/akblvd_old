@extends('app')
 
@section('content')
    <h2>Create Link</h2>

    {!! Form::model(new \App\Links, ['route' => ['links.store', 0]]) !!}
        @include('links/partials/_form', [
            'id' => 0
            ])
    {!! Form::close() !!}

@include('admin/partials/_footer')

@endsection