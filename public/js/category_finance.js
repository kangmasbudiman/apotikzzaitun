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
            { data: 'nama' },
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

    const edit = ({ idKategoriPengeluaran, nama }) => {
        const modal = $('#edit')
        const form = modal.find('form')[0]
        const url = updateUrl.replace(':id', idKategoriPengeluaran)

        reset(form)
        form.action = url

        modal.find('[name=nama]').val(nama)

        modal.modal('show')
    }

    const remove = id => {
        if (confirm('Hapus data kategori ini?')) {
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
                remove(data.idKategoriPengeluaran)
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

    $('#create').on('show.bs.modal', function () {
        reset($(this).find('form')[0])
    })

})