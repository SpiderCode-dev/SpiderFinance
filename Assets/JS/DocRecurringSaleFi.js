$(document).ready(function () {
    $('#formEditDocRecurringSalePeriod input[name="firstvalue"]').on('input', function() {
        let qty = $('#formEditDocRecurringSaleLine0 input[name="quantity"]').val();
        let price = $('#formEditDocRecurringSaleLine0 input[name="price"]').val();
        let total = parseFloat(qty) * parseFloat(price);
        let value = $(this).val();
        let percentage = (value * 100) / total;
        if (value !== 0) {
            $('#formEditDocRecurringSalePeriod input[name="firstpct"]').val(parseInt(percentage));
        }
    });
})