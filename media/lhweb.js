/*
 * *****************************************************************************
 * *************************    MISC    ****************************************
 * *****************************************************************************
 */
String.prototype.replaceAll = function(target, replacement) {
    try {
        return this.split(target).join(replacement);
    } catch(E){
        lhweb.log("REPLACE ALL [this:" + this + "] " + target + " -> " + replacement);
        lhweb.log("ERROR:" + E.stack);
        return target;
    }
};

String.prototype.lpad = function(length, padString) {
    var str = this;
    var padString = padString || "0";
    while (str.length < length)
        str = padString + str;
    return str;
};

jQuery(document).ajaxError(function(event,jqXHR,ajaxSettings,thrownError){
    var errorCallback = $(jqXHR).data("errorCallback");
    if(errorCallback!=undefined){
        try {
            lhweb.log("GLOBAL AJAX ERROR:" + errorCallback);
            errorCallback(event);
        } catch(E) {
            lhweb.log("GLOBAL AJAX ERROR EXCEPTION:"+ E);
        }
    }
});

var lhweb = {};
lhweb.version = 0.1;

lhweb.log = function(txt){
    if(console) console.log(txt);
};

lhweb.debug = function(txt){
    if(lhweb._debug) lhweb.log(txt);
};

lhweb.alert = function(txt){
    var dlg = new lhweb.dialog({
        "titulo": "Alerta",
        "conteudo": txt
    });
};

lhweb.confirm = function(title, txt, callback){
    if(confirm(txt)){
        callback();
    }
};

lhweb.prompt  = function(titulo, txt, valorPadrao, callback){
    var div = $("<div class='panel form-horizontal'></div>");
    div.append($("<label class='control-label col-sm-3'>" + txt +"</label>"));
    div.append($("<div class='col-sm-9'><input id='lhwebinp' type='text' class='form-control' /></div>"));
    
    var dlg = new lhweb.dialog({
        "titulo": titulo,
        "conteudo": div
    });
    
    div.find("input#lhwebinp").val(valorPadrao).focus();
    
    dlg.addBotao("OK",function(){
        callback(div.find("input#lhwebinp").val());
        dlg.destroy();
    },"btn-primary");
    
    return dlg;
};

lhweb.dialog = function(opt) {
    this.opt = $.extend({
        "titulo"  : "LH DIALOG",
        "conteudo": "LH DIALOG",
        "tamanho" : "normal",
        "modal"   : false,
        "botoes"  : []
    }, opt);
    
    this.show = function(){
        $(this.mdl).modal('show');
        return this;
    };
    
    this.destroy = function(){
        $(this.mdl).modal("hide").data( 'bs.modal', null );
        return this;
    };
    
    this.setConteudo = function(txt){
        this.body.html(txt);
        return this;
    };
    
    this.setModal = function(bool) {
        $(this.mdl).modal({
            "backdrop": bool?"static":true,
            "keyboard": bool
        });
        
        if(bool){
            $(this.header).find("button").hide();
        } else {
            $(this.header).find("button").show();
        }
    };
    
    this.loadConteudo = function(url, opt, callback) {
        $(this.body).load(url, opt, function(responseText, textStatus, jqXHR){
            if(callback){
                callback(responseText, textStatus, jqXHR);
            }
        });
        return this;
    };
    
    this.addBotao = function(txt, callback, classe){
        var bt  = $("<button />");
        bt.addClass("btn");
        bt.addClass(classe);
        bt.click(callback);
        bt.html(txt);
        $(this.foot).append(bt);
    };
    
    this.find = function(arg){
        return $(this.mdl).find(arg);
    };
    
    this.setTamanho = function(tamanho) {
        this.opt.tamanho = tamanho;
        $(this.dlg).removeClass("modal-lg");
        $(this.dlg).removeClass("modal-sm");
        
        switch(this.opt.tamanho){
            case "pequeno":
                $(this.dlg).addClass("modal-sm"); break;
            case "grande":
                $(this.dlg).addClass("modal-lg"); break;
            case "normal": break;
            default:
                lhweb.log("[LH DIALOG] SET TAMANHO INVALIDO:" + tamanho);
        }
    };
    
    this.mdl = $("<div class='modal fade' tabindex='-1' role='dialog'></div>");
    this.dlg = $("<div class='modal-dialog' role='document'></div>"); this.mdl.append(this.dlg);
    this.content = $("<div class='modal-content'></div>"); this.dlg.append(this.content);
    this.header = $("<div class='modal-header'></div>"); this.content.append(this.header);

    this.header.append($("<button type='button' class='close pull-right' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>"));
    this.titulo = $($("<h4>"+this.opt.titulo+"</h4>")); this.header.append(this.titulo);
    this.body   = $("<div class='modal-body'></div>"); this.content.append(this.body);
    this.foot = $("<div class='modal-footer'></div>"); this.content.append(this.foot);
    
    this.body.html(this.opt.conteudo);
    this.setTamanho(this.opt.tamanho);
    this.setModal(this.opt.modal);
    
    for(b in this.opt.botoes) {
        this.addBotao(b, this.opt.botoes[b]);
    };
    
    return this;
};

