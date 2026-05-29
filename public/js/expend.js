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
                d.category = $('[name=category]').val()
            }
        },
        columns: [
            { data: 'idPengeluaran' },
            { data: 'tanggal' },
            { data: 'pengeluaran' },
            {
                data: 'namaKategori',
            },
            {
                data: 'namaUser',
                render: d => d ?? '-'
            },
            {
                data: 'keterangan',
                render: d => d ?? '-'
            },
            {
                data: 'action',
                orderable: false,
                searchable: false
            }
        ],
        order: [[0, 'desc']]
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

        $('[name=idKategoriPengeluaran]').empty()
        $('[name=idUser]').empty()

        form.reset()
    }

    const edit = ({ idPengeluaran, idUser, namaUser, pengeluaran, category, keterangan }) => {
        const modal = $('#edit')
        const form = modal.find('form')[0]
        const url = updateUrl.replace(':id', idPengeluaran)

        reset(form)
        form.action = url

        $('.user-select').hide()

        modal.find('[name=pengeluaran]').val(pengeluaran.substr(3))
        
        if (category) {
            modal.find('[name=idKategoriPengeluaran]').append(`<option value="${category.idKategoriPengeluaran}">${category.nama}</option>`)
        }

        if (idUser) {
            $('.user-select').show()

            modal.find('[name=idUser]').append(`<option value="${idUser}">${namaUser}</option>`)
        }

        modal.find('[name=keterangan]').val(keterangan)

        modal.modal('show')
    }

    const remove = id => {
        if (confirm('Hapus data pengeluaran ini?')) {
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
                remove(data.idPengeluaran)
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

    $('.nominal').on('keyup', function () {
        const number = this.value.replace(/\D/gi, '')

        this.value = new Intl.NumberFormat().format(number)
    })

    $('[name=idKategoriPengeluaran]').select2({
        placeholder: 'Kategori Pengeluaran',
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

    $('[name=idUser]').select2({
        placeholder: 'Nama Karyawan',
        theme: 'bootstrap4',
        ajax: {
            url: userUrl,
            type: 'post',
            data: params => ({
                nama: params.term,
                _token: csrf
            }),
            processResults: res => ({
                results: res
            }),
            cache: true
        }
    })

    $('[name=idUser]').on('select2:select', e => {
        $('[name=namaUser]').val(e.params.data.text)
    })

    $('[name=idKategoriPengeluaran]').on('select2:select', e => {
        if (e.params.data.text === 'Gaji Karyawan') {
            $('.user-select').show()
        } else {
            $('.user-select').hide()
        }
    })

    $('.user-select').hide()

    $('[name=category]').select2({
        placeholder: 'Kategori',
        theme: 'bootstrap4'
    })

    $('#create').on('show.bs.modal', function () {
        $('.user-select').hide()
        reset($(this).find('form')[0])
    })

    $('.filter').on('click', reload)
    $('.print').on('click', () => {
        const filter = `?dari=${$('[name=dari]').val()}&sampai=${$('[name=sampai]').val()}&category=${$('[name=category]').val() ?? ''}`

        window.open(printUrl + filter, '_blank')
    })

    $('#import').on('show.bs.modal', function () {
        reset($(this).find('form')[0])
    })

})