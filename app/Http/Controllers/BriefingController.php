<?php
namespace App\Http\Controllers;
use App\Models\User; use App\Services\BriefingService; use Illuminate\Support\Facades\DB;
/** Pemicu manual briefing untuk demo/simulator. Di produksi briefing dikirim scheduler. */
class BriefingController extends Controller {
  public function __invoke(User $user, BriefingService $b) {
    $b->morning($user);
    return DB::table('messages')->where('user_id',$user->id)->orderBy('id')->get();
  }
}
