@extends('layouts.plain')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<div class="card card-info card-outline">
					<div class="card-header">
						<h3 class="card-title">
							<i class="fas fa-file-export"></i>
							{{ $judul }}
						</h3>
					</div>

					<div class="card-body">
						<!-- Filter Section -->
						<div class="row mb-3">
							<div class="col-md-4">
								<div class="form-group">
									<label for="tanggal">Tanggal Posting:</label>
									<input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ date('Y-m-d') }}" required>
								</div>
							</div>
							<div class="col-md-8">
								<div class="form-group">
									<label>&nbsp;</label>
									<div class="btn-toolbar" role="toolbar">
										<div class="btn-group mr-2" role="group">
											<button type="button" class="btn btn-success" id="btnValidate">
												<i class="fas fa-check"></i> Validasi Data
											</button>
											<button type="button" class="btn btn-primary" id="btnPreview">
												<i class="fas fa-eye"></i> Preview
											</button>
											<button type="button" class="btn btn-info" id="btnExport">
												<i class="fas fa-download"></i> Posting KSB
											</button>
										</div>
										<div class="btn-group" role="group">
											<button type="button" class="btn btn-secondary" id="btnStats">
												<i class="fas fa-chart-bar"></i> Statistik Toko
											</button>
											<button type="button" class="btn btn-warning" id="btnHistory">
												<i class="fas fa-history"></i> Riwayat Posting
											</button>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Alert Section -->
						<div id="alertContainer"></div>

						<!-- Validation Results -->
						<div id="validationResult" class="card" style="display: none;">
							<div class="card-header">
								<h5 class="card-title">
									<i class="fas fa-info-circle"></i> Hasil Validasi Data
								</h5>
							</div>
							<div class="card-body">
								<div class="row">
									<div class="col-md-6">
										<div class="info-box">
											<span class="info-box-icon bg-info">
												<i class="fas fa-database"></i>
											</span>
											<div class="info-box-content">
												<span class="info-box-text">Total Record</span>
												<span class="info-box-number" id="totalRecords">0</span>
											</div>
										</div>
									</div>
									<div class="col-md-6">
										<div class="info-box">
											<span class="info-box-icon bg-success">
												<i class="fas fa-calendar"></i>
											</span>
											<div class="info-box-content">
												<span class="info-box-text">Tanggal</span>
												<span class="info-box-number" id="selectedDate">-</span>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Preview Data Table -->
						<div id="previewSection" class="card mt-3" style="display: none;">
							<div class="card-header">
								<h5 class="card-title">
									<i class="fas fa-table"></i> Preview Data Posting
								</h5>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table id="previewTable" class="table-bordered table-striped table-sm table">
										<thead>
											<tr>
												<th>No</th>
												<th>KSR</th>
												<th>CCR</th>
												<th>Kode Barang</th>
												<th>Nama Barang</th>
												<th>KDTR</th>
												<th>QTY</th>
												<th>Harga</th>
												<th>Diskon</th>
												<th>Total</th>
												<th>KITIR</th>
												<th>Jam</th>
												<th>No TTP</th>
												<th>Shift</th>
												<th>Toko</th>
											</tr>
										</thead>
										<tbody></tbody>
									</table>
								</div>
							</div>
						</div>

						<!-- Export Progress -->
						<div id="exportProgress" class="card mt-3" style="display: none;">
							<div class="card-header">
								<h5 class="card-title">
									<i class="fas fa-cog fa-spin"></i> Proses Posting
								</h5>
							</div>
							<div class="card-body">
								<div class="progress">
									<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="progressBar">0%</div>
								</div>
								<p class="mt-2" id="progressText">Memproses Posting data...</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Statistics Modal -->
	<div class="modal fade" id="statsModal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="fas fa-chart-bar"></i> Statistik Data Per Toko
					</h5>
					<button type="button" class="close" data-dismiss="modal">
						<span>&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="table-responsive">
						<table id="statsTable" class="table-bordered table-striped table">
							<thead>
								<tr>
									<th>Kode Toko</th>
									<th>Nama Toko</th>
									<th>Shift Pagi</th>
									<th>Shift Sore</th>
									<th>Total</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- History Modal -->
	<div class="modal fade" id="historyModal" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="fas fa-history"></i> Riwayat Export
					</h5>
					<button type="button" class="close" data-dismiss="modal">
						<span>&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="row mb-3">
						<div class="col-md-12">
							<button type="button" class="btn btn-danger btn-sm" id="btnCleanOld">
								<i class="fas fa-trash"></i> Hapus File Lama (30+ hari)
							</button>
						</div>
					</div>
					<div class="table-responsive">
						<table id="historyTable" class="table-bordered table-striped table">
							<thead>
								<tr>
									<th>Nama File</th>
									<th>Ukuran</th>
									<th>Dibuat</th>
									<th>Aksi</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	@push('scripts')
		<script>
			$(document).ready(function() {
				const baseUrl = "{{ url('') }}";
				let previewDataTable;

				// Initialize tooltips
				$('[data-toggle="tooltip"]').tooltip();

				// Validate Data
				$('#btnValidate').click(function() {
					const tanggal = $('#tanggal').val();

					if (!tanggal) {
						showAlert('error', 'Tanggal harus diisi!');
						return;
					}

					showLoading('Memvalidasi data...');

					$.ajax({
						url: baseUrl + '/tpkbusana/validate',
						type: 'POST',
						data: {
							tanggal: tanggal,
							_token: '{{ csrf_token() }}'
						},
						success: function(response) {
							hideLoading();

							if (response.valid) {
								$('#totalRecords').text(response.total_records.toLocaleString());
								$('#selectedDate').text(tanggal);
								$('#validationResult').show();

								const alertType = response.total_records > 0 ? 'success' : 'warning';
								showAlert(alertType, response.message);
							} else {
								showAlert('error', response.error || 'Validasi gagal');
							}
						},
						error: function(xhr) {
							hideLoading();
							const errorMsg = xhr.responseJSON?.error || 'Terjadi kesalahan saat validasi';
							showAlert('error', errorMsg);
						}
					});
				});

				// Preview Data
				$('#btnPreview').click(function() {
					const tanggal = $('#tanggal').val();

					if (!tanggal) {
						showAlert('error', 'Tanggal harus diisi!');
						return;
					}

					showLoading('Memuat preview data...');

					$.ajax({
						url: baseUrl + '/tpkbusana/getdata',
						type: 'POST',
						data: {
							tanggal: tanggal,
							_token: '{{ csrf_token() }}'
						},
						success: function(response) {
							hideLoading();

							if (response.success && response.data.length > 0) {
								displayPreviewData(response.data);
								$('#previewSection').show();
								showAlert('success', `Berhasil memuat ${response.total_records} record`);
							} else {
								showAlert('warning', 'Tidak ada data untuk ditampilkan');
							}
						},
						error: function(xhr) {
							hideLoading();
							const errorMsg = xhr.responseJSON?.error || 'Gagal memuat preview data';
							showAlert('error', errorMsg);
						}
					});
				});

				// Export KSB
				$('#btnExport').click(function() {
					const tanggal = $('#tanggal').val();

					if (!tanggal) {
						showAlert('error', 'Tanggal harus diisi!');
						return;
					}

					if (!confirm('Apakah Anda yakin ingin melakukan export KSB untuk tanggal ' + tanggal + '?')) {
						return;
					}

					showExportProgress();

					$.ajax({
						url: baseUrl + '/tpkbusana/store',
						type: 'POST',
						data: {
							tanggal: tanggal,
							_token: '{{ csrf_token() }}'
						},
						success: function(response) {
							hideExportProgress();

							if (response.success) {
								let message = `Export KSB berhasil!<br>`;
								message += `Total record: ${response.total_records}<br>`;
								message += `File Shift P: ${response.files.shift_p}<br>`;
								message += `File Shift S: ${response.files.shift_s}`;

								showAlert('success', message);
							} else {
								showAlert('error', response.error || 'Export gagal');
							}
						},
						error: function(xhr) {
							hideExportProgress();
							const errorMsg = xhr.responseJSON?.error || 'Terjadi kesalahan saat export';
							showAlert('error', errorMsg);
						}
					});
				});

				// Statistics
				$('#btnStats').click(function() {
					const tanggal = $('#tanggal').val();

					if (!tanggal) {
						showAlert('error', 'Tanggal harus diisi!');
						return;
					}

					$.ajax({
						url: baseUrl + '/tpkbusana/stats',
						type: 'POST',
						data: {
							tanggal: tanggal,
							_token: '{{ csrf_token() }}'
						},
						success: function(response) {
							displayStats(response.stats);
							$('#statsModal').modal('show');
						},
						error: function(xhr) {
							const errorMsg = xhr.responseJSON?.error || 'Gagal memuat statistik';
							showAlert('error', errorMsg);
						}
					});
				});

				// History
				$('#btnHistory').click(function() {
					loadHistory();
					$('#historyModal').modal('show');
				});

				// Clean old files
				$('#btnCleanOld').click(function() {
					if (!confirm('Hapus semua file export yang berusia lebih dari 30 hari?')) {
						return;
					}

					$.ajax({
						url: baseUrl + '/tpkbusana/clean',
						type: 'POST',
						data: {
							days: 30,
							_token: '{{ csrf_token() }}'
						},
						success: function(response) {
							showAlert('success', response.message);
							loadHistory(); // Refresh history
						},
						error: function(xhr) {
							const errorMsg = xhr.responseJSON?.error || 'Gagal menghapus file lama';
							showAlert('error', errorMsg);
						}
					});
				});

				function displayPreviewData(data) {
					if (previewDataTable) {
						previewDataTable.destroy();
					}

					const tableBody = $('#previewTable tbody');
					tableBody.empty();

					data.forEach(function(item, index) {
						const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td>${item.ksr || ''}</td>
                    <td>${item.ccr || ''}</td>
                    <td>${item.kdbr || ''}</td>
                    <td>${item.nmbr || ''}</td>
                    <td>${item.kdtr || ''}</td>
                    <td class="text-right">${parseFloat(item.qty || 0).toLocaleString()}</td>
                    <td class="text-right">${parseFloat(item.hgm || 0).toLocaleString()}</td>
                    <td class="text-right">${parseFloat(item.ndis1 || 0).toLocaleString()}</td>
                    <td class="text-right">${parseFloat(item.njual || 0).toLocaleString()}</td>
                    <td>${item.kitir || ''}</td>
                    <td>${item.jam || ''}</td>
                    <td>${item.no_ttp || ''}</td>
                    <td>${item.shift_type || ''}</td>
                    <td>${item.cbg_code || ''}</td>
                </tr>
            `;
						tableBody.append(row);
					});

					previewDataTable = $('#previewTable').DataTable({
						pageLength: 25,
						responsive: true,
						scrollX: true,
						language: {
							url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
						}
					});
				}

				function displayStats(stats) {
					const tableBody = $('#statsTable tbody');
					tableBody.empty();

					stats.forEach(function(item) {
						const row = `
                <tr>
                    <td>${item.kode}</td>
                    <td>${item.nama}</td>
                    <td class="text-right">${item.shift_p.toLocaleString()}</td>
                    <td class="text-right">${item.shift_s.toLocaleString()}</td>
                    <td class="text-right"><strong>${item.total.toLocaleString()}</strong></td>
                </tr>
            `;
						tableBody.append(row);
					});
				}

				function loadHistory() {
					$.ajax({
						url: baseUrl + '/tpkbusana/history',
						type: 'GET',
						success: function(response) {
							const tableBody = $('#historyTable tbody');
							tableBody.empty();

							response.files.forEach(function(file) {
								const row = `
                        <tr>
                            <td>${file.name}</td>
                            <td>${formatFileSize(file.size)}</td>
                            <td>${file.created}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary"
                                        onclick="downloadFile('${file.name}')">
                                    <i class="fas fa-download"></i> Download
                                </button>
                            </td>
                        </tr>
                    `;
								tableBody.append(row);
							});
						}
					});
				}

				window.downloadFile = function(filename) {
					window.open(baseUrl + '/tpkbusana/download?filename=' + filename, '_blank');
				};

				function showAlert(type, message) {
					const alertClass = type === 'success' ? 'alert-success' :
						type === 'warning' ? 'alert-warning' : 'alert-danger';

					const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle'}"></i>
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;

					$('#alertContainer').html(alertHtml);
					setTimeout(() => $('.alert').alert('close'), 5000);
				}

				function showLoading(message = 'Memproses...') {
					$('#alertContainer').html(`
            <div class="alert alert-info">
                <i class="fas fa-spinner fa-spin"></i> ${message}
            </div>
        `);
				}

				function hideLoading() {
					$('#alertContainer').empty();
				}

				function showExportProgress() {
					$('#exportProgress').show();
					let progress = 0;
					const interval = setInterval(function() {
						progress += Math.random() * 20;
						if (progress > 90) progress = 90;

						$('#progressBar').css('width', progress + '%').text(Math.round(progress) + '%');
					}, 500);

					$('#exportProgress').data('interval', interval);
				}

				function hideExportProgress() {
					clearInterval($('#exportProgress').data('interval'));
					$('#progressBar').css('width', '100%').text('100%');

					setTimeout(function() {
						$('#exportProgress').hide();
						$('#progressBar').css('width', '0%').text('0%');
					}, 1000);
				}

				function formatFileSize(bytes) {
					if (bytes === 0) return '0 Bytes';
					const k = 1024;
					const sizes = ['Bytes', 'KB', 'MB', 'GB'];
					const i = Math.floor(Math.log(bytes) / Math.log(k));
					return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
				}
			});
		</script>
	@endpush
@endsection
