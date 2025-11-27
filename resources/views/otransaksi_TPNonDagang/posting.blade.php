@extends('layouts.plain')

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<div class="card card-info card-outline">
					<div class="card-header">
						<h3 class="card-title">
							<i class="fas fa-check-circle"></i>
							{{ $judul }}
						</h3>
						<div class="card-tools">
							<button type="button" class="btn btn-secondary btn-sm" onclick="kembali()">
								<i class="fas fa-arrow-left"></i> Kembali
							</button>
						</div>
					</div>

					<div class="card-body">
						<!-- Period Info -->
						@if (session('periode'))
							<div class="alert alert-info">
								<i class="fas fa-calendar"></i>
								<strong>Periode:</strong> {{ session('periode')['bulan'] }}/{{ session('periode')['tahun'] }}
							</div>
						@else
							<div class="alert alert-warning">
								<i class="fas fa-exclamation-triangle"></i>
								<strong>Peringatan:</strong> Periode belum diset! Silahkan set periode terlebih dahulu.
							</div>
						@endif

						<!-- Alert Container -->
						<div id="alertContainer"></div>

						<!-- Control Panel -->
						<div class="row mb-3">
							<div class="col-md-12">
								<div class="card card-outline card-primary">
									<div class="card-header">
										<h5 class="card-title">
											<i class="fas fa-cogs"></i> Kontrol Posting
										</h5>
									</div>
									<div class="card-body">
										<div class="row">
											<div class="col-md-6">
												<div class="btn-group" role="group">
													<button type="button" class="btn btn-success" id="btnPostSelected">
														<i class="fas fa-check"></i> Post Terpilih
													</button>
													<button type="button" class="btn btn-warning" id="btnUnpostSelected">
														<i class="fas fa-undo"></i> Unpost Terpilih
													</button>
												</div>
											</div>
											<div class="col-md-6">
												<div class="btn-group float-right" role="group">
													<button type="button" class="btn btn-primary" id="btnSelectAll">
														<i class="fas fa-check-square"></i> Pilih Semua
													</button>
													<button type="button" class="btn btn-secondary" id="btnDeselectAll">
														<i class="fas fa-square"></i> Batal Pilih
													</button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<!-- Data Table -->
						<div class="table-responsive">
							<table id="postingTable" class="table-bordered table-striped table-hover table">
								<thead>
									<tr>
										<th width="5%">
											<div class="form-check">
												<input class="form-check-input" type="checkbox" id="checkAll">
												<label class="form-check-label" for="checkAll">
													All
												</label>
											</div>
										</th>
										<th width="5%">No</th>
										<th width="12%">No Bukti</th>
										<th width="10%">Tanggal</th>
										<th width="8%">Kode Sup</th>
										<th width="20%">Nama Supplier</th>
										<th width="10%">J. Tempo</th>
										<th width="15%">Keterangan</th>
										<th width="10%">Total</th>
										<th width="8%">Status</th>
									</tr>
								</thead>
								<tbody></tbody>
							</table>
						</div>

						<!-- Summary Info -->
						<div class="row mt-3">
							<div class="col-md-12">
								<div class="card card-outline card-info">
									<div class="card-body">
										<div class="row">
											<div class="col-md-3">
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
											<div class="col-md-3">
												<div class="info-box">
													<span class="info-box-icon bg-success">
														<i class="fas fa-check-circle"></i>
													</span>
													<div class="info-box-content">
														<span class="info-box-text">Posted</span>
														<span class="info-box-number" id="postedCount">0</span>
													</div>
												</div>
											</div>
											<div class="col-md-3">
												<div class="info-box">
													<span class="info-box-icon bg-warning">
														<i class="fas fa-clock"></i>
													</span>
													<div class="info-box-content">
														<span class="info-box-text">Unposted</span>
														<span class="info-box-number" id="unpostedCount">0</span>
													</div>
												</div>
											</div>
											<div class="col-md-3">
												<div class="info-box">
													<span class="info-box-icon bg-primary">
														<i class="fas fa-hand-pointer"></i>
													</span>
													<div class="info-box-content">
														<span class="info-box-text">Terpilih</span>
														<span class="info-box-number" id="selectedCount">0</span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Progress Modal -->
	<div class="modal fade" id="progressModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="fas fa-cog fa-spin"></i> Proses Posting
					</h5>
				</div>
				<div class="modal-body">
					<div class="progress">
						<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" id="progressBar">0%</div>
					</div>
					<p class="mt-2" id="progressText">Memproses posting data...</p>
					<div id="progressDetails"></div>
				</div>
			</div>
		</div>
	</div>

	@push('scripts')
		<script>
			$(document).ready(function() {
				const baseUrl = "{{ url('') }}";
				let dataTable;
				let selectedItems = [];

				// Initialize DataTable
				function initDataTable() {
					if (dataTable) {
						dataTable.destroy();
					}

					dataTable = $('#postingTable').DataTable({
						processing: true,
						serverSide: true,
						ajax: {
							url: baseUrl + '/get-tpnondagang-post',
							type: 'GET',
							error: function(xhr, error, code) {
								console.log(xhr.responseText);
								showAlert('error', 'Gagal memuat data: ' + xhr.responseText);
							}
						},
						columns: [{
								data: 'action',
								name: 'action',
								orderable: false,
								searchable: false,
								className: 'text-center'
							},
							{
								data: 'DT_RowIndex',
								name: 'DT_RowIndex',
								orderable: false,
								searchable: false
							},
							{
								data: 'no_bukti',
								name: 'no_bukti'
							},
							{
								data: 'tgl',
								name: 'tgl'
							},
							{
								data: 'kodes',
								name: 'kodes'
							},
							{
								data: 'namas',
								name: 'namas'
							},
							{
								data: 'jtempo',
								name: 'jtempo'
							},
							{
								data: 'notes',
								name: 'notes'
							},
							{
								data: 'total',
								name: 'total',
								className: 'text-right'
							},
							{
								data: 'posted',
								name: 'posted',
								className: 'text-center'
							}
						],
						pageLength: 25,
						responsive: true,
						language: {
							url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
						},
						drawCallback: function() {
							updateSummary();
							updateSelectedCount();
						}
					});
				}

				// Initialize on page load
				initDataTable();

				// Check all functionality
				$('#checkAll').change(function() {
					const isChecked = $(this).is(':checked');
					$('input[type="checkbox"][name="selected_items"]').prop('checked', isChecked);
					updateSelectedItems();
				});

				// Individual checkbox change
				$(document).on('change', 'input[type="checkbox"][name="selected_items"]', function() {
					updateSelectedItems();

					// Update check all status
					const totalCheckboxes = $('input[type="checkbox"][name="selected_items"]').length;
					const checkedCheckboxes = $('input[type="checkbox"][name="selected_items"]:checked').length;

					$('#checkAll').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
					$('#checkAll').prop('checked', checkedCheckboxes === totalCheckboxes);
				});

				// Select all button
				$('#btnSelectAll').click(function() {
					$('input[type="checkbox"][name="selected_items"]').prop('checked', true);
					$('#checkAll').prop('checked', true);
					updateSelectedItems();
				});

				// Deselect all button
				$('#btnDeselectAll').click(function() {
					$('input[type="checkbox"]').prop('checked', false);
					updateSelectedItems();
				});

				// Post selected
				$('#btnPostSelected').click(function() {
					const selected = getSelectedItems();
					if (selected.length === 0) {
						showAlert('warning', 'Pilih minimal 1 data untuk diposting');
						return;
					}

					if (!confirm(`Yakin ingin posting ${selected.length} data terpilih?`)) {
						return;
					}

					processPosting(selected, 'post');
				});

				// Unpost selected
				$('#btnUnpostSelected').click(function() {
					const selected = getSelectedItems();
					if (selected.length === 0) {
						showAlert('warning', 'Pilih minimal 1 data untuk di-unpost');
						return;
					}

					if (!confirm(`Yakin ingin unpost ${selected.length} data terpilih?`)) {
						return;
					}

					processPosting(selected, 'unpost');
				});

				// Get selected items
				function getSelectedItems() {
					const selected = [];
					$('input[type="checkbox"][name="selected_items"]:checked').each(function() {
						selected.push($(this).val());
					});
					return selected;
				}

				// Update selected items array
				function updateSelectedItems() {
					selectedItems = getSelectedItems();
					updateSelectedCount();
				}

				// Update selected count
				function updateSelectedCount() {
					$('#selectedCount').text(selectedItems.length);
				}

				// Update summary info
				function updateSummary() {
					const totalRows = dataTable.data().count();
					let postedCount = 0;
					let unpostedCount = 0;

					// Count posted/unposted from visible data
					dataTable.rows().data().each(function(row) {
						if (row.posted == 1) {
							postedCount++;
						} else {
							unpostedCount++;
						}
					});

					$('#totalRecords').text(totalRows);
					$('#postedCount').text(postedCount);
					$('#unpostedCount').text(unpostedCount);
				}

				// Process posting/unposting
				function processPosting(items, action) {
					showProgressModal();

					const total = items.length;
					let processed = 0;
					let success = 0;
					let errors = [];

					function processNext() {
						if (processed >= total) {
							// Finished processing
							hideProgressModal();

							let message = `Proses ${action} selesai.<br>`;
							message += `Berhasil: ${success}<br>`;
							message += `Gagal: ${errors.length}`;

							if (errors.length > 0) {
								message += `<br><br>Detail error:<br>${errors.join('<br>')}`;
							}

							showAlert(errors.length === 0 ? 'success' : 'warning', message);

							// Refresh table
							setTimeout(() => {
								dataTable.ajax.reload();
								updateSelectedItems();
							}, 2000);

							return;
						}

						const item = items[processed];
						const url = action === 'post' ?
							baseUrl + '/tpnondagang/posting' :
							baseUrl + '/tpnondagang/unposting';

						// Update progress
						const progress = Math.round((processed / total) * 100);
						$('#progressBar').css('width', progress + '%').text(progress + '%');
						$('#progressText').text(`${action === 'post' ? 'Posting' : 'Unposting'} ${processed + 1} dari ${total}...`);

						$.ajax({
							url: url,
							type: 'POST',
							data: {
								no_bukti: item,
								_token: '{{ csrf_token() }}'
							},
							success: function(response) {
								if (response.success) {
									success++;
									$('#progressDetails').append(`<small class="text-success">✓ ${item}</small><br>`);
								} else {
									errors.push(`${item}: ${response.error}`);
									$('#progressDetails').append(`<small class="text-danger">✗ ${item}</small><br>`);
								}
							},
							error: function(xhr) {
								const errorMsg = xhr.responseJSON?.error || 'Error tidak diketahui';
								errors.push(`${item}: ${errorMsg}`);
								$('#progressDetails').append(`<small class="text-danger">✗ ${item}</small><br>`);
							},
							complete: function() {
								processed++;
								setTimeout(processNext, 100); // Small delay between requests
							}
						});
					}

					// Start processing
					processNext();
				}

				// Show progress modal
				function showProgressModal() {
					$('#progressBar').css('width', '0%').text('0%');
					$('#progressText').text('Memulai proses...');
					$('#progressDetails').empty();
					$('#progressModal').modal('show');
				}

				// Hide progress modal
				function hideProgressModal() {
					setTimeout(() => {
						$('#progressModal').modal('hide');
					}, 2000);
				}

				// Refresh table
				window.refreshTable = function() {
					if (dataTable) {
						dataTable.ajax.reload();
					}
				};

				// Back function
				window.kembali = function() {
					window.location.href = baseUrl + '/tpnondagang';
				};

				// Utility functions
				function showAlert(type, message) {
					const alertClass = type === 'success' ? 'alert-success' :
						type === 'warning' ? 'alert-warning' : 'alert-danger';

					const iconClass = type === 'success' ? 'fa-check-circle' :
						type === 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle';

					const alertHtml = `
			<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
				<i class="fas ${iconClass}"></i> ${message}
				<button type="button" class="close" data-dismiss="alert">
					<span>&times;</span>
				</button>
			</div>
		`;

					$('#alertContainer').html(alertHtml);

					// Auto dismiss after 10 seconds for long messages
					setTimeout(() => {
						$('.alert').alert('close');
					}, 10000);

					// Scroll to alert
					$('html, body').animate({
						scrollTop: $('#alertContainer').offset().top - 100
					}, 500);
				}
			});
		</script>
	@endpush
@endsection
