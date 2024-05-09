<?php

namespace App\Service;

use TCPDF;

class PDF extends TCPDF
{
    function Header()
    {

    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, '©' . date('Y') . ' NoTicket. All rights reserved.', 0, 1, 'C');
    }
}