lhweb._debug = false;
lhweb._baseUrl = "/";
lhweb._requestController = "";
lhweb.version  = "0.0.1";
lhweb.opt;

lhweb.setup = function(opt){
    var opt = $.extend({
        "debug": false,
        "baseUrl": "/",
        "requestController": ""
    }, opt);
    
    lhweb._debug = opt.debug;
    lhweb._baseUrl = opt.baseUrl;
    lhweb._requestController = opt.requestController;
};

lhweb.getBaseUrl = function (){
    return lhweb._baseUrl;
};

lhweb.setRequestController = function(c){
    lhweb._requestController = c;
};

lhweb.getRequestUrl = function(acao) {
    return lhweb._baseUrl + lhweb._requestController +acao;
};

lhweb.getXHR = function (uploadCallback, downloadCallback){
    var xhr = new window.XMLHttpRequest();
    
    xhr.upload.addEventListener("progress", uploadCallback || function(evt){
      if (evt.lengthComputable) {
        var percentComplete = evt.loaded / evt.total;
        console.log(percentComplete);
      }
    }, false);
    
    xhr.addEventListener("progress", downloadCallback, false);
    return xhr;
};

lhweb.request = function(acao, data, callback, errorCallback, opt){
    var xqr  = lhweb.getXHR(
                function(evt){
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        console.log("UPLOAD:" +percentComplete);
                    }
                }, function(evt){
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        console.log("DOWNLOAD:" +percentComplete);
                    }
                }
            );
    $(xqr).data("errorCallback", errorCallback);
    
    var url = lhweb.getRequestUrl(acao);
    var opt = $.extend({
        "dataType": "json",
        "type"    : "POST",
        "url"     : url,
        "data"    : data,
        "async"   : true,
        "success" : function(r){
            try {
                if(opt["dataType"]=="json"){
                    lhweb.parseRequestResponse(r, callback, errorCallback);
                } else {
                    try {
                        callback(r);
                    } catch(E){
                        
                    }
                }
            } catch(E) {
                lhweb.log("[LH REQUEST SUCCESS EX]" + E);
            }
        },
        "error"   : function(jqXHR, textStatus, errorThrown){
            lhweb.log("[LH REQUEST ERROR:" + acao +"] [" + textStatus + "] " + errorThrown);
            errorCallback(textStatus,errorThrown);
        }
    },opt);
    
    try {
        return $.ajax(url, opt);
    } catch(E) {
        var err = ""+E;
        lhweb.log("[LH REQUEST EXCEPTION] [" + acao + "] " + E);
        if(err.indexOf("Maximum call stack size exceeded")!=-1){
            lhweb.log("Check your request parameters, it's problably something wrong with them. Maybe your forgot to call the .val() on some object :)");
        }
        
    }
};

lhweb.parseRequestResponse = function(r, callback, errorcallback){
    if(r["status"]==="OK"){
        if(callback) {
            try {
                callback(r["data"]);
            } catch(E) {
                lhweb.log("[ERRO NO CALLBACK:" + callback);
                lhweb.log("ERROR:" + E);
            }
        }
    } else if(r["status"]==="AUTH"){
        window.open("index.php#" + location.hash,"_self");
    } else {
        if(errorcallback) {
            try {
                errorcallback(r["status"],r["data"]);
            } catch(E) {
                lhweb.log("[ERRO NO ERROR CALLBACK:" + callback);
                lhweb.log("ERROR:" + E);
            }
        } else {
            lhweb.alert(r["data"]);
        }
    }
};

