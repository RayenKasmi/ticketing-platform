<?php

namespace App\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use TCPDF;
use App\Entity\Ticket;
use App\Service\PDF;



class TicketGeneratorService
{
    private QrCodeGeneratorService $qrCodeGenerator;
    private KernelInterface $kernel;


    public function __construct(QrCodeGeneratorService $qrCodeGenerator, KernelInterface $kernel)
    {
        $this->qrCodeGenerator = $qrCodeGenerator;
        $this->kernel = $kernel;
    }

    public function generateSingleTicket(Ticket $ticket, string $action): string
    {
        $pdf = $this->createPDF();
        $pdf->AddPage();
        $this->addTicketContent($pdf, $ticket);
        return $this->outputPDF($pdf, $action, $ticket->getId());
    }

    public function generateCombinedTickets(array $ticketArray, string $fileName): string
    {
        $pdf = $this->createPDF();
        foreach ($ticketArray as $ticket) {
            $pdf->AddPage();
            $this->addTicketContent($pdf, $ticket);
        }
        return $this->savePDF($pdf, $fileName . '.pdf');
    }

    private function createPDF(): TCPDF
    {
        $pdf = new PDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('NoTicket');
        $pdf->SetAuthor('Someone');
        $pdf->SetTitle('Event Ticket');
        $pdf->SetHeaderData('', 0, '', '');
        $pdf->SetHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->SetFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        return $pdf;
    }

    private function addTicketContent(TCPDF $pdf, Ticket $ticket): void
    {
        $eventName = $ticket->getEvent()->getName();
        $eventDate = $ticket->getEvent()->getEventDate()->format('Y-m-d H:i:s');
        $eventVenue = $ticket->getEvent()->getVenue();
        $purchaseDate = $ticket->getPurchaseDate()->format('Y-m-d H:i:s');
        $buyer = $ticket->getBuyer();
        $buyerName = $buyer->getFirstName() . ' ' . $buyer->getLastName();
        $ticketHolderName = $ticket->getHolderName();
        $price = $ticket->getPrice() / 100;
        $ticketId = $ticket->getId();


        $pdf->SetFillColor(255, 255, 255);

        $margin = 50;
        $pdf->SetTextColor(24, 41, 135);
        $pdf->SetFont('helvetica', 'B', 26);
        $titleWidth = $pdf->GetStringWidth($eventName . ' Ticket');
        $availableWidth = $pdf->getPageWidth() - $margin - $margin;
        $pdf->SetXY($margin, $pdf->GetY());
        if ($titleWidth > $availableWidth) {
            $pdf->MultiCell($availableWidth, 15, $eventName . ' Ticket', 0, 'C', true);
        } else {
            $pdf->Cell($availableWidth, 15, $eventName . ' Ticket', 0, 1, 'C', true);
        }


        $projectDir = $this->kernel->getProjectDir();
        $directory = $projectDir . '/assets';
        $svgPath = $directory . '/' . 'pic.svg';
        $pdf->ImageSVG($svgPath, $x=30, $y=6, $w=25, $h=25, $align='', $palign='', $border=1, $fitonpage=false);

        $pdf->SetFont('helvetica', 'I', 12);
        $companyNameY = $pdf->GetY() ;
        $pdf->SetXY($margin, $companyNameY);
        $pdf->Cell($availableWidth, 10, "NoTicket", 0, 1, 'C');


        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(15);

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(95, 10, 'Event Information', 0, 0, 'L');
        $pdf->Cell(95, 10, '', 0, 1, 'R');
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(95, 10, "Event: $eventName", 0, 1, 'L', true);
        $pdf->Ln(5);
        $pdf->Cell(95, 10, "Date: $eventDate", 0, 1, 'L', true);
        $pdf->Ln(5);
        $pdf->Cell(95, 10, "Venue: $eventVenue", 0, 1, 'L', true);
        $pdf->Ln(5);

        $tmpFile = $this->qrCodeGenerator->generate($ticketId);
        $pdf->Image($tmpFile, 145, $pdf->GetY() - 55, 50);
        $pdf->Ln(10);

        $pdf->SetDrawColor(24, 41, 135);
        $pdf->SetLineWidth(1.5);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(10);

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(95, 10, 'Ticket Information', 0, 0, 'L');
        $pdf->Cell(95, 10, '', 0, 1, 'R');
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(95, 10, "Purchase Date: $purchaseDate", 0, 1, 'L', true);
        $pdf->Ln(5);
        $pdf->Cell(95, 10, "Buyer: $buyerName", 0, 1, 'L', true);
        $pdf->Ln(5);
        $pdf->Cell(95, 10, "Ticket Holder: $ticketHolderName", 0, 1, 'L', true);
        $pdf->Ln(5);
        $pdf->Cell(95, 10, "Price: " . $price . " $", 0, 1, 'L', true);
    }

    private function outputPDF(TCPDF $pdf, string $action, $id): string
    {
        $fileName = 'event_ticket_'. $id . '.pdf';
        if ($action === 'view') {
            $pdf->Output($fileName, 'I');
        } elseif ($action === 'download') {
            $pdf->Output($fileName, 'D');
        }
        return $fileName;
    }

    private function savePDF(TCPDF $pdf, string $filename): string
    {
        $projectDir = $this->kernel->getProjectDir();
        $directory = $projectDir . '/public/tickets';
        $filePath = $directory . '/' . $filename;

        $pdf->Output($filePath, 'F');
        return $filePath;
    }
}
