$(function(){
    $('#modalButton').click(function() {
    // get the click of the create button
    $('#modal').modal('show')
        .find('#modalContent')
        .load($(this).attr('value'));
     });
});