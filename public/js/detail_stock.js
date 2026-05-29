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
                d.distributor = $('[name=distributor]').val()
            }
        },
        columns: [
            { data: 'idDetail' },
            { data: 'date' },
            { data: 'stock.namaDist' },
            { data: 'obat' },
            { data: 'hargaPokok' },
            { data: 'jumlah' },
            { data: 'total' },
            { data: 'nobatch' },
            { data: 'expired' },
            
        ],
        order: [[ 0, 'desc' ]]
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
        const filter = `?dari=${$('[name=dari]').val()}&sampai=${$('[name=sampai]').val()}&distributor=${$('[name=distributor]').val() ?? ''}`

        window.open(printUrl + filter, '_blank')
    })

    $('#import').on('show.bs.modal', function () {
        reset($(this).find('form')[0])
    })

    $('[name=distributor]').select2({
        theme: 'bootstrap4',
        placeholder: 'Distributor'
    })

})