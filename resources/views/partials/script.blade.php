<script src="https://unpkg.com/paho-mqtt/mqttws31.min.js"></script>

{{-- <script>
    (function() {
        const exportAuditUrl = @json(route('audit-log.client-export'));
        const csrfToken = @json(csrf_token());

        function sendClientExportAudit(payload) {
            return fetch(exportAuditUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                keepalive: true,
                body: JSON.stringify(payload),
            }).catch(() => {});
        }

        function wrapDataMasukExportAudit() {
            if (window.__dataMasukAuditWrapped) return;
            if (typeof window.dataMasukManager !== 'function') return;

            const originalFactory = window.dataMasukManager;
            window.dataMasukManager = function(...args) {
                const state = originalFactory.apply(this, args);
                if (!state || typeof state.exportToExcel !== 'function') return state;

                const originalExport = state.exportToExcel.bind(state);
                state.exportToExcel = function(...exportArgs) {
                    const hasData = Array.isArray(this.tableData) && this.tableData.length > 0;
                    const loggerId = this.filters?.logger_id ? String(this.filters.logger_id) : '';
                    const tanggal = this.filters?.tanggal ? String(this.filters.tanggal) : '';

                    const result = originalExport(...exportArgs);

                    if (hasData && loggerId && tanggal) {
                        sendClientExportAudit({
                            module: 'Data Masuk',
                            target: `Logger: ${loggerId}`,
                            description: `Export CSV data masuk tanggal ${tanggal} untuk logger ${loggerId}.`,
                            metadata: {
                                source: 'data-masuk.client-csv',
                                logger_id: loggerId,
                                tanggal: tanggal,
                                total_rows: this.tableData.length,
                            },
                        });
                    }

                    return result;
                };

                return state;
            };

            window.__dataMasukAuditWrapped = true;
        }

        document.addEventListener('DOMContentLoaded', wrapDataMasukExportAudit);
    })();
</script> --}}