lhweb.dialogProcurar = function(lhform, callback){
    var dlg = new lhweb.dialog({
        "titulo": "Procurar",
        "tamanho": "grande",
        "conteudo": "Aguarde, carregando..."
    });

    dlg.loadConteudo("misc/procurar.php", {
        "webController": lhform.getWebController()
    },function(){
        dlg.find("#form_procurar").submit(function(e){
            e.preventDefault();
            e.stopPropagation();

            dlg.find("#form_procurar").find("fieldset").prop("disabled",true);
            dlg.find("#procurar_resultado").html("Aguarde, efetuando pesquisa");

            var opt = {};
            dlg.find("input,select").each(function(){
                opt[$(this).attr("id")] = $(this).val();
            });

            $(dlg.find("#procurar_resultado")).load("misc/procurar_resultado.php", opt, function(responseText, textStatus, jqXHR){
                dlg.find("#form_procurar").find("fieldset").prop("disabled",false);
                dlg.find("#form_procurar").find("table tbody tr td").click(function(){
                    var pk = $(this).parent().data("pk");
                    if(!pk){
                        lhweb.alert("FORM COM PROBLEMAS NA PROCURA [NO PK]");
                        return;
                    }

                    dlg.destroy();
                    callback(pk);
                });
            });
        });
    }).show();
};

/*
 * FORM OBJECT AND IT'S FUNCTIONALITIES
 */
lhweb.form = function(opt){
    /*
     * ARMAZENAMENTO DE CONFIGURAÇÕES DO FORMULÁRIO
     */
    this.opt = $.extend({
        "target"                : null,
        "webController"         : "webControllerStub",
        "entityName"            : "entityNameStub",
        "data"                  : null,
        "formulariosRequeridos" : []
    }, opt);
    
    this.getFormulariosRequeridos = function(){
        return this.opt.formulariosRequeridos;
    };
    
    this.setTarget = function(target) {
        this.opt.target = target;
    };
    
    this.getTarget = function(){
        return this.opt.target;
    };
    
    this.setData = function(data){
        this.opt.data = data;
    };
    
    this.getData = function(){
        return this.opt.data;
    };
    
    this.getPkName = function(){
        return "ID";
    };
    
    this.getPk = function(){
        if(this.opt && this.opt.data){
            return this.opt.data[this.getPkName()];
        }
    };
    
    this.getFormData = function(){
        lhweb.log("LH GET FORM DATA [STUB]");
    };
    
    this.getDataByPk = function(pk, callback){
        var opt = {};
        opt[this.getPkName()] = pk;
        lhweb.request(this.getWebController()+".Mover", opt,
            function(r){
                callback(r);
            },function(err, msg){
                lhweb.alert(msg);
            }
        );
    };
    
    this.getWebController = function(){
        return this.opt.webController;
    };
    
    this.setWebController = function(w) {
        this.opt.webController = w;
    };
    
    this.getEntityName = function(){
        return this.opt.entityName;
    };
    
    this.setEntityName = function(e){
        this.opt.entityName  = e;
    };
    
    this.start = function(){
        lhweb.log("LH FORM START [STUB]");
    };
    
    this.preencher = function(){
        lhweb.log("LH FORM PREENCHER [STUB]");
    };
    
    this.objetoToString = function(r){
        return "FORMATAR OBJETO STUB:" + r;
    };
    
    this.setupMenubar = function(target){
        var target = target || $(this.opt.target).find("#menubar");
        this.menubar = new lhweb.menubar(target, this);
    };
    
    this.getSearchField = function(opt) {
        var $this = this;
        var opt = $.extend({
            "required" : null,
            "id_hidden":  this.getEntityName().toLowerCase() + "_id",
            "id_txt"   :  this.getEntityName().toLowerCase(),
            "callback" : null,
            "id_value" : "",
            "txt_value": "",
        }, opt);
        
        var div    = $("<div class='input-group'></div>");
        var inphid = $("<input type='hidden' />").attr("id", opt.id_hidden).val(opt.id_value);
        var inptxt = $("<input type='text' />")
                .attr("id", opt.id_txt)
                .prop("readonly", true)
                .addClass("form-control")
                .val(opt.txt_value);
        
        var span = $("<span class='input-group-btn'></span>");
        if(!opt.required){
            var btnclear = $("<button></button>")
                    .addClass("btn btn-default")
                    .attr("type", "button")
                    .attr("title", "Remover " + this.getEntityName() + " Selecionado")
                    .append("<span class='glyphicon glyphicon-ban-circle' aria-hidden='true'></span>")
                    .click(function(){
                        inphid.val("");
                        inptxt.val("");
                    });
            span.append(btnclear);
        } else {
            inptxt.prop("required", true);
        }
        
        var btnsearch = $("<button></button>")
                    .attr("type", "button")
                    .attr("title", "Selecionar " + this.getEntityName())
                    .addClass("btn")
                    .addClass("btn-default")
                    .append("<span class='glyphicon glyphicon-search' aria-hidden='true'></span>")
                    .click(function(){
                        lhweb.dialogProcurar($this, function(pk){
                            $this.getDataByPk(pk, function(d){
                                inphid.val(d[$this.getPkName()]);
                                inptxt.val($this.objetoToString(d));
                                
                                if(opt.callback) {
                                    try {
                                        opt.callback(d);
                                    } catch(E) {
                                        lhweb.log("LH SEARCH FIELD CALLBACK ERROR:" + E);
                                    }
                                }
                            });
                        });
                    });
        span.append(btnsearch);
        
        div.append(inphid).append(inptxt).append(span);
        return div;
    };
    
    this.request = function(acao, data, callback, errorCallback) {
        return lhweb.request(this.getWebController()+"."+acao, data, callback, errorCallback);
    };
    
    return this;
};

