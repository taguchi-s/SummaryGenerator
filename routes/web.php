<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeneratorController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sample', function(){
    $text = '明日は運動会に行く予定です。お弁当が楽しみです。';

$MeCab = new \MeCab\Tagger();
$nodes = $MeCab->parseToNode($text);
$result = "";
foreach ($nodes as $n) {
    $result .= "---------------------------". "<br>";
    $result .= "[" . $n->getSurface() . "]" . "<br>";
    $result .= $n->getFeature() . "<br>";
} 
    return $result;
});

// ここに追記
Route::get('summarygenerator', [GeneratorController::class, 'index']);
Route::post('summarygenerator', [GeneratorController::class, 'generate']);