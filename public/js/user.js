jQuery(function ($) {
	
	const table = $('table').DataTable({
		serverSide: true,
		processing: true,
		ajax: {
			url: ajaxUrl,
			type: 'post',
			data: {
				_token: csrf
			}
		},
		columns: [
			{ data: 'DT_RowIndex' },
			{ data: 'username' },
			{ data: 'nama' },
			{ data: 'alamat' },
			{ data: 'telepon' },
			{
				data: 'hakAkses',
				render: data => {
					const badges = [
						{
							color: 'primary',
							text: 'admin'
						},
						{
							color: 'warning',
							text: 'kasir'
						},
						{
							color: 'danger',
							text: 'gudang'
						}
					]
					const badge = badges[data-1]

					return `<span class="badge badge-${badge.color}">${badge.text}</span>`
				}
			},
			{
				data: 'action',
				orderable: false,
				searchable: false
			}
		],
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
			input.next('.invalid-feedback').html(msg)
		})
	}

	const reset = form => {
		const inputs = $(form).find('.is-invalid')

		$.each(inputs, (key, input) => {
			$(input).removeClass('is-invalid')
		})

		form.reset()
	}

	const edit = ({idUser, nama, username, alamat, telepon, hakAkses}) => {
		const modal = $('.modal')
		const form = modal.find('form')[0]
		const url = updateUrl.replace(':id', idUser)

		reset(form)
		form.action = url

		modal.find('[name=id]').val(idUser)
		modal.find('[name=nama]').val(nama)
		modal.find('[name=username]').val(username)
		modal.find('[name=telepon]').val(telepon)
		modal.find('[name=alamat]').val(alamat)
		modal.find('[name=hakAkses]').val(hakAkses)

		modal.modal('show')
	}

	const remove = id => {
		if (confirm('Hapus data pengguna ini?')) {
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
			case 'remove':
				remove(data.idUser)
				break
		}
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

})