// MASCARAS PADRÃO
lhweb.mask = {};
lhweb.mask.cpf = function(target){
    return $(target).mask("000.000.000-00", {reverse: false});
};

lhweb.mask.cnpj = function(target){
    return $(target).mask("00.000.000/0000-00", {reverse: false});
};

lhweb.mask.data = function(target){
    return $(target).mask("00/00/0000", {reverse: false});
};

lhweb.mask.hora = function(target){
    return $(target).mask("00:00", {reverse: false});
};

lhweb.mask.datahora = function(target){
    return $(target).mask("00/00/0000 00:00", {reverse: false});
};

lhweb.mask.telefone = function(target){
    var SPMaskBehavior = function (val) {
        return val.replace(/\D/g, '').length === 11 ? '(00) 00000-0000' : '(00) 0000-00009';
    };

    var spOptions = {
        onKeyPress: function(val, e, field, options) {
            field.mask(SPMaskBehavior.apply({}, arguments), options);
        }
    };
    
    return $(target).mask(SPMaskBehavior, spOptions);
};

lhweb.mask.dinheiro = function(target){
    $(target).mask("#.##0,00", {reverse: true});
};

// FORM HANDLING FUNCTIONS
lhweb.forms = {};
lhweb.forms.active = new lhweb.form();
lhweb.forms.open = function(form, target) {
    if(lhweb.forms[form]!==undefined){
        lhweb.forms.start(form,target);
    } else {
        var dlg = new lhweb.dialog({
            "titulo": "Carregando Formulário [" + form + "]",
            "conteudo": "Aguarde, enviando solicitação",
            "modal": true
        });
        dlg.show();
        
        $.ajax({
            url: "/"+form+".js?v=" + lhweb.version,
            // cache: true, // cachear, vou controlar com o parametro da versao
            dataType: "script",
            success: function(){
                dlg.destroy();
                lhweb.forms.start(form,target); 
            },
            error: function( jqxhr, settings, exception) {
                dlg.setConteudo("Erro ao Carregar o Formulário<br/><p class='text-error'>" + exception +"</p>");
                dlg.setModal(false);
            }
        });
    }
};

lhweb.forms.load = function(form, callback) {
    try {
        if(lhweb.forms[form]===undefined){
            $.getScript("/"+form+".js",function(){
                callback(lhweb.forms[form]);
            });
        } else {
            callback(lhweb.forms[form]);
        }
    } catch(E) {
        lhweb.alert("FALHA AO CARREGAR FORMULARIO:" + form + "\n" + E);
    }
};

lhweb.forms.start = function(form,target){
    var formulariosRequeridos = lhweb.forms[form].getFormulariosRequeridos();
    for(var f in formulariosRequeridos){
        var f = formulariosRequeridos[f];
        if(!lhweb.forms[f] || lhweb.forms[f]===undefined){
            lhweb.forms.load(f,function(){
                lhweb.forms.start(form,target);
            });
            return;
        }
    }
    
    lhweb.forms.active = lhweb.forms[form];
    lhweb.forms.active.setTarget(target);
    lhweb.forms.active.start();
};

lhweb.button = function(id, icon, txt, btclass, onclick){
    var bt = $("<button />");
    bt.addClass("btn " + btclass);
    bt.attr("title", txt);
    bt.append("<span class='glyphicon glyphicon-" + icon + "' aria-hidden='true'></span>");
    bt.click(onclick);
    return bt;
};

