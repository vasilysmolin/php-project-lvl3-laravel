<?php

use DiDom\Document;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

Route::get('/', [
    'as' => 'main', function (): object {
    return view('welcome');
}]);

Route::post('urls', [
    'as' => 'urls.store', function (Request $request): object {
    $formData = $request->input('url');
    $validator = Validator::make($request->input('url'), [
        'name' => 'required|url|max:255|min:11',
    ]);

    if ($validator->fails()) {
        flash(__('Некорректный URL'))->error();
        return redirect()->route('main')
            ->withErrors($validator)
            ->withInput();
    };

    $parsedUrl = parse_url($formData['name']);
    $host = mb_strtolower("{$parsedUrl['scheme']}://{$parsedUrl['host']}");

    $url = DB::table('urls')->where('name', $host)->first();
    if (!is_null($url)) {
        $id = $url->id;
        flash(__('Cтраница уже существует'));
    } else {
        $urlData = [
            'name' => $host,
            'updated_at' => Carbon::now(),
            'created_at' => Carbon::now()
        ];
        DB::table('urls')->insert($urlData);
        $url = app('db')->table('urls')->latest()->first();
        $id = $url->id;
        flash(__('Страница успешно добавлена'));
    }

    return redirect()->route('urls.show', $id);
}]);

Route::get('urls', [
    'as' => 'urls.index', function (): object {
    $urls = DB::table('urls')->paginate(25);
    $lastedCheck = DB::table('urls_check')
        ->distinct('url_id')
        ->latest()
        ->get()
        ->keyBy('url_id');
    return view('urls.index', compact('urls', 'lastedCheck'));
}]);

Route::get('urls/{id}', [
    'as' => 'urls.show', function ($id): object {
    $url = DB::table('urls')->find($id);
    if(!$url) {
        abort(404);
    }

    $checks = DB::table('urls_check')
        ->where('url_id', $url->id)
        ->get();
    return view('urls.show', compact('url', 'checks'));
}]);

Route::post('urls/{id}/checks', [
    'as' => 'urls.checks.store', function ($id): object {
    $url = app('db')->table('urls')->find($id);
    if(!isset($url)){
        abort($url, 404);
    }

    $response = HTTP::get($url->name);

    $body = $response->getBody()->getContents();
    $document = new Document($body);
    if($document->first('p')) {
        $h1 = $document->first('p')->text();
    }

    if($document->first('meta[name=keywords]')) {
        $keywords = $document->first('meta[name=keywords]')->getAttribute('content');
    }

    if($document->first('meta[name=description]')) {
        $description = $document->first('meta[name=description]')->getAttribute('content');
    }

    app('db')->table('urls_check')->insert([
        'url_id' => $url->id,
        'status_code' => $response->getStatusCode(),
        'h1' => $h1 ?? null,
        'keywords' => $keywords ?? null,
        'description' => $description ?? null,
        'updated_at' => Carbon::now(),
        'created_at' => Carbon::now()
    ]);
    flash('Страница успешно проверена');

    return redirect()->route('urls.show', $url->id);
}]);

