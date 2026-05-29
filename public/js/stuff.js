jQuery(function ($) {
    
    bsCustomFileInput.init()
	
	const table = $('#data').DataTable({
		serverSide: true,
		processing: true,
		select: true,
		ajax: {
			url: ajaxUrl,
			type: 'post',
			data: d => {
				d._token = csrf
				d.penerbit = $('[name=suplier]').val()
				d.tahun = $('[name=tahun]').val()
			}
		},
		columns: [
			{
				data: null,
				orderable: false,
				searchable: false,
				render: d => '<input type="checkbox">',
			},
			{ data: 'DT_RowIndex' },
			{ data: 'idBuku' },
			{ data: 'barcode_generate' },
			{ data: 'obat' },
			{ data: 'suplier' },
			{ data: 'tahun' },
			{ data: 'stock' },
			{ data: 'price' },
			{
				data: 'action',
				orderable: false,
				searchable: false
			},
		],
		order: [[ 1, 'asc' ]]
	})

	const reload = () => table.ajax.reload()

	const success = msg => {
		const alert = $('#alert')
		const modal = $('.modal')

		alert.html(`<div class="alert alert-success alert-dismissible">
			${msg}
			<button class="close" data-dismiss="alert">&times;</button>
		</div>`)

		modal.modal('hide')
		reload()
	}

	const error = (errors, form) => {
		$.each(errors, (name, msg) => {
			const input = $(form).find(`[name=${name}]`)

			input.addClass('is-invalid')
			input.parents('.form-group').find('.invalid-feedback').html(msg)
		})
	}

	const reset = form => {
		const inputs = $(form).find('.is-invalid')

		$.each(inputs, (key, input) => {
			$(input).removeClass('is-invalid')
		})

		form.reset()
	}

	const edit = ({idBuku, category, rack, barcode, noisbn, obat, penulis, suplier, tahun, hargaPokok, hargaJual, disc, rak,margin,ppn}) => {
		const modal = $('#edit')
		const form = modal.find('form')[0]
		const url = updateUrl.replace(':id', idBuku)

		reset(form)

		modal.find('[name=idKategori]').empty()
		modal.find('[name=idRak]').empty()

		form.action = url

		modal.find('[name=id]').val(idBuku)
		modal.find('[name=barcode]').val(barcode)
		modal.find('[name=noisbn]').val(noisbn)
		modal.find('[name=obat]').val(obat)
		modal.find('[name=penulis]').val(penulis)
		modal.find('[name=suplier]').val(suplier)
		modal.find('[name=tahun]').val(tahun)
		modal.find('[name=hargaPokok]').val(hargaPokok)
		modal.find('[name=hargaJual]').val(hargaJual)
		modal.find('[name=disc]').val(disc)
		modal.find('[name=rak]').val(rak)
		modal.find('[name=margin]').val(margin)
		modal.find('[name=ppn]').val(ppn)
		if (category) {
			modal.find('[name=idKategori]').append(`<option value='${category.idKategori}' selected>${category.nama_kategori}</option>`)
		}

		if (rack) {
			modal.find('[name=idRak]').append(`<option value='${rack.idRak}' selected>${rack.nama_rak}</option>`)
		}

		modal.modal('show')
	}

	const show = data => {
		const modal = $('#show')

		for (let [key, value] of Object.entries(data)) {
			if (key === 'category') {
				modal.find(`[data-key=${key}]`).html(value?.nama_kategori ?? '-')
			} else if (key === 'rack') {
				modal.find(`[data-key=${key}]`).html(value?.nama_rak ?? '-')
			} else {
				modal.find(`[data-key=${key}]`).html(value ?? '-')
			}
		}

		modal.modal('show')
	}

	const remove = id => {
		if (confirm('Hapus data buku ini?')) {
			const url = deleteUrl.replace(':id', id)

			$.ajax({
				url: url,
				type: 'post',
				data: {
					_token: csrf,
					_method: 'DELETE'
				},
				success: res => success(res.success)
			})
		}
	}

	$('tbody').on('click', 'button', function () {
		const action = $(this).data('action')
		const data = table.row($(this).parents('tr')).data()

		switch(action){
			case 'edit':
				edit(data)
				break
			case 'show':
				show(data)
				break
			case 'remove':
				remove(data.idBuku)
				break
		}
	})

	$('tbody').on('change', '[type=checkbox]', function () {
		if (this.checked) {
			$(this).parents('tr').addClass('selected')
		} else {
			$(this).parents('tr').removeClass('selected')
		}

		const totalSelected = table.rows('.selected').count()
		const total = table.rows().count()

		if (totalSelected === total) {
			$('#select-all').prop('checked', true)
		} else {
			$('#select-all').prop('checked', false)
		}
	})

	$('#select-all').on('change', function () {
		if (this.checked) {
			table.rows().nodes().to$().addClass('selected')
			table.rows().nodes().to$().find('[type=checkbox]').prop('checked', true)
		} else {
			table.rows().nodes().to$().removeClass('selected')
			table.rows().nodes().to$().find('[type=checkbox]').prop('checked', false)
		}
	})

	$('.price').on('keyup', function() {
		const number = Number(this.value.replace(/\D/g, ''))
		const price = new Intl.NumberFormat().format(number)
		
		this.value = price
	})

	$('[name=idKategori]').select2({
		placeholder: 'Kategori',
		theme: 'bootstrap4',
		ajax: {
			url: categoryUrl,
			type: 'post',
			data: params => ({
				name: params.term,
				_token: csrf
			}),
			processResults: res => ({
				results: res
			}),
			cache: true
		}
	})

	$('[name=idRak]').select2({
		placeholder: 'Rak',
		theme: 'bootstrap4',
		ajax: {
			url: rackUrl,
			type: 'post',
			data: params => ({
				name: params.term,
				_token: csrf
			}),
			processResults: res => ({
				results: res
			}),
			cache: true
		}
	})

	$('form.post').submit(function (e) {
		e.preventDefault()

		data = new FormData(this)

		$(this).find('[type=submit]').attr('disabled', 'disabled')

		$.ajax({
		    url: this.action,
		    method: this.method,
		    data: data,
		    contentType: false,
		    processData: false,
		    success: res => {
		        success(res.success)
		        reset(this)
		
				$(this).find('[type=submit]').removeAttr('disabled')
		    },
		    error: err => {
		        const errors = err.responseJSON.errors

		        error(errors, this)
		        
				$(this).find('[type=submit]').removeAttr('disabled')
		    }
		})
	})

	$('.filter').on('click', reload)

    $('#import').on('show.bs.modal', function () {
        reset($(this).find('form')[0])
    })

    $('#select-all').prop('checked', false)

    $('#remove-all').on('click', () => {
    	if (confirm('Hapus data buku yang dipilih?')) {
    		const data = table.rows('.selected').data().map(data => data.idBuku).toArray()

    		$.ajax({
    			url: destroyBatchUrl,
    			method: 'post',
    			data: {
    				_method: 'DELETE',
    				_token: csrf,
    				stuffs: data
    			},
    			success: res => {
    				success(res.success)
    			}
    		})
    	}
    })

    table.on('draw.dt', function () {
    	$('#select-all').prop('checked', false)
    })

    $('#filter [name=suplier]').select2({
    	theme: 'bootstrap4',
    	placeholder: 'suplier'
    })

    $('#filter [name=tahun]').select2({
    	theme: 'bootstrap4',
    	placeholder: 'Tahun'
    })

})