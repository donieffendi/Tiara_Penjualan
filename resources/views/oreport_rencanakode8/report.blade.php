@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Laporan Rencana Order Kode 8</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Laporan Rencana Order Kode 8</li>
						</ol>
					</div>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<form method="GET" action="{{ route('get-rencanaorderkode8-report') }}" id="rencanaOrderKode8Form">
									@csrf
									<div class="form-group">
										<div class="row align-items-end">
											<div class="col-3 mb-2">
												<label for="cbg">Cabang</label>
												<select name="cbg" id="cbg" class="form-control" required>
													<option value="">Pilih Cabang</option>
													@foreach ($cbg as $cabang)
														<option value="{{ $cabang->CBG }}" {{ session()->get('filter_cbg') == $cabang->CBG ? 'selected' : '' }}>
															{{ $cabang->CBG }}
														</option>
													@endforeach
												</select>
											</div>

											<div class="col-2 mb-2">
												<label for="sub1">Sub</label>
											<input type="text" name="sub1" id="sub1" class="form-control" value="{{ session()->get('filter_sub1') }}" placeholder="000"
													maxlength="3" pattern="[0-9]{3}">
											</div>

											<div class="col-3 mb-2">
												<label for="no_rencana">No. Rencana Order</label>
												<input type="text" name="no_rencana" id="no_rencana" class="form-control" value="{{ session()->get('filter_no_rencana') }}"
													placeholder="Kosongkan untuk yang baru">
											</div>

											<div class="col-2 mb-2">
												<div class="form-check mt-4">
													<input type="checkbox" name="ulang" id="ulang" value="1" class="form-check-input"
														{{ session()->get('filter_ulang') ? 'checked' : '' }}>
													<label class="form-check-label" for="ulang">
														Cetak Ulang
													</label>
												</div>
											</div>

											<div class="col-2 mb-2 text-right">
												<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
													<i class="fas fa-search mr-1"></i>Proses
												</button>
											</div>
										</div>

										<div class="row">
											<div class="col-12 text-right">
												<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
													<i class="fas fa-undo mr-1"></i>Reset
												</button>
												@if (isset($hasilRencanaOrderKode8) && count($hasilRencanaOrderKode8) > 0)
													<button class="btn btn-success mr-1" type="submit" name="action" value="cetak"
														formaction="{{ route('jasper-rencanaorderkode8-report') }}" formmethod="POST" formtarget="_blank">
														<i class="fas fa-print mr-1"></i>Cetak
													</button>
													<button class="btn btn-warning mr-1" type="button" onclick="postData()" id="btnPost"
														{{ isset($hasilRencanaOrderKode8[0]['POSTED']) && $hasilRencanaOrderKode8[0]['POSTED'] == 1 ? 'disabled' : '' }}>
														<i class="fas fa-check mr-1"></i>Posting
													</button>
												@endif
												<button class="btn btn-info" type="button" onclick="exportData()">
													<i class="fas fa-download mr-1"></i>Export
												</button>
											</div>
										</div>

										@if (session()->get('filter_cbg'))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-info">
														<strong>Filter Aktif:</strong>
														Cabang: {{ session()->get('filter_cbg') }} |
														@if (session()->get('filter_sub1'))
															Sub: {{ session()->get('filter_sub1') }} |
														@endif
														@if (session()->get('filter_no_rencana'))
															No. Rencana: {{ session()->get('filter_no_rencana') }} |
														@endif
														@if (session()->get('filter_ulang'))
															<span class="badge badge-warning">Cetak Ulang</span>
														@endif
													</div>
												</div>
											</div>
										@endif

										@if (isset($warning))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-warning">
														<i class="fas fa-exclamation-triangle mr-2"></i>
														<strong>Peringatan:</strong> {{ $warning }}
														@if (isset($show_ulang_option) && $show_ulang_option)
															<br><br>
															<div class="form-check">
																<input type="checkbox" name="ulang" id="ulang_warning" value="1" class="form-check-input">
																<label class="form-check-label" for="ulang_warning">
																	Gunakan cetak ulang untuk melihat data yang sudah ada
																</label>
															</div>
														@endif
													</div>
												</div>
											</div>
										@endif

										@if (isset($error))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-danger">
														<i class="fas fa-exclamation-triangle mr-2"></i>
														<strong>Error:</strong> {{ $error }}
													</div>
												</div>
											</div>
										@endif

										<div style="margin-bottom: 15px;"></div>
										<div class="report-content" col-md-12>
											@if ($hasilRencanaOrderKode8 && count($hasilRencanaOrderKode8) > 0)
												@php
													$tableData = [];
													foreach ($hasilRencanaOrderKode8 as $item) {
													    $tableData[] = [
													        'NO_ID' => $item['NO_ID'] ?? '',
													        'NAMAFILE' => $item['NAMAFILE'] ?? '',
													        'KD_BRG' => $item['KD_BRG'] ?? '',
													        'NA_BRG' => $item['NA_BRG'] ?? '',
													        'KET_UK' => $item['KET_UK'] ?? '',
													        'SUPP' => $item['SUPP'] ?? '',
													        'STOK' => $item['STOK'] ?? '0',
													        'LPH' => $item['LPH'] ?? '0',
													        'DTR' => $item['DTR'] ?? '0',
													        'SRMIN' => $item['SRMIN'] ?? '0.00',
													        'QTY_ORDER' => $item['QTY_ORDER'] ?? '0',
													        'TGL_BUAT' => $item['TGL_BUAT'] ?? '',
													        'USER_BUAT' => $item['USER_BUAT'] ?? '',
													        'POSTED' => $item['POSTED'] ?? 0,
													        'STATUS' => $item['STATUS'] ?? 'DRAFT',
													    ];
													}

													// Prepare Excel title
													$excelTitle =
													    'Laporan_Rencana_Order_Kode8_' .
													    session()->get('filter_cbg') .
													    '_' .
													    (session()->get('filter_sub1') ? session()->get('filter_sub1') : 'ALL') .
													    '_' .
													    date('Ymd');

													KoolDataTables::create([
													    'dataSource' => $tableData,
													    'name' => 'rencanaOrderKode8Table',
													    'fastRender' => true,
													    'fixedHeader' => true,
													    'scrollX' => true,
													    'showFooter' => true,
													    'showFooter' => 'bottom',
													    'columns' => [
													        'NO_ID' => [
													            'label' => 'No ID',
													            'type' => 'string',
													            'visible' => false,
													        ],
													        'NAMAFILE' => [
													            'label' => 'Nama File',
													            'type' => 'string',
													            'visible' => false,
													        ],
													        'KD_BRG' => [
													            'label' => 'Sub Item',
													            'type' => 'string',
													        ],
													        'NA_BRG' => [
													            'label' => 'Nama Barang',
													            'type' => 'string',
													        ],
													        'KET_UK' => [
													            'label' => 'Ket Ukuran',
													            'type' => 'string',
													        ],
													        'SUPP' => [
													            'label' => 'Supplier',
													            'type' => 'string',
													        ],
													        'STOK' => [
													            'label' => 'Stok',
													            'type' => 'number',
													            'format' => '#,##0',
													        ],
													        'LPH' => [
													            'label' => 'Tarik',
													            'type' => 'number',
													            'format' => '#,##0',
													        ],
													        'DTR' => [
													            'label' => 'DTR',
													            'type' => 'number',
													            'format' => '#,##0',
													        ],
													        'SRMIN' => [
													            'label' => 'SRMIN',
													            'type' => 'number',
													            'format' => '#,##0.00',
													        ],
													        'QTY_ORDER' => [
													            'label' => 'Qty Order',
													            'type' => 'number',
													            'format' => '#,##0',
													        ],
													        'TGL_BUAT' => [
													            'label' => 'Tgl Posting',
													            'type' => 'string',
													        ],
													        'USER_BUAT' => [
													            'label' => 'User Buat',
													            'type' => 'string',
													        ],
													        'STATUS' => [
													            'label' => 'Status',
													            'type' => 'string',
													        ],
													        'POSTED' => [
													            'label' => 'Posted',
													            'type' => 'number',
													            'visible' => false,
													        ],
													    ],
													    'cssClass' => [
													        'table' => 'table table-hover table-striped table-bordered compact',
													        'th' => 'label-title',
													        'td' => 'detail',
													        'tf' => 'footerCss',
													    ],
													    'options' => [
													        'columnDefs' => [
													            [
													                'className' => 'dt-center',
													                'targets' => [0, 4, 6, 7, 8, 9, 10, 11, 13],
													            ],
													            [
													                'className' => 'dt-left',
													                'targets' => [1, 2, 12],
													            ],
													            [
													                'width' => '10%',
													                'targets' => [0],
													            ],
													            [
													                'width' => '25%',
													                'targets' => [1],
													            ],
													            [
													                'width' => '8%',
													                'targets' => [2],
													            ],
													            [
													                'width' => '8%',
													                'targets' => [3],
													            ],
													            [
													                'width' => '6%',
													                'targets' => [4],
													            ],
													            [
													                'width' => '6%',
													                'targets' => [5],
													            ],
													            [
													                'width' => '6%',
													                'targets' => [6],
													            ],
													            [
													                'width' => '8%',
													                'targets' => [7],
													            ],
													            [
													                'width' => '6%',
													                'targets' => [8],
													            ],
													            [
													                'width' => '8%',
													                'targets' => [9],
													            ],
													            [
													                'width' => '7%',
													                'targets' => [10],
													            ],
													            [
													                'width' => '6%',
													                'targets' => [11],
													            ],
													        ],
													        'order' => [[0, 'asc']],
													        'paging' => true,
													        'pageLength' => 25,
													        'searching' => true,
													        'colReorder' => true,
													        'select' => true,
													        'dom' => 'Blfrtip',
													        'buttons' => [
													            [
													                'extend' => 'collection',
													                'text' => 'Export',
													                'buttons' => [
													                    [
													                        'extend' => 'copy',
													                        'text' => 'Copy to Clipboard',
													                    ],
													                    [
													                        'extend' => 'excel',
													                        'text' => 'Export to Excel',
													                        'title' => $excelTitle,
													                    ],
													                    [
													                        'extend' => 'csv',
													                        'text' => 'Export to CSV',
													                    ],
													                    [
													                        'extend' => 'pdf',
													                        'text' => 'Export to PDF',
													                        'title' => 'Laporan Rencana Order Kode 8',
													                        'orientation' => 'landscape',
													                        'pageSize' => 'A4',
													                    ],
													                    [
													                        'extend' => 'print',
													                        'text' => 'Print',
													                    ],
													                ],
													            ],
													        ],
													        'language' => [
													            'url' => '//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json',
													        ],
													    ],
													]);
												@endphp

												{{-- Summary Statistics --}}
												@php
													$stats = app('App\Http\Controllers\OReport\RRencanaOrderKode8Controller')->getSummaryStats($hasilRencanaOrderKode8);
												@endphp

												<div class="row mt-2">
													<div class="col-md-3">
														<div class="small-box bg-info">
															<div class="inner">
																<h3>{{ $stats['total_items'] }}</h3>
																<p>Total Items</p>
															</div>
															<div class="icon">
																<i class="fas fa-cube"></i>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="small-box bg-warning">
															<div class="inner">
																<h3>{{ number_format($stats['total_stok']) }}</h3>
																<p>Total Stok</p>
															</div>
															<div class="icon">
																<i class="fas fa-boxes"></i>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="small-box bg-success">
															<div class="inner">
																<h3>{{ number_format($stats['total_qty_order']) }}</h3>
																<p>Total Qty Order</p>
															</div>
															<div class="icon">
																<i class="fas fa-shopping-cart"></i>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="small-box bg-primary">
															<div class="inner">
																<h3>{{ $stats['total_posted'] }}</h3>
																<p>Items Posted</p>
															</div>
															<div class="icon">
																<i class="fas fa-check-circle"></i>
															</div>
														</div>
													</div>
												</div>

												<div class="row">
													<div class="col-md-6">
														<div class="info-box">
															<span class="info-box-icon bg-secondary"><i class="fas fa-edit"></i></span>
															<div class="info-box-content">
																<span class="info-box-text">Items Draft</span>
																<span class="info-box-number">{{ $stats['total_draft'] }}</span>
															</div>
														</div>
													</div>
													<div class="col-md-6">
														<div class="info-box">
															<span class="info-box-icon bg-info"><i class="fas fa-file-alt"></i></span>
															<div class="info-box-content">
																<span class="info-box-text">Nama File</span>
																<span class="info-box-number" style="font-size: 14px;">
																	{{ !empty($hasilRencanaOrderKode8[0]['NAMAFILE']) ? $hasilRencanaOrderKode8[0]['NAMAFILE'] : 'Belum Ada' }}
																</span>
															</div>
														</div>
													</div>
												</div>
											@else
												<div class="row">
													<div class="col-12">
														<div class="alert alert-warning text-center">
															<i class="fas fa-info-circle mr-2"></i>
															<strong>Informasi:</strong>
															@if (session()->get('filter_cbg'))
																@if (isset($warning))
																	{{ $warning }}
																@else
																	Tidak ada data Rencana Order Kode 8 yang memenuhi kriteria untuk filter yang dipilih.
																@endif
															@else
																Silakan pilih cabang untuk menampilkan laporan Rencana Order Kode 8.
															@endif
														</div>
													</div>
												</div>
											@endif
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	{{-- Modal Loading --}}
	<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModalLabel" aria-hidden="true"
		data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog modal-sm modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-body text-center">
					<div class="spinner-border text-primary" role="status">
						<span class="sr-only">Loading...</span>
					</div>
					<p class="mt-2">Sedang memproses data...</p>
				</div>
			</div>
		</div>
	</div>

	{{-- Modal Confirm Delete --}}
	<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Konfirmasi Hapus</h5>
					<button type="button" class="close" data-dismiss="modal">
						<span>&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<p>Apakah Anda yakin ingin menghapus item ini?</p>
					<p class="text-muted" id="deleteItemInfo"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
					<button type="button" class="btn btn-danger" id="confirmDeleteBtn">Hapus</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
	<script>
		$(document).ready(function() {
			// Apply row callback after DataTable is initialized
			setTimeout(function() {
				var table = $('#rencanaOrderKode8Table').DataTable();
				var cbg = '{{ session()->get('filter_cbg') }}';

				// Function to apply row styling and attributes
				function applyRowStyling() {
					$('#rencanaOrderKode8Table tbody tr').each(function() {
						var row = table.row(this);
						var data = row.data();

						if (data) {
							// Highlight posted rows
							if (data.POSTED == 1 || data.STATUS === 'POSTED') {
								$(this).addClass('table-success');
							}

							// Add data attributes for actions
							$(this).attr('data-no-id', data.NO_ID || '');
							$(this).attr('data-namafile', data.NAMAFILE || '');
							$(this).attr('data-posted', data.POSTED || '0');
							$(this).attr('data-cbg', cbg);

							// Update status badge
							var statusCell = $(this).find('td').eq(11);
							var status = data.STATUS || 'DRAFT';

							if (status === 'POSTED') {
								statusCell.html('<span class="badge badge-success">POSTED</span>');
							} else if (status === 'READY') {
								statusCell.html('<span class="badge badge-primary">READY</span>');
							} else {
								statusCell.html('<span class="badge badge-secondary">DRAFT</span>');
							}
						}
					});
				}

				// Apply styling on initial load
				applyRowStyling();

				// Reapply on table redraw
				table.on('draw.dt', function() {
					applyRowStyling();
				});
			}, 500);

			// Format input sub dengan leading zeros
			$('#sub1').on('input', function() {
				let value = $(this).val().replace(/\D/g, '');
				if (value.length > 3) {
					value = value.substring(0, 3);
				}
				$(this).val(value);
			});

			// Handle ulang checkbox change
			$('#ulang, #ulang_warning').on('change', function() {
				let isChecked = $(this).is(':checked');

				if (isChecked) {
					$('#no_rencana').prop('required', true);
					$('#sub1').prop('required', false);
					$('#no_rencana').focus();
				} else {
					$('#no_rencana').prop('required', false);
					$('#sub1').prop('required', false);
				}

				$('#ulang, #ulang_warning').prop('checked', isChecked);
			});

			// Show loading modal on form submit
			$('#rencanaOrderKode8Form').on('submit', function(e) {
				let action = $('input[name="action"]:checked').val() || $('button[type="submit"]:focus').val();

				if (action === 'filter' || action === 'cetak') {
					let cbg = $('#cbg').val();
					let ulang = $('#ulang').is(':checked');
					let noRencana = $('#no_rencana').val();

					if (!cbg) {
						e.preventDefault();
						Swal.fire({
							icon: 'error',
							title: 'Validasi Error',
							text: 'Cabang harus dipilih!',
							confirmButtonText: 'OK'
						});
						return false;
					}

					if (ulang && !noRencana) {
						e.preventDefault();
						Swal.fire({
							icon: 'error',
							title: 'Validasi Error',
							text: 'No. Rencana Order harus diisi jika menggunakan cetak ulang!',
							confirmButtonText: 'OK'
						});
						return false;
					}

					if (action === 'filter') {
						$('#loadingModal').modal('show');
					}
				}
			});

			$('#loadingModal').modal('hide');

			// Handle row context menu for delete
			$(document).on('contextmenu', '#rencanaOrderKode8Table tbody tr', function(e) {
				e.preventDefault();
				let $row = $(this);
				let posted = $row.attr('data-posted');

				if (posted == '1') {
					Swal.fire({
						icon: 'warning',
						title: 'Tidak Dapat Dihapus',
						text: 'Data sudah terposting, tidak bisa diubah!',
						confirmButtonText: 'OK'
					});
					return;
				}

				let noId = $row.attr('data-no-id');
				let kdBrg = $row.find('td:eq(0)').text();
				let naBrg = $row.find('td:eq(1)').text();

				$('#deleteItemInfo').html(`<strong>Sub Item:</strong> ${kdBrg}<br><strong>Nama:</strong> ${naBrg}`);
				$('#confirmDeleteBtn').attr('data-no-id', noId);
				$('#confirmDeleteBtn').attr('data-cbg', $row.attr('data-cbg'));
				$('#confirmDeleteModal').modal('show');
			});

			// Handle delete confirmation
			$('#confirmDeleteBtn').on('click', function() {
				let noId = $(this).attr('data-no-id');
				let cbg = $(this).attr('data-cbg');

				$('#confirmDeleteModal').modal('hide');

				$.ajax({
					url: '{{ route('get-rencanaorderkode8-report') }}',
					method: 'DELETE',
					data: {
						no_id: noId,
						cbg: cbg,
						_token: '{{ csrf_token() }}'
					},
					success: function(response) {
						if (response.success) {
							Swal.fire({
								icon: 'success',
								title: 'Berhasil',
								text: response.message,
								confirmButtonText: 'OK'
							}).then(() => {
								location.reload();
							});
						} else {
							Swal.fire({
								icon: 'error',
								title: 'Error',
								text: response.message,
								confirmButtonText: 'OK'
							});
						}
					},
					error: function(xhr) {
						let message = 'Gagal menghapus item!';
						if (xhr.responseJSON && xhr.responseJSON.message) {
							message = xhr.responseJSON.message;
						}
						Swal.fire({
							icon: 'error',
							title: 'Error',
							text: message,
							confirmButtonText: 'OK'
						});
					}
				});
			});
		});

		// Reset form function
		function resetForm() {
			Swal.fire({
				title: 'Reset Form',
				text: 'Apakah Anda yakin ingin mereset semua filter?',
				icon: 'question',
				showCancelButton: true,
				confirmButtonText: 'Ya, Reset',
				cancelButtonText: 'Batal',
				confirmButtonColor: '#d33',
				cancelButtonColor: '#3085d6'
			}).then((result) => {
				if (result.isConfirmed) {
					$('#cbg').val('');
					$('#sub1').val('');
					$('#no_rencana').val('');
					$('#ulang, #ulang_warning').prop('checked', false);
					window.location.href = "{{ route('rrencanaorderkode8') }}";
				}
			});
		}

		// Post data function
		function postData() {
			@if (isset($hasilRencanaOrderKode8) && count($hasilRencanaOrderKode8) > 0)
				let namaFile = '{{ $hasilRencanaOrderKode8[0]['NAMAFILE'] ?? '' }}';
				let posted = {{ $hasilRencanaOrderKode8[0]['POSTED'] ?? 0 }};
				let cbg = '{{ session()->get('filter_cbg') }}';

				if (posted == 1) {
					Swal.fire({
						icon: 'warning',
						title: 'Sudah Terposting',
						text: 'Data sudah terposting, tidak bisa diubah!',
						confirmButtonText: 'OK'
					});
					return;
				}

				if (!namaFile) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: 'Nama file tidak ditemukan!',
						confirmButtonText: 'OK'
					});
					return;
				}

				Swal.fire({
					title: 'Konfirmasi Posting',
					text: `Apakah Anda yakin ingin memposting data dengan nama file: ${namaFile}?`,
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya, Posting',
					cancelButtonText: 'Batal',
					confirmButtonColor: '#28a745',
					cancelButtonColor: '#6c757d'
				}).then((result) => {
					if (result.isConfirmed) {
						$.ajax({
							url: '{{ route('get-rencanaorderkode8-report') }}',
							method: 'POST',
							data: {
								action: 'post',
								cbg: cbg,
								namafile: namaFile,
								posted: posted,
								_token: '{{ csrf_token() }}'
							},
							success: function(response) {
								if (response.success) {
									Swal.fire({
										icon: 'success',
										title: 'Berhasil',
										text: response.message,
										confirmButtonText: 'OK'
									}).then(() => {
										location.reload();
									});
								} else {
									Swal.fire({
										icon: 'error',
										title: 'Error',
										text: response.message,
										confirmButtonText: 'OK'
									});
								}
							},
							error: function(xhr) {
								let message = 'Gagal memposting data!';
								if (xhr.responseJSON && xhr.responseJSON.message) {
									message = xhr.responseJSON.message;
								}
								Swal.fire({
									icon: 'error',
									title: 'Error',
									text: message,
									confirmButtonText: 'OK'
								});
							}
						});
					}
				});
			@else
				Swal.fire({
					icon: 'warning',
					title: 'Tidak Ada Data',
					text: 'Tidak ada data untuk diposting!',
					confirmButtonText: 'OK'
				});
			@endif
		}

		// Export data function
		function exportData() {
			let cbg = $('#cbg').val();

			if (!cbg) {
				Swal.fire({
					icon: 'error',
					title: 'Export Error',
					text: 'Silakan pilih cabang terlebih dahulu sebelum export!',
					confirmButtonText: 'OK'
				});
				return false;
			}

			@if (isset($hasilRencanaOrderKode8) && count($hasilRencanaOrderKode8) > 0)
				Swal.fire({
					title: 'Export Data',
					text: 'Pilih format export:',
					icon: 'question',
					showCancelButton: true,
					showDenyButton: true,
					confirmButtonText: 'Excel',
					denyButtonText: 'CSV',
					cancelButtonText: 'Batal',
					confirmButtonColor: '#28a745',
					denyButtonColor: '#17a2b8',
					cancelButtonColor: '#6c757d'
				}).then((result) => {
					if (result.isConfirmed) {
						let table = $('#rencanaOrderKode8Table').DataTable();
						table.button('.buttons-excel').trigger();
					} else if (result.isDenied) {
						let table = $('#rencanaOrderKode8Table').DataTable();
						table.button('.buttons-csv').trigger();
					}
				});
			@else
				Swal.fire({
					icon: 'warning',
					title: 'Export Warning',
					text: 'Tidak ada data untuk di-export!',
					confirmButtonText: 'OK'
				});
			@endif
		}

		@if (session('success'))
			Swal.fire({
				icon: 'success',
				title: 'Berhasil',
				text: '{{ session('success') }}',
				confirmButtonText: 'OK'
			});
		@endif

		@if (session('error'))
			Swal.fire({
				icon: 'error',
				title: 'Error',
				text: '{{ session('error') }}',
				confirmButtonText: 'OK'
			});
		@endif

		$(document).keyup(function(e) {
			if (e.keyCode === 46) {
				let selectedRow = $('#rencanaOrderKode8Table tbody tr.selected');
				if (selectedRow.length > 0) {
					selectedRow.trigger('contextmenu');
				}
			}

			if (e.keyCode === 116) {
				e.preventDefault();
				$('#rencanaOrderKode8Form button[value="filter"]').click();
			}
		});

		$(function() {
			$('[data-toggle="tooltip"]').tooltip();
		});

		@if (session()->get('filter_cbg') && !session()->get('filter_ulang'))
			setInterval(function() {
				if (!$('.modal.show').length && !$(':focus').length) {
					console.log('Auto-refreshing Rencana Order Kode 8 data...');
					$.get(window.location.href, function(data) {
						console.log('Data refreshed');
					}).fail(function() {
						console.log('Auto-refresh failed');
					});
				}
			}, 600000);
		@endif
	</script>
