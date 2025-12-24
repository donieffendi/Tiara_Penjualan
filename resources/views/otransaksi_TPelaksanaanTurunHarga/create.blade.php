@extends('layouts.main')

@section('styles')
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
	<style>
		.page-header {
			background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
			color: white;
			padding: 20px;
			border-radius: 8px;
			margin-bottom: 20px;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
		}

		.page-header h3 {
			margin: 0;
			font-weight: 600;
		}

		.card {
			border: none;
			border-radius: 8px;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
			margin-bottom: 20px;
		}

		.card-header {
			background: white;
			border-bottom: 2px solid #f0f0f0;
			padding: 15px 20px;
		}

		.card-header h5 {
			margin: 0;
			font-weight: 600;
			color: #333;
		}

		.form-group label {
			font-weight: 600;
			color: #495057;
		}

		.form-control:focus {
			background-color: #e7f3ff;
			border-color: #80bdff;
			box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
		}

		.btn-custom {
			padding: 10px 25px;
			font-weight: 600;
			font-size: 14px;
			border-radius: 6px;
			transition: all 0.3s;
			border: none;
		}

		.btn-save {
			background: #28a745;
			color: white;
		}

		.btn-save:hover {
			background: #218838;
			color: white;
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3);
		}

		.btn-cancel {
			background: #6c757d;
			color: white;
		}

		.btn-cancel:hover {
			background: #5a6268;
			color: white;
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
		}

		.btn-add-item {
			background: #17a2b8;
			color: white;
		}

		.btn-add-item:hover {
			background: #138496;
			color: white;
		}

		.table thead th {
			background: #343a40;
			color: white;
			border: none;
			font-size: 12px;
			padding: 10px 8px;
			font-weight: 600;
		}

		.table tbody td {
			padding: 8px;
			font-size: 13px;
			vertical-align: middle;
		}

		.table tbody tr:hover {
			background-color: #f8f9fa;
		}

		.required-field::after {
			content: " *";
			color: red;
		}
	</style>
@endsection

