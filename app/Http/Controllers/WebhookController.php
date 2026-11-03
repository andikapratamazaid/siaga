<?php
namespace App\Http\Controllers;
use App\Models\User; use App\Services\FlowEngine; use App\Support\Incoming; use Illuminate\Http\Request;
class WebhookController extends Controller { // WhatsApp Cloud API
  public function verify(Request $r) { abort_unless($r->query('hub_verify_token') === config('siaga.wa.verify'), 403); return response($r->query('hub_challenge')); }
  public function receive(Request $r, FlowEngine $e) {
    $m = data_get($r->all(), 'entry.0.changes.0.value.messages.0'); if (!$m) return response('ok');
    $u = User::where('phone', $m['from'])->first(); if (!$u) return response('ok');
    $in = match ($m['type']) {
      'text' => new Incoming('text', $m['text']['body']),
      'image' => new Incoming('photo', $m['image']['id']),
      'interactive' => new Incoming('button', data_get($m,'interactive.button_reply.id', data_get($m,'interactive.list_reply.id'))),
      default => null, // audio/voice note: transkripsi di tahap berikutnya
    };
    if ($in) $e->handle($u, $in); return response('ok');
  }
}
