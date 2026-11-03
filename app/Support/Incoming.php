<?php
namespace App\Support;
/** Pesan masuk netral-kanal. kind: text | button | photo */
final class Incoming { public function __construct(public string $kind, public string $value = '') {} }
