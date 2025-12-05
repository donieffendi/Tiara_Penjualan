@extends('layouts.plain')

@section('content')
	<div class="content-wrapper">
		<div class="content-header">
			<div class="container-fluid">
				<div class="row mb-2">
					<div class="col-sm-6">
						<h1 class="m-0">Form Cek Perubahan LPH</h1>
					</div>
					<div class="col-sm-6">
						<ol class="breadcrumb float-sm-right">
							<li class="breadcrumb-item active">Form Cek Perubahan LPH</li>
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
								<form method="GET" action="{{ route('get-cekperubahanlph-report') }}" id="cekLphForm">
									@csrf
									<div class="form-group">
										<div class="row align-items-end">
											<div class="col-4 mb-2">
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

											<div class="col-4 mb-2">
												<label for="sub">SUB</label>
												<select name="sub" id="sub" class="form-control" required>
													<option value="">Pilih SUB</option>
													<!-- Options akan dimuat via AJAX berdasarkan cabang -->
												</select>
											</div>

											<div class="col-4 mb-2 text-right">
												<button class="btn btn-primary mr-1" type="submit" name="action" value="filter">
													<i class="fas fa-search mr-1"></i>Proses
												</button>
												<button class="btn btn-danger mr-1" type="button" onclick="resetForm()">
													<i class="fas fa-undo mr-1"></i>Reset
												</button>
												<button class="btn btn-success mr-1" type="submit" name="action" value="cetak" formaction="{{ route('jasper-cekperubahanlph-report') }}"
													formmethod="POST" formtarget="_blank">
													<i class="fas fa-print mr-1"></i>Cetak
												</button>
												{{-- <button class="btn btn-info" type="button" onclick="exportData()">
													<i class="fas fa-download mr-1"></i>Export
												</button> --}}
											</div>
										</div>

										@if (session()->get('filter_cbg') && session()->get('filter_sub'))
											<div class="row mt-2">
												<div class="col-12">
													<div class="alert alert-info">
														<strong>Filter Aktif:</strong>
														Cabang: {{ session()->get('filter_cbg') }} |
														SUB: {{ session()->get('filter_sub') }}
													</div>
												</div>
											</div>
										@endif

										<div style="margin-bottom: 15px;"></div>
										<div class="report-content" col-md-12>
											<?php
											use \koolreport\datagrid\DataTables;
											?>
											@if ($hasilCekLPH && count($hasilCekLPH) > 0)
												<?php
												$tableData = [];
												foreach ($hasilCekLPH as $item) {
													// Helper function untuk highlight jika berubah
													$highlightIfChanged = function ($comparison, $isChanged) {
														if ($isChanged) {
															return "<span class=\"badge badge-warning\">" . $comparison . "</span>";
														}
														return $comparison;
													};

													$tableData[] = [
														'CBG' => $item['CBG'],
														'SUB' => $item['SUB'],
														'KD_BRG' => $item['KD_BRG'],
														'NA_BRG' => $item['NA_BRG'],
														'KET_UK' => $item['KET_UK'] ?? '',
														'KET_KEM' => $item['KET_KEM'] ?? '',
														'LPH' => $item['LPH'],
														'KDLAKU_COMPARISON' => $highlightIfChanged(
															$item['KDLAKU_OLD'] . ' → ' . $item['KDLAKU_NEW'],
															$item['IS_KDLAKU_CHANGED']
														),
														'SMIN_COMPARISON' => $highlightIfChanged(
															number_format($item['SMIN_OLD'], 0, '.', ',') . ' → ' . number_format($item['SMIN_NEW'], 0, '.', ','),
															$item['IS_SMIN_CHANGED']
														),
														'SMAX_COMPARISON' => $highlightIfChanged(
															number_format($item['SMAX_OLD'], 0, '.', ',') . ' → ' . number_format($item['SMAX_NEW'], 0, '.', ','),
															$item['IS_SMAX_CHANGED']
														),
														'SRMIN_COMPARISON' => $highlightIfChanged(
															number_format($item['SRMIN_OLD'], 0, '.', ',') . ' → ' . number_format($item['SRMIN_NEW'], 0, '.', ','),
															$item['IS_SRMIN_CHANGED']
														),
														'SRMAX_COMPARISON' => $highlightIfChanged(
															number_format($item['SRMAX_OLD'], 0, '.', ',') . ' → ' . number_format($item['SRMAX_NEW'], 0, '.', ','),
															$item['IS_SRMAX_CHANGED']
														),
													];
												}

												DataTables::create([
													'dataSource' => $tableData,
													'name' => 'cekLphTable',
													'fastRender' => true,
													'fixedHeader' => true,
													'scrollX' => true,
													'showFooter' => 'bottom',
													'columns' => [
														'CBG' => [
															'label' => 'Cabang',
														],
														'SUB' => [
															'label' => 'SUB',
														],
														'KD_BRG' => [
															'label' => 'Kode Barang',
														],
														'NA_BRG' => [
															'label' => 'Nama Barang',
														],
														'KET_UK' => [
															'label' => 'Ukuran',
														],
														'KET_KEM' => [
															'label' => 'Kemasan',
														],
														'LPH' => [
															'label' => 'LPH',
															'type' => 'number',
															'decimals' => 2,
															'decimalPoint' => '.',
															'thousandSeparator' => ',',
														],
														'KDLAKU_COMPARISON' => [
															'label' => 'Kode Laku (Lama → Baru)',
															'type' => 'string',
														],
														'SMIN_COMPARISON' => [
															'label' => 'S Min (Lama → Baru)',
															'type' => 'string',
														],
														'SMAX_COMPARISON' => [
															'label' => 'S Max (Lama → Baru)',
															'type' => 'string',
														],
														'SRMIN_COMPARISON' => [
															'label' => 'SR Min (Lama → Baru)',
															'type' => 'string',
														],
														'SRMAX_COMPARISON' => [
															'label' => 'SR Max (Lama → Baru)',
															'type' => 'string',
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
																'className' => 'dt-right',
																'targets' => [6], // LPH column
															],
															[
																'className' => 'dt-center',
																'targets' => [0, 1, 2, 7, 8, 9, 10, 11], // CBG, SUB, KD_BRG, and comparison columns
															],
														],
														'order' => [[2, 'asc']], // Order by kode barang
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
																'buttons' => ['copy', 'excel', 'csv', 'pdf', 'print'],
															],
														],
													],
												]);
												?>

												<div class="row mt-3">
													<div class="col-12">
														<div class="alert alert-success">
															<i class="fas fa-info-circle mr-2"></i>
															<strong>Keterangan:</strong><br>
															• <span class="badge badge-warning">Kuning</span> = Perubahan Kode Laku<br>
															• <span class="text-primary font-weight-bold">Biru</span> = Perubahan S Min/Max<br>
															• <span class="text-success font-weight-bold">Hijau</span> = Perubahan SR Min/Max<br>
															• Total data yang berubah: <strong>{{ count($hasilCekLPH) }}</strong> barang
														</div>
													</div>
												</div>
											@elseif(request()->has('action') && request()->get('action') == 'filter')
												<div class="alert alert-warning text-center">
													<i class="fas fa-exclamation-triangle mr-2"></i>
													Tidak ada perubahan data LPH untuk filter yang dipilih.
												</div>
											@else
												<div class="alert alert-info text-center">
													<i class="fas fa-info-circle mr-2"></i>
													Silakan pilih cabang dan SUB untuk melakukan pengecekan perubahan LPH.
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

	<!-- Modal untuk Detail Perubahan -->
	<div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="detailModalLabel">Detail Perubahan LPH</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div id="modalContent">
						<!-- Content akan dimuat via JavaScript -->
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('javascripts')
	<script>
		$(document).ready(function() {
			// Load SUB options when cabang is selected
			$('#cbg').on('change', function() {
				var cbg = $(this).val();
				var subSelect = $('#sub');

				subSelect.empty().append('<option value="">Loading...</option>');

				if (cbg) {
					$.ajax({
						url: "{{ url('/get-sub-list') }}/" + cbg,
						type: 'GET',
						success: function(response) {
							subSelect.empty().append('<option value="">Pilih SUB</option>');
							if (response && response.length > 0) {
								$.each(response, function(index, item) {
									var selected = "{{ session()->get('filter_sub') }}" == item.SUB ? 'selected' : '';
									subSelect.append('<option value="' + item.SUB + '" ' + selected + '>' + item.SUB +
										'</option>');
								});
							} else {
								subSelect.append('<option value="">Tidak ada SUB tersedia</option>');
							}
						},
						error: function(xhr, status, error) {
							subSelect.empty().append('<option value="">Error loading SUB</option>');
							console.error('Error loading SUB:', error);
						}
					});
				} else {
					subSelect.empty().append('<option value="">Pilih SUB</option>');
				}
			});

			// Trigger change event if cbg has value on page load
			if ($('#cbg').val()) {
				$('#cbg').trigger('change');
			}

			// Auto-focus on SUB when cabang is selected
			$('#cbg').on('change', function() {
				if ($(this).val()) {
					setTimeout(function() {
						$('#sub').focus();
					}, 500);
				}
			});

			// Form validation
			$('#cekLphForm').on('submit', function(e) {
				if (!validateForm()) {
					e.preventDefault();
				}
			});

			// Enter key handling
			$('#sub').on('keypress', function(e) {
				if (e.which == 13) { // Enter key
					e.preventDefault();
					if (validateForm()) {
						$(this).closest('form').find('button[name="action"][value="filter"]').click();
					}
				}
			});
		});

		// Reset form function
		function resetForm() {
			window.location.href = "{{ route('rcekperubahanlph') }}";
		}

		// Form validation
		function validateForm() {
			var cbg = $('#cbg').val();
			var sub = $('#sub').val();

			if (!cbg) {
				alert('Harap pilih cabang');
				$('#cbg').focus();
				return false;
			}

			if (!sub) {
				alert('Harap pilih SUB');
				$('#sub').focus();
				return false;
			}

			return true;
		}

		// Export functions
		function exportData() {
			if (typeof window.cekLphTable !== 'undefined') {
				// Show export options
				var format = prompt('Pilih format export:\n1. Excel\n2. CSV\n3. PDF\nMasukkan nomor pilihan (1-3):');

				switch (format) {
					case '1':
						window.cekLphTable.button('.buttons-excel').trigger();
						break;
					case '2':
						window.cekLphTable.button('.buttons-csv').trigger();
						break;
					case '3':
						window.cekLphTable.button('.buttons-pdf').trigger();
						break;
					default:
						if (format !== null) {
							alert('Pilihan tidak valid');
						}
				}
			} else {
				alert('Tidak ada data untuk di-export. Silakan proses data terlebih dahulu.');
			}
		}

		// Show detail modal (optional feature for future enhancement)
		function showDetail(kdBrg, naBrg) {
			$('#detailModalLabel').text('Detail Perubahan: ' + kdBrg);

			var content = '<div class="row">' +
				'<div class="col-12">' +
				'<h6>Nama Barang: ' + naBrg + '</h6>' +
				'<p class="text-muted">Fitur detail akan segera tersedia</p>' +
				'</div>' +
				'</div>';

			$('#modalContent').html(content);
			$('#detailModal').modal('show');
		}

		// Custom styling for comparison columns
		$(document).ready(function() {
			setTimeout(function() {
				// Add custom CSS for better visualization
				$('<style>')
					.prop('type', 'text/css')
					.html(`
						.dt-center { text-align: center !important; }
						.dt-right { text-align: right !important; }
						
						.table td {
							font-size: 0.875rem;
						}
						.badge {
							font-size: 0.75em;
						}
					`)
					.appendTo('head');
			}, 1000);
		});

		// Add row highlighting for changed items
		$(document).on('draw.dt', '#cekLphTable', function() {
			$('#cekLphTable tbody tr').each(function() {
				var row = $(this);
				var hasChanges = false;

				// Check if any comparison column has changes
				row.find('td').each(function() {
					if ($(this).find('.badge, .font-weight-bold').length > 0) {
						hasChanges = true;
						return false;
					}
				});

				if (hasChanges) {
					row.addClass('comparison-changed');
				}
			});
		});

		// Loading indicator functions
		function showLoading() {
			$('button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Loading...');
		}

		function hideLoading() {
			$('button[type="submit"]').prop('disabled', false);
			$('button[name="action"][value="filter"]').html('<i class="fas fa-search mr-1"></i>Proses');
			$('button[name="action"][value="cetak"]').html('<i class="fas fa-print mr-1"></i>Cetak');
		}

		// Show loading on form submit
		$('#cekLphForm').on('submit', function() {
			if (validateForm()) {
				showLoading();
			}
		});

		// Hide loading when page loads
		$(window).on('load', function() {
			hideLoading();
		});
	</script>
@endsection
