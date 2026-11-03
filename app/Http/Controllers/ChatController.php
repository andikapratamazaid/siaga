<?php
namespace App\Http\Controllers;
use App\Models\User; use App\Services\FlowEngine; use App\Support\Incoming; use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
class ChatController extends Controller { // simulator chat
  public function send(Request $r, User $user, FlowEngine $e) {
    DB::table('messages')->insert(['user_id'=>$user->id,'direction'=>'in','body'=>$r->value ?? '','created_at'=>now(),'updated_at'=>now()]);
    $e->handle($user, new Incoming($r->kind, (string)$r->value)); return $this->messages($user); }
  public function messages(User $user) { return DB::table('messages')->where('user_id',$user->id)->orderBy('id')->get(); }
}
