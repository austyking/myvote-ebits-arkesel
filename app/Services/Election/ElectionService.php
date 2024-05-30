<?php

namespace App\Services\Election;

use App\Models\Election;

class ElectionService
{
    public function isElectionActive(string $ussdCode): bool|Election
    {
        $election = Election::where('ussd_code', $ussdCode)->ongoing ()->first ();
        return $election ?? false;
    }
}