@section('content')
	<div class="container-fluid">
		<!-- Page Header -->
		<div class="page-header">
			<h3><i class="fas fa-plus-circle"></i> {{ isset($data) ? 'Edit' : 'Tambah' }} Turun Harga</h3>
		</div>

		<form id="formTurunHarga" method="POST"
			action="{{ isset($data) ? route('tpelaksanaanturunharga.update', $data->NO_BUKTI) : route('tpelaksanaanturunharga.store') }}">
			@csrf
			@if (isset($data))
				@method('PUT')
			@endif

			<!-- Header Information -->
			<div class="card">
				<div class="card-header">
					<h5><i class="fas fa-info-circle"></i> Informasi Header</h5>
				</div>
				<div class="card-body">
					<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label class="required-field">No. Bukti</label>
								<input type="text" class="form-control" id="NO_BUKTI" name="NO_BUKTI" value="{{ $data->NO_BUKTI ?? 'Auto Generate' }}"
									{{ isset($data) ? 'readonly' : 'readonly' }}>
							</div>
							<div class="form-group">
								<label class="required-field">Kode Supplier</label>
								<select class="form-control select2" id="KODES" name="KODES" required>
									<option value="">Pilih Supplier</option>
									@foreach ($suppliers ?? [] as $sup)
										<option value="{{ $sup->KODES }}" {{ isset($data) && $data->KODES == $sup->KODES ? 'selected' : '' }}>
											{{ $sup->KODES }} - {{ $sup->NAMAS }}
										</option>
									@endforeach
								</select>
							</div>
							<div class="form-group">
								<label class="required-field">Nama Supplier</label>
								<input type="text" class="form-control" id="NAMAS" name="NAMAS" value="{{ $data->NAMAS ?? '' }}" required readonly>
							</div>
							<div class="form-group">
								<label class="required-field">Tanggal Mulai</label>
								<input type="date" class="form-control" id="TGL_MULAI" name="TGL_MULAI"
									value="{{ isset($data) ? date('Y-m-d', strtotime($data->TGL_MULAI)) : date('Y-m-d') }}" required>
							</div>
							<div class="form-group">
								<label>Jam Mulai</label>
								<input type="time" class="form-control" id="JAM_MULAI" name="JAM_MULAI" value="{{ $data->JAM_MULAI ?? '00:00' }}">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>Periode</label>
								<input type="text" class="form-control" id="PER" name="PER" value="{{ $periode ?? '' }}" readonly>
							</div>
							<div class="form-group">
								<label class="required-field">Tanggal Selesai</label>
								<input type="date" class="form-control" id="TGL_SLS" name="TGL_SLS"
									value="{{ isset($data) ? date('Y-m-d', strtotime($data->TGL_SLS)) : date('Y-m-d') }}" required>
							</div>
							<div class="form-group">
								<label>Jam Selesai</label>
								<input type="time" class="form-control" id="JAM_SLS" name="JAM_SLS" value="{{ $data->JAM_SLS ?? '23:59' }}">
							</div>
							<div class="form-group">
								<label>Catatan</label>
								<textarea class="form-control" id="NOTES" name="NOTES" rows="3">{{ $data->NOTES ?? '' }}</textarea>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Detail Items -->
			<div class="card">
				<div class="card-header">
					<div class="row">
						<div class="col-md-6">
							<h5><i class="fas fa-list"></i> Detail Barang</h5>
						</div>
						<div class="col-md-6 text-right">
							<button type="button" class="btn btn-sm btn-add-item" id="btnAddItem">
								<i class="fas fa-plus"></i> Tambah Barang
							</button>
						</div>
					</div>
				</div>
				<div class="card-body">
					<div class="table-responsive">
						<table class="table-bordered table" id="detailTable">
							<thead>
								<tr>
									<th width="5%">No</th>
									<th width="12%">Kode Barang</th>
									<th width="25%">Nama Barang</th>
									<th width="10%">Kemasan</th>
									<th width="10%">Ukuran</th>
									<th width="10%">HJ</th>
									<th width="10%">HB</th>
									<th width="10%">TH (Turun Harga)</th>
									<th width="8%">Part SP</th>
									<th width="5%">Aksi</th>
								</tr>
							</thead>
							<tbody id="detailTableBody">
								@if (isset($details) && count($details) > 0)
									@foreach ($details as $index => $detail)
										<tr>
											<td>{{ $index + 1 }}</td>
											<td>
												<input type="text" class="form-control form-control-sm kd-brg-input" name="details[{{ $index }}][KD_BRG]"
													value="{{ $detail->KD_BRG }}" required>
											</td>
											<td>
												<input type="text" class="form-control form-control-sm na-brg-input" name="details[{{ $index }}][NA_BRG]"
													value="{{ $detail->NA_BRG }}" readonly>
											</td>
											<td>
												<input type="text" class="form-control form-control-sm" name="details[{{ $index }}][KET_KEM]"
													value="{{ $detail->KET_KEM }}">
											</td>
											<td>
												<input type="text" class="form-control form-control-sm" name="details[{{ $index }}][KET_UK]" value="{{ $detail->KET_UK }}">
											</td>
											<td>
												<input type="number" class="form-control form-control-sm hj-input text-right" name="details[{{ $index }}][HJ]"
													value="{{ $detail->HJ }}" step="0.01">
											</td>
											<td>
												<input type="number" class="form-control form-control-sm hb-input text-right" name="details[{{ $index }}][HB]"
													value="{{ $detail->HB }}" step="0.01">
											</td>
											<td>
												<input type="number" class="form-control form-control-sm th-input text-right" name="details[{{ $index }}][TH]"
													value="{{ $detail->TH }}" step="0.01" required>
											</td>
											<td>
												<input type="number" class="form-control form-control-sm partsp-input text-right" name="details[{{ $index }}][PARTSP]"
													value="{{ $detail->PARTSP }}" step="0.01">
											</td>
											<td>
												<button type="button" class="btn btn-sm btn-danger btn-delete-row">
													<i class="fas fa-trash"></i>
												</button>
											</td>
										</tr>
									@endforeach
								@else
									<tr id="emptyRow">
										<td colspan="10" class="text-center">Belum ada detail barang. Klik "Tambah Barang" untuk menambahkan.</td>
									</tr>
								@endif
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<!-- Action Buttons -->
			<div class="card">
				<div class="card-body text-right">
					<button type="button" class="btn btn-custom btn-cancel" id="btnCancel">
						<i class="fas fa-times"></i> Batal
					</button>
					<button type="submit" class="btn btn-custom btn-save" id="btnSave">
						<i class="fas fa-save"></i> Simpan
					</button>
				</div>
			</div>
		</form>
	</div>

	<!-- Modal Add Item -->
	<div class="modal fade" id="modalAddItem" tabindex="-1" role="dialog">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><i class="fas fa-search"></i> Pilih Barang</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="table-responsive">
						<table class="table-bordered table-striped table" id="barangTable">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Nama Barang</th>
									<th>Kemasan</th>
									<th>Ukuran</th>
									<th>Harga</th>
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
@endsection

