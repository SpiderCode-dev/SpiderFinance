$(document).ready(function(){
    let reference = $('#formEditLineaProgramada input[data-field="referencia"]')
    reference.on('change', function(){
        fetch('/EditLineaProgramada?action=get-ref-data&ref='+reference.val())
            .then(response => response.json())
            .then(data => {
                $('#formEditLineaProgramada input[name="descripcion"]').val(data.descripcion)
                $('#formEditLineaProgramada input[name="idproducto"]').val(data.idproducto)
                $('#formEditLineaProgramada input[name="pvpunitario"]').val(data.pvpunitario)
                $('#formEditLineaProgramada select[name="codimpuesto"]').val(data.codimpuesto)
            })
    })
})