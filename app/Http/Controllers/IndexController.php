<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\igdata;
use App\comment;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;

class IndexController extends Controller
{
    //
    public function index(){
        
        $client = new Client();
        $res = $client->get('https://www.instagram.com/p/BeXJvpCBiXT/?__a=1');
        $resobj = json_decode(json_encode(json_decode($res->getBody(),TRUE)));        
        $data = new comment();
        $object = new \stdClass;            
                 
        foreach ($resobj->graphql->shortcode_media->edge_media_to_comment->edges as $_key => $_value) {
            $object->comment_shortcode = $resobj->graphql->shortcode_media->shortcode;
            $object->comment_id = $_value->node->id; 
            $object->comment_owner = $_value->node->owner->id;
            $object->comment = $_value->node->text;  
            $data->insert($object);              
        }      
         
        return view('welcome');
    }

    public function getHashtag(Request $request){
        $this->data['datasuccess'] = 'SUCCESS';
        $res = json_decode(json_encode($request->all()));
        $data = new igdata();
        foreach ($res->graphql->hashtag->edge_hashtag_to_media->edges as $key => $value) {
            $object = new \stdClass;
            $object->query_label = $res->query;
            $object->user = $value->node->owner->id;
            $object->comment_id = $value->node->id; 
            $object->comment_count = $value->node->edge_media_to_comment->count;
            $object->shortcode = $value->node->shortcode;         
            foreach ($value->node->edge_media_to_caption->edges as $_key => $_value) {
                $object->comment = $_value->node->text;                
            }      
            Log::debug(print_r($object,true));
            $data->insert($object);      
        }
        return redirect('/')->with($this->data);
    }

    public function getAllComment(){
        
    }
}
