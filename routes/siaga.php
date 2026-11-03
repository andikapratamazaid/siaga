<?php
use Illuminate\Support\Facades\Route; use App\Http\Controllers\{ChatController,WebhookController,ApprovalController,BriefingController,DashboardController};
Route::prefix('sim')->group(function () { Route::get('{user}/messages',[ChatController::class,'messages']); Route::post('{user}/send',[ChatController::class,'send']); Route::post('{user}/briefing',BriefingController::class); });
Route::get('wa/webhook',[WebhookController::class,'verify']); Route::post('wa/webhook',[WebhookController::class,'receive']);
Route::middleware('auth')->group(function () { Route::post('approvals/ticket/{id}',[ApprovalController::class,'ticket']); Route::post('approvals/report/{date}',[ApprovalController::class,'report']); Route::get('dashboard/summary',[DashboardController::class,'summary']); });
