<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Channel; use App\Channels\{SimulatorChannel,WhatsAppChannel};
use App\Services\Ai\{AiAssistant,ClaudeAssistant,KeywordAssistant};
class SiagaServiceProvider extends ServiceProvider {
  public function register(): void {
    $this->app->bind(Channel::class, fn()=>config('siaga.channel')==='whatsapp'?new WhatsAppChannel:new SimulatorChannel);
    $this->app->bind(AiAssistant::class, fn()=>config('siaga.ai_key')?new ClaudeAssistant:new KeywordAssistant);
  }
}
