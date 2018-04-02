<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class igdata extends Model
{
    //
    public function insert($request){
        $data = new igdata();
        $check = igdata::where('comment_id',$request->comment_id)->first();
        if(empty($check)){
            $data->comment_id = $request->comment_id;
            $data->query_label = $request->query_label;
            $data->user = $request->user;
            $data->comment = $request->comment;
            $data->comment_count = $request->comment_count;
            $data->shortcode = $request->shortcode;
            $data->save();
        }
    }
}
