<?php
namespace App\Contracts;
use App\Models\User;
/** Satu-satunya pintu keluar pesan. $buttons: ['Ya', ['cl:alat','Inspeksi Alat']] */
interface Channel { public function send(User $to, string $text, array $buttons = []): void; }
