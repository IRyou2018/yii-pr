$(function(){
    $('.modalButton').click(function() {
    // get the click of the create button
    $('#modal').modal('show')
        .find('#modalContent')
        .load($(this).attr('value'));
    });

    $('.modalButton2').click(function() {
    // get the click of the create button
    $('#modal2').modal('show')
        .find('#modalContent2')
        .load($(this).attr('value'));
     });

    $('#modalButtonAG').click(function() {
    // get the click of the create button
    $('#modalAG').modal('show')
        .find('#modalContentAG')
        .load($(this).attr('value'));
    });

    $('#modalButtonAS').click(function() {
    // get the click of the create button
    $('#modalAS').modal('show')
        .find('#modalContentAS')
        .load($(this).attr('value'));
    });
});