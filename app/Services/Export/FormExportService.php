<?php

namespace App\Services\Export;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FormExportService
{
    /**
     * Get all unique field names ever used in this form's submissions.
     * Returns array with field_name => label mapping.
     *
     * This method collects:
     * 1. Current form fields from form_fields table
     * 2. Historical fields from submission_data JSON that may no longer exist
     *
     * @param int $formId
     * @return array<string, string>
     */
    public function getAllUniqueFieldNames(int $formId): array
    {
        $form = Form::with('fields')->findOrFail($formId);

        // 1. Start with current fields (field_name => label)
        $fieldMap = [];
        foreach ($form->fields as $field) {
            $fieldMap[$field->field_name] = $field->label;
        }

        // 2. Get all unique keys from submission_data across all submissions
        $submissions = FormSubmission::where('form_id', $formId)
            ->select('submission_data')
            ->get();

        foreach ($submissions as $submission) {
            if (is_array($submission->submission_data)) {
                foreach (array_keys($submission->submission_data) as $key) {
                    if (!isset($fieldMap[$key])) {
                        // Historical field - use formatted key as label
                        $fieldMap[$key] = $this->formatFieldNameAsLabel($key);
                    }
                }
            }
        }

        return $fieldMap;
    }

    /**
     * Convert field_name slug to readable label.
     * Example: "como_conociste_a_fede" => "Como conociste a fede (histórico)"
     *
     * @param string $fieldName
     * @return string
     */
    private function formatFieldNameAsLabel(string $fieldName): string
    {
        return ucfirst(str_replace('_', ' ', $fieldName)) . ' (histórico)';
    }

