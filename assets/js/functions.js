function suggestionbox(target,url,callback,init_id,tipo,limit_char){
	if(!limit_char){
		limit_char = 2;
	}	
	if(init_id){
		init_id = ""+init_id+"";
	}else{
		init_id = false;
	}
	
	if(tipo){
		tipo = ""+tipo+"";
	}else{
		tipo = "default";
	}
	
	target.Suggestionbox({
		pag           : url,  //P�GINA CHAMADA PELO AJAX (OBRIGAT�RIO QDO N�O HOUVER Itens Pr� Listados)
		//itens         : data.itens,          //IT�NS PR� LISTADOS (N�O NECESSITA DE Pag Ajax)
	    qtd_view      : 10,                    //QTDE DE RESULTADOS VIS�VEIS
	    largura       : "auto",                //LARGURA DA CAIXA DE SUGGESTION
	    marca_texto   : true,                  //DESTACA TEXTO DIGITADO
	    cor_marca     : "#000",                //COR DA MARCA DE TEXTO
	    font_face     : "arial",               //FONTE DO SUGGESTION
	    font_color    : "#264E6D",             //COR DA FONTE DO SUGGESTION
		font_size     : "14px",                //TAMANHO DA FONTE DO SUGGESTION
		id            : true,                  //INDICA QUE O RESULTADO N�O SER� O DA CAIXA DE TEXTO MAS DE UM ID ESTABELECIDO (N�O OBRIGAT�RIO)	
		tipo          : tipo,            //TIPO 'default' (PADR�O), 'select' (TRANSFORMA O OBJETO EM UM SELECT SEMELHANTE AO SELECT DO FORM) OU 'multiple' (MULTIPLAS ESCOLHAS)
		init_id       : init_id,           //EXIBE RESULTADOS INICIAIS (CASO O TIPO SEJA MULTIPLE SEPARE POR  OS VALORES POR '|')
		largura_mult  : 'auto',                //LARGURA DA CAIXA DE MULTIPLO
		limit_char    : limit_char,
		callback      : function(){            //FUN��O CHAMADA AP�S A ESCOLHA DO ITEM
			//$("#teste").html(this.id);			
			if(callback){
				callback.call(undefined,target,this.id);
			}
		}
	});
	
}

function limpaCampoSuggNotFound(target,id){
	if(!id){
		target.parent().find("input[id^=campo_suggestion]").val('');
	}
}

function criarLoading(){
    window.scrollTo(0,0);
    var wscreen=window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
    var hscreen=window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight; 
    $("body").append("<div class='box-transp' style='width:"+wscreen+"px;height:"+hscreen+"px'>&nbsp;</div>");
    $(".box-transp").append("<div class='loading'></div>");	
}

function limparLoading(){
	$(".box-transp").remove();
}