@endpush

@push('styles')
	<style>
		.report-content {
			margin-top: 20px;
		}

		.small-box {
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
			transition: transform 0.2s ease;
		}

		.small-box:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
		}

		.info-box {
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
			transition: transform 0.2s ease;
		}

		.info-box:hover {
			transform: translateY(-1px);
			box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
		}

		.alert {
			border-radius: 8px;
		}

		.card {
			border-radius: 10px;
			box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
		}

		.card-body {
			padding: 25px;
		}

		.dataTables_wrapper .dataTables_filter input {
			border-radius: 20px;
			padding: 5px 15px;
			border: 1px solid #ddd;
		}

		.dataTables_wrapper .dataTables_length select {
			border-radius: 5px;
			border: 1px solid #ddd;
		}

		.btn {
			border-radius: 6px;
			transition: all 0.2s ease;
		}

		.btn:hover {
			transform: translateY(-1px);
			box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
		}

		.btn:disabled {
			opacity: 0.6;
			cursor: not-allowed;
			transform: none;
		}

		.form-control {
			border-radius: 6px;
			border: 1px solid #ddd;
			transition: border-color 0.2s ease;
		}

		.form-control:focus {
			border-color: #007bff;
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
		}

		.spinner-border {
			width: 3rem;
			height: 3rem;
		}

		.table-success {
			background-color: #d4edda !important;
		}

		.table tbody tr.selected {
			background-color: #007bff !important;
			color: white;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
			cursor: pointer;
		}

		.badge {
			font-size: 0.8em;
			padding: 0.25em 0.6em;
		}

		.form-check-input:checked {
			background-color: #007bff;
			border-color: #007bff;
		}

		.modal-content {
			border-radius: 10px;
			border: none;
			box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
		}

		.modal-header {
			border-bottom: 1px solid #dee2e6;
			background-color: #f8f9fa;
			border-radius: 10px 10px 0 0;
		}

		.modal-footer {
			border-top: 1px solid #dee2e6;
			background-color: #f8f9fa;
			border-radius: 0 0 10px 10px;
		}

		@media (max-width: 768px) {
			.table-responsive {
				font-size: 12px;
			}

			.small-box h3 {
				font-size: 1.5rem;
			}

			.info-box-number {
				font-size: 1.2rem;
			}

			.card-body {
				padding: 15px;
			}

			.btn {
				padding: 0.375rem 0.5rem;
				font-size: 0.875rem;
			}
		}

		@media print {
			.no-print {
				display: none !important;
			}

			.card {
				box-shadow: none;
				border: 1px solid #ddd;
			}

			.table {
				font-size: 12px;
			}

			.btn,
			.form-control,
			.modal {
				display: none !important;
			}
		}

		.dataTables_scrollBody::-webkit-scrollbar {
			width: 8px;
			height: 8px;
		}

		.dataTables_scrollBody::-webkit-scrollbar-track {
			background: #f1f1f1;
			border-radius: 4px;
		}

		.dataTables_scrollBody::-webkit-scrollbar-thumb {
			background: #c1c1c1;
			border-radius: 4px;
		}

		.dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
			background: #a8a8a8;
		}

		.tooltip-inner {
			max-width: 300px;
			background-color: #333;
			color: white;
			border-radius: 4px;
			font-size: 0.875rem;
		}

		.alert {
			animation: fadeIn 0.5s ease-in;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
				transform: translateY(-10px);
			}

			to {
				opacity: 1;
				transform: translateY(0);
			}
		}
	</style>
@endpush
