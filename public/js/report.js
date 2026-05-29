jQuery(function ($) {

	bsCustomFileInput.init()

	const table = $('table').DataTable({
		serverSide: true,
		processing: true,
		ajax: {
			url: ajaxUrl,
			type: 'post',
			data: d => {
				d._token = csrf
				d.dari = $('[name=dari]').val()
				d.sampai = $('[name=sampai]').val()
				d.user = $('[name=user').val()
				d.status = $('[name=status').val()
			}
		},
		columns: [
			{ data: 'DT_RowIndex' },
			{ data: 'idPenjualan' },
			{ data: 'tanggal' },
			{ data: 'status_badge' },
			{ data: 'total' },
			{
				data: 'action',
				orderable: false,
				searchable: false
			}
		],
	})

	const reload = () => table.ajax.reload()

	const error = (errors, form) => {
		$.each(errors, (name, msg) => {
			const input = $(form).find(`[name=${name}]`)

			input.addClass('is-invalid')
			input.next('.invalid-feedback').html(msg)
		})
	}

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

	const reset = form => {
		const inputs = $(form).find('.is-invalid')

		$.each(inputs, (key, input) => {
			$(input).removeClass('is-invalid')
		})

		form.reset()
	}

	const cancel = id => {
		if (confirm('Batalkan penjualan ini?')) {
			const url = cancelUrl.replace(':id', id)

			$.ajax({
				url: url,
				type: 'post',
				data: {
					_token: csrf,
					_method: 'PATCH'
				},
				success: res => success(res.success)
			})
		}
	}

	$('tbody').on('click', 'button', function () {
		const data = table.row($(this).parents('tr')).data()

		cancel(data.idPenjualan)
	})

	$('form').submit(function (e) {
		e.preventDefault()

		data = new FormData(this)

        $.ajax({
            url: this.action,
            method: this.method,
            data: data,
            contentType: false,
            processData: false,
			success: res => {
				success(res.success)
				reset(this)
			},
            error: err => {
                const errors = err.responseJSON.errors

                error(errors, this)
            }
		})
	})

	$('.filter').on('click', reload)
	$('.print').on('click', () => {
		let filter = `?dari=${$('[name=dari]').val()}&sampai=${$('[name=sampai]').val()}`

		if ($('[name=status]').val()) {
			filter += `&status=${$('[name=status]').val()}`
		}

		if ($('[name=user]').val()) {
			filter += `&user=${$('[name=user]').val()}`
		}

		window.open(printUrl + filter, '_blank')
	})

    $('#import').on('show.bs.modal', function () {
        reset($(this).find('form')[0])
    })

})