/*
 * Menubar :)
 */
lhweb.menubar = function(target, lhform) {
    var $this = this;
    this.target = target;
    this.lhform = lhform;
    
    this.mover = function(direcao, opt){
        var $this = this;
        this.setMode("view");
        var dlg = new lhweb.dialog({
            "titulo": "Mover [" + direcao +"]",
            "conteudo": "Aguarde, enviando solicitação",
            "modal": true
        });
        dlg.show();
        lhweb.request(this.lhform.getWebController()+"."+direcao, opt,
            function(r){
                if(r!=null && r!="null" && r!=undefined && r!=''){
                    $this.lhform.setData(r);
                    $this.lhform.preencher();
                }
                dlg.destroy();
            },function(err, msg){
                dlg.destroy();
                lhweb.alert(msg);
            }
        );
    };
    
    this.primeiro = function(){
        this.mover("Primeiro", {});
    };
    
    this.ultimo = function(){
        this.mover("Ultimo", {});
    };
    
    this.anterior = function(){
        if(this.lhform.getPk()!=null && this.lhform.getPk()!=undefined){
            var opt = {};
            opt[this.lhform.getPkName()] = this.lhform.getPk();
            this.mover("Anterior", opt);
        }
    };
    
    this.proximo = function(){
        if(this.lhform.getPk()!=null && this.lhform.getPk()!=undefined){
            var opt = {};
            opt[this.lhform.getPkName()] = this.lhform.getPk();
            this.mover("Proximo", opt);
        }
    };
    
    this.novo = function(){
        this.setMode("new");
    };
    
    this.editar = function(){
        if(this.lhform.getPk()!=null && this.lhform.getPk()!=undefined){
            this.setMode("edit");
        } else {
            lhweb.alert("Nenhum Registro para Editar");
        }
    };
    
    this.salvar = function(){
        var form_ok = true;
        
        $(this.lhform.getTarget()).find("input,button").each(function(){
            if($(this).prop("required")){
                if(!$(this).val()){
                    form_ok = false;
                    $(this).parent().addClass("has-error");
                } else {
                    $(this).parent().removeClass("has-error");
                }
            }
        });
        
        if(!form_ok){
            return;
        }
        
        var dlg = new lhweb.dialog({
            "titulo": "Salvar",
            "conteudo": "Aguarde, enviando solicitação",
            "modal": true
        });
        lhweb.request(this.lhform.getWebController()+".Salvar", 
            this.lhform.getFormData(),
            function(r){
                dlg.destroy();
                $this.setMode("view");
                $this.lhform.setData(r);
                $this.lhform.preencher();
            }, function(err, msg){
                dlg.destroy();
                lhweb.alert(msg);
            }
        );
    };
    
    this.cancelar = function(){
        this.setMode("view");
        this.lhform.preencher();
    };
    
    this.apagar = function(){
        var opt = {};
        opt[this.lhform.getPkName()] = this.lhform.getPk();
        lhweb.confirm("Deseja Apagar este Registro ?","Cuidado, essa operação é irreversível !", function(){
            lhweb.request($this.lhform.getWebController()+".Apagar", opt,
                function(r){
                    $this.mover("Anterior",opt);
                }, function(err, msg){
                    lhweb.alert(msg);
                }
            );
        });
    };
    
    this.procurar = function(){
        lhweb.dialogProcurar(this.lhform, function(pk){
            var opt = {};
            opt[$this.lhform.getPkName()] = pk;
            $this.mover("Mover", opt);
        });
    };
    
    this.setMode = function(mode) {
        this.mode = mode;
        $(this.lhform.getTarget()).find(".has-error").removeClass("has-error");
        switch(this.mode){
            case "view":
                $(this.lhform.getTarget()).find("input,button").each(function(){
                    $(this).val("");
                    switch($(this).attr("type")){
                        case "input":
                            $(this).prop("readonly",true); break;
                        default:
                            $(this).prop("disabled",true); break;
                    }
                });
                this.lhform.preencher();
                break;
            case "edit":
                $(this.lhform.getTarget()).find("input,button").each(function(){
                    switch($(this).attr("type")){
                        case "input": 
                            $(this).prop("readonly",false); break;
                        default:
                            $(this).prop("disabled",false); break;
                    }
                });
                this.lhform.preencher();
                break;
            case "new":
                $(this.lhform.getTarget()).find("input,button").each(function(){
                    $(this).val("");
                    switch($(this).attr("type")){
                        case "input": 
                            $(this).prop("readonly",false); break;
                        default:
                            $(this).prop("disabled",false); break;
                    }
                });
                break;
        }
        $(this.target).find("button").prop("disabled",true);
        $(this.target).find("button.mb-"+mode).prop("disabled",false);
    };
    
    var $this = this;
    this.target.addClass("btn-toolbar btn-group pull-right border-all");
    this.target.css("border","etched 1px");
    this.target.append(new lhweb.button("mb-prior"  ,"arrow-left"   ,"Anterior" , "btn-xs btn-default mb-view" , function(){ $this.anterior(); }));
    this.target.append(new lhweb.button("mb-next"   ,"arrow-right"  ,"Proximo"  , "btn-xs btn-default mb-view" , function(){ $this.anterior(); }));
    this.target.append(new lhweb.button("mb-new"    ,"file"         ,"Novo"     , "btn-xs btn-default mb-view" , function(){ $this.novo(); }));
    this.target.append(new lhweb.button("mb-edit"   ,"edit"         ,"Editar"   , "btn-xs btn-default mb-view" , function(){ $this.editar(); }));
    this.target.append(new lhweb.button("mb-prior"  ,"floppy-disk"  ,"Salvar"   , "btn-xs btn-default mb-new mb-edit" , function(){ $this.salvar(); }));
    this.target.append(new lhweb.button("mb-cancel" ,"repeat"       ,"Cancelar" , "btn-xs btn-default mb-new mb-edit" , function(){ $this.cancelar(); }));
    this.target.append(new lhweb.button("mb-del"    ,"trash"        ,"Apagar"   , "btn-xs btn-default mb-view" , function(){ $this.apagar(); }));
    this.target.append(new lhweb.button("mb-search" ,"search"       ,"Procurar" , "btn-xs btn-default mb-view" , function(){ $this.procurar();}));
    
    return this;
};

