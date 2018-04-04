<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\igdata;
use App\comment;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Support\Facades\Redirect;

class IndexController extends Controller
{
    //
    public function index(){ 
        $this->data['label'] = igdata::groupBy('query_label')->get(['query_label']);
        $hasComment = [];
        foreach ($this->data['label'] as $key => $value) {
            $check = comment::where('query_label',$value->query_label)->first();
            
            if($check){
                $hasComment[$value->query_label] = 'disabled';
            }else{
                $hasComment[$value->query_label] = 'active';
            }
            
        }
        $this->data['hasComment'] = $hasComment;
        return view('welcome',$this->data);
    }

    public function getHashtag(Request $request){
        $this->data['datasuccess'] = 'SUCCESS';
        $this->getPostByHashtag($request->_hastag);
        return Redirect::back()->with($this->data);
    }

    public function getPostByHashtag($hashtag){
        $handlerStack = HandlerStack::create(new CurlHandler());
        $handlerStack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));
        $client = new Client(array('handler' => $handlerStack));
        $endpoint = null;
        $nextPage = null;
        do {
            $endpoint ? $res = $client->get('https://www.instagram.com/explore/tags/'.$hashtag.'/?__a=1&max_id='.$endpoint) : $res = $client->get('https://www.instagram.com/explore/tags/'.$hashtag.'/?__a=1');
            $resobj = json_decode(json_encode(json_decode($res->getBody(),TRUE)));
            $data = new igdata();        
            foreach ($resobj->graphql->hashtag->edge_hashtag_to_media->edges as $key => $value) {
                $object = new \stdClass();
                $object->query_label = $resobj->graphql->hashtag->name;                      
                $object->comment_id = $value->node->id; 
                $object->user = $value->node->owner->id;
                $object->comment_count = $value->node->edge_media_to_comment->count;
                $object->shortcode = $value->node->shortcode;         
                foreach ($value->node->edge_media_to_caption->edges as $_key => $_value) {
                    $object->comment = $_value->node->text;                
                }               
                $data->insert($object);            
            }
            $hasNext = $resobj->graphql->hashtag->edge_hashtag_to_media->page_info;
            $endpoint = $hasNext->end_cursor;
            $nextPage = $hasNext->has_next_page;
        } while ($nextPage != null);
    }

    public function getComment($label){
        $shortcode = igdata::where('query_label',$label)->where('comment_count','!=','0')->pluck('shortcode');
        for ($i=0; $i < sizeof($shortcode); $i++) {
            $this->getCommentByPost($label,$shortcode[$i]);
        }
        return redirect()->back();
    }

    public function resumeLimitOfComment($label,$shortcode){
        $_id = igdata::where('shortcode',$shortcode)->get(['id'])->first();
        $_query = igdata::where('query_label',$label)->where('id','>=',$_id->id)->where('comment_count','!=','0')->pluck('shortcode');
        for ($i=0; $i < sizeof($_query); $i++) {
            $this->getCommentByPost($label,$_query[$i]);
        }
        return redirect('/');
    }

    public function getCommentByPost($label, $shortcode,$endcursor = null){
        $handlerStack = HandlerStack::create(new CurlHandler());
        $handlerStack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));
        $client = new Client(array('handler' => $handlerStack));
        $endpoint = null;
        $nextPage = null;
        do {
            if($endpoint){
                $res = $client->get('https://www.instagram.com/graphql/query/?query_hash=33ba35852cb50da46f5b5e889df7d159&variables={"shortcode":"'.$shortcode.'","first":20,"after":"'.$endpoint.'"}');
                $resobj = json_decode(json_encode(json_decode($res->getBody(),TRUE)));        
                $data = new comment();
                foreach ($resobj->data->shortcode_media->edge_media_to_comment->edges as $_key => $_value) {
                    $object = new \stdClass;            
                    $object->comment_shortcode = $shortcode;
                    $object->query_label = $label;
                    $object->comment_id = $_value->node->id; 
                    $object->comment_owner = $_value->node->owner->id;
                    $object->comment = $_value->node->text;  
                    $data->insert($object);              
                }
                $hasNext = $resobj->data->shortcode_media->edge_media_to_comment->page_info;
                $endpoint = $hasNext->end_cursor;
                $nextPage = $hasNext->has_next_page;
            }else{
                $res = $client->get('https://www.instagram.com/p/'.$shortcode.'/?__a=1');
                $resobj = json_decode(json_encode(json_decode($res->getBody(),TRUE)));        
                $data = new comment();
                foreach ($resobj->graphql->shortcode_media->edge_media_to_comment->edges as $_key => $_value) {
                    $object = new \stdClass;            
                    $object->comment_shortcode = $shortcode;
                    $object->query_label = $label;
                    $object->comment_id = $_value->node->id; 
                    $object->comment_owner = $_value->node->owner->id;
                    $object->comment = $_value->node->text;  
                    $data->insert($object);              
                }
                $hasNext = $resobj->graphql->shortcode_media->edge_media_to_comment->page_info;
                $endpoint = $hasNext->end_cursor;
                $nextPage = $hasNext->has_next_page;
            }
        } while ($nextPage != null);
    }

    public function retryDecider()
    {
        return function (
            $retries,
            GuzzleRequest $request,
            GuzzleResponse $response = null,
            RequestException $exception = null
        ) {
            // Limit the number of retries to 5
            if ($retries >= 5) {
                return false;
            }

            // Retry connection exceptions
            if ($exception instanceof ConnectException) {
                return true;
            }

            if ($response) {
                // Retry on server errors
                if ($response->getStatusCode() >= 500 ) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * delay 1s 2s 3s 4s 5s
     *
     * @return Closure
     */
    public function retryDelay()
    {
        return function ($numberOfRetries) {
            return 1000 * $numberOfRetries;
        };
    }    
}
