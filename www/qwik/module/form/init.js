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
        //On rajout la classe pour dire que le formulaire est en mode "loading"
        $form.addClass('qwik-form-loading');
        //S'il y a des erreurs on les cache
        var $errorContainer = $form.find('.qwik-form-error');
        $errorContainer.hide();

        //On ne peut plus envoyer le temps que ca charge
        $form.find(':submit').attr('disabled','disabled');
        //On post et on s'attend à récupérer du json
        $.ajax({
            type: "POST",
            dataType: 'json',
            url: $form.attr('action'),
            data: $form.serialize()
        }).done(function( data ) {
                //On a recu une "bonne réponse"
                try{
                    //On met les messages pour chaque field
                    qwikFormError($form, data.errors);

                    //Si c'est valide, on affiche le texte préchargé dans qwik-form-success
                    if(data.valid){
                        //$form.find('.qwik-form-form').hide();
                        $form.find('.qwik-form-success').show();
                    }else{ //Si y'a eu un problème, le submit est re-anabled
                        $form.find(':submit').removeAttr('disabled');
                        //On explique le pourquoi (message général)
                        $errorContainer.html(data.message).show();
                    }
                }catch(ex){
                    //On peut resubmit... Au cas où
                    $form.find(':submit').removeAttr('disabled');
                }
        }).fail(function(jqXHR, textStatus) {
            //On a eu un problème (pas de json, ou timeout)
            $form.find(':submit').removeAttr('disabled');
            //Erreur par défaut
            $errorContainer.html($errorContainer.data('default')).show();
        }).always(function(data, textStatus, jqXHR) {
                //Dans tous les cas, on enlève le loading
                $form.removeClass('qwik-form-loading');
        });
        return false;
    });
});

//Affichage des erreurs dans le formulaire
function qwikFormError($form, errors){
    var formId = $form.data('id');
    //On met les mesage à vide pour tous les fields
    $form.find('.qwik-form-field-message').hide().html('');
    //On enlève la class erreur pour tous les fields
    $form.find('.qwik-form-form-field').removeClass('error');
    //Pour chaque erreur, on va mettre l'erreur et afficher le message
    for(var name in errors){
        var $fieldGroup = $form.find("[name='form["+name+"]']").closest('.control-group');
        $fieldGroup.addClass('error');
        $fieldGroup.find('.qwik-form-field-message').show().html(errors[name]);
    }
}