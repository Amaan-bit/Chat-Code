<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Chat;
use Illuminate\Support\Facades\Hash;
use DB;
use DateTime;
use Validator;
use Carbon\Carbon;

class ChatController extends Controller
{
    
    public function sent_message(Request $request)
    {
        if(auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            if(!empty($user)){
                if($request->message!='' && $request->user_id!=''){
                    $chat = New Chat;
                    $chat->sender_id = $user->id;
                    $chat->reciever_id = $request->user_id;
                    $chat->message = $request->message;
                    $chat->save();
                    return response()->json(['status' => 200, 'msg_title' => "Success",'msg_body'=>'Message Sent Successfully']);
                }else{
                     return response()->json(['status' => 201, 'message' => "Fields are missing!"]);
                }
            }else{
                return response()->json(['status' => 502, 'message' => "Token expaired !"]);
            }
        }else{
            return response()->json(['status' => 502, 'message' => "Credentials do not match" ]);
        }
    }
    
    public function sent_image(Request $request)
    {
        if(auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            if(!empty($user)){
                if($request->hasfile('image') && $request->user_id!=''){
                    $file = $request->file('image');
                    $extension = $file->getClientOriginalExtension();
                    $filename = time().'.'.$extension;
                    
                    $path = 'public/uploads/chat/'.$filename;
                    if(File::exists($path)){
                        File::delete($path);
                    }
                    $file->move('public/uploads/chat/',$filename);
                    $chat = New Chat;
                    $chat->image = 'uploads/chat/'.$filename;
                    $chat->sender_id = $user->id;
                    $chat->reciever_id = $request->user_id;
                    $chat->save();
                    
                    return response()->json(['status' => 200, 'msg_title' => "Success",'msg_body'=>'Message Sent Successfully']);
                }else{
                     return response()->json(['status' => 201, 'message' => "Fields are missing!"]);
                }
            }else{
                return response()->json(['status' => 502, 'message' => "Token expaired !"]);
            }
        }else{
            return response()->json(['status' => 502, 'message' => "Credentials do not match" ]);
        }
    }
    
    public function get_message(Request $request)
    {
        if(auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            if(!empty($user)){
                if($request->user_id!=''){
                    $messages = DB::table('chat')
                        ->where(function ($query) use ($request, $user) {
                            $query->where('sender_id', $user->id)
                                  ->where('reciever_id', $request->user_id);
                        })
                        ->orWhere(function ($query) use ($request, $user) {
                            $query->where('sender_id', $request->user_id)
                                  ->where('reciever_id', $user->id);
                        })->latest()->get();
                    $data = [];
                    $url = "https://livestockhealth.in/public/";
                    foreach($messages as $message){
                        $reciever = User::find($message->sender_id);
                        $new_data = [
                            'first_person'=>($message->sender_id==$user->id) ? true : false,
                            'message'=> $message->message,
                            'image'=>($message->image!=null) ? $url.$message->image : '',
                            'name'=> $reciever->name,
                            'read'=>($message->status==1) ? true : false,
                            'time'=>Carbon::parse($message->created_at)->format('d M h:i A')
                        ];
                        array_push($data,$new_data);
                    }
                    return response()->json(['status' => 200, 'msg_title' => "Success",'msg_body'=>'Message Fetched Successfully','data'=>$data]);
                }else{
                     return response()->json(['status' => 201, 'message' => "Fields are missing!"]);
                }
            }else{
                return response()->json(['status' => 502, 'message' => "Token expaired !"]);
            }
        }else{
            return response()->json(['status' => 502, 'message' => "Credentials do not match" ]);
        }
    }
    
    public function read_message()
    {
        if(auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            if(!empty($user)){
                $chat = Chat::where('reciever_id',$user->id)->update([
                    'status'=>1
                ]);
                return response()->json(['status' => 200, 'msg_title' => "Success",'msg_body'=>'Message Read Successfully']);
            }else{
                return response()->json(['status' => 502, 'message' => "Token expaired !"]);
            }
        }else{
            return response()->json(['status' => 502, 'message' => "Credentials do not match" ]);
        }
    }
    
    public function get_chat()
    {
        if(auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            if(!empty($user)){
                $chats = Chat::where('reciever_id',$user->id)->orWhere('sender_id',$user->id)->latest()->get();
                $data = [];
                $url = "https://livestockhealth.in/public/";
                $ids = [];
                foreach($chats as $chat){
                    if($chat->sender_id==$user->id){
                        $id = $chat->reciever_id;
                    }elseif($chat->reciever_id==$user->id){
                         $id = $chat->sender_id;
                    }
                    $reciever = User::find($id);
                    
                    $new_data = [
                        'id'=>$id,
                        'name'=> $reciever->name,
                        'message'=> $chat->message,
                        'time'=>Carbon::parse($chat->created_at)->format('d M h:i A')
                    ];
                    if(!in_array($id,$ids)){
                        array_push($data,$new_data);
                    }
                     array_push($ids,$id);
                }
                return response()->json(['status' => 200, 'msg_title' => "Success",'msg_body'=>'Chat Fetched Successfully','data'=>$data]);
            }else{
                return response()->json(['status' => 502, 'message' => "Token expaired !"]);
            }
        }else{
            return response()->json(['status' => 502, 'message' => "Credentials do not match" ]);
        }
    }

}


