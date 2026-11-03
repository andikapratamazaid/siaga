<?php
use Illuminate\Database\Migrations\Migration; use Illuminate\Database\Schema\Blueprint; use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void {
    Schema::table('users', function (Blueprint $t) { $t->string('role')->default('tukang'); $t->string('zone')->nullable(); $t->string('phone')->nullable()->unique(); $t->unsignedBigInteger('supervisor_id')->nullable(); });
    $mk = fn($n,$f)=>Schema::create($n, function (Blueprint $t) use ($f) { $t->id(); $f($t); $t->timestamps(); });
    $mk('messages', function($t){ $t->foreignId('user_id'); $t->string('direction'); $t->text('body'); $t->json('buttons')->nullable(); });
    $mk('conversation_states', function($t){ $t->foreignId('user_id')->unique(); $t->json('state')->nullable(); });
    $mk('checklist_templates', function($t){ $t->string('type')->unique(); $t->string('name'); });
    $mk('checklist_items', function($t){ $t->foreignId('template_id'); $t->string('text'); $t->boolean('critical')->default(false); $t->string('condition')->nullable(); });
    $mk('checklist_runs', function($t){ $t->foreignId('user_id'); $t->foreignId('template_id'); $t->string('weather')->nullable(); $t->string('status')->default('berjalan'); });
    $mk('checklist_answers', function($t){ $t->foreignId('run_id'); $t->foreignId('item_id'); $t->boolean('passed'); $t->string('photo')->nullable(); });
    $mk('tickets', function($t){ $t->string('kind'); $t->string('level'); $t->text('description'); $t->string('zone')->nullable(); $t->foreignId('reporter_id'); $t->foreignId('pic_id'); $t->timestamp('due_at')->nullable(); $t->string('status')->default('terbuka'); $t->boolean('hold')->default(false); $t->string('fix_photo')->nullable(); });
    $mk('permits', function($t){ $t->string('code')->unique(); $t->string('type'); $t->foreignId('pj_id'); $t->unsignedTinyInteger('stage')->default(0); $t->string('status')->default('menunggu'); $t->timestamp('valid_until')->nullable(); });
    $mk('documents', function($t){ $t->string('name'); $t->text('body'); });
    $mk('doc_gaps', function($t){ $t->foreignId('user_id'); $t->text('question'); });
    $mk('field_reports', function($t){ $t->foreignId('user_id'); $t->date('date'); $t->json('data'); });
    $mk('daily_reports', function($t){ $t->date('date')->unique(); $t->string('status')->default('DRAF'); $t->json('payload')->nullable(); $t->unsignedBigInteger('approved_by')->nullable(); $t->timestamp('locked_at')->nullable(); });
    $mk('activity_logs', function($t){ $t->foreignId('user_id'); $t->string('role'); $t->string('action'); });
    $mk('case_playbooks', function($t){ $t->string('code')->unique(); $t->string('title'); $t->text('keywords'); $t->string('urgency'); $t->boolean('stop_work')->default(false); $t->json('steps'); });
  }
};
