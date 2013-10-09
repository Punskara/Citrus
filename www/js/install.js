
/* 
/* Extension jQuery 
 */

$.fn.extend({ 
    'getArray' : function ( res ) {
        // retourne les données d'un formulaire sous forme d'objet, res etant un objet a completer (ou initialisé si non précisé)
        if (this.get(0).nodeName=="FORM") {
            if (!res) res = {};
            var tr = {}, lst = $(this).serializeArray(), k;
            for ( var k in lst) tr[ lst[k].name ] = lst[k].value
            return $.extend(true, res, tr);
        }
        return this;
    },
    'setArray' : function ( res ) {
        // rempli un formulaire avec l'objet de données passé en parametre
        if (this.get(0).nodeName=="FORM") {
            for ( var k in res ) if (this.get(0)[k]) {
                if ( this.get(0)[k].length && this.get(0)[k][0].type && this.get(0)[k][0].type=="radio" ) 
                    $( this.get(0)[k][ res[k] ] ).attr( 'checked', 'checked' ).trigger('change');
                else $( this.get(0)[k] ).val( res[k] ).trigger('change');
            }
        }
        return this;
    },
    'setReset' : function () {
        // reset un formulaire à sa position initiale
        if (this.get(0).nodeName=="FORM") {
            this.get(0).reset();
            for ( var k = 0; k< this.get(0).elements.length; k++ ) $(this.get(0).elements[k]).trigger('change');
        }
        return this;
    }
});

var relocation, first_generated = true; // permet de savoir si l'on est en premiere install ou en edition

