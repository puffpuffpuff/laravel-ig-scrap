<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class comment extends Model
{
    //
    public function insert($request){
        $data = new comment();
        $check = comment::where('comment_id',$request->comment_id)->first();
        if(empty($check)){
            $data->comment_id = $request->comment_id;
            $data->comment_owner = $request->comment_owner;
            $data->comment_shortcode = $request->comment_shortcode;
            $data->comment = $request->comment;
            $data->save();
        }
    }
}
