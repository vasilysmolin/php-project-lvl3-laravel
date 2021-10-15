<?php

use DiDom\Document;
use Illuminate\Http\Client\ConnectionException;
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
        $validator = Validator::make($request->toArray(), [
            'url.name' => 'required|url|max:255',
        ]);

        if ($validator->fails()) {
            flash(__('Некорректный URL'))->error();
            return redirect()->route('main')
                ->withErrors($validator)
                ->withInput($request->all);
        }

        $parsedUrl = parse_url($validator->valid()['url']['name']);
        $normalizedUrl = mb_strtolower("{$parsedUrl['scheme']}://{$parsedUrl['host']}");
        $id = null;
        $url = DB::table('urls')->where('name', $normalizedUrl)->first();
        if (!is_null($url)) {
            flash(__('Cтраница уже существует'));
        } else {
            $urlData = [
                'name' => $normalizedUrl,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now()
            ];
            DB::table('urls')->insert($urlData);
            $url = app('db')->table('urls')->latest()->first();
            if (!is_null($url)) {
                $id = $url->id ;
            }

            flash(__('Страница успешно добавлена'));
            return redirect()->route('urls.show', $id);
        }

        return redirect()->route('main');
    }]);

Route::get('urls', [
    'as' => 'urls.index', function (): object {
        $urls = DB::table('urls')->paginate(25);
        $checks = DB::table('url_checks')
            ->distinct('url_id')
            ->orderBy('url_id')
            ->latest()
            ->get()
            ->keyBy('url_id');
        return view('urls.index', compact('urls', 'checks'));
    }]);

Route::get('urls/{id}', [
    'as' => 'urls.show', function ($id): object {
        $url = DB::table('urls')->find((int) $id);
        if (is_null($url)) {
            abort(404);
        }

        $checks = DB::table('url_checks')
            ->where('url_id', $url->id)
            ->get();
        return view('urls.show', compact('url', 'checks'));
    }]);

Route::post('urls/{id}/checks', [
    'as' => 'urls.checks.store', function ($id): object {
        $url = app('db')->table('urls')->find((int) $id);
        if (is_null($url)) {
            abort(404);
        }

        try {
            $response = HTTP::get($url->name);

            $body = $response->getBody()->getContents();
            $document = new Document($body);

            $h1 = optional($document->first('h1'))->text();

            if (!is_null($document->first('meta[name=keywords]'))) {
                $keywords = $document->first('meta[name=keywords]')->getAttribute('content');
            }

            if (!is_null($document->first('meta[name=description]'))) {
                $description = $document->first('meta[name=description]')->getAttribute('content');
            }

            app('db')->table('url_checks')->insert([
                'url_id' => $url->id,
                'status_code' => $response->getStatusCode(),
                'h1' => $h1 ?? null,
                'keywords' => $keywords ?? null,
                'description' => $description ?? null,
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now()
            ]);
            flash('Страница успешно проверена');
        } catch (ConnectionException | RequestException $error) {
            flash($error->getMessage())->error();
        }
        return redirect()->route('urls.show', $url->id);
    }]);
