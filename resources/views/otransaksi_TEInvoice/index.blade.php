@extends('layouts.plain')
@section('styles')
	<link rel="stylesheet" href="{{ asset('foxie_js_css/jquery.dataTables.min.css') }}" />
@endsection

<style>
	.card {
		padding: 5px 10px !important;
	}

	.table th {
		background-color: #8a2be2;
		color: #ffff;
	}

	.datatable tbody td {
		padding: 5px !important;
	}

	.datatable {
		border-right: solid 2px #000;
		border-left: solid 2px #000;
	}

	.btn-secondary {
		background-color: #42047e !important;
	}

	th {
		font-size: 13px;
	}

	td {
		font-size: 13px;
	}

	/* menghilangkan padding */
	.content-header {
		padding: 0 !important;
	}

	/* Layout untuk 2 datatable */
	.left-panel {
		width: 30%;
		min-height: 500px;
	}

	.right-panel {
		width: 70%;
		min-height: 500px;
	}

	.input-group-text {
		background-color: #6c757d;
		color: white;
		font-weight: bold;
	}

	.form-control:focus {
		border-color: #8a2be2;
		box-shadow: 0 0 0 0.2rem rgba(138, 43, 226, 0.25);
	}

	.customer-info {
		background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
		border-left: 4px solid #8a2be2;
	}

	.invoice-table {
		max-height: 400px;
		overflow-y: auto;
	}

	.btn-menu {
		background: linear-gradient(135deg, #8a2be2 0%, #6a1b9a 100%);
		border: none;
		color: white;
	}

	.btn-menu:hover {
		background: linear-gradient(135deg, #6a1b9a 0%, #8a2be2 100%);
		color: white;
	}
</style>

@section('content')
	<!-- Sweetalert -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<div class="content-wrapper">

		<!-- Status Messages -->
		@if (session('status'))
			<div class="alert alert-success">
				{{ session('status') }}
			</div>
			<script>
				Swal.fire({
					title: 'Success!',
					text: '{{ session('status') }}',
					icon: 'success',
					confirmButtonText: 'OK'
				})
			</script>
		@endif

		@if (session('error'))
			<div class="alert alert-danger">
				{{ session('error') }}
			</div>
			<script>
				Swal.fire({
					title: 'Error!',
					text: '{{ session('error') }}',
					icon: 'error',
					confirmButtonText: 'OK'
				})
			</script>
		@endif

		<div class="content">
			<div class="container-fluid">

				<!-- Main Layout: Left Panel (List Invoice) + Right Panel (Form & Detail) -->
				<div class="row">

					<!-- LEFT PANEL - List Invoice Available -->
					<div class="col-md-4 left-panel">
						<div class="card h-100">
							<div class="card-header">
								<h5><i class="fas fa-list"></i> Available Invoices</h5>
								<button type="button" class="btn btn-menu btn-sm float-right" onclick="toggleInvoiceMenu()">
									<i class="fas fa-bars"></i> Menu
								</button>
							</div>
							<div class="card-body p-0">
								<div id="invoiceMenuPanel" style="display: none;">
									<div class="border-bottom p-3">
										<div class="form-group mb-2">
											<label for="txtPeriode">Periode:</label>
											<input type="text" class="form-control form-control-sm" id="txtPeriode"
												value="{{ session('periode')['bulan'] ?? '' }}/{{ session('periode')['tahun'] ?? '' }}" readonly>
										</div>
										<button type="button" class="btn btn-primary btn-sm" onclick="loadAvailableInvoices()">
											<i class="fas fa-search"></i> Load Data
										</button>
									</div>
								</div>

								<table class="table-striped table-sm table" id="availableInvoicesTable">
									<thead class="table-dark">
										<tr>
											<th style="width: 15%">Invoice</th>
											<th style="width: 20%">Total</th>
											<th style="width: 20%">Tgl</th>
											<th style="width: 25%">User</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<!-- RIGHT PANEL - Form Input & Detail -->
					<div class="col-md-8 right-panel">
						<div class="card h-100">
							<div class="card-header">
								<h5><i class="fas fa-edit"></i> Invoice Details</h5>
							</div>
							<div class="card-body">

								<!-- Customer Information Form -->
								<div class="row mb-4">
									<div class="col-12">
										<div class="card customer-info">
											<div class="card-body">
												<div class="row">
													<div class="col-md-3">
														<div class="form-group">
															<label for="txtNomorKitir">Nomor Kitir:</label>
															<input type="text" class="form-control form-control-sm" id="txtNomorKitir" placeholder="Nomor Kitir...">
														</div>
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<label for="txtKodeLang">Kode Lang:</label>
															<input type="text" class="form-control form-control-sm" id="txtKodeLang" placeholder="Kode Lang...">
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label for="txtCompanyName">Company Name:</label>
															<input type="text" class="form-control form-control-sm" id="txtCompanyName" placeholder="TIARA DEWATA" readonly>
														</div>
													</div>
												</div>
												<div class="row">
													<div class="col-md-6">
														<div class="form-group">
															<label for="txtNama">Nama:</label>
															<input type="text" class="form-control form-control-sm" id="txtNama" placeholder="Nama Customer">
														</div>
													</div>
													<div class="col-md-6">
														<div class="form-group">
															<label for="txtAlamat">Alamat:</label>
															<input type="text" class="form-control form-control-sm" id="txtAlamat" placeholder="Alamat Customer">
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Invoice Details Table -->
								<div class="row">
									<div class="col-12">
										<div class="invoice-table">
											<table class="table-striped table-bordered table-sm table" id="invoiceDetailsTable">
												<thead class="table-dark">
													<tr>
														<th style="width: 15%">No.Bukti</th>
														<th style="width: 10%">Tanggal</th>
														<th style="width: 15%">Total</th>
														<th style="width: 12%">PPN</th>
														<th style="width: 12%">DPP</th>
														<th style="width: 15%">No.Invoice</th>
														<th style="width: 8%">Cek</th>
													</tr>
												</thead>
												<tbody>
												</tbody>
											</table>
										</div>
									</div>
								</div>

								<!-- Action Buttons -->
								<div class="row mt-3">
									<div class="col-12">
										<div class="text-center">
											<button type="button" class="btn btn-success mr-2" onclick="saveInvoice()">
												<i class="fas fa-save"></i> Simpan
											</button>
											<button type="button" class="btn btn-warning mr-2" onclick="refreshData()">
												<i class="fas fa-refresh"></i> Refresh
											</button>
											<button type="button" class="btn btn-primary mr-2" onclick="printSelected()">
												<i class="fas fa-print"></i> Print
											</button>
											<button type="button" class="btn btn-info" onclick="generateReport()">
												<i class="fas fa-file-pdf"></i> Rekap
											</button>
										</div>
									</div>
								</div>

							</div>
						</div>
					</div>
				</div>

				<!-- Print Form Section -->
				<div class="row mt-4">
					<div class="col-md-12">
						<div class="card">
							<div class="card-header">
								<h5><i class="fas fa-print"></i> Print Invoice Pelanggan</h5>
							</div>
							<div class="card-body">
								<form id="printForm" method="POST">
									@csrf
									<div class="row">
										<div class="col-md-3">
											<label for="txtbukti1">No Bukti Dari:</label>
											<input type="text" class="form-control" name="txtbukti1" id="txtbukti1" placeholder="IJ2501-0001">
										</div>
										<div class="col-md-1 d-flex align-items-center justify-content-center">
											<span style="margin-top: 25px; font-weight: bold;">s/D</span>
										</div>
										<div class="col-md-3">
											<label for="txtbukti2">No Bukti Sampai:</label>
											<input type="text" class="form-control" name="txtbukti2" id="txtbukti2" placeholder="IJ2501-0010">
										</div>
										<div class="col-md-5 d-flex align-items-end">
											<button type="button" class="btn btn-primary mr-2" onclick="printRange()">
												<i class="fas fa-print"></i> Print Range
											</button>
											<button type="button" class="btn btn-success mr-2" onclick="printReport()">
												<i class="fas fa-file-pdf"></i> Laporan
											</button>
											<button type="button" class="btn btn-info" onclick="printAllInvoices()">
												<i class="fas fa-print"></i> Print All
											</button>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>

				<input name="flagz" class="form-control flagz" id="flagz" value="{{ $flagz }}" hidden>
				<input type="hidden" id="currentNoSJ" value="">
				<input type="hidden" id="currentStatus" value="new">

			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

	<script>
		$(document).ready(function() {
			// Initialize
			loadAvailableInvoices();

			// Auto-load customer data when Kode Lang changes
			$('#txtKodeLang').on('keyup', function(e) {
				if (e.keyCode === 13 && $(this).val().trim() !== '') {
					loadCustomerData($(this).val().trim());
				}
			});

			// Double click on available invoice to load details
			$(document).on('dblclick', '#availableInvoicesTable tbody tr', function() {
				const noSJ = $(this).data('no-sj');
				const kodec = $(this).data('kodec');

				if (noSJ && noSJ !== '') {
					$('#currentStatus').val('edit');
					$('#currentNoSJ').val(noSJ);
					loadInvoiceForEdit(noSJ, kodec);
				}
			});
		});

		// Toggle Invoice Menu (equivalent to btnMenuClick in Delphi)
		function toggleInvoiceMenu() {
			const panel = document.getElementById('invoiceMenuPanel');
			if (panel.style.display === 'none') {
				panel.style.display = 'block';
				loadAvailableInvoices();
			} else {
				panel.style.display = 'none';
			}
		}

		// Load Available Invoices (equivalent to com2 query in Delphi)
		function loadAvailableInvoices() {
			const periode = $('#txtPeriode').val();

			if (!periode) {
				console.log('Periode tidak tersedia');
				return;
			}

			$.ajax({
				url: '{{ route('get-teinvoice') }}',
				method: 'GET',
				data: {
					per: periode
				},
				success: function(response) {
					let tableHtml = '';

					response.forEach(function(item) {
						tableHtml += `
							<tr data-no-sj="${item.NO_SJ || ''}" data-kodec="${item.KODEC || ''}" style="cursor: pointer;">
								<td><small>${item.NO_SJ || '-'}</small></td>
								<td><small>${parseInt(item.TOTALX || 0).toLocaleString('id-ID')}</small></td>
								<td><small>${item.TGLX || '-'}</small></td>
								<td><small>${item.USERX || '-'}</small></td>
							</tr>
						`;
					});

					$('#availableInvoicesTable tbody').html(tableHtml);
				},
				error: function(xhr) {
					console.error('Error loading available invoices:', xhr);
					Swal.fire({
						title: 'Error!',
						text: 'Gagal memuat data invoice tersedia',
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		// Load Customer Data (equivalent to txtkdlangKeyUp in Delphi)
		function loadCustomerData(kdLang) {
			$.ajax({
				url: '{{ route('teinvoice.get-supplier') }}',
				method: 'GET',
				data: {
					kd: kdLang
				},
				success: function(response) {
					$('#txtNama').val(response.nam || '');
					$('#txtAlamat').val(response.alm || '');

					// Load invoice data by bukti
					loadInvoiceByBukti(kdLang);
				},
				error: function(xhr) {
					let errorMessage = 'Customer tidak ditemukan';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMessage = xhr.responseJSON.error;
					}

					Swal.fire({
						title: 'Error!',
						text: errorMessage,
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		// Load Invoice by Bukti (equivalent to tampil procedure in Delphi)
		function loadInvoiceByBukti(bukti) {
			const periode = $('#txtPeriode').val();

			$.ajax({
				url: '{{ route('get-detail-teinvoice') }}',
				method: 'GET',
				data: {
					bukti: bukti,
					per: periode
				},
				success: function(response) {
					if (response.length === 0) {
						$('#invoiceDetailsTable tbody').empty();
						return;
					}

					// Fill customer info from first record
					const firstRecord = response[0];
					$('#txtCompanyName').val(firstRecord.NAMAC || '');
					$('#txtNama').val(firstRecord.NAMA || '');
					$('#txtAlamat').val(firstRecord.ALAMAT || '');

					// Fill invoice details table
					let tableHtml = '';
					let totalAmount = 0;

					response.forEach(function(item, index) {
						totalAmount += parseFloat(item.totala || 0);

						tableHtml += `
							<tr>
								<td><small>${item.no_bukti || ''}</small></td>
								<td><small>${formatDate(item.tgl)}</small></td>
								<td class="text-right"><small>${parseInt(item.totala || 0).toLocaleString('id-ID')}</small></td>
								<td class="text-right"><small>${parseInt(item.ppn || 0).toLocaleString('id-ID')}</small></td>
								<td class="text-right"><small>${parseInt(item.dpp || 0).toLocaleString('id-ID')}</small></td>
								<td><small>${item.no_SJ || ''}</small></td>
								<td class="text-center">
									<input type="checkbox" class="form-control invoice-check" data-bukti="${item.no_bukti}" value="0" style="width: 20px; margin: 0 auto;">
								</td>
							</tr>
						`;
					});

					$('#invoiceDetailsTable tbody').html(tableHtml);

					// Show message like in Delphi
					if ($('#currentStatus').val() === 'new') {
						Swal.fire({
							title: 'Info',
							text: 'Centang yang ada agenda penerimaan!',
							icon: 'info',
							confirmButtonText: 'OK'
						});
					}
				},
				error: function(xhr) {
					let errorMessage = 'Gagal memuat data invoice';
					if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMessage = xhr.responseJSON.error;
					}

					Swal.fire({
						title: 'Error!',
						text: errorMessage,
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		// Load Invoice for Edit (equivalent to cxGrid1DBTableView1DblClick in Delphi)
		function loadInvoiceForEdit(noSJ, kodec) {
			const periode = $('#txtPeriode').val();

			// First get member data
			$.ajax({
				url: '{{ route('teinvoice.get-supplier') }}',
				method: 'GET',
				data: {
					kodec: kodec
				},
				success: function(memberResponse) {
					$('#txtCompanyName').val(memberResponse.NAMAC || '');
					$('#txtKodeLang').val(memberResponse.KODEC || '');
					$('#txtAlamat').val(memberResponse.ALAMAT || '');
					$('#txtNama').val(memberResponse.NAMAC || '');
					$('#txtKodeLang').prop('disabled', true);

					// Then load invoice details
					$.ajax({
						url: '{{ route('get-detail-teinvoice') }}',
						method: 'GET',
						data: {
							no_sj: noSJ,
							per: periode
						},
						success: function(detailResponse) {
							let tableHtml = '';

							detailResponse.forEach(function(item) {
								tableHtml += `
									<tr>
										<td><small>${item.no_bukti || ''}</small></td>
										<td><small>${formatDate(item.tgl)}</small></td>
										<td class="text-right"><small>${parseInt(item.totala || 0).toLocaleString('id-ID')}</small></td>
										<td class="text-right"><small>${parseInt(item.ppn || 0).toLocaleString('id-ID')}</small></td>
										<td class="text-right"><small>${parseInt(item.dpp || 0).toLocaleString('id-ID')}</small></td>
										<td><small>${item.no_SJ || ''}</small></td>
										<td class="text-center">
											<input type="checkbox" class="form-control invoice-check" data-bukti="${item.no_bukti}" value="0" style="width: 20px; margin: 0 auto;">
										</td>
									</tr>
								`;
							});

							$('#invoiceDetailsTable tbody').html(tableHtml);
						},
						error: function(xhr) {
							console.error('Error loading invoice details:', xhr);
						}
					});
				},
				error: function(xhr) {
					console.error('Error loading member data:', xhr);
					Swal.fire({
						title: 'Error!',
						text: 'Member tidak ditemukan!',
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		// Handle checkbox toggle (equivalent to Ctrl key press in Delphi)
		$(document).on('keydown', function(e) {
			if (e.ctrlKey && $('.invoice-check:focus').length > 0) {
				const checkbox = $('.invoice-check:focus');
				checkbox.prop('checked', !checkbox.prop('checked'));
				checkbox.val(checkbox.prop('checked') ? 1 : 0);
			}
		});

		$(document).on('change', '.invoice-check', function() {
			$(this).val($(this).prop('checked') ? 1 : 0);
		});

		// Save Invoice (equivalent to btnSimpanClick in Delphi)
		function saveInvoice() {
			const status = $('#currentStatus').val();
			const alamat = $('#txtAlamat').val().trim();
			const nama = $('#txtNama').val().trim();
			const nomorKitir = $('#txtNomorKitir').val().trim();

			if (status === 'edit') {
				Swal.fire({
					title: 'Error!',
					text: 'Tidak bisa save!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			if (alamat === '') {
				Swal.fire({
					title: 'Error!',
					text: 'Alamat tidak boleh kosong!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			// Collect checked invoices
			const selectedInvoices = [];
			$('.invoice-check').each(function() {
				const bukti = $(this).data('bukti');
				const cek = $(this).val();

				selectedInvoices.push({
					no_bukti: bukti,
					cek: parseInt(cek)
				});
			});

			if (selectedInvoices.filter(item => item.cek === 1).length === 0) {
				Swal.fire({
					title: 'Error!',
					text: 'Isi data terlebih dahulu!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			// Show loading
			Swal.fire({
				title: 'Menyimpan...',
				text: 'Sedang memproses data invoice',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});

			$.ajax({
				url: '{{ route('teinvoice.store') }}',
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					ALAMAT: alamat,
					NAMA: nama,
					selected_invoices: selectedInvoices,
					NOMOR_KITIR: nomorKitir
				},
				success: function(response) {
					Swal.fire({
						title: 'Success!',
						text: 'Save Data Success',
						icon: 'success',
						confirmButtonText: 'OK'
					}).then(() => {
						refreshData();
					});
				},
				error: function(xhr) {
					Swal.close();
					let errorMessage = 'Gagal menyimpan data';

					if (xhr.responseJSON && xhr.responseJSON.message) {
						errorMessage = xhr.responseJSON.message;
					} else if (xhr.responseJSON && xhr.responseJSON.error) {
						errorMessage = xhr.responseJSON.error;
					}

					Swal.fire({
						title: 'Error!',
						text: errorMessage,
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		// Refresh Data (equivalent to btnRefreshClick in Delphi)
		function refreshData() {
			// Clear all fields
			$('#txtKodeLang, #txtCompanyName, #txtNama, #txtAlamat').val('');
			$('#txtKodeLang').prop('disabled', false);
			$('#invoiceDetailsTable tbody').empty();
			$('#currentNoSJ').val('');
			$('#currentStatus').val('new');
			$('#txtNomorKitir').val('');

			// Reload available invoices
			loadAvailableInvoices();
		}

		// Print Selected (equivalent to RekapClick in Delphi)
		function printSelected() {
			const selectedBukti = [];
			$('.invoice-check:checked').each(function() {
				selectedBukti.push($(this).data('bukti'));
			});

			if (selectedBukti.length === 0) {
				Swal.fire({
					title: 'Warning!',
					text: 'Pilih invoice yang akan dicetak!',
					icon: 'warning',
					confirmButtonText: 'OK'
				});
				return;
			}

			Swal.fire({
				title: 'Mencetak...',
				text: 'Sedang memproses print invoice',
				allowOutsideClick: false,
				didOpen: () => {
					Swal.showLoading();
				}
			});

			$.ajax({
				url: '{{ route('teinvoice.print') }}',
				method: 'POST',
				data: {
					_token: '{{ csrf_token() }}',
					selected_bukti: selectedBukti
				},
				xhrFields: {
					responseType: 'blob'
				},
				success: function(response) {
					Swal.close();

					const blob = new Blob([response], {
						type: 'application/pdf'
					});
					const url = window.URL.createObjectURL(blob);
					window.open(url, '_blank');

					setTimeout(() => {
						window.URL.revokeObjectURL(url);
					}, 100);
				},
				error: function(xhr) {
					Swal.close();
					Swal.fire({
						title: 'Error!',
						text: 'Gagal mencetak invoice',
						icon: 'error',
						confirmButtonText: 'OK'
					});
				}
			});
		}

		// Print Range Function
		function printRange() {
			const txtbukti1 = document.getElementById('txtbukti1').value.trim();
			const txtbukti2 = document.getElementById('txtbukti2').value.trim();

			if (!txtbukti1 || !txtbukti2) {
				Swal.fire({
					title: 'Error!',
					text: 'No Bukti Dari dan Sampai harus diisi!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			if (!txtbukti1.startsWith('IJ') || !txtbukti2.startsWith('IJ')) {
				Swal.fire({
					title: 'Error!',
					text: 'No Bukti harus dimulai dengan IJ!',
					icon: 'error',
					confirmButtonText: 'OK'
				});
				return;
			}

			window.open(`{{ route('teinvoice_print_range') }}?txtbukti1=${txtbukti1}&txtbukti2=${txtbukti2}`, '_blank');
		}

		// Print Report Function
		function printReport() {
			window.open('{{ route('teinvoice_jasper') }}', '_blank');
		}

		// Print All Invoices Function
		function printAllInvoices() {
			window.open('{{ route('teinvoice_print_all') }}', '_blank');
		}

		// Generate Report Function (equivalent to RekapClick with report)
		function generateReport() {
			window.open('{{ route('teinvoice_jasper') }}', '_blank');
		}

		// Utility function to format date
		function formatDate(dateString) {
			if (!dateString) return '';

			const date = new Date(dateString);
			const day = String(date.getDate()).padStart(2, '0');
			const month = String(date.getMonth() + 1).padStart(2, '0');
			const year = date.getFullYear();

			return `${day}-${month}-${year}`;
		}

		// Delete function (disabled like in Delphi)
		function deleteRow(link) {
			Swal.fire({
				title: 'Error!',
				text: 'Mau ngapain? ( >.<)==0)-3-)',
				icon: 'error',
				confirmButtonText: 'OK'
			});
		}
	</script>
@endsection
