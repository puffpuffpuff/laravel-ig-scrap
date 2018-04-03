<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\igdata;
use App\comment;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
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

    public function getPostByHashtag($hashtag,$endcursor = null){
        $client = new Client();
        $endcursor ? $res = $client->get('https://www.instagram.com/explore/tags/'.$hashtag.'/?__a=1&max_id='.$endcursor) : $res = $client->get('https://www.instagram.com/explore/tags/'.$hashtag.'/?__a=1');
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
        if($hasNext->has_next_page){
            $this->getPostByHashtag($hashtag,$hasNext->end_cursor);
        }
        
    }

    public function getComment($label){
        $shortcode = igdata::where('query_label',$label)->where('comment_count','!=','0')->pluck('shortcode');
        for ($i=0; $i < sizeof($shortcode); $i++) {
            $this->getCommentByPost($label,$shortcode[$i]);
        }
        return redirect()->back();
    }

    public function getCommentByPost($label, $shortcode,$endcursor = null){
        $client = new Client();
        if($endcursor){
            $res = $client->get('https://www.instagram.com/graphql/query/?query_hash=33ba35852cb50da46f5b5e889df7d159&variables={"shortcode":"'.$shortcode.'","first":20,"after":"'.$endcursor.'"}');
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
            if($hasNext->has_next_page){
                $this->getCommentByPost($label,$shortcode,$hasNext->end_cursor);
            }
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
            if($hasNext->has_next_page){
                $this->getCommentByPost($label,$shortcode,$hasNext->end_cursor);
            }
        }
        
    }
    
}
