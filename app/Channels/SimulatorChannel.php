<?php
namespace App\Channels;
use App\Contracts\Channel; use App\Models\User; use Illuminate\Support\Facades\DB;
class SimulatorChannel implements Channel {
  public function send(User $to, string $text, array $buttons = []): void {
    DB::table('messages')->insert(['user_id'=>$to->id,'direction'=>'out','body'=>$text,
      'buttons'=>json_encode(array_map(fn($b)=>is_array($b)?$b:[$b,$b],$buttons)),'created_at'=>now(),'updated_at'=>now()]);
  }
}
