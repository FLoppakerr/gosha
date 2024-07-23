$(document).ready(function () {
    $('#provinces').change(function () {
        var province_code = $(this).val();

        $.ajax({
            type: "POST",
            url: "thai_area/get_amphures.php",
            data: { province_code: province_code },
            success: function (data) {
                $('#amphures').html(data);
                $('#districts').html('<option value="">เลือกตำบล</option>');
                $('#zip_code').val('');
            }
        });
    });

    $('#amphures').change(function () {
        var amphure_code = $(this).val();

        $.ajax({
            type: "POST",
            url: "thai_area/get_districts.php",
            data: { amphure_code: amphure_code },
            success: function (data) {
                $('#districts').html(data);
                $('#zip_code').val('');
            }
        });
    });

    $('#districts').change(function () {
        var district_code = $(this).val();

        $.ajax({
            type: "POST",
            url: "thai_area/get_zip_code.php",
            data: { district_code: district_code },
            success: function (data) {
                $('#zip_code').val(data);
            }
        });
    });
});
