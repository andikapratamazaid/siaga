<?php
namespace App\Channels;
use App\Contracts\Channel; use App\Models\User; use Illuminate\Support\Facades\Http;
/** WhatsApp Cloud API (penyedia resmi). Tombol maks 3 -> reply buttons; 4-10 -> list. */
class WhatsAppChannel implements Channel {
  public function send(User $to, string $text, array $buttons = []): void {
    $b = array_map(fn($x)=>is_array($x)?$x:[$x,$x], $buttons);
    $p = ['messaging_product'=>'whatsapp','to'=>$to->phone];
    if (!$b) { $p += ['type'=>'text','text'=>['body'=>$text]]; }
    elseif (count($b) <= 3) { $p += ['type'=>'interactive','interactive'=>['type'=>'button','body'=>['text'=>$text],
      'action'=>['buttons'=>array_map(fn($x)=>['type'=>'reply','reply'=>['id'=>$x[0],'title'=>mb_substr($x[1],0,20)]],$b)]]]; }
    else { $p += ['type'=>'interactive','interactive'=>['type'=>'list','body'=>['text'=>$text],
      'action'=>['button'=>'Pilih','sections'=>[['title'=>'Menu','rows'=>array_map(fn($x)=>['id'=>$x[0],'title'=>mb_substr($x[1],0,24)],array_slice($b,0,10))]]]]]; }
    Http::withToken(config('siaga.wa.token'))->post('https://graph.facebook.com/v21.0/'.config('siaga.wa.phone_id').'/messages', $p)->throw();
  }
}
