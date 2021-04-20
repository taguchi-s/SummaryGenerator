<?php

namespace App\Http\Controllers;

use App\Models\Summary;
use Illuminate\Http\Request;
use Goutte\Client;
use Symfony\Component\HttpClient\Exception\TransportException;

class GeneratorController extends Controller
{
    public function index()
    {
        return view("generator.index");
    }

    public function generate(Request $request)
    {
        $url = $request->request->all()["url"];
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $error = "正しいURLを入力してください";
            return view("generator.index", ["error" => $error]);
        }
        $summary = Summary::where("url", $url)->first();
        // dd($summary);
        if (is_null($summary)) {
            $result = $this->createSummary($url);
            if($result[0]){
                //成功した場合の処理
                $summary = $result[1];
            }else{
                //失敗した場合の処理
                return view("generator.index", ["error" => $result[1]]);
            }
        }else{
            $summary = $summary->content;
        }

        return view("generator.index", ["summary" => $summary]);
    }

    private function createSummary($url)
    {
        try{
            $client = new Client();
            $crawler = $client->request('GET', $url);
            switch($client->getInternalResponse()->getStatusCode()){
                case 200:
                    $summary = "";
                    $crawler->filter('p')->each(function($item) use(&$summary){
                        $summary .= $item->text();
                    });
                    $summary = $this->convert($summary);
                    return [True, $summary];
                case 404:
                    return [False, "指定したページが見つかりませんでした"];
                case 500:
                    return [False, "指定したページがあるサーバーにエラーがあります"];
                default:
                    return [False, "何らかのエラーによって指定したページのデータを取得できませんでした"];
            } 
        } catch(TransportException $e){
            return [False, "タイムエラー or URLが間違っています"];
        }

        //コンテンツ取得
        // $http_response_header = array();
        // if ($data = @file_get_contents($url)) {
        //     return [True, $data];
        // } else {
        //     $error_message; //エラー処理
        //     if (count($http_response_header) > 0) {
        //         //「$http_response_header[0]」にはステータスコードがセットされている
        //         $status_code = explode(' ', $http_response_header[0]);  //「$status_code[1]」にステータスコードの数字だけが入る

        //         //エラーの判別
        //         switch ($status_code[1]) {
        //                 //404エラーの場合
        //             case 404:
        //                 $error_message = "指定したページが見つかりませんでした";
        //                 break;
        //                 //500エラーの場合
        //             case 500:
        //                 $error_message = "指定したページがあるサーバーにエラーがあります";
        //                 break;
        //                 //その他のエラーの場合
        //             default:
        //                 $error_message = "何らかのエラーによって指定したページのデータを取得できませんでした";
        //         }
        //         //「$http_response_header」の初期化
        //         $http_response_header = array();
        //         return [False, $error_message];
        //     } else {
        //         //タイムアウトの場合 or 存在しないドメインだった場合
        //         return [False, "タイムエラー or URLが間違っています"];
        //     }
        // }
    }
    private function convert($text){
        $MeCab = new \MeCab\Tagger();
        $nodes = $MeCab->parseToNode($text);
        $result = "";
        $nouns = [];
        $verbs = [];
        $adjectives = [];
        $particles = [];
        foreach ($nodes as $n) {
            $features = explode(",", $n->getFeature());
                switch($features[0]){
                    case "動詞":
                    case "助動詞":
                        $verbs[] = $n->getSurface();
                        break;
                    case "名詞":
                        if($features[1] === "固有名詞" || $features[1] === "一般"){
                            $nouns[] = $n->getSurface();
                        }                  
                        break;
                    case "形容詞":
                        $adjectives[] = $n->getSurface();
                        break;
                    case "助詞":
                        $particles[] = $n->getSurface();
                        break;
                }
               
        } 
        $max = min(10, count($nouns),count($verbs), count($adjectives), count($particles));
        for($i = 0; $i < $max; $i++){
            $result = $result.$nouns[$i].$particles[$i].$adjectives[$i].$verbs[$i];
        }

            // $result = implode(",", array_unique($nouns));
            // $nouns_index = rand(0, count($nouns) - 1);
            // $result = $result.$nouns[$nouns_index];
            // $verbs_index = rand(0, count($verbs) - 1);
            // $result = $result.$verbs[$verbs_index];

        return $result;
    }
}
