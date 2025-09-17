<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato Completado - {{ $contractTemplate->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .contract-content {
            background-color: white;
            padding: 40px;
            margin: 20px 0;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            min-height: 800px;
        }
        @media print {
            .no-print { display: none; }
            .contract-content { box-shadow: none; margin: 0; }
            body { background-color: white; }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row">
            <div class="col-12 text-center mb-4 no-print">
                @if(session('info'))
                    <div class="alert alert-info">{{ session('info') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <h2>Contrato {{ $sale->contract_signed_date ? 'Firmado' : 'Completado' }}</h2>
                @if($sale->contract_signed_date)
                    <p class="text-success">
                        Su contrato fue firmado el {{ $sale->contract_signed_date->format('d/m/Y H:i') }}.
                        Está pendiente de aprobación.
                    </p>
                    <div class="alert alert-warning">
                        <i class="bi bi-lock"></i> Este contrato ya ha sido firmado y no puede modificarse.
                    </div>
                @else
                    <p class="text-success">Su contrato ha sido completado exitosamente. Está pendiente de aprobación.</p>
                @endif

                <div class="mt-3">
                    <button onclick="window.print()" class="btn btn-primary me-2">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                    <button onclick="downloadPDF()" class="btn btn-outline-secondary">
                        <i class="bi bi-download"></i> Descargar PDF
                    </button>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <div class="contract-content">
                    {!! $contractHtml !!}
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            const element = document.querySelector('.contract-content');
            const opt = {
                margin: 1,
                filename: 'contrato_{{ $sale->id }}.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
            };

            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>
</html>