$(function(){
    //Pour la date on utilise jquery UI
    $('.qwik-form-date').each(function(){
        var $input = $(this);
        var options = {};
        if($input.data('range')){
            options.onClose = function( selectedDate ) {
                //On trouve celui qu'on a lié avec nous
                var $link = $input.closest('form').find('input[name=' + $input.data('link') + ']');
                var typeDateOption = '';
                //Si je suis le begin, alors le min de mon link est ma date
                if($input.data('range') == 'begin'){
                    typeDateOption = 'minDate';
                }else{ //Si je suis la fin, alors le maxDate de mon link est ma date
                    typeDateOption = 'maxDate';
                }
                $link.datepicker( "option", typeDateOption, selectedDate );
            }
        }


        $input.datepicker(options);
    });

    //On submit, on envoit en ajax, et on affiche les erreurs s'il y en a
    $('form.qwik-form').on('submit', function(){
        var $form = $(this);
        $form.addClass('qwik-form-loading');
        var $errorContainer = $form.find('.qwik-form-error');
        $errorContainer.hide();
        //ne peut plus envoyer le temps que ca charge
        $form.find(':submit').attr('disabled','disabled');
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: $form.attr('action'),
            data: $form.serialize()
        }).done(function( data ) {
                //On met les messages
                qwikFormError($form, data.errors);
                //Si c'est valide, on affiche le texte préchargé
                if(data.valid){
                    //$form.find('.qwik-form-form').hide();
                    $form.find('.qwik-form-success').show();
                }else{ //Si y'a eu un problème, le submit est re-anabled
                    $form.find(':submit').removeAttr('disabled');
                    $errorContainer.html(data.message).show();
                }
        }).fail(function(jqXHR, textStatus) {
            $form.find(':submit').removeAttr('disabled');
            //Erreure par défaut
            $errorContainer.html($errorContainer.data('default')).show();
        }).always(function(data, textStatus, jqXHR) {
                $form.removeClass('qwik-form-loading');
        });
        return false;
    });
});

//Affichage des erreurs dans le formulaire
function qwikFormError($form, errors){
    var formId = $form.data('id');
    $form.find('.qwik-form-field-message').hide().html('');
    $form.find('.qwik-form-form-field').removeClass('error');
    for(var name in errors){
        $form.find('#' + formId + '_' + name).addClass('error');
        $form.find('#' + formId + '_' + name + ' .qwik-form-field-message').show().html(errors[name]);
    }
}