<?php
namespace App\Services;
use App\Models\User; use Illuminate\Support\Facades\DB;
class ActivityLog { public static function record(User $u, string $action): void {
  DB::table('activity_logs')->insert(['user_id'=>$u->id,'role'=>$u->role,'action'=>$action,'created_at'=>now(),'updated_at'=>now()]); } }