lhweb.readFileAsDataUrl = function(file, callback) {
    var reader = new FileReader();
    reader.onload = function(e) {
        try {
            callback(reader.result);
        } catch(E) {
            lhweb.log("LH READ FILE AS DATAURL[" + file + "]:" + E);
        }
    };
    reader.readAsDataURL(file);
};

lhweb.thumbnail = function(target, opt) {
    var $this = this;
    this.opt = $.extend({
        "exibirBotao": true,
        "imageId": "image",
        "arquivoId": "arquivo",
        "urlSemImagem": "noimagem.png",
    },opt);
    
    this.arq = $("<input type='file' id='" + this.opt.arquivoId + "' style='display: none;' />"); 
    this.img = $("<img class='img-thumbnail' id='" + this.opt.imageId + "' src='" + this.opt.urlSemImagem + "' />");
    this.bt  = $("<button type='button' class='btn btn-primary'>Selecionar Imagem</button>");
    
    $(target).append(this.img);
    $(target).append("<br/><br/>");
    $(target).append(this.bt);
    
    this.getImageData = function(){
        return this.img.attr("src");
    };
    
    this.setImagemData = function(b64data){
        this.img.attr("src",b64data);
    };
    
    $(this.bt).click(function(){
        $this.arq.click();
    });
    
    $(this.arq).change(function(evt){
        evt.stopPropagation();
        evt.preventDefault();

        var files = evt.target.files;
        var file = files[0];
        if(!file){
            $this.img.attr("src", $this.opt.urlSemImagem);
        } else {
            lhweb.readFileAsDataUrl(file, function(dataUrl){
                $this.img.attr("src", dataUrl);
            });
        }
    });
    
    return this;
};

lhweb.setupTabs = function(target, callback){
    var t = $(target);
    t.find('.nav-pills, .nav-tabs').tabdrop();
    t.find('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        //e.target // newly activated tab, e.relatedTarget // previous active tab
        var active_tab = (""+e.target).split("#")[1];
        t.data("active_tab", active_tab);
        if(callback){
            try {
                callback(e.target, active_tab);
            } catch(E){
                lhweb.log("TAB CHANGE CALLBACK:" + E);
            }
        }
    });
    
    t.find(".nav-tabs a").click(function(e){
        e.preventDefault();
        return !$(this).parent().hasClass("disabled");
    });
    
    $(t.find('.nav-tabs a').get(1)).tab('show');
};