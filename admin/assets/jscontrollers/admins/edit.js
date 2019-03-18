
/**
 * Ajax action to api rest
*/
function admins(){
    var $ocrendForm = $(this), __data = new FormData( document.getElementById('admins_form') );

    if(undefined == $ocrendForm.data('locked') || false == $ocrendForm.data('locked')) {

        var l = Ladda.create( document.querySelector( '#admins_btn' ) );
        l.start();

        $.ajax({
            type : "POST",
            url : "api/admins/edit",
            contentType:false,
            processData:false,
            data : __data,
            beforeSend: function(){ 
                $ocrendForm.data('locked', true) 
            },
            success : function(json) {
                if(json.success == 1) {
                    success_message(json.message);
                    setTimeout(function(){
                        location.reload();
                    }, 1000);
                } else {
                    error_message(json.message);
                }
            },
            error : function(xhr, status) {
                error_message('Ha ocurrido un problema interno');
            },
            complete: function(){ 
                $ocrendForm.data('locked', false);
                l.stop();
            } 
        });
    }
} 

/**
 * Events
 */

$('form#admins_form').submit(function(e) {
    e.defaultPrevented;
    admins();
    return false;
 
});
