<!-- Modal para información de venta -->
<div class="modal fade" id="saleInfoModal" tabindex="-1" aria-labelledby="saleInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Información del Cliente -->
                    <div class="col-12"><h6 class="text-primary border-bottom pb-2 mb-3">Información del Cliente</h6></div>
                    <div class="col-md-6 mb-2"><strong>Nombre:</strong> <span id="modal-nombre"></span></div>
                    <div class="col-md-6 mb-2"><strong>Apellido:</strong> <span id="modal-apellido"></span></div>
                    <div class="col-md-6 mb-2"><strong>Email:</strong> <span id="modal-email"></span></div>
                    <div class="col-md-6 mb-2"><strong>Teléfono:</strong> <span id="modal-telefono"></span></div>
                    <div class="col-md-6 mb-2"><strong>ID Personal:</strong> <span id="modal-identificacion"></span></div>
                    <div class="col-md-6 mb-2"><strong>Domicilio:</strong> <span id="modal-domicilio"></span></div>

                    <!-- Información de Pago -->
                    <div class="col-12 mt-3"><h6 class="text-primary border-bottom pb-2 mb-3">Información de Pago</h6></div>
                    <div class="col-md-6 mb-2"><strong>Método de Pago:</strong> <span id="modal-metodo-pago"></span></div>
                    <div class="col-md-6 mb-2"><strong>Tipo de Acuerdo:</strong> <span id="modal-tipo-acuerdo"></span></div>
                    <div class="col-md-6 mb-2"><strong>Tipo de Contrato:</strong> <span id="modal-tipo-contrato"></span></div>

                    <!-- Información del Contrato -->
                    <div class="col-12 mt-3"><h6 class="text-primary border-bottom pb-2 mb-3">Información del Contrato</h6></div>
                    <div class="col-md-6 mb-2"><strong>Contrato:</strong> <span id="modal-contrato"></span></div>
                    <div class="col-md-6 mb-2"><strong>Estado del Contrato:</strong> <span id="modal-contrato-estado"></span></div>
                    <div class="col-12 mb-2"><strong>Forma de Pago (Contrato):</strong> <span id="modal-forma-pago"></span></div>
                    <div class="col-md-6 mb-2"><strong>Fecha de Firma:</strong> <span id="modal-fecha-firma"></span></div>

                    <!-- Comentarios -->
                    <div class="col-12 mt-3"><h6 class="text-primary border-bottom pb-2 mb-3">Comentarios/Aclaraciones</h6></div>
                    <div class="col-12 mb-3"><span id="modal-comentarios"></span></div>

                    <!-- Acciones -->
                    <div class="mt-3 text-end">
                        <a id="btnDescargarComprobante" href="#" target="_blank" class="btn btn-outline-success">
                            <i class="bi bi-download me-1"></i> Descargar Comprobante de Pago
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>