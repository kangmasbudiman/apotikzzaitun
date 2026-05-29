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
                d.jenis = $('[name=jenis]').val()
            }
        },
        columns: [
            { data: 'idPajak' },
            { data: 'jenis' },
            { data: 'nominal' },
            { data: 'tanggal' },
            {
                data: 'keterangan',
                render: d => d ?? '-'
            },
            {
                data: 'user.nama',
                render: d => d ?? '-'
            },
            {
                data: 'action',
                orderable: false,
                searchable: false
            }
        ],
        order: [[3,'desc']]
    })

    const reload = () => table.ajax.reload()

    const success = msg => {
        const alert = $('#alert')
        const modal = $('.modal')

        $.ajax(getPPNUrl).done(function (data) {
            $('#perolehan-ppn').html(`Rp ${new Intl.NumberFormat().format(data)}`)

            alert.html(`<div class="alert alert-success alert-dismissible">
                ${msg}
                <button class="close" data-dismiss="alert">&times;</button>
            </div>`)

            modal.modal('hide')
            reload()
        })

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

    const remove = id => {
        if (confirm('Hapus data ini?')) {
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
            case 'remove':
                remove(data.idPajak)
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

    $('[name=nominal]').on('keyup', function() {
        const number = Number(this.value.replace(/\D/g, ''))
        const price = new Intl.NumberFormat().format(number)
        
        this.value = price
    })

    $('#create').on('show.bs.modal', function () {
        reset($(this).find('form')[0])
    })

    $('#import').on('show.bs.modal', function () {
        reset($(this).find('form')[0])
    })

    $('.filter').on('click', reload)
    $('.print').on('click', () => {
        let filter = `?dari=${$('[name=dari]').val()}&sampai=${$('[name=sampai]').val()}`

        if ($('[name=jenis]').val()) {
            filter += `&jenis=${$('[name=jenis]').val()}`
        }

        window.open(printUrl + filter, '_blank')
    })

    $('#import').on('show.bs.modal', function () {
        reset($(this).find('form')[0])
    })

})