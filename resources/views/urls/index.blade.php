@extends('layouts.app')

@section('content')
    <div class="container-lg">
        <h1 class="mt-5 mb-3">Сайты</h1>
        <div class="table-responsive">
            <table class="table table-bordered table-hover text-nowrap">
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Последняя проверка</th>
                    <th>Статус кода</th>
                </tr>
                @foreach ($urls as $url)
                    <tr>
                        <td>{{ $url->id }}</td>
                        <td><a href="{{ route('urls.show', ['id' => $url->id]) }}">{{ $url->name }}</a></td>
                        <td>{{ $lastChecks[$url->id]->created_at ?? ''}} </td>
                        <td>{{ $lastChecks[$url->id]->status_code ?? '' }}</td>
                    </tr>
                @endforeach
            </table>
            {{ $urls->links() }}
        </div>
    </div>
@endsection
