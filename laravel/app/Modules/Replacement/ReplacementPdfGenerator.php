<?php

namespace App\Modules\Replacement;

use App\Models\FormSubmission;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Str;

class ReplacementPdfGenerator
{
    public function __construct(private readonly FilesystemFactory $filesystem) {}

    public function generate(FormSubmission $submission): string
    {
        $payload = $submission->payload_json ?? [];
        $lines = [
            'Solicitud de reemplazo',
            'Envio #'.$submission->id,
            'Fecha: '.($submission->submitted_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i')),
            '',
            'Funcionario titular: '.data_get($payload, 'subject_employee.full_name', 'No informado'),
            'RUT titular: '.trim((string) data_get($payload, 'subject_employee.rut', '').'-'.data_get($payload, 'subject_employee.dv', '')),
            'Reemplazante: '.data_get($payload, 'replacement.full_name', 'No informado'),
            'Origen reemplazante: '.(data_get($payload, 'replacement.is_external') ? 'Externo' : 'Interno'),
            'Tipo ausencia: '.data_get($payload, 'absence.type_name', 'No informado'),
            'Detalle ausencia: '.data_get($payload, 'absence.detail', 'Sin detalle'),
            'Inicio: '.data_get($payload, 'period.start_date', 'No informado'),
            'Termino: '.data_get($payload, 'period.end_date', 'No informado'),
            'Motivo: '.data_get($payload, 'motivo', 'No informado'),
        ];

        $content = $this->buildPdf($lines);
        $path = 'forms/replacement/submission-'.$submission->id.'-'.Str::slug((string) data_get($payload, 'replacement.full_name', 'reemplazo')).'.pdf';

        $this->filesystem->disk('local')->put($path, $content);

        return $path;
    }

    private function buildPdf(array $lines): string
    {
        $escapedLines = array_map(fn (string $line) => $this->escapePdfText($line), $lines);
        $contentStream = "BT\n/F1 12 Tf\n50 780 Td\n14 TL\n";

        foreach ($escapedLines as $index => $line) {
            $contentStream .= ($index === 0 ? '' : 'T*' . "\n") . '(' . $line . ") Tj\n";
        }

        $contentStream .= "ET";
        $length = strlen($contentStream);

        $objects = [];
        $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $objects[] = '2 0 obj << /Type /Pages /Count 1 /Kids [3 0 R] >> endobj';
        $objects[] = '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj';
        $objects[] = "4 0 obj << /Length {$length} >> stream\n{$contentStream}\nendstream endobj";
        $objects[] = '5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object."\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= 'xref' . "\n";
        $pdf .= '0 ' . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= 'trailer << /Size ' . (count($objects) + 1) . ' /Root 1 0 R >>' . "\n";
        $pdf .= 'startxref' . "\n{$xrefPosition}\n%%EOF";

        return $pdf;
    }

    private function escapePdfText(string $value): string
    {
        return str_replace(
            ['\\', '(', ')', "\r", "\n"],
            ['\\\\', '\\(', '\\)', '', ' '],
            $value,
        );
    }
}