@section('scripts')
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
	<script>
		let detailRowIndex = {{ isset($details) ? count($details) : 0 }};
		let barangTable;

		$(document).ready(function() {
			// Initialize Select2
			$('.select2').select2({
				theme: 'bootstrap4',
				width: '100%'
			});

			// Supplier change event
			$('#KODES').change(function() {
				const selectedOption = $(this).find('option:selected');
				const text = selectedOption.text();
				const namas = text.split(' - ')[1] || '';
				$('#NAMAS').val(namas);
			});

			// Initialize barang DataTable
			barangTable = $('#barangTable').DataTable({
				processing: true,
				serverSide: true,
				ajax: {
					url: '{{ url('api/get-barang') }}',
					type: 'POST',
					data: function(d) {
						d._token = '{{ csrf_token() }}';
					}
				},
				columns: [{
						data: 'KD_BRG',
						name: 'KD_BRG'
					},
					{
						data: 'NA_BRG',
						name: 'NA_BRG'
					},
					{
						data: 'KET_KEM',
						name: 'KET_KEM'
					},
					{
						data: 'KET_UK',
						name: 'KET_UK'
					},
					{
						data: 'HJ',
						name: 'HJ',
						render: function(data) {
							return parseFloat(data).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
						}
					},
					{
						data: null,
						orderable: false,
						render: function(data, type, row) {
							return '<button type="button" class="btn btn-sm btn-primary btn-select-barang" data-barang=\'' + JSON
								.stringify(row) + '\'><i class="fas fa-check"></i> Pilih</button>';
						}
					}
				]
			});

			// Add item button
			$('#btnAddItem').click(function() {
				$('#modalAddItem').modal('show');
			});

			// Select barang from modal
			$(document).on('click', '.btn-select-barang', function() {
				const barang = JSON.parse($(this).attr('data-barang'));
				addDetailRow(barang);
				$('#modalAddItem').modal('hide');
			});

			// Delete row
			$(document).on('click', '.btn-delete-row', function() {
				if ($('#detailTableBody tr').length <= 1) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Minimal harus ada 1 detail barang!'
					});
					return;
				}
				$(this).closest('tr').remove();
				reorderDetailRows();
			});

			// Cancel button
			$('#btnCancel').click(function() {
				if (confirm('Apakah Anda yakin ingin membatalkan? Data yang belum disimpan akan hilang.')) {
					window.location.href = '{{ route('tpelaksanaanturunharga.index') }}';
				}
			});

			// Form submit
			$('#formTurunHarga').submit(function(e) {
				e.preventDefault();

				if ($('#detailTableBody tr').length === 0 || $('#emptyRow').length > 0) {
					Swal.fire({
						icon: 'warning',
						title: 'Perhatian',
						text: 'Detail barang belum diisi!'
					});
					return false;
				}

				Swal.fire({
					title: 'Konfirmasi',
					text: 'Apakah Anda yakin data sudah benar?',
					icon: 'question',
					showCancelButton: true,
					confirmButtonText: 'Ya, Simpan',
					cancelButtonText: 'Batal'
				}).then((result) => {
					if (result.isConfirmed) {
						this.submit();
					}
				});
			});
		});

		function addDetailRow(barang) {
			if ($('#emptyRow').length > 0) {
				$('#emptyRow').remove();
			}

			const rowHtml = `
                <tr>
                    <td>${detailRowIndex + 1}</td>
                    <td>
                        <input type="text" class="form-control form-control-sm kd-brg-input"
                            name="details[${detailRowIndex}][KD_BRG]" value="${barang.KD_BRG}" required>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm na-brg-input"
                            name="details[${detailRowIndex}][NA_BRG]" value="${barang.NA_BRG}" readonly>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm"
                            name="details[${detailRowIndex}][KET_KEM]" value="${barang.KET_KEM || ''}">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm"
                            name="details[${detailRowIndex}][KET_UK]" value="${barang.KET_UK || ''}">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-right hj-input"
                            name="details[${detailRowIndex}][HJ]" value="${barang.HJ || 0}" step="0.01">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-right hb-input"
                            name="details[${detailRowIndex}][HB]" value="${barang.HB || 0}" step="0.01">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-right th-input"
                            name="details[${detailRowIndex}][TH]" value="0" step="0.01" required>
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm text-right partsp-input"
                            name="details[${detailRowIndex}][PARTSP]" value="0" step="0.01">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger btn-delete-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

			$('#detailTableBody').append(rowHtml);
			detailRowIndex++;
		}

		function reorderDetailRows() {
			$('#detailTableBody tr').each(function(index) {
				$(this).find('td:first').text(index + 1);
				$(this).find('input').each(function() {
					const name = $(this).attr('name');
					if (name) {
						const newName = name.replace(/\[\d+\]/, '[' + index + ']');
						$(this).attr('name', newName);
					}
				});
			});
		}
	</script>
@endsection