$(document).ready(function () {
    
/*
/* Définition des méthodes
 */
    
    // fonctions d'initialisation de chaques pages
    var init = {
            'installation' : function () {
                // reaffichage des données existantes
                $('#f_install').setArray( config );

                if(config.bdd == 1) $("#bloc_doctrine").show();
                else $("#bloc_doctrine").hide();
                
                if (generated && config.sitename) $('h1').html( config.sitename );

                //affichage option doctrine
                $('input[name=bdd]').bind('change', function () {
                    if (this.value === '1') $("#bloc_doctrine").show();
                    else $("#bloc_doctrine").hide();
                });
                
                // gestion submit du formulaire
                $('#f_install').bind('submit', function ( e ) {
                    config = $(this).getArray( config );
                    e.stopImmediatePropagation();
                    formTraitement();
                    $('#configuration').removeClass('disabled').trigger('click');
                    if (generated) boxmodif();
                    return false;
                });
                
                if (just_generated && first_generated) infoload($('div#generated'));
                first_generated = false;
            },
            'configuration' : function () {
                if (!config.hosts) config.hosts = Array();
                
                // reaffichage des hosts existants
                for (var k = 0; k< config.hosts.length; k++) setHost( config.hosts[k] , k+1 , true );
                
                // gestion bdd si necessaire
                if ( !config.bdd || config.bdd === '0' ) $('#bloc_bdd').hide();
                else $('#bloc_bdd').show();

                // formulaire d'ajout / modification de host
                $('#f_addHost').bind('submit', function ( e ) {
                    var res = $(this).getArray();
                    if (this.inst.value == 'new') {
                        config.hosts.push( res );
                        setHost( res, config.hosts.length, true );
                    }
                    else {
                        config.hosts[ this.inst.value ] = res;
                        setHost( res , Math.round(this.inst.value) + 1 );
                    }
                    $(this).setReset();
                    $('button[type=reset]').attr('disabled', 'disabled');
                    $('button[type=submit]').html('Ajouter');
                    $('h2#title_host').html('Créer un host');
                    e.stopImmediatePropagation();
                    formTraitement();
                    if (generated) boxmodif();
                    return false;
                });
                // annulation de modification
                $('button[type=reset]').bind('click', function (e) {
                    $(this.form).setReset();
                    $(this).attr('disabled', 'disabled');
                    $('button[type=submit]').html('Ajouter');
                    $('h2#title_host').html('Créer un host');
                    e.stopImmediatePropagation();
                    return false;
                });
                // chargement d'un host existant
                $(document).on('click', 'table.t_host tbody#recept tr', function () {
                    if ( !$(this).hasClass('vide') ) getHost( this.id.split('_').pop() );
                });
                // suppression d'un host
                $(document).on('click', 'table.t_host tbody#recept tr a', function (e) {
                    var id = $(this).parent().parent().attr('id').split('_').pop();
                    config.hosts = config.hosts.slice(0, id -1 ).concat( config.hosts.slice(id) );
                    $('table.t_host tbody#recept tr:not(.vide)').remove();
                    if (config.hosts.length == 0) $('table.t_host tbody#recept tr:first').show();
                    else for (var k = 0; k< config.hosts.length; k++) setHost( config.hosts[k] , k+1 , true );
                    if (generated) boxmodif();
                    e.stopImmediatePropagation();
                    return false;
                });
                // page suivante
                $('form#f_addHosts').bind('submit', function (e) {
                    $('#apps').removeClass('disabled').trigger('click');
                    e.stopImmediatePropagation();
                    return false;
                });
                
                if (generated) $('form#f_addHosts button').hide();

            },
            'apps' : function () {
                console.log("in apps");
                if (!config.apps) config.apps = $.extend(true, {}, appList);
                // reaffichage des apps existantes
                for (var k in config.apps) setApps( k );
                if (config.defaultApp) $('select[name=defaultApp]').val( config.defaultApp ).trigger('change');
                // formulaire d'ajout d'apps
                $('form#f_addApps').bind('submit', function (e) {
                    var name = this.app.value;
                    config.apps[ name ] = Array();
                    setApps( name );
                    $(this).setReset().hide();
                    $('button#bt_addApps').show();
                    if (generated) boxmodif();
                    e.stopImmediatePropagation();
                    return false;
                });
                // suppression d'apps
                $(document).on('click', 'table.t_apps tbody#recept tr a', function (e) {
                    var id = $('td:first', $(this).parent().parent()).html();
                    delete config.apps[ id ];
                    var old = $('select[name=defaultApp]').val();
                    $('select[name=defaultApp] option').remove();
                    $('table.t_apps tbody#recept tr:not(.vide)').remove();
                    if (config.apps === {}) $('table.t_apps tbody#recept tr:first').show();
                    else for (var k in config.apps) setApps( k );
                    $('select[name=defaultApp]').val( old ).trigger('change');
                    if (generated) boxmodif();
                    e.stopImmediatePropagation();
                    return false;
                });
                // apparition du formulaire de creation d'apps
                $('button#bt_addApps').bind('click', function () {
                    console.log("tamere");
                    $(this).hide();
                    $('form#f_addApps').show();
                });
                // annulation de crateion d'apps
                $('form#f_addApps button[type=reset]').bind('click', function (e) {
                    $(this.form).setReset().hide();
                    $('button#bt_addApps').show();
                    e.stopImmediatePropagation();
                    return false;
                });
                // affichage des modules d'une app
                $(document).on('click', 'table.t_apps tbody#recept tr.app', function () {
                    var id = $('td:first', $(this)).html();
                    $('table.t_apps tbody#recept tr.app').removeClass('selected');
                    $(this).addClass('selected');
                    // remplissage des modules dans le tableau
                    $('span.appName').html(id);
                    $('form#f_addModule input[name=app]').val(id);
                    $('table.t_modules tbody#recept tr:not(.vide)').remove();
                    for (var k in config.apps[id]) setModule( config.apps[id][k], id );
                    if (config.apps[id].length == 0) $('table.t_modules tbody#recept tr:first').show();
                    else $('table.t_modules tbody#recept tr:first').hide();
                    //affichage du tableau
                    $('div.bloc_module').show();
                });
                // formulaire d'ajout de modules
                $('form#f_addModule').bind('submit', function (e) {
                    var name = this.module.value, app = this.app.value;
                    config.apps[ app ].push( name );
                    setModule( name, app );
                    $(this).setReset().hide();
                    $('td:eq(1)', $('tr.app#inst_' + app)).html( config.apps[ app ].length )
                    $('button#bt_addModule').show();
                    if (generated) boxmodif();
                    e.stopImmediatePropagation();
                    return false;
                });
                // apparition du formulaire de creation de module
                $('button#bt_addModule').bind('click', function () {
                    $(this).hide();
                    $('form#f_addModule').show();
                });
                // annulation de crateion de module
                $('form#bt_addModule button[type=reset]').bind('click', function (e) {
                    $(this.form).setReset().hide();
                    $('button#bt_addModule').show();
                    e.stopImmediatePropagation();
                    return false;
                });
                // suppression de modules
                $(document).on('click', 'table.t_modules tbody#recept tr a', function (e) {
                    var id = $('td:first', $(this).parent().parent()).html(),
                        app = $('form#f_addModule input[name=app]').val(),
                        n = $.inArray(id, config.apps[ app ]);
                        
                    config.apps[ app ] = config.apps[ app ].slice(0, n ).concat( config.apps[ app ].slice( n + 1 ) );
                    $('table.t_modules tbody#recept tr:not(.vide)').remove();
                    if (config.apps[app] === []) $('table.t_apps tbody#recept tr:first').show();
                    else for (var k in config.apps[ app ]) setModule( config.apps[ app ][ k ], app );
                    $('td:eq(1)', $('tr.app#inst_' + app)).html( config.apps[ app ].length );
                    if (generated) boxmodif();
                    e.stopImmediatePropagation();
                    return false;
                });
                
                // application par défaut
                $('select[name=defaultApp]').bind('change', function () {
                    config.defaultApp = $(this).val();
                    if (generated) boxmodif();
                });
                
                if (generated) $('form#f_addAppMod button').hide();
                // page suivante
                $('form#f_addAppMod').bind('submit', function (e) {
                    config.defaultApp = $('select[name=defaultApp]').val();
                    generate();
                    e.stopImmediatePropagation();
                    return false;
                });
            },
            'model' : function () {
                $('form#shbuild').bind('submit', function (e) {
                    $.get(this.action, function () {
                        infoload($('div#model_build'));
                        $('form#shexec button').removeAttr('disabled');
                        $('form#shdl button').removeAttr('disabled');
                    });
                    e.stopImmediatePropagation();
                    return false;
                });
                
                $('form#shexec').bind('submit', function (e) {
                    $.get(this.action, function () {
                        infoload($('div#model_exec'));
                    });
                    e.stopImmediatePropagation();
                    return false;
                });
            }
        },
    // affichage du boutton de modification
        boxmodif = function ( hide ) {
            if (hide) {
                $('body > div.msgboxLight').get(0).activate = false;
                $('body > div.msgboxLight').animate({top:"-=155"})
            }
            else if (!$('body > div.msgboxLight').get(0).activate) {
                $('body > div.msgboxLight').get(0).activate = true;
                $('body > div.msgboxLight').animate({top:"+=155"})
            }
        },
        
    // affichage d'information
        infoload = function ( obj ) {
            obj.css('opacity', 0).show().fadeTo('fast', 1, function () {
                var o = this;
                setTimeout( function () {
                    $(o).fadeTo('slow', 0, function () { $(o).hide(); });
                }, 5000);
            })
        },
        
    // generation du fichier de configuration
        generate = function () {
            $.ajax({
                type: 'POST',
                url: 'generate',
                data: { config : JSON.stringify(config) },
                success: function () {
                    formTraitement( true );
                    if (relocation) location.href = relocation + '?generated=1';
                    else {
                        infoload($('div#generated'));
                        boxmodif( true );
                    }
                },
                error: function () {
                    infoload($('div#error_generated'));
                }
            });
        },
        
    // ajout d'une apps
        setApps = function ( app ) {
            var recept = $('table.t_apps tbody#recept'),
                tr = $('<tr></tr>').addClass('app').attr('id', 'inst_'+ app );
                tr.append( $('<td></td>').html( app ) );
                tr.append( $('<td></td>').html( config.apps[ app ].length ) );
                tr.append( $('<td></td>').html( appList[ app ] ? '' : '<a href="javascript:;">&#xD7;</a>' ) );
                recept.append( tr );
                $('table.t_apps tbody#recept tr:first').hide();
                $('<option></option>').html( app ).appendTo('select[name=defaultApp]');
                selectTraitement();
        },
    // ajout d'un module
        setModule = function ( module, app ) {
            var recept = $('table.t_modules tbody#recept'),
                tr = $('<tr></tr>').addClass('module');
                tr.append( $('<td></td>').html( module ) );
                tr.append( $('<td></td>').html( appList[ app ] && $.inArray( module, appList[ app ] ) > -1 ? '' : '<a href="javascript:;">&#xD7;</a>' ) );
                recept.append( tr );
                $('table.t_modules tbody#recept tr:first').hide();
        },
    // Recuperation d'un host existant
        getHost = function ( id ) {
            var host = config.hosts[ id - 1 ];
            host.inst = id - 1;
            $('#f_addHost').setArray( host );
            $('button[type=reset][disabled]').removeAttr('disabled');
            $('button[type=submit]').html('Modifier');
            $('h2#title_host').html('Modifier un host');
        },

    // Affichage / modification des hosts du tableau
        setHost = function ( arrHost, inst, create ) {
            var recept = $('tbody#recept'),
                tr = $('<tr></tr>').attr('id', 'inst_' + inst);
                tr.append( $('<td></td>').html( arrHost.hostname ) );
                tr.append( $('<td></td>').html( arrHost.type ) );
                tr.append( $('<td></td>').html( arrHost.path ) );
                tr.append( $('<td></td>').html( config.bdd && config.bdd === '1' ? 'Oui ( ' + arrHost.bddhost + ' - ' + arrHost.database + ' - ' + arrHost.login + ' )' : 'Non' ) );
                tr.append( $('<td></td>').html( arrHost.debug === '1' ? 'Oui' : 'Non' ) );
                tr.append( $('<td></td>').html( arrHost.log === '1' ? 'Oui' : 'Non' ) );
                tr.append( $('<td></td>').html( '<a href="javascript:;">&#xD7;</a>' ) );
                if (create) recept.append( tr );
                else $('tr#inst_' + inst).replaceWith( tr );
                $('tbody#recept tr:first').hide();
        },

    //habillage des radios
        radioTraitement = function () {
            $('input[type=radio]').each(function () {
                this.temoin = $('<span></span>').addClass('checkb').prependTo($(this).parent());
                $(this).bind( 'change.checkbox' , function () {
                    if (this.checked) {
                        $('input[name="' + this.name + '"]').each(function () { this.temoin.css('backgroundPosition', 'top left'); });
                        this.temoin.css('backgroundPosition', 'bottom left');
                    }
                });
            });
            $('input[type=radio]:checked').trigger('change.checkbox');
        },

    //habillage des selects
        selectTraitement = function () {
            $('select').each(function () {
                if (!this.temoin) {
                    this.temoin = $('<span></span>').addClass('selb').prependTo($(this).parent());
                    $(this).bind( 'change.select' , function () { this.temoin.html( $(this).val() ); });
                }
                var pos = $(this).position();
                $(this).width( $(this).width() + 25 );
                this.temoin.width( $(this).outerWidth() - 10 );
                this.temoin.css({top:pos.top, left:pos.left});
            });
            $('select').trigger('change.select');
        },

    // gestion du menu
        menuTraitement = function (e) {
            var lnk = this.href.replace( /.*\//, '' );
            if (!$(this).hasClass('disabled')) {
                $('body > header > nav a.activ').removeClass('activ');
                $(this).addClass('activ');
                $('.container').load(lnk, function () {
                    radioTraitement();
                    selectTraitement();
                    $('.container').append('<div class="cb"></div>');
                    if ( init[ lnk ] && typeof init[ lnk ] == "function") init[ lnk ].call();
                });
            }
            e.stopImmediatePropagation();
            return false;
        },

    // Traitement général de formulaire 
        formTraitement = function ( unset ) {
            window.onbeforeunload = !unset ? function () {
                return 'Attention:\n\nLe rechargement de la page va provoquer la perte des données saisies.\nÊtes vous sûr de vouloir poursuivre ?';
            } : function () {};
        };

/* 
/* Execution
 */
    
    // activation du menu
    $('body > header > nav a').bind('click.menu', menuTraitement );
    
    $(document).on('click', 'div.msgbox button', function () { $(this).parents('div.msgbox').hide(); });

    // generation du fichier de config
    $('div#modified button').bind('click', function () { generate(); });

    // premiere page
    if ( !$.isEmptyObject(config) ) {
        relocation = false;
        $('body > header > nav a').removeClass('disabled');
    } else relocation = 'install/main/';
    $('body > header > nav a:first').trigger('click');
    
    if (!generated) $('#model').remove();
    
});