    /**
     * Get submissions with filters applied.
     *
     * @param int $formId
     * @param array $filters ['date_from' => ?, 'date_to' => ?, 'status' => ?, 'user_id' => ?]
     * @return Collection
     */
    public function getFilteredSubmissions(int $formId, array $filters = []): Collection
    {
        $query = FormSubmission::where('form_id', $formId)
            ->with(['user', 'lead'])
            ->orderByDesc('submitted_at');

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('submitted_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('submitted_at', '<=', $filters['date_to']);
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter by user (Enviado por)
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->get();
    }

    /**
     * Export submissions to CSV format with filters applied.
     * Uses current form fields only.
     *
     * @param int $formId
     * @param array $filters
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportToCsv(int $formId, array $filters = [])
    {
        $form = Form::with('fields')->findOrFail($formId);
        $submissions = $this->getFilteredSubmissions($formId, $filters);

        $filename = 'respuestas_' . Str::slug($form->name) . '_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($form, $submissions) {
            $file = fopen('php://output', 'w');

            // BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Headers
            $csvHeaders = ['ID', 'Enviado por', 'Lead relacionado', 'Estado', 'Fecha de envio'];
            foreach ($form->fields as $field) {
                $csvHeaders[] = $field->label;
            }
            fputcsv($file, $csvHeaders);

            // Data rows
            foreach ($submissions as $submission) {
                $row = [
                    $submission->id,
                    $submission->user ? $submission->user->name : 'N/A',
                    $submission->lead ? $submission->lead->nombre : 'N/A',
                    FormSubmission::getStatuses()[$submission->status] ?? $submission->status,
                    $submission->submitted_at ? $submission->submitted_at->format('Y-m-d H:i') : 'N/A',
                ];

                foreach ($form->fields as $field) {
                    $value = $submission->getFieldValue($field->field_name, '');
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    $row[] = $value;
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export complete submissions to Excel format.
     * Includes ALL historical fields from all submissions.
     *
     * @param int $formId
     * @param array $filters
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToExcel(int $formId, array $filters = [])
    {
        $form = Form::findOrFail($formId);
        $submissions = $this->getFilteredSubmissions($formId, $filters);
        $allFields = $this->getAllUniqueFieldNames($formId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Respuestas');

        // --- HEADER ROW ---
        $baseHeaders = ['ID', 'Enviado por', 'Lead relacionado', 'Estado', 'Fecha de envio'];
        $col = 1;

        // Base headers
        foreach ($baseHeaders as $header) {
            $sheet->setCellValue([$col, 1], $header);
            $col++;
        }

        // Dynamic field headers (ALL unique fields)
        $fieldOrder = array_keys($allFields);
        foreach ($allFields as $fieldName => $label) {
            $sheet->setCellValue([$col, 1], $label);
            $col++;
        }

        // Style header row
        $lastCol = $col - 1;
        $headerRange = 'A1:' . $this->columnLetter($lastCol) . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // --- DATA ROWS ---
        $row = 2;
        foreach ($submissions as $submission) {
            $col = 1;

            // Base columns
            $sheet->setCellValue([$col++, $row], $submission->id);
            $sheet->setCellValue([$col++, $row], $submission->user ? $submission->user->name : 'N/A');
            $sheet->setCellValue([$col++, $row], $submission->lead ? $submission->lead->nombre : 'N/A');
            $sheet->setCellValue([$col++, $row], FormSubmission::getStatuses()[$submission->status] ?? $submission->status);
            $sheet->setCellValue([$col++, $row], $submission->submitted_at ? $submission->submitted_at->format('Y-m-d H:i') : 'N/A');

            // Dynamic field values - iterate ALL fields
            foreach ($fieldOrder as $fieldName) {
                $value = $submission->getFieldValue($fieldName, '');
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $sheet->setCellValue([$col++, $row], $value);
            }

            $row++;
        }

        // Auto-size columns (with max width limit)
        foreach (range(1, $lastCol) as $colNum) {
            $sheet->getColumnDimension($this->columnLetter($colNum))->setAutoSize(true);
        }

        // Add borders to data
        if ($row > 2) {
            $dataRange = 'A1:' . $this->columnLetter($lastCol) . ($row - 1);
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
        }

        // Freeze first row
        $sheet->freezePane('A2');

        // Generate file
        $filename = 'respuestas_completo_' . Str::slug($form->name) . '_' . date('Y-m-d_His') . '.xlsx';
        $tempPath = storage_path('app/temp/' . $filename);

        // Ensure temp directory exists
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Convert column number to Excel letter (1=A, 27=AA, etc.)
     *
     * @param int $columnNumber
     * @return string
     */
    private function columnLetter(int $columnNumber): string
    {
        $letter = '';
        while ($columnNumber > 0) {
            $temp = ($columnNumber - 1) % 26;
            $letter = chr($temp + 65) . $letter;
            $columnNumber = (int)(($columnNumber - $temp - 1) / 26);
        }
        return $letter;
    }

    /**
     * Get list of users who have submitted to this form.
     * Used for the "Enviado por" filter dropdown.
     *
     * @param int $formId
     * @return Collection
     */
    public function getSubmitterUsers(int $formId): Collection
    {
        $userIds = FormSubmission::where('form_id', $formId)
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        return User::whereIn('id', $userIds)->orderBy('name')->get(['id', 'name']);
    }

    /**
     * Get chart data for all fields based on submissions.
     * Respects filters. Used for dynamic charts view.
     *
     * @param int $formId
     * @param array $filters
     * @return array
     */
    public function getChartsData(int $formId, array $filters = []): array
    {
        $form = Form::with('fields')->findOrFail($formId);
        $submissions = $this->getFilteredSubmissions($formId, $filters);

        $chartsData = [];

        foreach ($form->fields as $field) {
            $chartData = $this->generateChartDataForField($field, $submissions);
            if ($chartData) {
                $chartsData[] = $chartData;
            }
        }

        return $chartsData;
    }

    /**
     * Generate chart data for a specific field.
     * Now includes ALL field types - text fields show top 10 responses.
     *
     * @param \App\Models\FormField $field
     * @param Collection $submissions
     * @return array|null
     */
    private function generateChartDataForField($field, Collection $submissions): ?array
    {
        // Count responses by value
        $counts = [];
        $respondedCount = 0;

        foreach ($submissions as $submission) {
            $value = $submission->getFieldValue($field->field_name);

            if ($field->field_type === 'checkbox' && is_array($value)) {
                // For checkbox, count each selected option
                if (!empty($value)) {
                    $respondedCount++;
                    foreach ($value as $option) {
                        $counts[$option] = ($counts[$option] ?? 0) + 1;
                    }
                }
            } elseif ($value !== null && $value !== '') {
                $respondedCount++;
                // For text fields, truncate long values for display
                $displayValue = $value;
                if (in_array($field->field_type, ['text', 'textarea', 'email'])) {
                    $displayValue = mb_strlen($value) > 30 ? mb_substr($value, 0, 30) . '...' : $value;
                }
                $counts[$displayValue] = ($counts[$displayValue] ?? 0) + 1;
            }
        }

        // Skip if no data
        if (empty($counts)) {
            return null;
        }

        // Sort by count descending for better visualization
        arsort($counts);

        // For text-based fields, limit to top 10 responses
        if (in_array($field->field_type, ['text', 'textarea', 'email', 'date'])) {
            $counts = array_slice($counts, 0, 10, true);
        }

        return [
            'field_id' => $field->id,
            'label' => $field->label,
            'type' => $field->field_type,
            'chart_type' => $this->getChartTypeForField($field->field_type),
            'labels' => array_keys($counts),
            'data' => array_values($counts),
            'total_responses' => $submissions->count(),
            'responded_count' => $respondedCount,
        ];
    }

    /**
     * Determine the chart type based on field type.
     *
     * @param string $fieldType
     * @return string
     */
    private function getChartTypeForField(string $fieldType): string
    {
        return match ($fieldType) {
            'select', 'radio' => 'doughnut',
            'checkbox' => 'bar_horizontal',
            'text', 'textarea', 'email' => 'bar_horizontal',
            'scale', 'rating', 'number' => 'bar',
            'date' => 'bar',
            default => 'bar'
        };
    }
}
