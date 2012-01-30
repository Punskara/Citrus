$(document).ready(function() {
	$('input#selectAll').click(function() { $('input.selectToDelete').attr('checked', $(this).attr('checked')); });
    $('input.date').datepicker();
	$('.linkDelete').click(function() { return window.confirm( "Vous êtes sur le point d'effacer l'élément, voulez-vous continuer ?" ); });
	$('.deleteButton').click(function() { return window.confirm( "Vous êtes sur le point d'effacer les éléments sélectionnés, voulez-vous continuer ?" ); });
    /* controles de surface */
    $('.editForm form').validate();
        
    /** users form (passwords match) **/
    $('#userForm').submit(function() {
        return !$('input[name=login]').hasClass('error');
    });
    // password matching
    $('#userForm').validate({
        rules: {
            password1: {
                //required: true,
                equalTo: '#password2'
            },
            password2: {
                //required: true,
                equalTo: '#password1'
            }
        },
        messages: {
            password1: {
                equalTo: "Les deux mots de passe ne correspondent pas."
            },
            password2: {
                equalTo: "Les deux mots de passe ne correspondent pas."
            }
        }
    });
    
    /** user login form **/
    $('#loginForm #login').focus();
});