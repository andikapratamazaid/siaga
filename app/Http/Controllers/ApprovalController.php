<?php
namespace App\Http\Controllers;
use App\Services\ApprovalService; use Illuminate\Http\Request;
class ApprovalController extends Controller { // butuh auth (Breeze/Sanctum); peran dicek di ApprovalService
  public function ticket(Request $r, int $id, ApprovalService $a) { $a->approveTicket($r->user(), $id); return ['ok'=>true]; }
  public function report(Request $r, string $date, ApprovalService $a) { $a->approveDailyReport($r->user(), $date); return ['ok'=>true]; }
}
