<div class="modal fade" id="apiSearchModal" tabindex="-1" aria-labelledby="apiSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="apiSearchModalLabel">Cari Data Eksternal (<span id="modal_api_source"></span> Tahun <span id="modal_api_tahun"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="apiDataTable" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr id="apiTableHeader">
                                <!-- Headers akan diisi oleh JS -->
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data akan diisi oleh DataTables -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>